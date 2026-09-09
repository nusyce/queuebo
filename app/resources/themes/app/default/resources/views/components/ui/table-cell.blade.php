@props([
    'head' => false,
])

@if ($head)
    <th {{ $attributes->merge(['style' => 'border-color: var(--theme-border-color); color: var(--theme-muted-text-color);'])->class('border-b px-5 py-4 align-middle text-[11px] font-semibold uppercase tracking-[0.18em] first:pl-6 last:pr-6') }}>
        {{ $slot }}
    </th>
@else
    <td {{ $attributes->merge(['style' => 'border-color: var(--theme-border-color);'])->class('border-b px-5 py-4 align-middle first:pl-6 last:pr-6') }}>
        {{ $slot }}
    </td>
@endif
