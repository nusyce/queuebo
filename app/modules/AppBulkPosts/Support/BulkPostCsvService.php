<?php

namespace Modules\AppBulkPosts\Support;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;
use Modules\AppBulkPosts\Models\BulkPostBatch;
use Modules\AppBulkPosts\Models\BulkPostRow;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class BulkPostCsvService
{
    public function import(UploadedFile $file, User $user, array $options = []): BulkPostBatch
    {
        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId($user);
        $timezone = (string) ($user->timezone ?: config('app.timezone', 'UTC'));
        $selectedAccountIds = collect((array) ($options['account_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
        $campaignId = filled($options['campaign_id'] ?? null) ? (int) $options['campaign_id'] : null;
        $labelIds = collect((array) ($options['label_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
        $intervalMinutes = max(1, (int) ($options['interval_minutes'] ?? 60));
        $shortenUrls = (bool) ($options['shorten_urls'] ?? false);
        $lines = preg_split("/\r\n|\n|\r/", (string) $file->get());
        $lines = array_values(array_filter($lines, static fn ($line) => trim((string) $line) !== ''));

        if (count($lines) < 2) {
            abort(422, 'The CSV file must contain a header row and at least one data row.');
        }

        $headers = array_map([$this, 'normalizeHeader'], str_getcsv((string) array_shift($lines)));
        $headerMap = array_flip($headers);
        $publishableCapabilityKeys = publishable_channel_capability_keys($user);
        $accounts = SocialAccount::query()
            ->where('created_by_user_id', $ownerUserId)
            ->where(function ($query) use ($publishableCapabilityKeys): void {
                $query->whereIn('capability_key', $publishableCapabilityKeys)
                    ->orWhere(function ($fallbackQuery) use ($publishableCapabilityKeys): void {
                        $fallbackQuery->whereNull('capability_key')
                            ->whereIn('provider_key', $publishableCapabilityKeys);
                    });
            })
            ->get()
            ->keyBy(fn (SocialAccount $account) => (string) $account->id);

        return DB::transaction(function () use ($file, $user, $timezone, $ownerUserId, $lines, $headers, $headerMap, $accounts, $selectedAccountIds, $campaignId, $labelIds, $intervalMinutes, $shortenUrls): BulkPostBatch {
            $batch = BulkPostBatch::query()->create([
                'owner_user_id' => $user->id,
                'team_id' => TeamWorkspaceAccess::activeTeam($user)?->id,
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'Bulk import',
                'status' => 'draft',
                'source_filename' => $file->getClientOriginalName(),
                'meta' => [
                    'timezone' => $timezone,
                    'workspace_owner_user_id' => $ownerUserId,
                    'headers' => $headers,
                    'selected_account_ids' => $selectedAccountIds,
                    'campaign_id' => $campaignId,
                    'label_ids' => $labelIds,
                    'interval_minutes' => $intervalMinutes,
                    'shorten_urls' => $shortenUrls,
                ],
                'stats' => [],
            ]);

            $stats = [
                'total_rows' => 0,
                'valid_rows' => 0,
                'invalid_rows' => 0,
                'processed_rows' => 0,
                'created_posts' => 0,
                'failed_rows' => 0,
            ];

            foreach ($lines as $index => $line) {
                $rowNumber = $index + 2;
                $values = str_getcsv($line);
                $data = [];

                foreach ($headers as $position => $header) {
                    $data[$header] = isset($values[$position]) ? trim((string) $values[$position]) : '';
                }

                $normalized = $this->normalizeRow($data, $timezone);
                $errors = $this->validateRow($normalized, $headerMap, $accounts, $selectedAccountIds);

                BulkPostRow::query()->create([
                    'batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'status' => $errors === [] ? 'valid' : 'invalid',
                    'payload' => $normalized,
                    'validation_errors' => $errors,
                ]);

                $stats['total_rows']++;
                if ($errors === []) {
                    $stats['valid_rows']++;
                } else {
                    $stats['invalid_rows']++;
                }
            }

            $batch->update([
                'stats' => $stats,
                'status' => $stats['valid_rows'] > 0 ? 'ready' : 'invalid',
            ]);

            return $batch->fresh(['rows']);
        });
    }

    public function flowSteps(): array
    {
        return [
            ['title' => 'Upload CSV', 'description' => 'Import a spreadsheet with caption, schedule time, media URL, and link columns.'],
            ['title' => 'Choose Publishing Metadata', 'description' => 'Apply one campaign and multiple labels to the imported posts before they enter Publishing.'],
            ['title' => 'Queue Import', 'description' => 'Push validated rows into the publishing pipeline and create scheduled publishing posts automatically.'],
        ];
    }

    public function csvColumns(): array
    {
        return [
            ['column' => 'caption', 'required' => 'Yes', 'example' => 'Flash sale starts now', 'description' => 'Main post caption.'],
            ['column' => 'schedule_time', 'required' => 'No', 'example' => '2026-04-08 09:00:00', 'description' => 'Post schedule time in the current user timezone. If empty, the importer uses now plus the chosen interval.'],
            ['column' => 'link', 'required' => 'No', 'example' => 'https://example.com/promo', 'description' => 'Optional destination link saved with the post payload.'],
            ['column' => 'media_url', 'required' => 'No', 'example' => 'https://cdn.example.com/banner.jpg', 'description' => 'Optional external media reference kept for future import and download flows.'],
            ['column' => 'status_mode', 'required' => 'No', 'example' => 'schedule', 'description' => 'Use draft or schedule. Defaults to schedule.'],
        ];
    }

    public function templateRows(): array
    {
        return [
            ['caption', 'schedule_time', 'link', 'media_url', 'status_mode'],
            ['Flash sale starts now', '2026-04-08 09:00:00', 'https://example.com/sale', 'https://cdn.example.com/sale.jpg', 'schedule'],
            ['New arrivals now live', '2026-04-08 14:30:00', 'https://example.com/new', '', 'draft'],
        ];
    }

    protected function normalizeHeader(string $header): string
    {
        return Str::of($header)->trim()->lower()->replace([' ', '-'], '_')->value();
    }

    protected function normalizeRow(array $row, string $timezone): array
    {
        $schedule = trim((string) ($row['schedule_time'] ?? ''));
        $scheduleIso = null;

        if ($schedule !== '') {
            try {
                $scheduleIso = Carbon::parse($schedule, $timezone)->toIso8601String();
            } catch (\Throwable) {
                $scheduleIso = null;
            }
        }

        return [
            'caption' => trim((string) ($row['caption'] ?? '')),
            'schedule_time' => $schedule,
            'schedule_time_iso' => $scheduleIso,
            'link' => trim((string) ($row['link'] ?? '')) ?: null,
            'media_url' => trim((string) ($row['media_url'] ?? '')) ?: null,
            'status_mode' => trim((string) ($row['status_mode'] ?? 'schedule')) ?: 'schedule',
            'timezone' => $timezone,
        ];
    }

    protected function validateRow(array $row, array $headerMap, $accounts, array $selectedAccountIds = []): array
    {
        $errors = [];

        foreach (['caption'] as $requiredHeader) {
            if (! array_key_exists($requiredHeader, $headerMap)) {
                $errors[] = "Missing required header: {$requiredHeader}.";
            }
        }

        if ($selectedAccountIds === [] && ! array_key_exists('account_id', $headerMap)) {
            $errors[] = 'At least one account must be selected in the form.';
        }

        if (($row['account_id'] ?? '') !== '' && ! $accounts->has((string) $row['account_id'])) {
            $errors[] = 'Account ID is not accessible in this workspace.';
        }

        if (($row['caption'] ?? '') === '') {
            $errors[] = 'Caption is required.';
        }

        if (($row['schedule_time'] ?? '') !== '' && ! $row['schedule_time_iso']) {
            $errors[] = 'Schedule time format is invalid.';
        }

        if (($row['link'] ?? null) && ! filter_var($row['link'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Link must be a valid URL.';
        }

        if (($row['media_url'] ?? null) && ! filter_var($row['media_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Media URL must be a valid URL.';
        }

        if (! in_array((string) ($row['status_mode'] ?? 'schedule'), ['draft', 'schedule'], true)) {
            $errors[] = 'Status mode must be draft or schedule.';
        }

        return $errors;
    }
}
