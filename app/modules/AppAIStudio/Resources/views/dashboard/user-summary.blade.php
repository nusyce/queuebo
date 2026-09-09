<x-ui.dashboard-module
    :eyebrow="__('AI Studio')"
    :title="null"
    :description="null"
>
    <div class="max-w-full overflow-hidden rounded-[1.55rem] border p-4 sm:p-5 xl:p-6" style="border-color: rgba(var(--theme-accent-rgb),0.12); background: linear-gradient(180deg, color-mix(in srgb, var(--theme-surface-overlay) 96%, rgba(var(--theme-accent-rgb),0.04)), color-mix(in srgb, var(--theme-surface-base) 95%, rgba(var(--theme-accent-rgb),0.03))); box-shadow: 0 26px 60px -52px rgba(15,23,42,0.22);">
        <div class="flex min-w-0 flex-col gap-5">
            <div class="min-w-0 rounded-[1.2rem] border px-4 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background:
                radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.1), transparent 34%),
                linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 95%, transparent), color-mix(in srgb, var(--theme-surface-base) 93%, rgba(var(--theme-accent-rgb),0.03)));">
                <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                <i class="fa-light fa-sparkles mr-1.5 text-[10px]"></i>{{ __('AI toolkit') }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                {{ __('Ready to create') }}
                            </span>
                        </div>
                        <h3 class="mt-3 text-[1.55rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Jump into the right AI workflow faster') }}</h3>
                        <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Keep image, caption, video, and repurpose tools close together so ideation starts here and publishing stays separate.') }}</p>
                    </div>

                    <div class="flex w-full min-w-0 flex-wrap items-center gap-3 lg:w-auto lg:shrink-0">
                        <x-ui.button :href="$item['route'] ?? route('portal.ai-studio')" size="sm" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" wire:navigate>{{ __('Open AI Studio') }}</x-ui.button>
                    </div>
                </div>
            </div>

            <div class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <div class="min-w-0 space-y-4">
                <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($tools as $tool)
                        <a href="{{ $tool['route'] }}" wire:navigate class="min-w-0 max-w-full rounded-[1rem] border p-4 transition hover:-translate-y-[1px]" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent); box-shadow: 0 18px 34px -34px rgba(var(--theme-accent-rgb),0.18);">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                <i class="{{ $tool['icon'] }}"></i>
                            </span>
                            <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $tool['label'] }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $tool['description'] }}</p>
                            <span class="mt-4 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">
                                {{ __('Open tool') }}
                                <i class="fa-light fa-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <x-ui.card class="min-w-0 space-y-4" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: color-mix(in srgb, var(--theme-surface-overlay) 90%, transparent); box-shadow: 0 22px 44px -40px rgba(15,23,42,0.16);">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Related tools') }}</p>
                    <p class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('AI adjacent workflows') }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Shortcuts for the workflows that usually follow AI ideation and generation.') }}</p>
                </div>

                <div class="grid min-w-0 gap-3">
                    @foreach ($supportTools as $tool)
                        <a href="{{ $tool['route'] }}" wire:navigate class="flex min-w-0 items-center justify-between gap-3 rounded-[1rem] border px-4 py-3 transition hover:-translate-y-[1px]" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background: color-mix(in srgb, var(--theme-surface-overlay) 92%, transparent);">
                            <span class="min-w-0 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $tool['label'] }}</span>
                            <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">
                                {{ __('Open') }}
                                <i class="fa-light fa-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
