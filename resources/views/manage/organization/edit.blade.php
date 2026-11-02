@extends('layouts.manage', ['title' => __('manage.organization.title')])

@section('content')
    <h1 class="samm-title">{{ __('manage.organization.title') }}</h1>
    <p class="samm-muted">{{ __('manage.organization.lead') }}</p>

    @if (session('status'))
        <div class="samm-alert samm-alert-succes" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('manage.organization.update') }}" enctype="multipart/form-data" novalidate class="samm-section">
        @csrf
        @method('PUT')

        <section class="samm-card mb-4" aria-labelledby="identity-section">
            <h2 id="identity-section" class="samm-heading">{{ __('manage.organization.sections.identity') }}</h2>
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('manage.organization.fields.name') }}</label>
                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $organization->name) }}" maxlength="120" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <fieldset>
                <legend class="form-label">{{ __('manage.organization.fields.service_name') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></legend>
                <p class="samm-caption">{{ __('manage.organization.fields.service_name_help') }}</p>
                <div class="row g-3">
                    @foreach ($locales as $code => $language)
                        <div class="col-sm-6">
                            <label for="service_name_{{ $code }}" class="form-label">{{ $language }}</label>
                            <input id="service_name_{{ $code }}" name="service_name[{{ $code }}]" type="text" class="form-control @error('service_name.'.$code) is-invalid @enderror" value="{{ old('service_name.'.$code, $organization->service_name[$code] ?? '') }}" maxlength="120" lang="{{ $code }}">
                            @error('service_name.'.$code)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>
            </fieldset>
        </section>

        <section class="samm-card mb-4" aria-labelledby="appearance-section">
            <h2 id="appearance-section" class="samm-heading">{{ __('manage.organization.sections.appearance') }}</h2>
            <p class="samm-caption">{{ __('manage.organization.fields.colors_help') }}</p>
            <div class="row g-3 mb-3">
                @foreach (['primary_color' => '#0E5A55', 'secondary_color' => '#A84B14'] as $field => $default)
                    <div class="col-sm-6">
                        <label for="{{ $field }}" class="form-label">{{ __('manage.organization.fields.'.$field) }}</label>
                        <div class="samm-row flex-nowrap">
                            <input id="{{ $field }}" name="{{ $field }}" type="text" class="form-control samm-code-sm @error($field) is-invalid @enderror" value="{{ old($field, $organization->{$field}) }}" placeholder="{{ $default }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}" autocomplete="off">
                            <span class="samm-swatch" style="background: {{ old($field, $organization->{$field}) ?: 'var(--'.($field === 'primary_color' ? 'samm' : 'terre').')' }}" aria-hidden="true"></span>
                        </div>
                        @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
            <div class="mb-2">
                <label for="logo" class="form-label">{{ __('manage.organization.fields.logo') }}</label>
                <p class="samm-caption">{{ __('manage.organization.fields.logo_help') }}</p>
                @if ($organization->hasLogo())
                    <div class="samm-row mb-2">
                        <img src="{{ $organization->logoUrl() }}" alt="{{ __('manage.organization.fields.logo_current') }}" class="samm-logo-preview">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                            <label class="form-check-label" for="remove_logo">{{ __('manage.organization.fields.remove_logo') }}</label>
                        </div>
                    </div>
                @endif
                <input id="logo" name="logo" type="file" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </section>

        <section class="samm-card mb-4" aria-labelledby="public-section">
            <h2 id="public-section" class="samm-heading">{{ __('manage.organization.sections.public') }}</h2>
            <div class="mb-3">
                <label for="neutral_title" class="form-label">{{ __('manage.organization.fields.neutral_title') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
                <p class="samm-caption">{{ __('manage.organization.fields.neutral_title_help') }}</p>
                <input id="neutral_title" name="neutral_title" type="text" class="form-control @error('neutral_title') is-invalid @enderror" value="{{ old('neutral_title', $organization->neutral_title) }}" maxlength="80">
                @error('neutral_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="quick_exit_url" class="form-label">{{ __('manage.organization.fields.quick_exit_url') }} <span class="samm-caption">{{ __('report_form.optional') }}</span></label>
                <p class="samm-caption">{{ __('manage.organization.fields.quick_exit_url_help', ['default' => config('samm.quick_exit_url')]) }}</p>
                <input id="quick_exit_url" name="quick_exit_url" type="url" class="form-control @error('quick_exit_url') is-invalid @enderror" value="{{ old('quick_exit_url', $organization->quick_exit_url) }}" placeholder="https://">
                @error('quick_exit_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="default_locale" class="form-label">{{ __('manage.organization.fields.default_locale') }}</label>
                <select id="default_locale" name="default_locale" class="form-select w-auto @error('default_locale') is-invalid @enderror">
                    @foreach ($locales as $code => $language)
                        <option value="{{ $code }}" @selected(old('default_locale', $organization->default_locale ?? 'fr') === $code)>{{ $language }}</option>
                    @endforeach
                </select>
                @error('default_locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </section>

        <button type="submit" class="btn btn-primary">{{ __('manage.organization.save') }}</button>
    </form>

    @include('manage.organization.partials.emergency-contacts')
    @include('manage.organization.partials.categories')
@endsection
