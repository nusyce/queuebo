@php
    $openInstallModal = request()->boolean('install') || $errors->has('module_zip') || $errors->has('purchase_code');
    $installMethod = old('install_method', $errors->has('module_zip') ? 'zip' : 'purchase');
@endphp

<div class="space-y-6">
    @if (session('status'))
        <x-ui.alert variant="success" :title="__('Updated')" :description="session('status')" dismissible />
    @endif

    @if ($errors->has('marketplace'))
        <x-ui.alert :title="__('Marketplace error')" :description="$errors->first('marketplace')" variant="danger" dismissible />
    @endif

        <x-ui.page-hero
            :eyebrow="__('Settings')"
            :title="__('Installed packages')"
            :description="__('Operate module packages that are physically present in this app: install, upgrade, and uninstall.')"
            :count="$summary['total']"
            icon="fa-light fa-boxes-stacked"
        >
        <x-slot:actions>
            <x-ui.button :href="route('admin-marketplace.index')" variant="outline" wire:navigate>{{ __('Back to marketplace') }}</x-ui.button>

            <form method="POST" action="{{ route('admin-marketplace.rescan') }}">
                @csrf
                <x-ui.button type="submit" variant="outline">{{ __('Rescan') }}</x-ui.button>
            </form>

            <x-ui.modal
                width="lg"
                :title="__('Install package')"
                :description="__('Install with a purchase code or upload a ZIP package manually.')"
                :initially-open="$openInstallModal"
            >
                <x-slot:trigger>
                    <x-ui.button type="button">{{ __('Install package') }}</x-ui.button>
                </x-slot:trigger>

                <div x-data="{ method: '{{ $installMethod }}', submitting: false }">
                    <form id="marketplace-install-form" method="POST" action="{{ route('admin-marketplace.install') }}" enctype="multipart/form-data" class="space-y-5" x-on:submit="submitting = true">
                        @csrf
                        <input type="hidden" name="install_method" x-model="method">

                        <div class="grid grid-cols-2 gap-3 rounded-2xl border p-2" style="border-color: var(--theme-border-color); background: rgba(15,23,42,0.02);">
                            <button
                                type="button"
                                x-on:click="method = 'purchase'"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :style="method === 'purchase'
                                    ? 'background: var(--theme-accent); color: white;'
                                    : 'background: transparent; color: var(--theme-header-text-color);'"
                            >
                                {{ __('Purchase code') }}
                            </button>
                            <button
                                type="button"
                                x-on:click="method = 'zip'"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :style="method === 'zip'
                                    ? 'background: var(--theme-accent); color: white;'
                                    : 'background: transparent; color: var(--theme-header-text-color);'"
                            >
                                {{ __('ZIP file') }}
                            </button>
                        </div>

                        <div x-show="method === 'purchase'" x-cloak class="space-y-3">
                            <x-ui.input
                                id="purchase_code"
                                name="purchase_code"
                                :label="__('Purchase code')"
                                :placeholder="__('Enter your Envato purchase code')"
                                :value="old('purchase_code')"
                                :error="$errors->first('purchase_code')"
                            />
                            <div class="rounded-2xl border px-4 py-3 text-sm" style="border-color: var(--theme-border-color); background: rgba(59,130,246,0.04); color: var(--theme-muted-text-color);">
                                {{ __('Use this when you want to verify a purchase code and install the package directly from the marketplace.') }}
                            </div>
                        </div>

                        <div x-show="method === 'zip'" x-cloak class="space-y-3">
                            <div class="space-y-2">
                                <label for="module_zip" class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Module ZIP') }}</label>
                                <input
                                    id="module_zip"
                                    name="module_zip"
                                    type="file"
                                    accept=".zip"
                                    class="block w-full rounded-2xl border px-4 py-3 text-sm"
                                    style="border-color: var(--theme-border-color); background: var(--theme-card-background); color: var(--theme-header-text-color);"
                                >
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('The ZIP must extract into modules/YourModuleName and include module.json at the root of that folder.') }}</p>
                                @error('module_zip')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--theme-border-color);">
                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                            <x-ui.button type="submit" x-bind:disabled="submitting">
                                <span x-show="!submitting">{{ __('Install package') }}</span>
                                <span x-show="submitting" x-cloak>{{ __('Installing...') }}</span>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.modal>
        </x-slot:actions>
    </x-ui.page-hero>

    <x-ui.card>
        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]">
            <label class="space-y-2">
                <span class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Search installed packages') }}</span>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="{{ __('Package name, module, version...') }}"
                    class="block w-full rounded-2xl border px-4 py-3 text-sm"
                    style="border-color: var(--theme-border-color); background: var(--theme-card-background); color: var(--theme-header-text-color);"
                >
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Status') }}</span>
                <select
                    wire:model.live="statusFilter"
                    class="block w-full rounded-2xl border px-4 py-3 text-sm"
                    style="border-color: var(--theme-border-color); background: var(--theme-card-background); color: var(--theme-header-text-color);"
                >
                    <option value="all">{{ __('All') }}</option>
                    <option value="1">{{ __('Active') }}</option>
                    <option value="0">{{ __('Inactive') }}</option>
                </select>
            </label>

            <div class="flex flex-wrap items-end gap-3">
                <x-ui.button type="button" variant="outline" wire:click="resetPackageFilters">{{ __('Clear') }}</x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <x-ui.datatable-shell :title="__('Installed packages')" :info="__('Operational package manager for modules actually present in this app.')">
        <x-ui.table class="rounded-none border-0 shadow-none">
            <x-ui.table-head>
                <x-ui.table-cell head>{{ __('Package') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Source') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Version') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Providers') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Actions') }}</x-ui.table-cell>
            </x-ui.table-head>
            <x-ui.table-body>
                @forelse ($packages as $package)
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            <div class="space-y-1">
                                <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $package->title }}</p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">
                                    <span class="font-medium">{{ __('Module') }}:</span>
                                    <span class="font-mono">{{ $package->module_name }}</span>
                                </p>
                                @if ($package->description)
                                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($package->description, 120) }}</p>
                                @endif
                                @if ($package->source_type === 'purchase')
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 pt-1 text-xs" style="color: var(--theme-muted-text-color);">
                                        @if ($package->product_id)
                                            <span><strong>{{ __('Product ID') }}:</strong> {{ $package->product_id }}</span>
                                        @endif
                                        @if ($package->purchase_code)
                                            <span><strong>{{ __('License') }}:</strong> {{ \Illuminate\Support\Str::mask($package->purchase_code, '*', 8, max(strlen($package->purchase_code) - 12, 0)) }}</span>
                                        @endif
                                        @if ($package->licensed_domain)
                                            <span><strong>{{ __('Domain') }}:</strong> {{ $package->licensed_domain }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <x-ui.badge :variant="$package->source_type === 'purchase' ? 'success' : ($package->source_type === 'zip' ? 'info' : 'neutral')">
                                {{
                                    $package->source_type === 'purchase'
                                        ? __('Purchase code')
                                        : ($package->source_type === 'zip' ? __('ZIP install') : __('Local module'))
                                }}
                            </x-ui.badge>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <span class="text-sm" style="color: var(--theme-header-text-color);">{{ $package->version ?: __('Unknown') }}</span>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="space-y-1">
                                @forelse ((array) $package->providers as $provider)
                                    <p class="text-xs font-mono break-all" style="color: var(--theme-muted-text-color);">{{ $provider }}</p>
                                @empty
                                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No providers') }}</p>
                                @endforelse
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <x-ui.badge :variant="$package->is_active ? 'success' : 'neutral'">
                                {{ $package->is_active ? __('Active') : __('Inactive') }}
                            </x-ui.badge>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($package->source_type === 'purchase')
                                    @if ($package->has_update ?? false)
                                        <form method="POST" action="{{ route('admin-marketplace.update', $package->id_secure) }}">
                                            @csrf
                                            <x-ui.button type="submit" variant="warning" size="sm">
                                                {{ __('Upgrade version') }}
                                            </x-ui.button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center rounded-full border px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(16,185,129,0.28); color: rgb(6, 95, 70); background: rgba(16,185,129,0.08);">
                                            {{ __('Installed') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @empty
                    <x-ui.table-row>
                        <x-ui.table-cell colspan="6" class="py-10">
                            <x-ui.empty icon="fa-light fa-box-open" :title="__('No installed marketplace packages found.')" :description="__('Upload the first module ZIP or rescan after you copy a package into the modules directory.')" />
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @endforelse
            </x-ui.table-body>
        </x-ui.table>
    </x-ui.datatable-shell>
</div>
