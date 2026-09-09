@props([
    'items' => [],
    'columns' => 'md:grid-cols-3',
    'gap' => 'gap-4',
    'cardStyle' => 'border-color: var(--theme-border-color); background-color: color-mix(in srgb, var(--theme-surface-base) 98%, transparent);',
    'progressTrackStyle' => 'background-color: color-mix(in srgb, var(--theme-border-color) 55%, transparent);',
    'showIcons' => true,
    'showProgress' => true,
])

<div {{ $attributes->class("grid {$gap} {$columns}") }}>
    @foreach ($items as $item)
        @php
            $tone = $item['tone'] ?? 'var(--theme-accent)';
            $progress = (int) ($item['progress'] ?? 0);
            $resolvedCardStyle = trim(($cardStyle ?? '').' '.($item['cardStyle'] ?? ''));
            $resolvedProgressTrackStyle = $item['progressTrackStyle'] ?? $progressTrackStyle;
            $iconSurface = $item['iconSurface'] ?? 'transparent';
            $iconBorder = $item['iconBorder'] ?? 'var(--theme-border-color)';
        @endphp

        <div class="rounded-[1.35rem] border p-5" style="{{ $resolvedCardStyle }}">
            <div class="flex items-center justify-between gap-4">
                <div>
                    @if (!empty($item['label']))
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: {{ $tone }};">{{ $item['label'] }}</p>
                    @endif

                    <div class="mt-4 flex items-end gap-2">
                        <p class="text-3xl font-semibold leading-none" style="color: var(--theme-header-text-color);">{{ $item['value'] ?? '0' }}</p>

                        @if (!empty($item['suffix']))
                            <span class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ $item['suffix'] }}</span>
                        @endif
                    </div>
                </div>

                @if ($showIcons && !empty($item['icon']))
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full border text-sm" style="border-color: {{ $iconBorder }}; background: {{ $iconSurface }}; color: {{ $tone }};">
                        <i class="{{ str_contains($item['icon'], 'fa-') ? $item['icon'] : 'fa-light '.$item['icon'] }}"></i>
                    </span>
                @endif
            </div>

            @if (!empty($item['description']))
                <p class="mt-5 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $item['description'] }}</p>
            @endif

            @if ($showProgress)
                <div class="mt-5 flex items-center gap-3">
                    <div class="h-2 flex-1 overflow-hidden rounded-full" style="{{ $resolvedProgressTrackStyle }}">
                        <div class="h-full rounded-full" style="width: {{ $progress }}%; background-color: {{ $tone }};"></div>
                    </div>
                    <span class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ $progress }}%</span>
                </div>
            @endif
        </div>
    @endforeach
</div>
