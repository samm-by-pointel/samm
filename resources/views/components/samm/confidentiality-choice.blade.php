{{-- Niveau de confidentialité (charte §12.4) : trois cartes radio de même poids, aucune n'est « meilleure ». --}}
@props(['levels', 'selected' => null, 'name' => 'confidentiality_level'])
<div class="samm-choices px-0" role="radiogroup" aria-labelledby="{{ $name }}-label">
    <span id="{{ $name }}-label" class="samm-label">{{ __('report_form.confidentiality.legend') }}</span>
    @foreach ($levels as $level)
        <label @class(['samm-choice', 'is-selected' => $selected === $level->value])>
            <input type="radio" name="{{ $name }}" value="{{ $level->value }}" @checked($selected === $level->value) required>
            <i class="bi {{ $level->icon() }}" aria-hidden="true"></i>
            <div>
                <b>{{ $level->label() }}</b>
                <span>{{ $level->description() }}</span>
            </div>
        </label>
    @endforeach
</div>
