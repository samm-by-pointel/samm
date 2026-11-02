{{-- Bloc « En cas de danger immédiat » : numéros configurés par l'organisation, fournis par un composeur de vue. Rien n'est affiché sans contact. --}}
@php
    /** @var \Illuminate\Support\Collection<int, \App\Modules\Organizations\Models\EmergencyContact> $contacts */
    $contacts ??= collect();
@endphp
@if ($contacts->isNotEmpty())
    <section class="samm-card samm-emergency" aria-labelledby="samm-emergency-title">
        <h2 id="samm-emergency-title" class="samm-heading">
            <i class="bi bi-telephone" aria-hidden="true"></i>
            {{ __('public.emergency.title') }}
        </h2>
        <p class="samm-muted">{{ __('public.emergency.lead') }}</p>
        <ul class="samm-emergency-list">
            @foreach ($contacts as $contact)
                <li>
                    <a class="samm-emergency-link" href="tel:{{ $contact->dialable() }}">
                        <span class="samm-emergency-label">{{ $contact->label() }}</span>
                        <span class="samm-code-lg">{{ $contact->phone_number }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
