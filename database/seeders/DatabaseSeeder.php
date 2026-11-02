<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with fictional data only.
     * Demonstration accounts are seeded separately (DemoUsersSeeder), never automatically.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OrganizationSeeder::class,
            EmergencyContactSeeder::class,
            ReportCategorySeeder::class,
        ]);
    }
}
