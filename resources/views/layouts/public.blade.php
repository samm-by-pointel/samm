@php
    /** @var \App\Modules\Organizations\Models\Organization|null $organization */
    $brandName = $organization?->serviceName() ?: config('app.name');
    $neutralTitle = $organization?->effectiveNeutralTitle() ?? config('samm.neutral_title');
    $siteTitle = $neutralTitle ?: $brandName;
    $pageTitle = isset($title) && ! $neutralTitle ? $title.' · '.$siteTitle : $siteTitle;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('images/samm-logo/png/favicon-32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/samm-logo/png/samm-icone-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (! empty($brandingCss))
        {{-- Couleurs de l'organisation : valeurs hexadécimales validées par BrandingCss (charte §9) --}}
        <style>{!! $brandingCss !!}</style>
    @endif
</head>
<body>
    <a class="visually-hidden-focusable" href="#contenu">{{ __('public.skip_to_content') }}</a>

    <header class="samm-topbar">
        <a class="samm-brand" href="{{ route('public.home') }}">
            @if ($organization?->hasLogo())
                <img src="{{ $organization->logoUrl() }}" alt="" class="samm-org-logo">
                <span class="samm-brand-separator" aria-hidden="true"></span>
            @endif
            <x-samm.symbol :size="32" />
            <span>{{ $brandName }}</span>
        </a>
        <div class="samm-topbar-actions">
            <x-samm.locale-switcher />
            <x-samm.quick-exit />
        </div>
    </header>

    <main id="contenu" class="samm-page">
        @yield('content')
    </main>

    <footer class="samm-footer">
        {{ __('public.footer.initiative') }}
    </footer>
</body>
</html>
