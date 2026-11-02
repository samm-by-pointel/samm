{{-- Symbole Sàmm en SVG en ligne : il prend currentColor (charte §7, §12.1). --}}
@props(['size' => 32])
<svg {{ $attributes }} viewBox="0 0 48 48" width="{{ $size }}" height="{{ $size }}" fill="none" aria-hidden="true" focusable="false">
    <path d="M8 29 A16 16 0 0 1 40 29" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
    <rect x="15" y="21" width="18" height="13" rx="6.5" fill="currentColor"/>
    <path d="M18.5 32 L16 39.5 L25 33.5 Z" fill="currentColor"/>
</svg>
