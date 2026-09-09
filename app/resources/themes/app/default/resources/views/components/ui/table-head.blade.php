<thead style="background-color: var(--theme-table-head-bg);">
    <tr {{ $attributes->merge(['style' => 'color: var(--theme-muted-text-color);'])->class('text-left text-[11px] font-semibold tracking-[0.14em] uppercase') }}>
        {{ $slot }}
    </tr>
</thead>
