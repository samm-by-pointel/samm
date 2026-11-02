<?php

namespace App\Modules\AccessControl\Models;

use App\Modules\AccessControl\Enums\Permission;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One permission granted to one role.
 *
 * @property int $role_id
 * @property Permission $permission
 */
#[Table('permission_role')]
#[WithoutTimestamps]
#[Fillable(['role_id', 'permission'])]
class RolePermission extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permission' => Permission::class,
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
