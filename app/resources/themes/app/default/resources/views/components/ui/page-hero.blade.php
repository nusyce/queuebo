@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'count' => null,
    'icon' => 'fa-light fa-layer-group',
    'iconStyle' => null,
    'panelStyle' => null,
])

@php
    $resolvedPanelStyle = $panelStyle ?: 'background:
        radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.22), transparent 38%),
        linear-gradient(135deg,
            color-mix(in srgb, var(--theme-accent) 10%, var(--theme-surface-base)) 0%,
            var(--theme-surface-base) 58%,
            color-mix(in srgb, var(--theme-header-text-color) 6%, var(--theme-surface-base)) 100%
        );';

    $resolvedIconStyle = $iconStyle ?: 'background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);';
@endphp

<x-ui.card class="overflow-hidden border-none shadow-[0_30px_80px_-48px_rgba(15,23,42,0.42)]" style="{{ $resolvedPanelStyle }}">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
        <div class="flex items-start gap-4">
            <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl shadow-[0_18px_38px_-24px_rgba(15,23,42,0.34)]" style="{{ $resolvedIconStyle }}">
                <i class="{{ $icon }}"></i>
            </span>
            <div class="min-w-0">
                <x-ui.sub-header
                    :eyebrow="$eyebrow"
                    :title="$title"
                    :description="$description"
                    :count="$count"
                />
            </div>
        </div>

        @if (isset($actions))
            <div class="flex min-w-0 flex-wrap items-center justify-start gap-2 lg:justify-end">
                {{ $actions }}
            </div>
        @endif
    </div>
</x-ui.card>
