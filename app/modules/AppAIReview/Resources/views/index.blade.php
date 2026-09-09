<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.page-hero
        :eyebrow="__('AI Studio')"
        :title="__('AI Review')"
        :description="__('Run a pre-publish quality pass on your draft to catch weak hooks, messaging gaps, and missing fixes before it goes live.')"
        icon="fa-light fa-shield-check"
    />

    <section class="grid gap-4 lg:grid-cols-3">
        <x-ui.metric-card
            :label="__('Review type')"
            :value="__('Pre-publish')"
            :description="__('Final draft quality pass')"
            icon="fa-light fa-clipboard-check"
            accent="primary"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Output language')"
            :value="$languageLabel"
            :description="__('Review output language')"
            icon="fa-light fa-language"
            accent="success"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Credits')"
            :value="($creditPreview['amount'] ?? 0) . ' ' . __('per review')"
            :description="__('Estimated review cost')"
            icon="fa-light fa-coins"
            accent="warning"
            class="min-h-[150px]"
        />
    </section>

    <section class="grid gap-5 xl:grid-cols-[21rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review focus') }}</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Hook') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Checks whether the opening line earns attention quickly enough.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Risk') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Flags weak CTA, low clarity, or platform mismatch before publishing.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Fixes') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Returns concrete next edits instead of only giving a score.') }}</p>
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.language-select
                        wire:model.live="language"
                        :label="__('Output language')"
                        :preferred="['vi', 'en']"
                    />
                    <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">
                        {{ __('Choose the language you want the verdict, strengths, risks, and fixes to be returned in.') }}
                    </p>
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

            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt history') }}</p>
                @if ($promptHistory->isNotEmpty())
                    <div class="mt-4 max-h-[22rem] space-y-2 overflow-y-auto pr-1">
                        @foreach ($promptHistory as $history)
                            <button type="button" wire:click="loadPromptHistory({{ $history->id }})" class="w-full rounded-[1rem] border px-4 py-3 text-left transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Review prompt') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 78) }}</p>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4">
                        <x-ui.empty
                            icon="fa-light fa-book-open"
                            :title="__('No prompt history yet')"
                            :description="__('Reviewed drafts will be stored here so you can reopen them quickly.')"
                        />
                    </div>
                @endif
            </div>
        </aside>

        <div class="space-y-5">
            <div class="rounded-[1.25rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Draft under review') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Paste the draft you want checked and let AI return strengths, risks, and concrete fixes.') }}</p>
                    </div>
                    <div class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        {{ __('Quality pass') }}
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.textarea wire:model.defer="reviewDraft" :label="__('Draft')" :error="$errors->first('reviewDraft')" rows="9" placeholder="{{ __('Paste the draft to review...') }}">{{ $reviewDraft }}</x-ui.textarea>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" wire:click="review" wire:loading.attr="disabled" wire:target="review" :disabled="!($creditPreview['enough'] ?? true)">
                        <i class="fa-light fa-shield-check"></i>
                        <span wire:loading.remove wire:target="review">{{ __('Review draft') }}</span>
                        <span wire:loading wire:target="review">{{ __('Reviewing...') }}</span>
                    </x-ui.button>
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

                <div wire:loading.flex wire:target="review" class="mt-4 items-center gap-3 rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-muted-text-color);">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-loader animate-spin"></i>
                    </span>
                    <div>
                        <p class="font-medium" style="color: var(--theme-header-text-color);">{{ __('Reviewing the draft...') }}</p>
                        <p class="text-xs sm:text-sm">{{ __('AI is checking hook quality, message clarity, risks, and suggested fixes.') }}</p>
                    </div>
                </div>
            </div>

            @if ($result)
                <div class="grid gap-5 2xl:grid-cols-[minmax(0,1.15fr)_24rem]">
                    <div class="rounded-[1.3rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $result['verdict'] ?? __('Review complete') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use this as a final revision pass before sending the draft to publishing.') }}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                                {{ $result['score'] ?? 0 }}/100
                            </span>
                        </div>

                        <div class="mt-5 space-y-4">
                            @foreach (['strengths' => __('Strengths'), 'risks' => __('Risks'), 'fixes' => __('Fixes')] as $key => $label)
                                @if (!empty($result[$key]))
                                    <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background:
                                        linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.04), transparent 28%),
                                        color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ $label }}</p>
                                        @foreach ($result[$key] as $line)
                                            <p class="mt-3 text-sm leading-7" style="color: var(--theme-header-text-color);">{{ $line }}</p>
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review score') }}</p>
                            <x-ui.progress class="mt-4" :value="(int) ($result['score'] ?? 0)" :max="100" :label="__('Review score')" />
                            @if (!empty($result['final_tip']))
                                <p class="mt-4 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $result['final_tip'] }}</p>
                            @endif
                        </div>

                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review intent') }}</p>
                            <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('This module is meant to improve clarity, call-to-action quality, and overall publish-readiness rather than rewrite the whole draft.') }}</p>
                        </div>
                    </div>
                </div>
            @else
                <x-ui.empty
                    class="rounded-[1.3rem] border px-6 py-10 sm:px-7"
                    icon="fa-light fa-shield"
                    :title="__('No review result yet')"
                    :description="__('Your first review run will appear here with a score, clear risks, and actionable fixes.')"
                >
                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to audit') }}</span>
                        <span>&bull;</span>
                        <span>{{ __('Draft, review, improve.') }}</span>
                    </div>
                </x-ui.empty>
            @endif
        </div>
    </section>
</div>
