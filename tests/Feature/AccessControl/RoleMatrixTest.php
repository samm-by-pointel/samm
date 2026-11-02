<?php

namespace Tests\Feature\AccessControl;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_each_system_role_holds_exactly_its_default_permissions(): void
    {
        foreach (RoleSeeder::matrix() as $key => $definition) {
            $role = Role::query()->where('key', $key)->firstOrFail();
            $expected = collect($definition['permissions'])->map(fn (Permission $permission): string => $permission->value)->sort()->values()->all();
            $granted = $role->grantedPermissions()->map(fn (Permission $permission): string => $permission->value)->sort()->values()->all();

            $this->assertSame($expected, $granted, "Permissions du rôle {$key}");
        }
    }

    public function test_technical_administrator_has_no_access_to_report_content(): void
    {
        $role = Role::query()->where('key', 'technical_admin')->firstOrFail();

        foreach (Permission::contentRelated() as $permission) {
            $this->assertFalse($role->hasPermission($permission), "L'administrateur technique ne doit pas avoir {$permission->value}");
        }

        $this->assertTrue($role->hasPermission(Permission::ManageSettings));
    }

    public function test_organization_administrator_has_no_case_access_by_default(): void
    {
        $role = Role::query()->where('key', 'organization_admin')->firstOrFail();

        foreach (Permission::contentRelated() as $permission) {
            $this->assertFalse($role->hasPermission($permission), "L'administrateur d'organisation ne doit pas avoir {$permission->value}");
        }

        $this->assertTrue($role->hasPermission(Permission::ManageUsers));
    }

    public function test_external_third_party_never_sees_identities_nor_the_case_list(): void
    {
        $role = Role::query()->where('key', 'external_third_party')->firstOrFail();

        $this->assertFalse($role->hasPermission(Permission::ViewIdentities));
        $this->assertFalse($role->hasPermission(Permission::ViewCaseList));
        $this->assertFalse($role->hasPermission(Permission::ViewUnassignedCases));
        $this->assertFalse($role->hasPermission(Permission::DownloadEvidence));
        $this->assertTrue($role->hasPermission(Permission::ViewAssignedCases));
    }

    public function test_case_manager_cannot_assign_nor_see_unassigned_cases_while_supervisor_can(): void
    {
        $manager = Role::query()->where('key', 'case_manager')->firstOrFail();
        $supervisor = Role::query()->where('key', 'supervisor')->firstOrFail();

        $this->assertFalse($manager->hasPermission(Permission::AssignCases));
        $this->assertFalse($manager->hasPermission(Permission::ViewUnassignedCases));
        $this->assertTrue($supervisor->hasPermission(Permission::AssignCases));
        $this->assertTrue($supervisor->hasPermission(Permission::ViewUnassignedCases));
    }

    public function test_auditor_only_reads(): void
    {
        $role = Role::query()->where('key', 'auditor')->firstOrFail();

        $this->assertSame(['analytics.view', 'audit.view'], $role->grantedPermissions()->map->value->sort()->values()->all());
    }

    public function test_every_permission_has_a_label_in_both_languages(): void
    {
        foreach (Permission::cases() as $permission) {
            $this->assertNotSame('access_control.permissions.'.$permission->value, __('access_control.permissions.'.$permission->value, [], 'fr'));
            $this->assertNotSame('access_control.permissions.'.$permission->value, __('access_control.permissions.'.$permission->value, [], 'en'));
        }
    }
}
