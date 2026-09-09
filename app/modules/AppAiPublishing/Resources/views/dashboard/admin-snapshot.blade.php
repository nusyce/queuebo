@php
    $dailyCategories = collect($daily ?? [])->map(fn (array $point) => $point['label'] ?? '--')->all();
    $dailyValues = collect($daily ?? [])->map(fn (array $point) => (int) ($point['value'] ?? 0))->values();
    $dailySeries = [[
        'name' => __('Runs'),
        'data' => $dailyValues->all(),
    ]];
    $totalRuns = (int) ($metrics['total'] ?? 0);
    $queuedRuns = (int) ($metrics['queued'] ?? 0);
    $completedRuns = (int) ($metrics['completed'] ?? 0);
    $failedRuns = (int) ($metrics['failed'] ?? 0);
    $weekTotal = (int) ($metrics['week_total'] ?? 0);
    $thisMonth = (int) ($metrics['this_month'] ?? 0);
    $avgDailyRuns = $dailyValues->isNotEmpty() ? round($dailyValues->avg(), 1) : 0;
    $peakDailyRuns = (int) $dailyValues->max();
    $successRate = $totalRuns > 0 ? (int) round(($completedRuns / max(1, $totalRuns)) * 100) : 0;
    $failureRate = $totalRuns > 0 ? (int) round(($failedRuns / max(1, $totalRuns)) * 100) : 0;
    $queuePressure = $totalRuns > 0 ? (int) round(($queuedRuns / max(1, $totalRuns)) * 100) : 0;
    $healthTone = $failedRuns > 0 ? 'warning' : ($completedRuns > 0 ? 'success' : 'neutral');
    $healthLabel = match ($healthTone) {
        'warning' => __('Needs review'),
        'success' => __('Stable flow'),
        default => __('Early activity'),
    };
    $healthCopy = match ($healthTone) {
        'warning' => __('Failures are present in the current AI publishing pipeline and should be reviewed.'),
        'success' => __('AI publishing is completing cleanly with no failed runs in the current snapshot.'),
        default => __('There is not enough completed activity yet to establish a strong trend.'),
    };
    $footerStats = [
        ['label' => __('Runs (7d)'), 'value' => $weekTotal],
        ['label' => __('This month'), 'value' => $thisMonth],
        ['label' => __('Success rate'), 'value' => $successRate, 'suffix' => '%'],
        ['label' => __('Queue share'), 'value' => $queuePressure, 'suffix' => '%'],
    ];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('AI publishing')"
    :title="null"
    :description="null"
>
    <div class="grid gap-4">
        <div class="overflow-hidden rounded-[1.55rem] border p-5 xl:p-6" style="border-color: rgba(var(--theme-accent-rgb),0.12); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 95%, rgba(var(--theme-accent-rgb),0.03))); box-shadow: 0 26px 60px -52px rgba(15,23,42,0.22);">
            <div class="flex flex-col gap-5">
                <div class="rounded-[1.2rem] border px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background:
                    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.1), transparent 34%),
                    linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 95%, transparent), color-mix(in srgb, var(--theme-surface-base) 93%, rgba(var(--theme-accent-rgb),0.03)));">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                    <i class="fa-light fa-wand-magic-sparkles mr-1.5 text-[10px]"></i>{{ __('AI publishing') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Admin dashboard') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Monitor AI publishing performance with real operational context') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Surface throughput, queue pressure, completion quality, and weekly velocity so the team can spot pipeline issues before they slow down publishing.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-ui.button :href="$route" size="sm" wire:navigate>{{ __('Open AI publishing') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
                    <x-ui.card class="space-y-5 overflow-hidden" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 97%, rgba(var(--theme-accent-rgb),0.02))); box-shadow: 0 22px 48px -42px rgba(15,23,42,0.16);">
                        <div class="grid gap-4 lg:grid-cols-[1.12fr_0.88fr]">
                            <div class="relative overflow-hidden rounded-[var(--theme-card-radius,1.15rem)] border px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background: radial-gradient(circle at top right, rgba(var(--theme-accent-rgb),0.10), transparent 38%), linear-gradient(140deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                                <div class="pointer-events-none absolute -right-10 top-0 h-32 w-32 rounded-full blur-3xl" style="background: rgba(var(--theme-accent-rgb),0.10);"></div>

                                <div class="relative">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Run command center') }}</p>
                                            <p class="mt-3 text-[2.7rem] font-semibold tracking-[-0.065em]" style="color: var(--theme-header-text-color);">{{ number_format($totalRuns) }}</p>
                                            <p class="mt-2 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Total AI publishing runs tracked') }}</p>
                                        </div>

                                        <div class="rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background: {{ $healthTone === 'warning' ? 'rgba(245,158,11,0.12)' : ($healthTone === 'success' ? 'rgba(var(--theme-success-color-rgb),0.12)' : 'rgba(var(--theme-accent-rgb),0.09)') }}; color: {{ $healthTone === 'warning' ? 'rgb(217,119,6)' : ($healthTone === 'success' ? 'var(--theme-success-color)' : 'rgba(var(--theme-accent-rgb),0.92)') }};">
                                            {{ $healthLabel }}
                                        </div>
                                    </div>

                                    <p class="mt-4 max-w-[34rem] text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $healthCopy }}</p>

                                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Completion rate') }}</p>
                                            <p class="mt-2 text-[1.75rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $successRate }}%</p>
                                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Share of tracked runs that have completed successfully') }}</p>
                                        </div>

                                        <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Average daily volume') }}</p>
                                            <p class="mt-2 text-[1.75rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($avgDailyRuns, 1) }}</p>
                                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Typical daily run output across the last 7 days') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-success-color-rgb),0.06)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-success-color-rgb),0.03)));">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Queue pressure') }}</p>
                                    <p class="mt-2 text-[1.9rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $queuePressure }}%</p>
                                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Portion of total runs still queued and waiting for execution.') }}</p>
                                </div>

                                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-warning-color-rgb),0.05)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-warning-color-rgb),0.02)));">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Failure rate') }}</p>
                                    <p class="mt-2 text-[1.9rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $failureRate }}%</p>
                                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Failed runs relative to the total tracked AI publishing volume.') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[var(--theme-card-radius,1.15rem)] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Queued now') }}</p>
                                    <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($queuedRuns) }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Waiting in the pipeline') }}</p>
                                </div>

                                <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Completed') }}</p>
                                    <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($completedRuns) }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Finished successfully') }}</p>
                                </div>

                                <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Peak day') }}</p>
                                    <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($peakDailyRuns) }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Highest 1-day AI run volume this week') }}</p>
                                </div>

                                <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Month volume') }}</p>
                                    <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($thisMonth) }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Runs created during the current month') }}</p>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.chart
                        :title="__('AI publishing trend')"
                        :description="__('Daily AI publishing run volume over the last 7 days, with queue and execution context summarized below.')"
                        type="line"
                        :categories="$dailyCategories"
                        :series="$dailySeries"
                        :height="380"
                        :footer-stats="$footerStats"
                    />
                </div>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
