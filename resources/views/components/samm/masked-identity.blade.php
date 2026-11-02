{{-- Identité de la personne qui signale (charte §12.10) : masquée par défaut, révélée pour une seule réponse après un motif, accès journalisé. --}}
@props(['case', 'report', 'canView', 'consentGranted', 'revealed' => null])
<section class="samm-id" aria-labelledby="identity-title">
    <div>
        <b id="identity-title">{{ __('manage.cases.identity.title') }}</b>
        @if (! $report->confidentiality_level->allowsIdentity())
            <span class="samm-muted">{{ __('manage.cases.identity.none', ['level' => $report->confidentiality_level->label()]) }}</span>
        @elseif ($revealed)
            <dl class="samm-summary mt-2 mb-0">
                <div><dt>{{ __('manage.cases.identity.full_name') }}</dt><dd>{{ $revealed->full_name }}</dd></div>
                <div><dt>{{ __('manage.cases.identity.email') }}</dt><dd>{{ $revealed->email ?: '-' }}</dd></div>
                <div><dt>{{ __('manage.cases.identity.phone') }}</dt><dd>{{ $revealed->phone ?: '-' }}</dd></div>
            </dl>
            <small>{{ __('manage.cases.identity.revealed_help') }}</small>
        @else
            <span class="samm-mask" aria-label="{{ __('manage.cases.identity.masked') }}">•••••• ••••••</span>
            <small>{{ __('manage.cases.identity.confidential_help') }}</small>
        @endif
    </div>

    @if ($report->confidentiality_level->allowsIdentity() && ! $revealed)
        @if ($canView)
            <details class="samm-reveal">
                <summary class="btn btn-outline-secondary">
                    <i class="bi bi-eye" aria-hidden="true"></i> {{ __('manage.cases.identity.reveal') }}
                </summary>
                <form method="POST" action="{{ route('manage.cases.identity.reveal', $case) }}" class="mt-3" novalidate>
                    @csrf
                    <label for="reason" class="form-label">{{ __('manage.cases.identity.reason') }}</label>
                    <p class="samm-caption">{{ __('manage.cases.identity.reason_help') }}</p>
                    <textarea id="reason" name="reason" rows="2" class="form-control @error('reason') is-invalid @enderror" minlength="10" maxlength="190" required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-outline-secondary mt-3">{{ __('manage.cases.identity.confirm') }}</button>
                </form>
            </details>
        @elseif (! $consentGranted)
            <span class="samm-caption">{{ __('manage.cases.identity.no_consent') }}</span>
        @else
            <span class="samm-caption">{{ __('manage.cases.identity.forbidden') }}</span>
        @endif
    @endif
</section>
