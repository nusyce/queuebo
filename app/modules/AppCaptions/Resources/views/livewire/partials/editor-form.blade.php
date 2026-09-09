<div class="flex items-center justify-between gap-3">
    <div>
        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $editingId ? __('Edit Caption') : __('Create Caption') }}</p>
        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">
            {{ $editingId ? __('Update content, tags, or status for this caption entry.') : __('Add a reusable caption block for your publishing team and future campaigns.') }}
        </p>
    </div>
    <x-ui.badge :variant="$editingId ? 'primary' : 'success'">{{ $editingId ? __('Editing') : __('New') }}</x-ui.badge>
</div>

<form wire:submit="save" class="mt-6 space-y-4">
    <x-ui.input wire:model.defer="name" :label="__('Caption name')" :error="$errors->first('name')" :placeholder="__('Product teaser set')" />
    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.select wire:model.defer="sourceType" :label="__('Source')" :error="$errors->first('sourceType')">
            <option value="manual">{{ __('Manual') }}</option>
            <option value="ai">{{ __('AI') }}</option>
        </x-ui.select>
        <x-ui.select wire:model.defer="captionStatus" :label="__('Status')" :error="$errors->first('captionStatus')">
            <option value="active">{{ __('Active') }}</option>
            <option value="draft">{{ __('Draft') }}</option>
            <option value="archived">{{ __('Archived') }}</option>
        </x-ui.select>
    </div>
    <x-ui.textarea wire:model.defer="content" :label="__('Caption')" :error="$errors->first('content')" rows="9" :placeholder="__('Write or paste the caption body...')"></x-ui.textarea>
    <x-ui.input wire:model.defer="tags" :label="__('Tags')" :error="$errors->first('tags')" :placeholder="__('launch, promo, product')" />
    <x-ui.textarea wire:model.defer="notes" :label="__('Notes')" :error="$errors->first('notes')" rows="4" :placeholder="__('Optional internal context, usage notes, or reminders...')"></x-ui.textarea>

    <div class="flex flex-wrap items-center gap-3 pt-2">
        <x-ui.button type="submit">
            <i class="fa-light fa-floppy-disk"></i>
            {{ $editingId ? __('Update Caption') : __('Create Caption') }}
        </x-ui.button>
        <x-ui.button type="button" variant="outline" wire:click="resetEditor">{{ __('Cancel') }}</x-ui.button>
    </div>
</form>
