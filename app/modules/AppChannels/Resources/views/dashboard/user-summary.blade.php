@php
    $limit = (int) ($metrics['limit'] ?? -1);
    $usagePercent = $metrics['usage_percent'] ?? null;
    $limitLabel = $limit > -1 ? number_format($limit) : __('Unlimited');
@endphp

<x-ui.dashboard-module
    :eyebrow="__('Channels')"
    :title="null"
    :description="null"
>
    <div class="space-y-4">
        <div class="max-w-full overflow-hidden border" style="border-radius: var(--theme-card-radius, 1.15rem); border-color: rgba(var(--theme-accent-rgb),0.12); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 95%, rgba(var(--theme-accent-rgb),0.03))); box-shadow: 0 26px 60px -52px rgba(15,23,42,0.22);">
            <div class="flex min-w-0 flex-col gap-5 p-4 sm:p-5 xl:p-6">
                <div class="min-w-0 rounded-[1.2rem] border px-4 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background:
                    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.1), transparent 34%),
                    linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 95%, transparent), color-mix(in srgb, var(--theme-surface-base) 93%, rgba(var(--theme-accent-rgb),0.03)));">
                    <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                    <i class="fa-light fa-share-nodes mr-1.5 text-[10px]"></i>{{ __('Channel overview') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Publishing ready') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('See coverage, activity, and provider spread at a glance') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Track connected destinations, active accounts, and recent growth before moving into publishing operations.') }}</p>
                        </div>

                        <div class="flex w-full min-w-0 flex-wrap items-center gap-3 lg:w-auto lg:shrink-0">
                            <x-ui.button :href="$item['route'] ?? route('portal.channels')" size="sm" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" wire:navigate>{{ __('Open channels') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <div class="min-w-0 space-y-4">
                    <div class="grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <x-ui.card padding="md" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(var(--theme-accent-rgb),0.32);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, var(--theme-accent), rgba(var(--theme-accent-rgb),0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['total'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Total channels') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('All connected accounts') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(var(--theme-success-color-rgb),0.16); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(var(--theme-success-color-rgb),0.24);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, var(--theme-success-color), rgba(var(--theme-success-color-rgb),0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['active'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Active') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Ready for publishing and sync') }}</p>
                        </x-ui.card>

                        <x-ui.card padding="md" style="border-color: rgba(var(--theme-warning-color-rgb),0.16); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 18px 38px -34px rgba(var(--theme-warning-color-rgb),0.22);">
                            <span class="mb-4 block h-1.5 w-11 rounded-full" style="background: linear-gradient(90deg, var(--theme-warning-color), rgba(var(--theme-warning-color-rgb),0.22));"></span>
                            <p class="text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['providers'] ?? 0)) }}</p>
                            <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Providers') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Distinct networks connected here') }}</p>
                        </x-ui.card>
                    </div>

                    <x-ui.card class="space-y-4" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent); box-shadow: 0 22px 44px -40px rgba(15,23,42,0.16);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Channel limit') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">
                                    {{ $limit > -1
                                        ? __(':used of :limit channels are currently in use.', ['used' => number_format((int) ($metrics['total'] ?? 0)), 'limit' => $limitLabel])
                                        : __('This plan does not enforce a channel cap.') }}
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
                                style="width: {{ $usagePercent ?? 14 }}%; background: linear-gradient(90deg, var(--theme-accent), rgba(var(--theme-accent-rgb),0.72));"
                            ></div>
                        </div>

                        <div class="grid min-w-0 gap-4 sm:grid-cols-3">
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-danger-color-rgb),0.16); background: rgba(var(--theme-danger-color-rgb),0.04);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Paused') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['paused'] ?? 0)) }}</p>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: rgba(var(--theme-accent-rgb),0.05);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('OAuth') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['oauth'] ?? 0)) }}</p>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-info-color-rgb),0.16); background: rgba(var(--theme-info-color-rgb),0.05);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Last 30 days') }}</p>
                                <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['recent'] ?? 0)) }}</p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <x-ui.chart
                    class="min-w-0"
                    :title="__('Provider mix')"
                    :description="__('Where your connected channels are concentrated right now.')"
                    type="bar"
                    :series="$providerChart"
                    :height="420"
                />
                </div>
            </div>
        </div>

        <section class="min-w-0 space-y-4 rounded-[var(--theme-card-radius,1.15rem)] border p-4 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background:
            linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 95%, rgba(var(--theme-accent-rgb),0.02)));
            box-shadow: 0 24px 56px -46px rgba(15,23,42,0.14);">
            <div class="flex min-w-0 items-center justify-between gap-3 border-b pb-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42);">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Recent channels') }}</p>
                    <p class="mt-2 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Latest connected accounts') }}</p>
                    <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('The newest accounts that were added to your channel mix.') }}</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-full border" style="border-color: rgba(var(--theme-accent-rgb),0.12); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                    <i class="fa-light fa-share-nodes text-base"></i>
                </span>
            </div>

            <div class="grid min-w-0 gap-3 lg:grid-cols-2">
                @forelse ($recentAccounts as $account)
                    @php($usesLocalSocialAvatar = str_contains((string) ($account['avatar_url'] ?? ''), '/media/public/social-accounts/') || str_contains((string) ($account['avatar_url'] ?? ''), '/storage/social-accounts/'))
                    <a href="{{ route('portal.channels') }}" wire:navigate class="group flex items-center gap-3 rounded-[1rem] border px-4 py-3 transition hover:-translate-y-[1px]" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent); box-shadow: 0 14px 28px -28px rgba(15,23,42,0.12);">
                        <span class="inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-full border" style="border-color: rgba(var(--theme-border-color-rgb),0.38); background: {{ $usesLocalSocialAvatar ? 'rgba(255,255,255,0.96)' : 'rgba(var(--theme-accent-rgb),0.05)' }}; color: var(--theme-accent);">
                            @if ($account['avatar_url'])
                                @if ($usesLocalSocialAvatar)
                                    <span class="inline-flex h-8 w-8 items-center justify-center overflow-hidden rounded-[0.7rem] bg-white shadow-[0_8px_16px_-12px_rgba(15,23,42,0.22)] ring-1 ring-slate-200/70">
                                        <img src="{{ $account['avatar_url'] }}" alt="{{ $account['name'] }}" class="h-full w-full object-contain">
                                    </span>
                                @else
                                    <img src="{{ $account['avatar_url'] }}" alt="{{ $account['name'] }}" class="h-full w-full object-cover">
                                @endif
                            @else
                                <i class="fa-light fa-user-group"></i>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $account['name'] }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">
                                        {{ $account['provider'] }}{{ $account['handle'] ? ' • '.$account['handle'] : '' }}
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em]" style="background: {{ $account['active'] ? 'rgba(var(--theme-success-color-rgb),0.12)' : 'rgba(var(--theme-danger-color-rgb),0.1)' }}; color: {{ $account['active'] ? 'var(--theme-success-color)' : 'var(--theme-danger-color)' }};">
                                    {{ $account['active'] ? __('Active') : __('Paused') }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                @if ($account['connected_at'])
                                    <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Connected') }}: {{ $account['connected_at'] }}</p>
                                @endif
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] opacity-0 transition group-hover:opacity-100" style="color: var(--theme-accent);">
                                    {{ __('Open') }}
                                    <i class="fa-light fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <x-ui.empty
                        icon="fa-light fa-share-nodes"
                        :title="__('No channels yet')"
                        :description="__('Connected accounts will appear here once you start adding social channels.')"
                    />
                @endforelse
            </div>
        </section>

    </div>
</x-ui.dashboard-module>
