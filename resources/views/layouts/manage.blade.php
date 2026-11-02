@php
    $areaTitle = __('manage.layout.area');
    $pageTitle = isset($title) ? $title.' · '.$areaTitle.' · '.config('app.name') : $areaTitle.' · '.config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="same-origin">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('images/samm-logo/png/favicon-32.png') }}" sizes="32x32">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="visually-hidden-focusable" href="#contenu">{{ __('manage.layout.skip_to_content') }}</a>

    <header class="samm-topbar">
        <a class="samm-brand" href="{{ auth()->check() ? route('manage.dashboard') : route('login') }}">
            @if (($organization ?? null)?->hasLogo())
                <img src="{{ $organization->logoUrl() }}" alt="" class="samm-org-logo">
                <span class="samm-brand-separator" aria-hidden="true"></span>
            @endif
            <x-samm.symbol :size="32" />
            <span>{{ config('app.name') }}</span>
            <span class="samm-caption samm-brand-area">{{ $areaTitle }}</span>
        </a>
        @auth
            <div class="samm-topbar-actions">
                @can('viewAny', \App\Modules\Cases\Models\CaseFile::class)
                    <a class="btn btn-link" href="{{ route('manage.cases.index') }}">
                        <i class="bi bi-folder2-open" aria-hidden="true"></i>
                        {{ __('manage.layout.cases_link') }}
                    </a>
                @endcan
                @can('settings.manage')
                    <a class="btn btn-link" href="{{ route('manage.organization.edit') }}">
                        <i class="bi bi-building-gear" aria-hidden="true"></i>
                        {{ __('manage.layout.organization_link') }}
                    </a>
                @endcan
                @can('audit.view')
                    <a class="btn btn-link" href="{{ route('manage.audit.index') }}">
                        <i class="bi bi-journal-text" aria-hidden="true"></i>
                        {{ __('manage.layout.audit_link') }}
                    </a>
                @endcan
                <span class="samm-body-sm">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        {{ __('manage.layout.logout') }}
                    </button>
                </form>
            </div>
        @endauth
    </header>

    <main id="contenu" class="samm-manage-page">
        @yield('content')
    </main>
</body>
</html>
