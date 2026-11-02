<?php

namespace App\Modules\AccessControl\Events;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;

class RolePermissionsChanged
{
    use Dispatchable;

    /**
     * @param  array<int, Permission>  $granted
     * @param  array<int, Permission>  $revoked
     */
    public function __construct(
        public readonly Role $role,
        public readonly array $granted = [],
        public readonly array $revoked = [],
    ) {}
}
