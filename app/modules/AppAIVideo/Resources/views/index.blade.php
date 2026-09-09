<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="relative overflow-hidden rounded-[1.7rem] border px-6 py-6 sm:px-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
        radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.15), transparent 32%),
        linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-overlay) 94%, transparent), color-mix(in srgb, var(--theme-surface-base) 96%, transparent));">
        <div class="absolute inset-y-0 right-0 hidden w-[24rem] opacity-70 xl:block" style="background:
            radial-gradient(circle at center, rgba(var(--theme-accent-rgb), 0.14), transparent 60%);"></div>

        <div class="relative flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em]" style="border-color: rgba(var(--theme-accent-rgb), 0.22); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                    <span class="inline-flex h-2 w-2 rounded-full" style="background-color: var(--theme-accent);"></span>
                    {{ __('AI Studio') }}
                </div>
                <h1 class="mt-4 text-[2rem] font-semibold tracking-[-0.06em] sm:text-[2.4rem]" style="color: var(--theme-header-text-color);">{{ __('AI Video') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                    {{ __('Launch a video render job, track it manually, and save the final MP4 into Files when the provider finishes rendering.') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[27rem] xl:max-w-[30rem]">
                <div class="rounded-[1.05rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Duration') }}</p>
                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $duration }}s</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Max :seconds sec on current plan', ['seconds' => $maxVideoSeconds]) }}</p>
                </div>
                <div class="rounded-[1.05rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Format') }}</p>
                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ str($format)->replace('-', ' ')->title() }}</p>
                </div>
                <div class="rounded-[1.05rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 92%, transparent);">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Delivery') }}</p>
                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Files library') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __(':credits credits for current selection', ['credits' => $selectedDurationCredits]) }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-[21rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-film text-[1rem] leading-none"></i>
                    </span>
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Output setup') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose format and target duration before starting the render job.') }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    <x-ui.select wire:model.live="duration" :label="__('Duration')">
                        @foreach ($durationOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }} · {{ $option['credits'] }} {{ __('credits') }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select wire:model.live="format" :label="__('Format')">
                        @foreach ($formatOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <p class="mt-4 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Only durations allowed by the current plan are shown here. Video credits scale by duration: 4 seconds uses the base cost, 8 seconds uses 2x, and 12 seconds uses 3x.') }}</p>
            </div>

            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Render flow') }}</p>
                <div class="mt-4 space-y-3">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('1. Prompt') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Describe the scene, motion, pacing, and CTA beat you want in the clip.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('2. Render') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('The provider creates an async job and returns a job id immediately.') }}</p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('3. Save') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('When completed, the MP4 is stored in Files and can be reused later.') }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="space-y-5">
            <div class="rounded-[1.25rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Video prompt') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Write a scene brief strong enough for motion generation: subject, camera direction, lighting, transitions, speed, and what the ending frame should communicate.') }}</p>
                    </div>
                    <div class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        {{ __('Async render job') }}
                    </div>
                </div>

                <div class="mt-5">
                    <x-ui.textarea wire:model.defer="prompt" :label="__('Motion brief')" rows="8" placeholder="{{ __('Example: A premium SaaS dashboard animates onto a phone screen, smooth camera push-in, clean product UI, soft studio light, modern motion graphics, and a final CTA frame about scheduling content faster.') }}">{{ $prompt }}</x-ui.textarea>
                    @error('prompt')
                        <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                    @enderror
                    @error('duration')
                        <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" :disabled="!($creditPreview['enough'] ?? true)">
                        <i class="fa-light fa-video"></i>
                        <span wire:loading.remove wire:target="generate">{{ __('Generate video') }}</span>
                        <span wire:loading wire:target="generate">{{ __('Starting...') }}</span>
                    </x-ui.button>

                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-timer text-xs" style="color: var(--theme-accent);"></i>
                        <span>{{ __('Duration :duration', ['duration' => $duration.'s']) }}</span>
                        <span>&bull;</span>
                        <span>{{ str($format)->replace('-', ' ')->title() }}</span>
                        <span>&bull;</span>
                        <span>{{ __(':credits credits', ['credits' => $selectedDurationCredits]) }}</span>
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
                <div class="grid gap-5 2xl:grid-cols-[minmax(0,1.15fr)_24rem]">
                    <div class="rounded-[1.3rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Video job') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Track the current render job and pull the latest state manually.') }}</p>
                            </div>
                            <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                                {{ strtoupper((string) ($result['status'] ?? 'queued')) }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,22rem)_minmax(0,1fr)]">
                            <div class="space-y-3">
                                @if (($result['status'] ?? '') === 'completed' && !empty($result['preview_url']))
                                    <div class="overflow-hidden rounded-[1.2rem] border shadow-[0_30px_80px_-50px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background:
                                        linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.05), transparent 18%),
                                        color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                                        <video controls preload="metadata" class="block aspect-[9/16] w-full object-cover" src="{{ $result['preview_url'] }}"></video>
                                    </div>
                                @else
                                    <div class="rounded-[1.2rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background:
                                        radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 28%),
                                        color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                                        <div class="flex items-start gap-3">
                                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                                <i class="fa-light fa-film text-lg"></i>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Render in progress') }}</p>
                                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('The provider is still preparing the video. Refresh when you want to check if the MP4 is ready.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Prompt length') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ mb_strlen((string) ($result['prompt'] ?? '')) }} {{ __('characters') }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Provider: :provider', ['provider' => strtoupper((string) ($result['provider'] ?? '')) ?: 'AI']) }}</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div class="rounded-[1rem] border px-4 py-3 md:col-span-2" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Job ID') }}</p>
                                                <p class="mt-2 break-all text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $result['id'] ?? '' }}</p>
                                            </div>
                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-accent);">
                                                {{ __('Tracked') }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Progress') }}</p>
                                        <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ (int) ($result['progress'] ?? 0) }}%</p>
                                        <div class="mt-3 h-2.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.18);">
                                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ max(6, min(100, (int) ($result['progress'] ?? 0))) }}%; background: linear-gradient(90deg, rgba(var(--theme-accent-rgb), 0.55), var(--theme-accent));"></div>
                                        </div>
                                    </div>

                                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Output') }}</p>
                                        <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $result['size'] ?? __('Pending') }}</p>
                                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Seconds: :seconds', ['seconds' => $result['seconds'] ?? $duration]) }}</p>
                                    </div>
                                </div>

                                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background:
                                    linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.04), transparent 28%),
                                    color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Actions') }}</p>
                                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Download, open the asset in Files, or clear this finished job from the tracker.') }}</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @if (($result['status'] ?? '') !== 'completed')
                                                <x-ui.button type="button" variant="outline" wire:click="refreshResult" wire:loading.attr="disabled" wire:target="refreshResult">
                                                    <i class="fa-light fa-rotate"></i>
                                                    <span wire:loading.remove wire:target="refreshResult">{{ __('Refresh status') }}</span>
                                                    <span wire:loading wire:target="refreshResult">{{ __('Refreshing...') }}</span>
                                                </x-ui.button>
                                            @endif

                                            @if (($result['status'] ?? '') === 'completed' && !empty($result['download_url']))
                                                <x-ui.button :href="$result['download_url'] ?? null" target="_blank" rel="noopener">
                                                    <i class="fa-light fa-download"></i>
                                                    {{ __('Download MP4') }}
                                                </x-ui.button>
                                            @endif

                                            @if (($result['status'] ?? '') === 'completed' && !empty($result['files_url']))
                                                <x-ui.button :href="$result['files_url'] ?? null" variant="outline" wire:navigate>
                                                    <i class="fa-light fa-folder-open"></i>
                                                    {{ __('Open Files') }}
                                                </x-ui.button>
                                            @endif

                                            @if (($result['status'] ?? '') === 'completed')
                                                <x-ui.button
                                                    type="button"
                                                    variant="danger"
                                                    wire:click="deleteCompletedJob"
                                                    wire:loading.attr="disabled"
                                                    wire:target="deleteCompletedJob"
                                                >
                                                    <i class="fa-light fa-trash"></i>
                                                    <span wire:loading.remove wire:target="deleteCompletedJob">{{ __('Delete job') }}</span>
                                                    <span wire:loading wire:target="deleteCompletedJob">{{ __('Deleting...') }}</span>
                                                </x-ui.button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Job details') }}</p>
                            <div class="mt-4 space-y-3">
                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Provider') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ strtoupper((string) ($result['provider'] ?? '')) }}</p>
                                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $result['model'] ?? '' }}</p>
                                </div>
                                <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Format') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ str((string) ($result['format'] ?? $format))->replace('-', ' ')->title() }}</p>
                                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Duration: :duration', ['duration' => ($result['duration'] ?? $duration).'s']) }}</p>
                                </div>
                                @if (!empty($result['file_name']) || !empty($result['file_size']))
                                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Saved asset') }}</p>
                                        <p class="mt-2 break-all text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $result['file_name'] ?? '' }}</p>
                                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $result['file_size'] ?? '' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt snapshot') }}</p>
                            <p class="mt-3 text-sm leading-7 whitespace-pre-line" style="color: var(--theme-muted-text-color);">{{ $result['prompt'] ?? '' }}</p>
                        </div>

                        <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent video jobs') }}</p>
                            @if ($recentJobs->isNotEmpty())
                                <div class="mt-4 space-y-2">
                                    @foreach ($recentJobs as $job)
                                        <button type="button" wire:click="loadJob({{ $job->id }})" class="w-full rounded-[1rem] border px-4 py-3 text-left transition" style="border-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.34)' : 'rgba(var(--theme-border-color-rgb), 0.42)' }}; background-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.07)' : 'color-mix(in srgb, var(--theme-surface-base) 94%, transparent)' }};">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ str($job->format)->replace('-', ' ')->title() }} · {{ $job->duration }}s</p>
                                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($job->prompt, 72) }}</p>
                                                </div>
                                                <span class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ strtoupper((string) $job->status) }}</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4">
                                    <x-ui.empty
                                        icon="fa-light fa-film"
                                        :title="__('No video jobs yet')"
                                        :description="__('Started video render jobs will appear here for quick reopen.')"
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
                                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Video prompt') }}</p>
                                            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 78) }}</p>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4">
                                    <x-ui.empty
                                        icon="fa-light fa-book-open"
                                        :title="__('No prompt history yet')"
                                        :description="__('Video prompts you run here will be saved for quick reuse.')"
                                    />
                                </div>
                            @endif
                        </div>

                        @if (($result['status'] ?? '') === 'failed' && !empty($result['error_message']))
                            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-danger-rgb, 239, 68, 68), 0.25); background-color: rgba(var(--theme-danger-rgb, 239, 68, 68), 0.06);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Render error') }}</p>
                                <p class="mt-3 text-sm leading-7" style="color: var(--theme-danger-color);">{{ $result['error_message'] }}</p>
                            </div>
                        @elseif (($result['status'] ?? '') !== 'completed')
                            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Manual tracking') }}</p>
                                <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('This page keeps the latest job in the database, so you can reload later and continue checking from the same render state.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-[1.3rem] border p-6 sm:p-7" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background:
                    radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.08), transparent 30%),
                    color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No render job yet') }}</p>
                            <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Once you start a video render, the current job state will appear here with progress, provider metadata, and download actions when the MP4 is ready.') }}</p>
                        </div>
                        <div class="inline-flex items-center gap-3 rounded-[1.1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-film text-base"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to render') }}</p>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Prompt, render, refresh, save.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
