<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.page-hero
        :eyebrow="__('AI Studio')"
        :title="__('Repurpose')"
        :description="__('Turn one long-form source into multiple platform-native assets, each shaped for a different publishing format.')"
        icon="fa-light fa-code-branch"
    />

    <section class="grid gap-4 lg:grid-cols-3">
        <x-ui.metric-card
            :label="__('Tone')"
            :value="ucfirst($tone)"
            :description="__('Current rewrite voice')"
            icon="fa-light fa-waveform-lines"
            accent="primary"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Language')"
            :value="$languageLabel"
            :description="__('Output language target')"
            icon="fa-light fa-language"
            accent="success"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Credits')"
            :value="($creditPreview['amount'] ?? 0) . ' ' . __('per run')"
            :description="__('Estimated rewrite cost')"
            icon="fa-light fa-coins"
            accent="warning"
            class="min-h-[150px]"
        />
    </section>

    <section class="grid gap-5 xl:grid-cols-[21rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-sliders text-[1rem] leading-none"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Rewrite setup') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Set voice, language, and destinations before repurposing the source.') }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    <x-ui.select wire:model.live="tone" :label="__('Tone')">
                        @foreach ($toneOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.language-select
                        wire:model.live="language"
                        :label="__('Language')"
                        :preferred="['vi', 'en']"
                    />
                </div>
            </div>

            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Target platforms') }}</p>
                @if (!empty($platformOptions))
                    <div class="mt-4 grid gap-2">
                        @foreach ($platformOptions as $platform)
                            <div class="rounded-[1rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                                <x-ui.checkbox
                                    wire:model.live="selectedPlatforms"
                                    :value="$platform['value']"
                                    :label="$platform['label']"
                                />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4">
                        <x-ui.empty
                            icon="fa-light fa-share-nodes"
                            :title="__('No connected socials yet')"
                            :description="__('Connect one or more social channels first, then use those destinations here for repurposing output.')"
                        >
                            <x-ui.button :href="route('portal.channels')" variant="outline" wire:navigate>
                                {{ __('Open Channels') }}
                            </x-ui.button>
                        </x-ui.empty>
                    </div>
                @endif
            </div>

            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Repurpose flow') }}</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('1. Source') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Paste a blog section, campaign note, sales pitch, or post draft.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('2. Adapt') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('AI reframes the same idea into shorter, platform-native outputs.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('3. Reuse') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Pick the strongest angle and move it into publishing or captions.') }}</p>
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

            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt history') }}</p>
                @if ($promptHistory->isNotEmpty())
                    <div class="mt-4 max-h-[22rem] space-y-2 overflow-y-auto pr-1">
                        @foreach ($promptHistory as $history)
                            <button type="button" wire:click="loadPromptHistory({{ $history->id }})" class="w-full rounded-[1rem] border px-4 py-3 text-left transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Repurpose prompt') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 78) }}</p>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4">
                        <x-ui.empty
                            icon="fa-light fa-book-open"
                            :title="__('No prompt history yet')"
                            :description="__('Repurpose source prompts will be saved here for quick reuse.')"
                        />
                    </div>
                @endif
            </div>
        </aside>

        <div class="space-y-5">
            <div class="rounded-[1.25rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Source content') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Feed the system with a longer source, then let AI compress it into multiple packaging styles.') }}</p>
                    </div>
                    <div class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        {{ __('Cross-format rewrite') }}
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.textarea wire:model.defer="sourceContent" :label="__('Source content')" :error="$errors->first('sourceContent')" rows="9" placeholder="{{ __('Paste a blog excerpt, sales message, or long-form draft...') }}">{{ $sourceContent }}</x-ui.textarea>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" wire:click="repurpose" wire:loading.attr="disabled" wire:target="repurpose" :disabled="!($creditPreview['enough'] ?? true)">
                        <i class="fa-light fa-code-branch"></i>
                        <span wire:loading.remove wire:target="repurpose">{{ __('Repurpose content') }}</span>
                        <span wire:loading wire:target="repurpose">{{ __('Repurposing...') }}</span>
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

                <div wire:loading.flex wire:target="repurpose" class="mt-4 items-center gap-3 rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-muted-text-color);">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-loader animate-spin"></i>
                    </span>
                    <div>
                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Repurposing content') }}</p>
                        <p class="mt-1">{{ __('AI is rewriting your source into multiple publishing formats.') }}</p>
                    </div>
                </div>
            </div>

            @if ($result)
                <div class="rounded-[1.3rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Repurposed outputs') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Review the strongest format and keep the best angle for scheduling.') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.button
                                type="button"
                                size="sm"
                                variant="outline"
                                wire:click="saveAllResults"
                                wire:loading.attr="disabled"
                                wire:target="saveAllResults"
                                :disabled="empty($result['items'] ?? [])"
                            >
                                <i class="fa-light fa-books"></i>
                                <span wire:loading.remove wire:target="saveAllResults">{{ __('Save all') }}</span>
                                <span wire:loading wire:target="saveAllResults">{{ __('Saving all...') }}</span>
                            </x-ui.button>
                            <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                                {{ strtoupper((string) ($result['source'] ?? '')) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 2xl:grid-cols-2">
                        @foreach (($result['items'] ?? []) as $item)
                            <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background:
                                linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.04), transparent 28%),
                                color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-base font-semibold leading-6" style="color: var(--theme-header-text-color);">{{ $item['title'] ?: strtoupper((string) $item['target']) }}</p>
                                    <div class="flex items-center gap-2">
                                        @if (in_array($loop->index, $savedResultIndexes ?? [], true))
                                            <x-ui.badge variant="success">{{ __('Saved') }}</x-ui.badge>
                                        @endif
                                        <x-ui.badge variant="primary">{{ $item['format'] }}</x-ui.badge>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ strtoupper((string) $item['target']) }}</p>
                                <p class="mt-3 text-sm leading-7 whitespace-pre-line" style="color: var(--theme-header-text-color);">{{ $item['caption'] }}</p>
                                @if (!empty($item['notes']))
                                    <p class="mt-4 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $item['notes'] }}</p>
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
                                        <span wire:loading wire:target="saveResultCaption({{ $loop->index }})">
                                            {{ __('Saving...') }}
                                        </span>
                                    </x-ui.button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <x-ui.empty
                    class="rounded-[1.3rem] border px-6 py-10 sm:px-7"
                    icon="fa-light fa-repeat"
                    :title="__('No repurpose output yet')"
                    :description="__('Your first run will appear here as reusable content cards for each target format.')"
                >
                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to adapt') }}</span>
                        <span>&bull;</span>
                        <span>{{ __('Source, remix, reuse.') }}</span>
                    </div>
                </x-ui.empty>
            @endif
        </div>
    </section>
</div>
