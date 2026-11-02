@extends('layouts.manage', ['title' => __('manage.dashboard.title')])

@section('content')
    <h1 class="samm-title">{{ __('manage.dashboard.title') }}</h1>
    <p class="samm-body-lg">{{ __('manage.dashboard.welcome', ['name' => auth()->user()->name]) }}</p>

    @if ($canViewCases)
        <div class="samm-counters samm-section">
            <a class="samm-card samm-counter text-decoration-none" href="{{ route('manage.cases.index', ['assignment' => 'mine']) }}">
                <span class="n">{{ $counters['mine_open'] }}</span>
                <span class="d-block samm-label">{{ __('manage.dashboard.mine_open') }}</span>
            </a>
            <a class="samm-card samm-counter text-decoration-none" href="{{ route('manage.cases.index', ['assignment' => 'unassigned']) }}">
                <span class="n">{{ $counters['unassigned_open'] }}</span>
                <span class="d-block samm-label">{{ __('manage.dashboard.unassigned_open') }}</span>
            </a>
            <a class="samm-card samm-counter text-decoration-none" href="{{ route('manage.cases.index') }}">
                <span class="n">{{ $counters['visible_total'] }}</span>
                <span class="d-block samm-label">{{ __('manage.dashboard.visible_total') }}</span>
            </a>
        </div>
    @else
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>{{ __('manage.dashboard.no_case_access') }}</div>
        </div>
    @endif
@endsection
