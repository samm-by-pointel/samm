<?php

namespace Tests\Feature\Audit;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleChangesAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_assignment_and_removal_are_recorded_with_the_acting_administrator(): void
    {
        $administrator = User::factory()->create();
        $target = User::factory()->create();
        $role = Role::factory()->create(['key' => 'case_manager']);
        $this->actingAs($administrator);

        $target->assignRole($role);
        $target->assignRole($role);
        $target->removeRole($role);

        $assigned = AuditLog::query()->where('action', AuditAction::RoleAssigned->value)->get();
        $removed = AuditLog::query()->where('action', AuditAction::RoleRemoved->value)->sole();

        $this->assertCount(1, $assigned, 'Une attribution déjà effective ne produit pas de seconde entrée.');
        $this->assertTrue($assigned->first()->actor->is($administrator));
        $this->assertTrue($assigned->first()->subject->is($target));
        $this->assertSame('case_manager', $assigned->first()->context['role']);
        $this->assertTrue($removed->subject->is($target));
    }

    public function test_permission_changes_are_recorded_with_granted_and_revoked_values(): void
    {
        $role = Role::factory()->create(['key' => 'auditor']);

        $role->grant(Permission::ViewAuditLog, Permission::ViewAnalytics);
        $role->revoke(Permission::ViewAnalytics);
        $role->revoke(Permission::ViewAnalytics);

        $entries = AuditLog::query()->where('action', AuditAction::RolePermissionsChanged->value)->oldest('id')->get();

        $this->assertCount(2, $entries, 'Une révocation sans effet ne produit pas d\'entrée.');
        $this->assertSame(['audit.view', 'analytics.view'], $entries[0]->context['granted']);
        $this->assertSame([], $entries[0]->context['revoked']);
        $this->assertSame(['analytics.view'], $entries[1]->context['revoked']);
        $this->assertSame('auditor', $entries[1]->context['role']);
    }

    public function test_sync_records_only_the_difference(): void
    {
        $role = Role::factory()->withPermissions(Permission::ViewAuditLog, Permission::ViewAnalytics)->create();
        AuditLog::query()->toBase()->delete();

        $role->syncPermissions([Permission::ViewAnalytics, Permission::ManageUsers]);

        $entries = AuditLog::query()->where('action', AuditAction::RolePermissionsChanged->value)->oldest('id')->get();

        $this->assertCount(2, $entries);
        $this->assertSame(['audit.view'], $entries[0]->context['revoked']);
        $this->assertSame(['users.manage'], $entries[1]->context['granted']);
        $this->assertEqualsCanonicalizing(['analytics.view', 'users.manage'], $role->fresh()->grantedPermissions()->map->value->all());
    }
}
