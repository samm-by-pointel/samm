@extends('layouts.manage', ['title' => __('manage.cases.index.title')])

@section('content')
    <h1 class="samm-title">{{ __('manage.cases.index.title') }}</h1>

    <form method="GET" action="{{ route('manage.cases.index') }}" class="samm-card mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-2">
                <label for="status" class="form-label">{{ __('manage.cases.filters.status') }}</label>
                <select id="status" name="status" class="form-select">
                    <option value="">{{ __('manage.cases.filters.all') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="category" class="form-label">{{ __('manage.cases.filters.category') }}</label>
                <select id="category" name="category" class="form-select">
                    <option value="">{{ __('manage.cases.filters.all') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->uuid }}" @selected(($filters['category'] ?? null) === $category->uuid)>{{ $category->name() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="assignment" class="form-label">{{ __('manage.cases.filters.assignment') }}</label>
                <select id="assignment" name="assignment" class="form-select">
                    <option value="all" @selected(($filters['assignment'] ?? 'all') === 'all')>{{ __('manage.cases.filters.all') }}</option>
                    <option value="mine" @selected(($filters['assignment'] ?? null) === 'mine')>{{ __('manage.cases.filters.mine') }}</option>
                    <option value="unassigned" @selected(($filters['assignment'] ?? null) === 'unassigned')>{{ __('manage.cases.filters.unassigned') }}</option>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="priority" class="form-label">{{ __('manage.cases.filters.priority') }}</label>
                <select id="priority" name="priority" class="form-select">
                    <option value="">{{ __('manage.cases.filters.all') }}</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? null) === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-1">
                <label for="from" class="form-label">{{ __('manage.cases.filters.from') }}</label>
                <input id="from" name="from" type="date" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-lg-1">
                <label for="to" class="form-label">{{ __('manage.cases.filters.to') }}</label>
                <input id="to" name="to" type="date" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-12 col-lg-1">
                <button type="submit" class="btn btn-outline-secondary w-100">{{ __('manage.cases.filters.submit') }}</button>
            </div>
        </div>
        @if ($errors->any())
            <p class="invalid-feedback d-block mt-2">{{ $errors->first() }}</p>
        @endif
    </form>

    @if ($cases->isEmpty())
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>{{ __('manage.cases.index.empty') }}</div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table samm-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('manage.cases.columns.reference') }}</th>
                        <th scope="col">{{ __('manage.cases.columns.category') }}</th>
                        <th scope="col">{{ __('manage.cases.columns.status') }}</th>
                        <th scope="col">{{ __('manage.cases.columns.assignee') }}</th>
                        <th scope="col">{{ __('manage.cases.columns.submitted_at') }}</th>
                        <th scope="col">{{ __('manage.cases.columns.messages') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cases as $case)
                        <tr>
                            <td>
                                <a class="samm-code-sm" href="{{ route('manage.cases.show', $case) }}">{{ $case->report->reference }}</a>
                                @if ($case->priority->isHigh())
                                    <span class="samm-badge samm-badge-urgent ms-2">{{ $case->priority->label() }}</span>
                                @endif
                            </td>
                            <td>{{ $case->report->category->name() }}</td>
                            <td><x-samm.status-badge :status="$case->status" /></td>
                            <td>{{ $case->assignee?->name ?? __('manage.cases.index.unassigned') }}</td>
                            <td><time datetime="{{ $case->report->submitted_at?->toIso8601String() }}">{{ $case->report->submitted_at?->translatedFormat('d M Y') }}</time></td>
                            <td>
                                @php($unread = $case->report->conversation?->unread_for_staff_count ?? 0)
                                @if ($unread > 0)
                                    <span class="samm-badge samm-badge-traitement">{{ trans_choice('manage.cases.index.unread', $unread, ['count' => $unread]) }}</span>
                                @else
                                    <span class="samm-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $cases->links() }}
    @endif
@endsection
