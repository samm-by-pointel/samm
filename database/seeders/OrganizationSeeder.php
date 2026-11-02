<?php

namespace Database\Seeders;

use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Create the single organization of this installation if it does not exist yet.
     * Fictional data only; the administrator replaces it from the settings screen.
     */
    public function run(): void
    {
        if (Organization::query()->exists()) {
            return;
        }

        Organization::query()->create([
            'name' => 'Organisation de démonstration',
            'service_name' => null,
            'default_locale' => 'fr',
        ]);
    }
}
