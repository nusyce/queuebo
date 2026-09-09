<?php

namespace Modules\AppAiPublishing\Livewire;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminUser\Models\Team;
use Modules\AppAiPublishing\Jobs\ProcessAiPublishingRunJob;
use Modules\AppAiPublishing\Models\AiPublishingRun;
use Modules\AppAiPublishing\Support\AiPublishingScheduler;
use Modules\AppPublishing\Models\PublishingPost;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('AI Publishing')]
class AiPublishingIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $status = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = 'all';
        $this->resetPage();
    }

    public function stopRun(int $runId): void
    {
        abort_unless($this->aiPublishingEnabled(), 404);

        $run = $this->workspaceRunsQuery()->findOrFail($runId);

        PublishingPost::query()
            ->where('custom_data_1', (string) $run->id)
            ->where('custom_data_3', 'ai-publishing')
            ->where('status', PublishingPost::STATUS_SCHEDULED)
            ->update([
                'status' => PublishingPost::STATUS_DRAFT,
                'changed' => now()->timestamp,
            ]);

        $run->update([
            'status' => 'paused',
            'last_processed_at' => now(),
        ]);

        session()->flash('status', __('AI publishing run stopped successfully.'));
    }

    public function startRun(int $runId): void
    {
        abort_unless($this->aiPublishingEnabled(), 404);

        $run = $this->workspaceRunsQuery()->findOrFail($runId);
        $this->ensureRunTeamContext($run);

        $restoredPosts = PublishingPost::query()
            ->where('custom_data_1', (string) $run->id)
            ->where('custom_data_3', 'ai-publishing')
            ->where('status', PublishingPost::STATUS_DRAFT)
            ->update([
                'status' => PublishingPost::STATUS_SCHEDULED,
                'changed' => now()->timestamp,
            ]);

        $nextStatus = $this->hasScheduleEnded($run) ? 'completed' : 'queued';

        $run->update([
            'status' => $nextStatus,
            'last_processed_at' => now(),
        ]);

        session()->flash('status', __('AI publishing run started successfully.'));
    }

    public function runNow(int $runId): void
    {
        abort_unless($this->aiPublishingEnabled(), 404);

        $run = $this->workspaceRunsQuery()->findOrFail($runId);
        $this->ensureRunTeamContext($run);

        if ((string) $run->status === 'processing') {
            session()->flash('status', __('This AI publishing run is already processing.'));

            return;
        }

        $run->update([
            'status' => 'queued',
            'last_processed_at' => now(),
        ]);

        app()->call([
            new ProcessAiPublishingRunJob((int) $run->id, true),
            'handle',
        ]);

        $run->refresh();
        $this->syncPublishingCalendarCache();

        session()->flash('status', __('Run now finished. The generated post was sent for immediate publishing.'));
    }

    public function latestRunError(AiPublishingRun $run): ?string
    {
        $message = trim((string) data_get($run->stats, 'last_error_message', ''));

        return $message !== '' ? $message : null;
    }

    public function deleteRun(int $runId): void
    {
        abort_unless($this->aiPublishingEnabled(), 404);

        $run = $this->workspaceRunsQuery()->findOrFail($runId);

        if (! $this->canDeleteRun($run)) {
            session()->flash('status', __('This AI publishing schedule is processing and cannot be deleted right now.'));

            return;
        }

        PublishingPost::query()
            ->where('custom_data_1', (string) $run->id)
            ->where('custom_data_3', 'ai-publishing')
            ->whereIn('status', [
                PublishingPost::STATUS_DRAFT,
                PublishingPost::STATUS_PROCESSING,
                PublishingPost::STATUS_SCHEDULED,
            ])
            ->delete();

        $run->delete();

        session()->flash('status', __('AI publishing schedule deleted successfully. Published history was kept.'));
    }

    public function render(): View
    {
        abort_unless($this->aiPublishingEnabled(), 404);

        $filteredQuery = $this->workspaceRunsQuery()
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.trim($this->search).'%'))
            ->latest('id');

        $runs = (clone $filteredQuery)->paginate(9);
        $runCollection = $runs->getCollection();
        $runMetrics = $this->buildRunMetrics($runCollection);
        $runIds = $runCollection->pluck('id')->map(fn ($id) => (string) $id)->all();
        $draftReadyRunIds = $this->loadDraftReadyRunIds($runIds);
        

        return view('appaipublishing::livewire.index', [
            'runs' => $runs,
            'nextRunLabels' => $this->buildNextRunLabels($runCollection),
            'runMetrics' => $runMetrics,
            'resumableRunIds' => $this->buildResumableRunIds($runCollection, $draftReadyRunIds),
            'summary' => $this->buildSummary(),
            'statusOptions' => [
                ['value' => 'all', 'label' => __('All schedules')],
                ['value' => 'queued', 'label' => __('Queued')],
                ['value' => 'processing', 'label' => __('Processing')],
                ['value' => 'paused', 'label' => __('Paused')],
                ['value' => 'completed', 'label' => __('Completed')],
                ['value' => 'failed', 'label' => __('Failed')],
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('AI Publishing'),
        ]);
    }

    public function canEditRun(AiPublishingRun $run): bool
    {
        return $run->status !== 'processing';
    }

    public function canDeleteRun(AiPublishingRun $run): bool
    {
        return $run->status !== 'processing';
    }

    public function canStopRun(AiPublishingRun $run): bool
    {
        return $run->status !== 'paused' && ! $this->hasScheduleEnded($run);
    }

    public function canRunNow(AiPublishingRun $run): bool
    {
        return $run->status !== 'processing' && ! $this->hasScheduleEnded($run);
    }

    public function canStartRun(AiPublishingRun $run): bool
    {
        if ($run->status === 'paused') {
            return true;
        }

        if ($this->hasScheduleEnded($run)) {
            return false;
        }

        if ($run->status === 'failed' && (int) data_get($run->stats, 'created_posts', 0) === 0) {
            return true;
        }

        return PublishingPost::query()
            ->where('custom_data_1', (string) $run->id)
            ->where('custom_data_3', 'ai-publishing')
            ->where('status', PublishingPost::STATUS_DRAFT)
            ->exists();
    }

    public function statusBadge(AiPublishingRun $run): array
    {
        return match ((string) $run->status) {
            'completed' => $this->hasScheduleEnded($run)
                ? ['label' => __('Completed'), 'background' => 'rgba(var(--theme-success-rgb,34,197,94),0.12)', 'color' => 'var(--theme-success-color)']
                : ['label' => __('Running'), 'background' => 'rgba(15,118,110,0.12)', 'color' => '#0f766e'],
            'queued' => ['label' => __('Running'), 'background' => 'rgba(15,118,110,0.12)', 'color' => '#0f766e'],
            'processing' => ['label' => __('Running'), 'background' => 'rgba(var(--theme-accent-rgb),0.12)', 'color' => 'var(--theme-accent)'],
            'paused' => ['label' => __('Paused'), 'background' => 'rgba(245,158,11,0.12)', 'color' => '#b45309'],
            'failed' => ['label' => __('Failed'), 'background' => 'rgba(var(--theme-danger-rgb,239,68,68),0.12)', 'color' => 'var(--theme-danger-color)'],
            default => ['label' => str($run->status)->headline()->value(), 'background' => 'rgba(var(--theme-border-color-rgb),0.12)', 'color' => 'var(--theme-header-text-color)'],
        };
    }

    protected function aiPublishingEnabled(): bool
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        if (! TeamWorkspaceAccess::teamHasModule($team, 'ai_publishing')) {
            return false;
        }

        return ! $planOwner?->plan || $planOwner->hasPlanFeature('ai_publishing');
    }

    protected function workspaceRunsQuery()
    {
        $ownerId = $this->workspaceOwnerUserId();

        return AiPublishingRun::query()
            ->where(function ($query) use ($ownerId): void {
                $query->where('workspace_owner_user_id', $ownerId)
                    ->orWhere(function ($legacyQuery) use ($ownerId): void {
                        $legacyQuery->whereNull('workspace_owner_user_id')
                            ->where('owner_user_id', $ownerId);
                    });
            });
    }

    protected function workspaceOwnerUserId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }

    protected function ensureRunTeamContext(AiPublishingRun $run): void
    {
        if ((int) ($run->team_id ?? 0) > 0) {
            return;
        }

        $teamId = (int) (TeamWorkspaceAccess::activeTeam(auth()->user())?->id ?? 0);

        if ($teamId <= 0) {
            $teamId = (int) Team::query()
                ->where('owner_user_id', (int) ($run->workspace_owner_user_id ?: $run->owner_user_id))
                ->value('id');
        }

        if ($teamId <= 0) {
            return;
        }

        $run->update(['team_id' => $teamId]);

        PublishingPost::query()
            ->where('custom_data_1', (string) $run->id)
            ->where('custom_data_3', 'ai-publishing')
            ->whereNull('team_id')
            ->update([
                'team_id' => $teamId,
                'changed' => now()->timestamp,
            ]);

        $run->refresh();
    }

    protected function buildSummary(): array
    {
        $summary = [
            'total' => 0,
            'completed' => 0,
            'running' => 0,
            'paused' => 0,
            'failed' => 0,
        ];

        $runs = $this->workspaceRunsQuery()->get(['status', 'schedule_config']);

        foreach ($runs as $run) {
            $summary['total']++;

            if ((string) $run->status === 'paused') {
                $summary['paused']++;
                continue;
            }

            if ((string) $run->status === 'failed') {
                $summary['failed']++;
                continue;
            }

            if ($this->hasScheduleEnded($run)) {
                $summary['completed']++;
                continue;
            }

            $summary['running']++;
        }

        return $summary;
    }

    protected function buildRunMetrics(Collection $runs): array
    {
        $runIds = $runs
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values();

        if ($runIds->isEmpty()) {
            return [];
        }

        $postCounts = PublishingPost::query()
            ->selectRaw('custom_data_1 as run_id, COUNT(*) as total_posts, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed_posts', [
                PublishingPost::STATUS_FAILED,
            ])
            ->where('custom_data_3', 'ai-publishing')
            ->whereIn('custom_data_1', $runIds->all())
            ->groupBy('custom_data_1')
            ->get()
            ->keyBy(fn ($row) => (string) $row->run_id);

        $metrics = [];

        foreach ($runs as $run) {
            $row = $postCounts->get((string) $run->id);
            $runLogs = collect((array) data_get($run->stats, 'run_logs', []))
                ->filter(fn ($entry) => is_array($entry))
                ->values();

            $metrics[$run->id] = [
                'generated' => $runLogs->where('stage', 'content_generated')->count(),
                'posts' => (int) ($row?->total_posts ?? 0),
                'failed' => max(
                    (int) ($row?->failed_posts ?? 0),
                    $runLogs->where('stage', 'prompt_failed')->count()
                ),
            ];
        }

        return $metrics;
    }

    protected function buildNextRunLabels(Collection $runs): array
    {
        return $runs->mapWithKeys(function (AiPublishingRun $run): array {
            $timezone = (string) data_get($run->schedule_config, 'timezone', config('app.timezone'));
            $label = __('No next run');

            try {
                $slots = app(AiPublishingScheduler::class)->buildSlots(
                    (array) ($run->schedule_config ?? []),
                    1,
                    $timezone
                );
                $slot = $slots[0] ?? null;
                $label = $slot instanceof CarbonInterface
                    ? Carbon::parse($slot->format('Y-m-d H:i:s'), $timezone)->format('d/m/Y H:i')
                    : $label;
            } catch (\Throwable) {
                $endAt = (string) data_get($run->schedule_config, 'end_at', '');

                if ($endAt !== '' && now($timezone)->greaterThan(\Carbon\Carbon::parse($endAt, $timezone)->endOfDay())) {
                    $label = __('Ended');
                }
            }

            return [$run->id => $label];
        })->all();
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

    protected function loadDraftReadyRunIds(array $runIds): array
    {
        if ($runIds === []) {
            return [];
        }

        return PublishingPost::query()
            ->select(['custom_data_1'])
            ->where('custom_data_3', 'ai-publishing')
            ->whereIn('custom_data_1', $runIds)
            ->where('status', PublishingPost::STATUS_DRAFT)
            ->distinct()
            ->pluck('custom_data_1')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    protected function buildResumableRunIds(Collection $runs, array $draftReadyRunIds): array
    {
        $draftReadyMap = array_fill_keys($draftReadyRunIds, true);

        return $runs
            ->filter(function (AiPublishingRun $run) use ($draftReadyMap): bool {
                return $run->status === 'paused'
                    || ($run->status === 'failed' && (int) data_get($run->stats, 'created_posts', 0) === 0)
                    || isset($draftReadyMap[$run->id]);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    protected function syncPublishingCalendarCache(): void
    {
        $latestRun = $this->workspaceRunsQuery()
            ->whereNotNull('last_processed_at')
            ->whereRaw("JSON_EXTRACT(stats, '$.created_posts') IS NOT NULL")
            ->orderByDesc('last_processed_at')
            ->first(['id', 'team_id', 'workspace_owner_user_id', 'owner_user_id', 'last_processed_at', 'stats']);

        if (! $latestRun || (int) data_get($latestRun->stats, 'created_posts', 0) <= 0) {
            return;
        }

        $ownerId = (int) ($latestRun->workspace_owner_user_id ?: $latestRun->owner_user_id);
        $teamId = (int) ($latestRun->team_id ?? 0);
        $processedAt = optional($latestRun->last_processed_at)?->timestamp ?: 0;

        if ($ownerId <= 0 || $processedAt <= 0) {
            return;
        }

        $syncKey = 'ai-publishing:calendar-sync:v1:'.$ownerId.':'.$teamId;
        $lastSyncedAt = (int) Cache::get($syncKey, 0);

        if ($processedAt <= $lastSyncedAt) {
            return;
        }

        $calendarKey = 'publishing:calendar-version:v1:'.$ownerId.':'.$teamId;
        Cache::forever($calendarKey, (int) Cache::get($calendarKey, 1) + 1);
        Cache::forever($syncKey, $processedAt);
    }
}
