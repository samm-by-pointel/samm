<?php

namespace Tests\Feature\AccessControl;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\AccessControl\Models\RolePermission;
use App\Modules\Users\Models\User;
use Database\Seeders\DemoUsersSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_six_system_roles_and_is_idempotent(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertSame(6, Role::query()->count());
        $this->assertSame(6, Role::query()->where('is_system', true)->count());
        $this->assertSame(
            collect(RoleSeeder::matrix())->sum(fn (array $definition): int => count($definition['permissions'])),
            RolePermission::query()->count(),
        );
    }

    public function test_reseeding_does_not_overwrite_an_administrator_change(): void
    {
        $this->seed(RoleSeeder::class);
        Role::query()->where('key', 'auditor')->firstOrFail()->grant(Permission::ViewCaseList);

        $this->seed(RoleSeeder::class);

        $this->assertTrue(Role::query()->where('key', 'auditor')->firstOrFail()->hasPermission(Permission::ViewCaseList));
    }

    public function test_demo_users_seeder_creates_active_accounts_with_their_roles(): void
    {
        $this->seed(DemoUsersSeeder::class);
        $this->seed(DemoUsersSeeder::class);

        $this->assertSame(count(DemoUsersSeeder::ACCOUNTS), User::query()->count());

        foreach (DemoUsersSeeder::ACCOUNTS as $account) {
            $user = User::query()->where('email', $account['email'])->firstOrFail();

            $this->assertTrue($user->is_active);
            $this->assertTrue($user->hasRole($account['role']), "{$account['email']} doit avoir le rôle {$account['role']}");
            $this->assertCount(1, $user->roles);
        }
    }

    public function test_demo_manager_can_sign_in_with_the_demo_password(): void
    {
        $this->withoutVite();
        $this->seed(DemoUsersSeeder::class);

        $this->post(route('login'), ['email' => 'gestionnaire@demo.test', 'password' => DemoUsersSeeder::PASSWORD])
            ->assertRedirect('/gestion');

        $this->assertTrue(auth()->user()->hasPermission(Permission::ViewAssignedCases));
        $this->assertFalse(auth()->user()->hasPermission(Permission::AssignCases));
    }
}
