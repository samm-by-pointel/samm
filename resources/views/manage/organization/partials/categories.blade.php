<section class="samm-section" aria-labelledby="categories-section">
    <h2 id="categories-section" class="samm-heading">{{ __('manage.organization.categories.title') }}</h2>
    <p class="samm-caption">{{ __('manage.organization.categories.help') }}</p>

    @if ($errors->categories->any())
        <div class="samm-alert samm-alert-urgent" role="alert">
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <div><b>{{ __('report_form.errors.title') }}</b>{{ $errors->categories->first() }}</div>
        </div>
    @endif

    @foreach ($categories as $category)
        <form method="POST" action="{{ route('manage.organization.categories.update', $category) }}" class="samm-card samm-inline-form mb-3" novalidate>
            @csrf
            @method('PUT')
            @foreach ($locales as $code => $language)
                <div>
                    <label for="category_{{ $category->uuid }}_name_{{ $code }}" class="form-label">{{ __('manage.organization.categories.name') }} ({{ $language }})</label>
                    <input id="category_{{ $category->uuid }}_name_{{ $code }}" name="name[{{ $code }}]" type="text" class="form-control" value="{{ $category->name[$code] ?? '' }}" maxlength="120" lang="{{ $code }}">
                </div>
            @endforeach
            @foreach ($locales as $code => $language)
                <div class="w-100">
                    <label for="category_{{ $category->uuid }}_description_{{ $code }}" class="form-label">{{ __('manage.organization.categories.description') }} ({{ $language }})</label>
                    <input id="category_{{ $category->uuid }}_description_{{ $code }}" name="description[{{ $code }}]" type="text" class="form-control" value="{{ $category->description[$code] ?? '' }}" maxlength="500" lang="{{ $code }}">
                </div>
            @endforeach
            <div>
                <label for="category_{{ $category->uuid }}_position" class="form-label">{{ __('manage.organization.categories.position') }}</label>
                <input id="category_{{ $category->uuid }}_position" name="position" type="number" class="form-control" value="{{ $category->position }}" min="0" max="999">
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="category_{{ $category->uuid }}_active" name="is_active" value="1" @checked($category->is_active)>
                <label class="form-check-label" for="category_{{ $category->uuid }}_active">{{ __('manage.organization.categories.active') }}</label>
            </div>
            <button type="submit" class="btn btn-outline-secondary">{{ __('manage.organization.categories.update') }}</button>
        </form>
    @endforeach

    <form method="POST" action="{{ route('manage.organization.categories.store') }}" class="samm-card samm-inline-form" novalidate>
        @csrf
        <h3 class="samm-label mb-0 w-100">{{ __('manage.organization.categories.add') }}</h3>
        @foreach ($locales as $code => $language)
            <div>
                <label for="new_category_name_{{ $code }}" class="form-label">{{ __('manage.organization.categories.name') }} ({{ $language }})</label>
                <input id="new_category_name_{{ $code }}" name="name[{{ $code }}]" type="text" class="form-control" value="{{ old('name.'.$code) }}" maxlength="120" lang="{{ $code }}">
            </div>
        @endforeach
        <div>
            <label for="new_category_position" class="form-label">{{ __('manage.organization.categories.position') }}</label>
            <input id="new_category_position" name="position" type="number" class="form-control" value="{{ old('position', $categories->count() + 1) }}" min="0" max="999">
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="new_category_active" name="is_active" value="1" checked>
            <label class="form-check-label" for="new_category_active">{{ __('manage.organization.categories.active') }}</label>
        </div>
        <button type="submit" class="btn btn-outline-secondary">{{ __('manage.organization.categories.submit') }}</button>
    </form>
</section>
