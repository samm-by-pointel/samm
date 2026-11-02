@extends('layouts.manage', ['title' => $report->reference])

@section('content')
    <div class="samm-row justify-content-between">
        <a class="btn btn-link ps-0" href="{{ route('manage.cases.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('manage.cases.show.back') }}</a>
        @can('audit.view')
            <a class="btn btn-link" href="{{ route('manage.audit.index', ['case' => $case->uuid]) }}"><i class="bi bi-journal-text" aria-hidden="true"></i> {{ __('manage.cases.show.audit_link') }}</a>
        @endcan
    </div>

    <div class="samm-row mb-3">
        <h1 class="samm-title mb-0"><span class="samm-code-lg">{{ $report->reference }}</span></h1>
        <x-samm.status-badge :status="$case->status" />
        @if ($case->priority->isHigh())
            <span class="samm-badge samm-badge-urgent">{{ $case->priority->label() }}</span>
        @endif
    </div>

    <dl class="samm-case-header samm-card samm-section">
        <div><dt>{{ __('manage.cases.show.category') }}</dt><dd>{{ $report->category->name() }}</dd></div>
        <div><dt>{{ __('manage.cases.show.confidentiality') }}</dt><dd>{{ $report->confidentiality_level->label() }}</dd></div>
        <div><dt>{{ __('manage.cases.show.reporter') }}</dt><dd>{{ $report->displayName() }}</dd></div>
        <div><dt>{{ __('manage.cases.show.assignee') }}</dt><dd>{{ $case->assignee?->name ?? __('manage.cases.index.unassigned') }}</dd></div>
        <div><dt>{{ __('manage.cases.show.submitted_at') }}</dt><dd><time datetime="{{ $report->submitted_at?->toIso8601String() }}">{{ $report->submitted_at?->translatedFormat('d F Y, H:i') }}</time></dd></div>
        <div><dt>{{ __('manage.cases.show.last_activity') }}</dt><dd>{{ $case->last_activity_at?->translatedFormat('d F Y, H:i') ?? '-' }}</dd></div>
    </dl>

    @if ($report->immediate_danger)
        <div class="samm-alert samm-alert-urgent" role="alert">
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div>
                <b>{{ __('manage.cases.show.danger_title') }}</b>
                {{ __('manage.cases.show.danger_help') }}
            </div>
        </div>
    @endif

    @include('manage.cases.partials.actions')

    <x-samm.masked-identity :case="$case" :report="$report" :can-view="$canViewIdentity" :consent-granted="$identityConsentGranted" :revealed="$revealedIdentity" />

    <section class="samm-card samm-section" aria-labelledby="report-title">
        <h2 id="report-title" class="samm-heading">{{ __('manage.cases.show.report_title') }}</h2>
        <p class="samm-prose">{{ $report->description }}</p>
        <dl class="samm-summary">
            <div><dt>{{ __('report_form.description.facts_date') }}</dt><dd>{{ $report->facts_date?->translatedFormat('d F Y') ?? '-' }}</dd></div>
            <div><dt>{{ __('report_form.description.facts_period') }}</dt><dd>{{ $report->facts_period ?: '-' }}</dd></div>
            <div><dt>{{ __('report_form.description.location') }}</dt><dd>{{ $report->location ?: '-' }}</dd></div>
            <div><dt>{{ __('report_form.people.people_involved') }}</dt><dd class="samm-prose">{{ $report->people_involved ?: '-' }}</dd></div>
            <div><dt>{{ __('report_form.people.witnesses') }}</dt><dd class="samm-prose">{{ $report->witnesses ?: '-' }}</dd></div>
            <div><dt>{{ __('report_form.description.is_recurring') }}</dt><dd>{{ $report->is_recurring === null ? __('report_form.unknown') : ($report->is_recurring ? __('report_form.yes') : __('report_form.no')) }}</dd></div>
            <div><dt>{{ __('report_form.description.is_ongoing') }}</dt><dd>{{ $report->is_ongoing === null ? __('report_form.unknown') : ($report->is_ongoing ? __('report_form.yes') : __('report_form.no')) }}</dd></div>
        </dl>
    </section>

    <section class="samm-section" aria-labelledby="history-title">
        <h2 id="history-title" class="samm-heading">{{ __('manage.cases.show.history_title') }}</h2>
        @if ($histories->isEmpty())
            <p class="samm-muted">{{ __('manage.cases.show.history_empty') }}</p>
        @else
            <ol class="samm-timeline">
                @foreach ($histories as $history)
                    <li>
                        <div>
                            <b>{{ $history->to_status->label() }}</b>
                            @if ($history->from_status)
                                <span class="samm-muted">({{ __('manage.cases.show.history_from', ['status' => $history->from_status->label()]) }})</span>
                            @endif
                            <br>
                            <span class="samm-caption">{{ $history->created_at->translatedFormat('d M Y, H:i') }} · {{ $history->changedBy?->name ?? __('audit.actor_kinds.system') }}</span>
                            @if ($history->comment)
                                <p class="samm-prose mb-0 mt-1">{{ $history->comment }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    @include('manage.cases.partials.notes')

    <section class="samm-section" aria-labelledby="messages-title">
        <h2 id="messages-title" class="samm-heading">{{ __('manage.cases.show.messages_title') }}</h2>
        <x-samm.message-thread :messages="$messages" viewer="staff" :report="$report" />

        @if ($can['reply'])
            <form method="POST" action="{{ route('manage.cases.messages.store', $case) }}" novalidate class="samm-card mt-3">
                @csrf
                <label for="body" class="form-label">{{ __('manage.cases.reply.compose') }}</label>
                <p class="samm-caption">{{ __('manage.cases.reply.help') }}</p>
                <textarea id="body" name="body" rows="4" class="form-control @error('body') is-invalid @enderror" maxlength="5000" required>{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <button type="submit" class="btn btn-primary mt-2">{{ __('manage.cases.reply.submit') }}</button>
            </form>
        @endif
    </section>
@endsection
