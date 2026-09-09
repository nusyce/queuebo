@props([
    'title' => null,
    'description' => null,
    'side' => 'right',
    'width' => 'md',
])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
    ];
    $position = $side === 'left' ? 'justify-start' : 'justify-end';
    $translate = $side === 'left' ? '-translate-x-full' : 'translate-x-full';
@endphp

<div x-data="{ open: false }" {{ $attributes }}>
    <div x-on:click="open = true">
        {{ $trigger ?? '' }}
    </div>

    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-[120] flex {{ $position }}" x-on:keydown.escape.window="open = false">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm" x-on:click="open = false"></div>

            <aside
                x-show="open"
                x-transition:enter="transform transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-300"
                x-transition:enter-start="{{ $translate }}"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="{{ $translate }}"
                class="relative h-full w-full {{ $widths[$width] ?? $widths['md'] }} border-l shadow-[0_24px_60px_-24px_rgba(15,23,42,0.3)]"
                style="border-color: var(--theme-border-color); background-color: var(--theme-surface-overlay);"
            >
                <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: var(--theme-border-color);">
                    <div>
                        @if ($title)
                            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $title }}</h3>
                        @endif
                        @if ($description)
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
                        @endif
                    </div>

                    <button type="button" class="text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200" x-on:click="open = false">
                        <i class="fa-light fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="h-[calc(100%-73px)] overflow-y-auto px-5 py-5">
                    {{ $slot }}
                </div>
            </aside>
        </div>
    </template>
</div>
