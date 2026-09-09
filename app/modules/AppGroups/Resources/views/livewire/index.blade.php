@php
    $channelProviderRegistry = collect(channel_provider_cards())->keyBy('key');
    $channelOptions = collect($accounts)
        ->map(function ($account) use ($channelProviderRegistry) {
            $provider = $channelProviderRegistry->get((string) $account->provider_key, []);
            $providerLabel = (string) data_get($provider, 'label', str($account->provider_key)->headline());
            $capabilityLabel = (string) ($account->category ?: __('Channel'));
            $providerTone = publishing_provider_tone((string) $account->provider_key);

            return [
                'key' => (string) $account->id,
                'label' => (string) ($account->display_name ?: ($account->username ? '@'.$account->username : strtoupper((string) $account->provider_key))),
                'subtitle' => trim($providerLabel.' '.str($capabilityLabel)->lower()),
                'avatarUrl' => (string) ($account->avatar_url ?? ''),
                'providerKey' => (string) $account->provider_key,
                'providerLabel' => $providerLabel,
                'providerIcon' => (string) data_get($provider, 'icon', ''),
                'providerColor' => (string) data_get($provider, 'color', ''),
                'providerToneSurface' => (string) ($providerTone['surface'] ?? ''),
                'providerToneText' => (string) ($providerTone['text'] ?? ''),
            ];
        })
        ->values()
        ->all();
    $channelNetworks = $channelProviderRegistry
        ->only(collect($accounts)->pluck('provider_key')->filter()->unique()->values()->all())
        ->map(function ($provider) {
            $providerKey = (string) ($provider['key'] ?? '');
            $providerTone = publishing_provider_tone($providerKey);

            return [
                'key' => $providerKey,
                'label' => (string) ($provider['label'] ?? ''),
                'icon' => (string) ($provider['icon'] ?? ''),
                'color' => (string) ($provider['color'] ?? ''),
                'toneSurface' => (string) ($providerTone['surface'] ?? ''),
                'toneText' => (string) ($providerTone['text'] ?? ''),
            ];
        })
        ->filter(fn ($provider) => $provider['key'] !== '' && $provider['label'] !== '')
        ->values()
        ->all();
@endphp

