<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="relative overflow-hidden rounded-[1.7rem] border px-6 py-6 sm:px-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.16), transparent 32%),
        linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent), color-mix(in srgb, var(--theme-surface-base) 96%, transparent));">
        <div class="absolute inset-y-0 right-0 hidden w-[24rem] opacity-70 xl:block" style="background:
            radial-gradient(circle at center, rgba(var(--theme-accent-rgb), 0.16), transparent 58%);"></div>

        <div class="relative flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em]" style="border-color: rgba(var(--theme-accent-rgb), 0.22); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                    <span class="inline-flex h-2 w-2 rounded-full" style="background-color: var(--theme-accent);"></span>
                    {{ __('AI Studio') }}
                </div>
                <h1 class="mt-4 text-[2rem] font-semibold tracking-[-0.06em] sm:text-[2.4rem]" style="color: var(--theme-header-text-color);">{{ __('AI Image') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                    {{ __('Turn a visual brief into a reusable asset, save it to Files, and keep it ready for publishing workflows.') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[27rem] xl:max-w-[30rem]">
                <div class="rounded-[1.05rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Style') }}</p>
                    <p class="mt-2 text-sm font-semibold capitalize" style="color: var(--theme-header-text-color);">{{ $style }}</p>
                </div>
                <div class="rounded-[1.05rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Ratio') }}</p>
                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $ratio }}</p>
                </div>
                <div class="rounded-[1.05rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Library') }}</p>
                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Auto save') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __(':credits credits per image', ['credits' => $creditPreview['amount'] ?? 0]) }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-[21rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-sliders text-[1rem] leading-none"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Direction') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Set the image character before generating.') }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    <x-ui.select wire:model.live="style" :label="__('Style')">
                        @foreach ($styleOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select wire:model.live="ratio" :label="__('Aspect ratio')">
                        @foreach ($ratioOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>

            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Output flow') }}</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('1. Brief') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Describe subject, composition, lighting, and any brand constraints.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('2. Generate') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create one real image with the provider currently configured in Admin AI.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('3. Reuse') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Open Files, download it, or use it later in post creation.') }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="space-y-5">
            <div class="rounded-[1.25rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Image prompt') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Write a production-ready brief. Strong prompts usually mention subject, scene, framing, mood, and what to avoid.') }}</p>
                    </div>
                    <div class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        {{ __('Saved to Files after generate') }}
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.textarea wire:model.defer="prompt" :label="__('Visual brief')" rows="8" placeholder="{{ __('Example: A premium laptop on a clean desk with soft morning light, editorial framing, subtle brand colors, and no watermark or messy background.') }}">{{ $prompt }}</x-ui.textarea>
                    @error('prompt')
                        <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" :disabled="!($creditPreview['enough'] ?? true)">
                        <i class="fa-light fa-image"></i>
                        <span wire:loading.remove wire:target="generate">{{ __('Generate image') }}</span>
                        <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
                    </x-ui.button>

                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-sparkles text-xs" style="color: var(--theme-accent);"></i>
                        <span>{{ __('Style :style', ['style' => ucfirst($style)]) }}</span>
                        <span>&bull;</span>
                        <span>{{ __('Ratio :ratio', ['ratio' => $ratio]) }}</span>
                        <span>&bull;</span>
                        <span>{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</span>
                    </div>
                </div>
                @if (!($creditPreview['enough'] ?? true))
                    <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ __('Not enough credits remaining for this action.') }}</p>
                    @include(theme_view('partials.credit-topup-cta', 'app'))
                @endif
            </div>

            @if ($result)
                <div class="grid gap-5 2xl:grid-cols-[minmax(0,1.2fr)_24rem]">
                    <div class="rounded-[1.3rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Generated image') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Latest generated asset preview.') }}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                                {{ strtoupper((string) ($result['status'] ?? 'generated')) }}
                            </span>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-[1.15rem] border shadow-[0_30px_80px_-50px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background:
                            linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.05), transparent 18%),
                            color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                            <img src="{{ $result['preview_url'] ?? '' }}" alt="{{ $result['file_name'] ?? 'AI image' }}" class="block h-auto w-full object-cover">
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Asset details') }}</p>
                            <div class="mt-4 space-y-3">
                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('File') }}</p>
                                    <p class="mt-2 text-sm font-semibold break-all" style="color: var(--theme-header-text-color);">{{ $result['file_name'] ?? '' }}</p>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-1">
                                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Size') }}</p>
                                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $result['file_size'] ?? '' }}</p>
                                    </div>
                                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Dimensions') }}</p>
                                        <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $result['dimensions'] ?: __('Detected after save') }}</p>
                                    </div>
                                </div>
                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Provider') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ strtoupper((string) ($result['provider'] ?? '')) }}</p>
                                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $result['model'] ?? '' }}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <x-ui.button :href="$result['download_url'] ?? null" target="_blank" rel="noopener">
                                    <i class="fa-light fa-download"></i>
                                    {{ __('Download') }}
                                </x-ui.button>
                                <x-ui.button :href="$result['files_url'] ?? null" variant="outline" wire:navigate>
                                    <i class="fa-light fa-folder-open"></i>
                                    {{ __('Open Files') }}
                                </x-ui.button>
                            </div>
                        </div>

                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Generation brief') }}</p>
                            <p class="mt-3 text-sm leading-7 whitespace-pre-line" style="color: var(--theme-muted-text-color);">{{ $result['prompt'] ?? '' }}</p>
                        </div>

                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent image jobs') }}</p>
                            @if ($recentJobs->isNotEmpty())
                                <div class="mt-4 space-y-2">
                                    @foreach ($recentJobs as $job)
                                        <button type="button" wire:click="loadJob({{ $job->id }})" class="w-full rounded-[1rem] border px-4 py-3 text-left transition" style="border-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.34)' : 'rgba(var(--theme-border-color-rgb), 0.42)' }}; background-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.07)' : 'color-mix(in srgb, var(--theme-surface-base) 94%, transparent)' }};">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold capitalize" style="color: var(--theme-header-text-color);">{{ $job->style }} · {{ $job->ratio }}</p>
                                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($job->prompt, 72) }}</p>
                                                </div>
                                                <span class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ optional($job->created_at)->diffForHumans() }}</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4">
                                    <x-ui.empty
                                        icon="fa-light fa-image"
                                        :title="__('No image jobs yet')"
                                        :description="__('Generated image jobs will appear here for quick reopen.')"
                                    />
                                </div>
                            @endif
                        </div>

                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt history') }}</p>
                            @if ($promptHistory->isNotEmpty())
                                <div class="mt-4 max-h-[22rem] space-y-2 overflow-y-auto pr-1">
                                    @foreach ($promptHistory as $history)
                                        <button type="button" wire:click="loadPromptHistory({{ $history->id }})" class="w-full rounded-[1rem] border px-4 py-3 text-left transition" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Image prompt') }}</p>
                                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 78) }}</p>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4">
                                    <x-ui.empty
                                        icon="fa-light fa-book-open"
                                        :title="__('No prompt history yet')"
                                        :description="__('Image prompts you run here will be saved for quick reuse.')"
                                    />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-[1.3rem] border p-6 sm:p-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                    radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 30%),
                    color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No image generated yet') }}</p>
                            <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Your first generated image will appear here with preview, file metadata, and quick actions to open the media library.') }}</p>
                        </div>
                        <div class="inline-flex items-center gap-3 rounded-[1.1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-image-polaroid text-base"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready for generation') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Prompt, generate, save, reuse.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
