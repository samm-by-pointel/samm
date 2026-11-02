<?php

namespace App\Modules\Cases\Enums;

/**
 * Case priority, for the manager's space only: "High priority" is never shown
 * to the public (charter §12.7).
 */
enum CasePriority: string
{
    case Normal = 'normal';
    case High = 'high';

    public function label(): string
    {
        return __('cases.priorities.'.$this->value);
    }

    public function isHigh(): bool
    {
        return $this === self::High;
    }
}
