<div class="flex items-center justify-between gap-3">
    <div>
        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit Group') : __('Create Group') }}</p>
        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">
            {{ $editingId ? __('Update channels, notes, or status for this group.') : __('Add a reusable account cluster for your publishing workspace and future campaigns.') }}
        </p>
    </div>
    <x-ui.badge :variant="$editingId ? 'primary' : 'success'">{{ $editingId ? __('Editing') : __('New') }}</x-ui.badge>
</div>

<form wire:submit="save" class="mt-6 space-y-4">
    <x-ui.input wire:model.defer="name" :label="__('Group name')" :error="$errors->first('name')" :placeholder="__('Launch accounts')" />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.color-picker wire:model.defer="color" name="color" :label="__('Accent color')" :value="$color" :error="$errors->first('color')" />
        <x-ui.select wire:model.defer="groupStatus" :label="__('Status')" :error="$errors->first('groupStatus')">
            <option value="active">{{ __('Active') }}</option>
            <option value="inactive">{{ __('Inactive') }}</option>
        </x-ui.select>
    </div>

    <x-ui.channel-selector
        name="account_ids"
        wire-model="accountIds"
        :options="$channelOptions"
        :network-options="$channelNetworks"
        :selected="collect($accountIds)->map(fn ($id) => (string) $id)->all()"
        :label="__('Accounts')"
        :error="$errors->first('accountIds') ?: $errors->first('accountIds.*')"
        :placeholder="__('Choose one or more accounts')"
        :empty-label="__('No matching channels found.')"
        :multiple="true"
        :show-network-filters="true"
        :connect-href="route('portal.channels')"
        :connect-label="__('Connect a channel')"
        :inline-panel="true"
    />

    <x-ui.textarea wire:model.defer="description" :label="__('Description')" :error="$errors->first('description')" rows="6" :placeholder="__('Optional internal note about campaign purpose, region, or audience.')"></x-ui.textarea>

    <div class="flex flex-wrap items-center gap-3 pt-2">
        <x-ui.button type="submit">
            {{ $editingId ? __('Update Group') : __('Create Group') }}
        </x-ui.button>
        <x-ui.button type="button" variant="outline" wire:click="resetEditor">{{ __('Cancel') }}</x-ui.button>
    </div>
</form>
