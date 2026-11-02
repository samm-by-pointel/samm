<?php

namespace App\Modules\Organizations\Events;

use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class EmergencyContactChanged
{
    use Dispatchable;

    /**
     * @param  'created'|'updated'|'deleted'  $operation
     */
    public function __construct(public readonly EmergencyContact $contact, public readonly string $operation, public readonly User $actor) {}
}
