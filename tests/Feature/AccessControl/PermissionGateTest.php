<?php

namespace Tests\Feature\AccessControl;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'can:'.Permission::ViewAuditLog->value])
            ->get('/_test/journal', fn (): string => 'journal');
    }

    public function test_user_without_the_permission_is_refused_by_the_middleware(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::factory()->withPermissions(Permission::ViewAnalytics)->create());

        $this->actingAs($user)->get('/_test/journal')->assertForbidden();
    }

    public function test_user_with_the_permission_passes_the_middleware_and_the_gate(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::factory()->withPermissions(Permission::ViewAuditLog)->create());

        $this->actingAs($user)->get('/_test/journal')->assertOk()->assertSee('journal');
        $this->assertTrue($user->can(Permission::ViewAuditLog->value));
        $this->assertFalse($user->can(Permission::ManageUsers->value));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/_test/journal')->assertRedirect(route('login'));
        $this->assertFalse(Gate::forUser(null)->allows(Permission::ViewAuditLog->value));
    }

    public function test_permissions_of_several_roles_add_up(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::factory()->withPermissions(Permission::ViewAuditLog)->create());
        $user->assignRole(Role::factory()->withPermissions(Permission::ViewAnalytics)->create());

        $this->assertTrue($user->hasPermission(Permission::ViewAuditLog));
        $this->assertTrue($user->hasPermission(Permission::ViewAnalytics));
        $this->assertCount(2, $user->permissionSet());
        $this->assertFalse($user->hasPermission(Permission::ViewIdentities));
    }

    public function test_revoked_permission_and_removed_role_are_no_longer_granted(): void
    {
        $role = Role::factory()->withPermissions(Permission::ViewAuditLog, Permission::ViewAnalytics)->create();
        $user = User::factory()->create();
        $user->assignRole($role);

        $role->revoke(Permission::ViewAuditLog);
        $this->assertFalse($user->fresh()->hasPermission(Permission::ViewAuditLog));
        $this->assertTrue($user->fresh()->hasPermission(Permission::ViewAnalytics));

        $user->removeRole($role);
        $this->assertFalse($user->fresh()->hasPermission(Permission::ViewAnalytics));
        $this->assertFalse($user->fresh()->hasRole($role->key));
    }

    public function test_blade_can_directive_follows_the_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::factory()->withPermissions(Permission::ViewAuditLog)->create());
        $this->actingAs($user);

        $rendered = Blade::render("@can('audit.view') visible @else masqué @endcan | @can('users.manage') visible @else masqué @endcan");

        $this->assertSame('visible | masqué', trim(preg_replace('/\s+/', ' ', $rendered)));
    }
}
