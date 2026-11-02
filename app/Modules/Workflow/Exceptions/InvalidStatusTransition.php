<?php

namespace App\Modules\Workflow\Exceptions;

use App\Modules\Workflow\Enums\CaseStatus;
use DomainException;

class InvalidStatusTransition extends DomainException
{
    public static function between(CaseStatus $from, CaseStatus $to): self
    {
        return new self("A case cannot go from [{$from->value}] to [{$to->value}].");
    }
}
