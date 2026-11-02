@extends('public.report.layout', ['submitLabel' => __('report_form.attachments.continue_without')])

@section('intro')
    <p class="samm-body-lg">{{ __('report_form.attachments.lead') }}</p>
@endsection

@section('fields')
    <div class="samm-alert samm-alert-info" role="status">
        <i class="bi bi-paperclip" aria-hidden="true"></i>
        <div>
            <b>{{ __('report_form.attachments.soon_title') }}</b>
            {{ __('report_form.attachments.soon_help') }}
        </div>
    </div>

    <h2 class="samm-heading mt-4">{{ __('report_form.attachments.metadata_title') }}</h2>
    <p class="samm-muted mb-0">{{ __('report_form.attachments.metadata_help') }}</p>
@endsection
