@props([
    'label' => null,
    'help' => null,
    'error' => null,
])

@php
    $type = $attributes->get('type', 'text');
    $isDateInput = $type === 'date';
    $inputClasses = 'flex h-11 w-full border px-4 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 placeholder:font-normal placeholder:text-[var(--theme-input-placeholder)] focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]';
@endphp

<div {{ $attributes->only('class')->class('space-y-2.5') }}>
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    @if ($isDateInput)
        <input
            x-data
            x-on:focus="if ($event.target.showPicker) { $event.target.showPicker(); }"
            {{ $attributes->merge(['style' => 'border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);'])->except('class')->class($inputClasses) }}
        >
    @else
        <input {{ $attributes->merge(['style' => 'border-radius: var(--theme-input-radius, 0.75rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);'])->except('class')->class($inputClasses) }}>
    @endif

    @if ($error)
        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $error }}</p>
    @elseif ($help)
        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $help }}</p>
    @endif
</div>
