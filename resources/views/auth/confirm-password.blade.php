@extends('layouts.manage', ['title' => __('manage.confirm_password.title')])

@section('content')
    <div class="samm-card samm-auth-card">
        <h1 class="samm-title">{{ __('manage.confirm_password.title') }}</h1>
        <p class="samm-muted">{{ __('manage.confirm_password.lead') }}</p>

        <form method="POST" action="{{ route('password.confirm') }}" novalidate>
            @csrf

            <div class="mb-4">
                <label for="password" class="form-label">{{ __('manage.auth.fields.password') }}</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" autofocus>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">{{ __('manage.confirm_password.submit') }}</button>
        </form>
    </div>
@endsection
