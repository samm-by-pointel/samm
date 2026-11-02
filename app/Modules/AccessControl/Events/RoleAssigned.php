<?php

namespace App\Modules\AccessControl\Events;

use App\Modules\AccessControl\Models\Role;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class RoleAssigned
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly Role $role,
    ) {}
}
