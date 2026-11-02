{{-- Badge de statut (charte §12.7) : libellé toujours en toutes lettres, couleur verrouillée. En public, « Archivé » devient « Clôturé ». --}}
@props(['status', 'public' => false])
@php
    /** @var \App\Modules\Workflow\Enums\CaseStatus $status */
    $shown = $public ? $status->publicEquivalent() : $status;
@endphp
<span {{ $attributes->merge(['class' => 'samm-badge '.$shown->badgeClass()]) }}>{{ $shown->label() }}</span>
