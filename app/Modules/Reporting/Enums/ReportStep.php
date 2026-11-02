<?php

namespace App\Modules\Reporting\Enums;

use Illuminate\Validation\Rule;

/**
 * The steps of the public reporting form (specification §6), in order.
 *
 * Values are the French URL slugs. Adding or removing a step happens here only:
 * order, rules, view and label live together, and the Stepper adapts.
 */
enum ReportStep: string
{
    case Situation = 'situation';
    case Confidentiality = 'confidentialite';
    case Description = 'description';
    case People = 'personnes';
    case Attachments = 'pieces-jointes';
    case Consent = 'consentement';

    public static function first(): self
    {
        return self::cases()[0];
    }

    /**
     * Steps that have a screen: all of them.
     *
     * @return array<int, self>
     */
    public static function served(): array
    {
        return self::cases();
    }

    public function isServed(): bool
    {
        return in_array($this, self::served(), true);
    }

    /**
     * 1-based position in the form.
     */
    public function position(): int
    {
        return array_search($this, self::cases(), true) + 1;
    }

    public function next(): ?self
    {
        return self::cases()[$this->position()] ?? null;
    }

    public function previous(): ?self
    {
        return $this->position() > 1 ? self::cases()[$this->position() - 2] : null;
    }

    /**
     * @return array<int, self>
     */
    public function previousSteps(): array
    {
        return array_slice(self::cases(), 0, $this->position() - 1);
    }

    public function label(): string
    {
        return __('report_form.steps.'.$this->value);
    }

    /**
     * Validation rules of the step. Only the description is ever required:
     * nothing that could stop a person from reporting is mandatory (§6, step 3).
     *
     * @param  array<string, mixed>  $context  Earlier answers the rules depend on (e.g. the confidentiality level).
     * @return array<string, array<int, mixed>>
     */
    public function rules(array $context = []): array
    {
        $confidential = ($context['confidentiality_level'] ?? null) === ConfidentialityLevel::Confidential->value;

        return match ($this) {
            self::Situation => [
                'category' => ['required', 'string', Rule::exists('report_categories', 'uuid')->where('is_active', true)],
            ],
            self::Confidentiality => [
                'confidentiality_level' => ['required', Rule::enum(ConfidentialityLevel::class)],
                'pseudonym' => ['nullable', 'string', 'max:80', 'required_if:confidentiality_level,'.ConfidentialityLevel::Pseudonymous->value],
                'identity_full_name' => ['nullable', 'string', 'max:190', 'required_if:confidentiality_level,'.ConfidentialityLevel::Confidential->value],
                'identity_email' => ['nullable', 'email', 'max:190'],
                'identity_phone' => ['nullable', 'string', 'max:32'],
            ],
            self::Description => [
                'description' => ['required', 'string', 'max:20000'],
                'facts_date' => ['nullable', 'date', 'before_or_equal:today'],
                'facts_period' => ['nullable', 'string', 'max:500'],
                'location' => ['nullable', 'string', 'max:500'],
                'is_recurring' => ['nullable', 'boolean'],
                'is_ongoing' => ['nullable', 'boolean'],
                'immediate_danger' => ['nullable', 'boolean'],
            ],
            self::People => [
                'people_involved' => ['nullable', 'string', 'max:5000'],
                'witnesses' => ['nullable', 'string', 'max:5000'],
            ],
            self::Attachments => [],
            self::Consent => [
                'consent_processing' => ['accepted'],
                'consent_contact' => ['nullable', 'boolean'],
                'consent_identity_disclosure' => $confidential ? ['accepted'] : ['nullable', 'boolean'],
            ],
        };
    }

    /**
     * Custom messages where the generic ":other vaut :value" wording would be unclear.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return match ($this) {
            self::Confidentiality => [
                'pseudonym.required_if' => __('report_form.errors.pseudonym_required'),
                'identity_full_name.required_if' => __('report_form.errors.name_required'),
            ],
            self::Consent => [
                'consent_processing.accepted' => __('report_form.errors.processing_consent_required'),
                'consent_identity_disclosure.accepted' => __('report_form.errors.identity_consent_required'),
            ],
            default => [],
        };
    }

    /**
     * Keep only what the chosen options make meaningful, so the draft never
     * carries an identity typed then abandoned for anonymity.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function sanitize(array $validated): array
    {
        if ($this === self::Confidentiality) {
            $level = ConfidentialityLevel::from($validated['confidentiality_level']);

            if (! $level->requiresPseudonym()) {
                $validated['pseudonym'] = null;
            }

            if (! $level->allowsIdentity()) {
                $validated['identity_full_name'] = null;
                $validated['identity_email'] = null;
                $validated['identity_phone'] = null;
            }
        }

        if ($this === self::Description) {
            foreach (['is_recurring', 'is_ongoing', 'immediate_danger'] as $flag) {
                $validated[$flag] = isset($validated[$flag]) && $validated[$flag] !== null ? (bool) $validated[$flag] : null;
            }
        }

        if ($this === self::Attachments) {
            $validated = ['skipped' => true];
        }

        if ($this === self::Consent) {
            foreach (['consent_processing', 'consent_contact', 'consent_identity_disclosure'] as $flag) {
                $validated[$flag] = (bool) ($validated[$flag] ?? false);
            }
        }

        return $validated;
    }
}
