@extends('layouts.manage', ['title' => __('manage.auth.forgot.title')])

@section('content')
    <div class="samm-card samm-auth-card">
        <h1 class="samm-title">{{ __('manage.auth.forgot.title') }}</h1>
        <p class="samm-muted">{{ __('manage.auth.forgot.lead') }}</p>

        @if (session('status'))
            <div class="samm-alert samm-alert-succes" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">{{ __('manage.auth.fields.email') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="samm-row">
                <button type="submit" class="btn btn-primary">{{ __('manage.auth.forgot.submit') }}</button>
                <a class="btn btn-link" href="{{ route('login') }}">{{ __('manage.auth.forgot.back') }}</a>
            </div>
        </form>
    </div>
@endsection
