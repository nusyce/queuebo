@if ($result)
    <x-ui.surface-card class="space-y-5">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 space-y-3">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Generated content variants') }}</p>
                <p class="max-w-3xl pb-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                    {{ $result['summary'] ?: __('Generated from your selected template, prompt, and output settings in multiple ready-to-use variants.') }}
                </p>
            </div>
            <div class="shrink-0">
                <x-ui.button
                    type="button"
                    size="sm"
                    variant="outline"
                    wire:click="saveAllResults"
                    wire:loading.attr="disabled"
                    wire:target="saveAllResults"
                    :disabled="empty($result['variants'] ?? [])"
                >
                    <i class="fa-light fa-books"></i>
                    <span wire:loading.remove wire:target="saveAllResults">{{ __('Save all') }}</span>
                    <span wire:loading wire:target="saveAllResults">{{ __('Saving all...') }}</span>
                </x-ui.button>
            </div>
        </div>

        <div class="grid gap-5 pt-1">
            @foreach (($result['variants'] ?? []) as $item)
                <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background:
                    linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.04), transparent 28%),
                    color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Variation :number', ['number' => $loop->iteration]) }}</p>
                        </div>
                        @if (in_array($loop->index, $savedResultIndexes ?? [], true))
                            <x-ui.badge variant="success">{{ __('Saved') }}</x-ui.badge>
                        @endif
                    </div>

                    @if (!empty($item['hook']))
                        <p class="mt-4 text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Hook') }}</p>
                        <p class="mt-2 text-sm font-medium leading-6" style="color: var(--theme-header-text-color);">{{ $item['hook'] }}</p>
                    @endif

                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Caption') }}</p>
                    <p class="mt-2 text-sm leading-7 whitespace-pre-line" style="color: var(--theme-header-text-color);">{{ $item['caption'] }}</p>

                    @if (!empty($item['cta']))
                        <div class="mt-4 rounded-[0.95rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('CTA') }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ $item['cta'] }}</p>
                        </div>
                    @endif

                    @if (!empty($item['hashtags']))
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($item['hashtags'] as $tag)
                                <span class="rounded-full px-2 py-1 text-[11px]" style="background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">#{{ ltrim($tag, '#') }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($item['notes']))
                        <p class="mt-4 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $item['notes'] }}</p>
                    @endif

                    <div class="mt-5 flex items-center gap-2">
                        <x-ui.button
                            type="button"
                            size="sm"
                            variant="{{ in_array($loop->index, $savedResultIndexes ?? [], true) ? 'outline' : 'secondary' }}"
                            wire:click="saveResultCaption({{ $loop->index }})"
                            wire:loading.attr="disabled"
                            wire:target="saveResultCaption({{ $loop->index }})"
                            :disabled="in_array($loop->index, $savedResultIndexes ?? [], true)"
                        >
                            <i class="fa-light fa-book-bookmark"></i>
                            <span wire:loading.remove wire:target="saveResultCaption({{ $loop->index }})">
                                {{ in_array($loop->index, $savedResultIndexes ?? [], true) ? __('Saved to Captions') : __('Save to Captions') }}
                            </span>
                            <span wire:loading wire:target="saveResultCaption({{ $loop->index }})">{{ __('Saving...') }}</span>
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.surface-card>
@else
    <x-ui.empty
        class="rounded-[1.3rem] border px-6 py-10 sm:px-7"
        icon="fa-light fa-pen-nib"
        :title="__('No content generated yet')"
        :description="__('Choose a template, add your own context, and run the generator to create multiple ready-to-save content variations.')"
    >
        <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to write') }}</span>
            <span>&bull;</span>
            <span>{{ __('Template, context, generate.') }}</span>
        </div>
    </x-ui.empty>
@endif
