@extends('public.report.layout')

@php
    $flag = fn (string $name) => old($name, isset($values[$name]) ? ($values[$name] === null ? '' : ($values[$name] ? '1' : '0')) : '');
@endphp

@section('intro')
    <p class="samm-body-lg">{{ __('report_form.description.lead') }}</p>
@endsection

@section('fields')
    <div class="mb-4">
        <label for="description" class="form-label">{{ __('report_form.description.description') }}</label>
        <p class="samm-caption">{{ __('report_form.description.description_help') }}</p>
        <textarea id="description" name="description" rows="8" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $values['description'] ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="facts_date" class="form-label">{{ __('report_form.description.facts_date') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
        <input id="facts_date" name="facts_date" type="date" class="form-control @error('facts_date') is-invalid @enderror" value="{{ old('facts_date', $values['facts_date'] ?? '') }}" max="{{ now()->toDateString() }}">
        @error('facts_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="facts_period" class="form-label">{{ __('report_form.description.facts_period') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
        <p class="samm-caption">{{ __('report_form.description.facts_period_help') }}</p>
        <input id="facts_period" name="facts_period" type="text" class="form-control @error('facts_period') is-invalid @enderror" value="{{ old('facts_period', $values['facts_period'] ?? '') }}" maxlength="500">
        @error('facts_period')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="location" class="form-label">{{ __('report_form.description.location') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
        <input id="location" name="location" type="text" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $values['location'] ?? '') }}" maxlength="500">
        @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @foreach (['is_recurring' => true, 'is_ongoing' => true, 'immediate_danger' => false] as $name => $allowUnknown)
        <fieldset class="mb-4 samm-flag">
            <legend class="form-label">{{ __('report_form.description.'.$name) }} <span class="samm-caption">{{ __('report_form.optional') }}</span></legend>
            <div class="samm-row">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $name }}_yes" value="1" @checked($flag($name) === '1')>
                    <label class="form-check-label" for="{{ $name }}_yes">{{ __('report_form.yes') }}</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $name }}_no" value="0" @checked($flag($name) === '0')>
                    <label class="form-check-label" for="{{ $name }}_no">{{ __('report_form.no') }}</label>
                </div>
                @if ($allowUnknown)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $name }}_unknown" value="" @checked($flag($name) === '')>
                        <label class="form-check-label" for="{{ $name }}_unknown">{{ __('report_form.unknown') }}</label>
                    </div>
                @endif
            </div>
            @error($name)
                <p class="invalid-feedback d-block">{{ $message }}</p>
            @enderror
        </fieldset>
    @endforeach

    <x-samm.emergency-contacts />
@endsection
