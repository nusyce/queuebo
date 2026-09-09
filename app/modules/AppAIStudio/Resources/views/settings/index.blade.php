@component(theme_view('layouts.app', 'app'), ['title' => __('AI Settings')])
    @php
        $resolveLanguageLabel = function ($code) {
            $code = (string) $code;

            return collect(world_languages())->firstWhere('code', $code)['name'] ?? strtoupper($code);
        };
    @endphp
    <div class="space-y-6">
        

        <section class="overflow-hidden rounded-[2rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.6); background:
            radial-gradient(circle at top left, rgba(var(--theme-accent-rgb), 0.16), transparent 30%),
            radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.1), transparent 22%),
            linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.985), rgba(var(--theme-surface-soft-rgb,248,250,252),0.92));
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.06);
        ">
            <div class="grid gap-6 px-6 py-6 sm:px-8 xl:grid-cols-[minmax(0,1fr)_18rem] xl:items-end">
                <div class="flex items-start gap-4">
                    <span class="inline-flex h-16 w-16 flex-none items-center justify-center rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.45); background:
                        linear-gradient(145deg, rgba(var(--theme-accent-rgb), 0.18), rgba(var(--theme-accent-rgb), 0.06));
                        color: var(--theme-accent);
                    ">
                        <i class="fa-light fa-sparkles text-xl"></i>
                    </span>

                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em]" style="color: var(--theme-muted-text-color);">{{ __('AI Studio') }}</p>
                        <h1 class="mt-2 text-[2.1rem] font-semibold tracking-[-0.06em] leading-tight" style="color: var(--theme-header-text-color);">{{ __('AI Settings') }}</h1>
                        <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Set module defaults for captions, rewrite, planning, image, video, and review. Workspace rules can also steer the final prompt behavior for everyone on the team.') }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.2rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Scope') }}</p>
                        <p class="mt-2 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('User + workspace defaults') }}</p>
                    </div>
                    <div class="rounded-[1.2rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Priority') }}</p>
                        <p class="mt-2 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('User settings override workspace defaults') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <form method="POST" action="{{ route('portal.ai-studio.settings.user') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <x-theme.section-card
                    :title="__('My AI defaults')"
                    :description="__('These defaults prefill AI Studio modules only for your own account.')"
                    body-class="space-y-6 p-6"
                >
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Default language') }}</p>
                            <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $resolveLanguageLabel($userSettings['default_language'] ?? $defaults['default_language']) }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Primary language for caption, repurpose, planner, and review.') }}</p>
                        </div>

                        <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Default tone') }}</p>
                            <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ collect($toneOptions)->firstWhere('value', $userSettings['default_tone'] ?? $defaults['default_tone'])['label'] ?? ucfirst((string) ($userSettings['default_tone'] ?? $defaults['default_tone'])) }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Baseline voice when a module does not override the writing style.') }}</p>
                        </div>

                        <div class="rounded-[1.1rem] border px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Caption defaults') }}</p>
                            <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ count($userSettings['default_caption_platforms'] ?? []) ?: 0 }} {{ __('selected') }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Channel defaults used by Caption Generator and Repurpose.') }}</p>
                        </div>
                    </div>

                    <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[0.72rem] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('General configuration') }}</p>
                                <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Set the default language, tone of voice, planning horizon, and media defaults used across your AI Studio tools.') }}</p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <x-ai.language-field name="default_language" :value="$userSettings['default_language']" />
                            <x-ai.tone-field name="default_tone" :value="$userSettings['default_tone'] ?? $defaults['default_tone']" :options="$toneOptions" />
                            <x-ai.language-field name="review_language" :label="__('Review Language')" :value="$userSettings['review_language']" />
                            <x-ai.planner-days-field :value="$userSettings['planner_days']" />
                        </div>
                    </div>

                    <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                        <div>
                            <p class="text-[0.72rem] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Media defaults') }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Keep image and video generation aligned with your most common setup.') }}</p>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <x-ai.image-style-field :value="$userSettings['image_style'] ?? $defaults['image_style']" :options="$imageStyleOptions" />
                            <x-ai.image-ratio-field :value="$userSettings['image_ratio'] ?? $defaults['image_ratio']" :options="$imageRatioOptions" />
                            <x-ai.video-format-field :value="$userSettings['video_format'] ?? $defaults['video_format']" :options="$videoFormatOptions" />
                            <x-ai.video-duration-field :value="$userSettings['video_duration'] ?? $defaults['video_duration']" :options="$videoDurationOptions" />
                        </div>
                    </div>

                    <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                        <x-ai.caption-platforms-field :options="$platformOptions" :selected="$userSettings['default_caption_platforms'] ?? []" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-ui.button type="submit">{{ __('Save my defaults') }}</x-ui.button>
                        <x-ui.button :href="route('portal.ai-studio.prompt-history')" variant="outline" wire:navigate>{{ __('Prompt History') }}</x-ui.button>
                    </div>
                </x-theme.section-card>
            </form>

            <div class="space-y-6">
                <x-theme.section-card
                    :title="__('Workspace prompt rules')"
                    :description="__('These shared rules shape how AI writes for the active workspace. They are applied to caption, repurpose, planner, and review prompts.')"
                    body-class="space-y-6 p-6"
                >
                    @if ($canManageWorkspace)
                        <form method="POST" action="{{ route('portal.ai-studio.settings.workspace') }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                <div>
                                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Prompt rules') }}</p>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Shape the voice, CTA style, and disallowed wording used across shared AI outputs.') }}</p>
                                </div>

                                <div class="mt-5 space-y-4">
                                    <x-ai.brand-voice-field :value="$workspaceSettings['brand_voice'] ?? ''" />
                                    <x-ai.cta-style-field :value="$workspaceSettings['preferred_cta_style'] ?? ''" />
                                    <x-ai.banned-words-field :value="$workspaceSettings['banned_words'] ?? ''" />
                                </div>
                            </div>

                            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                <div>
                                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.24em]" style="color: var(--theme-muted-text-color);">{{ __('Workspace defaults') }}</p>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('These become the baseline when a team member has not set their own AI Studio defaults.') }}</p>
                                </div>

                                <div class="mt-5 grid gap-4 md:grid-cols-2">
                                    <x-ai.language-field name="default_language" :label="__('Workspace Default Language')" :value="$workspaceSettings['default_language']" />
                                    <x-ai.tone-field name="default_tone" :label="__('Workspace Default Tone Of Voice')" :value="$workspaceSettings['default_tone'] ?? $defaults['default_tone']" :options="$toneOptions" />
                                </div>
                            </div>

                            <div class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                                <x-ai.caption-platforms-field
                                    :label="__('Workspace Caption Defaults')"
                                    :description="__('These become the team baseline when a user has not set personal platform defaults.')"
                                    :options="$platformOptions"
                                    :selected="$workspaceSettings['default_caption_platforms'] ?? []"
                                />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-ui.button type="submit">{{ __('Save workspace defaults') }}</x-ui.button>
                                @if ($team)
                                    <x-ui.badge variant="neutral">{{ __('Team: :name', ['name' => $team->name]) }}</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">{{ __('Personal workspace') }}</x-ui.badge>
                                @endif
                            </div>
                        </form>
                    @else
                        <x-ui.empty
                            icon="fa-light fa-lock-keyhole"
                            :title="__('Workspace defaults are restricted')"
                            :description="__('Only the workspace owner can edit shared AI rules for this team.')"
                        />
                    @endif
                </x-theme.section-card>

                <x-theme.section-card
                    :title="__('How these defaults apply')"
                    :description="__('A quick map of which modules read which AI Studio settings.')"
                    body-class="space-y-4 p-6"
                >
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ui.card class="space-y-2">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Caption Generator + Repurpose') }}</p>
                            <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Read default language, tone, default platforms, and workspace prompt rules.') }}</p>
                        </x-ui.card>
                        <x-ui.card class="space-y-2">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Content Planner') }}</p>
                            <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Reads planner days, default language, and workspace prompt rules.') }}</p>
                        </x-ui.card>
                        <x-ui.card class="space-y-2">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI Review') }}</p>
                            <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Reads review language and shared prompt rules for stronger output consistency.') }}</p>
                        </x-ui.card>
                        <x-ui.card class="space-y-2">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI Image + AI Video') }}</p>
                            <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Read default style, ratio, format, and duration so new jobs start from your preferred setup.') }}</p>
                        </x-ui.card>
                    </div>
                </x-theme.section-card>
            </div>
        </div>
    </div>
@endcomponent
