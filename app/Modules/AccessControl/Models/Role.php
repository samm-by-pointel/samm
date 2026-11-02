<?php

namespace App\Modules\AccessControl\Models;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Events\RolePermissionsChanged;
use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Core\Concerns\HasTranslations;
use App\Modules\Users\Models\User;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A role is a named set of permissions, never an absolute authorization.
 *
 * @property string $key
 * @property array<string, string> $name
 * @property array<string, string>|null $description
 * @property bool $is_system
 */
#[Fillable(['key', 'name', 'description', 'is_system'])]
#[UseFactory(RoleFactory::class)]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory, HasPublicIdentifier, HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function name(): ?string
    {
        return $this->translate('name');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /**
     * @return Collection<int, Permission>
     */
    public function grantedPermissions(): Collection
    {
        return $this->permissions->pluck('permission');
    }

    public function hasPermission(Permission $permission): bool
    {
        return $this->grantedPermissions()->contains($permission);
    }

    public function grant(Permission ...$permissions): void
    {
        $granted = [];

        foreach ($permissions as $permission) {
            if (! $this->permissions()->where('permission', $permission->value)->exists()) {
                $this->permissions()->create(['permission' => $permission->value]);
                $granted[] = $permission;
            }
        }

        $this->unsetRelation('permissions');

        if ($granted !== []) {
            RolePermissionsChanged::dispatch($this, $granted, []);
        }
    }

    public function revoke(Permission ...$permissions): void
    {
        $revoked = array_values(array_filter(
            $permissions,
            fn (Permission $permission): bool => $this->permissions()->where('permission', $permission->value)->exists(),
        ));

        $this->permissions()
            ->whereIn('permission', array_map(fn (Permission $permission): string => $permission->value, $revoked))
            ->delete();

        $this->unsetRelation('permissions');

        if ($revoked !== []) {
            RolePermissionsChanged::dispatch($this, [], $revoked);
        }
    }

    /**
     * Replace the whole permission set of this role.
     *
     * @param  array<int, Permission>  $permissions
     */
    public function syncPermissions(array $permissions): void
    {
        $current = $this->grantedPermissions()->all();
        $toRevoke = array_values(array_filter($current, fn (Permission $permission): bool => ! in_array($permission, $permissions, true)));
        $toGrant = array_values(array_filter($permissions, fn (Permission $permission): bool => ! in_array($permission, $current, true)));

        $this->revoke(...$toRevoke);
        $this->grant(...$toGrant);
    }
}
