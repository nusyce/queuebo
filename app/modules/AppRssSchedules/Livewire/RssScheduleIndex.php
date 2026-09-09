<?php

namespace Modules\AppRssSchedules\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppRssSchedules\Models\RssSchedule;
use Modules\AppRssSchedules\Services\RssScheduleService;
use Modules\AppRssSchedules\Support\InteractsWithRssSchedules;

#[Title('RSS Schedules')]
class RssScheduleIndex extends Component
{
    use InteractsWithRssSchedules;
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

    public function runSchedule(string $scheduleKey, RssScheduleService $rss): void
    {
        abort_unless($this->rssSchedulesEnabled(), 404);

        $schedule = RssSchedule::query()->where('id_secure', $scheduleKey)->firstOrFail();
        abort_unless($this->canManage($schedule), 404);

        $result = $rss->processSchedule($schedule, true, true);

        session()->flash('status', __('Manual RSS run finished. Queued :queued, skipped :skipped.', [
            'queued' => $result['queued'] ?? 0,
            'skipped' => $result['skipped'] ?? 0,
        ]));
    }

    public function toggleSchedule(string $scheduleKey): void
    {
        abort_unless($this->rssSchedulesEnabled(), 404);

        $schedule = RssSchedule::query()->where('id_secure', $scheduleKey)->firstOrFail();
        abort_unless($this->canManage($schedule), 404);

        $schedule->update([
            'status' => ! $schedule->status,
            'changed' => now()->timestamp,
        ]);

        session()->flash('status', $schedule->status ? __('RSS schedule started.') : __('RSS schedule paused.'));
    }

    public function deleteSchedule(string $scheduleKey): void
    {
        abort_unless($this->rssSchedulesEnabled(), 404);

        $schedule = RssSchedule::query()->where('id_secure', $scheduleKey)->firstOrFail();
        abort_unless($this->canManage($schedule), 404);

        $schedule->delete();

        session()->flash('status', __('RSS schedule deleted successfully.'));
    }

    public function statusBadge(RssSchedule $schedule): array
    {
        return $schedule->status
            ? ['label' => __('Running'), 'color' => '#4f46e5', 'background' => 'rgba(79, 70, 229, 0.16)', 'icon' => 'fa-loader fa-spin']
            : ['label' => __('Pause/Stop'), 'color' => '#b45309', 'background' => 'rgba(180, 83, 9, 0.16)', 'icon' => 'fa-pause'];
    }

