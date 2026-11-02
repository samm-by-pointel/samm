@extends('public.report.layout')

@section('intro')
    <p class="samm-body-lg">{{ __('report_form.people.lead') }}</p>
    @if ($immediateDanger)
        <x-samm.emergency-contacts />
    @endif
@endsection

@section('fields')
    <div class="mb-4">
        <label for="people_involved" class="form-label">{{ __('report_form.people.people_involved') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
        <p class="samm-caption">{{ __('report_form.people.people_involved_help') }}</p>
        <textarea id="people_involved" name="people_involved" rows="4" class="form-control @error('people_involved') is-invalid @enderror">{{ old('people_involved', $values['people_involved'] ?? '') }}</textarea>
        @error('people_involved')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="witnesses" class="form-label">{{ __('report_form.people.witnesses') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
        <p class="samm-caption">{{ __('report_form.people.witnesses_help') }}</p>
        <textarea id="witnesses" name="witnesses" rows="3" class="form-control @error('witnesses') is-invalid @enderror">{{ old('witnesses', $values['witnesses'] ?? '') }}</textarea>
        @error('witnesses')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endsection
