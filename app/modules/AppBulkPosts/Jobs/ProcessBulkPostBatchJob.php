<?php

namespace Modules\AppBulkPosts\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;
use Modules\AppBulkPosts\Models\BulkPostBatch;
use Modules\AppBulkPosts\Models\BulkPostRow;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppPublishing\Models\PublishingPost;

class ProcessBulkPostBatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public int $batchId) {}

    public function handle(): void
    {
        $batch = BulkPostBatch::query()->with(['rows'])->find($this->batchId);

        if (! $batch) {
            return;
        }

        $stats = array_merge([
            'total_rows' => 0,
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'processed_rows' => 0,
            'created_posts' => 0,
            'failed_rows' => 0,
        ], (array) $batch->stats);

        $batch->update(['status' => 'processing']);
        $selectedAccountIds = collect((array) data_get($batch->meta, 'selected_account_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();
        $campaignId = filled(data_get($batch->meta, 'campaign_id'))
            ? (int) data_get($batch->meta, 'campaign_id')
            : null;
        $labelIds = collect((array) data_get($batch->meta, 'label_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
        $intervalMinutes = max(1, (int) data_get($batch->meta, 'interval_minutes', 60));
        $shortenUrls = (bool) data_get($batch->meta, 'shorten_urls', false);
        $fallbackScheduleAt = now();
        $owner = User::query()->find((int) $batch->owner_user_id);
        $publishableCapabilityKeys = publishable_channel_capability_keys($owner);

        $rows = BulkPostRow::query()
            ->where('batch_id', $batch->id)
            ->where('status', 'valid')
            ->orderBy('row_number')
            ->get();

        foreach ($rows as $row) {
            $payload = (array) $row->payload;
            $accountIds = $selectedAccountIds->isNotEmpty()
                ? $selectedAccountIds
                : collect([(int) ($payload['account_id'] ?? 0)])->filter(fn ($id) => $id > 0)->values();

            if ($accountIds->isEmpty()) {
                $row->update([
                    'status' => 'failed',
                    'validation_errors' => ['No account was selected for this row.'],
                    'processed_at' => now(),
                ]);
                $stats['failed_rows']++;
                $stats['processed_rows']++;
                continue;
            }

            try {
                if ($shortenUrls && $owner && url_shortening_enabled($owner)) {
                    if (filled($payload['link'] ?? null)) {
                        $payload['link'] = shorten_url((string) $payload['link']) ?: $payload['link'];
                    }

                    if (filled($payload['caption'] ?? null)) {
                        $payload['caption'] = shorten_urls_in_content((string) $payload['caption']);
                    }
                }

                $scheduleAt = filled($payload['schedule_time_iso'] ?? null)
                    ? Carbon::parse((string) $payload['schedule_time_iso'])
                    : $fallbackScheduleAt->copy();
                $statusMode = (string) ($payload['status_mode'] ?? 'schedule');

                $createdPosts = 0;

                foreach ($accountIds as $accountId) {
                    $account = SocialAccount::query()
                        ->where('created_by_user_id', (int) data_get($batch->meta, 'workspace_owner_user_id', $batch->owner_user_id))
                        ->where(function ($query) use ($publishableCapabilityKeys): void {
                            $query->whereIn('capability_key', $publishableCapabilityKeys)
                                ->orWhere(function ($fallbackQuery) use ($publishableCapabilityKeys): void {
                                    $fallbackQuery->whereNull('capability_key')
                                        ->whereIn('provider_key', $publishableCapabilityKeys);
                                });
                        })
                        ->find((int) $accountId);

                    if (! $account) {
                        continue;
                    }

                    $post = PublishingPost::query()->create([
                        'id_secure' => Str::random(32),
                        'user_id' => $batch->owner_user_id,
                        'team_id' => $batch->team_id,
                        'campaign' => $campaignId,
                        'labels' => $labelIds,
                        'account_id' => $account->id,
                        'social_network' => (string) $account->provider_key,
                        'category' => (string) ($account->capability_key ?: $account->category ?: $account->provider_key),
                        'module' => (string) $account->provider_key,
                        'function' => 'post',
                        'api_type' => 1,
                        'type' => filled($payload['media_url'] ?? null) ? 'media' : 'text',
                        'method' => 'basic',
                        'query_id' => null,
                        'data' => [
                            'title' => trim((string) Str::limit((string) ($payload['caption'] ?? ''), 46, '...')),
                            'caption' => trim((string) ($payload['caption'] ?? '')),
                            'media_type' => filled($payload['media_url'] ?? null) ? 'image' : 'text',
                            'medias' => [],
                            'source_media_urls' => array_values(array_filter([(string) ($payload['media_url'] ?? '')])),
                            'options' => [
                                'schedule_mode' => 'specific_days_times',
                                'schedule_slot_index' => 0,
                                'timezone' => (string) ($payload['timezone'] ?? data_get($batch->meta, 'timezone', config('app.timezone'))),
                                'campaign_id' => $campaignId,
                                'label_ids' => $labelIds,
                                'link' => $payload['link'] ?? null,
                                'bulk_batch_id' => $batch->id,
                                'bulk_row_id' => $row->id,
                            ],
                            'link' => $payload['link'] ?? null,
                        ],
                        'time_post' => $scheduleAt->timestamp,
                        'delay' => 0,
                        'repost_frequency' => 0,
                        'repost_until' => null,
                        'result' => null,
                        'tmp' => null,
                        'custom_data_1' => (string) $batch->id,
                        'custom_data_2' => (string) $row->id,
                        'custom_data_3' => 'bulk-posts',
                        'status' => $statusMode === 'draft'
                            ? PublishingPost::STATUS_DRAFT
                            : PublishingPost::STATUS_SCHEDULED,
                        'changed' => now()->timestamp,
                        'created' => now()->timestamp,
                    ]);

                    $row->publishing_post_id = $post->id;
                    $createdPosts++;
                    $stats['created_posts']++;
                }

                if ($createdPosts === 0) {
                    throw new \RuntimeException('No selected account is available in this workspace.');
                }

                $row->update([
                    'status' => 'imported',
                    'publishing_post_id' => $row->publishing_post_id,
                    'processed_at' => now(),
                ]);

                if (! filled($payload['schedule_time_iso'] ?? null)) {
                    $fallbackScheduleAt = $scheduleAt->copy()->addMinutes($intervalMinutes);
                }
            } catch (\Throwable $exception) {
                $row->update([
                    'status' => 'failed',
                    'validation_errors' => [$exception->getMessage()],
                    'processed_at' => now(),
                ]);
                $stats['failed_rows']++;
            }

            $stats['processed_rows']++;
        }

        $batch->update([
            'status' => $stats['created_posts'] > 0 ? 'completed' : 'failed',
            'stats' => $stats,
            'last_processed_at' => now(),
        ]);
    }
}
