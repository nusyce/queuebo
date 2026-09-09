@php
    $dailyCategories = collect($daily ?? [])->map(fn (array $point) => $point['label'] ?? '--')->all();
    $dailyValues = collect($daily ?? [])->map(fn (array $point) => (int) ($point['value'] ?? 0))->values();
    $dailySeries = [[
        'name' => __('Requests'),
        'data' => $dailyValues->all(),
    ]];
    $totalRequests = (int) ($metrics['total_requests'] ?? 0);
    $successRate = (int) ($metrics['success_rate'] ?? 0);
    $successfulRequests = (int) ($metrics['successful_requests'] ?? 0);
    $failedRequests = (int) ($metrics['failed_requests'] ?? 0);
    $totalTokens = (int) ($metrics['total_tokens'] ?? 0);
    $estimatedCost = (float) ($metrics['estimated_cost'] ?? 0);
    $avgLatency = (int) ($metrics['avg_latency'] ?? 0);
    $requests7d = (int) ($metrics['requests_7d'] ?? 0);
    $avgDailyRequests = $dailyValues->isNotEmpty() ? round($dailyValues->avg(), 1) : 0;
    $peakDailyRequests = (int) $dailyValues->max();
    $failureRate = $totalRequests > 0 ? (int) round(($failedRequests / max(1, $totalRequests)) * 100) : 0;
    $tokensPerRequest = $totalRequests > 0 ? (int) round($totalTokens / max(1, $totalRequests)) : 0;
    $costPerRequest = $totalRequests > 0 ? $estimatedCost / max(1, $totalRequests) : 0;
    $latencySeconds = $avgLatency > 0 ? round($avgLatency / 1000, 1) : 0;
    $healthTone = $failedRequests > 0 || $avgLatency >= 8000 ? 'warning' : ($successRate >= 90 ? 'success' : 'neutral');
    $healthLabel = match ($healthTone) {
        'warning' => __('Needs review'),
        'success' => __('Healthy'),
        default => __('Observing'),
    };
    $healthCopy = match ($healthTone) {
        'warning' => __('Failure or latency signals are elevated. Review request logs and model routing before traffic grows further.'),
        'success' => __('Request quality is stable, failures are low, and current response behavior looks healthy.'),
        default => __('Traffic exists, but the signal is still too light to draw strong conclusions from the current window.'),
    };
    $chartFooterStats = [
        ['label' => __('7-day requests'), 'value' => $requests7d],
        ['label' => __('Success rate'), 'value' => $successRate, 'suffix' => '%'],
        ['label' => __('Failed'), 'value' => $failedRequests],
        ['label' => __('Avg latency'), 'value' => $avgLatency, 'suffix' => 'ms'],
    ];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('AI center')"
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
                                    <i class="fa-light fa-sparkles mr-1.5 text-[10px]"></i>{{ __('AI center') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Admin dashboard') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Run a real AI operations command center from the dashboard') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Track request volume, quality, latency, token intensity, and spend efficiency from one place before diving into logs and reporting.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-ui.button :href="$logsRoute" variant="outline" size="sm" wire:navigate>{{ __('Open logs') }}</x-ui.button>
                            <x-ui.button :href="$reportRoute" size="sm" wire:navigate>{{ __('Open report') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <x-ui.card class="space-y-5 overflow-hidden" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 97%, rgba(var(--theme-accent-rgb),0.02))); box-shadow: 0 22px 48px -42px rgba(15,23,42,0.16);">
                    <div class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
                        <div class="relative overflow-hidden rounded-[1.35rem] border px-5 py-5 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background:
                            radial-gradient(circle at top right, rgba(var(--theme-accent-rgb),0.14), transparent 34%),
                            linear-gradient(145deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                            <div class="pointer-events-none absolute -right-10 -top-8 h-36 w-36 rounded-full blur-3xl" style="background: rgba(var(--theme-accent-rgb),0.12);"></div>

                            <div class="relative flex h-full flex-col justify-between gap-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="max-w-3xl">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('AI operations command') }}</p>
                                        <div class="mt-3 flex flex-wrap items-end gap-x-4 gap-y-2">
                                            <p class="text-[2.9rem] font-semibold leading-none tracking-[-0.07em]" style="color: var(--theme-header-text-color);">{{ number_format($totalRequests) }}</p>
                                            <p class="pb-1 text-sm font-medium" style="color: var(--theme-muted-text-color);">{{ __('Total AI requests tracked') }}</p>
                                        </div>
                                        <p class="mt-4 max-w-[40rem] text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $healthCopy }}</p>
                                    </div>

                                    <div class="inline-flex self-start rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background: {{ $healthTone === 'warning' ? 'rgba(245,158,11,0.12)' : ($healthTone === 'success' ? 'rgba(var(--theme-success-color-rgb),0.12)' : 'rgba(var(--theme-accent-rgb),0.09)') }}; color: {{ $healthTone === 'warning' ? 'rgb(217,119,6)' : ($healthTone === 'success' ? 'var(--theme-success-color)' : 'rgba(var(--theme-accent-rgb),0.92)') }};">
                                        {{ $healthLabel }}
                                    </div>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                    <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Success rate') }}</p>
                                        <p class="mt-2 text-[1.85rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $successRate }}%</p>
                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Share of AI requests that completed successfully') }}</p>
                                    </div>

                                    <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Avg latency') }}</p>
                                        <p class="mt-2 text-[1.85rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($avgLatency) }}<span class="text-base">ms</span></p>
                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('About :seconds seconds per request.', ['seconds' => number_format($latencySeconds, 1)]) }}</p>
                                    </div>

                                    <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Average daily demand') }}</p>
                                        <p class="mt-2 text-[1.85rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format($avgDailyRequests, 1) }}</p>
                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Typical daily request volume across the last 7 days') }}</p>
                                    </div>

                                    <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Spend efficiency') }}</p>
                                        <p class="mt-2 text-[1.85rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">${{ number_format($costPerRequest, 4) }}</p>
                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Estimated average cost per request.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <div class="rounded-[1.25rem] border px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('7-day demand') }}</p>
                                <p class="mt-3 text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($requests7d) }}</p>
                                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Requests handled this week') }}</p>
                            </div>

                            <div class="rounded-[1.25rem] border px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Successful') }}</p>
                                <p class="mt-3 text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($successfulRequests) }}</p>
                                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Requests completed cleanly') }}</p>
                            </div>

                            <div class="rounded-[1.25rem] border px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Peak day') }}</p>
                                <p class="mt-3 text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($peakDailyRequests) }}</p>
                                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Highest 1-day request volume this week') }}</p>
                            </div>

                            <div class="rounded-[1.25rem] border px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Tokens / request') }}</p>
                                <p class="mt-3 text-[2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($tokensPerRequest) }}</p>
                                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Average token intensity per call') }}</p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.chart
                    :title="__('AI request trend')"
                    :description="__('Daily request volume over the last 7 days, with quality and latency summarized below.')"
                    type="line"
                    :categories="$dailyCategories"
                    :series="$dailySeries"
                    :height="360"
                    :footer-stats="$chartFooterStats"
                    class="w-full"
                />
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
