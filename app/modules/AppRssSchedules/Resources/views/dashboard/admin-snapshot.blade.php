@php
    $dailyCategories = collect($daily ?? [])->map(fn (array $point) => $point['label'] ?? '--')->all();
    $dailySeries = [[
        'name' => __('Queued'),
        'data' => collect($daily ?? [])->map(fn (array $point) => (int) ($point['value'] ?? 0))->all(),
    ]];
    $footerStats = [
        ['label' => __('Queued (7d)'), 'value' => (int) ($metrics['queued_week'] ?? 0)],
        ['label' => __('Active schedules'), 'value' => (int) ($metrics['active'] ?? 0)],
        ['label' => __('Paused schedules'), 'value' => (int) ($metrics['paused'] ?? 0)],
        ['label' => __('History rows'), 'value' => (int) ($metrics['history_total'] ?? 0)],
    ];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('Automation')"
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
                                    <i class="fa-light fa-rss mr-1.5 text-[10px]"></i>{{ __('Automation') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Admin dashboard') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Read feed coverage and queued RSS activity in one compact block') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Feed schedule coverage and queued publishing activity for the last 7 days now use the same tighter hero treatment as the rest of admin dashboard.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-ui.button :href="$route" size="sm" wire:navigate>{{ __('Open RSS schedules') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4">
                    <x-ui.card class="space-y-5 overflow-hidden" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 97%, rgba(var(--theme-accent-rgb),0.02))); box-shadow: 0 22px 48px -42px rgba(15,23,42,0.16);">
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Total schedules') }}</p>
                                <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['total'] ?? 0)) }}</p>
                            </div>
                            <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Active') }}</p>
                                <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['active'] ?? 0)) }}</p>
                            </div>
                            <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Paused') }}</p>
                                <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['paused'] ?? 0)) }}</p>
                            </div>
                            <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Next run') }}</p>
                                <p class="mt-2 text-[1rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $metrics['next_run_label'] ?? __('No next run') }}</p>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.chart
                        :title="__('Queued post trend')"
                        :description="__('Posts queued by RSS schedules per day (last 7 days).')"
                        type="line"
                        :categories="$dailyCategories"
                        :series="$dailySeries"
                        :height="320"
                        :footer-stats="$footerStats"
                    />
                </div>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
