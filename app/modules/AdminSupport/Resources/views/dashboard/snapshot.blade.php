@php
    $supportSeries = [[
        'name' => __('Tickets'),
        'data' => [
            (int) $metrics['total'],
            (int) $metrics['open'],
            (int) $metrics['resolved'],
            (int) $metrics['unread'],
        ],
    ]];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('Support')"
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
                                    <i class="fa-light fa-headset mr-1.5 text-[10px]"></i>{{ __('Support') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Admin dashboard') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('See queue pressure and unread support load at a glance') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Ticket load, unresolved work, and unread pressure now sit inside the same compact hero pattern used throughout admin dashboard.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-ui.button :href="$route" size="sm" wire:navigate>{{ __('Open support queue') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 xl:grid-cols-[1.04fr_0.96fr]">
        <x-ui.card class="space-y-5" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.03)), color-mix(in srgb, var(--theme-surface-base) 97%, rgba(var(--theme-accent-rgb),0.02))); box-shadow: 0 22px 48px -42px rgba(15,23,42,0.16);">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Service summary') }}</p>
                    <p class="mt-2 text-[2.2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['open']) }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Tickets still in progress and waiting for response or resolution.') }}</p>
                </div>
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-3 text-right" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background: color-mix(in srgb, var(--theme-surface-base) 88%, rgba(var(--theme-accent-rgb),0.04));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Unread by admin') }}</p>
                    <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['unread']) }}</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-accent-rgb),0.02)));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('All tickets') }}</p>
                    <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['total']) }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Queue size') }}</p>
                </div>
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-success-color-rgb),0.06)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-success-color-rgb),0.03)));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Resolved') }}</p>
                    <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['resolved']) }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Completed work') }}</p>
                </div>
                <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, rgba(var(--theme-warning-color-rgb),0.05)), color-mix(in srgb, var(--theme-surface-base) 96%, rgba(var(--theme-warning-color-rgb),0.02)));">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Unread') }}</p>
                    <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $metrics['unread']) }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Need review') }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.chart
            :title="__('Queue distribution')"
            :description="__('A quick service view across total, open, resolved, and unread states.')"
            type="column"
            :categories="[__('All'), __('Open'), __('Resolved'), __('Unread')]"
            :series="$supportSeries"
            :height="320"
        />
                </div>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
