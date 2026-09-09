<aside class="space-y-5">
    <x-ui.surface-card class="space-y-4">
        <div>
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Get started') }}</p>
            <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Use a reusable template to define the writing job, then add the topic, offer, audience, or context in your own words.') }}</p>
        </div>

        <div class="space-y-3">
            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Step 1') }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ __('Pick a category and load a template, or start from a blank custom prompt.') }}</p>
            </div>
            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Step 2') }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ __('Add the campaign context, set tone/creativity, and choose the target platforms.') }}</p>
            </div>
            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Step 3') }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ __('Generate multiple content variants, then save the strong ones straight into Captions.') }}</p>
            </div>
        </div>
    </x-ui.surface-card>

    <x-ui.surface-card class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt history') }}</p>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Reload previous runs with one click if you want to refine or regenerate them.') }}</p>
            </div>
            <x-ui.badge variant="neutral">{{ $promptHistory->count() }}</x-ui.badge>
        </div>

        @if ($promptHistory->isNotEmpty())
            <div class="max-h-[28rem] space-y-2 overflow-y-auto pr-1">
                @foreach ($promptHistory as $history)
                    <button type="button" wire:click="loadPromptHistory({{ $history->id }})" class="w-full rounded-[1rem] border px-4 py-3 text-left transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Content prompt') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 88) }}</p>
                    </button>
                @endforeach
            </div>
        @else
            <x-ui.empty
                icon="fa-light fa-book-open"
                :title="__('No prompt history yet')"
                :description="__('Generated caption briefs will be stored here after your first run.')"
            />
        @endif
    </x-ui.surface-card>

    <x-ui.surface-card class="space-y-4">
        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Current setup') }}</p>
        <div class="space-y-3">
            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Template') }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ $selectedTemplate?->category?->name ?: __('Custom prompt') }}</p>
            </div>
            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Output') }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ __(':count results - :words words target', ['count' => $totalResults, 'words' => $approximateWords]) }}</p>
            </div>
            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Hashtags') }}</p>
                <p class="mt-2 text-sm leading-6" style="color: var(--theme-header-text-color);">{{ ucfirst($hashtagMode) }}</p>
            </div>
        </div>
    </x-ui.surface-card>
</aside>
