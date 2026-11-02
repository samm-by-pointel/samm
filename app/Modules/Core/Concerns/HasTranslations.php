<?php

namespace App\Modules\Core\Concerns;

/**
 * Attributes stored as a JSON object keyed by locale, e.g. {"fr": "…", "en": "…"}.
 *
 * `translate()` returns the value for the current locale, then for each fallback
 * locale, then the first non-empty value, so a missing translation never leaves
 * an empty label on screen.
 */
trait HasTranslations
{
    public function translate(string $attribute, ?string $locale = null): ?string
    {
        $values = $this->getAttribute($attribute);

        if (is_string($values)) {
            return $values;
        }

        if (! is_array($values)) {
            return null;
        }

        $candidates = array_unique(array_filter([
            $locale ?? app()->getLocale(),
            ...$this->translationFallbackLocales(),
        ]));

        foreach ($candidates as $candidate) {
            if (isset($values[$candidate]) && $values[$candidate] !== '') {
                return $values[$candidate];
            }
        }

        foreach ($values as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Locales tried after the current one. Models aware of the organization may add its default locale first.
     *
     * @return array<int, string>
     */
    protected function translationFallbackLocales(): array
    {
        return array_values(array_filter([config('app.fallback_locale'), 'fr']));
    }
}
