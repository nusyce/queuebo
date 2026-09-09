<div class="min-w-0 max-w-full overflow-x-hidden space-y-8 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="min-w-0 max-w-full overflow-hidden rounded-[1.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at 0% 0%, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-base-rgb,255,255,255),0.94));
    ">
        <div class="grid min-w-0 gap-6 px-4 py-6 sm:px-8 sm:py-7 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
            <div class="flex min-w-0 items-start gap-4">
                <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.35rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                    <i class="fa-light fa-link text-lg"></i>
                </span>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-accent);">{{ __('Workspace channels') }}</p>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.74); color: var(--theme-muted-text-color);">
                            {{ trans_choice(':count records', $accountTotal, ['count' => number_format($accountTotal)]) }}
                        </span>
                    </div>

                    <h1 class="mt-2 text-[1.85rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('Channels') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Manage connected destinations, keep provider access healthy, and keep publishing-ready accounts easy to operate.') }}</p>
                </div>
            </div>

            <div class="flex min-w-0 flex-wrap items-center gap-3 lg:justify-end">
                @if ($plan?->name)
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76); color: var(--theme-header-text-color); box-shadow: 0 12px 30px -24px rgba(15,23,42,0.28);">
                        <i class="fa-light fa-badge-check"></i>
                        {{ $plan->name }}
                    </span>
                @endif

                <x-ui.modal width="xl" :title="__('Connect new channels')" :description="__('Choose a provider and launch the right capability flow for this workspace.')">
                    <x-slot:trigger>
                        @if ($canCreateChannels)
                            <x-ui.button type="button" size="lg">
                                <i class="fa-light fa-plus"></i>
                                {{ __('Add channels') }}
                            </x-ui.button>
                        @else
                            <x-ui.button type="button" size="lg" disabled>
                                <i class="fa-light fa-lock"></i>
                                {{ __('Add channels') }}
                            </x-ui.button>
                        @endif
                    </x-slot:trigger>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($providerCards as $provider)
                        @php
                            $providerModalDescription = __('Connect :provider accounts and keep them available inside this workspace.', [
                                'provider' => $provider['label'],
                            ]);
                            $providerTone = publishing_provider_tone((string) ($provider['key'] ?? ''));
                        @endphp
                        <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: var(--theme-surface-base);">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-base" style="background-color: {{ $providerTone['surface'] }}; color: {{ $providerTone['text'] }};">
                                    <i class="{{ $provider['icon'] }}"></i>
                                </span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $provider['label'] }}</h4>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em]" style="background-color: {{ $provider['is_ready'] ? 'rgba(var(--theme-success-color-rgb),0.12)' : 'rgba(var(--theme-warning-color-rgb),0.12)' }}; color: {{ $provider['is_ready'] ? 'var(--theme-success-color)' : 'var(--theme-warning-color)' }};">
                                            {{ $provider['is_ready'] ? __('Ready') : __('Setup') }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $providerModalDescription }}</p>
                                </div>
                            </div>

                            <div @class([
                                'mt-4 gap-2',
                                'grid' => count($provider['capabilities']) > 1,
                                'space-y-3' => count($provider['capabilities']) === 1,
                            ])>
                                @foreach ($provider['capabilities'] as $capability)
                                    <div class="min-w-0">
                                        <button
                                            type="button"
                                            wire:click="createAccount('{{ $capability['key'] }}')"
                                            x-on:click="open = false"
                                            class="w-full rounded-[0.95rem] border px-4 py-2.5 text-sm font-medium transition"
                                            @if ($provider['is_ready'] && $canCreateChannels)
                                                style="border-color: rgba(var(--theme-border-color-rgb), 0.68); color: var(--theme-header-text-color); background-color: rgba(var(--theme-border-color-rgb), 0.03);"
                                            @else
                                                disabled
                                                style="border-color: rgba(var(--theme-border-color-rgb), 0.45); color: var(--theme-muted-text-color); background-color: rgba(var(--theme-border-color-rgb), 0.03); opacity: .55; cursor: not-allowed;"
                                            @endif
                                        >
                                            <i class="fa-light fa-plus mr-1.5 text-xs"></i>{{ $capability['connect_label'] ?? $capability['label'] }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.modal>
            </div>
        </div>
    </section>

    <div class="grid min-w-0 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card
            :label="__('Total')"
            :value="number_format($accountTotal)"
            :description="__('All connected destinations available to this workspace.')"
            icon="fa-light fa-link"
            icon-style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);"
        />

        <x-ui.stat-card
            :label="__('Active')"
            :value="number_format($activeAccounts)"
            :description="__('Channels currently ready to receive publishing actions.')"
            icon="fa-light fa-circle-check"
            icon-style="background-color: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);"
            value-style="color: var(--theme-success-color);"
        />

        <x-ui.stat-card
            :label="__('Paused')"
            :value="number_format($pausedAccounts)"
            :description="__('Accounts paused from publishing but still kept in the workspace inventory.')"
            icon="fa-light fa-pause"
            icon-style="background-color: rgba(var(--theme-warning-color-rgb),0.1); color: var(--theme-warning-color);"
            value-style="color: var(--theme-warning-color);"
        />

        <x-ui.stat-card
            :label="__('Recent')"
            :value="number_format($connectedThisMonth)"
            :description="__('Channels connected during the last 30 days across all providers.')"
            icon="fa-light fa-sparkles"
            icon-style="background-color: rgba(var(--theme-info-color-rgb,59,130,246),0.1); color: var(--theme-info-color, #3b82f6);"
        />
    </div>

    @if ($bannerMessage)
        <x-ui.card
            @class([
                'border-amber-200 bg-amber-50/80 dark:border-amber-500/20 dark:bg-amber-500/10' => $bannerTone === 'warning',
                'border-emerald-200 bg-emerald-50/80 dark:border-emerald-500/20 dark:bg-emerald-500/10' => $bannerTone === 'success',
                'border-rose-200 bg-rose-50/80 dark:border-rose-500/20 dark:bg-rose-500/10' => $bannerTone === 'danger',
            ])
        >
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.08); color: var(--theme-header-text-color);">
                    <i class="fa-light {{ $bannerTone === 'success' ? 'fa-circle-check' : ($bannerTone === 'danger' ? 'fa-circle-xmark' : 'fa-circle-exclamation') }}"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                        {{ $bannerTone === 'success' ? __('Channel updated') : ($bannerTone === 'danger' ? __('Channel action failed') : __('Channel attention required')) }}
                    </p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $bannerMessage }}</p>
                </div>
            </div>
        </x-ui.card>
    @endif

    @if (blank($providerCards))
        <x-ui.card>
            <div class="rounded-[1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.07), rgba(var(--theme-border-color-rgb), 0.03));">
                <p class="text-sm leading-7" style="color: var(--theme-muted-text-color);">
                    {{ __('Install and register channel modules first. Once a provider is registered, this dashboard will expose its connection workflows here.') }}
                </p>
            </div>
        </x-ui.card>
    @else
        <div class="min-w-0 max-w-full space-y-8">
            @if ($currentProvider && ! $currentProvider['is_ready'])
                <x-ui.card>
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Provider setup required') }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">
                                {{ __('This provider is visible, but cannot accept a clean connection flow yet.') }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-warning-color-rgb),0.28); background-color: rgba(var(--theme-warning-color-rgb),0.06);">
                                <p class="text-sm font-semibold" style="color: var(--theme-warning-color);">{{ __('Missing requirements') }}</p>
                                <div class="mt-3 space-y-2">
                                    @forelse ($currentProvider['missing_fields'] as $field)
                                        <div class="flex items-center gap-2 text-sm" style="color: var(--theme-warning-color);">
                                            <i class="fa-light fa-circle-small"></i>
                                            <span>{{ str($field)->replace('_', ' ')->headline() }}</span>
                                        </div>
                                    @empty
                                        <p class="text-sm" style="color: var(--theme-warning-color);">{{ __('The provider is disabled in API Integration.') }}</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('What to do') }}</p>
                                <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                    {{ __('Complete the API Integration credentials first, then return here to connect channels for this provider.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            @if ($showAccountForm && $selectedCapabilityDefinition)
                @php
                    $selectedCapabilityTone = publishing_provider_tone((string) data_get($selectedCapabilityDefinition, 'provider_key', data_get($selectedCapabilityDefinition, 'key', '')));
                @endphp
                <x-theme.section-card
                    :title="__('Channel editor')"
                    :description="__('Capture identity, connection metadata, and internal ops notes for the selected capability.')"
                    body-class="space-y-6 p-6"
                >
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] text-lg" style="background-color: {{ $selectedCapabilityTone['surface'] }}; color: {{ $selectedCapabilityTone['text'] }};">
                                <i class="{{ data_get($selectedCapabilityDefinition, 'icon', 'fa-light fa-share-nodes') }}"></i>
                            </span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Selected capability') }}</p>
                                <h4 class="mt-1 text-lg font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ data_get($selectedCapabilityDefinition, 'title', __('Channel')) }}</h4>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ data_get($selectedCapabilityDefinition, 'description', __('Store channel metadata and connection details for this destination.')) }}</p>
                            </div>
                        </div>

                        <button type="button" wire:click="cancelAccountForm" class="rounded-[0.9rem] border px-4 py-2 text-sm font-semibold transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); color: var(--theme-muted-text-color);">
                            {{ __('Close editor') }}
                        </button>
                    </div>

                    <form wire:submit="saveAccount" class="space-y-5">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <x-ui.input wire:model="accountForm.display_name" :label="__('Display name')" :error="$errors->first('accountForm.display_name')" />
                            <x-ui.input wire:model="accountForm.username" :label="__('Username / handle')" :error="$errors->first('accountForm.username')" />
                            <x-ui.input wire:model="accountForm.external_id" :label="__('External ID')" :error="$errors->first('accountForm.external_id')" />
                            <x-ui.select wire:model="accountForm.category" :label="__('Category')" :error="$errors->first('accountForm.category')">
                                @foreach ($categoryOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.select wire:model="accountForm.account_type" :label="__('Connection type')" :error="$errors->first('accountForm.account_type')">
                                @foreach ($accountTypeOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </x-ui.select>
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                <x-ui.radio-group name="accountForm.is_active" wire:model.live="accountForm.is_active" :label="__('Status')" :options="$statusOptions" :value="$accountForm['is_active']" />
                            </div>
                            <x-ui.input wire:model="accountForm.profile_url" :label="__('Profile URL')" :error="$errors->first('accountForm.profile_url')" />
                            <x-ui.input wire:model="accountForm.avatar_url" :label="__('Avatar URL')" :error="$errors->first('accountForm.avatar_url')" />
                            <x-ui.input wire:model="accountForm.reconnect_url" :label="__('Reconnect URL')" :error="$errors->first('accountForm.reconnect_url')" />
                            <x-ui.input wire:model="accountForm.scopes" :label="__('Scopes')" :error="$errors->first('accountForm.scopes')" />
                            <div class="lg:col-span-2">
                                <x-ui.textarea wire:model="accountForm.access_token" :label="__('Access token')" :error="$errors->first('accountForm.access_token')">{{ $accountForm['access_token'] }}</x-ui.textarea>
                            </div>
                            <div class="lg:col-span-2">
                                <x-ui.textarea wire:model="accountForm.refresh_token" :label="__('Refresh token')" :error="$errors->first('accountForm.refresh_token')">{{ $accountForm['refresh_token'] }}</x-ui.textarea>
                            </div>
                            <div class="lg:col-span-2">
                                <x-ui.textarea wire:model="accountForm.auth_data_json" :label="__('Auth payload JSON')" :error="$errors->first('accountForm.auth_data_json')">{{ $accountForm['auth_data_json'] }}</x-ui.textarea>
                            </div>
                            <div class="lg:col-span-2">
                                <x-ui.textarea wire:model="accountForm.metadata_json" :label="__('Metadata JSON')" :error="$errors->first('accountForm.metadata_json')">{{ $accountForm['metadata_json'] }}</x-ui.textarea>
                            </div>
                            <div class="lg:col-span-2">
                                <x-ui.textarea wire:model="accountForm.notes" :label="__('Internal notes')" :error="$errors->first('accountForm.notes')">{{ $accountForm['notes'] }}</x-ui.textarea>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-4 border-t pt-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68);">
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('This record inherits provider-level credentials from API Integration and stores the account-specific metadata here.') }}</p>
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="cancelAccountForm" class="rounded-[0.9rem] border px-4 py-2 text-sm font-semibold transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); color: var(--theme-muted-text-color);">{{ __('Cancel') }}</button>
                                <x-ui.button type="submit">{{ __('Save channel') }}</x-ui.button>
                            </div>
                        </div>
                    </form>
                </x-theme.section-card>
            @endif

            <section class="min-w-0 max-w-full space-y-5">
                <div class="min-w-0 max-w-full rounded-[1.5rem] border px-4 py-4 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
                    linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.98), rgba(var(--theme-surface-soft-rgb,248,250,252),0.74));
                ">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="w-full max-w-[40rem]">
                            <x-ui.icon-input
                                wire:model.live.debounce.300ms="search"
                                icon="fa-light fa-magnifying-glass"
                                :placeholder="__('Search')"
                            />
                        </div>

                        <div class="grid min-w-0 w-full grid-cols-2 gap-2 sm:flex sm:w-auto sm:flex-wrap sm:items-center">
                            <x-ui.button type="button" variant="outline" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" wire:click="toggleSelectAllVisible">
                                <i class="fa-light {{ $allVisibleSelected ? 'fa-square-check' : 'fa-square' }}"></i>
                                {{ $allVisibleSelected ? __('Uncheck all') : __('Check all') }}
                            </x-ui.button>

                            <x-ui.dropdown-menu align="right" width="auto" class="min-w-[18rem]">
                                <x-slot:trigger>
                                    <x-ui.button type="button" variant="outline" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto">
                                        <i class="fa-light fa-filter"></i>
                                        {{ __('Filters') }}
                                    </x-ui.button>
                                </x-slot:trigger>

                                <div class="min-w-[18rem]">
                                    <div class="border-b px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.55);">
                                        <p class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Filters') }}</p>
                                    </div>

                                    <div class="space-y-4 px-3 py-3">
                                        <div>
                                            <p class="mb-2 text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Status') }}</p>
                                            <x-ui.select wire:model.live="statusFilter">
                                                @foreach ($statusFilterOptions as $option)
                                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>

                                        <div>
                                            <p class="mb-2 text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Social network') }}</p>
                                            <x-ui.select wire:model.live="activeProvider">
                                                <option value="">{{ __('All') }}</option>
                                                @foreach ($providerCards as $provider)
                                                    <option value="{{ $provider['key'] }}">{{ $provider['label'] }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>
                                    </div>
                                </div>
                            </x-ui.dropdown-menu>

                            <x-ui.dropdown-menu align="right" width="auto" class="min-w-[11rem]">
                                <x-slot:trigger>
                                    <x-ui.button type="button" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto">
                                        <i class="fa-light fa-arrow-down-wide-short"></i>
                                        {{ __('Sort') }}
                                    </x-ui.button>
                                </x-slot:trigger>

                                <div class="min-w-[11rem] p-1.5">
                                    @foreach ($sortOptions as $option)
                                        <x-ui.dropdown-menu-item
                                            type="button"
                                            wire:click="$set('sortBy', '{{ $option['value'] }}')"
                                            :icon="$sortBy === $option['value'] ? 'fa-light fa-check' : 'fa-light fa-arrow-down-wide-short'"
                                        >
                                            {{ $option['label'] }}
                                        </x-ui.dropdown-menu-item>
                                    @endforeach
                                </div>
                            </x-ui.dropdown-menu>

                            <x-ui.dropdown-menu align="right" width="auto" class="min-w-[10.5rem]">
                                <x-slot:trigger>
                                    <x-ui.button type="button" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" :disabled="$selectedAccountCount === 0">
                                        <i class="fa-light fa-grid-2"></i>
                                        {{ __('Actions') }}
                                    </x-ui.button>
                                </x-slot:trigger>

                                <div class="min-w-[10.5rem] p-1.5">
                                    @if ($canToggleChannelStatus)
                                        <x-ui.dropdown-menu-item type="button" icon="fa-light fa-check" wire:click="bulkActivateSelected">
                                            {{ __('Active') }}
                                        </x-ui.dropdown-menu-item>
                                        <x-ui.dropdown-menu-item type="button" icon="fa-light fa-pause" wire:click="bulkPauseSelected">
                                            {{ __('Pause') }}
                                        </x-ui.dropdown-menu-item>
                                    @endif

                                    @if ($canDeleteChannels)
                                        <x-ui.dropdown-menu-item type="button" icon="fa-light fa-trash" variant="danger" wire:click="bulkDeleteSelected" wire:confirm="{{ __('Delete selected channels?') }}">
                                            {{ __('Delete') }}
                                        </x-ui.dropdown-menu-item>
                                    @endif
                                </div>
                            </x-ui.dropdown-menu>

                        </div>
                    </div>

                </div>

                <div class="hidden">
                    <x-action-message class="text-emerald-600 dark:text-emerald-400" on="portal-account-saved">{{ __('Channel updated.') }}</x-action-message>
                    <x-action-message class="text-emerald-600 dark:text-emerald-400" on="portal-account-deleted">{{ __('Channel removed.') }}</x-action-message>
                </div>

                <div class="grid min-w-0 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <?php if ($accounts->isEmpty()): ?>
                        <div class="md:col-span-2 xl:col-span-4">
                            <x-ui.empty :title="__('No channels match this view')" :description="__('Adjust the filters or connect a new channel to populate this inventory.')" />
                        </div>
                    <?php else: ?>
                    <?php foreach ($accounts as $account): ?>
                            @php
                                $capability = $capabilityMap[$account->capability_key] ?? [];
                                $provider = $providerRegistry[$account->provider_key] ?? [];
                                $providerColor = data_get($provider, 'color', '#64748b');
                                $providerTone = publishing_provider_tone((string) $account->provider_key);
                                $usesLocalSocialAvatar = str_contains((string) ($account->avatar_url ?? ''), '/media/public/social-accounts/')
                                    || str_contains((string) ($account->avatar_url ?? ''), '/storage/social-accounts/');
                                $avatarFallback = collect(preg_split('/\s+/', trim((string) $account->display_name)) ?: [])
                                    ->filter()
                                    ->map(fn (string $part): string => \Illuminate\Support\Str::of($part)->substr(0, 1)->upper()->toString())
                                    ->take(2)
                                    ->implode('');

                                if ($avatarFallback === '') {
                                    $avatarFallback = \Illuminate\Support\Str::of((string) $account->username)->replace('@', '')->substr(0, 2)->upper()->toString();
                                }

                                if ($avatarFallback === '') {
                                    $avatarFallback = 'TG';
                                }
                            @endphp

                            <div class="flex min-w-0 max-w-full h-full flex-col rounded-[1.55rem] border p-4 sm:p-5" style="border-color: {{ in_array($account->id, $selectedAccountIds, true) ? 'rgba(var(--theme-accent-rgb), 0.48)' : 'rgba(var(--theme-border-color-rgb), 0.54)' }}; background:
                                radial-gradient(circle at top left, {{ $providerTone['surface'] }} 0%, transparent 24%),
                                linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.995), rgba(var(--theme-surface-base-rgb,255,255,255),0.965));
                                box-shadow: 0 22px 54px -42px rgba(15,23,42,0.16);">
                                <div class="min-h-[5.5rem] flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span class="relative inline-flex h-14 w-14 shrink-0 items-center justify-center text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                            <span class="inline-flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border shadow-[0_16px_34px_-26px_rgba(15,23,42,0.18)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background-color: {{ $usesLocalSocialAvatar ? 'rgba(255,255,255,0.96)' : $providerTone['surface'] }};">
                                                @if ($account->avatar_url)
                                                    @if ($usesLocalSocialAvatar)
                                                        <span class="inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-[0.8rem] bg-white shadow-[0_10px_20px_-14px_rgba(15,23,42,0.24)] ring-1 ring-slate-200/70">
                                                            <img src="{{ $account->avatar_url }}" alt="{{ $account->display_name }}" class="h-full w-full object-contain">
                                                        </span>
                                                    @else
                                                        <img src="{{ $account->avatar_url }}" alt="{{ $account->display_name }}" class="h-full w-full object-cover">
                                                    @endif
                                                @else
                                                    <span class="text-[1rem] font-bold tracking-[-0.05em] leading-none" style="color: {{ $providerTone['text'] }};">
                                                        {{ $avatarFallback }}
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="absolute bottom-0 right-0 inline-flex h-[1.15rem] w-[1.15rem] translate-x-[10%] translate-y-[10%] items-center justify-center rounded-full border-2 text-[8px] shadow-sm" style="border-color: var(--theme-surface-base); background-color: {{ $providerColor }}; color: white;">
                                                <i class="{{ data_get($provider, 'icon', 'fa-light fa-share-nodes') }}"></i>
                                            </span>
                                        </span>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="truncate text-base font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $account->display_name }}</p>
                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="background-color: {{ $account->is_active ? 'rgba(var(--theme-success-color-rgb),0.14)' : 'rgba(var(--theme-warning-color-rgb),0.14)' }}; color: {{ $account->is_active ? 'var(--theme-success-color)' : 'var(--theme-warning-color)' }}; box-shadow: inset 0 1px 0 rgba(255,255,255,0.5);">
                                                    {{ $account->is_active ? __('Active') : __('Paused') }}
                                                </span>
                                            </div>

                                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                                                <?php if ($account->username): ?>
                                                    <span class="font-medium" style="color: {{ $providerTone['text'] }};">{{ '@'.$account->username }}</span>
                                                    <span class="mx-2">•</span>
                                                <?php endif; ?>
                                                <span>{{ data_get($capability, 'title', $account->category ?: __('Channel')) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <label class="inline-flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-[0.7rem] border transition" style="border-color: {{ in_array($account->id, $selectedAccountIds, true) ? 'rgba(var(--theme-accent-rgb), 0.42)' : 'rgba(var(--theme-border-color-rgb), 0.62)' }}; background-color: {{ in_array($account->id, $selectedAccountIds, true) ? 'rgba(var(--theme-accent-rgb), 0.12)' : 'rgba(var(--theme-surface-base-rgb,255,255,255),0.8)' }};">
                                        <input type="checkbox" class="sr-only" wire:model.live="selectedAccountIds" value="{{ $account->id }}">
                                        <i class="fa-light {{ in_array($account->id, $selectedAccountIds, true) ? 'fa-check' : 'fa-plus' }} text-xs" style="color: {{ in_array($account->id, $selectedAccountIds, true) ? 'var(--theme-accent)' : 'var(--theme-muted-text-color)' }};"></i>
                                    </label>
                                </div>

                                <div class="mt-auto flex min-w-0 items-center gap-2 border-t pt-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.54);">
                                    <?php if ($account->profile_url): ?>
                                        <a href="{{ $account->profile_url }}" target="_blank" class="inline-flex min-w-0 flex-1 items-center justify-center rounded-[0.95rem] border px-4 py-2.5 text-sm font-medium transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.56); color: var(--theme-header-text-color); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82); box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);">
                                            <i class="fa-light fa-arrow-up-right-from-square mr-1.5 text-xs"></i>{{ __('Open') }}
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($canReconnectChannels): ?>
                                        <button type="button" wire:click="reconnectAccount({{ $account->id }})" class="inline-flex min-w-0 flex-1 items-center justify-center rounded-[0.95rem] border px-4 py-2.5 text-sm font-medium transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.56); color: var(--theme-header-text-color); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82); box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);">
                                            <i class="fa-light fa-rotate-right mr-1.5 text-xs"></i>{{ __('Reconnect') }}
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($canToggleChannelStatus || $canDeleteChannels): ?>
                                        <div x-data="{ open: false }" class="relative" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
                                            <button type="button" x-on:click="open = !open" class="inline-flex h-[2.875rem] w-[2.875rem] shrink-0 items-center justify-center rounded-[0.95rem] border text-sm font-medium transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.56); color: var(--theme-header-text-color); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82); box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);">
                                                <i class="fa-light fa-grid-2 text-xs"></i>
                                            </button>

                                            <div x-cloak x-show="open" x-transition.origin.bottom.right class="absolute bottom-full right-0 z-50 mb-2 w-48 overflow-hidden rounded-[1rem] border p-1.5 shadow-[0_22px_42px_-20px_rgba(15,23,42,0.24)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 78%, transparent 22%); background:
                                                linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.99), rgba(var(--theme-surface-base-rgb,255,255,255),0.95));">
                                                <?php if ($canToggleChannelStatus): ?>
                                                    <button
                                                        type="button"
                                                        wire:click="toggleAccountStatus({{ $account->id }})"
                                                        class="flex w-full items-center gap-2 rounded-[0.85rem] px-3 py-2 text-sm font-medium transition"
                                                        style="color: var(--theme-header-text-color);"
                                                    >
                                                        <i class="fa-light {{ $account->is_active ? 'fa-pause' : 'fa-play' }} text-xs"></i>
                                                        {{ $account->is_active ? __('Pause') : __('Resume') }}
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($canDeleteChannels): ?>
                                                    <x-ui.dialog
                                                        width="sm"
                                                        dismissible
                                                        :title="__('Remove this channel?')"
                                                        :description="__('This permanently removes the selected channel from the workspace.')"
                                                    >
                                                        <x-slot:trigger>
                                                            <button
                                                                type="button"
                                                                x-on:click="open = false"
                                                                class="flex w-full items-center gap-2 rounded-[0.85rem] px-3 py-2 text-sm font-medium transition"
                                                                style="color: var(--theme-error-color, #ef4444);"
                                                            >
                                                                <i class="fa-light fa-trash text-xs"></i>
                                                                {{ __('Delete') }}
                                                            </button>
                                                        </x-slot:trigger>

                                                        <x-slot:footer>
                                                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                            <x-ui.button type="button" variant="danger" wire:click="deleteAccount({{ $account->id }})" x-on:click="open = false">{{ __('Delete channel') }}</x-ui.button>
                                                        </x-slot:footer>
                                                    </x-ui.dialog>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    @endif
</div>
