<?php

namespace App\Modules\Reporting\Enums;

/**
 * The three confidentiality modes (specification §5), presented with equal
 * weight: none is "better" than another (charter §12.4).
 */
enum ConfidentialityLevel: string
{
    case Anonymous = 'anonymous';
    case Pseudonymous = 'pseudonymous';
    case Confidential = 'confidential';

    public function label(): string
    {
        return __('reporting.confidentiality.'.$this->value.'.label');
    }

    public function description(): string
    {
        return __('reporting.confidentiality.'.$this->value.'.description');
    }

    /**
     * Bootstrap Icons name from the charter (§6).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Anonymous => 'bi-incognito',
            self::Pseudonymous => 'bi-person-badge',
            self::Confidential => 'bi-shield-lock',
        };
    }

    public function requiresPseudonym(): bool
    {
        return $this === self::Pseudonymous;
    }

    public function allowsIdentity(): bool
    {
        return $this === self::Confidential;
    }
}
