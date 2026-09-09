<div class="space-y-8 px-4 pb-10 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
    ">
        <div class="grid gap-6 px-6 py-7 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div class="space-y-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Bulk Publishing') }}</p>
                    <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('Bulk Post') }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Upload one CSV, pick the channels, define the posting interval, and let the system distribute the schedule automatically.') }}</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Step 1') }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose accounts') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Step 2') }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Upload CSV') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Step 3') }}</p>
                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Campaign, labels, interval') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                <x-ui.button :href="route('portal.bulk-posts.template')" size="lg">
                    <i class="fa-light fa-file-arrow-down"></i>
                    {{ __('Bulk Template') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-4xl">
        <form wire:submit="save" class="space-y-5">
            <x-ui.card class="p-0 overflow-visible">
                <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">1</span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Select channels') }}</p>
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose one or more connected social accounts for this bulk upload.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-6 sm:px-8">
                    <x-ui.channel-selector
                        name="account_ids"
                        wire-model="account_ids"
                        :options="$bulkChannelOptions"
                        :network-options="$bulkChannelNetworks"
                        :selected="collect($account_ids)->map(fn ($id) => (string) $id)->all()"
                        :label="__('Accounts')"
                        :description="__('Choose one or more connected social accounts for this bulk upload.')"
                        :error="$errors->first('account_ids') ?: $errors->first('account_ids.*')"
                        :placeholder="__('Choose one or more accounts')"
                        :empty-label="__('No matching channels found.')"
                        :multiple="true"
                        :show-network-filters="true"
                        :connect-href="route('portal.channels')"
                        :connect-label="__('Connect a channel')"
                        :inline-panel="true"
                    />
                </div>
            </x-ui.card>

            <x-ui.card class="p-0 overflow-hidden">
                <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">2</span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Upload CSV') }}</p>
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use the bulk template so your caption and schedule columns match the importer.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 px-6 py-6 sm:px-8">
                    <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="max-w-lg">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('CSV source file') }}</p>
                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Upload the spreadsheet that contains the post rows. Each row becomes one scheduled publishing item for each selected account.') }}</p>
                            </div>
                            <x-ui.button :href="route('portal.bulk-posts.template')" variant="outline">{{ __('Download Template') }}</x-ui.button>
                        </div>

                        <div class="mt-5">
                            <div class="space-y-2.5">
                                <x-ui.label>{{ __('Media CSV file') }}</x-ui.label>
                                <label
                                    for="bulk-posts-csv-file"
                                    class="flex min-h-[3.5rem] w-full cursor-pointer items-center justify-between gap-4 rounded-[0.9rem] border px-4 py-3 transition hover:border-[var(--theme-accent)]"
                                    style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                                >
                                    <input
                                        id="bulk-posts-csv-file"
                                        type="file"
                                        wire:model="csv_file"
                                        accept=".csv,text/csv"
                                        class="sr-only"
                                    >
                                    <span class="min-w-0 flex-1 truncate text-sm font-medium">
                                        {{ $csv_file?->getClientOriginalName() ?: __('Choose CSV file') }}
                                    </span>
                                    <span class="inline-flex h-9 items-center rounded-[0.75rem] border px-3 text-sm font-medium"
                                        style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.9); color: var(--theme-header-text-color);">
                                        {{ __('Browse') }}
                                    </span>
                                </label>
                                @error('csv_file')
                                    <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-0 overflow-hidden">
                <div class="border-b px-6 py-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">3</span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Publishing settings') }}</p>
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Apply campaign, labels, and spacing before posts enter Publishing.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 px-6 py-6 sm:px-8">
                    <div class="grid gap-4 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                        <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                            <x-ui.select
                                wire:model="campaign_id"
                                :label="__('Campaign')"
                                :error="$errors->first('campaign_id')"
                            >
                                <option value="">{{ __('No campaign') }}</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}{{ $campaign->status !== 'active' ? ' ('.str($campaign->status)->headline().')' : '' }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                            <x-ui.input
                                wire:model="interval_minutes"
                                type="number"
                                min="1"
                                step="1"
                                :label="__('Interval per post (minute)')"
                                :error="$errors->first('interval_minutes')"
                            />
                        </div>
                    </div>

                    <div class="rounded-[1.05rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                        <x-ui.tag-selector
                            name="label_ids"
                            wire-model="label_ids"
                            :label="__('Labels')"
                            :options="$labels->map(fn ($label) => ['key' => (string) $label->id, 'label' => $label->name])->all()"
                            :selected="collect($label_ids)->map(fn ($id) => (string) $id)->all()"
                            :description="__('Use Labels to organize, filter and report on imported posts.')"
                            :placeholder="__('Search or pick labels...')"
                            :empty-label="__('No matching labels found.')"
                            :error="$errors->first('label_ids') ?: $errors->first('label_ids.*')"
                        />
                    </div>

                    <div class="rounded-[1rem] border px-4 py-4 text-sm leading-7" style="border-color: rgba(245,158,11,0.28); background-color: rgba(254,243,199,0.75); color: #9a6700;">
                        {{ __('If the CSV has no valid schedule time, the first post will use the current time and the next posts will follow this interval.') }}
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden p-0">
                <div class="border-b px-6 py-5 sm:px-8" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to import') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Upload the CSV now. The importer will validate rows and add valid items directly into Publishing.') }}</p>
                </div>

                <div class="flex flex-col gap-4 px-6 py-6 sm:px-8">
                    <div class="flex items-start gap-3 rounded-[1rem] border px-4 py-4 text-sm leading-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.7); color: var(--theme-muted-text-color);">
                        <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                            <i class="fa-light fa-bolt text-sm"></i>
                        </span>
                        <span>{{ __('Valid rows will be pushed straight into the posts table so you can manage them in Publishing immediately after import.') }}</span>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" size="lg" wire:loading.attr="disabled" wire:target="save,csv_file">
                            <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
                            <span wire:loading wire:target="save">{{ __('Importing...') }}</span>
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </form>
    </div>
</div>
