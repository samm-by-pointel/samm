{{-- Fil de messages (charte §12.8) : équipe à gauche, personne à droite. Côté public, l'équipe est signée par son rôle, jamais par un nom. --}}
@props(['messages', 'viewer' => 'public', 'report' => null])
@if ($messages->isEmpty())
    <p class="samm-muted">{{ __('tracking.thread.empty') }}</p>
@else
    <div class="samm-thread px-0" role="log" aria-label="{{ __('tracking.thread.label') }}">
        @foreach ($messages as $message)
            <div @class(['samm-msg', 'samm-msg-me' => $message->isFromReporter(), 'samm-msg-them' => ! $message->isFromReporter()])>
                {!! nl2br(e($message->body)) !!}
                <small>{{ $viewer === 'staff' && $report ? $message->author_type->staffLabel($report) : $message->author_type->publicLabel() }} · <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->translatedFormat('d M, H:i') }}</time></small>
            </div>
        @endforeach
    </div>
@endif
