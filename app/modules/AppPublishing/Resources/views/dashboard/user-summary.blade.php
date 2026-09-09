@php
    $limit = (int) ($metrics['limit'] ?? 0);
    $usagePercent = $metrics['usage_percent'] ?? null;
    $limitLabel = $limit > 0 ? number_format($limit) : __('Unlimited');
@endphp

<x-ui.dashboard-module
    :eyebrow="__('Publishing')"
    :title="null"
    :description="null"
>
    <div class="space-y-4">
        <div class="max-w-full overflow-hidden rounded-[1.55rem] border" style="border-color: rgba(var(--theme-accent-rgb,37,99,235),0.12); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 95%, rgba(var(--theme-accent-rgb),0.03))); box-shadow: 0 26px 60px -52px rgba(15,23,42,0.22);">
            <div class="flex min-w-0 flex-col gap-5 p-4 sm:p-5 xl:p-6">
                <div class="min-w-0 rounded-[1.2rem] border px-4 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background:
                    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.1), transparent 34%),
                    linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 95%, transparent), color-mix(in srgb, var(--theme-surface-base) 93%, rgba(var(--theme-accent-rgb),0.03)));">
                    <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                    <i class="fa-light fa-calendar-days mr-1.5 text-[10px]"></i>{{ __('Publishing performance') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Queue in motion') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Measure output, trends, and usage before the month runs away') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Track monthly post volume, watch the last 7 days of activity, and compare current usage against your publishing plan limit.') }}</p>
                        </div>

                        <div class="flex w-full min-w-0 flex-wrap items-center gap-3 lg:w-auto lg:shrink-0">
                            <x-ui.button :href="$item['route'] ?? route('portal.publishing.calendar')" size="sm" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" wire:navigate>{{ __('Open calendar') }}</x-ui.button>
                            <x-ui.button :href="$campaignRoute" variant="outline" size="sm" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" wire:navigate>{{ __('Campaigns') }}</x-ui.button>
                            <x-ui.button :href="$labelRoute" variant="outline" size="sm" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" wire:navigate>{{ __('Labels') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                <div class="min-w-0 space-y-4">
                    <div class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <x-ui.card padding="md" style="border-color: rgba(91,124,250,0.16); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(91,124,250,0.32);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(91,124,250,0.95), rgba(91,124,250,0.28));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['month_total'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('This month') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Posts created this month') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(243,181,98,0.2); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(243,181,98,0.28);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(243,181,98,0.92), rgba(243,181,98,0.24));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['scheduled'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Scheduled') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Ready to publish') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(111,207,151,0.2); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(111,207,151,0.28);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(111,207,151,0.94), rgba(111,207,151,0.24));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['published'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Published') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Sent successfully this month') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(177,138,232,0.18); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(177,138,232,0.28);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(177,138,232,0.92), rgba(177,138,232,0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['drafts'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Drafts') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Still waiting for scheduling') }}</p>
                        </x-ui.card>
                    </div>

                    <div class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <x-ui.card padding="md" style="border-color: rgba(242,139,130,0.18); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(242,139,130,0.28);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(242,139,130,0.94), rgba(242,139,130,0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['failed'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Failed') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Posts needing attention') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(103,183,220,0.18); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(103,183,220,0.28);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(103,183,220,0.94), rgba(103,183,220,0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['processing'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Processing') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Currently running') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(94,199,194,0.18); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(94,199,194,0.28);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(94,199,194,0.94), rgba(94,199,194,0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['upcoming'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Next 7 days') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Upcoming scheduled queue') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(243,181,98,0.18); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(243,181,98,0.28);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, rgba(243,181,98,0.94), rgba(243,181,98,0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $metrics['publish_success_rate'] !== null ? $metrics['publish_success_rate'].'%' : '—' }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Success rate') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Published vs failed this month') }}</p>
                        </x-ui.card>
                    </div>

                    <x-ui.card class="space-y-4" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent); box-shadow: 0 22px 44px -40px rgba(15,23,42,0.16);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Monthly limit') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">
                                    {{ $limit > 0
                                        ? __(':used of :limit posts used this month', ['used' => number_format((int) ($metrics['month_total'] ?? 0)), 'limit' => $limitLabel])
                                        : __('This plan does not enforce a monthly publishing cap.') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $limitLabel }}</p>
                                @if ($usagePercent !== null)
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $usagePercent }}%</p>
                                @endif
                            </div>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb),0.24);">
                            <div
                                class="h-full rounded-full transition-all"
                                style="width: {{ $usagePercent ?? 12 }}%; background: linear-gradient(90deg, #5b7cfa, #6fcf97, #b18ae8);"
                            ></div>
                        </div>

                        <div class="grid min-w-0 gap-4 sm:grid-cols-3">
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(91,124,250,0.12); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, rgba(91,124,250,0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Campaigns') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['campaigns'] ?? 0)) }}</p>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(94,199,194,0.14); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, rgba(94,199,194,0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Labels') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['labels'] ?? 0)) }}</p>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(243,181,98,0.16); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, rgba(243,181,98,0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Active accounts') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['accounts'] ?? 0)) }}</p>
                            </div>
                        </div>

                    </x-ui.card>
                </div>

                <x-ui.chart
                    class="min-w-0"
                    :title="__('Posting trend')"
                    :description="__('Daily publishing volume over the last 7 days.')"
                    type="column"
                    :categories="$chart['categories']"
                    :series="$chart['series']"
                    :height="520"
                />
                </div>
            </div>
        </div>

        <div class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <x-ui.chart
                class="min-w-0"
                :title="__('Status mix')"
                :description="__('How the publishing workload for this month is distributed by status.')"
                type="donut"
                :series="$statusChart"
                :height="360"
            />

            <x-ui.chart
                class="min-w-0"
                :title="__('Network mix')"
                :description="__('Where publishing activity is concentrated this month.')"
                type="bar"
                :series="$providerChart"
                :height="360"
            />
        </div>

        @if ($rssSummary || $aiPublishingSummary)
            <div class="grid min-w-0 gap-4 xl:grid-cols-2">
                @if ($rssSummary)
                    <x-ui.card class="space-y-4" style="border-color: rgba(91,124,250,0.16); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(91,124,250,0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(91,124,250,0.02))); box-shadow: 0 20px 42px -36px rgba(91,124,250,0.22);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Automation lane') }}</p>
                                <p class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('RSS Schedules') }}</p>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $rssSummary['description'] }}</p>
                            </div>
                            <a href="{{ $rssSummary['route'] }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-full transition hover:-translate-y-[1px]" style="background: linear-gradient(145deg, rgba(91,124,250,0.14), color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent)); color: #5b7cfa; box-shadow: inset 0 1px 0 color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
                                <i class="fa-light fa-rss text-base"></i>
                            </a>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(91,124,250,0.14); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, rgba(91,124,250,0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Active feeds') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($rssSummary['active'] ?? 0)) }}</p>
                            </div>
                            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-overlay) 86%, transparent);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Queued posts') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($rssSummary['queued'] ?? 0)) }}</p>
                            </div>
                            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-overlay) 86%, transparent);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Paused') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($rssSummary['paused'] ?? 0)) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 border-t pt-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.36); color: var(--theme-muted-text-color);">
                            <span>{{ __('Total schedules: :count', ['count' => number_format((int) ($rssSummary['total'] ?? 0))]) }}</span>
                            <span>{{ __('Next run: :time', ['time' => $rssSummary['next_run_label'] ?? __('No next run')]) }}</span>
                        </div>
                    </x-ui.card>
                @endif

                @if ($aiPublishingSummary)
                    <x-ui.card class="space-y-4" style="border-color: rgba(94,199,194,0.18); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(94,199,194,0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(94,199,194,0.02))); box-shadow: 0 20px 42px -36px rgba(94,199,194,0.22);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Automation lane') }}</p>
                                <p class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('AI Publishing') }}</p>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $aiPublishingSummary['description'] }}</p>
                            </div>
                            <a href="{{ $aiPublishingSummary['route'] }}" wire:navigate class="inline-flex h-10 w-10 items-center justify-center rounded-full transition hover:-translate-y-[1px]" style="background: linear-gradient(145deg, rgba(94,199,194,0.16), color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent)); color: #1e9d93; box-shadow: inset 0 1px 0 color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
                                <i class="fa-light fa-sparkles text-base"></i>
                            </a>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(94,199,194,0.16); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, rgba(94,199,194,0.03));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Runs this month') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($aiPublishingSummary['month_total'] ?? 0)) }}</p>
                            </div>
                            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-overlay) 86%, transparent);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Queued now') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($aiPublishingSummary['queued'] ?? 0)) }}</p>
                            </div>
                            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-overlay) 86%, transparent);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Completed') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($aiPublishingSummary['completed'] ?? 0)) }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 border-t pt-3" style="border-color: rgba(var(--theme-border-color-rgb),0.36);">
                            <div class="flex items-center justify-between gap-3 text-xs" style="color: var(--theme-muted-text-color);">
                                <span>
                                    {{ ($aiPublishingSummary['limit'] ?? 0) > 0
                                        ? __(':used of :limit AI runs used', ['used' => number_format((int) ($aiPublishingSummary['month_total'] ?? 0)), 'limit' => number_format((int) ($aiPublishingSummary['limit'] ?? 0))])
                                        : __('Unlimited AI publishing usage on this plan.') }}
                                </span>
                                @if (($aiPublishingSummary['usage_percent'] ?? null) !== null)
                                    <span>{{ $aiPublishingSummary['usage_percent'] }}%</span>
                                @endif
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb),0.24);">
                                <div
                                    class="h-full rounded-full"
                                    style="width: {{ $aiPublishingSummary['usage_percent'] ?? 14 }}%; background: linear-gradient(90deg, rgba(94,199,194,0.98), rgba(var(--theme-accent-rgb),0.9));"
                                ></div>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-xs" style="color: var(--theme-muted-text-color);">
                                <span>{{ __('Failed runs: :count', ['count' => number_format((int) ($aiPublishingSummary['failed'] ?? 0))]) }}</span>
                                <span>{{ __('Total runs: :count', ['count' => number_format((int) ($aiPublishingSummary['total'] ?? 0))]) }}</span>
                            </div>
                        </div>
                    </x-ui.card>
                @endif
            </div>
        @endif

        <div class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,0.78fr)_minmax(0,1.22fr)]">
            <x-ui.card class="min-w-0 space-y-6" style="background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Publishing insights') }}</p>
                        <p class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Top campaigns and labels') }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full" style="background: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                        <i class="fa-light fa-layer-group text-base"></i>
                    </span>
                </div>

                <div class="space-y-5">
                    <section class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top campaigns') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Which campaign shells are driving the most publishing volume.') }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full" style="background: rgba(91,124,250,0.10); color: #5b7cfa;">
                                <i class="fa-light fa-bullhorn"></i>
                            </span>
                        </div>

                        <div class="space-y-3 max-h-[14rem] overflow-y-auto pr-1">
                            @forelse ($topCampaigns as $index => $campaign)
                                <div class="flex items-center justify-between gap-4 rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold" style="background: rgba(91,124,250,0.12); color: #5b7cfa;">
                                            <i class="fa-light fa-star"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $campaign['name'] }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Campaign rank #:rank', ['rank' => $index + 1]) }}</p>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $campaign['count']) }}</span>
                                </div>
                            @empty
                                <div class="rounded-[1rem] border px-5 py-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-overlay) 70%, transparent);">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No campaign data yet') }}</p>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Campaign performance will appear here once posts start using campaign assignments.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="space-y-3 border-t pt-5" style="border-color: rgba(var(--theme-border-color-rgb),0.42);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top labels') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('The labels that are showing up most across your recent queue.') }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full" style="background: rgba(94,199,194,0.10); color: #5ec7c2;">
                                <i class="fa-light fa-tags"></i>
                            </span>
                        </div>

                        <div class="max-h-[17rem] overflow-y-auto pr-1">
                            @forelse ($topLabels as $index => $label)
                                <div class="{{ $index === 0 ? '' : 'mt-3' }}">
                                    <div class="flex items-center justify-between gap-3 rounded-[1rem] border px-4 py-3" style="border-color: rgba(94,199,194,0.16); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold" style="background: rgba(94,199,194,0.12); color: #5ec7c2;">
                                                <i class="fa-light fa-tags"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $label['name'] }}</p>
                                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Label rank #:rank', ['rank' => $index + 1]) }}</p>
                                            </div>
                                        </div>
                                        <span class="shrink-0 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $label['count']) }}</span>
                                    </div>
                                </div>
                            @empty
                                <x-ui.empty
                                    icon="fa-light fa-tags"
                                    :title="__('No label data yet')"
                                    :description="__('Label usage will appear here once publishing posts start carrying labels.')"
                                    class="rounded-[1rem] border px-5 py-6"
                                    style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-overlay) 70%, transparent);"
                                />
                            @endforelse
                        </div>
                    </section>
                </div>
            </x-ui.card>

            <x-ui.card class="min-w-0 space-y-4" style="background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Recent queue') }}</p>
                        <p class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Recent editable posts') }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full" style="background: rgba(243,181,98,0.12); color: #d88f38;">
                        <i class="fa-light fa-clock-rotate-left text-base"></i>
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($recentPosts as $post)
                        <a
                            href="{{ $post['post_url'] && $post['status'] === __('Published') ? $post['post_url'] : $post['route'] }}"
                            @if ($post['post_url'] && $post['status'] === __('Published'))
                                target="_blank" rel="noreferrer"
                            @else
                                wire:navigate
                            @endif
                            class="block rounded-[1rem] border p-3 transition hover:-translate-y-[1px]"
                            style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent); box-shadow: 0 18px 34px -34px rgba(15,23,42,0.18);"
                        >
                            <div class="flex items-start gap-3">
                                <div class="relative h-[4.75rem] w-[4.75rem] shrink-0 overflow-hidden rounded-[0.95rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: linear-gradient(135deg, rgba(91,124,250,0.12), rgba(111,207,151,0.1));">
                                    @if ($post['media_preview'])
                                        @if ($post['is_video'])
                                            <video class="h-full w-full object-cover" muted playsinline preload="metadata">
                                                <source src="{{ $post['media_preview'] }}" type="{{ $post['media_mime'] ?: 'video/mp4' }}">
                                            </video>
                                            <span class="absolute inset-0 flex items-center justify-center">
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-950/65 text-white">
                                                    <i class="fa-solid fa-play text-[11px]"></i>
                                                </span>
                                            </span>
                                        @else
                                            <img src="{{ $post['media_preview'] }}" alt="{{ $post['title'] }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                        @endif
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xl" style="color: var(--theme-muted-text-color);">
                                            <i class="fa-light {{ $post['media_type'] === 'VIDEO' || $post['media_type'] === 'REEL' ? 'fa-video' : ($post['media_type'] === 'IMAGE' || $post['media_type'] === 'CAROUSEL' ? 'fa-image' : 'fa-align-left') }}"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $post['title'] }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">
                                                {{ $post['network'] }}{{ $post['handle'] ? ' • '.$post['handle'] : '' }}
                                            </p>
                                        </div>
                                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em]" style="background: {{ $post['status_tone']['surface'] }}; color: {{ $post['status_tone']['text'] }};">
                                            {{ $post['status'] }}
                                        </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em]" style="background: rgba(var(--theme-border-color-rgb),0.08); color: var(--theme-muted-text-color);">
                                            {{ $post['media_type'] }}
                                        </span>
                                        @if ($post['media_count'] > 1)
                                            <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold" style="background: rgba(91,124,250,0.08); color: #5b7cfa;">
                                                {{ __(':count files', ['count' => $post['media_count']]) }}
                                            </span>
                                        @endif
                                        <span
                                            class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                            style="{{ $post['campaign']
                                                ? 'background: color-mix(in srgb, '.($post['campaign_color'] ?: '#c9802a').' 14%, white); color: '.($post['campaign_color'] ?: '#c9802a').';'
                                                : 'background: rgba(243,181,98,0.08); color: #c9802a;' }}"
                                        >
                                            {{ $post['campaign'] ?: __('No campaign') }}
                                        </span>
                                    </div>

                                    @if ($post['caption'])
                                        <p class="mt-3 text-xs leading-5" style="color: var(--theme-muted-text-color); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $post['caption'] }}
                                        </p>
                                    @endif

                                    @if ($post['status'] === __('Failed') && $post['error'])
                                        <div class="mt-3 rounded-[0.85rem] border px-3 py-2 text-xs leading-5" style="border-color: rgba(242,139,130,0.26); background: rgba(242,139,130,0.08); color: #b85b55;">
                                            <span class="font-semibold">{{ __('Error') }}:</span>
                                            {{ $post['error'] }}
                                        </div>
                                    @endif

                                    <div class="mt-3 flex items-center justify-between gap-3 border-t pt-3 text-[11px]" style="border-color: rgba(var(--theme-border-color-rgb),0.38); color: var(--theme-muted-text-color);">
                                        <div class="flex items-center gap-3">
                                            @if ($post['scheduled'])
                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fa-light fa-calendar-clock"></i>
                                                    {{ $post['scheduled'] }}
                                                </span>
                                            @elseif ($post['created'])
                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fa-light fa-clock"></i>
                                                    {{ $post['created'] }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($post['post_url'] && $post['status'] === __('Published'))
                                            <span class="inline-flex items-center gap-1.5 font-semibold" style="color: var(--theme-accent);">
                                                <i class="fa-light fa-arrow-up-right-from-square"></i>
                                                {{ __('View post') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 font-semibold" style="color: var(--theme-accent);">
                                                <i class="fa-light {{ $post['editable'] ? 'fa-pen-to-square' : 'fa-calendar-lines-pen' }}"></i>
                                                {{ $post['editable'] ? __('Edit post') : __('Open calendar') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <x-ui.empty
                            icon="fa-light fa-clock-rotate-left"
                            :title="__('No recent posts yet')"
                            :description="__('Recent scheduled, draft, and published posts with media previews will appear here once the queue starts filling up.')"
                        />
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-ui.dashboard-module>
