@php
    $usersPerTeam = (int) ($metrics['teams'] ?? 0) > 0
        ? number_format(((int) $metrics['users']) / max(1, (int) $metrics['teams']), 1)
        : number_format((int) $metrics['users']);
    $rolesPerTeam = (int) ($metrics['teams'] ?? 0) > 0
        ? number_format(((int) $metrics['roles']) / max(1, (int) $metrics['teams']), 1)
        : number_format((int) $metrics['roles']);
    $signupShare = (int) ($metrics['users'] ?? 0) > 0
        ? round((((int) $metrics['new_users']) / max(1, (int) $metrics['users'])) * 100)
        : 0;
    $signupReport = $signupReport ?? [
        'today' => 0,
        'week' => 0,
        'month' => 0,
        'total' => (int) $metrics['users'],
        'daily' => collect(range(6, 0))->map(fn () => ['label' => '--', 'value' => 0])->values(),
    ];
    $signupTrendCategories = collect($signupReport['daily'] ?? [])->map(fn (array $day) => $day['label'] ?? '--')->all();
    $signupTrendSeries = [[
        'name' => __('Signups'),
        'data' => collect($signupReport['daily'] ?? [])->map(fn (array $day) => (int) ($day['value'] ?? 0))->all(),
    ]];
    $signupFooterStats = [
        ['label' => __('Today'), 'value' => (int) $signupReport['today']],
        ['label' => __('Last 7 days'), 'value' => (int) $signupReport['week']],
        ['label' => __('Month'), 'value' => (int) $signupReport['month']],
        ['label' => __('Total accounts'), 'value' => (int) $signupReport['total']],
    ];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('User portal')"
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
                                    <i class="fa-light fa-users mr-1.5 text-[10px]"></i>{{ __('User portal') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Admin dashboard') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Review users, access structure, and signup motion from one surface') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Quick visibility into identity volume, team structure, role coverage, and recent signup motion with the same compact hero used across admin dashboard.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-ui.button :href="$reportRoute" variant="outline" size="sm" wire:navigate>{{ __('Open report') }}</x-ui.button>
                            <x-ui.button :href="$route" size="sm" wire:navigate>{{ __('Manage users') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 xl:grid-cols-[1.03fr_0.97fr]">
        <x-ui.card class="space-y-5 overflow-hidden" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 97%, rgba(var(--theme-accent-rgb),0.02))); box-shadow: 0 22px 48px -42px rgba(15,23,42,0.16);">
            <div class="grid gap-4 lg:grid-cols-[1.14fr_0.64fr]">
                <div class="relative overflow-hidden rounded-[var(--theme-card-radius,1.15rem)] border px-5 py-5" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background: radial-gradient(circle at top right, rgba(var(--theme-accent-rgb),0.12), transparent 36%), linear-gradient(140deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                    <div class="pointer-events-none absolute -right-10 top-0 h-32 w-32 rounded-full blur-3xl" style="background: rgba(var(--theme-accent-rgb),0.10);"></div>

                    <div class="relative">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Identity overview') }}</p>
                                <p class="mt-3 text-[2.7rem] font-semibold tracking-[-0.065em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['users']) }}</p>
                                <p class="mt-2 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Accounts under management') }}</p>
                            </div>

                            <div class="rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background: rgba(var(--theme-accent-rgb),0.09); color: rgba(var(--theme-accent-rgb),0.92);">
                                {{ __('Identity layer') }}
                            </div>
                        </div>

                        <p class="mt-4 max-w-[34rem] text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Monitor user volume, access structure, and signup movement from one admin-facing identity surface.') }}</p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 88%, rgba(var(--theme-accent-rgb),0.04));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Users per team') }}</p>
                                <p class="mt-2 text-[1.75rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $usersPerTeam }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Average identity density across team groupings') }}</p>
                            </div>

                            <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.44); background: color-mix(in srgb, var(--theme-surface-base) 88%, rgba(var(--theme-accent-rgb),0.04));">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Growth share') }}</p>
                                <p class="mt-2 text-[1.75rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $signupShare }}%</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Current users added during the last 7 days') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-success-color-rgb),0.08)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-success-color-rgb),0.04)));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Signup motion') }}</p>
                        <p class="mt-2 text-[1.9rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['new_users']) }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('New identities landed during the current 7-day window.') }}</p>
                    </div>

                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.05)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.03)));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Role coverage') }}</p>
                        <p class="mt-2 text-[1.9rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ $rolesPerTeam }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Average access profiles attached to each team.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[var(--theme-card-radius,1.15rem)] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Teams') }}</p>
                        <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['teams']) }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Structured admin groups') }}</p>
                    </div>

                    <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Roles') }}</p>
                        <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['roles']) }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Access definitions in rotation') }}</p>
                    </div>

                    <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Users / team') }}</p>
                        <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $usersPerTeam }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Typical team density') }}</p>
                    </div>

                    <div class="rounded-[calc(var(--theme-card-radius,1.15rem)-0.2rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, var(--theme-surface-base) 90%, rgba(var(--theme-accent-rgb),0.03));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('7-day share') }}</p>
                        <p class="mt-2 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $signupShare }}%</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Portion of users created recently') }}</p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.chart
            :title="__('Registration report')"
            :description="__('Recent signups and growth signal from the last 7 days.')"
            type="line"
            :categories="$signupTrendCategories"
            :series="$signupTrendSeries"
            :height="360"
            :footer-stats="$signupFooterStats"
            class="h-full"
        />
                </div>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
