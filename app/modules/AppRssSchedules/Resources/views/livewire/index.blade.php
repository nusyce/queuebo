<div class="min-w-0 max-w-full overflow-x-hidden space-y-8 px-4 pb-10 pt-4 sm:px-5 xl:px-6">
    <section class="min-w-0 max-w-full overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
    ">
        <div class="grid min-w-0 gap-6 px-4 py-6 sm:px-8 sm:py-7 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Content automation') }}</p>
                <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('RSS Schedules') }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Manage recurring RSS feed schedules, route unseen items into Publishing, and control each automation from one board.') }}</p>
            </div>

            <div class="flex min-w-0 flex-wrap items-center justify-end gap-2">
                <x-ui.button :href="route('portal.rss-schedules.create')" size="lg" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto">
                    <i class="fa-light fa-plus"></i>
                    {{ __('Add RSS Schedule') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    

    <x-ui.metric-strip
        columns="md:grid-cols-2 xl:grid-cols-4"
        gap="gap-5"
        :card-style="'border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96); min-height: 12rem;'"
        :progress-track-style="'background-color: rgba(var(--theme-border-color-rgb), 0.18);'"
        :items="[
            [
                'label' => __('Total'),
                'value' => number_format($summary['total']),
                'description' => __('All configured RSS schedules.'),
                'tone' => '#7c3aed',
                'icon' => 'fa-light fa-rss',
                'cardStyle' => 'background: linear-gradient(180deg, rgba(124, 58, 237, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(124, 58, 237, 0.16);',
                'iconSurface' => 'linear-gradient(145deg, rgba(124, 58, 237, 0.14), rgba(255,255,255,0.96));',
                'iconBorder' => 'rgba(124, 58, 237, 0.18)',
                'progressTrackStyle' => 'background-color: rgba(124, 58, 237, 0.12);',
            ],
            [
                'label' => __('Running'),
                'value' => number_format($summary['active']),
                'description' => __('Schedules actively watching feeds.'),
                'tone' => '#4f46e5',
                'icon' => 'fa-light fa-loader',
                'cardStyle' => 'background: linear-gradient(180deg, rgba(79, 70, 229, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(79, 70, 229, 0.16);',
                'iconSurface' => 'linear-gradient(145deg, rgba(79, 70, 229, 0.14), rgba(255,255,255,0.96));',
                'iconBorder' => 'rgba(79, 70, 229, 0.18)',
                'progressTrackStyle' => 'background-color: rgba(79, 70, 229, 0.12);',
            ],
            [
                'label' => __('Paused'),
                'value' => number_format($summary['paused']),
                'description' => __('Schedules waiting to be resumed.'),
                'tone' => '#ea580c',
                'icon' => 'fa-light fa-circle-pause',
                'cardStyle' => 'background: linear-gradient(180deg, rgba(249, 115, 22, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(249, 115, 22, 0.16);',
                'iconSurface' => 'linear-gradient(145deg, rgba(249, 115, 22, 0.14), rgba(255,255,255,0.96));',
                'iconBorder' => 'rgba(249, 115, 22, 0.18)',
                'progressTrackStyle' => 'background-color: rgba(249, 115, 22, 0.12);',
            ],
            [
                'label' => __('Queued'),
                'value' => number_format($summary['queued']),
                'description' => __('Feed items already inserted into Publishing.'),
                'tone' => '#0284c7',
                'icon' => 'fa-light fa-paper-plane-top',
                'cardStyle' => 'background: linear-gradient(180deg, rgba(2, 132, 199, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(2, 132, 199, 0.16);',
                'iconSurface' => 'linear-gradient(145deg, rgba(2, 132, 199, 0.14), rgba(255,255,255,0.96));',
                'iconBorder' => 'rgba(2, 132, 199, 0.18)',
                'progressTrackStyle' => 'background-color: rgba(2, 132, 199, 0.12);',
            ],
        ]"
    />

    <div class="min-w-0 max-w-full rounded-[1.45rem] border px-4 py-4 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
        radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 28%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-soft-rgb,248,250,252),0.8));
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.04);
    ">
        <div class="flex min-w-0 flex-col gap-3 xl:flex-row xl:items-end">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-magnifying-glass text-sm"></i>
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Search schedules') }}</p>
                        <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Filter feeds by title, URL, or description.') }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <x-ui.input wire:model.live.debounce.300ms="search" :placeholder="__('Search RSS schedules...')" />
                </div>
            </div>

            <div class="grid min-w-0 gap-3 sm:grid-cols-[minmax(0,14rem)_minmax(0,1fr)_minmax(0,1fr)] xl:w-auto xl:items-end">
                <div class="min-w-0">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Status') }}</p>
                    <x-ui.select wire:model.live="status">
                        @foreach ($statusOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.button type="button" size="sm" class="h-11 w-full justify-center px-5 text-center whitespace-normal" wire:click="$refresh">{{ __('Apply') }}</x-ui.button>
                <x-ui.button type="button" variant="outline" size="sm" class="h-11 w-full justify-center px-5 text-center whitespace-normal" wire:click="resetFilters">{{ __('Reset') }}</x-ui.button>
            </div>
        </div>
    </div>

    <div class="grid min-w-0 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($schedules as $schedule)
                @php
                    $counts = $publishingCounts[$schedule->id] ?? ['published' => 0, 'failed' => 0];
                    $aiSettings = is_array($schedule->settings) ? $schedule->settings : [];
                    $aiRewriteEnabled = (bool) ($aiSettings['enable_ai_rewrite'] ?? false);
                    $aiPerRunLimit = max(0, (int) ($aiSettings['ai_rewrite_max_per_run'] ?? 0));
                    $aiMonthlyLimit = max(0, (int) ($aiSettings['ai_rewrite_monthly_credit_limit'] ?? 0));
                    $aiUsage = $aiRewriteUsage[$schedule->id] ?? ['credits_used' => 0, 'rewrite_count' => 0];
                    $aiCreditsUsed = (int) ($aiUsage['credits_used'] ?? 0);
                    $aiRewriteCount = (int) ($aiUsage['rewrite_count'] ?? 0);
                    $nextRun = $schedule->next_run_at ? \Carbon\Carbon::createFromTimestamp((int) $schedule->next_run_at, $timezone)->format('d/m/Y H:i') : __('No next run');
                    $badge = $this->statusBadge($schedule);
                    $publishedCount = (int) ($counts['published'] ?? 0);
                    $failedCount = (int) ($counts['failed'] ?? 0);
                    $queuedCount = (int) ($queuedCounts[$schedule->id] ?? 0);
                    $deliveryTotal = max(1, $publishedCount + $failedCount);
                    $deliveryRate = (int) round(($publishedCount / $deliveryTotal) * 100);
                    $weekdayOrder = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $weekdays = collect((array) $schedule->weekdays)
                        ->filter(fn ($enabled) => (bool) $enabled)
                        ->keys()
                        ->sortBy(fn ($day) => array_search($day, $weekdayOrder, true))
                        ->values();
                    $trendChart = $trendCharts[$schedule->id] ?? null;
                    $queuedTrend = collect((array) data_get($trendChart, 'series.0.data', []));
                    $publishedTrend = collect((array) data_get($trendChart, 'series.1.data', []));
                    $latestQueued = (int) ($queuedTrend->last() ?? 0);
                    $latestPublished = (int) ($publishedTrend->last() ?? 0);
                    $weeklyQueued = (int) $queuedTrend->sum();
                    $weeklyPublished = (int) $publishedTrend->sum();
                    $recentLogs = $schedule->history()
                        ->with('publishingPost:id,status,changed,data')
                        ->latest('queued_at')
                        ->limit(8)
                        ->get();
                @endphp

                <div class="min-w-0 max-w-full h-full">
                <article class="group flex min-w-0 max-w-full h-full flex-col overflow-hidden rounded-[1.7rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.64); background:
                    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.08), transparent 24%),
                    linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.99), rgba(var(--theme-surface-soft-rgb,248,250,252),0.84));
                    box-shadow: 0 22px 50px rgba(15, 23, 42, 0.045);
                ">
                    <div class="flex min-w-0 h-full flex-col gap-3.5 px-4 py-5 sm:px-6 sm:py-6">
                        <div class="flex min-w-0 items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-4">
                                <span class="inline-flex h-12 w-12 flex-none items-center justify-center rounded-[1.1rem] border" style="border-color: rgba(249, 115, 22, 0.18); background:
                                    radial-gradient(circle at 30% 30%, rgba(254, 215, 170, 0.95), rgba(253, 186, 116, 0.9) 58%, rgba(251, 146, 60, 0.82) 100%);
                                    color: #ffffff;
                                    box-shadow: inset 0 1px 0 rgba(255,255,255,0.35), 0 10px 24px rgba(249, 115, 22, 0.18);
                                ">
                                    <i class="fa-solid fa-square-rss text-[1.05rem]"></i>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('RSS automation') }}</p>
                                    <p class="mt-1 truncate text-[1.15rem] font-semibold tracking-[-0.05em] leading-tight" style="color: var(--theme-header-text-color);">{{ $schedule->name }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: color-mix(in srgb, {{ $badge['color'] }} 18%, rgba(var(--theme-border-color-rgb), 0.42)); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72); color: {{ $badge['color'] }};">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $badge['color'] }};"></span>
                                            {{ $badge['label'] }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72); color: var(--theme-muted-text-color);">
                                            {{ count((array) $schedule->time_posts) }} {{ __('slots') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <x-ui.dropdown-menu align="right" width="auto" class="min-w-[13rem]">
                                <x-slot:trigger>
                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-[1rem] border transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78); color: var(--theme-header-text-color);">
                                        <i class="fa-light fa-ellipsis text-sm"></i>
                                    </button>
                                </x-slot:trigger>

                                <button type="button" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false; document.getElementById('rss-analytics-trigger-{{ $schedule->id }}')?.click()">
                                    <i class="fa-light fa-chart-mixed text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('View Analytics') }}</span>
                                </button>

                                <button type="button" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false; document.getElementById('rss-log-trigger-{{ $schedule->id }}')?.click()">
                                    <i class="fa-light fa-rectangle-history text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('View RSS Log') }}</span>
                                </button>

                                <a href="{{ $schedule->feed_url }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false">
                                    <i class="fa-light fa-arrow-up-right-from-square text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Open RSS Link') }}</span>
                                </a>

                                <a href="{{ route('portal.rss-schedules.edit', $schedule) }}" wire:navigate class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false">
                                    <i class="fa-light fa-pen-to-square text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Edit Setup') }}</span>
                                </a>

                                <div class="my-1 border-t" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent);"></div>

                                <button type="button" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-danger-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: rgba(244, 63, 94, 0.08); color: var(--theme-danger-color);' : 'color: var(--theme-danger-color);'" x-on:click.stop="open = false; document.getElementById('rss-delete-trigger-{{ $schedule->id }}')?.click()">
                                    <i class="fa-light fa-trash text-[14px]" style="color: var(--theme-danger-color);"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Delete Schedule') }}</span>
                                </button>
                            </x-ui.dropdown-menu>
                        </div>

                        <div class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.46); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.52);">
                            <div class="grid gap-0 text-sm sm:grid-cols-2">
                                <div class="flex items-start gap-2.5 px-3.5 py-3" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.32);">
                                    <span class="mt-0.5 inline-flex h-7 w-7 flex-none items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                                        <i class="fa-light fa-clock text-[11px]"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Next run') }}</span>
                                        <p class="mt-0.5 font-medium" style="color: var(--theme-header-text-color);">{{ $nextRun }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2.5 border-t px-3.5 py-3 sm:border-l sm:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.32);">
                                    <span class="mt-0.5 inline-flex h-7 w-7 flex-none items-center justify-center rounded-full" style="background-color: rgba(14, 165, 233, 0.08); color: #0284c7;">
                                        <i class="fa-light fa-share-nodes text-[11px]"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Channels') }}</span>
                                        <p class="mt-0.5 font-medium" style="color: var(--theme-header-text-color);">{{ number_format(count((array) $schedule->account_ids)) }} {{ __('selected') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 border-t px-3.5 py-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.36);">
                                <span class="mt-0.5 inline-flex h-7 w-7 flex-none items-center justify-center rounded-full" style="background-color: rgba(16, 185, 129, 0.08); color: #059669;">
                                    <i class="fa-light fa-repeat text-[11px]"></i>
                                </span>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Active days') }}</span>
                                    @if ($weekdays->isNotEmpty())
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($weekdays as $day)
                                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold tracking-[0.02em]" style="border-color: rgba(16, 185, 129, 0.16); background-color: rgba(16, 185, 129, 0.08); color: #047857;">
                                                    {{ $day }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-0.5 font-medium" style="color: var(--theme-header-text-color);">{{ __('No days') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid min-w-0 gap-2.5 sm:grid-cols-3">
                            <div class="rounded-[0.9rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Published') }}</p>
                                <p class="mt-1 text-[1.4rem] font-semibold tracking-[-0.05em]" style="color: #059669;">{{ number_format($publishedCount) }}</p>
                            </div>
                            <div class="rounded-[0.9rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Queued') }}</p>
                                <p class="mt-1 text-[1.4rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($queuedCount) }}</p>
                            </div>
                            <div class="rounded-[0.9rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Failed') }}</p>
                                <p class="mt-1 text-[1.4rem] font-semibold tracking-[-0.05em]" style="color: {{ $failedCount > 0 ? 'var(--theme-danger-color)' : 'var(--theme-header-text-color)' }};">{{ number_format($failedCount) }}</p>
                            </div>
                        </div>

                        @if ($aiRewriteEnabled)
                            <div class="rounded-[0.95rem] border px-3.5 py-3" style="border-color: rgba(124, 58, 237, 0.16); background:
                                linear-gradient(180deg, rgba(124, 58, 237, 0.05), rgba(var(--theme-surface-base-rgb,255,255,255),0.82));
                            ">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: #6d28d9;">{{ __('AI rewrite') }}</p>
                                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">
                                            {{ __(':credits credit per rewrite', ['credits' => $aiRewriteCreditPreview['unit_cost'] ?? 0]) }}
                                            •
                                            {{ $aiPerRunLimit > 0 ? __('max :count/run', ['count' => number_format($aiPerRunLimit)]) : __('unlimited/run') }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                            {{ $aiMonthlyLimit > 0 ? __(':used/:limit month', ['used' => number_format($aiCreditsUsed), 'limit' => number_format($aiMonthlyLimit)]) : __(':used this month', ['used' => number_format($aiCreditsUsed)]) }}
                                        </p>
                                        <p class="mt-1 text-[11px]" style="color: var(--theme-muted-text-color);">
                                            {{ ($aiRewriteCreditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => number_format((int) ($aiRewriteCreditPreview['remaining'] ?? 0))]) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-auto flex items-center gap-2 pt-1">
                            <button
                                type="button"
                                wire:click.prevent="runSchedule('{{ $schedule->id_secure }}')"
                                wire:loading.attr="disabled"
                                class="inline-flex h-9 min-w-0 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-[0.75rem] border px-3.5 text-sm font-semibold tracking-[-0.01em] text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-2 focus:ring-offset-[#f4f6fb] disabled:pointer-events-none disabled:opacity-50"
                                style="border-color: var(--theme-accent); background-color: var(--theme-accent);"
                            >
                                <i class="fa-light fa-play"></i>
                                {{ __('Run Now') }}
                            </button>

                            <button
                                type="button"
                                wire:click.prevent="toggleSchedule('{{ $schedule->id_secure }}')"
                                wire:loading.attr="disabled"
                                class="inline-flex h-9 min-w-0 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-[0.75rem] border bg-transparent px-3.5 text-sm font-semibold tracking-[-0.01em] text-[var(--theme-header-text-color)] shadow-sm transition-all duration-200 hover:-translate-y-px hover:shadow-[0_14px_28px_-22px_rgba(15,23,42,0.3)] focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-2 focus:ring-offset-[#f4f6fb] disabled:pointer-events-none disabled:opacity-50"
                                style="border-color: var(--theme-border-color);"
                            >
                                <i class="fa-light {{ $schedule->status ? 'fa-pause' : 'fa-play' }}"></i>
                                {{ $schedule->status ? __('Pause') : __('Resume') }}
                            </button>
                        </div>

                    </div>
                </article>
                <x-ui.modal
                    width="xl"
                    :title="__('RSS analytics')"
                    :description="__('Queued and published activity for this schedule over the last seven days.')"
                >
                    <x-slot:trigger>
                        <button type="button" id="rss-analytics-trigger-{{ $schedule->id }}" class="hidden"></button>
                    </x-slot:trigger>

                    <div class="space-y-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Performance band') }}</p>
                                <h3 class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('7-day activity') }}</h3>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: rgba(79, 70, 229, 0.18); background-color: rgba(79, 70, 229, 0.08); color: #4f46e5;">
                                    <span class="h-2 w-2 rounded-full" style="background-color: #4f46e5;"></span>
                                    {{ __('Queued: :count', ['count' => number_format($weeklyQueued)]) }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: rgba(16, 185, 129, 0.18); background-color: rgba(16, 185, 129, 0.08); color: #059669;">
                                    <span class="h-2 w-2 rounded-full" style="background-color: #10b981;"></span>
                                    {{ __('Published: :count', ['count' => number_format($weeklyPublished)]) }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_16rem]">
                            <div class="overflow-hidden rounded-[1.2rem] border px-4 py-4 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background:
                                radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 32%),
                                linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.92));
                            ">
                                @if ($trendChart)
                                    <x-ui.chart
                                        :id="'rss-schedule-trend-'.$schedule->id"
                                        type="line"
                                        :categories="$trendChart['categories']"
                                        :series="$trendChart['series']"
                                        :options="$trendChart['options']"
                                        :height="300"
                                        class="border-0 p-0 shadow-none"
                                    />
                                @else
                                    <div class="flex h-full items-center justify-center text-sm" style="color: var(--theme-muted-text-color);">
                                        {{ __('No queue history yet.') }}
                                    </div>
                                @endif
                            </div>

                            <div class="grid gap-3">
                                <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Today window') }}</p>
                                    <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($latestQueued) }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Queued items recorded in the latest chart point.') }}</p>
                                </div>

                                <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Delivery') }}</p>
                                    <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: #059669;">{{ number_format($latestPublished) }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Published posts at the latest chart point.') }}</p>
                                </div>

                                <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background:
                                    linear-gradient(180deg, rgba(79, 70, 229, 0.08), rgba(16, 185, 129, 0.05));
                                ">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Output health') }}</p>
                                    <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $deliveryRate }}%</p>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.16);">
                                        <div class="h-full rounded-full" style="width: {{ $deliveryRate }}%; background: linear-gradient(90deg, #4f46e5, #10b981);"></div>
                                    </div>
                                    <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Share of RSS-generated posts that ended up published instead of failed.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.modal>

                <x-ui.dialog
                    width="lg"
                    dismissible
                    :title="__('RSS Log')"
                    :description="__('Recent RSS items processed for this schedule.')"
                >
                    <x-slot:trigger>
                        <button type="button" id="rss-log-trigger-{{ $schedule->id }}" class="hidden"></button>
                    </x-slot:trigger>

                    <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1">
                        @if ($aiRewriteEnabled)
                            <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(124, 58, 237, 0.16); background:
                                linear-gradient(180deg, rgba(124, 58, 237, 0.05), rgba(var(--theme-surface-base-rgb,255,255,255),0.9));
                            ">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5" style="border-color: rgba(124, 58, 237, 0.16); background-color: rgba(124, 58, 237, 0.08); color: #6d28d9;">
                                        <i class="fa-light fa-sparkles text-[11px]"></i>
                                        {{ __('AI rewrite enabled') }}
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.44); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8); color: var(--theme-muted-text-color);">
                                        {{ __(':credits credit per rewrite', ['credits' => $aiRewriteCreditPreview['unit_cost'] ?? 0]) }}
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.44); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8); color: var(--theme-muted-text-color);">
                                        {{ $aiPerRunLimit > 0 ? __('max :count/run', ['count' => number_format($aiPerRunLimit)]) : __('unlimited/run') }}
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.44); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8); color: var(--theme-muted-text-color);">
                                        {{ $aiMonthlyLimit > 0 ? __(':used/:limit month', ['used' => number_format($aiCreditsUsed), 'limit' => number_format($aiMonthlyLimit)]) : __(':used this month', ['used' => number_format($aiCreditsUsed)]) }}
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.44); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8); color: var(--theme-muted-text-color);">
                                        {{ ($aiRewriteCreditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => number_format((int) ($aiRewriteCreditPreview['remaining'] ?? 0))]) }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        @forelse ($recentLogs as $log)
                            @php
                                $postStatus = (int) optional($log->publishingPost)->status;
                                $rewriteState = (array) data_get($log->publishingPost?->data, 'options.rss_ai_rewrite', []);
                                $rewriteStatus = (string) ($rewriteState['status'] ?? '');
                                $rewriteMessage = trim((string) ($rewriteState['message'] ?? ''));
                                $rewriteCreditsUsed = (int) ($rewriteState['credits_used'] ?? 0);
                                $statusLabel = match (true) {
                                    $log->publishingPost === null => __('Deleted'),
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_PUBLISHED => __('Published'),
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_FAILED => __('Failed'),
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_PROCESSING => __('Processing'),
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_SCHEDULED => __('Queued'),
                                    default => __('Draft'),
                                };
                                $statusColor = match (true) {
                                    $log->publishingPost === null => 'var(--theme-muted-text-color)',
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_PUBLISHED => '#059669',
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_FAILED => 'var(--theme-danger-color)',
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_PROCESSING => '#0284c7',
                                    $postStatus === \Modules\AppPublishing\Models\PublishingPost::STATUS_SCHEDULED => '#4f46e5',
                                    default => 'var(--theme-muted-text-color)',
                                };
                                $queuedAt = $log->queued_at ? \Carbon\Carbon::createFromTimestamp((int) $log->queued_at, $timezone)->format('d/m/Y H:i') : __('Unknown');
                            @endphp
                            <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.44); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $log->title ?: __('Untitled RSS item') }}</p>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: var(--theme-muted-text-color);">
                                            <span>{{ $queuedAt }}</span>
                                            <span>#{{ $log->account_id }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold" style="border-color: color-mix(in srgb, {{ $statusColor }} 18%, rgba(var(--theme-border-color-rgb), 0.4)); color: {{ $statusColor }}; background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                @if (filled($log->external_url))
                                    <a href="{{ $log->external_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-xs font-medium" style="color: var(--theme-accent);">
                                        <i class="fa-light fa-arrow-up-right-from-square text-[11px]"></i>
                                        <span class="truncate">{{ __('Open article') }}</span>
                                    </a>
                                @endif

                                @if ($aiRewriteEnabled && $rewriteStatus !== '')
                                    @php
                                        $rewriteLabel = match ($rewriteStatus) {
                                            'rewritten' => __('AI rewritten'),
                                            'skipped_per_run_limit' => __('Skipped: run limit'),
                                            'skipped_monthly_limit' => __('Skipped: monthly limit'),
                                            'skipped_no_credits' => __('Skipped: no credits'),
                                            'fallback_error' => __('Fallback: AI failed'),
                                            'fallback_empty' => __('Fallback: empty output'),
                                            default => __('AI not used'),
                                        };
                                        $rewriteColor = match ($rewriteStatus) {
                                            'rewritten' => '#6d28d9',
                                            'skipped_no_credits', 'skipped_monthly_limit', 'skipped_per_run_limit' => '#b45309',
                                            'fallback_error', 'fallback_empty' => 'var(--theme-danger-color)',
                                            default => 'var(--theme-muted-text-color)',
                                        };
                                    @endphp
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px]">
                                        <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 font-semibold" style="border-color: color-mix(in srgb, {{ $rewriteColor }} 18%, rgba(var(--theme-border-color-rgb), 0.4)); color: {{ $rewriteColor }}; background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                                            <i class="fa-light fa-sparkles text-[10px]"></i>
                                            {{ $rewriteLabel }}
                                        </span>
                                        @if ($rewriteCreditsUsed > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 font-semibold" style="border-color: rgba(124, 58, 237, 0.16); background-color: rgba(124, 58, 237, 0.08); color: #6d28d9;">
                                                {{ __(':credits credit', ['credits' => number_format($rewriteCreditsUsed)]) }}
                                            </span>
                                        @endif
                                        @if ($rewriteMessage !== '')
                                            <span style="color: var(--theme-muted-text-color);">{{ $rewriteMessage }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-[1rem] border border-dashed px-4 py-8 text-center text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.44); color: var(--theme-muted-text-color);">
                                {{ __('No RSS log yet.') }}
                            </div>
                        @endforelse
                    </div>
                </x-ui.dialog>

                <x-ui.dialog
                    width="sm"
                    dismissible
                    :title="__('Delete this RSS schedule?')"
                    :description="__('This removes the RSS schedule and keeps only the remaining publishing history.')"
                >
                    <x-slot:trigger>
                        <button type="button" id="rss-delete-trigger-{{ $schedule->id }}" class="hidden"></button>
                    </x-slot:trigger>

                    <x-slot:footer>
                        <div class="flex justify-end gap-3">
                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                            <x-ui.button type="button" variant="danger" wire:click="deleteSchedule('{{ $schedule->id_secure }}')" x-on:click="open = false">
                                {{ __('Delete') }}
                            </x-ui.button>
                        </div>
                    </x-slot:footer>
                </x-ui.dialog>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3">
                    <div class="rounded-[1.7rem] border border-dashed px-6 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.45);">
                        <x-ui.empty
                            icon="fa-light fa-square-rss"
                            :title="__('No RSS schedules found.')"
                            :description="__('Create your first RSS schedule to start turning feed items into queued publishing posts.')"
                        />

                        <div class="mt-5">
                            <x-ui.button :href="route('portal.rss-schedules.create')" size="lg">
                                <i class="fa-light fa-plus"></i>
                                {{ __('Add RSS Schedule') }}
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            @endforelse
    </div>

    @if ($schedules->hasPages())
        <div>{{ $schedules->links() }}</div>
    @endif
</div>
