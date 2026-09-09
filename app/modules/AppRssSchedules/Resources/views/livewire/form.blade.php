<div class="space-y-8 px-4 pb-10 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
    ">
        <div class="grid gap-6 px-6 py-7 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Content automation') }}</p>
                <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $isEditing ? __('Edit RSS Schedule') : __('Create RSS Schedule') }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Configure channels, feed rules, and recurring schedule slots in the same visual flow used by AI Publishing.') }}</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <x-ui.button :href="route('portal.rss-schedules.index')" variant="outline" size="lg">{{ __('Back to schedules') }}</x-ui.button>
            </div>
        </div>
    </section>

    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(22rem,0.8fr)]">
        <div class="space-y-6">
            <x-ui.card padding="none" class="overflow-hidden">
                <div class="border-b px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('RSS Source') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Define the feed endpoint and how this automation should appear in your workspace.') }}</p>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <x-ui.input wire:model.defer="feed_url" :label="__('RSS URL')" :error="$errors->first('feed_url')" :placeholder="__('Enter your RSS URL')" required />

                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-ui.input wire:model.defer="name" :label="__('Schedule name')" :error="$errors->first('name')" :placeholder="__('Optional. Auto-filled from feed title if empty.')" />
                        <x-ui.input wire:model.defer="description" :label="__('Description')" :error="$errors->first('description')" :placeholder="__('Optional short description')" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card padding="none" class="overflow-hidden">
                <div class="border-b px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Channels') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose the active channels that should receive publishing posts created from this feed.') }}</p>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <x-ui.channel-selector
                        name="account_ids"
                        wire-model="account_ids"
                        :options="$channelOptions"
                        :network-options="$channelNetworks"
                        :selected="collect($account_ids)->map(fn ($id) => (string) $id)->all()"
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
                        :description="__('Use labels to organize and filter RSS-generated posts in Publishing.')"
                        :placeholder="__('Search or pick labels...')"
                        :empty-label="__('No matching labels found.')"
                        :error="$errors->first('label_ids') ?: $errors->first('label_ids.*')"
                    />
                </div>
            </x-ui.card>

            <x-ui.card padding="none" class="overflow-hidden">
                <div class="border-b px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Content Rules') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Control how feed items are transformed before they enter Publishing.') }}</p>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <div class="grid gap-4 lg:grid-cols-3">
                        @if ($urlShortenerAvailable)
                            <x-ui.checkbox wire:model.defer="url_shorten" value="1" :label="__('Auto URL Shortener')" />
                        @endif
                        <x-ui.checkbox wire:model.defer="deny_link" value="1" :label="__('Publish posts without link')" />
                        <x-ui.checkbox wire:model.defer="accept_caption" value="1" :label="__('Post the caption')" />
                    </div>

                    <div class="rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background:
                        linear-gradient(180deg, rgba(124, 58, 237, 0.04), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
                    ">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(124, 58, 237, 0.10); color: #7c3aed;">
                                <i class="fa-light fa-sparkles text-sm"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <x-ui.checkbox wire:model.defer="enable_ai_rewrite" value="1" :label="__('Enable AI rewrite')" />
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('When enabled, RSS items will be rewritten into a fresh caption before entering Publishing. If AI fails, the original RSS caption is used.') }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <x-ui.select wire:model.defer="ai_rewrite_tone" :label="__('Tone of voice')" :error="$errors->first('ai_rewrite_tone')">
                                @foreach ($toneOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </x-ui.select>

                            <x-ui.language-select
                                wire:model.defer="ai_rewrite_language"
                                :label="__('Language')"
                                :error="$errors->first('ai_rewrite_language')"
                                :preferred="['vi', 'en']"
                            />
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <x-ui.input
                                type="number"
                                min="0"
                                step="1"
                                wire:model.defer="ai_rewrite_max_per_run"
                                :label="__('Max AI rewrites per run')"
                                :error="$errors->first('ai_rewrite_max_per_run')"
                                :placeholder="__('0 = unlimited')"
                                :help="__('How many RSS items can use AI rewrite in one manual/automatic run. Enter 0 for unlimited.')"
                            />

                            <x-ui.input
                                type="number"
                                min="0"
                                step="1"
                                wire:model.defer="ai_rewrite_monthly_credit_limit"
                                :label="__('Monthly AI credit limit')"
                                :error="$errors->first('ai_rewrite_monthly_credit_limit')"
                                :placeholder="__('0 = unlimited')"
                                :help="__('Maximum AI credits this schedule may spend in one month. Enter 0 for unlimited.')"
                            />
                        </div>

                        <div class="mt-4">
                            <x-ui.textarea
                                wire:model.defer="ai_rewrite_prompt"
                                :label="__('Rewrite prompt')"
                                :error="$errors->first('ai_rewrite_prompt')"
                                rows="4"
                                :placeholder="__('Example: Rewrite the caption to be short, persuasive, and suitable for Facebook. Keep key facts accurate.')"
                            />
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs" style="color: var(--theme-muted-text-color);">
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5" style="border-color: rgba(124, 58, 237, 0.16); background-color: rgba(124, 58, 237, 0.08); color: #6d28d9;">
                                <i class="fa-light fa-coins text-[11px]"></i>
                                {{ __(':credits credit per rewrite', ['credits' => $aiRewriteCreditPreview['unit_cost'] ?? 0]) }}
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                                {{ ($aiRewriteCreditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits credits left', ['credits' => number_format((int) ($aiRewriteCreditPreview['remaining'] ?? 0))]) }}
                            </span>
                        </div>
                    </div>

                    <x-ui.input
                        wire:model.defer="referral_code"
                        :label="__('Referral Code')"
                        :error="$errors->first('referral_code')"
                        :placeholder="__('Example: your-ref-code')"
                        :help="__('A unique code used to refer new users and track invitations.')"
                    />

                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-ui.textarea wire:model.defer="include_keywords" :label="__('Only publish posts containing these words')" :error="$errors->first('include_keywords')" rows="4" />
                        <x-ui.textarea wire:model.defer="ignore_keywords" :label="__('Do not publish posts containing these words')" :error="$errors->first('ignore_keywords')" rows="4" />
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card padding="none" class="relative z-20 overflow-visible">
                <div class="border-b px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Schedule Regularly') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Pick the time slots, weekdays, and validity range for this RSS automation.') }}</p>
                </div>

                <div
                    x-data="{
                        slots: $wire.entangle('time_posts'),
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
                    class="space-y-6 px-5 py-5 sm:px-6"
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
                        @error('time_posts')<p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>@enderror
                        @error('time_posts.*')<p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>@enderror

                        <button type="button" x-on:click="addSlot()" class="flex h-11 w-full items-center justify-center gap-2 rounded-[0.85rem] border text-sm font-medium transition hover:border-[var(--theme-accent)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.05); color: var(--theme-header-text-color);">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Add time') }}
                        </button>
                    </div>

                    <div class="space-y-3">
                        <x-ui.label>{{ __('Schedule every') }}</x-ui.label>
                        <div class="grid gap-3 md:grid-cols-7">
                            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                <button
                                    type="button"
                                    x-on:click="toggleDay('{{ $day }}')"
                                    class="rounded-[0.95rem] border px-3 py-5 text-sm font-medium transition"
                                    x-bind:style="hasDay('{{ $day }}')
                                        ? 'border-color: transparent; background-color: var(--theme-accent); color: white;'
                                        : 'border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96); color: var(--theme-header-text-color);'"
                                >
                                    {{ __($day) }}
                                </button>
                            @endforeach
                        </div>
                        @error('weekdays')<p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4">
                        <x-ui.date-picker class="relative z-30" name="start_date" wire:model.defer="start_date" :label="__('Start date')" :value="$start_date" :error="$errors->first('start_date')" :placeholder="__('Choose start date')" />
                        <x-ui.date-picker class="relative z-20" name="end_date" wire:model.defer="end_date" :label="__('End date')" :value="$end_date" :error="$errors->first('end_date')" :placeholder="__('Choose end date')" />
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card padding="none" class="relative z-0 overflow-hidden">
                <div class="flex flex-col gap-4 px-5 py-4 sm:px-6">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $isEditing ? __('Update schedule') : __('Create schedule') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Eligible RSS items will be converted into publishing posts and inserted directly into Publishing.') }}</p>
                    </div>

                    <x-ui.checkbox wire:model.defer="status" value="1" :label="__('Active schedule')" />

                    <x-ui.button type="submit" size="lg">
                        {{ $isEditing ? __('Save changes') : __('Create RSS Schedule') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </form>
</div>
