@extends('layouts.manage', ['title' => __('manage.auth.login.title')])

@section('content')
    <div class="samm-card samm-auth-card">
        <h1 class="samm-title">{{ __('manage.auth.login.title') }}</h1>

        @if (session('status'))
            <div class="samm-alert samm-alert-succes" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('manage.auth.fields.email') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ __('manage.auth.fields.password') }}</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check mb-4">
                <input id="remember" name="remember" type="checkbox" class="form-check-input" value="1" @checked(old('remember'))>
                <label for="remember" class="form-check-label">{{ __('manage.auth.login.remember') }}</label>
            </div>

            <div class="samm-row">
                <button type="submit" class="btn btn-primary">{{ __('manage.auth.login.submit') }}</button>
                @if (Route::has('password.request'))
                    <a class="btn btn-link" href="{{ route('password.request') }}">{{ __('manage.auth.login.forgot') }}</a>
                @endif
            </div>
        </form>
    </div>
@endsection