<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ editorOpen: @entangle('editorOpen') }">
    <section class="overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
    ">
        <div class="grid gap-6 px-6 py-7 sm:px-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Workspace clusters') }}</p>
                <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('Groups Workspace') }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Group connected social accounts into reusable publishing clusters so campaign setup, filtering, and future reporting all stay aligned.') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button :href="route('portal.channels')" variant="outline">
                    <i class="fa-light fa-share-nodes"></i>
                    {{ __('Open Channels') }}
                </x-ui.button>
                <x-ui.button type="button" wire:click="openCreateEditor">
                    <i class="fa-light fa-plus"></i>
                    {{ $editingId ? __('New group') : __('Create group') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        <x-ui.metric-card
            :label="__('Total')"
            :value="$stats['total'] ?? 0"
            :description="__('Reusable account clusters available across the workspace.')"
            icon="fa-light fa-layer-group"
            accent="primary"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Active')"
            :value="$stats['active'] ?? 0"
            :description="__('Groups currently ready to use in publishing flows.')"
            icon="fa-light fa-bolt"
            accent="success"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Inactive')"
            :value="$stats['inactive'] ?? 0"
            :description="__('Groups paused or kept for later reuse.')"
            icon="fa-light fa-box-archive"
            accent="warning"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Connected reach')"
            :value="$stats['reach'] ?? 0"
            :description="__('Unique connected accounts represented across groups.')"
            icon="fa-light fa-share-nodes"
            accent="neutral"
            class="min-h-[150px]"
        />
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_28rem]">
        <section class="space-y-5">
            <x-ui.surface-card padding="lg" featured accent="primary">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Group Inventory') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Browse saved groups, filter by status, and reopen any cluster for editing.') }}</p>
                    </div>
                    <x-ui.badge variant="primary">{{ count($groups) }} {{ __('shown') }}</x-ui.badge>
                </div>

                <div class="mt-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_12rem_auto]">
                    <x-ui.input wire:model.live.debounce.300ms="search" :placeholder="__('Search name, description, or slug')" />
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </x-ui.select>
                    <div class="flex items-center gap-3">
                        <x-ui.button type="button" variant="ghost" wire:click="resetFilters">{{ __('Reset') }}</x-ui.button>
                    </div>
                </div>
            </x-ui.surface-card>

            @if ($groups->isNotEmpty())
                <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($groups as $group)
                        <x-ui.surface-card padding="md" class="h-full">
                            <div class="flex h-full flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge variant="neutral">{{ __('GROUP') }}</x-ui.badge>
                                        <x-ui.badge :variant="$group->status === 'active' ? 'success' : 'warning'">
                                            {{ strtoupper($group->status === 'active' ? __('active') : __('inactive')) }}
                                        </x-ui.badge>
                                    </div>
                                    <span class="text-xs font-medium" style="color: var(--theme-muted-text-color);">{{ $group->updated_at?->diffForHumans() }}</span>
                                </div>

                                <div class="mt-4 min-h-[6.75rem]">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-1 inline-flex h-4 w-4 shrink-0 rounded-full border border-white/80 shadow-[0_8px_20px_-14px_rgba(15,23,42,0.35)]" style="background-color: {{ $group->color }};"></span>
                                        <div class="min-w-0">
                                            <p class="text-[1.05rem] font-semibold leading-7 tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $group->name }}</p>
                                            @if (filled($group->description))
                                                <p class="mt-2 text-sm leading-7" style="color: var(--theme-header-text-color);">{{ \Illuminate\Support\Str::limit($group->description, 180) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-[1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background-color: rgba(var(--theme-border-color-rgb), 0.04);">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Accounts') }}</p>
                                        <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                            {{ trans_choice(':count account|:count accounts', $group->accounts_count, ['count' => $group->accounts_count]) }}
                                        </span>
                                    </div>

                                    @if ($group->accounts->isNotEmpty())
                                            <div class="mt-3 flex items-center">
                                                @foreach ($group->accounts->take(5) as $account)
                                                    @php($accountTone = publishing_provider_tone((string) $account->provider_key))
                                                    <span
                                                        class="inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border-2 shadow-[0_10px_20px_-14px_rgba(15,23,42,0.35)] {{ $loop->first ? '' : '-ml-3' }}"
                                                        style="border-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96); background-color: {{ $accountTone['surface'] }};"
                                                        title="{{ $account->display_name }}"
                                                    >
                                                        @if ($account->avatar_url)
                                                            <img src="{{ $account->avatar_url }}" alt="{{ $account->display_name }}" class="h-full w-full object-cover">
                                                        @else
                                                            <span class="text-[11px] font-semibold uppercase tracking-[0.12em]" style="color: {{ $accountTone['text'] }};">
                                                                {{ str($account->display_name)->substr(0, 2)->upper() }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            @if ($group->accounts->count() > 5)
                                                <span class="-ml-3 inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-full border-2 px-2 text-[12px] font-semibold shadow-[0_10px_20px_-14px_rgba(15,23,42,0.35)]"
                                                    style="border-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.96); background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">
                                                    +{{ $group->accounts->count() - 5 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('No accounts assigned yet.') }}</p>
                                    @endif
                                </div>

                                <div class="mt-auto flex items-center gap-2 pt-5">
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="editGroup({{ $group->id }})">{{ __('Edit') }}</x-ui.button>

                                    <x-ui.dialog
                                        width="sm"
                                        dismissible
                                        :title="__('Delete this group?')"
                                        :description="__('This removes the group definition but does not delete any connected social accounts.')"
                                    >
                                        <x-slot:trigger>
                                            <x-ui.button type="button" size="sm" variant="danger">{{ __('Delete') }}</x-ui.button>
                                        </x-slot:trigger>

                                        <x-slot:footer>
                                            <div class="flex items-center justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" variant="danger" size="sm" wire:click="deleteGroup({{ $group->id }})" x-on:click="open = false">
                                                    {{ __('Delete') }}
                                                </x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                </div>
                            </div>
                        </x-ui.surface-card>
                    @endforeach
                </div>
            @else
                <x-ui.empty
                    icon="fa-light fa-layer-group"
                    :title="__('No groups found')"
                    :description="__('Create your first account group to speed up scheduling and prepare for group-level analytics later.')"
                />
            @endif
        </section>

        <div class="hidden xl:block xl:self-start xl:sticky xl:top-24">
            <x-ui.surface-card padding="lg" featured :accent="$editingId ? 'warning' : 'primary'">
                @include('appgroups::livewire.partials.editor-form')
            </x-ui.surface-card>
        </div>
    </div>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="editorOpen"
            class="fixed inset-0 z-[120] flex items-end justify-center p-0 xl:hidden"
            x-on:keydown.escape.window="editorOpen = false"
        >
            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-[4px]" x-on:click="editorOpen = false"></div>

            <div x-show="editorOpen" x-transition.opacity.scale.95 class="relative w-full max-w-2xl">
                <div class="max-h-[88vh] overflow-y-auto rounded-t-[1.4rem] border px-5 py-5 shadow-[0_-24px_70px_-30px_rgba(15,23,42,0.45)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl" style="background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">
                                <i class="fa-light fa-layer-group"></i>
                            </span>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit group') : __('Create group') }}</p>
                        </div>

                        <x-ui.button type="button" variant="ghost" x-on:click="editorOpen = false">
                            <i class="fa-light fa-xmark"></i>
                        </x-ui.button>
                    </div>

                    @include('appgroups::livewire.partials.editor-form')
                </div>
            </div>
        </div>
    </template>
</div>
