@component(theme_view('layouts.app', 'app'), [
    'title' => __('Search Media Online'),
    'fullWorkspace' => true,
    'showLoadingBackdrop' => false,
])
    <div
        x-data="{
            keyword: '',
            source: @js((string) array_key_first($providers)),
            providers: @js($providers),
            results: [],
            selected: [],
            searching: false,
            saving: false,
            csrf: document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '',
            searchEndpoint: @js($searchOnlineEndpoint),
            uploadEndpoint: @js($uploadFromUrlEndpoint),
            providerCount() {
                return Object.keys(this.providers || {}).length;
            },
            selectedCount() {
                return this.selected.length;
            },
            notify(type, message, title = '') {
                window.dispatchEvent(new CustomEvent('app-toast', {
                    detail: { type, title, message },
                }));
            },
            responseErrorMessage(payload, fallback) {
                if (typeof payload?.message === 'string' && payload.message.trim() !== '' && payload.message !== 'The given data was invalid.') {
                    return payload.message;
                }

                const errors = payload?.errors || {};

                for (const key of Object.keys(errors)) {
                    const value = errors[key];

                    if (Array.isArray(value) && value[0]) {
                        return String(value[0]);
                    }
                }

                return fallback;
            },
            isSelected(url) {
                return this.selected.includes(String(url || ''));
            },
            toggleSelect(url) {
                url = String(url || '');

                if (!url) return;

                if (this.isSelected(url)) {
                    this.selected = this.selected.filter((item) => item !== url);
                    return;
                }

                this.selected = [...this.selected, url];
            },
            clearSelection() {
                this.selected = [];
            },
            async searchMedia() {
                if (!String(this.keyword || '').trim() || !this.source) {
                    return;
                }

                this.searching = true;

                try {
                    const response = await fetch(this.searchEndpoint, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrf,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            keyword: String(this.keyword || '').trim(),
                            source: this.source,
                            type: 'all',
                        }),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(this.responseErrorMessage(payload, 'Online media search failed.'));
                    }

                    this.results = Array.isArray(payload?.data) ? payload.data : [];
                    this.selected = [];
                } catch (error) {
                    this.results = [];
                    this.notify('error', error?.message || @js(__('Online media search failed.')), @js(__('Search Media Online')));
                } finally {
                    this.searching = false;
                }
            },
            async importUrl(url) {
                const response = await fetch(this.uploadEndpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        remote_url: String(url || ''),
                        type: 'all',
                        folder: '',
                    }),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(this.responseErrorMessage(payload, 'Import failed.'));
                }

                return payload;
            },
            async saveSelected() {
                if (!this.selected.length) {
                    return;
                }

                this.saving = true;

                try {
                    for (const url of this.selected) {
                        await this.importUrl(url);
                    }

                    this.notify('success', @js(__('Imported selected media to Files successfully.')), @js(__('Search Media Online')));
                    this.selected = [];
                } catch (error) {
                    this.notify('error', error?.message || @js(__('Import failed.')), @js(__('Search Media Online')));
                } finally {
                    this.saving = false;
                }
            },
            async importSingle(url) {
                this.saving = true;

                try {
                    await this.importUrl(url);
                    this.notify('success', @js(__('Imported media to Files successfully.')), @js(__('Search Media Online')));
                } catch (error) {
                    this.notify('error', error?.message || @js(__('Import failed.')), @js(__('Search Media Online')));
                } finally {
                    this.saving = false;
                }
            },
        }"
        class="min-h-full bg-[linear-gradient(180deg,rgba(var(--theme-accent-rgb),0.05)_0%,transparent_18%)] px-5 py-5 sm:px-6 xl:px-8"
    >
        <div class="mx-auto max-w-[1540px] space-y-6">
            <template x-if="!providerCount()">
                <x-ui.empty
                    class="rounded-[1.8rem] px-6 py-20"
                    icon="fa-light fa-plug-circle-xmark"
                    :title="__('No provider enabled')"
                    :description="__('Enable at least one media provider in File Manager Settings to start searching online images and videos.')"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                    radial-gradient(circle at top, rgba(var(--theme-accent-rgb), 0.06), transparent 42%),
                    rgba(var(--theme-border-color-rgb), 0.02);
                "
                />
            </template>

            <template x-if="providerCount()">
                <div class="space-y-6">
            <x-ui.page-hero
                :eyebrow="__('Creative Asset Search')"
                :title="__('Search Media Online')"
                :description="__('Find stock images and videos from your connected providers, then send the best picks straight into your Files library without leaving the workspace.')"
                icon="fa-light fa-photo-film-music"
            >
                <x-slot:actions>
                    <div class="grid w-full gap-3 lg:min-w-[460px]">
                        <x-ui.input
                            x-model="keyword"
                            x-on:keydown.enter.prevent="searchMedia()"
                            :label="__('Keyword')"
                            :placeholder="__('Enter keyword')"
                        />
                        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                            <x-ui.select x-model="source" :label="__('Source')">
                                <template x-for="[providerKey, providerLabel] in Object.entries(providers || {})" :key="providerKey">
                                    <option :value="providerKey" x-text="providerLabel"></option>
                                </template>
                            </x-ui.select>
                            <div class="grid content-end gap-3 sm:grid-cols-2">
                                <x-ui.button type="button" class="justify-center" x-on:click="searchMedia()" x-bind:disabled="searching || !keyword.trim()">
                                    <span x-show="!searching">{{ __('Search') }}</span>
                                    <span x-show="searching">{{ __('Searching...') }}</span>
                                </x-ui.button>
                                <x-ui.button type="button" variant="outline" class="justify-center" x-on:click="saveSelected()" x-bind:disabled="saving || !selected.length">
                                    <span x-show="!saving">{{ __('Save To Files') }}</span>
                                    <span x-show="saving">{{ __('Saving...') }}</span>
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                </x-slot:actions>
            </x-ui.page-hero>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <x-ui.card class="min-h-[140px]">
                    <div class="flex h-full items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Providers') }}</p>
                            <p class="mt-4 text-[2.25rem] font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);" x-text="providerCount()"></p>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent);">
                            <i class="fa-light fa-satellite-dish"></i>
                        </span>
                    </div>
                </x-ui.card>
                <x-ui.card class="min-h-[140px]">
                    <div class="flex h-full items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Results') }}</p>
                            <p class="mt-4 text-[2.25rem] font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);" x-text="results.length"></p>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent);">
                            <i class="fa-light fa-grid-2-plus"></i>
                        </span>
                    </div>
                </x-ui.card>
                <x-ui.card class="min-h-[140px]">
                    <div class="flex h-full items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Selected') }}</p>
                            <p class="mt-4 text-[2.25rem] font-semibold leading-none tracking-[-0.05em]" style="color: var(--theme-header-text-color);" x-text="selectedCount()"></p>
                        </div>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent);">
                            <i class="fa-light fa-layer-group"></i>
                        </span>
                    </div>
                </x-ui.card>
            </div>

            <section class="sticky top-[calc(var(--app-shell-header-height,79px)+1rem)] z-10">
                <x-ui.card class="px-4 py-3 shadow-[0_20px_60px_-44px_rgba(15,23,42,0.25)] backdrop-blur-xl" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">
                                <i class="fa-light fa-layer-group"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Selection Tray') }}</p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">
                                    <span x-text="selectedCount()"></span> {{ __('item(s) ready to save into Files') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-ui.button type="button" variant="outline" size="sm" x-on:click="clearSelection()" x-bind:disabled="!selected.length || saving">
                                {{ __('Clear') }}
                            </x-ui.button>
                            <x-ui.button type="button" size="sm" x-on:click="saveSelected()" x-bind:disabled="saving || !selected.length">
                                <span x-show="!saving">{{ __('Save Selected') }}</span>
                                <span x-show="saving">{{ __('Saving...') }}</span>
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            </section>

            <template x-if="!results.length && !searching">
                <x-ui.empty
                    class="rounded-[1.8rem] px-6 py-20"
                    icon="fa-light fa-photo-film"
                    :title="__('Start with a visual idea')"
                    :description="__('Try keywords like \"sunset office\", \"street portrait\", \"product mockup\", or \"cinematic city video\" to build a richer result gallery.')"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                    radial-gradient(circle at top, rgba(var(--theme-accent-rgb), 0.06), transparent 42%),
                    rgba(var(--theme-border-color-rgb), 0.02);
                " />
            </template>

            <template x-if="searching">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <template x-for="index in 8" :key="`skeleton-${index}`">
                        <div class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.95);">
                            <div class="aspect-[4/3] animate-pulse" style="background: linear-gradient(90deg, rgba(var(--theme-border-color-rgb),0.10), rgba(var(--theme-accent-rgb),0.12), rgba(var(--theme-border-color-rgb),0.10));"></div>
                            <div class="space-y-3 px-4 py-4">
                                <div class="h-3 w-20 animate-pulse rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.20);"></div>
                                <div class="h-4 w-full animate-pulse rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.16);"></div>
                                <div class="h-4 w-3/4 animate-pulse rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.12);"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="results.length">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5">
                    <template x-for="media in results" :key="`${media.provider}-${media.id}`">
                        <x-ui.card class="group overflow-hidden !p-0 transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_70px_-40px_rgba(15,23,42,0.34)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.98);">
                            <div class="relative aspect-[4/3] overflow-hidden" style="background-color: rgba(var(--theme-border-color-rgb), 0.05);">
                                <img x-bind:src="media.thumbnail || media.full" x-bind:alt="media.author || media.source || 'media'" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">

                                <div class="absolute inset-x-0 top-0 flex items-start justify-between p-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-header-text-color);">
                                        <span x-text="media.source"></span>
                                    </span>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border shadow-sm transition" x-bind:style="isSelected(media.full) ? 'border-color: rgba(var(--theme-accent-rgb),0.34); background-color: var(--theme-accent); color: white;' : 'border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.92); color: var(--theme-header-text-color);'" x-on:click="toggleSelect(media.full)">
                                        <i class="fa-light" x-bind:class="isSelected(media.full) ? 'fa-check' : 'fa-plus'"></i>
                                    </button>
                                </div>

                                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/40 via-black/8 to-transparent opacity-80"></div>
                            </div>

                            <div class="space-y-4 px-4 py-4">
                                <div class="space-y-1">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="media.author || media.full"></p>
                                    <p class="truncate text-xs" style="color: var(--theme-muted-text-color);" x-text="media.mediaType === 'video' ? '{{ __('Video asset') }}' : '{{ __('Image asset') }}'"></p>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <a x-bind:href="media.link || media.full" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs font-semibold transition hover:opacity-80" style="color: var(--theme-accent);">
                                        <i class="fa-light fa-arrow-up-right-from-square"></i>
                                        <span>{{ __('Preview') }}</span>
                                    </a>
                                    <x-ui.button type="button" size="sm" x-on:click="importSingle(media.full)" x-bind:disabled="saving">
                                        {{ __('Import') }}
                                    </x-ui.button>
                                </div>
                            </div>
                        </x-ui.card>
                    </template>
                </div>
            </template>
                </div>
            </template>
        </div>
    </div>
@endcomponent
