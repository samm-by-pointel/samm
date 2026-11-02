{{-- Progression du formulaire (charte §12.5) : nombre d'étapes variable, étape en cours annoncée, libellé mobile « Étape x sur y ». --}}
@props(['steps', 'current'])
@php
    /** @var array<int, \App\Modules\Reporting\Enums\ReportStep> $steps */
    /** @var \App\Modules\Reporting\Enums\ReportStep $current */
    $total = count($steps);
    $position = $current->position();
@endphp
<nav aria-label="{{ __('report_form.progress') }}" {{ $attributes }}>
    <p class="samm-label d-sm-none mb-2">{{ __('report_form.step_of', ['current' => $position, 'total' => $total, 'label' => $current->label()]) }}</p>
    <ol class="samm-steps">
        @foreach ($steps as $step)
            <li @class(['is-done' => $step->position() < $position, 'is-current' => $step === $current]) @if ($step === $current) aria-current="step" @endif>
                <span class="samm-steps-label">{{ $step->label() }}</span>
            </li>
        @endforeach
    </ol>
</nav>
