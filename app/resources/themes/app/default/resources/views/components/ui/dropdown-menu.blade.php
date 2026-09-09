@props([
    'align' => 'left',
    'width' => '56',
])

@php
    $alignClass = $align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left';
    $widthClass = match ($width) {
        'auto' => 'w-max min-w-[9.25rem]',
        'full' => 'w-full',
        default => 'w-'.$width,
    };
@endphp

<div x-data="{ open: false }" x-bind:class="open ? 'z-[60]' : 'z-0'" class="relative inline-block text-left" x-on:click.stop {{ $attributes }}>
    <div x-on:click.stop="open = !open">
        {{ $trigger ?? '' }}
    </div>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        x-on:click.stop
        x-on:click.outside="open = false"
        x-on:keydown.escape.window="open = false"
        class="absolute {{ $alignClass }} z-50 mt-2 {{ $widthClass }} overflow-hidden rounded-[1.1rem] border p-1.5 shadow-[0_18px_38px_-18px_rgba(15,23,42,0.22)]"
        style="border-color: color-mix(in srgb, var(--theme-border-color) 78%, transparent 22%); background-color: var(--theme-surface-base);"
    >
        {{ $slot }}
    </div>
</div>
