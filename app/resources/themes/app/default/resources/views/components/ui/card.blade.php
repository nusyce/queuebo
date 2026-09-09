@props([
    'padding' => 'md',
    'tone' => 'default',
])

@php
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-5',
        'md' => 'p-6',
        'lg' => 'p-7',
    ];

    $toneClasses = [
        'default' => 'shadow-[0_20px_52px_-40px_rgba(15,23,42,0.18)]',
        'soft' => 'shadow-[0_14px_36px_-30px_rgba(15,23,42,0.12)]',
        'contrast' => 'shadow-[0_24px_54px_-36px_rgba(15,23,42,0.5)]',
    ];

    $toneStyles = [
        'default' => 'border-color: var(--theme-border-color); background-color: var(--theme-surface-base);',
        'soft' => 'border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);',
        'contrast' => 'border-color: var(--theme-card-contrast-bg); background-color: var(--theme-card-contrast-bg); color: var(--theme-card-contrast-text);',
    ];
@endphp

<div {{ $attributes->merge(['style' => 'border-radius: var(--theme-card-radius, 1.15rem); '.($toneStyles[$tone] ?? $toneStyles['default'])])->class('min-w-0 max-w-full border backdrop-blur-[1px] '.($toneClasses[$tone] ?? $toneClasses['default']).' '.($paddingClasses[$padding] ?? 'p-6')) }}>
    {{ $slot }}
</div>
