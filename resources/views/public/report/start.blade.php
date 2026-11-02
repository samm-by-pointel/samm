@extends('layouts.public', ['title' => __('report_form.start.title')])

@section('content')
    <section class="samm-section">
        <h1 class="samm-title">{{ __('report_form.start.title') }}</h1>
        <p class="samm-body-lg">{{ __('report_form.start.lead') }}</p>
    </section>

    <section class="samm-section samm-card">
        <h2 class="samm-heading">{{ __('report_form.start.collected_title') }}</h2>
        <ul>
            <li>{{ __('report_form.start.collected_1') }}</li>
            <li>{{ __('report_form.start.collected_2') }}</li>
            <li>{{ __('report_form.start.collected_3') }}</li>
        </ul>
        <h2 class="samm-heading mt-4">{{ __('report_form.start.not_collected_title') }}</h2>
        <ul>
            <li>{{ __('report_form.start.not_collected_1') }}</li>
            <li>{{ __('report_form.start.not_collected_2') }}</li>
        </ul>
        <p class="samm-caption mb-0">{{ __('report_form.start.limits') }}</p>
    </section>

    <section class="samm-section">
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>
                <b>{{ __('report_form.start.session_title') }}</b>
                {{ __('report_form.start.session_help') }}
            </div>
        </div>
    </section>

    <section class="samm-section samm-row">
        @if ($hasDraft)
            <a class="btn btn-primary" href="{{ route('report.step.show', $resumeStep) }}">{{ __('report_form.start.resume') }}</a>
            <form method="POST" action="{{ route('report.abandon') }}">
                @csrf
                <button type="submit" class="btn btn-link">{{ __('report_form.abandon') }}</button>
            </form>
        @else
            <a class="btn btn-primary" href="{{ route('report.step.show', $firstStep) }}">{{ __('report_form.start.begin') }}</a>
        @endif
        <a class="btn btn-link" href="{{ route('public.home') }}">{{ __('report_form.back_home') }}</a>
    </section>
@endsection
