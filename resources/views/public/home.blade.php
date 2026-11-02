@extends('layouts.public')

@section('content')
    <section class="samm-section">
        <h1 class="samm-display">{{ __('public.home.title') }}</h1>
        <p class="samm-body-lg">{{ __('public.home.lead') }}</p>
    </section>

    @if (session('status'))
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <section class="samm-section samm-row">
        <a class="btn btn-primary" href="{{ route('report.start') }}">{{ __('public.home.report_cta') }}</a>
        <a class="btn btn-outline-secondary" href="{{ route('tracking.login') }}">{{ __('public.home.track_cta') }}</a>
    </section>

    <section class="samm-section">
        <x-samm.emergency-contacts />
    </section>

    <section class="samm-section">
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>
                <b>{{ __('public.home.quick_exit_help_title') }}</b>
                {{ __('public.home.quick_exit_help') }}
            </div>
        </div>
    </section>
@endsection
