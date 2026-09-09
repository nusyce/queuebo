<aside>
    <x-ui.surface-card class="space-y-5">
        @if ((string) $selectedCategoryId === '')
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Categories') }}</p>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Choose a category to open its example prompts.') }}</p>
            </div>

            <x-ui.input
                wire:model.live.debounce.300ms="categorySearch"
                :label="__('Search categories')"
                :placeholder="__('Search categories...')"
            />

            @if (count($categoryOptions) > 0)
                <div class="mt-4 max-h-[42rem] space-y-2 overflow-y-auto pr-1">
                    @foreach ($categoryOptions as $category)
                        @php($colors = $category->colorClasses())
                        <button
                            type="button"
                            wire:click="selectCategory({{ $category->id }})"
                            class="flex w-full items-start gap-3 rounded-[1rem] border px-4 py-3 text-left transition"
                            style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);"
                        >
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.95rem] ring-1 {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['ring'] }}">
                                <i class="{{ $category->icon ?: 'fa-light fa-folder-tree' }}"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $category->name }}</span>
                                <span class="mt-1 block text-xs" style="color: var(--theme-muted-text-color);">{{ number_format((int) $category->templates_count) }} {{ __('templates') }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            @else
                @if (trim((string) $categorySearch) !== '')
                    <div class="mt-4 rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, transparent); color: var(--theme-muted-text-color);">
                        {{ __('No categories match your search.') }}
                    </div>
                @else
                    <div class="mt-4">
                        <x-ui.empty
                            icon="fa-light fa-folder-open"
                            :title="__('No AI template categories yet')"
                            :description="__('Create template categories first to build a reusable caption prompt library.')"
                        />
                    </div>
                @endif
            @endif
        @else
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="min-w-0 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI Templates') }}</p>
                    <x-ui.button type="button" size="sm" variant="outline" wire:click="backToCategories">
                        {{ __('Back') }}
                    </x-ui.button>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @php($selectedCategory = collect($categoryOptions)->firstWhere('id', (int) $selectedCategoryId))
                    @if ($selectedCategory)
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent); color: var(--theme-muted-text-color);">
                            <i class="{{ $selectedCategory->icon ?: 'fa-light fa-folder-tree' }}"></i>
                            <span>{{ $selectedCategory->name }}</span>
                        </span>
                    @endif
                    @if ($selectedTemplate)
                        <x-ui.button type="button" size="sm" variant="outline" wire:click="clearTemplate">
                            {{ __('Clear') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="pt-1">
                <x-ui.input
                    wire:model.live.debounce.300ms="templateSearch"
                    :label="__('Search')"
                    :placeholder="__('Search prompts...')"
                />
            </div>

            @if ($templates->isNotEmpty())
                <div class="mt-4 max-h-[42rem] space-y-3 overflow-y-auto pr-1">
                    @foreach ($templates as $template)
                        <button
                            type="button"
                            x-on:click="
                                window.dispatchEvent(new CustomEvent('caption-template-selected', {
                                    detail: {
                                        content: @js(trim((string) $template->content)),
                                    }
                                }));
                                $wire.selectTemplate({{ $template->id }});
                            "
                            class="w-full rounded-[1rem] border px-4 py-3 text-left transition"
                            style="
                                border-color: {{ (int) $selectedTemplateId === (int) $template->id ? 'rgba(var(--theme-accent-rgb), 0.42)' : 'rgba(var(--theme-border-color-rgb), 0.46)' }};
                                background: {{ (int) $selectedTemplateId === (int) $template->id ? 'rgba(var(--theme-accent-rgb), 0.08)' : 'color-mix(in srgb, var(--theme-surface-base) 95%, transparent)' }};
                            "
                        >
                            <p class="text-sm font-medium leading-6" style="color: var(--theme-header-text-color);">{{ $template->contentPreview(138) }}</p>
                            <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ $template->category?->name ?: __('Uncategorized') }}</p>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="mt-4">
                    <x-ui.empty
                        icon="fa-light fa-magnifying-glass"
                        :title="__('No templates found')"
                        :description="__('Try another search or go back to choose a different category.')"
                    />
                </div>
            @endif
        @endif
    </x-ui.surface-card>
</aside>
