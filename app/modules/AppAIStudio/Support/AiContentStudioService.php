<?php

namespace Modules\AppAIStudio\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminUser\Models\User;
use Modules\AppCaptions\Models\CaptionLibraryItem;
use Modules\AppFiles\Models\AppFile;
use Modules\AppFiles\Support\FileManager;
use Modules\AppPublishing\Models\PublishingPost;
use Modules\AppAIStudio\Models\AIPromptHistory;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use RuntimeException;
use Throwable;

class AiContentStudioService
{
    public function __construct(
        protected OptionStore $options,
        protected FileManager $files,
    ) {}

    public function generateWorkspaceImage(User $user, string $prompt, string $style = 'product', string $ratio = '1:1'): array
    {
        if ((string) $this->options->get('ai_image_status', '1') !== '1') {
            throw new RuntimeException(__('AI image generation is currently disabled.'));
        }

        $team = TeamWorkspaceAccess::activeTeam($user);
        $creditUser = $team?->owner ?: $user;

        if ($creditUser->plan && ! $creditUser->hasPlanFeature('ai_studio_image')) {
            throw new RuntimeException(__('Your current plan does not allow AI image generation.'));
        }

        if (function_exists('credit_service')) {
            credit_service()->ensureCanConsume($creditUser, 'ai_studio_generate_image');
        }

        $provider = (string) $this->options->get('ai_image_provider', 'openai');
        $model = (string) $this->options->get('ai_image_model', 'gpt-image-1');
        $startedAt = microtime(true);
        $workspaceOwner = User::query()->findOrFail(TeamWorkspaceAccess::workspaceOwnerUserId($user));

        try {
            $image = match ($provider) {
                'openai' => $this->generateImageWithOpenAi($prompt, $style, $ratio, $model),
                'gemini' => $this->generateImageWithGemini($prompt, $style, $ratio, $model),
                default => throw new RuntimeException(__('The selected AI image provider is not supported.')),
            };

            $file = $this->files->storeBinaryFile(
                owner: $workspaceOwner,
                originalName: 'ai-studio-image-'.now()->format('YmdHis').'.'.$image['extension'],
                mimeType: $image['mime_type'],
                contents: $image['contents'],
                note: $image['prompt'],
            );

            if (function_exists('consume_credits')) {
                consume_credits($creditUser, 'ai_studio_generate_image', [
                    'feature' => 'ai-studio.image',
                    'metadata' => [
                        'provider' => $provider,
                        'model' => $model,
                        'style' => $style,
                        'ratio' => $ratio,
                        'file_id' => $file->id,
                    ],
                ]);
            }

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'image',
                    'model' => $model,
                    'feature' => 'ai-content-studio.image',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            }

            return [
                'file' => $file,
                'provider' => $provider,
                'model' => $model,
                'prompt' => $image['prompt'],
            ];
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'image',
                    'model' => $model,
                    'feature' => 'ai-content-studio.image',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                ]);
            }

            throw $exception;
        }
    }

    public function startWorkspaceVideoGeneration(User $user, string $prompt, string $duration = '8', string $format = 'vertical-short'): array
    {
        if ((string) $this->options->get('ai_video_status', '0') !== '1') {
            throw new RuntimeException(__('AI video generation is currently disabled.'));
        }

        $team = TeamWorkspaceAccess::activeTeam($user);
        $creditUser = $team?->owner ?: $user;

        if ($creditUser->plan && ! $creditUser->hasPlanFeature('ai_studio_video')) {
            throw new RuntimeException(__('Your current plan does not allow AI video generation.'));
        }

        $normalizedDuration = (string) $this->normalizeVideoDurationForUser($user, $duration);
        $creditCost = $this->videoCreditCostForUser($user, $normalizedDuration);

        if (function_exists('credit_service')) {
            credit_service()->ensureCanConsume($creditUser, 'ai_studio_generate_video', unitCost: $creditCost);
        }

        $provider = (string) $this->options->get('ai_video_provider', 'openai');
        $model = (string) $this->options->get('ai_video_model', 'sora-2');
        $startedAt = microtime(true);

        try {
            $job = match ($provider) {
                'openai' => $this->startOpenAiVideoGeneration($prompt, $normalizedDuration, $format, $model),
                default => throw new RuntimeException(__('The selected AI video provider is not supported yet.')),
            };

            if (function_exists('consume_credits')) {
                consume_credits($creditUser, 'ai_studio_generate_video', [
                    'unit_cost' => $creditCost,
                    'feature' => 'ai-studio.video',
                    'metadata' => [
                        'provider' => $provider,
                        'model' => $model,
                        'requested_duration' => $duration,
                        'duration' => $normalizedDuration,
                        'format' => $format,
                        'max_seconds' => $this->videoMaxSecondsForUser($user),
                        'video_id' => $job['id'] ?? null,
                    ],
                ]);
            }

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'video',
                    'model' => $model,
                    'feature' => 'ai-content-studio.video',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            }

            return $job;
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'video',
                    'model' => $model,
                    'feature' => 'ai-content-studio.video',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                ]);
            }

            throw $exception;
        }
    }

    public function refreshWorkspaceVideoGeneration(User $user, string $videoId, array $context = []): array
    {
        if ($videoId === '') {
            throw new RuntimeException(__('The video job identifier is missing.'));
        }

        $provider = (string) ($context['provider'] ?? $this->options->get('ai_video_provider', 'openai'));
        $model = (string) ($context['model'] ?? $this->options->get('ai_video_model', 'sora-2'));
        $startedAt = microtime(true);

        try {
            $job = match ($provider) {
                'openai' => $this->retrieveOpenAiVideoGeneration($videoId),
                default => throw new RuntimeException(__('The selected AI video provider is not supported yet.')),
            };

            if (($job['status'] ?? '') === 'completed') {
                $workspaceOwner = User::query()->findOrFail(TeamWorkspaceAccess::workspaceOwnerUserId($user));
                $content = match ($provider) {
                    'openai' => $this->downloadOpenAiVideoContent($videoId),
                    default => throw new RuntimeException(__('The selected AI video provider is not supported yet.')),
                };

                $file = $this->files->storeBinaryFile(
                    owner: $workspaceOwner,
                    originalName: 'ai-studio-video-'.now()->format('YmdHis').'.mp4',
                    mimeType: 'video/mp4',
                    contents: $content,
                    note: (string) ($context['prompt'] ?? ''),
                );

                $job['file'] = $file;
            }

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'video',
                    'model' => $model,
                    'feature' => 'ai-content-studio.video.refresh',
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            }

            return $job;
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'video',
                    'model' => $model,
                    'feature' => 'ai-content-studio.video.refresh',
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                ]);
            }

            throw $exception;
        }
    }

    public function recordPromptHistory(User $user, string $module, string $prompt, array $inputPayload = [], array $outputPayload = [], array $config = []): AIPromptHistory
    {
        return AIPromptHistory::query()->create([
            'owner_user_id' => TeamWorkspaceAccess::workspaceOwnerUserId($user),
            'requested_by_user_id' => (int) $user->id,
            'team_id' => TeamWorkspaceAccess::activeTeam($user)?->id,
            'module' => $module,
            'title' => trim((string) ($config['title'] ?? Str::limit($prompt, 110, ''))),
            'language' => trim((string) ($config['language'] ?? '')) ?: null,
            'tone' => trim((string) ($config['tone'] ?? '')) ?: null,
            'prompt' => $prompt,
            'input_payload' => $inputPayload !== [] ? $inputPayload : null,
            'output_payload' => $outputPayload !== [] ? $outputPayload : null,
            'metadata' => $config['metadata'] ?? null,
        ]);
    }

    public function availableVideoDurationsForUser(User $user): array
    {
        $maxSeconds = $this->videoMaxSecondsForUser($user);

        return collect([4, 8, 12])
            ->filter(fn (int $seconds): bool => $seconds <= $maxSeconds)
            ->values()
            ->all();
    }

    public function videoMaxSecondsForUser(User $user): int
    {
        $team = TeamWorkspaceAccess::activeTeam($user);
        $creditUser = $team?->owner ?: $user;

        if (! $creditUser->plan) {
            return 12;
        }

        $value = $creditUser->planLimit('ai_studio_video_max_seconds', 12);

        return max(0, is_numeric($value) ? (int) round((float) $value) : 12);
    }

    public function normalizeVideoDurationForUser(User $user, string|int $duration): int
    {
        $allowed = $this->availableVideoDurationsForUser($user);

        if ($allowed === []) {
            throw new RuntimeException(__('Your current plan does not allow AI video rendering for any supported duration.'));
        }

        $seconds = max(1, (int) $duration);

        if (in_array($seconds, $allowed, true)) {
            return $seconds;
        }

        return (int) collect($allowed)
            ->sortBy(fn (int $value): int => abs($value - $seconds))
            ->first();
    }

    public function videoCreditCostForUser(User $user, string|int $duration): int
    {
        $team = TeamWorkspaceAccess::activeTeam($user);
        $creditUser = $team?->owner ?: $user;
        $seconds = $this->normalizeVideoDurationForUser($user, $duration);

        $baseCost = function_exists('credit_service')
            ? credit_service()->costFor($creditUser, 'ai_studio_generate_video', 8)
            : 8;

        return max(0, (int) $baseCost) * (int) ($seconds / 4);
    }

    public function generatePlatformCaptions(string $brief, array $platforms = [], array $config = []): array
    {
        $platforms = $this->normalizePlatforms($platforms, ['facebook', 'instagram', 'linkedin', 'x', 'threads']);
        $template = trim((string) ($config['template'] ?? ''));
        $creativity = trim((string) ($config['creativity'] ?? 'economic')) ?: 'economic';
        $hashtagMode = trim((string) ($config['hashtag_mode'] ?? 'auto')) ?: 'auto';
        $approximateWords = max(40, min(320, (int) ($config['approximate_words'] ?? 100)));
        $totalResults = max(1, min(8, (int) ($config['total_results'] ?? max(1, count($platforms)))));

        try {
            $payload = $this->requestJson(
                feature: 'ai-content-studio.caption-generator',
                systemPrompt: 'You are a social media strategist. Return valid JSON only.',
                userPrompt: trim(implode("\n\n", [
                    'Create platform-native caption drafts for the provided brief.',
                    'Return JSON with keys: summary, variants.',
                    'variants must be an array of objects with keys: platform, caption, hook, hashtags, cta, notes.',
                    'Generate exactly '.$totalResults.' distinct caption variants in total.',
                    'If multiple platforms are requested, spread the variants across those platforms before repeating a platform.',
                    'Keep each caption ready to publish and adapted to the specific platform.',
                    'Aim for roughly '.$approximateWords.' words for each caption body, unless the platform naturally needs less.',
                    'Language: '.($config['language'] ?? 'en').'.',
                    'Tone: '.($config['tone'] ?? 'professional').'.',
                    'Creativity level: '.$creativity.'.',
                    'Hashtag mode: '.$hashtagMode.'. If mode is off, return an empty hashtags array.',
                    ...$this->promptPreferenceLines($config),
                    'Target platforms: '.implode(', ', $platforms).'.',
                    $template !== '' ? 'Prompt template: '.$template : null,
                    'Source brief: '.$brief,
                ])),
            );

            $items = collect((array) ($payload['variants'] ?? $payload['platforms'] ?? []))
                ->map(fn ($item) => [
                    'platform' => strtolower(trim((string) ($item['platform'] ?? ''))),
                    'caption' => trim((string) ($item['caption'] ?? '')),
                    'hook' => trim((string) ($item['hook'] ?? '')),
                    'hashtags' => $this->normalizeCaptionHashtags((array) ($item['hashtags'] ?? []), $hashtagMode),
                    'cta' => trim((string) ($item['cta'] ?? '')),
                    'notes' => trim((string) ($item['notes'] ?? '')),
                ])
                ->filter(fn ($item) => $item['platform'] !== '' && $item['caption'] !== '')
                ->values();

            if ($items->isNotEmpty()) {
                return [
                    'summary' => trim((string) ($payload['summary'] ?? '')),
                    'variants' => $items->values()->all(),
                    'source' => 'ai',
                ];
            }
        } catch (Throwable) {
        }

        return [
            'summary' => __('Generated from your brief with local platform rules and template guidance.'),
            'variants' => $this->fallbackCaptionVariants($brief, $platforms, $config, $totalResults),
            'source' => 'fallback',
        ];
    }

    public function repurposeContent(string $content, array $targets = [], array $config = []): array
    {
        $targets = $this->normalizePlatforms($targets, ['facebook', 'instagram', 'linkedin', 'x', 'threads', 'tiktok']);

        try {
            $payload = $this->requestJson(
                feature: 'ai-content-studio.repurpose',
                systemPrompt: 'You repurpose source content into multiple social formats. Return valid JSON only.',
                userPrompt: trim(implode("\n\n", [
                    'Rewrite the source content for the target platforms.',
                    'Return JSON with keys: strategy, items.',
                    'items must be an array of objects with keys: target, format, title, caption, notes.',
                    'Language: '.($config['language'] ?? 'en').'.',
                    'Tone: '.($config['tone'] ?? 'professional').'.',
                    ...$this->promptPreferenceLines($config),
                    'Targets: '.implode(', ', $targets).'.',
                    'Source content: '.$content,
                ])),
            );

            $items = collect((array) ($payload['items'] ?? []))
                ->map(fn ($item) => [
                    'target' => strtolower(trim((string) ($item['target'] ?? ''))),
                    'format' => trim((string) ($item['format'] ?? 'Post')),
                    'title' => trim((string) ($item['title'] ?? '')),
                    'caption' => trim((string) ($item['caption'] ?? '')),
                    'notes' => trim((string) ($item['notes'] ?? '')),
                ])
                ->filter(fn ($item) => $item['target'] !== '' && $item['caption'] !== '')
                ->values();

            if ($items->isNotEmpty()) {
                return [
                    'strategy' => trim((string) ($payload['strategy'] ?? '')),
                    'items' => $items->all(),
                    'source' => 'ai',
                ];
            }
        } catch (Throwable) {
        }

        return [
            'strategy' => __('Repurposed locally by shortening, reformatting, and adjusting the call to action for each network.'),
            'items' => collect($targets)->map(fn ($target) => $this->fallbackRepurposeItem($content, $target))->all(),
            'source' => 'fallback',
        ];
    }

    public function planCalendar(string $brief, array $config = []): array
    {
        $days = max(3, min(31, (int) ($config['days'] ?? 14)));

        try {
            $payload = $this->requestJson(
                feature: 'ai-content-studio.calendar-planner',
                systemPrompt: 'You are a senior social media strategist and content planner. Return valid JSON only.',
                userPrompt: trim(implode("\n\n", [
                    'Create an actionable social content calendar plan.',
                    'Return JSON with keys: overview, items.',
                    'items must be an array of objects with keys: date_offset, theme, objective, caption_brief, asset_brief, cta.',
                    'Keep the plan practical for scheduling.',
                    'Write the plan in this language: '.($config['language'] ?? 'en').'.',
                    'Each item must feel meaningfully different from the others.',
                    'Do not repeat the same theme wording, objective wording, CTA wording, or asset phrasing across items.',
                    'Make the caption_brief concrete and tied to the campaign brief, not generic labels like awareness or offer.',
                    'Avoid placeholders and generic copy such as "use a simple visual" or "invite the audience to take one next step".',
                    ...$this->promptPreferenceLines($config),
                    'Plan length in days: '.$days.'.',
                    'Campaign brief: '.$brief,
                ])),
            );

            $items = collect((array) ($payload['items'] ?? []))
                ->map(fn ($item) => [
                    'date_offset' => max(0, (int) ($item['date_offset'] ?? 0)),
                    'theme' => trim((string) ($item['theme'] ?? '')),
                    'objective' => trim((string) ($item['objective'] ?? '')),
                    'caption_brief' => trim((string) ($item['caption_brief'] ?? '')),
                    'asset_brief' => trim((string) ($item['asset_brief'] ?? '')),
                    'cta' => trim((string) ($item['cta'] ?? '')),
                ])
                ->filter(fn ($item) => $item['theme'] !== '' && $item['caption_brief'] !== '')
                ->values();

            if ($items->isNotEmpty()) {
                return [
                    'overview' => trim((string) ($payload['overview'] ?? '')),
                    'items' => $items->all(),
                    'source' => 'ai',
                ];
            }
        } catch (Throwable) {
        }

        return $this->fallbackCalendarPlan($brief, $days);
    }

    public function reviewDraft(string $content, array $platforms = [], array $config = []): array
    {
        $platforms = $this->normalizePlatforms($platforms, ['facebook']);
        $languageCode = strtolower(trim((string) ($config['language'] ?? app()->getLocale() ?? 'en')));
        $languageLabel = collect(world_languages())->firstWhere('code', $languageCode)['name'] ?? strtoupper($languageCode);

        try {
            $payload = $this->requestJson(
                feature: 'ai-content-studio.review',
                systemPrompt: 'You review social media drafts. Return valid JSON only.',
                userPrompt: trim(implode("\n\n", [
                    'Review the draft before it is published.',
                    'Return JSON with keys: score, verdict, strengths, risks, fixes, final_tip.',
                    'score must be between 0 and 100.',
                    'strengths, risks, and fixes must be arrays of short strings.',
                    'Write every field in this language: '.$languageLabel.' ('.$languageCode.').',
                    'Target platforms: '.implode(', ', $platforms).'.',
                    'Tone: '.($config['tone'] ?? 'professional').'.',
                    ...$this->promptPreferenceLines($config),
                    'Draft: '.$content,
                ])),
            );

            return [
                'score' => max(0, min(100, (int) ($payload['score'] ?? 0))),
                'verdict' => trim((string) ($payload['verdict'] ?? '')),
                'strengths' => collect((array) ($payload['strengths'] ?? []))->map(fn ($value) => trim((string) $value))->filter()->values()->all(),
                'risks' => collect((array) ($payload['risks'] ?? []))->map(fn ($value) => trim((string) $value))->filter()->values()->all(),
                'fixes' => collect((array) ($payload['fixes'] ?? []))->map(fn ($value) => trim((string) $value))->filter()->values()->all(),
                'final_tip' => trim((string) ($payload['final_tip'] ?? '')),
                'source' => 'ai',
            ];
        } catch (Throwable) {
            return $this->fallbackReview($content, $platforms);
        }
    }

    public function suggestTags(string $content, array $context = []): array
    {
        try {
            $payload = $this->requestJson(
                feature: 'ai-content-studio.tagging',
                systemPrompt: 'You generate short reusable content tags. Return valid JSON only.',
                userPrompt: trim(implode("\n\n", [
                    'Suggest concise tags for the content.',
                    'Return JSON with keys: tags, intent, category.',
                    'tags must be an array of 4 to 10 short lowercase tags.',
                    'Content: '.$content,
                    'Optional context: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ])),
            );

            $tags = collect((array) ($payload['tags'] ?? []))
                ->map(fn ($tag) => $this->normalizeTag((string) $tag))
                ->filter()
                ->unique()
                ->values();

            if ($tags->isNotEmpty()) {
                return [
                    'tags' => $tags->all(),
                    'intent' => trim((string) ($payload['intent'] ?? '')),
                    'category' => trim((string) ($payload['category'] ?? '')),
                    'source' => 'ai',
                ];
            }
        } catch (Throwable) {
        }

        return [
            'tags' => $this->fallbackTags($content, $context),
            'intent' => '',
            'category' => '',
            'source' => 'fallback',
        ];
    }

    public function semanticSearch(int $ownerUserId, string $query, int $limit = 10): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $queryTags = $this->fallbackTags($query);
        $queryTokens = $this->tokenize($query, $queryTags);

        $captions = CaptionLibraryItem::query()
            ->ownedBy($ownerUserId)
            ->orderByDesc('updated_at')
            ->limit(80)
            ->get()
            ->map(function (CaptionLibraryItem $caption) use ($query, $queryTokens, $queryTags) {
                $metadata = is_array($caption->metadata) ? $caption->metadata : [];
                $storedTags = collect((array) ($caption->tags ?? []))
                    ->merge((array) data_get($metadata, 'ai.tags', []))
                    ->map(fn ($tag) => $this->normalizeTag((string) $tag))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $haystack = implode(' ', array_filter([
                    $caption->name,
                    $caption->content,
                    $caption->notes,
                    implode(' ', $storedTags),
                ]));

                return [
                    'type' => 'caption',
                    'id' => (int) $caption->id,
                    'title' => (string) $caption->name,
                    'excerpt' => Str::limit(trim((string) $caption->content), 180),
                    'tags' => $storedTags,
                    'meta' => [
                        'status' => (string) $caption->status,
                        'source_type' => (string) $caption->source_type,
                        'updated_at' => optional($caption->updated_at)->diffForHumans(),
                    ],
                    'score' => $this->relevanceScore($query, $queryTokens, $queryTags, $haystack, $storedTags),
                ];
            });

        $posts = PublishingPost::query()
            ->ownedBy($ownerUserId)
            ->where('function', 'post')
            ->orderByDesc('id')
            ->limit(120)
            ->get()
            ->map(function (PublishingPost $post) use ($query, $queryTokens, $queryTags) {
                $data = is_array($post->data) ? $post->data : [];
                $aiTags = collect((array) data_get($data, 'ai.tags', []))
                    ->map(fn ($tag) => $this->normalizeTag((string) $tag))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $labelNames = collect((array) data_get($data, 'options.label_names', []))
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values()
                    ->all();
                $caption = trim((string) ($data['caption'] ?? ''));
                $haystack = implode(' ', array_filter([
                    $data['title'] ?? null,
                    $caption,
                    implode(' ', $labelNames),
                    implode(' ', $aiTags),
                    (string) $post->social_network,
                ]));

                return [
                    'type' => 'post',
                    'id' => (int) $post->id,
                    'title' => trim((string) ($data['title'] ?? Str::limit($caption, 70))),
                    'excerpt' => Str::limit($caption, 180),
                    'tags' => array_values(array_unique(array_filter(array_merge($labelNames, $aiTags)))),
                    'meta' => [
                        'network' => (string) ($post->social_network ?? ''),
                        'status' => (int) $post->status,
                        'scheduled_for' => $post->time_post ? Carbon::createFromTimestamp((int) $post->time_post)->toDateTimeString() : null,
                    ],
                    'score' => $this->relevanceScore($query, $queryTokens, $queryTags, $haystack, array_merge($labelNames, $aiTags)),
                ];
            });

        return $captions->merge($posts)
            ->where('score', '>', 0)
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();
    }

    public function bestTimeSuggestions(int $ownerUserId, array $accountIds = [], string $timezone = 'UTC', int $limit = 4): array
    {
        $posts = PublishingPost::query()
            ->ownedBy($ownerUserId)
            ->where('function', 'post')
            ->whereIn('status', [
                PublishingPost::STATUS_SCHEDULED,
                PublishingPost::STATUS_PUBLISHED,
            ])
            ->when($accountIds !== [], fn ($query) => $query->whereIn('account_id', $accountIds))
            ->whereNotNull('time_post')
            ->orderByDesc('id')
            ->limit(240)
            ->get();

        $history = $posts->map(function (PublishingPost $post) use ($timezone) {
            if (! $post->time_post) {
                return null;
            }

            $when = Carbon::createFromTimestamp((int) $post->time_post, $timezone);
            $score = match ((int) $post->status) {
                PublishingPost::STATUS_PUBLISHED => 3,
                PublishingPost::STATUS_SCHEDULED => 2,
                default => 1,
            };

            return [
                'day' => strtolower($when->format('D')),
                'hour' => (int) $when->format('H'),
                'slot' => $when->format('D H:00'),
                'score' => $score,
            ];
        })->filter();

        $ranked = $history->groupBy(fn ($item) => $item['day'].'-'.$item['hour'])
            ->map(function (Collection $bucket) {
                $first = $bucket->first();

                return [
                    'weekday' => $first['day'],
                    'hour' => $first['hour'],
                    'label' => strtoupper($first['day']).' '.str_pad((string) $first['hour'], 2, '0', STR_PAD_LEFT).':00',
                    'confidence' => min(98, 45 + ($bucket->sum('score') * 7)),
                    'reasons' => [
                        __('Based on :count recent scheduled or published posts in this window.', ['count' => $bucket->count()]),
                        __('Weighted toward posts that already reached the published state.'),
                    ],
                ];
            })
            ->sortByDesc('confidence')
            ->take($limit)
            ->values();

        if ($ranked->isNotEmpty()) {
            return $ranked->all();
        }

        return collect([
            ['weekday' => 'tue', 'hour' => 9],
            ['weekday' => 'wed', 'hour' => 11],
            ['weekday' => 'thu', 'hour' => 14],
            ['weekday' => 'fri', 'hour' => 10],
        ])->take($limit)->map(fn ($item) => [
            'weekday' => $item['weekday'],
            'hour' => $item['hour'],
            'label' => strtoupper($item['weekday']).' '.str_pad((string) $item['hour'], 2, '0', STR_PAD_LEFT).':00',
            'confidence' => 52,
            'reasons' => [
                __('No local posting history yet, so this slot uses a balanced default schedule.'),
            ],
        ])->all();
    }

    public function annotateCaption(CaptionLibraryItem $caption): CaptionLibraryItem
    {
        $tagging = $this->suggestTags($caption->name."\n".$caption->content, [
            'source_type' => $caption->source_type,
            'status' => $caption->status,
        ]);

        $existingMetadata = is_array($caption->metadata) ? $caption->metadata : [];
        $existingTags = collect((array) ($caption->tags ?? []))
            ->map(fn ($tag) => $this->normalizeTag((string) $tag))
            ->filter();

        $caption->update([
            'tags' => $existingTags->merge($tagging['tags'])->unique()->values()->all(),
            'metadata' => [
                ...$existingMetadata,
                'ai' => [
                    ...(is_array($existingMetadata['ai'] ?? null) ? $existingMetadata['ai'] : []),
                    'tags' => $tagging['tags'],
                    'intent' => $tagging['intent'],
                    'category' => $tagging['category'],
                    'tag_source' => $tagging['source'],
                    'tagged_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        return $caption->fresh();
    }

    public function rewriteSocialCaption(string $sourceCaption, array $context = [], array $config = []): array
    {
        $sourceCaption = trim($sourceCaption);

        if ($sourceCaption === '') {
            return [
                'caption' => '',
                'source' => 'empty',
            ];
        }

        try {
            $payload = $this->requestJson(
                feature: 'ai-content-studio.caption-rewrite',
                systemPrompt: 'You rewrite social captions. Return valid JSON only.',
                userPrompt: trim(implode("\n\n", array_filter([
                    'Rewrite the caption into one fresh social-ready version.',
                    'Return JSON with keys: caption, notes.',
                    'caption must stay accurate to the source facts and ready to publish.',
                    'Do not invent facts that are not present in the source.',
                    'Do not wrap the response in markdown.',
                    'Language: '.($config['language'] ?? 'en').'.',
                    'Tone: '.($config['tone'] ?? 'professional').'.',
                    ...$this->promptPreferenceLines($config),
                    filled($context['title'] ?? null) ? 'Title: '.trim((string) $context['title']) : null,
                    filled($context['summary'] ?? null) ? 'Summary: '.trim((string) $context['summary']) : null,
                    filled($context['content'] ?? null) ? 'Article excerpt: '.Str::limit(trim((string) $context['content']), 1200, '...') : null,
                    filled($context['link'] ?? null) ? 'Source link: '.trim((string) $context['link']) : null,
                    filled($config['instruction'] ?? null) ? 'Rewrite instruction: '.trim((string) $config['instruction']) : null,
                    'Source caption: '.$sourceCaption,
                ]))),
            );

            $caption = trim((string) ($payload['caption'] ?? ''));

            if ($caption !== '') {
                return [
                    'caption' => $caption,
                    'notes' => trim((string) ($payload['notes'] ?? '')),
                    'source' => 'ai',
                ];
            }
        } catch (Throwable) {
        }

        return [
            'caption' => $sourceCaption,
            'source' => 'fallback',
        ];
    }

    protected function requestJson(string $feature, string $systemPrompt, string $userPrompt): array
    {
        $provider = (string) $this->options->get('ai_content_provider', 'openai');
        $model = (string) $this->options->get('ai_content_model', 'gpt-5.4');
        $startedAt = microtime(true);

        try {
            $response = match ($provider) {
                'openai' => $this->requestOpenAi($systemPrompt, $userPrompt, $model),
                'gemini' => $this->requestGemini($systemPrompt, $userPrompt, $model),
                default => throw new RuntimeException(__('The selected AI provider is not supported.')),
            };

            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => $feature,
                    'status' => 'success',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                ]);
            }

            return $response;
        } catch (Throwable $exception) {
            if (function_exists('log_ai_usage')) {
                log_ai_usage([
                    'provider' => $provider,
                    'capability' => 'content',
                    'model' => $model,
                    'feature' => $feature,
                    'status' => 'error',
                    'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    'error_message' => $exception->getMessage(),
                ]);
            }

            throw $exception;
        }
    }

    protected function requestOpenAi(string $systemPrompt, string $userPrompt, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $response = Http::timeout(120)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI request failed.')));
        }

        $decoded = json_decode((string) data_get($response->json(), 'choices.0.message.content', ''), true);

        if (! is_array($decoded)) {
            throw new RuntimeException(__('AI returned invalid JSON.'));
        }

        return $decoded;
    }

    protected function requestGemini(string $systemPrompt, string $userPrompt, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_gemini_api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException(__('Gemini API key is missing.'));
        }

        $response = Http::timeout(120)
            ->acceptJson()
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [[
                        'text' => $systemPrompt."\n\n".$userPrompt,
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI request failed.')));
        }

        $text = collect((array) data_get($response->json(), 'candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode("\n");

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw new RuntimeException(__('AI returned invalid JSON.'));
        }

        return $decoded;
    }

    protected function generateImageWithOpenAi(string $prompt, string $style, string $ratio, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $payload = [
            'model' => $model,
            'prompt' => $this->imagePrompt($prompt, $style, $ratio),
            'size' => $this->openAiImageSize($ratio),
        ];

        if (str_starts_with($model, 'dall-e-')) {
            $payload['response_format'] = 'b64_json';
        }

        $response = Http::timeout(180)
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl.'/images/generations', $payload);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI image generation request failed.')));
        }

        $json = $response->json();
        $b64 = (string) data_get($json, 'data.0.b64_json', '');

        if ($b64 === '') {
            $b64 = (string) data_get($json, 'data.0.image_base64', '');
        }

        if ($b64 === '') {
            throw new RuntimeException(__('AI image generation returned no image data.'));
        }

        $contents = base64_decode($b64, true);

        if ($contents === false) {
            throw new RuntimeException(__('AI image generation returned invalid image data.'));
        }

        return [
            'contents' => $contents,
            'mime_type' => 'image/png',
            'extension' => 'png',
            'prompt' => $this->imagePrompt($prompt, $style, $ratio),
        ];
    }

    protected function generateImageWithGemini(string $prompt, string $style, string $ratio, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_gemini_api_key', ''));

        if ($apiKey === '') {
            throw new RuntimeException(__('Gemini API key is missing.'));
        }

        $response = Http::timeout(180)
            ->acceptJson()
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [[
                        'text' => $this->imagePrompt($prompt, $style, $ratio),
                    ]],
                ]],
                'generationConfig' => [
                    'responseModalities' => ['TEXT', 'IMAGE'],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI image generation request failed.')));
        }

        $parts = (array) data_get($response->json(), 'candidates.0.content.parts', []);
        $inlineData = collect($parts)->pluck('inlineData')->filter()->first();
        $b64 = (string) data_get($inlineData, 'data', '');
        $mimeType = (string) data_get($inlineData, 'mimeType', 'image/png');

        if ($b64 === '') {
            throw new RuntimeException(__('AI image generation returned no image data.'));
        }

        $contents = base64_decode($b64, true);

        if ($contents === false) {
            throw new RuntimeException(__('AI image generation returned invalid image data.'));
        }

        return [
            'contents' => $contents,
            'mime_type' => $mimeType,
            'extension' => str_contains($mimeType, 'webp') ? 'webp' : 'png',
            'prompt' => $this->imagePrompt($prompt, $style, $ratio),
        ];
    }

    protected function startOpenAiVideoGeneration(string $prompt, string $duration, string $format, string $model): array
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $response = Http::timeout(180)
            ->withToken($apiKey)
            ->acceptJson()
            ->asMultipart()
            ->post($baseUrl.'/videos', [
                [
                    'name' => 'prompt',
                    'contents' => $this->videoPrompt($prompt, $format),
                ],
                [
                    'name' => 'model',
                    'contents' => $model,
                ],
                [
                    'name' => 'size',
                    'contents' => $this->openAiVideoSize($format, $model),
                ],
                [
                    'name' => 'seconds',
                    'contents' => $this->openAiVideoSeconds($duration),
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('AI video generation request failed.')));
        }

        return $this->normalizeVideoJob((array) $response->json(), [
            'prompt' => $prompt,
            'duration' => $duration,
            'format' => $format,
            'provider' => 'openai',
            'model' => $model,
        ]);
    }

    protected function retrieveOpenAiVideoGeneration(string $videoId): array
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $response = Http::timeout(120)
            ->withToken($apiKey)
            ->acceptJson()
            ->get($baseUrl.'/videos/'.$videoId);

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('Could not refresh the video job status.')));
        }

        return $this->normalizeVideoJob((array) $response->json());
    }

    protected function downloadOpenAiVideoContent(string $videoId): string
    {
        $apiKey = trim((string) $this->options->get('ai_openai_api_key', ''));
        $baseUrl = rtrim((string) $this->options->get('ai_openai_url', 'https://api.openai.com/v1'), '/');

        if ($apiKey === '') {
            throw new RuntimeException(__('OpenAI API key is missing.'));
        }

        $response = Http::timeout(300)
            ->withToken($apiKey)
            ->get($baseUrl.'/videos/'.$videoId.'/content?variant=video');

        if (! $response->successful()) {
            throw new RuntimeException($this->responseErrorMessage($response, __('Could not download the rendered video.')));
        }

        $contents = $response->body();

        if ($contents === '') {
            throw new RuntimeException(__('The rendered video payload was empty.'));
        }

        return $contents;
    }

    protected function fallbackCaptionVariants(string $brief, array $platforms, array $config, int $totalResults): array
    {
        $platformCount = max(1, count($platforms));

        return collect(range(1, $totalResults))
            ->map(function (int $variantNumber) use ($brief, $platforms, $config, $platformCount): array {
                $platform = (string) ($platforms[($variantNumber - 1) % $platformCount] ?? $platforms[0] ?? 'facebook');

                return $this->fallbackCaptionForPlatform($brief, $platform, $config, $variantNumber);
            })
            ->values()
            ->all();
    }

    protected function fallbackCaptionForPlatform(string $brief, string $platform, array $config, int $variantNumber = 1): array
    {
        $tone = trim((string) ($config['tone'] ?? 'professional'));
        $hashtagMode = trim((string) ($config['hashtag_mode'] ?? 'auto')) ?: 'auto';
        $template = trim((string) ($config['template'] ?? ''));
        $approximateWords = max(40, min(320, (int) ($config['approximate_words'] ?? 100)));
        $cleanBrief = trim(preg_replace('/\s+/', ' ', strip_tags($brief)));
        $base = $template !== ''
            ? trim($template.' '.__('Use this context: :brief', ['brief' => $cleanBrief]))
            : $cleanBrief;

        $wordLimit = match ($platform) {
            'x', 'threads' => min(50, max(24, (int) round($approximateWords * 0.45))),
            'instagram' => min(90, max(40, (int) round($approximateWords * 0.8))),
            'linkedin' => min(120, max(55, (int) round($approximateWords * 1.05))),
            default => min(105, max(45, $approximateWords)),
        };

        $anglePool = [
            __('Lead with the strongest takeaway first, then make the next step obvious.'),
            __('Frame the message around one clear benefit and one concrete supporting detail.'),
            __('Turn the idea into a quick audience-facing insight with a natural CTA.'),
            __('Make the opening sharper, the value clearer, and the CTA easier to act on.'),
        ];
        $ctaPool = [
            __('Ask the audience to comment with their opinion or current situation.'),
            __('Invite the audience to save this for later.'),
            __('Prompt the audience to share this with someone who needs it.'),
            __('Ask the audience to reply with a keyword to continue the conversation.'),
        ];
        $hookPool = [
            __('The clearer the message, the easier it is to make people care fast.'),
            __('One strong angle can outperform a long explanation.'),
            __('If the audience understands the payoff immediately, engagement usually improves.'),
            __('The best captions make the value feel obvious in the first line.'),
        ];

        $captionBody = Str::words($base, $wordLimit, '...');
        $caption = trim(implode("\n\n", array_filter([
            $captionBody,
            $anglePool[($variantNumber - 1) % count($anglePool)],
        ])));

        if (in_array($platform, ['instagram', 'facebook', 'linkedin'], true)) {
            $caption = trim($caption."\n\n".$ctaPool[($variantNumber - 1) % count($ctaPool)]);
        }

        $tags = $this->fallbackTags($brief, ['platform' => $platform, 'template' => $template]);
        $hashtags = match ($hashtagMode) {
            'off' => [],
            'light' => array_slice($tags, 0, 2),
            'heavy' => array_slice($tags, 0, 6),
            default => array_slice($tags, 0, 4),
        };

        return [
            'platform' => $platform,
            'caption' => $caption,
            'hook' => Str::limit($hookPool[($variantNumber - 1) % count($hookPool)].' '.$cleanBrief, 110),
            'hashtags' => $hashtags,
            'cta' => $ctaPool[($variantNumber - 1) % count($ctaPool)],
            'notes' => __('Locally adapted for :platform in a :tone tone. Variation :number.', ['platform' => strtoupper($platform), 'tone' => $tone, 'number' => $variantNumber]),
        ];
    }

    protected function normalizeCaptionHashtags(array $hashtags, string $hashtagMode): array
    {
        if ($hashtagMode === 'off') {
            return [];
        }

        $normalized = collect($hashtags)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->values()
            ->all();

        return match ($hashtagMode) {
            'light' => array_slice($normalized, 0, 2),
            'heavy' => array_slice($normalized, 0, 6),
            default => array_slice($normalized, 0, 4),
        };
    }

    protected function fallbackRepurposeItem(string $content, string $target): array
    {
        $base = trim(preg_replace('/\s+/', ' ', strip_tags($content)));

        return [
            'target' => $target,
            'format' => match ($target) {
                'linkedin' => 'Insight post',
                'instagram' => 'Carousel caption',
                'tiktok' => 'Short video script',
                'x', 'threads' => 'Thread opener',
                default => 'Social post',
            },
            'title' => Str::limit($base, 55),
            'caption' => Str::limit($base, match ($target) {
                'x', 'threads' => 240,
                'instagram' => 320,
                default => 480,
            }),
            'notes' => __('Repurposed for :target with a shorter, platform-native structure.', ['target' => strtoupper($target)]),
        ];
    }

    protected function fallbackReview(string $content, array $platforms): array
    {
        $length = Str::length(trim($content));
        $score = 62;
        $strengths = [];
        $risks = [];
        $fixes = [];

        if ($length >= 60) {
            $score += 8;
            $strengths[] = __('The draft has enough substance to explain the core point.');
        } else {
            $risks[] = __('The message is short and may not deliver enough context.');
            $fixes[] = __('Add one specific benefit, proof point, or outcome.');
        }

        if (Str::contains($content, ['?', 'comment', 'reply', 'click', 'save', 'share'])) {
            $score += 8;
            $strengths[] = __('The draft already contains a participation or call-to-action cue.');
        } else {
            $risks[] = __('There is no explicit next step for the audience.');
            $fixes[] = __('Add a single CTA matched to the platform goal.');
        }

        if ($length > 500 && collect($platforms)->contains(fn ($platform) => in_array($platform, ['x', 'threads'], true))) {
            $risks[] = __('This is long for short-form platforms.');
            $fixes[] = __('Trim the opener and move detail into a thread or carousel.');
            $score -= 6;
        }

        if ($strengths === []) {
            $strengths[] = __('The message is clear enough to refine rather than rewrite from scratch.');
        }

        return [
            'score' => max(40, min(92, $score)),
            'verdict' => $score >= 75 ? __('Ready with minor polish') : __('Needs one more revision pass'),
            'strengths' => $strengths,
            'risks' => $risks === [] ? [__('No major platform risk detected from the local review pass.')] : $risks,
            'fixes' => $fixes === [] ? [__('Tighten the opening sentence and keep one clear CTA.')] : $fixes,
            'final_tip' => __('Lead with the strongest benefit in the first sentence.'),
            'source' => 'fallback',
        ];
    }

    protected function fallbackCalendarPlan(string $brief, int $days): array
    {
        $cleanBrief = trim(preg_replace('/\s+/', ' ', strip_tags($brief)));
        $topicSeeds = $this->plannerTopicSeeds($brief);
        $themePool = $this->plannerThemePool($brief, $topicSeeds);
        $objectivePool = $this->plannerObjectivePool($brief);
        $assetAngles = [
            __('Use a talking-head visual that anchors the main point with one clear on-screen message.'),
            __('Build a carousel-ready visual sequence with one takeaway per frame.'),
            __('Lead with a proof-driven visual using data, screenshots, or a concrete before/after reference.'),
            __('Use a comparison visual that makes the decision or contrast easy to grasp.'),
            __('Show a practical workflow, checklist, or process diagram the audience can save.'),
            __('Use a social-proof visual with a quote, testimonial, or customer outcome highlight.'),
            __('Create a simple explainer visual with one dominant headline and a supporting subpoint.'),
        ];
        $ctaPool = [
            __('Ask the audience to comment with their situation or opinion.'),
            __('Prompt the audience to save the post for later use.'),
            __('Invite the audience to share the post with a teammate or friend.'),
            __('Encourage the audience to click through for the full context or next step.'),
            __('Ask the audience to reply with a keyword to continue the conversation.'),
            __('Invite the audience to compare this idea against their current approach.'),
            __('Ask the audience which option, problem, or lesson fits them best.'),
        ];

        return [
            'overview' => __('Locally planned calendar built around the core campaign topic, rotating through proof, education, conversion, and conversation angles.'),
            'items' => collect(range(0, $days - 1))->map(function (int $offset) use ($themePool, $objectivePool, $topicSeeds, $assetAngles, $ctaPool, $cleanBrief) {
                $theme = $themePool[$offset % count($themePool)];
                $objective = $objectivePool[$offset % count($objectivePool)];
                $topic = $topicSeeds[$offset % count($topicSeeds)];
                $supportingTopic = $topicSeeds[($offset + 1) % count($topicSeeds)];

                return [
                    'date_offset' => $offset,
                    'theme' => $theme,
                    'objective' => $objective,
                    'caption_brief' => $this->plannerCaptionBrief($theme, $topic, $supportingTopic, $cleanBrief, $offset),
                    'asset_brief' => $this->plannerAssetBrief($theme, $topic, $assetAngles[$offset % count($assetAngles)]),
                    'cta' => $this->plannerCta($theme, $ctaPool[$offset % count($ctaPool)]),
                ];
            })->all(),
            'source' => 'fallback',
        ];
    }

    protected function plannerTopicSeeds(string $brief): array
    {
        $normalized = trim(preg_replace('/\s+/', ' ', strip_tags($brief)));

        $sentenceSeeds = collect(preg_split('/(?<=[.!?])\s+/u', $normalized) ?: [])
            ->map(fn ($sentence) => trim((string) $sentence))
            ->filter(fn ($sentence) => Str::length($sentence) >= 18)
            ->map(fn ($sentence) => Str::limit($sentence, 110, ''))
            ->take(5);

        $keywordSeeds = collect($this->fallbackTags($brief))
            ->map(fn ($tag) => str($tag)->replace('-', ' ')->title()->value())
            ->filter(fn ($tag) => Str::length($tag) >= 4)
            ->take(6);

        $seeds = $sentenceSeeds
            ->concat($keywordSeeds)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $seeds !== [] ? $seeds : [Str::limit($normalized, 110, '')];
    }

    protected function plannerThemePool(string $brief, array $topicSeeds): array
    {
        $normalized = strtolower($brief);
        $themes = [
            __('Key shift'),
            __('Audience pain point'),
            __('Proof and context'),
            __('What changes now'),
            __('Practical takeaway'),
            __('Common mistake'),
            __('What to do next'),
        ];

        if (str_contains($normalized, 'launch') || str_contains($normalized, 'release')) {
            $themes = [__('Launch angle'), __('Why it matters'), __('Use case spotlight'), __('Proof and response'), __('Early objection'), __('Action step')];
        } elseif (str_contains($normalized, 'sale') || str_contains($normalized, 'promo') || str_contains($normalized, 'discount')) {
            $themes = [__('Offer hook'), __('Urgency driver'), __('Buyer hesitation'), __('Proof and trust'), __('Best-fit audience'), __('Conversion push')];
        } elseif (str_contains($normalized, 'news') || str_contains($normalized, 'update') || str_contains($normalized, 'trend')) {
            $themes = [__('What happened'), __('Why it matters'), __('Immediate implication'), __('Expert lens'), __('What to watch next'), __('Audience reaction')];
        }

        $topicTheme = collect($topicSeeds)
            ->map(fn ($topic) => Str::limit($topic, 38, ''))
            ->take(2)
            ->all();

        return collect(array_merge($themes, $topicTheme))->filter()->unique()->values()->all();
    }

    protected function plannerObjectivePool(string $brief): array
    {
        $normalized = strtolower($brief);

        if (str_contains($normalized, 'sale') || str_contains($normalized, 'promo') || str_contains($normalized, 'discount')) {
            return [__('Clicks'), __('Replies'), __('Saves'), __('Conversions'), __('Comments')];
        }

        if (str_contains($normalized, 'community') || str_contains($normalized, 'engagement')) {
            return [__('Comments'), __('Replies'), __('Shares'), __('Saves'), __('Reach')];
        }

        if (str_contains($normalized, 'launch') || str_contains($normalized, 'release')) {
            return [__('Reach'), __('Saves'), __('Clicks'), __('Replies'), __('Shares')];
        }

        return [__('Reach'), __('Saves'), __('Comments'), __('Clicks'), __('Replies'), __('Shares')];
    }

    protected function plannerCaptionBrief(string $theme, string $topic, string $supportingTopic, string $brief, int $offset): string
    {
        $angles = [
            __('Connect :theme to :topic and explain why it matters right now.', ['theme' => strtolower($theme), 'topic' => $topic]),
            __('Use :topic as the lead, then support it with :supporting.', ['topic' => $topic, 'supporting' => $supportingTopic]),
            __('Start from the audience tension around :topic, then translate it into a practical takeaway.', ['topic' => $topic]),
            __('Use the brief context to make :theme feel specific, timely, and actionable.', ['theme' => strtolower($theme)]),
        ];

        return trim(__('Shape this as a clear social idea with one dominant message and one supporting point.').' '.$angles[$offset % count($angles)].' '.__('Anchor the wording to this campaign context: :brief', ['brief' => Str::limit($brief, 150)]));
    }

    protected function plannerAssetBrief(string $theme, string $topic, string $baseAngle): string
    {
        return trim($baseAngle.' '.__('Tie the visual to :topic and the angle ":theme".', ['topic' => $topic, 'theme' => $theme]).' '.__('Keep it direct, clear, and easy to grasp quickly.'));
    }

    protected function plannerCta(string $theme, string $baseCta): string
    {
        return trim($baseCta.' '.__('Align it with the ":theme" angle.', ['theme' => $theme]).' '.__('Keep the CTA simple, natural, and easy to act on.'));
    }

    protected function normalizePlatforms(array $platforms, array $fallback): array
    {
        $normalized = collect($platforms)
            ->map(fn ($platform) => strtolower(trim((string) $platform)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $normalized !== [] ? $normalized : $fallback;
    }

    protected function fallbackTags(string $content, array $context = []): array
    {
        $raw = strtolower(strip_tags($content.' '.implode(' ', array_map('strval', $context))));
        $raw = preg_replace('/[^a-z0-9\\s\\-]+/i', ' ', $raw) ?? '';

        $tokens = collect(preg_split('/\\s+/', $raw) ?: [])
            ->map(fn ($token) => trim((string) $token))
            ->filter(fn ($token) => Str::length($token) >= 4 && ! in_array($token, $this->stopwords(), true))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(8)
            ->map(fn ($tag) => $this->normalizeTag((string) $tag))
            ->filter()
            ->values()
            ->all();

        return $tokens !== [] ? $tokens : ['content', 'publishing'];
    }

    protected function normalizeTag(string $tag): string
    {
        $value = Str::of($tag)->ascii()->lower()->trim()->value();
        $value = preg_replace('/[^a-z0-9\\-\\s]+/i', '', $value) ?? '';
        $value = preg_replace('/\\s+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    protected function promptPreferenceLines(array $config): array
    {
        $lines = [];

        if (filled($config['brand_voice'] ?? null)) {
            $lines[] = 'Brand voice: '.trim((string) $config['brand_voice']).'.';
        }

        if (filled($config['preferred_cta_style'] ?? null)) {
            $lines[] = 'Preferred CTA style: '.trim((string) $config['preferred_cta_style']).'.';
        }

        if (filled($config['banned_words'] ?? null)) {
            $lines[] = 'Avoid these words or phrases: '.trim((string) $config['banned_words']).'.';
        }

        return $lines;
    }

    protected function tokenize(string $query, array $extraTags = []): array
    {
        $base = strtolower(strip_tags($query.' '.implode(' ', $extraTags)));
        $base = preg_replace('/[^a-z0-9\\s\\-]+/i', ' ', $base) ?? '';

        return collect(preg_split('/\\s+/', $base) ?: [])
            ->map(fn ($token) => trim((string) $token))
            ->filter(fn ($token) => Str::length($token) >= 2 && ! in_array($token, $this->stopwords(), true))
            ->flatMap(function (string $token) {
                $synonyms = [
                    'sale' => ['promo', 'offer', 'discount'],
                    'launch' => ['release', 'announce'],
                    'guide' => ['tips', 'howto'],
                    'engagement' => ['comment', 'reply', 'community'],
                    'product' => ['feature', 'benefit'],
                ];

                return array_unique(array_merge([$token], $synonyms[$token] ?? []));
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function relevanceScore(string $query, array $queryTokens, array $queryTags, string $haystack, array $tags = []): int
    {
        $score = 0;
        $normalizedHaystack = strtolower($haystack);
        $normalizedTags = collect($tags)->map(fn ($tag) => strtolower((string) $tag))->all();

        if (Str::contains($normalizedHaystack, strtolower($query))) {
            $score += 40;
        }

        foreach ($queryTokens as $token) {
            if (Str::contains($normalizedHaystack, $token)) {
                $score += 8;
            }
        }

        foreach ($queryTags as $tag) {
            if (in_array(strtolower($tag), $normalizedTags, true)) {
                $score += 12;
            }
        }

        return $score;
    }

    protected function stopwords(): array
    {
        return [
            'about', 'after', 'again', 'also', 'and', 'are', 'but', 'cho', 'cung', 'from',
            'have', 'into', 'just', 'make', 'more', 'that', 'than', 'them', 'then', 'there',
            'this', 'those', 'with', 'your', 'what', 'when', 'where', 'which', 'while', 'will',
            'the', 'for', 'can', 'you', 'toi', 'tren', 'duoc', 'mot', 'nhung', 'content',
        ];
    }

    protected function imagePrompt(string $prompt, string $style, string $ratio): string
    {
        $styleDirection = match ($style) {
            'lifestyle' => 'Use a polished lifestyle advertising look with natural context and human energy.',
            'editorial' => 'Use an editorial magazine visual language with clear hierarchy and premium composition.',
            'minimal' => 'Use a minimal clean composition with strong spacing and restrained details.',
            'cinematic' => 'Use cinematic lighting, depth, and mood with strong visual contrast.',
            default => 'Use a sharp commercial product-shot style with clear focal point and refined lighting.',
        };

        return trim(implode("\n\n", [
            'Create a polished marketing image for a social media workflow.',
            $styleDirection,
            'Respect this aspect ratio: '.$ratio.'.',
            'Do not add watermarks, UI chrome, collage frames, illegible text, or extra branding.',
            'If text appears, keep it minimal and highly legible.',
            'User brief: '.trim($prompt),
        ]));
    }

    protected function openAiImageSize(string $ratio): string
    {
        return match ($ratio) {
            '4:5' => '1024x1536',
            '16:9' => '1536x1024',
            '9:16' => '1024x1536',
            default => '1024x1024',
        };
    }

    protected function responseErrorMessage(Response $response, string $fallback): string
    {
        $message = trim((string) data_get($response->json(), 'error.message', ''));

        if ($message === '') {
            $message = trim((string) data_get($response->json(), 'message', ''));
        }

        if ($message === '') {
            $message = trim((string) $response->body());
        }

        if ($message === '') {
            return $fallback;
        }

        return Str::limit($message, 280);
    }

    protected function normalizeVideoJob(array $payload, array $context = []): array
    {
        $status = strtolower(trim((string) ($payload['status'] ?? 'queued')));
        $errorMessage = trim((string) data_get($payload, 'error.message', ''));

        return [
            'id' => (string) ($payload['id'] ?? ''),
            'status' => $status,
            'progress' => (int) round((float) ($payload['progress'] ?? 0)),
            'seconds' => (string) ($payload['seconds'] ?? ($context['duration'] ?? '')),
            'size' => (string) ($payload['size'] ?? $this->openAiVideoSize((string) ($context['format'] ?? 'vertical-short'), (string) ($context['model'] ?? 'sora-2'))),
            'prompt' => (string) ($context['prompt'] ?? ''),
            'duration' => (string) ($context['duration'] ?? ($payload['seconds'] ?? '')),
            'format' => (string) ($context['format'] ?? ''),
            'provider' => (string) ($context['provider'] ?? 'openai'),
            'model' => (string) ($context['model'] ?? ($payload['model'] ?? '')),
            'error_message' => $errorMessage,
        ];
    }

    protected function videoPrompt(string $prompt, string $format): string
    {
        $formatDirection = match ($format) {
            'landscape' => 'Compose for a cinematic landscape frame suitable for desktop or widescreen playback.',
            'square' => 'Compose for a balanced square frame with centered action and clean subject placement.',
            default => 'Compose for a vertical short-form video suitable for reels and story-style playback.',
        };

        return trim(implode("\n\n", [
            'Create a polished marketing video from the prompt below.',
            $formatDirection,
            'Keep motion readable, avoid watermarks, avoid illegible text, and preserve a clear visual subject.',
            'User brief: '.trim($prompt),
        ]));
    }

    protected function openAiVideoSize(string $format, string $model): string
    {
        $pro = $model === 'sora-2-pro';

        return match ($format) {
            'landscape' => $pro ? '1920x1080' : '1280x720',
            'square' => '720x720',
            default => $pro ? '1080x1920' : '720x1280',
        };
    }

    protected function openAiVideoSeconds(string $duration): string
    {
        $allowed = [4, 8, 12];
        $seconds = (int) $duration;

        if (in_array($seconds, $allowed, true)) {
            return (string) $seconds;
        }

        return (string) collect($allowed)
            ->sortBy(fn (int $value) => abs($value - $seconds))
            ->first();
    }
}
