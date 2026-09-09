<x-ui.surface-card
    class="space-y-7"
    x-data="{
        typingTimer: null,
        typePrompt(content) {
            const value = String(content ?? '');
            const textarea = this.$refs.promptTextarea;

            if (!textarea) {
                return;
            }

            if (this.typingTimer) {
                clearInterval(this.typingTimer);
                this.typingTimer = null;
            }

            textarea.focus();
            textarea.value = '';
            textarea.dispatchEvent(new Event('input', { bubbles: true }));

            if (value.length === 0) {
                return;
            }

            let index = 0;
            const step = value.length > 180 ? 3 : (value.length > 90 ? 2 : 1);

            this.typingTimer = setInterval(() => {
                index = Math.min(value.length, index + step);
                textarea.value = value.slice(0, index);
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                textarea.setSelectionRange(index, index);

                if (index >= value.length) {
                    clearInterval(this.typingTimer);
                    this.typingTimer = null;
                }
            }, 16);
        },
    }"
    x-on:caption-template-selected.window="typePrompt($event.detail.content)"
>
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt builder') }}</p>
            <p class="mt-1 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Start from a reusable template or write your own prompt, then generate platform-ready caption variations from that single instruction.') }}</p>
        </div>
        <div class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
            {{ __('Template-driven workflow') }}
        </div>
    </div>

    <div class="mt-4">
        <x-ui.textarea
            wire:model.defer="promptTemplate"
            :label="__('Prompt')"
            :error="$errors->first('promptTemplate')"
            rows="7"
            :placeholder="__('Example: Write an Instagram caption for a product launch targeting busy professionals. Keep it concise, confident, and CTA-driven.')"
            x-ref="promptTextarea"
            class="space-y-1.5"
        >{{ $promptTemplate }}</x-ui.textarea>
    </div>

    <div class="grid gap-x-4 gap-y-6 mt-3 md:grid-cols-2 xl:grid-cols-3">
        <x-ai.language-field wire:model.defer="language" name="language" :value="$language" :label="__('Language')" :preferred="['vi', 'en']" class="space-y-1.5" />

        <x-ai.tone-field
            wire:model.defer="tone"
            name="tone"
            :value="$tone"
            :label="__('Tone Of Voice')"
            :options="$toneOptions"
            class="space-y-1.5"
        />

        <x-ui.select wire:model.defer="creativity" :label="__('Creativity')" class="space-y-1.5">
            @foreach ($creativityOptions as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.select wire:model.defer="hashtagMode" :label="__('Add hashtags')" class="space-y-1.5">
            @foreach ($hashtagOptions as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.input
            wire:model.defer="approximateWords"
            type="number"
            min="40"
            max="320"
            step="10"
            :label="__('Approximate words')"
            class="space-y-1.5"
        />

        <x-ui.input
            wire:model.defer="totalResults"
            type="number"
            min="1"
            max="8"
            step="1"
            :label="__('Total results')"
            class="space-y-1.5"
        />
    </div>

    <div class="flex flex-wrap items-center gap-3 pt-5">
        <x-ui.button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" :disabled="!($creditPreview['enough'] ?? true)">
            <i class="fa-light fa-wand-magic-sparkles"></i>
            <span wire:loading.remove wire:target="generate">{{ __('Generate content') }}</span>
            <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
        </x-ui.button>

        <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
            <i class="fa-light fa-coins text-xs" style="color: var(--theme-accent);"></i>
            <span>{{ __(':credits credits', ['credits' => $creditPreview['amount'] ?? 0]) }}</span>
            <span>&bull;</span>
            <span>{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</span>
        </div>

        @if ($selectedTemplate)
            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                <span class="font-medium">{{ __('Template category:') }}</span>
                <span style="color: var(--theme-header-text-color);">{{ $selectedTemplate->category?->name ?: __('Template') }}</span>
            </span>
        @endif
    </div>

    @if (!($creditPreview['enough'] ?? true))
        <p class="pt-1 text-sm font-medium" style="color: var(--theme-danger-color);">{{ __('Not enough credits remaining for this action.') }}</p>
        @include(theme_view('partials.credit-topup-cta', 'app'))
    @endif

    <div wire:loading.flex wire:target="generate" class="mt-4 items-center gap-3 rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-muted-text-color);">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.12); color: var(--theme-accent);">
            <i class="fa-light fa-loader animate-spin"></i>
        </span>
        <div>
            <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Generating content') }}</p>
            <p class="mt-1">{{ __('AI is combining your prompt and settings into multiple content variations.') }}</p>
        </div>
    </div>

    @if ($tags)
        <div class="pt-2 flex flex-wrap gap-2">
            @foreach ($tags as $tag)
                <span class="rounded-full px-2.5 py-1 text-[11px] font-medium" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">{{ $tag }}</span>
            @endforeach
        </div>
    @endif
</x-ui.surface-card>
