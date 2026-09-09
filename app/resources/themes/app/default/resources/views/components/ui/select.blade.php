@props([
    'label' => null,
    'help' => null,
    'error' => null,
])

<div {{ $attributes->only('class')->class('space-y-2.5') }}>
    @if ($label)
        <x-ui.label>{{ $label }}</x-ui.label>
    @endif

    <div class="relative">
        <select {{ $attributes->merge(['style' => 'border-radius: var(--theme-input-radius, 0.65rem); border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);'])->except('class')->class('flex h-11 w-full appearance-none border px-4 pr-14 text-sm font-medium shadow-[0_1px_2px_rgba(15,23,42,0.04)] outline-none transition duration-200 focus:border-[var(--theme-accent)] focus:ring-4 focus:ring-[color:rgba(var(--theme-accent-rgb),0.10)]') }}>
            {{ $slot }}
        </select>

        <span class="pointer-events-none absolute inset-y-0 right-4 inline-flex items-center" style="color: var(--theme-muted-text-color);">
            <i class="fa-light fa-chevron-down text-xs"></i>
        </span>
    </div>

    @if ($error)
        <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $error }}</p>
    @elseif ($help)
        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ $help }}</p>
    @endif
</div>
