@extends('layouts.public', ['title' => __('tracking.login.title')])

@section('content')
    <section class="samm-section">
        <h1 class="samm-title">{{ __('tracking.login.title') }}</h1>
        <p class="samm-body-lg">{{ __('tracking.login.lead') }}</p>
    </section>

    @if (session('status'))
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('tracking.authenticate') }}" novalidate class="samm-card samm-section">
        @csrf

        @error('reference')
            <div class="samm-alert samm-alert-urgent mt-0" role="alert">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <div>
                    <b>{{ __('tracking.login.failed_title') }}</b>
                    {{ $message }}
                </div>
            </div>
        @enderror

        <div class="mb-3">
            <label for="reference" class="form-label">{{ __('tracking.login.reference') }}</label>
            <input id="reference" name="reference" type="text" class="form-control samm-code-lg" value="{{ old('reference') }}" placeholder="SMM-XXXX-XXXX" autocomplete="off" autocapitalize="characters" spellcheck="false" required>
        </div>

        <div class="mb-2">
            <label for="secret" class="form-label">{{ __('tracking.login.secret') }}</label>
            <input id="secret" name="secret" type="password" class="form-control samm-code-lg @error('secret') is-invalid @enderror" autocomplete="off" required>
            @error('secret')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <p class="samm-caption">{{ __('tracking.login.help') }}</p>

        <div class="samm-row mt-4">
            <button type="submit" class="btn btn-primary">{{ __('tracking.login.submit') }}</button>
            <a class="btn btn-link" href="{{ route('public.home') }}">{{ __('report_form.back_home') }}</a>
        </div>
    </form>
@endsection
