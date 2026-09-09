<div class="space-y-8 px-4 pb-10 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
    ">
        <div class="grid gap-6 px-6 py-7 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('AI content studio') }}</p>
                <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('AI Publishing Schedules') }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Manage recurring AI publishing schedules separately from the create/edit workflow. Each schedule can generate content and push valid posts into Publishing.') }}</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <x-ui.button :href="route('portal.ai-publishing.create')" size="lg">
                    <i class="fa-light fa-plus"></i>
                    {{ __('Create AI Publishing') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <x-ui.metric-strip
        columns="md:grid-cols-2 xl:grid-cols-5"
        gap="gap-5"
        :card-style="'border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96); min-height: 12rem;'"
        :progress-track-style="'background-color: rgba(var(--theme-border-color-rgb), 0.18);'"
        :items="[
            ['label' => __('Total'), 'value' => number_format($summary['total']), 'description' => __('All AI publishing schedules.'), 'tone' => '#7c3aed', 'icon' => 'fa-light fa-sparkles', 'cardStyle' => 'background: linear-gradient(180deg, rgba(124, 58, 237, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(124, 58, 237, 0.16);', 'iconSurface' => 'linear-gradient(145deg, rgba(124, 58, 237, 0.14), rgba(255,255,255,0.96));', 'iconBorder' => 'rgba(124, 58, 237, 0.18)', 'progressTrackStyle' => 'background-color: rgba(124, 58, 237, 0.12);'],
            ['label' => __('Running'), 'value' => number_format($summary['running']), 'description' => __('Schedules still active within their date range.'), 'tone' => '#059669', 'icon' => 'fa-light fa-loader', 'cardStyle' => 'background: linear-gradient(180deg, rgba(16, 185, 129, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(16, 185, 129, 0.16);', 'iconSurface' => 'linear-gradient(145deg, rgba(16, 185, 129, 0.14), rgba(255,255,255,0.96));', 'iconBorder' => 'rgba(16, 185, 129, 0.18)', 'progressTrackStyle' => 'background-color: rgba(16, 185, 129, 0.12);'],
            ['label' => __('Completed'), 'value' => number_format($summary['completed']), 'description' => __('Schedules that reached their end date.'), 'tone' => '#0f766e', 'icon' => 'fa-light fa-circle-check', 'cardStyle' => 'background: linear-gradient(180deg, rgba(15, 118, 110, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(15, 118, 110, 0.16);', 'iconSurface' => 'linear-gradient(145deg, rgba(15, 118, 110, 0.14), rgba(255,255,255,0.96));', 'iconBorder' => 'rgba(15, 118, 110, 0.18)', 'progressTrackStyle' => 'background-color: rgba(15, 118, 110, 0.12);'],
            ['label' => __('Paused'), 'value' => number_format($summary['paused']), 'description' => __('Schedules whose publishing posts are temporarily paused.'), 'tone' => '#ea580c', 'icon' => 'fa-light fa-circle-pause', 'cardStyle' => 'background: linear-gradient(180deg, rgba(249, 115, 22, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(249, 115, 22, 0.16);', 'iconSurface' => 'linear-gradient(145deg, rgba(249, 115, 22, 0.14), rgba(255,255,255,0.96));', 'iconBorder' => 'rgba(249, 115, 22, 0.18)', 'progressTrackStyle' => 'background-color: rgba(249, 115, 22, 0.12);'],
            ['label' => __('Failed'), 'value' => number_format($summary['failed']), 'description' => __('Schedules with failed generation items.'), 'tone' => '#dc2626', 'icon' => 'fa-light fa-circle-exclamation', 'cardStyle' => 'background: linear-gradient(180deg, rgba(239, 68, 68, 0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.98) 42%); border-color: rgba(239, 68, 68, 0.16);', 'iconSurface' => 'linear-gradient(145deg, rgba(239, 68, 68, 0.14), rgba(255,255,255,0.96));', 'iconBorder' => 'rgba(239, 68, 68, 0.18)', 'progressTrackStyle' => 'background-color: rgba(239, 68, 68, 0.12);'],
        ]"
    />

    <div class="rounded-[1.45rem] border px-4 py-4 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
        radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 28%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-soft-rgb,248,250,252),0.8));
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.04);
    ">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-end">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-magnifying-glass text-sm"></i>
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Search schedules') }}</p>
                        <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Filter runs by schedule name.') }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    <x-ui.input wire:model.live.debounce.300ms="search" :placeholder="__('Search AI publishing schedules...')" />
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-[14rem_auto_auto] xl:w-auto xl:items-end">
                <div class="min-w-0">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Status') }}</p>
                    <x-ui.select wire:model.live="status">
                        @foreach ($statusOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.button type="button" size="sm" class="h-11 px-5" wire:click="$refresh">{{ __('Apply') }}</x-ui.button>
                <x-ui.button type="button" variant="outline" size="sm" class="h-11 px-5" wire:click="resetFilters">{{ __('Reset') }}</x-ui.button>
            </div>
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($runs as $run)
            @php
                $badge = $this->statusBadge($run);
                $itemsCount = collect((array) $run->prompt_ids)->filter()->count();
                $createdPosts = (int) data_get($runMetrics, $run->id.'.posts', 0);
                $generatedItems = (int) data_get($runMetrics, $run->id.'.generated', 0);
                $failedItems = (int) data_get($runMetrics, $run->id.'.failed', 0);
                $failedPrompts = collect((array) data_get($run->stats, 'failed_prompts', []))->filter(fn ($item) => filled(data_get($item, 'message')));
                $runLogs = collect((array) data_get($run->stats, 'run_logs', []))->filter(fn ($item) => is_array($item))->values();
                $successRatio = $createdPosts > 0 ? min(100, max(0, (int) round((($createdPosts - $failedItems) / max(1, $createdPosts)) * 100))) : 0;
                $weekdayOrder = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
                $weekdays = collect((array) data_get($run->schedule_config, 'weekdays', []))
                    ->map(fn ($day) => ucfirst(strtolower((string) $day)))
                    ->filter()
                    ->sortBy(fn ($day) => array_search(strtolower($day), $weekdayOrder, true))
                    ->values();
                $nextRunLabel = $nextRunLabels[$run->id] ?? __('No next run');
                $runTimezone = (string) data_get($run->schedule_config, 'timezone', config('app.timezone'));
            @endphp

            <div class="h-full">
                <article class="group relative z-0 flex h-full flex-col overflow-visible rounded-[1.7rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.64); background:
                    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.08), transparent 24%),
                    linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.99), rgba(var(--theme-surface-soft-rgb,248,250,252),0.84));
                    box-shadow: 0 22px 50px rgba(15, 23, 42, 0.045);
                ">
                    <div class="flex h-full flex-col gap-3.5 px-6 py-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-4">
                            <span class="inline-flex h-12 w-12 flex-none items-center justify-center rounded-[1.1rem] border" style="border-color: rgba(124, 58, 237, 0.18); background:
                                radial-gradient(circle at 30% 30%, rgba(221, 214, 254, 0.95), rgba(196, 181, 253, 0.88) 58%, rgba(167, 139, 250, 0.82) 100%);
                                color: #ffffff;
                                box-shadow: inset 0 1px 0 rgba(255,255,255,0.35), 0 10px 24px rgba(124, 58, 237, 0.18);
                            ">
                                <i class="fa-solid fa-sparkles text-[1rem]"></i>
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('AI automation') }}</p>
                                <p class="mt-1 truncate text-[1.15rem] font-semibold tracking-[-0.05em] leading-tight" style="color: var(--theme-header-text-color);">{{ $run->name ?: __('AI Publishing Schedule') }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: color-mix(in srgb, {{ $badge['color'] }} 18%, rgba(var(--theme-border-color-rgb), 0.42)); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72); color: {{ $badge['color'] }};">
                                        <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $badge['color'] }};"></span>
                                        {{ $badge['label'] }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72); color: var(--theme-muted-text-color);">
                                        {{ number_format($itemsCount) }} {{ __('prompts') }}
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

                            <button type="button" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false; document.getElementById('ai-analytics-trigger-{{ $run->id }}')?.click()">
                                <i class="fa-light fa-chart-mixed text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                <span class="min-w-0 flex-1 truncate">{{ __('View Analytics') }}</span>
                            </button>

                            @if ($this->canEditRun($run))
                                <a href="{{ route('portal.ai-publishing.edit', $run) }}" wire:navigate class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false">
                                    <i class="fa-light fa-pen-to-square text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Edit Setup') }}</span>
                                </a>
                            @else
                                <div class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 opacity-55" style="color: var(--theme-muted-text-color); cursor: not-allowed;">
                                    <i class="fa-light fa-pen-to-square text-[14px]"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Edit Setup') }}</span>
                                </div>
                            @endif

                            <a href="{{ route('portal.publishing.calendar') }}" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false">
                                <i class="fa-light fa-arrow-up-right-from-square text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                <span class="min-w-0 flex-1 truncate">{{ __('Open Publishing') }}</span>
                            </a>

                            <button type="button" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-header-text-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: var(--theme-surface-soft); color: var(--theme-header-text-color);' : 'color: var(--theme-header-text-color);'" x-on:click.stop="open = false; document.getElementById('ai-log-trigger-{{ $run->id }}')?.click()">
                                <i class="fa-light fa-rectangle-history text-[14px]" style="color: var(--theme-muted-text-color);"></i>
                                <span class="min-w-0 flex-1 truncate">{{ __('View Run Log') }}</span>
                            </button>

                            <div class="my-1 border-t" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent);"></div>

                            @if ($this->canDeleteRun($run))
                                <button type="button" class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 transition" style="color: var(--theme-danger-color);" x-data="{ hover: false }" x-on:mouseenter="hover = true" x-on:mouseleave="hover = false" x-bind:style="hover ? 'background-color: rgba(244, 63, 94, 0.08); color: var(--theme-danger-color);' : 'color: var(--theme-danger-color);'" x-on:click.stop="open = false; document.getElementById('ai-delete-trigger-{{ $run->id }}')?.click()">
                                    <i class="fa-light fa-trash text-[14px]" style="color: var(--theme-danger-color);"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Delete Schedule') }}</span>
                                </button>
                            @else
                                <div class="flex w-full items-center gap-3 rounded-[0.8rem] px-3 py-2 text-left text-[15px] font-medium leading-6 opacity-55" style="color: var(--theme-danger-color); cursor: not-allowed;">
                                    <i class="fa-light fa-trash text-[14px]"></i>
                                    <span class="min-w-0 flex-1 truncate">{{ __('Delete Schedule') }}</span>
                                </div>
                            @endif
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
                                    <p class="mt-0.5 font-medium" style="color: var(--theme-header-text-color);">{{ $nextRunLabel }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 border-t px-3.5 py-3 sm:border-l sm:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.32);">
                                <span class="mt-0.5 inline-flex h-7 w-7 flex-none items-center justify-center rounded-full" style="background-color: rgba(14, 165, 233, 0.08); color: #0284c7;">
                                    <i class="fa-light fa-share-nodes text-[11px]"></i>
                                </span>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Channels') }}</span>
                                    <p class="mt-0.5 font-medium truncate" style="color: var(--theme-header-text-color);">{{ number_format(count((array) $run->account_ids)) }} {{ __('selected') }}</p>
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

                    <div class="grid gap-2.5 sm:grid-cols-3">
                        <div class="rounded-[0.9rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Generated') }}</p>
                            <p class="mt-1 text-[1.4rem] font-semibold tracking-[-0.05em]" style="color: #7c3aed;">{{ number_format($generatedItems) }}</p>
                        </div>
                        <div class="rounded-[0.9rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Posts') }}</p>
                            <p class="mt-1 text-[1.4rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($createdPosts) }}</p>
                        </div>
                        <div class="rounded-[0.9rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Failed') }}</p>
                            <p class="mt-1 text-[1.4rem] font-semibold tracking-[-0.05em]" style="color: {{ $failedItems > 0 ? 'var(--theme-danger-color)' : 'var(--theme-header-text-color)' }};">{{ number_format($failedItems) }}</p>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center gap-2 pt-1">
                        @if ($this->canRunNow($run))
                            <x-ui.dialog width="sm" dismissible :title="__('Run AI publishing now?')" :description="__('This will generate content immediately and try to publish it to the selected channel right away.')">
                                <x-slot:trigger>
                                    <button
                                        type="button"
                                        class="inline-flex h-9 min-w-0 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-[0.75rem] border px-3.5 text-sm font-semibold tracking-[-0.01em] text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-2 focus:ring-offset-[#f4f6fb] disabled:pointer-events-none disabled:opacity-50"
                                        style="border-color: var(--theme-accent); background-color: var(--theme-accent);"
                                    >
                                        <i class="fa-light fa-play"></i>
                                        {{ __('Run Now') }}
                                    </button>
                                </x-slot:trigger>

                                <x-slot:footer>
                                    <div class="flex justify-end gap-3">
                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                        <x-ui.button type="button" wire:click="runNow({{ $run->id }})" wire:loading.attr="disabled" x-on:click="open = false">
                                            <i class="fa-light fa-play"></i>
                                            {{ __('Run Now') }}
                                        </x-ui.button>
                                    </div>
                                </x-slot:footer>
                            </x-ui.dialog>
                        @else
                            <div class="inline-flex h-9 min-w-0 flex-1 items-center justify-center rounded-[0.75rem] border px-3.5 text-sm font-semibold tracking-[-0.01em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); color: var(--theme-muted-text-color); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.62);">
                                {{ __('Processing') }}
                            </div>
                        @endif

                        @if (in_array($run->id, $resumableRunIds ?? [], true))
                            <button
                                type="button"
                                wire:click="startRun({{ $run->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex h-9 min-w-0 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-[0.75rem] border bg-transparent px-3.5 text-sm font-semibold tracking-[-0.01em] text-[var(--theme-header-text-color)] shadow-sm transition-all duration-200 hover:-translate-y-px hover:shadow-[0_14px_28px_-22px_rgba(15,23,42,0.3)] focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-2 focus:ring-offset-[#f4f6fb] disabled:pointer-events-none disabled:opacity-50"
                                style="border-color: var(--theme-border-color);"
                            >
                                <i class="fa-light fa-play"></i>
                                {{ __('Resume') }}
                            </button>
                        @elseif ($this->canStopRun($run))
                            <button
                                type="button"
                                wire:click="stopRun({{ $run->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex h-9 min-w-0 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-[0.75rem] border bg-transparent px-3.5 text-sm font-semibold tracking-[-0.01em] text-[var(--theme-header-text-color)] shadow-sm transition-all duration-200 hover:-translate-y-px hover:shadow-[0_14px_28px_-22px_rgba(15,23,42,0.3)] focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-2 focus:ring-offset-[#f4f6fb] disabled:pointer-events-none disabled:opacity-50"
                                style="border-color: var(--theme-border-color);"
                            >
                                <i class="fa-light fa-pause"></i>
                                {{ __('Pause') }}
                            </button>
                        @else
                            <div class="inline-flex h-9 min-w-0 flex-1 items-center justify-center rounded-[0.75rem] border px-3.5 text-sm font-semibold tracking-[-0.01em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); color: var(--theme-muted-text-color); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.62);">
                                {{ __('Stopped') }}
                            </div>
                        @endif
                    </div>
                    </div>
                </article>

                <x-ui.modal
                    width="lg"
                    :title="__('AI publishing analytics')"
                    :description="__('Quick schedule stats for this run.')"
                >
                    <x-slot:trigger>
                        <button type="button" id="ai-analytics-trigger-{{ $run->id }}" class="hidden"></button>
                    </x-slot:trigger>

                    <div class="space-y-5">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Next run') }}</p>
                                <p class="mt-2 text-[1.1rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $nextRunLabel }}</p>
                            </div>

                            <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Channels') }}</p>
                                <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format(count((array) $run->account_ids)) }}</p>
                            </div>

                            <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Generated') }}</p>
                                <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: #7c3aed;">{{ number_format($generatedItems) }}</p>
                            </div>

                            <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Posts') }}</p>
                                <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($createdPosts) }}</p>
                            </div>

                            <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.8);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Failed') }}</p>
                                <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: {{ $failedItems > 0 ? 'var(--theme-danger-color)' : 'var(--theme-header-text-color)' }};">{{ number_format($failedItems) }}</p>
                            </div>

                            <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background:
                                linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.08), rgba(16, 185, 129, 0.05));
                            ">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Output health') }}</p>
                                <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $successRatio }}%</p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.16);">
                                    <div class="h-full rounded-full" style="width: {{ $successRatio }}%; background: linear-gradient(90deg, #7c3aed, #10b981);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.modal>

                <x-ui.dialog width="lg" dismissible :title="__('AI Publishing Log')" :description="__('Recent run activity for this schedule, including generation and publish attempts.')">
                    <x-slot:trigger>
                        <button type="button" id="ai-log-trigger-{{ $run->id }}" class="hidden"></button>
                    </x-slot:trigger>

                    <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1">
                        @forelse ($runLogs as $log)
                            @php
                                $level = (string) data_get($log, 'level', 'info');
                                $levelColor = match ($level) {
                                    'success' => '#059669',
                                    'warning' => '#b45309',
                                    'error' => 'var(--theme-danger-color)',
                                    default => 'var(--theme-accent)',
                                };
                                $loggedAt = trim((string) data_get($log, 'logged_at', ''));
                                $loggedAtLabel = $loggedAt !== ''
                                    ? \Carbon\Carbon::parse($loggedAt)->timezone($runTimezone)->format('d/m/Y H:i:s')
                                    : __('Unknown');
                            @endphp
                            <div class="rounded-[1rem] border px-4 py-3" style="border-color: color-mix(in srgb, {{ $levelColor }} 18%, rgba(var(--theme-border-color-rgb), 0.44)); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($log, 'message', __('Activity recorded')) }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: var(--theme-muted-text-color);">
                                            <span>{{ $loggedAtLabel }}</span>
                                            <span>{{ $runTimezone }}</span>
                                            <span>{{ str(data_get($log, 'stage', 'info'))->headline() }}</span>
                                            @if (filled(data_get($log, 'prompt_id')))
                                                <span>#{{ data_get($log, 'prompt_id') }}</span>
                                            @endif
                                            @if (filled(data_get($log, 'account_label')))
                                                <span>{{ data_get($log, 'account_label') }}</span>
                                            @endif
                                            @if (filled(data_get($log, 'post_id')))
                                                <span>{{ __('Post #:id', ['id' => data_get($log, 'post_id')]) }}</span>
                                            @endif
                                        </div>
                                        @if (filled(data_get($log, 'prompt')))
                                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($log, 'prompt') }}</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold" style="border-color: color-mix(in srgb, {{ $levelColor }} 18%, rgba(var(--theme-border-color-rgb), 0.4)); color: {{ $levelColor }}; background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                                        {{ str($level)->headline() }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[1rem] border border-dashed px-4 py-8 text-center text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.44); color: var(--theme-muted-text-color);">
                                {{ __('No run log yet.') }}
                            </div>
                        @endforelse
                    </div>
                </x-ui.dialog>

                @if ($this->canDeleteRun($run))
                    <x-ui.dialog width="sm" dismissible :title="__('Delete this AI publishing schedule?')" :description="__('This removes the schedule and keeps published history.')">
                        <x-slot:trigger>
                            <button type="button" id="ai-delete-trigger-{{ $run->id }}" class="hidden"></button>
                        </x-slot:trigger>

                        <x-slot:footer>
                            <div class="flex justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="button" variant="danger" wire:click="deleteRun({{ $run->id }})" x-on:click="open = false">
                                    {{ __('Delete') }}
                                </x-ui.button>
                            </div>
                        </x-slot:footer>
                    </x-ui.dialog>
                @endif
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3">
                <div class="rounded-[1.7rem] border border-dashed px-6 py-10 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.45);">
                    <x-ui.empty icon="fa-light fa-sparkles" :title="__('No AI publishing schedules found.')" :description="__('Create your first AI publishing schedule to start generating posts automatically.')" />

                    <div class="mt-5">
                        <x-ui.button :href="route('portal.ai-publishing.create')" size="lg">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Create AI Publishing') }}
                        </x-ui.button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($runs->hasPages())
        <div>{{ $runs->links() }}</div>
    @endif
</div>
