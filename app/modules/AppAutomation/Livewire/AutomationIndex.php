<?php

namespace Modules\AppAutomation\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAutomation\Models\AutomationApiKey;
use Modules\AppAutomation\Models\AutomationLog;
use Modules\AppAutomation\Models\AutomationWebhook;
use Modules\AppAutomation\Services\AutomationApiKeyService;
use Modules\AppAutomation\Support\AutomationAccess;

#[Title('Automation Hub')]
class AutomationIndex extends Component
{
    public string $keyName = '';

    public string $webhookName = '';

    public string $webhookUrl = '';

    public string $webhookEvents = 'post.published,post.failed';

    public string $revealedApiKey = '';

    protected AutomationAccess $access;

    protected AutomationApiKeyService $keys;

    public function boot(AutomationAccess $access, AutomationApiKeyService $keys): void
    {
        $this->access = $access;
        $this->keys = $keys;
    }

    public function mount(): void
    {
        abort_unless($this->access->enabled(auth()->user()), 404);
    }

    public function createApiKey(): void
    {
        abort_unless($this->access->enabled(auth()->user()), 404);

        $validated = $this->validate([
            'keyName' => ['required', 'string', 'max:120'],
        ]);

        [, $plain] = $this->keys->create(auth()->user(), (string) $validated['keyName']);

        $this->reset('keyName');
        $this->revealedApiKey = $plain;
        $this->notify(__('Saved'), __('Automation API key created successfully.'));
    }

    public function dismissRevealedApiKey(): void
    {
        $this->revealedApiKey = '';
    }

    public function revokeApiKey(int $apiKeyId): void
    {
        $apiKey = $this->findOwnedApiKey($apiKeyId);

        $apiKey->forceFill(['revoked_at' => now()])->save();

        $this->notify(__('Saved'), __('Automation API key revoked.'));
    }

    public function deleteApiKey(int $apiKeyId): void
    {
        $apiKey = $this->findOwnedApiKey($apiKeyId);

        $apiKey->delete();

        $this->notify(__('Saved'), __('Automation API key deleted.'));
    }

    public function createWebhook(): void
    {
        abort_unless($this->access->enabled(auth()->user()), 404);

        $validated = $this->validate([
            'webhookName' => ['required', 'string', 'max:120'],
            'webhookUrl' => ['required', 'url', 'max:1000'],
            'webhookEvents' => ['nullable', 'string', 'max:500'],
        ]);

        $events = collect(preg_split('/[\s,]+/', (string) ($validated['webhookEvents'] ?? '')) ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        AutomationWebhook::query()->create([
            'user_id' => $this->access->workspaceOwnerUserId(auth()->user()),
            'team_id' => $this->access->teamId(auth()->user()),
            'name' => trim((string) $validated['webhookName']),
            'url' => trim((string) $validated['webhookUrl']),
            'signing_secret' => Str::random(40),
            'events' => $events,
            'is_active' => true,
            'last_sent_at' => null,
        ]);

        $this->reset('webhookName', 'webhookUrl');
        $this->webhookEvents = 'post.published,post.failed';
        $this->notify(__('Saved'), __('Automation webhook created.'));
    }

    public function toggleWebhook(int $webhookId): void
    {
        $webhook = $this->findOwnedWebhook($webhookId);

        $webhook->forceFill(['is_active' => ! $webhook->is_active])->save();

        $message = $webhook->is_active
            ? __('Automation webhook enabled.')
            : __('Automation webhook paused.');
        $this->notify(__('Saved'), $message);
    }

    public function deleteWebhook(int $webhookId): void
    {
        $webhook = $this->findOwnedWebhook($webhookId);

        $webhook->delete();

        $this->notify(__('Saved'), __('Automation webhook deleted.'));
    }

    public function render(): View
    {
        abort_unless($this->access->enabled(auth()->user()), 404);

        $workspaceOwnerUserId = $this->access->workspaceOwnerUserId(auth()->user());
        $teamId = $this->access->teamId(auth()->user());

        return view('appautomation::livewire.index', [
            'apiKeys' => AutomationApiKey::query()
                ->where('user_id', $workspaceOwnerUserId)
                ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
                ->latest('id')
                ->get(),
            'webhooks' => AutomationWebhook::query()
                ->where('user_id', $workspaceOwnerUserId)
                ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
                ->latest('id')
                ->get(),
            'logs' => AutomationLog::query()
                ->where('user_id', $workspaceOwnerUserId)
                ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
                ->latest('id')
                ->limit(25)
                ->get(),
            'apiBase' => url(config('modules.appautomation.api_prefix', 'api/automation/v1')),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Automation Hub'),
        ]);
    }

    protected function findOwnedApiKey(int $apiKeyId): AutomationApiKey
    {
        $apiKey = AutomationApiKey::query()->findOrFail($apiKeyId);

        abort_unless(
            $this->access->enabled(auth()->user())
            && (int) $apiKey->user_id === (int) $this->access->workspaceOwnerUserId(auth()->user()),
            404
        );

        return $apiKey;
    }

    protected function findOwnedWebhook(int $webhookId): AutomationWebhook
    {
        $webhook = AutomationWebhook::query()->findOrFail($webhookId);

        abort_unless(
            $this->access->enabled(auth()->user())
            && (int) $webhook->user_id === (int) $this->access->workspaceOwnerUserId(auth()->user()),
            404
        );

        return $webhook;
    }

    protected function notify(string $title, string $message, string $type = 'success'): void
    {
        $this->dispatch('app-toast',
            type: $type,
            title: $title,
            message: $message,
        );
    }
}
