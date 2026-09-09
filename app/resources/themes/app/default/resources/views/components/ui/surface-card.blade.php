@props([
    'padding' => 'md',
    'tone' => 'default',
    'accent' => 'none',
    'featured' => false,
])

@php
    $accentBlobs = [
        'none' => null,
        'primary' => 'background: radial-gradient(circle at top left, rgba(var(--theme-accent-rgb,37 99 235),0.18), rgba(var(--theme-accent-rgb,37 99 235),0.02) 58%, transparent 72%);',
        'success' => 'background: radial-gradient(circle at top left, rgba(var(--theme-success-color-rgb,16 185 129),0.16), rgba(var(--theme-success-color-rgb,16 185 129),0.02) 58%, transparent 72%);',
        'warning' => 'background: radial-gradient(circle at top left, rgba(var(--theme-warning-color-rgb,245 158 11),0.16), rgba(var(--theme-warning-color-rgb,245 158 11),0.02) 58%, transparent 72%);',
        'danger' => 'background: radial-gradient(circle at top left, rgba(var(--theme-danger-color-rgb,244 63 94),0.16), rgba(var(--theme-danger-color-rgb,244 63 94),0.02) 58%, transparent 72%);',
    ];
@endphp

<x-ui.card
    :padding="$padding"
    :tone="$tone"
    {{ $attributes->class('relative overflow-hidden') }}
>
    @if ($featured && ($accentBlobs[$accent] ?? null))
        <div class="pointer-events-none absolute left-0 top-0 h-28 w-28" style="{{ $accentBlobs[$accent] }}"></div>
    @endif

    <div class="relative">
        {{ $slot }}
    </div>
</x-ui.card>
