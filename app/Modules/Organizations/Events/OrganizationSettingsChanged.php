<?php

namespace App\Modules\Organizations\Events;

use App\Modules\Organizations\Models\Organization;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class OrganizationSettingsChanged
{
    use Dispatchable;

    /**
     * @param  array<int, string>  $changedFields
     */
    public function __construct(public readonly Organization $organization, public readonly array $changedFields, public readonly User $actor) {}
}
