@props([
    'sections' => [],
    'mode' => 'desktop',
])

@php
    $icons = [
        'dashboard' => 'fa-light fa-house',
        'blogs' => 'fa-light fa-rss',
        'faq' => 'fa-light fa-messages-question',
        'support' => 'fa-light fa-life-ring',
        'mail' => 'fa-light fa-envelopes-bulk',
        'notification' => 'fa-light fa-bell',
        'proxy' => 'fa-light fa-hard-drive',
        'ai-report' => 'fa-light fa-chart-mixed',
        'ai-template' => 'fa-light fa-brain-circuit',
        'plans' => 'fa-light fa-box-open',
        'money' => 'fa-light fa-coins',
        'coupon' => 'fa-light fa-ticket-percent',
        'affiliate' => 'fa-light fa-handshake-angle',
        'users' => 'fa-light fa-users',
        'user-report' => 'fa-light fa-chart-user',
        'themes' => 'fa-light fa-swatchbook',
        'settings' => 'fa-light fa-sliders',
    ];
@endphp

@if ($mode === 'mobile')
    <div class="mt-6 space-y-4">
        @foreach ($sections as $section)
            @php
                $sectionItems = collect($section['items'] ?? [])
                    ->flatMap(function ($item) {
                        if (! empty($item['children']) && is_array($item['children'])) {
                            return collect($item['children'])->map(function ($child) use ($item) {
                                $child['mobile_icon'] = $item['icon'] ?? 'dashboard';

                                return $child;
                            });
                        }

                        $item['mobile_icon'] = $item['icon'] ?? 'dashboard';

                        return [$item];
                    })
                    ->values();
            @endphp

            <section class="{{ $loop->first ? '' : 'border-t pt-4' }}" style="{{ $loop->first ? '' : 'border-color: rgba(var(--theme-border-color-rgb), 0.4);' }}">
                @if (! empty($section['label']))
                    <p class="px-2 text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">
                        {{ __($section['label']) }}
                    </p>
                @endif

                <div class="mt-2 space-y-1">
                    @foreach ($sectionItems as $mobileLink)
                        @php
                            $iconKey = $mobileLink['mobile_icon'] ?? 'dashboard';
                            $icon = str_starts_with((string) $iconKey, 'fa-')
                                ? $iconKey
                                : ($icons[$iconKey] ?? $icons['dashboard']);
                            $isActive = (bool) ($mobileLink['active'] ?? false);
                            $isDisabled = (bool) ($mobileLink['disabled'] ?? false);
                            $href = $isDisabled ? '#' : ($mobileLink['route'] ?? '#');
                            $wireNavigate = ! $isDisabled && ! empty($mobileLink['route']);
                            $linkClass = 'group relative flex h-11 items-center rounded-xl pl-[52px] pr-3 text-[14px] font-medium tracking-[0.005em] transition-colors duration-150';
                            $linkClass .= $isDisabled ? ' cursor-default opacity-60' : '';
                            $linkStyle = $isActive
                                ? 'background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);'
                                : 'color: var(--theme-sidebar-text-color);';
                            $iconWrapStyle = $isActive
                                ? 'border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);'
                                : 'background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-sidebar-text-color);';
                        @endphp

                        @if ($wireNavigate)
                            <a
                                href="{{ $href }}"
                                wire:navigate
                                class="{{ $linkClass }}"
                                style="{{ $linkStyle }}"
                            >
                                <span
                                    class="absolute left-[10px] top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    style="{{ $iconWrapStyle }}"
                                >
                                    <i class="{{ $icon }} fa-fw text-[15px] leading-none"></i>
                                </span>
                                <span class="truncate">{{ __($mobileLink['label'] ?? '') }}</span>
                            </a>
                        @else
                            <a
                                href="{{ $href }}"
                                class="{{ $linkClass }}"
                                style="{{ $linkStyle }}"
                            >
                                <span
                                    class="absolute left-[10px] top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    style="{{ $iconWrapStyle }}"
                                >
                                    <i class="{{ $icon }} fa-fw text-[15px] leading-none"></i>
                                </span>
                                <span class="truncate">{{ __($mobileLink['label'] ?? '') }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@else
    @foreach ($sections as $section)
        <section class="{{ $loop->first ? '' : 'mt-2.5 border-t border-slate-300/65 pt-2.5 dark:border-slate-800' }}" @if (! $loop->first) style="border-color: var(--theme-border-color);" @endif>
            @if (! empty($section['label']))
                <div class="h-5 px-2.5">
                    <p class="overflow-hidden text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500/90"
                        x-cloak
                        x-show="sidebarContentVisible"
                        x-transition:enter="transition ease-out duration-140"
                        x-transition:enter-start="opacity-0 -translate-x-1"
                        x-transition:enter-end="opacity-100 translate-x-0">
                        {{ __($section['label']) }}
                    </p>

                    <div class="flex justify-center overflow-hidden"
                        x-cloak
                        x-show="!sidebarContentVisible"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100">
                        <span class="text-slate-400">...</span>
                    </div>
                </div>
            @endif

            <div class="mt-1 space-y-px">
                @foreach ($section['items'] as $item)
                    @php
                        $hasChildren = ! empty($item['children']);
                        $isActive = (bool) ($item['active'] ?? false);
                        $isCurrent = $isActive || collect($item['children'] ?? [])->contains(fn ($child) => $child['active'] ?? false);
                        $isDisabled = (bool) ($item['disabled'] ?? false);
                        $iconKey = $item['icon'] ?? 'dashboard';
                        $icon = str_starts_with($iconKey, 'fa-')
                            ? $iconKey
                            : ($icons[$iconKey] ?? $icons['dashboard']);
                    @endphp

                    @if ($hasChildren)
                        <div x-data="{ open: {{ $isCurrent ? 'true' : 'false' }} }" class="rounded-xl">
                            <button
                                type="button"
                                class="group relative flex w-full items-center text-left transition-colors duration-150"
                                x-on:click="!sidebarContentVisible ? null : open = ! open"
                                x-bind:class="sidebarContentVisible
                                    ? '{{ $isCurrent ? 'h-10 rounded-xl pl-[45px] pr-3 text-slate-950 dark:text-white' : 'h-10 rounded-xl pl-[45px] pr-3 text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white' }}'
                                    : '{{ $isCurrent ? 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-950 shadow-none ring-0 dark:text-white' : 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-600 shadow-none ring-0 dark:text-slate-300' }}'"
                                title="{{ __($item['label']) }}"
                            >
                                <span class="absolute left-[7px] top-1/2 inline-flex h-[1.875rem] w-[1.875rem] -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    x-bind:class="sidebarContentVisible
                                        ? '{{ $isCurrent ? 'shadow-[0_10px_18px_-16px_rgba(var(--theme-accent-rgb),0.32)]' : 'bg-transparent text-slate-500 group-hover:border-[color:rgba(var(--theme-accent-rgb),0.16)] group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.12)] group-hover:text-[var(--theme-accent)] dark:text-slate-300 dark:group-hover:border-[color:rgba(var(--theme-accent-rgb),0.22)] dark:group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.16)] dark:group-hover:text-[var(--theme-accent)]' }}'
                                        : '{{ $isCurrent ? 'bg-[var(--theme-accent)] text-white shadow-[0_10px_18px_-14px_rgba(var(--theme-accent-rgb),0.65)] dark:bg-[var(--theme-accent)] dark:text-white' : 'bg-transparent text-slate-600 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white' }}'"
                                    @if ($isCurrent)
                                        x-bind:style="sidebarContentVisible ? 'border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);' : ''"
                                    @endif>
                                    <i class="{{ $icon }} fa-fw text-[16px] leading-none"></i>
                                </span>
                                <span class="min-w-0 truncate text-[13.5px] font-medium tracking-[0.005em] {{ $isCurrent ? 'text-slate-950 dark:text-white' : 'text-slate-700 dark:text-slate-300' }}"
                                    x-cloak
                                    x-show="sidebarContentVisible"
                                    x-transition:enter="transition ease-out duration-140"
                                    x-transition:enter-start="opacity-0 -translate-x-1.5"
                                    x-transition:enter-end="opacity-100 translate-x-0">{{ __($item['label']) }}</span>
                                <span class="ml-auto inline-flex h-5 w-5 items-center justify-center text-slate-500"
                                    x-cloak
                                    x-show="sidebarContentVisible"
                                    x-transition:enter="transition ease-out duration-120"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100">
                                    <i class="fa-solid text-[10px]" :class="open ? 'fa-minus' : 'fa-plus'"></i>
                                </span>
                            </button>

                            <div class="relative ml-[1.75rem] mt-1 space-y-1 overflow-hidden pl-4 before:absolute before:bottom-1.5 before:left-0 before:top-1.5 before:w-px before:bg-slate-300/75 dark:before:bg-slate-700"
                                x-cloak
                                x-show="open && sidebarContentVisible"
                                x-transition:enter="transition ease-out duration-160"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0">
                                @foreach ($item['children'] as $child)
                                    @php($childDisabled = (bool) ($child['disabled'] ?? false))
                                    <a
                                        href="{{ $childDisabled ? '#' : ($child['route'] ?? '#') }}"
                                        @if (! $childDisabled && ! empty($child['route'])) wire:navigate @endif
                                        class="{{ ($child['active'] ?? false) ? 'text-slate-900 dark:text-white' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }} group relative flex items-center rounded-lg px-3 py-1.5 text-[12.5px] font-medium tracking-[0.005em] transition {{ $childDisabled ? 'cursor-default opacity-60' : '' }}"
                                    >
                                        <span class="absolute left-0 top-1/2 h-px w-3 -translate-x-[1rem] -translate-y-1/2 {{ ($child['active'] ?? false) ? 'bg-slate-400 dark:bg-slate-500' : 'bg-slate-300/90 dark:bg-slate-700' }}"></span>
                                        <span class="truncate">{{ __($child['label']) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ $isDisabled ? '#' : ($item['route'] ?? '#') }}"
                            @if (! $isDisabled && ! empty($item['route'])) wire:navigate @endif
                            class="group relative flex items-center transition-colors duration-150 {{ $isDisabled ? 'cursor-default opacity-60' : '' }}"
                            x-bind:class="sidebarContentVisible
                                ? '{{ $isCurrent ? 'h-10 rounded-xl pl-[45px] pr-3 text-slate-950 dark:text-white' : 'h-10 rounded-xl pl-[45px] pr-3 text-slate-600 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white' }}'
                                : '{{ $isCurrent ? 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-950 shadow-none ring-0 dark:text-white' : 'h-10 rounded-none bg-transparent pl-[7px] pr-0 text-slate-600 shadow-none ring-0 dark:text-slate-300' }}'"
                            title="{{ __($item['label']) }}"
                        >
                                <span class="absolute left-[7px] top-1/2 inline-flex h-[1.875rem] w-[1.875rem] -translate-y-1/2 items-center justify-center rounded-lg border border-transparent transition-colors duration-100"
                                    x-bind:class="sidebarContentVisible
                                    ? '{{ $isCurrent ? 'shadow-[0_10px_18px_-16px_rgba(var(--theme-accent-rgb),0.32)]' : 'bg-transparent text-slate-500 group-hover:border-[color:rgba(var(--theme-accent-rgb),0.16)] group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.12)] group-hover:text-[var(--theme-accent)] dark:text-slate-300 dark:group-hover:border-[color:rgba(var(--theme-accent-rgb),0.22)] dark:group-hover:bg-[color:rgba(var(--theme-accent-rgb),0.16)] dark:group-hover:text-[var(--theme-accent)]' }}'
                                    : '{{ $isCurrent ? 'bg-[var(--theme-accent)] text-white shadow-[0_10px_18px_-14px_rgba(var(--theme-accent-rgb),0.65)] dark:bg-[var(--theme-accent)] dark:text-white' : 'bg-transparent text-slate-600 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white' }}'"
                                @if ($isCurrent)
                                    x-bind:style="sidebarContentVisible ? 'border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);' : ''"
                                @endif>
                                <i class="{{ $icon }} fa-fw text-[16px] leading-none"></i>
                            </span>
                            <span class="min-w-0 truncate text-[13.5px] font-medium tracking-[0.005em] {{ $isCurrent ? 'text-slate-950 dark:text-white' : 'text-slate-700 dark:text-slate-300' }}"
                                x-cloak
                                x-show="sidebarContentVisible"
                                x-transition:enter="transition ease-out duration-140"
                                x-transition:enter-start="opacity-0 -translate-x-1.5"
                                x-transition:enter-end="opacity-100 translate-x-0">{{ __($item['label']) }}</span>
                            @if (! empty($item['suffix']))
                                <span class="text-[12px] text-slate-400 group-hover:text-slate-500"
                                    x-cloak
                                    x-show="sidebarContentVisible"
                                    x-transition:enter="transition ease-out duration-120"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100">{{ $item['suffix'] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach
@endif
