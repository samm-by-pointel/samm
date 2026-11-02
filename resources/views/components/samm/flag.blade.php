{{-- Drapeau en SVG en ligne, toujours accompagné d'un libellé (charte §6). Les couleurs d'un drapeau sont fixes par nature : ce sont les seules valeurs hors jetons admises dans les vues. --}}
@props(['code'])
@switch($code)
    @case('fr')
        <svg {{ $attributes->merge(['class' => 'samm-locale-flag']) }} viewBox="0 0 60 40" width="20" height="14" aria-hidden="true" focusable="false">
            <rect width="20" height="40" fill="#0055A4"/><rect x="20" width="20" height="40" fill="#FFFFFF"/><rect x="40" width="20" height="40" fill="#EF4135"/>
        </svg>
        @break
    @case('gb')
        <svg {{ $attributes->merge(['class' => 'samm-locale-flag']) }} viewBox="0 0 60 30" width="20" height="14" aria-hidden="true" focusable="false">
            <clipPath id="samm-flag-gb-{{ $code }}"><rect width="60" height="30"/></clipPath>
            <g clip-path="url(#samm-flag-gb-{{ $code }})">
                <rect width="60" height="30" fill="#012169"/>
                <path d="M0 0 L60 30 M60 0 L0 30" stroke="#FFFFFF" stroke-width="6"/>
                <path d="M0 0 L60 30 M60 0 L0 30" stroke="#C8102E" stroke-width="2"/>
                <path d="M30 0 V30 M0 15 H60" stroke="#FFFFFF" stroke-width="10"/>
                <path d="M30 0 V30 M0 15 H60" stroke="#C8102E" stroke-width="6"/>
            </g>
        </svg>
        @break
    @case('sn')
        <svg {{ $attributes->merge(['class' => 'samm-locale-flag']) }} viewBox="0 0 60 40" width="20" height="14" aria-hidden="true" focusable="false">
            <rect width="20" height="40" fill="#00853F"/><rect x="20" width="20" height="40" fill="#FDEF42"/><rect x="40" width="20" height="40" fill="#E31B23"/>
            <path d="M30 12 l2.4 7.2 7.6 0 -6.2 4.5 2.4 7.3 -6.2 -4.5 -6.2 4.5 2.4 -7.3 -6.2 -4.5 7.6 0z" fill="#00853F"/>
        </svg>
        @break
@endswitch
