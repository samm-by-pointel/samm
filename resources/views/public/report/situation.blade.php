@extends('public.report.layout')

@section('intro')
    <p class="samm-body-lg">{{ __('report_form.situation.lead') }}</p>
@endsection

@section('fields')
    <fieldset>
        <legend class="samm-label">{{ __('report_form.situation.legend') }}</legend>
        <div class="samm-choices px-0" role="radiogroup" aria-label="{{ __('report_form.situation.legend') }}">
            @foreach ($categories as $category)
                <label @class(['samm-choice', 'is-selected' => old('category', $values['category'] ?? null) === $category->uuid])>
                    <input type="radio" name="category" value="{{ $category->uuid }}" @checked(old('category', $values['category'] ?? null) === $category->uuid) required>
                    <div>
                        <b>{{ $category->name() }}</b>
                        @if ($category->description())
                            <span>{{ $category->description() }}</span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
        @error('category')
            <p class="invalid-feedback d-block">{{ $message }}</p>
        @enderror
    </fieldset>
@endsection
