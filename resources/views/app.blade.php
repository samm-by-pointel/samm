@php
    /*
     * Gabarit racine des pages React. Le titre d'onglet est calculé ici, côté
     * serveur, pour qu'un titre neutre s'applique avant même le chargement du
     * script (charte §8) ; les pages le complètent ensuite sans jamais le révéler.
     */
    $site = $page['props']['site'] ?? [];
    $isManage = request()->routeIs('manage.*', 'login', 'password.*', 'two-factor.*');
    $neutralTitle = $site['neutralTitle'] ?? null;
    $tabTitle = $isManage
        ? __('manage.layout.area').' · '.config('app.name')
        : ($neutralTitle ?: ($site['name'] ?? config('app.name')));
    $organization = app(\App\Modules\Organizations\Contracts\OrganizationResolver::class)->current();
    $brandingCss = $isManage ? '' : app(\App\Modules\Organizations\Services\BrandingCss::class)->for($organization);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($isManage)
        <meta name="referrer" content="same-origin">
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="referrer" content="no-referrer">
    @endif
    <title>{{ $tabTitle }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('images/samm-logo/png/favicon-32.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/samm-logo/png/samm-icone-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @if ($brandingCss !== '')
        {{-- Couleurs de l'organisation : valeurs hexadécimales validées par BrandingCss (charte §9) --}}
        <style>{!! $brandingCss !!}</style>
    @endif
</head>
<body>
    @inertia
</body>
</html>
