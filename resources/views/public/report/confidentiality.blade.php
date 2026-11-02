@extends('public.report.layout')

@php
    use App\Modules\Reporting\Enums\ConfidentialityLevel;

    $selected = old('confidentiality_level', $values['confidentiality_level'] ?? null);
@endphp

@section('intro')
    <p class="samm-body-lg">{{ __('report_form.confidentiality.lead') }}</p>
@endsection

@section('fields')
    <x-samm.confidentiality-choice :levels="$levels" :selected="$selected" />
    @error('confidentiality_level')
        <p class="invalid-feedback d-block">{{ $message }}</p>
    @enderror

    <div class="samm-conditional mt-4" data-level="{{ ConfidentialityLevel::Pseudonymous->value }}">
        <h2 class="samm-heading">{{ __('report_form.confidentiality.pseudonym_title') }}</h2>
        <p class="samm-caption">{{ __('report_form.confidentiality.pseudonym_help') }}</p>
        <label for="pseudonym" class="form-label">{{ __('report_form.confidentiality.pseudonym') }}</label>
        <input id="pseudonym" name="pseudonym" type="text" class="form-control @error('pseudonym') is-invalid @enderror" value="{{ old('pseudonym', $values['pseudonym'] ?? '') }}" maxlength="80" autocomplete="off">
        @error('pseudonym')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="samm-conditional mt-4" data-level="{{ ConfidentialityLevel::Confidential->value }}">
        <h2 class="samm-heading">{{ __('report_form.confidentiality.identity_title') }}</h2>
        <p class="samm-caption">{{ __('report_form.confidentiality.identity_help') }}</p>

        <div class="mb-3">
            <label for="identity_full_name" class="form-label">{{ __('report_form.confidentiality.identity_full_name') }}</label>
            <input id="identity_full_name" name="identity_full_name" type="text" class="form-control @error('identity_full_name') is-invalid @enderror" value="{{ old('identity_full_name', $values['identity_full_name'] ?? '') }}" maxlength="190" autocomplete="off">
            @error('identity_full_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="identity_email" class="form-label">{{ __('report_form.confidentiality.identity_email') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
            <input id="identity_email" name="identity_email" type="email" class="form-control @error('identity_email') is-invalid @enderror" value="{{ old('identity_email', $values['identity_email'] ?? '') }}" maxlength="190" autocomplete="off">
            @error('identity_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label for="identity_phone" class="form-label">{{ __('report_form.confidentiality.identity_phone') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
            <input id="identity_phone" name="identity_phone" type="tel" class="form-control @error('identity_phone') is-invalid @enderror" value="{{ old('identity_phone', $values['identity_phone'] ?? '') }}" maxlength="32" autocomplete="off">
            @error('identity_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endsection
