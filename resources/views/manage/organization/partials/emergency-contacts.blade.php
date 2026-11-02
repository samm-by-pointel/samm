<section class="samm-section" aria-labelledby="emergency-section">
    <h2 id="emergency-section" class="samm-heading">{{ __('manage.organization.emergency.title') }}</h2>
    <p class="samm-caption">{{ __('manage.organization.emergency.help') }}</p>

    @if ($errors->emergency->any())
        <div class="samm-alert samm-alert-urgent" role="alert">
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div><b>{{ __('report_form.errors.title') }}</b>{{ $errors->emergency->first() }}</div>
        </div>
    @endif

    @foreach ($contacts as $contact)
        <form method="POST" action="{{ route('manage.organization.emergency.update', $contact) }}" class="samm-card samm-inline-form mb-3" novalidate>
            @csrf
            @method('PUT')
            @foreach ($locales as $code => $language)
                <div>
                    <label for="contact_{{ $contact->uuid }}_label_{{ $code }}" class="form-label">{{ __('manage.organization.emergency.label') }} ({{ $language }})</label>
                    <input id="contact_{{ $contact->uuid }}_label_{{ $code }}" name="label[{{ $code }}]" type="text" class="form-control" value="{{ $contact->label[$code] ?? '' }}" maxlength="80" lang="{{ $code }}">
                </div>
            @endforeach
            <div>
                <label for="contact_{{ $contact->uuid }}_phone" class="form-label">{{ __('manage.organization.emergency.phone_number') }}</label>
                <input id="contact_{{ $contact->uuid }}_phone" name="phone_number" type="text" class="form-control samm-code-sm" value="{{ $contact->phone_number }}" maxlength="32" required>
            </div>
            <div>
                <label for="contact_{{ $contact->uuid }}_position" class="form-label">{{ __('manage.organization.emergency.position') }}</label>
                <input id="contact_{{ $contact->uuid }}_position" name="position" type="number" class="form-control" value="{{ $contact->position }}" min="0" max="999">
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="contact_{{ $contact->uuid }}_active" name="is_active" value="1" @checked($contact->is_active)>
                <label class="form-check-label" for="contact_{{ $contact->uuid }}_active">{{ __('manage.organization.emergency.active') }}</label>
            </div>
            <div class="samm-row">
                <button type="submit" class="btn btn-outline-secondary">{{ __('manage.organization.emergency.update') }}</button>
                <button type="submit" class="btn btn-link samm-text-urgent" form="delete_contact_{{ $contact->uuid }}">{{ __('manage.organization.emergency.delete') }}</button>
            </div>
        </form>
        <form id="delete_contact_{{ $contact->uuid }}" method="POST" action="{{ route('manage.organization.emergency.destroy', $contact) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <form method="POST" action="{{ route('manage.organization.emergency.store') }}" class="samm-card samm-inline-form" novalidate>
        @csrf
        <h3 class="samm-label mb-0 w-100">{{ __('manage.organization.emergency.add') }}</h3>
        @foreach ($locales as $code => $language)
            <div>
                <label for="new_contact_label_{{ $code }}" class="form-label">{{ __('manage.organization.emergency.label') }} ({{ $language }})</label>
                <input id="new_contact_label_{{ $code }}" name="label[{{ $code }}]" type="text" class="form-control" value="{{ old('label.'.$code) }}" maxlength="80" lang="{{ $code }}">
            </div>
        @endforeach
        <div>
            <label for="new_contact_phone" class="form-label">{{ __('manage.organization.emergency.phone_number') }}</label>
            <input id="new_contact_phone" name="phone_number" type="text" class="form-control samm-code-sm" value="{{ old('phone_number') }}" maxlength="32" required>
        </div>
        <div>
            <label for="new_contact_position" class="form-label">{{ __('manage.organization.emergency.position') }}</label>
            <input id="new_contact_position" name="position" type="number" class="form-control" value="{{ old('position', $contacts->count() + 1) }}" min="0" max="999">
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="new_contact_active" name="is_active" value="1" checked>
            <label class="form-check-label" for="new_contact_active">{{ __('manage.organization.emergency.active') }}</label>
        </div>
        <button type="submit" class="btn btn-outline-secondary">{{ __('manage.organization.emergency.submit') }}</button>
    </form>
</section>
