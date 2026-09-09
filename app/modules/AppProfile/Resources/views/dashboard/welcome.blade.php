@php
    $creditRemainingLabel = $credits['unlimited']
        ? __('Unlimited')
        : number_format((int) ($credits['remaining'] ?? 0));
    $usagePercent = (int) ($credits['usage_percent'] ?? 0);
@endphp

<div class="min-w-0 max-w-full overflow-hidden rounded-[1.6rem] border" style="border-color: rgba(var(--theme-accent-rgb,37,99,235),0.16); background:
    radial-gradient(circle at top left, rgba(var(--theme-accent-rgb,37,99,235),0.08), transparent 28%),
    linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-soft) 90%, rgba(var(--theme-accent-rgb,37,99,235),0.08)) 0%, var(--theme-surface-overlay) 46%, color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb,37,99,235),0.04)) 100%);
    box-shadow: 0 28px 70px -48px rgba(15,23,42,0.24);">
    <div class="grid min-w-0 gap-5 p-4 sm:p-5 xl:grid-cols-[minmax(0,1.65fr)_minmax(0,0.95fr)] xl:p-6">
        <div class="relative min-w-0 overflow-hidden rounded-[1.35rem] border px-4 py-5 sm:px-6 sm:py-6" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background:
            radial-gradient(circle at 88% 16%, rgba(var(--theme-accent-rgb,37,99,235),0.16), transparent 26%),
            radial-gradient(circle at 8% 100%, rgba(16,185,129,0.10), transparent 24%),
            linear-gradient(155deg,
                color-mix(in srgb, var(--theme-surface-overlay) 92%, rgba(var(--theme-accent-rgb,37,99,235),0.04)),
                color-mix(in srgb, var(--theme-surface-base) 88%, rgba(var(--theme-accent-rgb,37,99,235),0.02)));">
            <div class="pointer-events-none absolute inset-y-0 right-[18%] w-px" style="background: linear-gradient(180deg, transparent, rgba(var(--theme-border-color-rgb),0.28), transparent);"></div>

            <div class="relative flex min-w-0 h-full flex-col justify-between gap-6 sm:gap-8">
                <div class="flex min-w-0 flex-wrap items-start justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[12px] font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-accent-rgb,37,99,235),0.16); background: rgba(var(--theme-accent-rgb,37,99,235),0.08); color: var(--theme-accent,#2563eb);">
                            <i class="fa-light fa-calendar-days text-[11px]"></i>
                            {{ $todayLabel }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[12px] font-semibold" style="background: rgba(var(--theme-success-color-rgb),0.10); color: var(--theme-success-color);">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full" style="background: currentColor;"></span>
                            {{ __('Portal ready') }}
                        </span>
                    </div>

                    <div class="min-w-0 max-w-full rounded-[1rem] border px-4 py-3 text-left sm:text-right" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent);">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Plan status') }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $planName }}</p>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $planExpiryLabel }}</p>
                    </div>
                </div>

                <div class="min-w-0 max-w-[46rem]">
                    <p class="text-[2.1rem] font-semibold leading-[1.02] tracking-[-0.075em] sm:text-[2.7rem]" style="color: var(--theme-header-text-color);">
                        {{ __('Welcome, :name', ['name' => $user?->name ?: $user?->username ?: __('there')]) }}
                    </p>
                    <p class="mt-4 max-w-[38rem] text-[15px] leading-7" style="color: var(--theme-muted-text-color);">
                        {{ __("Here's your control surface for plan health, credits, and account readiness before you jump back into work.") }}
                    </p>
                </div>

                <div class="grid min-w-0 gap-3 md:grid-cols-3">
                    <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-accent-rgb,37,99,235),0.14); background: color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent);">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Current plan') }}</p>
                        <p class="mt-3 text-[1.25rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $planName }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Active package attached to this portal.') }}</p>
                    </div>

                    <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-success-color-rgb),0.18); background: color-mix(in srgb, var(--theme-surface-overlay) 84%, rgba(var(--theme-success-color-rgb),0.04));">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Access window') }}</p>
                        <p class="mt-3 text-[1.25rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">
                            {{ $user?->isInPlanTrial() ? __('Trial') : __('Live') }}
                        </p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">
                            {{ $user?->isInPlanTrial()
                                ? __('Trial ends :date', ['date' => $user?->trialEndsAt()?->format('Y-m-d') ?: __('soon')])
                                : $planExpiryLabel }}
                        </p>
                    </div>

                    <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent);">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Workspace signal') }}</p>
                        <p class="mt-3 text-[1.25rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-success-color);">{{ __('Ready to publish') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Account, plan, and credits are visible from one panel.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative min-w-0 overflow-hidden rounded-[1.35rem] border px-4 py-4 sm:px-5 sm:py-5" style="border-color: rgba(var(--theme-warning-color-rgb),0.18); background:
            radial-gradient(circle at top right, rgba(245,158,11,0.16), transparent 28%),
            linear-gradient(180deg,
                color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-warning-color-rgb),0.06)),
                color-mix(in srgb, var(--theme-surface-base) 88%, rgba(var(--theme-warning-color-rgb),0.10)));
            box-shadow: inset 0 1px 0 rgba(var(--theme-border-color-rgb),0.12), 0 20px 48px -34px rgba(var(--theme-warning-color-rgb),0.18);">
            <div class="relative min-w-0 space-y-5">
                <div class="flex min-w-0 flex-wrap items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem]" style="background: linear-gradient(145deg, rgba(var(--theme-warning-color-rgb),0.18), color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent)); color: var(--theme-warning-color);">
                            <i class="fa-light fa-gem text-lg"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Your Credits') }}</p>
                            <p class="mt-2 text-[2.15rem] font-semibold leading-none tracking-[-0.075em] sm:text-[2.65rem]" style="color: var(--theme-header-text-color);">
                                {{ $creditRemainingLabel }}
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex max-w-full items-center rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="background: rgba(var(--theme-warning-color-rgb),0.10); color: var(--theme-warning-color);">
                        {{ $credits['unlimited'] ? __('Unlimited') : __('Credit balance') }}
                    </span>
                </div>

                <p class="max-w-[24rem] text-sm leading-6" style="color: var(--theme-muted-text-color);">
                    {{ $credits['unlimited']
                        ? __('Your account can generate without a hard credit ceiling across AI writing, media, and automation.')
                        : __('Available credits ready for image generation, AI writing, and automation runs.') }}
                </p>

                <div class="grid min-w-0 gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background: color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent);">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Used') }}</p>
                        <p class="mt-3 text-[1.6rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $credits['used']) }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Credits already consumed') }}</p>
                    </div>

                    <div class="rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-success-color-rgb),0.18); background: linear-gradient(180deg, rgba(var(--theme-success-color-rgb),0.10), color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent));">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Available') }}</p>
                        <p class="mt-3 text-[1.6rem] font-semibold tracking-[-0.045em]" style="color: var(--theme-success-color);">{{ $creditRemainingLabel }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Still ready to use') }}</p>
                    </div>
                </div>

                <div class="min-w-0 rounded-[1.05rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent);">
                    <div class="flex min-w-0 items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Usage') }}</p>
                            <p class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $usagePercent }}%</p>
                        </div>
                        <div class="min-w-0 text-right text-xs" style="color: var(--theme-muted-text-color);">
                            <p>{{ __('Used: :count', ['count' => number_format((int) $credits['used'])]) }}</p>
                            <p class="mt-1">{{ $credits['unlimited'] ? __('Unlimited available') : __(':count left', ['count' => $creditRemainingLabel]) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 h-3 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb),0.26);">
                        <div class="h-full rounded-full" style="width: {{ max(4, $usagePercent) }}%; background: linear-gradient(90deg, #f59e0b 0%, #f97316 36%, rgba(var(--theme-accent-rgb,37,99,235),0.88) 100%); box-shadow: 0 0 18px rgba(245,158,11,0.22);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
