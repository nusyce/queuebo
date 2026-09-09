<?php

namespace Modules\AppBulkPosts\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\AppBulkPosts\Jobs\ProcessBulkPostBatchJob;
use Modules\AppBulkPosts\Support\BulkPostCsvService;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppPublishing\Models\PublishingCampaign;
use Modules\AppPublishing\Models\PublishingLabel;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Bulk Post')]
class BulkPostsIndex extends Component
{
    use WithFileUploads;

    /** @var array<int, string> */
    public array $account_ids = [];

    /** @var array<int, string> */
    public array $label_ids = [];

    public mixed $csv_file = null;

    public string $campaign_id = '';

    public int|string $interval_minutes = 60;

    protected BulkPostCsvService $csvService;

    public function boot(BulkPostCsvService $csvService): void
    {
        $this->csvService = $csvService;
    }

    public function mount(): void
    {
        abort_unless($this->bulkPostsEnabled(), 404);
    }

    public function save(): mixed
    {
        $validated = $this->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'interval_minutes' => ['nullable', 'integer', 'min:1'],
            'account_ids' => ['required', 'array', 'min:1'],
            'account_ids.*' => ['integer'],
            'campaign_id' => ['nullable', 'integer'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer'],
        ]);

        $workspaceOwnerUserId = $this->workspaceOwnerUserId();
        $campaignId = filled($validated['campaign_id'] ?? null)
            ? PublishingCampaign::query()->ownedBy($workspaceOwnerUserId)->find((int) $validated['campaign_id'])?->id
            : null;
        $labelIds = PublishingLabel::query()
            ->ownedBy($workspaceOwnerUserId)
            ->whereIn('id', collect((array) ($validated['label_ids'] ?? []))->map(fn ($id) => (int) $id)->filter()->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $accountIds = $this->publishableAccountsQuery()
            ->whereIn('id', collect((array) $validated['account_ids'])->map(fn ($id) => (int) $id)->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $batch = $this->csvService->import($validated['csv_file'], auth()->user(), [
            'account_ids' => $accountIds,
            'interval_minutes' => (int) ($validated['interval_minutes'] ?? 60),
            'campaign_id' => $campaignId,
            'label_ids' => $labelIds,
        ]);

        ProcessBulkPostBatchJob::dispatchSync($batch->id);
        $batch->refresh();

        return redirect()
            ->route('portal.publishing.calendar')
            ->with('status', __('Bulk posts have been added to Publishing. Success: :success, Failed: :failed.', [
                'success' => number_format((int) data_get($batch->stats, 'created_posts', 0)),
                'failed' => number_format((int) data_get($batch->stats, 'failed_rows', 0)),
            ]));
    }

    public function render(): View
    {
        abort_unless($this->bulkPostsEnabled(), 404);

        $user = auth()->user();
        $accountQuery = $this->publishableAccountsQuery();
        $workspaceOwnerUserId = $this->workspaceOwnerUserId();
        $channelProviderRegistry = collect(channel_provider_cards())->keyBy('key');
        $accounts = (clone $accountQuery)
            ->orderBy('display_name')
            ->get(['id', 'display_name', 'username', 'provider_key', 'category', 'avatar_url']);

        $bulkChannelOptions = $accounts
            ->map(function ($account) use ($channelProviderRegistry) {
                $provider = $channelProviderRegistry->get((string) $account->provider_key, []);
                $providerLabel = (string) data_get($provider, 'label', str($account->provider_key)->headline());
                $capabilityLabel = (string) ($account->category ?: __('Channel'));
                $providerTone = publishing_provider_tone((string) $account->provider_key);

                return [
                    'key' => (string) $account->id,
                    'label' => (string) ($account->display_name ?: ($account->username ? '@'.$account->username : strtoupper($account->provider_key))),
                    'subtitle' => trim($providerLabel.' '.str($capabilityLabel)->lower()),
                    'avatarUrl' => (string) ($account->avatar_url ?? ''),
                    'providerKey' => (string) $account->provider_key,
                    'providerLabel' => $providerLabel,
                    'providerIcon' => (string) data_get($provider, 'icon', ''),
                    'providerColor' => (string) data_get($provider, 'color', ''),
                    'providerToneSurface' => (string) ($providerTone['surface'] ?? ''),
                    'providerToneText' => (string) ($providerTone['text'] ?? ''),
                ];
            })
            ->values()
            ->all();

        $bulkChannelNetworks = $channelProviderRegistry
            ->only($accounts->pluck('provider_key')->filter()->unique()->values()->all())
            ->map(function ($provider) {
                $providerKey = (string) ($provider['key'] ?? '');
                $providerTone = publishing_provider_tone($providerKey);

                return [
                    'key' => $providerKey,
                    'label' => (string) ($provider['label'] ?? ''),
                    'icon' => (string) ($provider['icon'] ?? ''),
                    'color' => (string) ($provider['color'] ?? ''),
                    'toneSurface' => (string) ($providerTone['surface'] ?? ''),
                    'toneText' => (string) ($providerTone['text'] ?? ''),
                ];
            })
            ->filter(fn ($provider) => $provider['key'] !== '' && $provider['label'] !== '')
            ->values()
            ->all();

        return view('appbulkposts::livewire.index', [
            'bulkChannelOptions' => $bulkChannelOptions,
            'bulkChannelNetworks' => $bulkChannelNetworks,
            'campaigns' => PublishingCampaign::query()
                ->ownedBy($workspaceOwnerUserId)
                ->orderBy('name')
                ->get(['id', 'name', 'status']),
            'labels' => PublishingLabel::query()
                ->ownedBy($workspaceOwnerUserId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Bulk Post'),
        ]);
    }

    protected function workspaceOwnerUserId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }

    protected function bulkPostsEnabled(): bool
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        if (! TeamWorkspaceAccess::teamHasModule($team, 'bulk_posts')) {
            return false;
        }

        if (! TeamWorkspaceAccess::hasPermission($user, 'bulk_posts.view', $team)) {
            return false;
        }

        return ! $planOwner?->plan || $planOwner->hasPlanFeature('bulk_posts');
    }

    protected function publishableAccountsQuery()
    {
        $publishableCapabilityKeys = publishable_channel_capability_keys(auth()->user());

        return TeamWorkspaceAccess::accessibleAccountsQuery(auth()->user())
            ->where(function ($query) use ($publishableCapabilityKeys): void {
                $query->whereIn('capability_key', $publishableCapabilityKeys)
                    ->orWhere(function ($fallbackQuery) use ($publishableCapabilityKeys): void {
                        $fallbackQuery->whereNull('capability_key')
                            ->whereIn('provider_key', $publishableCapabilityKeys);
                    });
            });
    }
}
