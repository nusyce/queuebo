@props([
    'fieldsClass' => 'lg:grid-cols-[minmax(0,1.5fr)_180px_180px_180px_160px_auto]',
    'contentClass' => '',
    'panelStyle' => null,
    'overflowVisible' => false,
])

@php
    $resolvedPanelStyle = $panelStyle ?: 'border-color: var(--theme-border-color); background:
        radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 24%),
        linear-gradient(180deg,
            color-mix(in srgb, var(--theme-surface-base) 98%, transparent) 0%,
            color-mix(in srgb, var(--theme-surface-soft) 62%, var(--theme-surface-base)) 100%
        );';

    $hasActions = isset($actions) && trim((string) $actions) !== '';
    $hasChips = isset($chips) && trim((string) $chips) !== '';
@endphp

<x-ui.card class="space-y-5 {{ $overflowVisible ? 'overflow-visible' : 'overflow-hidden' }} border p-0" style="{{ $resolvedPanelStyle }}">
    <div {{ $attributes->class(['space-y-4 px-6 py-6', $contentClass]) }}>
        <div class="grid gap-3 {{ $fieldsClass }}">
            {{ $fields ?? $slot }}

            @if ($hasActions)
                <div class="flex items-end gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>

        @if ($hasChips)
            <div class="flex flex-wrap gap-2">
                {{ $chips }}
            </div>
        @endif
    </div>
</x-ui.card>
