{{-- Cadre commun des étapes du formulaire : stepper, titre, formulaire, navigation, abandon. --}}
@extends('layouts.public', ['title' => $step->label()])

@section('content')
    <x-samm.stepper :steps="\App\Modules\Reporting\Enums\ReportStep::cases()" :current="$step" class="samm-section" />

    <h1 class="samm-title">{{ $step->label() }}</h1>
    @yield('intro')

    @if ($errors->any())
        <div class="samm-alert samm-alert-urgent" role="alert">
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div>
                <b>{{ __('report_form.errors.title') }}</b>
                {{ __('report_form.errors.help') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $formAction ?? route('report.step.store', $step) }}" novalidate class="samm-card samm-section">
        @csrf
        @yield('fields')

        <div class="samm-row mt-4">
            <button type="submit" class="btn btn-primary">{{ $submitLabel ?? __('report_form.continue') }}</button>
            @if ($step->previous())
                <a class="btn btn-link" href="{{ route('report.step.show', $step->previous()) }}">{{ __('report_form.back') }}</a>
            @else
                <a class="btn btn-link" href="{{ route('report.start') }}">{{ __('report_form.back') }}</a>
            @endif
        </div>
    </form>

    <form method="POST" action="{{ route('report.abandon') }}" class="samm-section">
        @csrf
        <button type="submit" class="btn btn-link samm-muted">{{ __('report_form.abandon') }}</button>
    </form>
@endsection
