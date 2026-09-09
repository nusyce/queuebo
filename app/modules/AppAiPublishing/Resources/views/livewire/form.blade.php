<div class="space-y-8 px-4 pb-10 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
    ">
        <div class="grid gap-6 px-6 py-7 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('AI content studio') }}</p>
                <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $isEditing ? __('Edit AI Publishing') : __('Create AI Publishing') }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Prompt management and schedule configuration now live on a dedicated create/edit page, separate from the schedules list.') }}</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <x-ui.button :href="route('portal.ai-publishing')" variant="outline" size="lg">{{ __('Back to schedules') }}</x-ui.button>
            </div>
        </div>
    </section>

    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(22rem,0.8fr)]">
        <div class="space-y-6">
            <x-ui.card padding="none" class="overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <div>
                        <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt list') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Create and select prompts for this schedule.') }}</p>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-6 sm:px-8">
                    <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                        linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-soft-rgb,248,250,252),0.72));
                    ">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                <i class="fa-light fa-pen-line text-sm"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt composer') }}</p>
                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Write a new idea, topic, or instruction, then add it to the prompt library for this schedule.') }}</p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <x-ui.textarea
                                wire:model.defer="newPrompt"
                                :label="__('New prompt')"
                                rows="5"
                                :placeholder="__('Describe the topic, audience, tone, offer, or product you want AI to generate content for...')"
                                :error="$errors->first('newPrompt')"
                            />
                        </div>

                        <div class="mt-4 flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Tip: one prompt usually works best when it contains a single clear content angle.') }}</p>
                            <x-ui.button type="button" wire:click="addPrompt" size="lg">
                                <i class="fa-light fa-plus"></i>
                                {{ __('Add prompt') }}
                            </x-ui.button>
                        </div>
                    </div>

                    <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.94);">
                        <x-ui.input wire:model.live.debounce.300ms="promptSearch" :label="__('Search')" :placeholder="__('Search prompt...')" />
                    </div>

                    <div class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.94);">
                        <div class="border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt library') }}</p>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                    {{ number_format($prompts->count()) }} {{ __('items') }}
                                </span>
                            </div>
                        </div>

                    <div class="max-h-[34rem] overflow-y-auto">
                        @forelse ($prompts as $prompt)
                            @php($selected = in_array((string) $prompt->id, $selectedPromptIds, true))
                            <label class="flex cursor-pointer items-start gap-4 border-b px-4 py-4 transition last:border-b-0 hover:bg-slate-900/[0.02]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                                <x-ui.checkbox
                                    wire:model.defer="selectedPromptIds"
                                    value="{{ $prompt->id }}"
                                    minimal
                                    class="mt-1"
                                />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        @if ($selected)
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">{{ __('Selected') }}</span>
                                        @endif
                                        <span class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Prompt') }} #{{ $prompt->id }}</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-7" style="color: var(--theme-header-text-color);">{{ $prompt->prompt_text }}</p>
                                </div>
                                <button type="button" wire:click="deletePrompt({{ $prompt->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem] border transition hover:bg-slate-900/[0.03]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); color: var(--theme-muted-text-color);" title="{{ __('Delete prompt') }}">
                                    <i class="fa-light fa-trash"></i>
                                </button>
                            </label>
                        @empty
                            <x-ui.empty
                                icon="fa-light fa-rectangle-history"
                                :title="__('No prompts yet.')"
                                :description="__('Add your first prompt to start building the schedule prompt library.')"
                            />
                        @endforelse
                    </div>
                    </div>
                    @error('selectedPromptIds')<p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>@enderror
                </div>
            </x-ui.card>

            <x-ui.card padding="none" class="overflow-visible">
                <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI Configuration') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose channels, metadata, and how AI should generate the content.') }}</p>
                </div>

                <div class="space-y-5 px-6 py-6 sm:px-8">
                    <x-ui.input wire:model.defer="runName" :label="__('Run name')" :placeholder="__('Weekly AI publishing')" :error="$errors->first('runName')" />

                    <x-ui.channel-selector
                        name="account_ids"
                        wire-model="account_ids"
                        :options="$bulkChannelOptions"
                        :network-options="$bulkChannelNetworks"
                        :selected="collect($account_ids)->map(fn ($id) => (string) $id)->all()"
                        :label="__('Accounts')"
                        :description="__('Choose one or more connected social accounts for this AI publishing run.')"
                        :error="$errors->first('account_ids') ?: $errors->first('account_ids.*')"
                        :placeholder="__('Choose one or more accounts')"
                        :empty-label="__('No matching channels found.')"
                        :multiple="true"
                        :show-network-filters="true"
                        :connect-href="route('portal.channels')"
                        :connect-label="__('Connect a channel')"
                        :inline-panel="true"
                    />

                    <x-ui.select wire:model.defer="campaign_id" :label="__('Campaign')" :error="$errors->first('campaign_id')">
                        <option value="">{{ __('No campaign') }}</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}{{ $campaign->status !== 'active' ? ' ('.str($campaign->status)->headline().')' : '' }}</option>
                        @endforeach
                    </x-ui.select>

                        <x-ui.tag-selector
                            name="label_ids"
                            wire-model="label_ids"
                            :live="false"
                            :label="__('Labels')"
                        :options="$labels->map(fn ($label) => ['key' => (string) $label->id, 'label' => $label->name])->all()"
                        :selected="collect($label_ids)->map(fn ($id) => (string) $id)->all()"
                        :description="__('Use labels to organize and filter AI-generated posts in Publishing.')"
                        :placeholder="__('Search or pick labels...')"
                        :empty-label="__('No matching labels found.')"
                        :error="$errors->first('label_ids') ?: $errors->first('label_ids.*')"
                    />

                    <div class="grid gap-4 lg:grid-cols-3">
                        <x-ui.select wire:model.defer="include_media" :label="__('Include media')" :error="$errors->first('include_media')">
                            @foreach ($mediaSourceGroups as $group)
                                <optgroup label="{{ $group['label'] }}">
                                    @foreach ($group['options'] as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </x-ui.select>

                        <x-ui.select wire:model.defer="tone" :label="__('Tone of voice')">
                            @foreach ($toneOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.language-select
                            wire:model.defer="language"
                            :label="__('Language')"
                            :error="$errors->first('language')"
                            :preferred="['vi', 'en']"
                        />
                    </div>

                    @if (! $searchMediaOnlineEnabled)
                        <x-ui.empty
                            icon="fa-light fa-ban"
                            :title="__('Search Media Online is disabled')"
                            :description="__('Online media sources are hidden in AI Publishing because your current plan or workspace does not allow Search Media Online.')"
                        />
                    @elseif (! $hasOnlineMediaProviders)
                        <x-ui.empty
                            icon="fa-light fa-photo-film"
                            :title="__('No provider enabled')"
                            :description="__('Enable at least one provider such as Unsplash, Pexels, or Pixabay in Files settings to use online media sources here.')"
                        />
                    @endif

                    <div class="grid gap-4 lg:grid-cols-3">
                        <x-ui.checkbox wire:model.defer="include_hashtags" value="1" :label="__('Include hashtags')" />
                        <x-ui.checkbox wire:model.defer="include_cta" value="1" :label="__('Include CTA')" />
                        <x-ui.checkbox wire:model.defer="allow_emoji" value="1" :label="__('Allow emoji')" />
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card padding="none" class="relative z-20 overflow-visible">
                <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Schedule Regularly') }}</p>
                </div>

                <div
                    x-data="{
                        slots: $wire.entangle('time_slots'),
                        selectedDays: $wire.entangle('weekdays'),
                        addSlot() {
                            this.slots = [...(this.slots || []), '09:00'];
                        },
                        removeSlot(index) {
                            const next = [...(this.slots || [])];
                            next.splice(index, 1);
                            this.slots = next.length ? next : ['09:00'];
                        },
                        toggleDay(day) {
                            const current = [...(this.selectedDays || [])];
                            this.selectedDays = current.includes(day)
                                ? current.filter((item) => item !== day)
                                : [...current, day];
                        },
                        hasDay(day) {
                            return (this.selectedDays || []).includes(day);
                        }
                    }"
                    class="space-y-6 px-6 py-6 sm:px-8"
                >
                    <div class="space-y-3">
                        <template x-for="(slot, index) in slots" :key="'slot-'+index">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <x-ui.time-picker x-model="slots[index]" :placeholder="__('Select time')" class="flex-1" />
                                </div>
                                <button type="button" x-on:click="removeSlot(index)" class="inline-flex h-11 w-11 items-center justify-center rounded-[0.85rem] border transition hover:bg-slate-900/[0.03]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); color: var(--theme-muted-text-color);">
                                    <i class="fa-light fa-trash"></i>
                                </button>
                            </div>
                        </template>
                        @error('time_slots')<p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>@enderror
                        @error('time_slots.*')<p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>@enderror

                        <button type="button" x-on:click="addSlot()" class="flex h-11 w-full items-center justify-center gap-2 rounded-[0.85rem] border text-sm font-medium transition hover:border-[var(--theme-accent)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.05); color: var(--theme-header-text-color);">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Add time') }}
                        </button>
                    </div>

                    <div class="space-y-3">
                        <x-ui.label>{{ __('Schedule every') }}</x-ui.label>
                        <div class="grid gap-3 md:grid-cols-7">
                            @foreach ($weekdayOptions as $option)
                                <button
                                    type="button"
                                    x-on:click="toggleDay('{{ $option['key'] }}')"
                                    class="rounded-[0.95rem] border px-3 py-5 text-sm font-medium transition"
                                    x-bind:style="hasDay('{{ $option['key'] }}')
                                        ? 'border-color: transparent; background-color: var(--theme-accent); color: white;'
                                        : 'border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96); color: var(--theme-header-text-color);'"
                                >
                                    {{ __($option['label']) }}
                                </button>
                            @endforeach
                        </div>
                        @error('weekdays')<p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4">
                        <x-ui.date-picker
                            wire:model.defer="start_at"
                            name="start_at"
                            :label="__('Start date')"
                            :value="$start_at"
                            :error="$errors->first('start_at')"
                            :placeholder="__('Choose start date')"
                        />
                        <x-ui.date-picker
                            wire:model.defer="end_at"
                            name="end_at"
                            :label="__('End date')"
                            :value="$end_at"
                            :error="$errors->first('end_at')"
                            :placeholder="__('Choose end date')"
                        />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card padding="none" class="relative z-0 overflow-hidden">
                <div class="flex flex-col gap-4 px-6 py-5 sm:px-8">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $isEditing ? __('Update schedule') : __('Create schedule') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Selected prompts will generate AI items and valid posts will be inserted directly into Publishing.') }}</p>
                    </div>

                    @if ($include_media === 'ai_image')
                        <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                            <div class="flex flex-wrap items-center gap-2 text-sm" style="color: var(--theme-muted-text-color);">
                                <i class="fa-light fa-sparkles text-xs" style="color: var(--theme-accent);"></i>
                                <span>{{ __('AI Studio image credit') }}</span>
                                <span>&bull;</span>
                                <span>{{ __(':credits credits per image', ['credits' => $creditPreview['amount'] ?? 0]) }}</span>
                                <span>&bull;</span>
                                <span>{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</span>
                            </div>
                        </div>

                        @if (!($creditPreview['enough'] ?? true))
                            <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ __('Not enough credits remaining for AI image generation.') }}</p>
                        @endif
                    @endif

                    <x-ui.button type="submit" size="lg" wire:loading.attr="disabled" :disabled="$include_media === 'ai_image' && !($creditPreview['enough'] ?? true)">
                        <span wire:loading.remove>{{ $isEditing ? __('Save changes') : __('Run AI Publishing') }}</span>
                        <span wire:loading>{{ __('Generating...') }}</span>
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </form>
</div>
