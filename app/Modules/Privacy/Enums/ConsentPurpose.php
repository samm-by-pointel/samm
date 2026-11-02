<?php

namespace App\Modules\Privacy\Enums;

/**
 * Purposes a reporting person consents to separately (specification §6, step 5):
 * never grouped into a single checkbox.
 */
enum ConsentPurpose: string
{
    case Processing = 'processing';
    case Contact = 'contact';
    case IdentityDisclosure = 'identity_disclosure';

    public function label(): string
    {
        return __('reporting.consents.'.$this->value.'.label');
    }

    public function description(): string
    {
        return __('reporting.consents.'.$this->value.'.description');
    }
}
