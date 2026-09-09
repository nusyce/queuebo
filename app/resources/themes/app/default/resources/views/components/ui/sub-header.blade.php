@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'count' => null,
])

<div class="pb-2">
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
        <div class="min-w-0">
            @if ($eyebrow)
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __($eyebrow) }}</p>
            @endif

            @if ($title)
                <div class="mt-2 flex flex-wrap items-end gap-x-3 gap-y-2">
                    <h1 class="text-[1.85rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __($title) }}</h1>
                    @if ($count !== null)
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft); color: var(--theme-muted-text-color);">
                            {{ number_format((int) $count) }} {{ __('records') }}
                        </span>
                    @endif
                </div>
            @endif

            @if ($description)
                <p class="mt-2 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __($description) }}</p>
            @endif
        </div>

        @if (isset($actions))
            <div class="flex min-w-0 flex-wrap items-center justify-start gap-2 xl:justify-end">
                {{ $actions }}
            </div>
        @endif
    </div>

</div>
