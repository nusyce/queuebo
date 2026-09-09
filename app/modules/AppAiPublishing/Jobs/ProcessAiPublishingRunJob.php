<?php

namespace Modules\AppAiPublishing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AppAiPublishing\Models\AiPublishingPrompt;
use Modules\AppAiPublishing\Models\AiPublishingRun;
use Modules\AppAiPublishing\Support\AiPublishingScheduler;
use Modules\AppAiPublishing\Support\AiPublishingTextGenerator;
use Modules\AppAIStudio\Support\AiContentStudioService;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppFiles\Models\AppFile;
use Modules\AppFiles\Support\FileManager;
use Modules\AppFiles\Support\OnlineMediaSearchService;
use Modules\AppPublishing\Jobs\PublishScheduledPostJob;
use Modules\AppPublishing\Models\PublishingPost;
use Carbon\Carbon;

class ProcessAiPublishingRunJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(public int $runId, public bool $publishImmediately = false) {}

    public function handle(
        AiPublishingTextGenerator $textGenerator,
        AiPublishingScheduler $scheduler,
        AiContentStudioService $studio,
        OnlineMediaSearchService $onlineMediaSearch,
        FileManager $files
    ): void
    {
        $run = AiPublishingRun::query()->with('owner')->find($this->runId);

        if (! $run) {
            return;
        }

        if ((string) $run->status === 'paused') {
            return;
        }

        $run->update(['status' => 'processing']);

        try {
            $publishableCapabilityKeys = publishable_channel_capability_keys($run->owner);
            $creditUser = User::query()->find($run->workspace_owner_user_id ?: $run->owner_user_id);

            $accounts = SocialAccount::query()
                ->where('created_by_user_id', (int) $run->workspace_owner_user_id)
                ->where(function ($query) use ($publishableCapabilityKeys): void {
                    $query->whereIn('capability_key', $publishableCapabilityKeys)
                        ->orWhere(function ($fallbackQuery) use ($publishableCapabilityKeys): void {
                            $fallbackQuery->whereNull('capability_key')
                                ->whereIn('provider_key', $publishableCapabilityKeys);
                        });
                })
                ->whereIn('id', collect((array) $run->account_ids)->map(fn ($id) => (int) $id)->filter()->all())
                ->get()
                ->keyBy('id');

            $resolvedTeamId = (int) ($run->team_id ?? 0);

            if ($resolvedTeamId <= 0) {
                $resolvedTeamId = (int) Team::query()
                    ->where('owner_user_id', (int) ($run->workspace_owner_user_id ?: $run->owner_user_id))
                    ->value('id');

                if ($resolvedTeamId > 0) {
                    $run->update(['team_id' => $resolvedTeamId]);
                    $run->refresh();
                }
            }

            $promptIds = collect((array) $run->prompt_ids)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->values();
            $prompts = AiPublishingPrompt::query()
                ->whereIn('id', $promptIds->all())
                ->get()
                ->keyBy('id');
            $slots = $scheduler->buildSlots((array) $run->schedule_config, $promptIds->count(), (string) data_get($run->schedule_config, 'timezone', config('app.timezone')));
            $existingLogs = collect((array) data_get($run->stats, 'run_logs', []))
                ->filter(fn ($entry) => is_array($entry))
                ->values()
                ->all();
            $stats = [
                'total_items' => $promptIds->count(),
                'generated_items' => 0,
                'failed_items' => 0,
                'created_posts' => 0,
                'failed_prompt_ids' => [],
                'failed_prompts' => [],
                'last_error_message' => null,
                'run_logs' => $existingLogs,
            ];

            $this->pushRunLog($stats, [
                'level' => 'info',
                'stage' => 'run_started',
                'message' => $this->publishImmediately
                    ? 'Immediate AI publishing run started.'
                    : 'Scheduled AI publishing run started.',
                'run_id' => $run->id,
            ]);

            foreach ($promptIds as $index => $promptId) {
                $run->refresh();

                if ((string) $run->status === 'paused') {
                    $this->pushRunLog($stats, [
                        'level' => 'warning',
                        'stage' => 'run_paused',
                        'message' => 'The AI publishing run was paused before all prompts were processed.',
                        'run_id' => $run->id,
                    ]);
                    break;
                }

                try {
                    $promptText = trim((string) $prompts->get($promptId)?->prompt_text);

                    if ($promptText === '') {
                        throw new \RuntimeException('The selected AI publishing prompt no longer exists.');
                    }

                    credit_service()->ensureCanConsume($creditUser, 'ai_publishing_generate_content');
                    $generation = $textGenerator->generate($promptText, (array) $run->generation_config);
                    consume_credits($creditUser, 'ai_publishing_generate_content', [
                        'feature' => 'ai-publishing.generate',
                        'metadata' => [
                            'run_id' => $run->id,
                            'prompt_id' => $promptId,
                            'mode' => data_get($run->generation_config, 'mode'),
                        ],
                    ]);
                    $caption = (string) ($generation['caption'] ?? '');
                    $content = (string) ($generation['content'] ?? $caption);
                    $imagePrompt = (string) ($generation['image_prompt'] ?? $caption);
                    $this->pushRunLog($stats, [
                        'level' => 'info',
                        'stage' => 'content_generated',
                        'message' => 'AI content generated successfully.',
                        'run_id' => $run->id,
                        'prompt_id' => $promptId,
                        'prompt' => Str::limit($promptText, 120),
                    ]);

                    $mediaItems = [];
                    $includeMedia = (string) data_get(
                        $run->generation_config,
                        'include_media',
                        (string) data_get($run->generation_config, 'mode', 'caption_only') === 'caption_content_image'
                            ? 'ai_image'
                            : 'disable'
                    );
                    if ($includeMedia === 'ai_image') {
                        $owner = $run->owner;
                        if ($owner) {
                            $generatedImage = $studio->generateWorkspaceImage(
                                user: $owner,
                                prompt: $imagePrompt,
                            );
                            $imageFile = $generatedImage['file'];
                            $mediaItems[] = $this->serializeMediaFile($imageFile);
                            $this->pushRunLog($stats, [
                                'level' => 'info',
                                'stage' => 'image_generated',
                                'message' => 'AI image generated successfully.',
                                'run_id' => $run->id,
                                'prompt_id' => $promptId,
                                'file_id' => $imageFile->id,
                            ]);
                        }
                    } elseif ($includeMedia !== 'disable' && $run->owner) {
                        $resolvedMediaFile = $this->resolveExternalMediaFile(
                            owner: $run->owner,
                            includeMedia: $includeMedia,
                            imagePrompt: $imagePrompt,
                            caption: $caption,
                            content: $content,
                            search: $onlineMediaSearch,
                            files: $files,
                        );

                        if ($resolvedMediaFile) {
                            $mediaItems[] = $this->serializeMediaFile($resolvedMediaFile);
                            $this->pushRunLog($stats, [
                                'level' => 'info',
                                'stage' => 'image_generated',
                                'message' => 'External media attached successfully.',
                                'run_id' => $run->id,
                                'prompt_id' => $promptId,
                                'file_id' => $resolvedMediaFile->id,
                                'media_source' => $includeMedia,
                            ]);
                        } else {
                            $this->pushRunLog($stats, [
                                'level' => 'warning',
                                'stage' => 'image_generated',
                                'message' => 'No media result found for the selected source. The post was created without media.',
                                'run_id' => $run->id,
                                'prompt_id' => $promptId,
                                'media_source' => $includeMedia,
                            ]);
                        }
                    }

                    $timezone = (string) data_get($run->schedule_config, 'timezone', config('app.timezone'));
                    $scheduledAt = $this->publishImmediately
                        ? now($timezone)
                        : (
                            $scheduler->nextDueSlot(
                                (array) $run->schedule_config,
                                $timezone,
                                $run->last_processed_at ? Carbon::parse($run->last_processed_at)->timezone($timezone) : null
                            )
                            ?? ($slots[$index] ?? now($timezone))
                        );

                    foreach ($accounts as $account) {
                        $post = PublishingPost::query()->create([
                            'id_secure' => Str::random(32),
                            'user_id' => $run->owner_user_id,
                            'team_id' => $resolvedTeamId > 0 ? $resolvedTeamId : null,
                            'campaign' => $run->campaign_id,
                            'labels' => collect((array) $run->label_ids)->map(fn ($id) => (int) $id)->filter()->values()->all(),
                            'account_id' => $account->id,
                            'social_network' => (string) $account->provider_key,
                            'category' => (string) ($account->capability_key ?: $account->category ?: $account->provider_key),
                            'module' => (string) $account->provider_key,
                            'function' => 'post',
                            'api_type' => 1,
                            'type' => $mediaItems !== [] ? 'media' : 'text',
                            'method' => 'basic',
                            'query_id' => null,
                            'data' => [
                                'title' => trim((string) Str::limit($caption !== '' ? $caption : $content, 46, '...')),
                                'caption' => trim($caption !== '' ? $caption : Str::limit($content, 2200, '...')),
                                'content' => $content,
                                'media_type' => $mediaItems !== []
                                    ? ((string) data_get($mediaItems, '0.category', 'image') === 'video' ? 'video' : 'image')
                                    : 'text',
                                'medias' => $mediaItems,
                                'options' => [
                                    'schedule_mode' => 'specific_days_times',
                                    'timezone' => $timezone,
                                    'campaign_id' => $run->campaign_id,
                                    'label_ids' => collect((array) $run->label_ids)->map(fn ($id) => (int) $id)->filter()->values()->all(),
                                    'ai_publishing_run_id' => $run->id,
                                    'ai_publishing_prompt_id' => $promptId,
                                    'image_prompt' => $imagePrompt,
                                ],
                            ],
                            'time_post' => $scheduledAt->timestamp,
                            'delay' => 0,
                            'repost_frequency' => 0,
                            'repost_until' => null,
                            'result' => null,
                            'tmp' => null,
                            'custom_data_1' => (string) $run->id,
                            'custom_data_2' => (string) $promptId,
                            'custom_data_3' => 'ai-publishing',
                            'status' => PublishingPost::STATUS_SCHEDULED,
                            'changed' => now()->timestamp,
                            'created' => now()->timestamp,
                        ]);

                        $stats['created_posts']++;
                        $this->pushRunLog($stats, [
                            'level' => 'info',
                            'stage' => 'post_created',
                            'message' => $this->publishImmediately
                                ? 'Publishing post created for immediate delivery.'
                                : 'Publishing post created successfully.',
                            'run_id' => $run->id,
                            'prompt_id' => $promptId,
                            'account_id' => $account->id,
                            'account_label' => (string) ($account->display_name ?: $account->username ?: $account->provider_key),
                            'post_id' => $post->id,
                            'scheduled_at' => date('c', (int) $scheduledAt->timestamp),
                        ]);

                        if ($this->publishImmediately) {
                            app()->call([
                                new PublishScheduledPostJob((int) $post->id),
                                'handle',
                            ]);
                            $post->refresh();
                            $publishState = (string) data_get($post->result, 'state', '');
                            $publishError = trim((string) data_get($post->result, 'error', ''));
                            $this->pushRunLog($stats, [
                                'level' => $publishState === 'published' ? 'success' : 'error',
                                'stage' => 'post_published',
                                'message' => $publishState === 'published'
                                    ? 'Post published successfully.'
                                    : ($publishError !== '' ? $publishError : 'Immediate publishing failed.'),
                                'run_id' => $run->id,
                                'prompt_id' => $promptId,
                                'account_id' => $account->id,
                                'account_label' => (string) ($account->display_name ?: $account->username ?: $account->provider_key),
                                'post_id' => $post->id,
                                'publish_state' => $publishState !== '' ? $publishState : 'failed',
                            ]);
                        }
                    }

                    $stats['generated_items']++;
                } catch (\Throwable $exception) {
                    $stats['failed_items']++;
                    $stats['failed_prompt_ids'][] = $promptId;
                    $stats['failed_prompts'][] = [
                        'prompt_id' => $promptId,
                        'prompt' => Str::limit(trim((string) $prompts->get($promptId)?->prompt_text), 120),
                        'message' => Str::limit(trim($exception->getMessage()), 500),
                    ];
                    $stats['last_error_message'] = trim($exception->getMessage());
                    $this->pushRunLog($stats, [
                        'level' => 'error',
                        'stage' => 'prompt_failed',
                        'message' => trim($exception->getMessage()),
                        'run_id' => $run->id,
                        'prompt_id' => $promptId,
                        'prompt' => Str::limit(trim((string) $prompts->get($promptId)?->prompt_text), 120),
                    ]);
                }
            }

            $run->refresh();

            $run->update([
                'status' => ((string) $run->status === 'paused')
                    ? 'paused'
                    : ($this->hasScheduleEnded($run) ? 'completed' : ($stats['generated_items'] > 0 ? 'queued' : 'failed')),
                'stats' => $stats,
                'last_processed_at' => now(),
            ]);

            if ((int) $stats['created_posts'] > 0) {
                $this->bumpPublishingCalendarCache((int) ($run->workspace_owner_user_id ?: $run->owner_user_id), max(0, $resolvedTeamId));
            }
        } catch (\Throwable $exception) {
            $stats = array_merge((array) ($run->stats ?? []), [
                'last_error_message' => trim($exception->getMessage()),
            ]);
            $this->pushRunLog($stats, [
                'level' => 'error',
                'stage' => 'run_failed',
                'message' => trim($exception->getMessage()),
                'run_id' => $run->id,
            ]);

            $run->update([
                'status' => 'failed',
                'stats' => $stats,
                'last_processed_at' => now(),
            ]);

            throw $exception;
        }
    }

    protected function serializeMediaFile(AppFile $file): array
    {
        $previewUrl = route('portal.files.preview', $file);

        return [
            'id' => $file->id,
            'idSecure' => $file->id_secure,
            'name' => $file->name,
            'url' => $previewUrl,
            'previewUrl' => $previewUrl,
            'embedUrl' => $previewUrl,
            'size' => $file->humanSize(),
            'category' => $file->category,
            'mimeType' => $file->mime_type,
            'extension' => $file->extension,
            'isImage' => (bool) $file->is_image,
        ];
    }

    protected function resolveExternalMediaFile(
        User $owner,
        string $includeMedia,
        string $imagePrompt,
        string $caption,
        string $content,
        OnlineMediaSearchService $search,
        FileManager $files
    ): ?AppFile {
        if (str_starts_with($includeMedia, 'folder:')) {
            $folderId = (int) Str::after($includeMedia, 'folder:');

            if ($folderId <= 0) {
                return null;
            }

            return AppFile::query()
                ->ownedBy($owner)
                ->where('parent_id', $folderId)
                ->where('is_folder', false)
                ->where('is_image', true)
                ->inRandomOrder()
                ->first();
        }

        $provider = trim($includeMedia);

        if ($provider === '') {
            return null;
        }

        foreach ($this->buildSearchKeywords($imagePrompt, $caption, $content) as $keyword) {
            $results = $search->search($keyword, $provider, 'image');
            $first = collect($results)->first(fn ($item) => filled(data_get($item, 'full')));

            if (! is_array($first)) {
                continue;
            }

            return $files->storeRemoteFile($owner, (string) $first['full']);
        }

        return null;
    }

    protected function buildSearchKeywords(string $imagePrompt, string $caption, string $content): array
    {
        $base = trim($imagePrompt !== '' ? $imagePrompt : ($caption !== '' ? $caption : $content));
        $normalized = Str::of($base)
            ->lower()
            ->replaceMatches('/[^\pL\pN\s]+/u', ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->value();

        $stopWords = [
            'the', 'and', 'for', 'with', 'that', 'this', 'from', 'into', 'your',
            'about', 'have', 'will', 'would', 'could', 'should', 'after', 'before',
            'trong', 'nhung', 'những', 'các', 'cua', 'của', 'voi', 'với', 'cho', 'mot', 'một',
            'ngay', 'ngày', 'sau', 'tren', 'trên', 'giua', 'giữa', 'dua', 'đưa',
        ];

        $tokens = collect(explode(' ', $normalized))
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => $token !== '' && mb_strlen($token) >= 3 && ! in_array($token, $stopWords, true))
            ->unique()
            ->values();

        $candidates = collect();

        if ($normalized !== '') {
            $candidates->push(Str::limit($normalized, 80, ''));
        }

        foreach ([4, 3, 2, 1] as $size) {
            $slice = $tokens->take($size)->implode(' ');

            if ($slice !== '') {
                $candidates->push($slice);
            }
        }

        return $candidates
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function bumpPublishingCalendarCache(int $ownerUserId, int $teamId): void
    {
        $cacheKey = 'publishing:calendar-version:v1:'.$ownerUserId.':'.$teamId;
        Cache::forever($cacheKey, (int) Cache::get($cacheKey, 1) + 1);
    }

    protected function hasScheduleEnded(AiPublishingRun $run): bool
    {
        $timezone = (string) data_get($run->schedule_config, 'timezone', config('app.timezone'));
        $endAt = (string) data_get($run->schedule_config, 'end_at', '');

        if ($endAt === '') {
            return false;
        }

        try {
            return now($timezone)->greaterThan(Carbon::parse($endAt, $timezone)->endOfDay());
        } catch (\Throwable) {
            return false;
        }
    }

    protected function pushRunLog(array &$stats, array $entry): void
    {
        $logs = collect((array) ($stats['run_logs'] ?? []))
            ->filter(fn ($item) => is_array($item))
            ->prepend(array_merge([
                'logged_at' => now()->toIso8601String(),
                'level' => 'info',
                'stage' => 'info',
                'message' => '',
            ], $entry))
            ->take(30)
            ->values()
            ->all();

        $stats['run_logs'] = $logs;
    }
}
