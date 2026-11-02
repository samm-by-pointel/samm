<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Events\RoleAssigned;
use App\Modules\AccessControl\Events\RolePermissionsChanged;
use App\Modules\AccessControl\Events\RoleRemoved;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Events\Dispatcher;

/**
 * Records every change to roles and permissions (specification §11.4).
 */
class RecordRoleChanges
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(RoleAssigned::class, [$this, 'handleAssigned']);
        $events->listen(RoleRemoved::class, [$this, 'handleRemoved']);
        $events->listen(RolePermissionsChanged::class, [$this, 'handlePermissionsChanged']);
    }

    public function handleAssigned(RoleAssigned $event): void
    {
        $this->audit->recordForCurrentUser(AuditAction::RoleAssigned, $event->user, ['role' => $event->role->key]);
    }

    public function handleRemoved(RoleRemoved $event): void
    {
        $this->audit->recordForCurrentUser(AuditAction::RoleRemoved, $event->user, ['role' => $event->role->key]);
    }

    public function handlePermissionsChanged(RolePermissionsChanged $event): void
    {
        $this->audit->recordForCurrentUser(AuditAction::RolePermissionsChanged, $event->role, [
            'role' => $event->role->key,
            'granted' => array_map(fn (Permission $permission): string => $permission->value, $event->granted),
            'revoked' => array_map(fn (Permission $permission): string => $permission->value, $event->revoked),
        ]);
    }
}
