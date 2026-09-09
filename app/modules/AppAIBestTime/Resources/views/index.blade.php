<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.page-hero
        :eyebrow="__('AI Studio')"
        :title="__('Best Time')"
        :description="__('Analyze channel history and surface the strongest posting windows based on actual workspace behavior.')"
        icon="fa-light fa-clock"
    />

    <section class="grid gap-4 lg:grid-cols-3">
        <x-ui.metric-card
            :label="__('Selected')"
            :value="count($selectedAccountIds) . ' ' . __('channels')"
            :description="__('Connected channels in this run')"
            icon="fa-light fa-share-nodes"
            accent="primary"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Workspace')"
            :value="__('History-based')"
            :description="__('Recommendations use workspace behavior')"
            icon="fa-light fa-chart-network"
            accent="success"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Credits')"
            :value="($creditPreview['amount'] ?? 0) . ' ' . __('per run')"
            :description="__('Estimated recommendation cost')"
            icon="fa-light fa-coins"
            accent="warning"
            class="min-h-[150px]"
        />
    </section>

    <section class="grid gap-5 xl:grid-cols-[21rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Channel set') }}</p>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Pick one or more connected channels to compare timing patterns.') }}</p>
                <div class="mt-4 grid gap-2 max-h-[26rem] overflow-y-auto pr-1">
                    @foreach ($accounts as $account)
                        <div class="rounded-[1rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                            <x-ui.checkbox
                                wire:model.live="selectedAccountIds"
                                :value="$account->id"
                                :label="$account->display_name"
                            />
                        </div>
                    @endforeach
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
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recommendation engine') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Run a timing recommendation pass and compare the highest-confidence windows across the selected channels.') }}</p>
                    </div>
                    <div class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        {{ __('Manual suggestion run') }}
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" wire:click="suggest" wire:loading.attr="disabled" wire:target="suggest" :disabled="!($creditPreview['enough'] ?? true)">
                        <i class="fa-light fa-clock"></i>
                        <span wire:loading.remove wire:target="suggest">{{ __('Suggest times') }}</span>
                        <span wire:loading wire:target="suggest">{{ __('Analyzing...') }}</span>
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

                <div wire:loading.flex wire:target="suggest" class="mt-4 items-center gap-3 rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-muted-text-color);">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-loader animate-spin"></i>
                    </span>
                    <div>
                        <p class="font-medium" style="color: var(--theme-header-text-color);">{{ __('Finding the strongest posting windows...') }}</p>
                        <p class="text-xs sm:text-sm">{{ __('AI is comparing channel history to rank the most promising time slots.') }}</p>
                    </div>
                </div>
            </div>

            @if ($result)
                <div class="rounded-[1.3rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recommended windows') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use these windows as candidate posting slots with confidence and reasoning.') }}</p>
                        </div>
                        <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                            {{ count($result) }} {{ __('slots') }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-4 2xl:grid-cols-2">
                        @foreach ($result as $slot)
                            <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background:
                                linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.04), transparent 28%),
                                color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $slot['label'] }}</p>
                                    <span class="text-xs uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ $slot['confidence'] }}%</span>
                                </div>
                                <x-ui.progress class="mt-3" :value="(int) ($slot['confidence'] ?? 0)" :max="100" />
                                @foreach (($slot['reasons'] ?? []) as $reason)
                                    <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $reason }}</p>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <x-ui.empty
                    class="rounded-[1.3rem] border px-6 py-10 sm:px-7"
                    icon="fa-light fa-timer"
                    :title="__('No timing recommendations yet')"
                    :description="__('Choose channels and run a suggestion pass to see ranked publishing windows here.')"
                >
                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to analyze') }}</span>
                        <span>&bull;</span>
                        <span>{{ __('Select, compare, publish.') }}</span>
                    </div>
                </x-ui.empty>
            @endif
        </div>
    </section>
</div>
