@extends('layouts.manage', ['title' => __('manage.two_factor.title')])

@section('content')
    <div class="samm-card samm-auth-card">
        <h1 class="samm-title">{{ __('manage.two_factor.title') }}</h1>
        <p class="samm-muted">{{ __('manage.two_factor.lead') }}</p>

        <form method="POST" action="{{ route('two-factor.login') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="code" class="form-label">{{ __('manage.two_factor.code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" class="form-control samm-code-lg @error('code') is-invalid @enderror" autocomplete="one-time-code" autofocus>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <details class="mb-4" @if ($errors->has('recovery_code') || old('recovery_code')) open @endif>
                <summary class="samm-label">{{ __('manage.two_factor.use_recovery') }}</summary>
                <div class="mt-3">
                    <label for="recovery_code" class="form-label">{{ __('manage.two_factor.recovery_code') }}</label>
                    <input id="recovery_code" name="recovery_code" type="text" class="form-control samm-code-sm @error('recovery_code') is-invalid @enderror" autocomplete="off">
                    @error('recovery_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </details>

            <button type="submit" class="btn btn-primary">{{ __('manage.two_factor.submit') }}</button>
        </form>
    </div>
@endsection
