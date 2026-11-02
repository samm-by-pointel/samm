@extends('layouts.public', ['title' => __('report_form.confirmation.title')])

@section('content')
    <section class="samm-section">
        <div class="samm-alert samm-alert-succes" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            <div>
                <b>{{ __('report_form.confirmation.title') }}</b>
                {{ __('report_form.confirmation.lead') }}
            </div>
        </div>
    </section>

    <section class="samm-section">
        <x-samm.tracking-code :reference="$reference" :secret="$secret" />
    </section>

    <section class="samm-section">
        <h2 class="samm-heading">{{ __('report_form.confirmation.next_title') }}</h2>
        <p>{{ __('report_form.confirmation.next_help') }}</p>
        <div class="samm-row">
            <a class="btn btn-outline-secondary" href="{{ route('tracking.login') }}">{{ __('public.home.track_cta') }}</a>
            <a class="btn btn-link" href="{{ route('public.home') }}">{{ __('report_form.back_home') }}</a>
        </div>
    </section>
@endsection
