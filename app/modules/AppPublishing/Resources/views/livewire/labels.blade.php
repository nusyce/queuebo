<div class="space-y-6 px-4 pb-6 pt-4 sm:px-5 xl:px-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Publishing Labels') }}</p>
            <h1 class="mt-1 text-[1.6rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Labels Workspace') }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Use Labels to organize, filter and report on your content.') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button :href="route('portal.publishing.calendar')" variant="outline" wire:navigate>{{ __('Calendar') }}</x-ui.button>
            <x-ui.button type="button" wire:click="createNew">{{ __('New Label') }}</x-ui.button>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[24rem_minmax(0,1fr)]">
        <section class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.98);">
            <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit Label') : __('Create Label') }}</p>
            </div>

            <div class="space-y-4 px-5 py-5">
                <x-ui.input wire:model.defer="form.name" :label="__('Label name')" :error="$errors->first('form.name')" :placeholder="__('Launch')" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <x-ui.label>{{ __('Status') }}</x-ui.label>
                        <select wire:model.defer="form.status" class="flex h-11 w-full rounded-[0.75rem] border px-4 text-sm font-medium" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-ui.color-picker wire:model.defer="form.color" :label="__('Accent color')" :value="$form['color'] ?? '#2563eb'" :error="$errors->first('form.color')" />
                </div>

                <x-ui.textarea wire:model.defer="form.description" :label="__('Description')" :error="$errors->first('form.description')" rows="4" placeholder="{{ __('What this label should be used for across the publishing workflow...') }}">{{ $form['description'] ?? '' }}</x-ui.textarea>

                <div class="flex items-center gap-3 pt-2">
                    <x-ui.button type="button" wire:click="save">{{ $editingId ? __('Update Label') : __('Create Label') }}</x-ui.button>
                    <x-ui.button type="button" variant="outline" wire:click="createNew">{{ __('Reset') }}</x-ui.button>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Total') }}</p>
                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(16,185,129,0.05);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Active') }}</p>
                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($summary['active']) }}</p>
                </div>
                <div class="rounded-[1.15rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(99,102,241,0.05);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Draft') }}</p>
                    <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($summary['draft']) }}</p>
                </div>
            </div>

            <div class="rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.98);">
                <div class="flex flex-col gap-3 border-b px-5 py-4 lg:flex-row lg:items-center lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Label Inventory') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Manage reusable labels for composer organization and reporting.') }}</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.input wire:model.live.debounce.300ms="search" :placeholder="__('Search labels...')" />
                        <select wire:model.live="statusFilter" class="flex h-11 w-full rounded-[0.75rem] border px-4 text-sm font-medium" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                            <option value="all">{{ __('All statuses') }}</option>
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="divide-y" style="divide-color: rgba(var(--theme-border-color-rgb), 0.68);">
                    @forelse ($labels as $label)
                        <div class="grid gap-4 px-5 py-4 lg:grid-cols-[minmax(0,1fr)_12rem_12rem] lg:items-center">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-3.5 w-3.5 shrink-0 rounded-full" style="background-color: {{ $label->color }};"></span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $label->name }}</p>
                                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $label->description ?: __('No label description yet.') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Status') }}</p>
                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ str($label->status)->headline() }}</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                <x-ui.button type="button" size="sm" variant="outline" wire:click="edit({{ $label->id }})">{{ __('Edit') }}</x-ui.button>
                                <x-ui.button type="button" size="sm" variant="secondary" wire:click="duplicate({{ $label->id }})">{{ __('Duplicate') }}</x-ui.button>
                                <x-ui.button type="button" size="sm" variant="danger" wire:click="delete({{ $label->id }})">{{ __('Delete') }}</x-ui.button>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8">
                            <x-ui.empty
                                :icon="'fa-light fa-tags'"
                                :title="__('No Labels Yet')"
                                :description="__('No publishing labels found yet. Create the first one to organize and report on your scheduled content.')"
                            >
                                <x-ui.button type="button" size="sm" wire:click="createNew">
                                    <i class="fa-light fa-plus"></i>
                                    {{ __('Create Label') }}
                                </x-ui.button>
                            </x-ui.empty>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