    public function render(): View
    {
        abort_unless($this->rssSchedulesEnabled(), 404);

        $query = RssSchedule::query()
            ->where('user_id', $this->workspaceOwnerUserId())
            ->when($this->currentTeamId(), fn ($builder, $teamId) => $builder->where('team_id', $teamId))
            ->when(trim($this->search) !== '', function ($builder): void {
                $search = trim($this->search);
                $builder->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('feed_url', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->status !== 'all', fn ($builder) => $builder->where('status', $this->status === '1'))
            ->orderByDesc('changed')
            ->orderByDesc('id');

        $schedules = $query->paginate(12);
        $scheduleIds = $schedules->getCollection()->pluck('id')->all();

        return view('apprssschedules::livewire.index', [
            'schedules' => $schedules,
            'summary' => $this->statusSummary(),
            'queuedCounts' => $this->queuedCountsFor($scheduleIds),
            'publishingCounts' => $this->publishingCountsFor($scheduleIds),
            'aiRewriteUsage' => $this->aiRewriteUsageFor($scheduleIds),
            'aiRewriteCreditPreview' => $this->aiRewriteCreditPreview(),
            'trendCharts' => $this->trendChartsFor($schedules->getCollection(), $timezone = $this->currentTimezone()),
            'statusOptions' => [
                ['value' => 'all', 'label' => __('All')],
                ['value' => '1', 'label' => __('Running')],
                ['value' => '0', 'label' => __('Pause/Stop')],
            ],
            'timezone' => $timezone,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('RSS Schedules'),
        ]);
    }

    protected function trendChartsFor(Collection $schedules, string $timezone): array
    {
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $days = collect(range(6, 0))
            ->map(fn (int $offset) => $today->subDays($offset))
            ->values();

        $labels = $days->map(fn (CarbonImmutable $day) => $day->format('d M'))->all();
        $keys = $days->map(fn (CarbonImmutable $day) => $day->format('Y-m-d'))->all();
        $windowStart = $days->first()?->startOfDay()->timestamp ?? now()->subDays(6)->startOfDay()->timestamp;

        return $schedules
            ->mapWithKeys(function (RssSchedule $schedule) use ($timezone, $labels, $keys, $windowStart) {
                $queuedCounts = array_fill_keys($keys, 0);
                $publishedCounts = array_fill_keys($keys, 0);

                $rows = $schedule->history()
                    ->leftJoin('posts', 'posts.id', '=', 'rss_schedule_histories.publishing_post_id')
                    ->where(function ($query) use ($windowStart) {
                        $query
                            ->where('rss_schedule_histories.created', '>=', $windowStart)
                            ->orWhere('posts.changed', '>=', $windowStart);
                    })
                    ->get([
                        'rss_schedule_histories.created as history_created',
                        'posts.changed as post_changed',
                        'posts.status as post_status',
                    ]);

                foreach ($rows as $row) {
                    $historyCreated = (int) ($row->history_created ?? 0);
                    if ($historyCreated > 0) {
                        $queuedKey = CarbonImmutable::createFromTimestamp($historyCreated, $timezone)->format('Y-m-d');
                        if (array_key_exists($queuedKey, $queuedCounts)) {
                            $queuedCounts[$queuedKey]++;
                        }
                    }

                    if ((int) ($row->post_status ?? 0) === 4) {
                        $postChanged = (int) ($row->post_changed ?? 0);
                        if ($postChanged > 0) {
                            $publishedKey = CarbonImmutable::createFromTimestamp($postChanged, $timezone)->format('Y-m-d');
                            if (array_key_exists($publishedKey, $publishedCounts)) {
                                $publishedCounts[$publishedKey]++;
                            }
                        }
                    }
                }

                return [
                    $schedule->id => [
                        'categories' => $labels,
                        'series' => [
                            [
                                'name' => __('Queued'),
                                'data' => array_values($queuedCounts),
                                'color' => '#4f46e5',
                            ],
                            [
                                'name' => __('Published'),
                                'data' => array_values($publishedCounts),
                                'color' => '#10b981',
                            ],
                        ],
                        'options' => [
                            'chart' => [
                                'type' => 'line',
                                'spacing' => [14, 8, 10, 8],
                                'backgroundColor' => 'transparent',
                            ],
                            'title' => ['text' => null],
                            'credits' => ['enabled' => false],
                            'legend' => [
                                'enabled' => true,
                                'align' => 'left',
                                'verticalAlign' => 'top',
                            ],
                            'xAxis' => [
                                'categories' => $labels,
                                'tickLength' => 0,
                            ],
                            'yAxis' => [
                                'title' => ['text' => null],
                                'allowDecimals' => false,
                            ],
                            'tooltip' => [
                                'shared' => true,
                            ],
                            'plotOptions' => [
                                'series' => [
                                    'lineWidth' => 3,
                                    'marker' => [
                                        'enabled' => true,
                                        'radius' => 3,
                                    ],
                                    'states' => [
                                        'inactive' => [
                                            'opacity' => 1,
                                        ],
                                    ],
                                ],
                            ],
                            'series' => [
                                [
                                    'name' => __('Queued'),
                                    'data' => array_values($queuedCounts),
                                    'color' => '#4f46e5',
                                ],
                                [
                                    'name' => __('Published'),
                                    'data' => array_values($publishedCounts),
                                    'color' => '#10b981',
                                ],
                            ],
                        ],
                    ],
                ];
            })
            ->all();
    }

}
