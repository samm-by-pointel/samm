@extends('layouts.manage', ['title' => __('manage.auth.reset.title')])

@section('content')
    <div class="samm-card samm-auth-card">
        <h1 class="samm-title">{{ __('manage.auth.reset.title') }}</h1>
        <p class="samm-muted">{{ __('manage.auth.reset.rules') }}</p>

        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('manage.auth.fields.email') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ __('manage.auth.fields.new_password') }}</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">{{ __('manage.auth.fields.password_confirmation') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary">{{ __('manage.auth.reset.submit') }}</button>
        </form>
    </div>
@endsection
