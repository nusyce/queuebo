<?php

namespace Modules\AppAutomation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminUser\Models\User;
use Modules\AppAutomation\Models\AutomationLog;
use Modules\AppAutomation\Services\AutomationApiKeyService;
use Modules\AppAutomation\Services\AutomationInboundPostService;
use Modules\AppAutomation\Services\AutomationWebhookDispatcher;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppPublishing\Jobs\PublishScheduledPostJob;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Symfony\Component\HttpFoundation\Response;

class AutomationApiController extends Controller
{
    public function __construct(
        protected AutomationApiKeyService $keys,
        protected AutomationInboundPostService $posts,
        protected AutomationWebhookDispatcher $webhooks,
    ) {}

    public function me(Request $request): JsonResponse
    {
        [$apiKey, $user] = $this->authenticate($request);

        return response()->json([
            'ok' => true,
            'workspace_owner_user_id' => (int) $user->id,
            'team_id' => TeamWorkspaceAccess::activeTeam($user)?->id,
            'permissions' => (array) $apiKey->permissions,
        ]);
    }

    public function accounts(Request $request): JsonResponse
    {
        [$apiKey, $user] = $this->authenticate($request, 'accounts:read');

        $accounts = TeamWorkspaceAccess::accessibleAccountsQuery($user)
            ->where(function ($query) use ($user): void {
                $publishableCapabilityKeys = publishable_channel_capability_keys($user);

                $query->whereIn('capability_key', $publishableCapabilityKeys)
                    ->orWhere(function ($fallbackQuery) use ($publishableCapabilityKeys): void {
                        $fallbackQuery->whereNull('capability_key')
                            ->whereIn('provider_key', $publishableCapabilityKeys);
                    });
            })
            ->where('is_active', true)
            ->orderBy('provider_key')
            ->orderBy('display_name')
            ->get()
            ->map(function (SocialAccount $account): array {
                return [
                    'id' => (int) $account->id,
                    'display_name' => (string) ($account->display_name ?? ''),
                    'username' => (string) ($account->username ?? ''),
                    'provider_key' => (string) ($account->provider_key ?? ''),
                    'capability_key' => (string) ($account->capability_key ?? ''),
                    'avatar_url' => (string) ($account->avatar_url ?? ''),
                    'is_active' => (bool) $account->is_active,
                ];
            })
            ->values();

        $this->logInbound($apiKey, $user, 'accounts.read', Response::HTTP_OK, [], ['count' => $accounts->count()]);

        return response()->json([
            'ok' => true,
            'data' => $accounts,
        ]);
    }

    public function createPost(Request $request): JsonResponse
    {
        [$apiKey, $user] = $this->authenticate($request, 'posts:write');

        $validated = $request->validate([
            'account_ids' => ['required', 'array', 'min:1'],
            'account_ids.*' => ['integer'],
            'caption' => ['nullable', 'string', 'max:5000'],
            'media_urls' => ['nullable', 'array'],
            'media_urls.*' => ['url', 'max:1000'],
            'mode' => ['nullable', 'in:draft,scheduled,publish_now'],
            'schedule_at' => ['nullable', 'string', 'max:80'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'campaign_id' => ['nullable', 'integer'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer'],
            'network_options' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'source' => ['nullable', 'string', 'max:80'],
            'external_reference' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $posts = $this->posts->create($user, $validated);
        } catch (\Throwable $exception) {
            $this->logInbound($apiKey, $user, 'posts.create', Response::HTTP_UNPROCESSABLE_ENTITY, $validated, [
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ($posts as $post) {
            $this->webhooks->dispatchForPost('post.created', $post);

            if (($validated['mode'] ?? 'scheduled') === 'publish_now') {
                PublishScheduledPostJob::dispatchSync((int) $post->id);
                $post->refresh();
            }
        }

        $response = [
            'ok' => true,
            'data' => $posts->map(fn ($post) => [
                'id' => (int) $post->id,
                'id_secure' => (string) $post->id_secure,
                'account_id' => (int) $post->account_id,
                'status' => (int) $post->status,
                'scheduled_at' => $post->time_post ? now()->setTimestamp((int) $post->time_post)->toIso8601String() : null,
            ])->values(),
        ];

        $this->logInbound($apiKey, $user, 'posts.create', Response::HTTP_CREATED, $validated, $response);

        return response()->json($response, Response::HTTP_CREATED);
    }

    protected function authenticate(Request $request, ?string $permission = null): array
    {
        $apiKey = $this->keys->resolveFromRequest($request);

        abort_unless($apiKey, Response::HTTP_UNAUTHORIZED, 'Missing or invalid automation API key.');

        /** @var User|null $user */
        $user = User::query()->find((int) $apiKey->user_id);
        abort_unless($user, Response::HTTP_UNAUTHORIZED, 'Automation API key owner is no longer available.');

        if ($permission !== null) {
            $permissions = collect((array) $apiKey->permissions)->map(fn ($value) => trim((string) $value))->all();
            abort_unless(in_array($permission, $permissions, true), Response::HTTP_FORBIDDEN, 'This automation API key does not have the required permission.');
        }

        $this->keys->touch($apiKey);

        return [$apiKey, $user];
    }

    protected function logInbound($apiKey, User $user, string $event, int $statusCode, array $payload, array $response): void
    {
        AutomationLog::query()->create([
            'user_id' => (int) $user->id,
            'team_id' => TeamWorkspaceAccess::activeTeam($user)?->id,
            'api_key_id' => (int) $apiKey->id,
            'webhook_id' => null,
            'direction' => 'inbound',
            'event' => $event,
            'request_id' => (string) request()->header('X-Request-Id', ''),
            'status' => $statusCode >= 400 ? 'failed' : 'accepted',
            'status_code' => $statusCode,
            'payload' => $payload,
            'response' => $response,
        ]);
    }
}
