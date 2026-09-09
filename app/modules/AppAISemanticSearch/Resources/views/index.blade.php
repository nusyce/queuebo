<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.page-hero
        :eyebrow="__('AI Studio')"
        :title="__('Semantic Search')"
        :description="__('Search captions and publishing posts by intent, neighboring meaning, and content similarity instead of exact keyword matches.')"
        icon="fa-light fa-compass"
    />

    <section class="grid gap-4 lg:grid-cols-3">
        <x-ui.metric-card
            :label="__('Search mode')"
            :value="__('Meaning-based')"
            :description="__('Intent-led retrieval')"
            icon="fa-light fa-brain-circuit"
            accent="primary"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Result cap')"
            :value="__('Top 12')"
            :description="__('Maximum ranked matches')"
            icon="fa-light fa-list-ol"
            accent="success"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Credits')"
            :value="($creditPreview['amount'] ?? 0) . ' ' . __('per search')"
            :description="__('Estimated semantic lookup cost')"
            icon="fa-light fa-coins"
            accent="warning"
            class="min-h-[150px]"
        />
    </section>

    <section class="grid gap-5 xl:grid-cols-[21rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('How it works') }}</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('1. Query intent') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Search with natural language phrases like launch, promo, educational, or engagement.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('2. Match nearby meaning') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('The engine scores captions and posts even when the wording is similar rather than exact.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('3. Reuse') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Use the best matches to avoid duplication and recover strong content ideas faster.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Credit preview') }}</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('This run') }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $creditPreview['amount'] ?? 0 }} {{ __('credits') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Remaining') }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="space-y-5">
            <div class="rounded-[1.25rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Search workspace content') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Search with topic language instead of exact strings to surface nearby posts, captions, and concepts.') }}</p>
                    </div>
                    <div class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        {{ __('Workspace-wide lookup') }}
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-3 md:flex-row">
                    <div class="flex-1">
                        <x-ui.input wire:model.defer="searchQuery" :error="$errors->first('searchQuery')" :placeholder="__('launch, promo, discount, educational, engagement...')" />
                    </div>
                    <x-ui.button type="button" wire:click="search" wire:loading.attr="disabled" wire:target="search" :disabled="!($creditPreview['enough'] ?? true)">
                        <i class="fa-light fa-magnifying-glass"></i>
                        <span wire:loading.remove wire:target="search">{{ __('Search') }}</span>
                        <span wire:loading wire:target="search">{{ __('Searching...') }}</span>
                    </x-ui.button>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-coins text-xs" style="color: var(--theme-accent);"></i>
                        <span>{{ __(':credits credits', ['credits' => $creditPreview['amount'] ?? 0]) }}</span>
                        <span>&bull;</span>
                        <span>{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</span>
                    </div>
                </div>
                @if (!($creditPreview['enough'] ?? true))
                    <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ __('Not enough credits remaining for this action.') }}</p>
                    @include(theme_view('partials.credit-topup-cta', 'app'))
                @endif

                <div wire:loading.flex wire:target="search" class="mt-4 items-center gap-3 rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-muted-text-color);">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-loader animate-spin"></i>
                    </span>
                    <div>
                        <p class="font-medium" style="color: var(--theme-header-text-color);">{{ __('Searching for related content...') }}</p>
                        <p class="text-xs sm:text-sm">{{ __('AI is ranking nearby matches across captions and publishing content.') }}</p>
                    </div>
                </div>
            </div>

            @if ($result)
                <div class="rounded-[1.3rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Search results') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Highest-scoring matches across captions and publishing content.') }}</p>
                        </div>
                        <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                            {{ count($result) }} {{ __('matches') }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-4 2xl:grid-cols-2">
                        @foreach ($result as $item)
                            <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background:
                                linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.04), transparent 28%),
                                color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['title'] }}</p>
                                    <span class="text-xs uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ strtoupper((string) $item['type']) }} &bull; {{ $item['score'] }}</span>
                                </div>
                                <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $item['excerpt'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <x-ui.empty
                    class="rounded-[1.3rem] border px-6 py-10 sm:px-7"
                    icon="fa-light fa-compass"
                    :title="__('No semantic matches yet')"
                    :description="__('Run a query to see ranked caption and publishing matches with intent-based scoring.')"
                >
                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to search') }}</span>
                        <span>&bull;</span>
                        <span>{{ __('Query, match, reuse.') }}</span>
                    </div>
                </x-ui.empty>
            @endif
        </div>
    </section>
</div>
