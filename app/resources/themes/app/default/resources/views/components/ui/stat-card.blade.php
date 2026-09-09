@props([
    'label' => null,
    'value' => null,
    'description' => null,
    'icon' => null,
    'iconStyle' => 'background-color: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent);',
    'valueStyle' => 'color: var(--theme-header-text-color);',
    'minHeight' => 'min-h-[175px]',
])

<div {{ $attributes->class("rounded-[1.5rem] border p-5 {$minHeight}") }} style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96);">
    <div class="flex h-full flex-col">
        <div class="flex items-start justify-between gap-4">
            <div>
                @if ($label)
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ $label }}</p>
                @endif

                @if (! is_null($value) && $value !== '')
                    <p class="mt-4 text-[2.25rem] font-semibold leading-none tracking-[-0.05em]" style="{{ $valueStyle }}">{{ $value }}</p>
                @endif
            </div>

            @if ($icon)
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1rem] text-base" style="{{ $iconStyle }}">
                    <i class="{{ $icon }}"></i>
                </span>
            @endif
        </div>

        @if ($description)
            <p class="mt-5 max-w-[18rem] text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $description }}</p>
        @endif

        @if (trim((string) $slot) !== '')
            <div class="mt-4">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
