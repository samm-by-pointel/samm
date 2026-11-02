@extends('layouts.manage', ['title' => __('manage.audit.title')])

@section('content')
    <h1 class="samm-title">{{ __('manage.audit.title') }}</h1>
    <p class="samm-muted">{{ __('manage.audit.lead') }}</p>

    @if ($filteredCase)
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-funnel" aria-hidden="true"></i>
            <div>
                <b>{{ __('manage.audit.filtered_case', ['reference' => $filteredCase->report->reference]) }}</b>
                <a href="{{ route('manage.cases.show', $filteredCase) }}">{{ __('manage.audit.back_to_case') }}</a> · <a href="{{ route('manage.audit.index') }}">{{ __('manage.audit.clear_filter') }}</a>
            </div>
        </div>
    @endif

    <form method="GET" action="{{ route('manage.audit.index') }}" class="samm-row mb-4">
        @if ($filteredCase)
            <input type="hidden" name="case" value="{{ $filteredCase->uuid }}">
        @endif
        <label for="action" class="form-label mb-0">{{ __('manage.audit.filter_action') }}</label>
        <select id="action" name="action" class="form-select w-auto">
            <option value="">{{ __('manage.audit.all_actions') }}</option>
            @foreach ($actions as $action)
                <option value="{{ $action->value }}" @selected($selectedAction === $action)>{{ $action->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline-secondary">{{ __('manage.audit.filter_submit') }}</button>
    </form>

    @if ($entries->isEmpty())
        <div class="samm-alert samm-alert-info" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <div>{{ __('manage.audit.empty') }}</div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table samm-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('manage.audit.columns.date') }}</th>
                        <th scope="col">{{ __('manage.audit.columns.action') }}</th>
                        <th scope="col">{{ __('manage.audit.columns.actor') }}</th>
                        <th scope="col">{{ __('manage.audit.columns.subject') }}</th>
                        <th scope="col">{{ __('manage.audit.columns.reference') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr>
                            <td><time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->translatedFormat('d M Y, H:i') }}</time></td>
                            <td>{{ $entry->action->label() }}</td>
                            <td>{{ $entry->actor?->name ?? __('audit.actor_kinds.'.$entry->actor_kind->value) }}</td>
                            <td>
                                @if ($entry->subject)
                                    {{ __('manage.audit.subjects.'.class_basename($entry->subject)) }}
                                    @if ($entry->subject instanceof \App\Modules\Cases\Models\CaseFile)
                                        · <a class="samm-code-sm" href="{{ route('manage.cases.show', $entry->subject) }}">{{ $entry->subject->report?->reference }}</a>
                                    @elseif ($entry->subject instanceof \App\Modules\Reporting\Models\Report)
                                        · <span class="samm-code-sm">{{ $entry->subject->reference }}</span>
                                    @elseif (isset($entry->subject->name) && is_string($entry->subject->name))
                                        · {{ $entry->subject->name }}
                                    @elseif (isset($entry->subject->key))
                                        · {{ $entry->subject->key }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="samm-code-sm">{{ $entry->uuid }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $entries->links() }}
    @endif
@endsection
