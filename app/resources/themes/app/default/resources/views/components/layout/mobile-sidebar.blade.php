@props([
    'sections' => [],
    'dashboardSwitch' => null,
    'planCard' => null,
])

<div
    x-cloak
    x-show="sidebarOpen"
    class="fixed inset-0 lg:hidden"
    style="z-index: 140;"
    x-on:keydown.escape.window="sidebarOpen = false"
>
    <div class="absolute inset-0 bg-slate-950/60" x-on:click="sidebarOpen = false"></div>

    <div
        class="relative flex h-full w-[14.75rem] max-w-[85vw] flex-col border-r border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
        style="z-index: 141; color: var(--theme-sidebar-text-color); border-color: var(--theme-border-color); background: var(--theme-sidebar-bg);"
        x-data="{ sidebarContentVisible: true, sidebarCollapsed: false, sidebarPanelExpanded: true }"
    >
        <div class="relative h-[76px] px-5 py-4">
            <div class="flex h-10 items-center justify-start">
                <img
                    x-show="appearanceResolved !== 'dark'"
                    src="{{ theme_asset('assets/img/logo-brand-dark.png', 'app') }}"
                    alt="{{ config('app.name', 'Stackposts') }}"
                    class="block h-8 w-auto max-w-none shrink-0"
                >
                <img
                    x-show="appearanceResolved === 'dark'"
                    src="{{ theme_asset('assets/img/logo-brand-light.png', 'app') }}"
                    alt="{{ config('app.name', 'Stackposts') }}"
                    class="block h-8 w-auto max-w-none shrink-0"
                >
            </div>

            <button
                type="button"
                class="absolute -right-3 top-1/2 inline-flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md border border-slate-300/80 bg-white text-slate-500 shadow-[0_6px_14px_-12px_rgba(15,23,42,0.2)] transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600 dark:hover:text-slate-200"
                x-on:click="sidebarOpen = false"
                title="Close menu"
            >
                <i class="fa-light fa-xmark text-[11px]"></i>
            </button>
        </div>

        <div class="app-sidebar-scroll min-h-0 flex-1 overflow-y-auto px-3 pt-2 pb-3">
            <x-layout.sidebar-menu :sections="$sections" mode="desktop" />

            @if ($dashboardSwitch || $planCard)
                <div class="mt-4 border-t pt-4" style="border-color: var(--theme-border-color);">
                    @if ($dashboardSwitch)
                        <x-ui.button
                            :href="$dashboardSwitch['route']"
                            class="w-full justify-center"
                            size="sm"
                            wire:navigate
                        >
                            <i class="fa-light fa-arrow-right-arrow-left mr-2 text-[12px]"></i>
                            {{ __($dashboardSwitch['label']) }}
                        </x-ui.button>
                    @endif

                    @if ($planCard)
                        <div
                            class="mt-3 rounded-2xl border p-3"
                            style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-sidebar-bg) 84%, white 16%);"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Your plan') }}</p>

                            <div class="mt-3 flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                        <i class="fa-solid fa-crown mr-1 text-[11px]"></i>{{ $planCard['name'] }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-1 text-xs whitespace-nowrap" style="color: var(--theme-muted-text-color);">
                                        <span>{{ __('Expire:') }}</span>
                                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $planCard['expiry'] }}</span>
                                    </div>
                                </div>

                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-semibold whitespace-nowrap {{ $planCard['badge_tone'] === 'success' ? 'bg-emerald-400/12 text-emerald-500' : 'bg-slate-400/10 text-slate-500 dark:text-slate-300' }}">
                                    {{ $planCard['badge'] }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                                <span style="color: var(--theme-muted-text-color);">{{ __('Credits used') }}</span>
                                <span style="color: var(--theme-header-text-color);">{{ $planCard['credits_used_label'] }} / {{ $planCard['credits_limit_label'] }}</span>
                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background: color-mix(in srgb, var(--theme-sidebar-bg) 72%, black 28%);">
                                <div
                                    class="h-full rounded-full"
                                    style="width: {{ $planCard['unlimited'] ? 100 : ($planCard['credits_percent'] ?? 0) }}%; background: linear-gradient(90deg, var(--theme-accent,#2563eb) 0%, color-mix(in srgb, var(--theme-accent,#2563eb) 72%, #8b5cf6 28%) 100%);"
                                ></div>
                            </div>

                            <x-ui.button href="{{ $planCard['details_route'] }}" class="mt-4 w-full justify-center" size="sm" wire:navigate>
                                {{ __('Upgrade / Details') }}
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
