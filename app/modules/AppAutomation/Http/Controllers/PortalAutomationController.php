<?php

namespace Modules\AppAutomation\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\AppAutomation\Models\AutomationApiKey;
use Modules\AppAutomation\Models\AutomationLog;
use Modules\AppAutomation\Models\AutomationWebhook;
use Modules\AppAutomation\Services\AutomationApiKeyService;
use Modules\AppAutomation\Support\AutomationAccess;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class PortalAutomationController extends Controller
{
    public function __construct(
        protected AutomationAccess $access,
        protected AutomationApiKeyService $keys,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($this->access->enabled($request->user()), 404);

        $workspaceOwnerUserId = $this->access->workspaceOwnerUserId($request->user());
        $teamId = TeamWorkspaceAccess::activeTeam($request->user())?->id;

        return view('appautomation::index', [
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
            'revealedApiKey' => (string) session('automation_new_api_key', ''),
        ]);
    }

    public function createApiKey(Request $request): RedirectResponse
    {
        abort_unless($this->access->enabled($request->user()), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        [, $plain] = $this->keys->create($request->user(), (string) $validated['name']);

        return redirect()
            ->route('portal.automation.index')
            ->with('status', __('Automation API key created successfully.'))
            ->with('automation_new_api_key', $plain);
    }

    public function revokeApiKey(Request $request, AutomationApiKey $apiKey): RedirectResponse
    {
        abort_unless($this->ownsApiKey($request, $apiKey), 404);

        $apiKey->forceFill(['revoked_at' => now()])->save();

        return redirect()
            ->route('portal.automation.index')
            ->with('status', __('Automation API key revoked.'));
    }

    public function deleteApiKey(Request $request, AutomationApiKey $apiKey): RedirectResponse
    {
        abort_unless($this->ownsApiKey($request, $apiKey), 404);

        $apiKey->delete();

        return redirect()
            ->route('portal.automation.index')
            ->with('status', __('Automation API key deleted.'));
    }

    public function createWebhook(Request $request): RedirectResponse
    {
        abort_unless($this->access->enabled($request->user()), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:1000'],
            'events' => ['nullable', 'string', 'max:500'],
        ]);

        $workspaceOwnerUserId = $this->access->workspaceOwnerUserId($request->user());
        $teamId = TeamWorkspaceAccess::activeTeam($request->user())?->id;
        $events = collect(preg_split('/[\s,]+/', (string) ($validated['events'] ?? '')) ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        AutomationWebhook::query()->create([
            'user_id' => $workspaceOwnerUserId,
            'team_id' => $teamId,
            'name' => trim((string) $validated['name']),
            'url' => trim((string) $validated['url']),
            'signing_secret' => Str::random(40),
            'events' => $events,
            'is_active' => true,
            'last_sent_at' => null,
        ]);

        return redirect()
            ->route('portal.automation.index')
            ->with('status', __('Automation webhook created.'));
    }

    public function toggleWebhook(Request $request, AutomationWebhook $webhook): RedirectResponse
    {
        abort_unless($this->ownsWebhook($request, $webhook), 404);

        $webhook->forceFill(['is_active' => ! $webhook->is_active])->save();

        return redirect()
            ->route('portal.automation.index')
            ->with('status', $webhook->is_active ? __('Automation webhook enabled.') : __('Automation webhook paused.'));
    }

    public function deleteWebhook(Request $request, AutomationWebhook $webhook): RedirectResponse
    {
        abort_unless($this->ownsWebhook($request, $webhook), 404);

        $webhook->delete();

        return redirect()
            ->route('portal.automation.index')
            ->with('status', __('Automation webhook deleted.'));
    }

    protected function ownsApiKey(Request $request, AutomationApiKey $apiKey): bool
    {
        return $this->access->enabled($request->user())
            && (int) $apiKey->user_id === (int) $this->access->workspaceOwnerUserId($request->user());
    }

    protected function ownsWebhook(Request $request, AutomationWebhook $webhook): bool
    {
        return $this->access->enabled($request->user())
            && (int) $webhook->user_id === (int) $this->access->workspaceOwnerUserId($request->user());
    }
}
