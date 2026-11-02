@extends('public.report.layout')

@php
    $checked = fn (string $name) => (bool) old($name, $values[$name] ?? false);
@endphp

@section('intro')
    <p class="samm-body-lg">{{ __('report_form.consent.lead') }}</p>

    <section class="samm-card samm-section" aria-labelledby="recap-title">
        <h2 id="recap-title" class="samm-heading">{{ __('report_form.consent.summary_title') }}</h2>
        <dl class="samm-summary">
            <div>
                <dt>{{ __('report_form.consent.summary_category') }}</dt>
                <dd>{{ $category?->name() ?? '-' }}</dd>
            </div>
            <div>
                <dt>{{ __('report_form.consent.summary_level') }}</dt>
                <dd>{{ $level?->label() ?? '-' }}</dd>
            </div>
            <div>
                <dt>{{ __('report_form.consent.summary_danger') }}</dt>
                <dd>{{ $immediateDanger ? __('report_form.yes') : __('report_form.no') }}</dd>
            </div>
        </dl>
        <p class="samm-caption mb-0">{{ __('report_form.consent.summary_help') }}</p>
    </section>
@endsection

@section('fields')
    @foreach (['processing' => true, 'contact' => true, 'identity_disclosure' => $isConfidential] as $purpose => $shown)
        @continue(! $shown)
        <div class="form-check samm-consent @error('consent_'.$purpose) is-invalid @enderror">
            <input class="form-check-input @error('consent_'.$purpose) is-invalid @enderror" type="checkbox" value="1" id="consent_{{ $purpose }}" name="consent_{{ $purpose }}" @checked($checked('consent_'.$purpose))>
            <label class="form-check-label" for="consent_{{ $purpose }}">
                <b>{{ __('reporting.consents.'.$purpose.'.label') }}</b>
                @if ($purpose === 'contact')
                    <span class="samm-caption">{{ __('report_form.optional') }}</span>
                @endif
                <span class="d-block samm-muted">{{ __('reporting.consents.'.$purpose.'.description') }}</span>
            </label>
            @error('consent_'.$purpose)
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    @endforeach

    <p class="samm-caption mt-4 mb-0">{{ __('report_form.consent.before_submit') }}</p>
@endsection
