<?php

namespace Modules\AppAutomation\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\AppAutomation\Models\AutomationLog;
use Modules\AppAutomation\Models\AutomationWebhook;
use Modules\AppPublishing\Models\PublishingPost;

class AutomationWebhookDispatcher
{
    public function dispatchForPost(string $event, PublishingPost $post): void
    {
        $post->loadMissing('account');

        $webhooks = AutomationWebhook::query()
            ->where('user_id', (int) $post->user_id)
            ->where('is_active', true)
            ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        $payload = $this->payloadForPost($event, $post);

        foreach ($webhooks as $webhook) {
            if (! $this->supportsEvent($webhook, $event)) {
                continue;
            }

            $timestamp = (string) now()->timestamp;
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, (string) $webhook->signing_secret);
            $requestId = (string) Str::uuid();

            try {
                $response = Http::timeout(30)
                    ->acceptJson()
                    ->withHeaders([
                        'X-Stackposts-Event' => $event,
                        'X-Stackposts-Request-Id' => $requestId,
                        'X-Stackposts-Timestamp' => $timestamp,
                        'X-Stackposts-Signature' => $signature,
                    ])
                    ->post((string) $webhook->url, $payload);

                AutomationLog::query()->create([
                    'user_id' => $webhook->user_id,
                    'team_id' => $webhook->team_id,
                    'api_key_id' => null,
                    'webhook_id' => $webhook->id,
                    'direction' => 'outbound',
                    'event' => $event,
                    'request_id' => $requestId,
                    'status' => $response->successful() ? 'delivered' : 'failed',
                    'status_code' => $response->status(),
                    'payload' => $payload,
                    'response' => $response->json() ?: ['body' => $response->body()],
                ]);

                $webhook->forceFill(['last_sent_at' => now()])->save();
            } catch (\Throwable $exception) {
                AutomationLog::query()->create([
                    'user_id' => $webhook->user_id,
                    'team_id' => $webhook->team_id,
                    'api_key_id' => null,
                    'webhook_id' => $webhook->id,
                    'direction' => 'outbound',
                    'event' => $event,
                    'request_id' => $requestId,
                    'status' => 'failed',
                    'status_code' => null,
                    'payload' => $payload,
                    'response' => ['error' => $exception->getMessage()],
                ]);
            }
        }
    }

    protected function supportsEvent(AutomationWebhook $webhook, string $event): bool
    {
        $events = collect((array) $webhook->events)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        return $events === [] || in_array($event, $events, true);
    }

    protected function payloadForPost(string $event, PublishingPost $post): array
    {
        $postData = is_array($post->data) ? $post->data : [];
        $result = is_array($post->result) ? $post->result : [];

        return [
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'post' => [
                'id' => (int) $post->id,
                'id_secure' => (string) ($post->id_secure ?? ''),
                'status' => (int) $post->status,
                'status_key' => match ((int) $post->status) {
                    PublishingPost::STATUS_DRAFT => 'draft',
                    PublishingPost::STATUS_PROCESSING => 'processing',
                    PublishingPost::STATUS_SCHEDULED => 'scheduled',
                    PublishingPost::STATUS_PUBLISHED => 'published',
                    PublishingPost::STATUS_FAILED => 'failed',
                    default => 'unknown',
                },
                'caption' => (string) ($postData['caption'] ?? ''),
                'title' => (string) ($postData['title'] ?? ''),
                'scheduled_at' => $post->time_post ? now()->setTimestamp((int) $post->time_post)->toIso8601String() : null,
                'account_id' => (int) ($post->account_id ?? 0),
                'provider_key' => (string) ($post->social_network ?? ''),
                'remote_post_id' => (string) ($result['remote_post_id'] ?? ''),
                'url' => $result['url'] ?? null,
                'error' => $result['error'] ?? null,
            ],
            'account' => $post->account ? [
                'id' => (int) $post->account->id,
                'display_name' => (string) ($post->account->display_name ?? ''),
                'username' => (string) ($post->account->username ?? ''),
                'provider_key' => (string) ($post->account->provider_key ?? ''),
                'capability_key' => (string) ($post->account->capability_key ?? ''),
            ] : null,
        ];
    }
}
