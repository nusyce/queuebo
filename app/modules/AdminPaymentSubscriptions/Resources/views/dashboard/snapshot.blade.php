@php
    $inactiveSubscriptions = max(0, (int) $metrics['total'] - (int) $metrics['active'] - (int) $metrics['cancelled']);
    $subscriptionSeries = [[
        'name' => __('Subscriptions'),
        'data' => [
            ['name' => __('Active'), 'y' => (int) $metrics['active']],
            ['name' => __('Cancelled'), 'y' => (int) $metrics['cancelled']],
            ['name' => __('Inactive'), 'y' => $inactiveSubscriptions],
        ],
    ]];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('Recurring billing')"
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
                                    <i class="fa-light fa-arrows-rotate mr-1.5 text-[10px]"></i>{{ __('Recurring billing') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Admin dashboard') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('See recurring revenue health and churn pressure faster') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Recurring plans, active subscriptions, cancellations, and billing volume now follow the same hero treatment used across the admin dashboard.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-ui.button :href="$route" size="sm" wire:navigate>{{ __('Open subscriptions') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 xl:grid-cols-[1.04fr_0.96fr]">
        <x-ui.card class="space-y-5" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-base) !important; background-image: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 97%, rgba(var(--theme-accent-rgb),0.02))) !important; box-shadow: 0 22px 48px -42px rgba(15,23,42,0.16);">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Recurring summary') }}</p>
                    <p class="mt-2 text-[2.2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format($metrics['active_volume'], 2) }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Current recurring billing amount from active subscriptions.') }}</p>
                </div>
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-3 text-right" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background: color-mix(in srgb, var(--theme-surface-base) 88%, rgba(var(--theme-accent-rgb),0.04));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Active base') }}</p>
                    <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['active']) }}</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('All subscriptions') }}</p>
                    <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['total']) }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Rows in billing registry') }}</p>
                </div>
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-danger-color-rgb),0.05)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-danger-color-rgb),0.02)));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Cancelled') }}</p>
                    <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['cancelled']) }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Stopped recurring plans') }}</p>
                </div>
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-warning-color-rgb),0.05)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-warning-color-rgb),0.02)));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Inactive') }}</p>
                    <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($inactiveSubscriptions) }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('No longer billing') }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.chart
            :title="__('Subscription states')"
            :description="__('The recurring base split between active, cancelled, and inactive subscriptions.')"
            type="donut"
            :series="$subscriptionSeries"
            :height="320"
        />
                </div>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
