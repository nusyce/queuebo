<div
    class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{
        init() {
            window.__appShellSuppressLoading = true;
        },
    }"
    x-on:livewire:navigating.window="window.__appShellSuppressLoading = false"
>
    <x-ui.page-hero
        :eyebrow="__('AI Studio')"
        :title="__('AI Content')"
        :description="__('Browse reusable AI templates, shape the prompt with your own context, and generate multiple save-ready content variations in one run.')"
        icon="fa-light fa-wand-magic-sparkles"
    />

    <section class="grid gap-4 lg:grid-cols-3">
        <x-ui.metric-card
            :label="__('Template Library')"
            :value="count($categoryOptions) . ' ' . __('categories')"
            :description="__('Reusable prompt categories available in your AI library')"
            icon="fa-light fa-rectangle-history-circle-plus"
            accent="primary"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Platforms')"
            :value="count($selectedPlatforms) . ' ' . __('selected')"
            :description="__('Active output destinations for this generation')"
            icon="fa-light fa-share-nodes"
            accent="success"
            class="min-h-[150px]"
        />
        <x-ui.metric-card
            :label="__('Credits')"
            :value="($creditPreview['amount'] ?? 0) . ' ' . __('per run')"
            :description="__('Estimated generation cost for the current request')"
            icon="fa-light fa-coins"
            accent="warning"
            class="min-h-[150px]"
        />
    </section>

    <section class="grid gap-5 2xl:grid-cols-[26rem_minmax(0,1fr)_20rem]">
        @include('appaicontent::partials.template-sidebar')

        <div class="space-y-5">
            @include('appaicontent::partials.builder-panel')
            @include('appaicontent::partials.results-panel')
        </div>

        @include('appaicontent::partials.support-sidebar')
    </section>
</div>
