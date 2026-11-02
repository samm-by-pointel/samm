<?php

namespace App\Modules\Organizations\Rules;

use App\Modules\Organizations\Services\ContrastChecker;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A brand color must be a 6-digit hexadecimal value with enough contrast;
 * otherwise the nearest accessible shade is suggested.
 */
class AccessibleBrandColor implements ValidationRule
{
    public function __construct(private readonly ContrastChecker $checker = new ContrastChecker) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->checker->isHexColor($value)) {
            $fail(__('manage.organization.errors.color_format'));

            return;
        }

        if (! $this->checker->isAccessible($value)) {
            $fail(__('manage.organization.errors.color_contrast', [
                'ratio' => ContrastChecker::MIN_RATIO,
                'suggestion' => $this->checker->nearestAccessible($value) ?? '-',
            ]));
        }
    }
}
