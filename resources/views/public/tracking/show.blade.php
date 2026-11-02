@extends('layouts.public', ['title' => __('tracking.show.title')])

@section('content')
    <section class="samm-section">
        <h1 class="samm-title">{{ __('tracking.show.title') }}</h1>
        <dl class="samm-summary samm-card">
            <div>
                <dt>{{ __('tracking.show.reference') }}</dt>
                <dd class="samm-code-sm">{{ $report->reference }}</dd>
            </div>
            <div>
                <dt>{{ __('tracking.show.status') }}</dt>
                <dd><x-samm.status-badge :status="$case->status" :public="true" /></dd>
            </div>
            <div>
                <dt>{{ __('tracking.show.category') }}</dt>
                <dd>{{ $report->category->name() }}</dd>
            </div>
            <div>
                <dt>{{ __('tracking.show.submitted_at') }}</dt>
                <dd><time datetime="{{ $report->submitted_at->toIso8601String() }}">{{ $report->submitted_at->translatedFormat('d F Y') }}</time></dd>
            </div>
        </dl>
    </section>

    @if (session('status'))
        <div class="samm-alert samm-alert-succes" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <section class="samm-section" aria-labelledby="thread-title">
        <h2 id="thread-title" class="samm-heading">{{ __('tracking.thread.title') }}</h2>
        <p class="samm-muted">{{ __('tracking.thread.lead') }}</p>
        <x-samm.message-thread :messages="$messages" />
    </section>

    <section class="samm-section">
        <form method="POST" action="{{ route('tracking.messages.store') }}" novalidate class="samm-card">
            @csrf
            <label for="body" class="form-label">{{ __('tracking.thread.compose') }}</label>
            <p class="samm-caption">{{ __('tracking.thread.compose_help') }}</p>
            <textarea id="body" name="body" rows="5" class="form-control @error('body') is-invalid @enderror" required maxlength="5000">{{ old('body') }}</textarea>
            @error('body')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="samm-row mt-3">
                <button type="submit" class="btn btn-primary">{{ __('tracking.thread.send') }}</button>
            </div>
        </form>
    </section>

    <section class="samm-section">
        <form method="POST" action="{{ route('tracking.logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-door-closed" aria-hidden="true"></i>
                {{ __('tracking.show.logout') }}
            </button>
        </form>
        <p class="samm-caption mt-2">{{ __('tracking.show.logout_help', ['minutes' => config('samm.tracking.session_minutes')]) }}</p>
    </section>
@endsection
