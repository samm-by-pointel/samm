{{-- Écran de confirmation (charte §12.6) : identifiant et secret affichés une seule fois, copie et téléchargement, jamais d'e-mail. --}}
@props(['reference', 'secret'])
@php
    $download = __('report_form.confirmation.download_content', ['reference' => $reference, 'secret' => $secret, 'url' => route('tracking.login')]);
@endphp
<section class="samm-code" aria-labelledby="samm-code-title">
    <h2 id="samm-code-title">{{ __('report_form.confirmation.codes_title') }}</h2>
    <p>{{ __('report_form.confirmation.codes_help') }}</p>
    <dl>
        <div>
            <dt>{{ __('report_form.confirmation.reference') }}</dt>
            <dd id="samm-reference">{{ $reference }}</dd>
        </div>
        <div>
            <dt>{{ __('report_form.confirmation.secret') }}</dt>
            <dd id="samm-secret">{{ $secret }}</dd>
        </div>
    </dl>
    <pre id="samm-codes-text" class="visually-hidden">{{ $download }}</pre>
    <div class="samm-row">
        <button type="button" class="btn btn-primary" data-copy-target="#samm-codes-text" data-copied-label="{{ __('report_form.confirmation.copied') }}">
            <i class="bi bi-copy" aria-hidden="true"></i> {{ __('report_form.confirmation.copy') }}
        </button>
        <a class="btn btn-outline-secondary" download="samm-codes.txt" href="data:text/plain;charset=utf-8,{{ rawurlencode($download) }}">
            <i class="bi bi-download" aria-hidden="true"></i> {{ __('report_form.confirmation.download') }}
        </a>
    </div>
    <p class="samm-caption mt-3 mb-0" role="status" data-copy-status></p>
</section>
