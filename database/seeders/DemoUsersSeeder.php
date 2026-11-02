<?php

namespace Database\Seeders;

use App\Modules\Users\Models\User;
use Illuminate\Database\Seeder;

/**
 * Fictional demonstration accounts, one per useful role.
 *
 * Never called by DatabaseSeeder and never to be run in production: the
 * password is public. Run explicitly with:
 *   php artisan db:seed --class=DemoUsersSeeder
 *
 * Password for every account: Demonstration-2026!
 */
class DemoUsersSeeder extends Seeder
{
    public const PASSWORD = 'Demonstration-2026!';

    /**
     * @var array<int, array{name: string, email: string, role: string}>
     */
    public const ACCOUNTS = [
        ['name' => 'Aminata Ndiaye', 'email' => 'administration@demo.test', 'role' => 'organization_admin'],
        ['name' => 'Moussa Sarr', 'email' => 'gestionnaire@demo.test', 'role' => 'case_manager'],
        ['name' => 'Fatou Sy', 'email' => 'supervision@demo.test', 'role' => 'supervisor'],
        ['name' => 'Ibrahima Fall', 'email' => 'technique@demo.test', 'role' => 'technical_admin'],
    ];

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        foreach (self::ACCOUNTS as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => self::PASSWORD, 'is_active' => true],
            );

            $user->assignRole($account['role']);
        }
    }
}
