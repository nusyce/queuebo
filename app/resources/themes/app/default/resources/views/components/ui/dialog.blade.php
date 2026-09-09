@props([
    'title' => null,
    'description' => null,
    'width' => 'lg',
    'dismissible' => false,
])

@php
    $widths = [
        'sm' => 'max-w-[26rem]',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];

    $hasBodyContent = trim((string) $slot) !== '';
@endphp

<div x-data="{ open: false }" {{ $attributes }}>
    <div x-on:click="open = true">
        {{ $trigger ?? '' }}
    </div>

    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-[120] flex items-center justify-center p-6" x-on:keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="open = false"></div>

            <div x-show="open" x-transition.opacity.scale.90 class="relative w-full {{ $widths[$width] ?? $widths['lg'] }}">
                <div class="overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    @if ($title || $description)
                        <div class="flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                            <div class="min-w-0">
                                @if ($title)
                                    <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $title }}</h3>
                                @endif
                                @if ($description)
                                    <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">{{ $description }}</p>
                                @endif
                            </div>

                            @if ($dismissible)
                                <button type="button" class="transition" style="color: var(--theme-muted-text-color);" x-on:click="open = false">
                                    <i class="fa-light fa-xmark text-lg"></i>
                                </button>
                            @endif
                        </div>
                    @endif

                    @if ($hasBodyContent)
                        <div class="px-5 py-4 sm:px-6 sm:py-5">
                            {{ $slot }}
                        </div>
                    @endif

                    @if (isset($footer))
                        <div class="border-t px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent); background-color: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);">
                            {{ $footer }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
