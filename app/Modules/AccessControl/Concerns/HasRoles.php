<?php

namespace App\Modules\AccessControl\Concerns;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Events\RoleAssigned;
use App\Modules\AccessControl\Events\RoleRemoved;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Roles and permissions of an internal account.
 *
 * Permissions are resolved from the account's roles and loaded once per model
 * instance; every check happens server side through this trait or the gates.
 */
trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(string $key): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains('key', $key);
    }

    public function hasPermission(Permission $permission): bool
    {
        return $this->permissionSet()->contains($permission);
    }

    /**
     * Every permission granted through the account's roles, without duplicates.
     *
     * @return Collection<int, Permission>
     */
    public function permissionSet(): Collection
    {
        $this->loadMissing('roles.permissions');

        return $this->roles
            ->flatMap(fn (Role $role): Collection => $role->grantedPermissions())
            ->unique(fn (Permission $permission): string => $permission->value)
            ->values();
    }

    public function assignRole(Role|string $role): void
    {
        $role = $this->resolveRole($role);
        $changes = $this->roles()->syncWithoutDetaching([$role->getKey()]);
        $this->unsetRelation('roles');

        if ($changes['attached'] !== []) {
            RoleAssigned::dispatch($this, $role);
        }
    }

    public function removeRole(Role|string $role): void
    {
        $role = $this->resolveRole($role);
        $detached = $this->roles()->detach($role->getKey());
        $this->unsetRelation('roles');

        if ($detached > 0) {
            RoleRemoved::dispatch($this, $role);
        }
    }

    private function resolveRole(Role|string $role): Role
    {
        return $role instanceof Role ? $role : Role::query()->where('key', $role)->firstOrFail();
    }
}
