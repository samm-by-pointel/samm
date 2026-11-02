<?php

namespace Database\Seeders;

use App\Modules\Organizations\Models\EmergencyContact;
use Illuminate\Database\Seeder;

class EmergencyContactSeeder extends Seeder
{
    /**
     * Default emergency contact: Police, 17. No other number is invented here;
     * the organization administrator adds, edits and orders them.
     */
    public function run(): void
    {
        if (EmergencyContact::query()->exists()) {
            return;
        }

        EmergencyContact::query()->create([
            'label' => ['fr' => 'Police', 'en' => 'Police'],
            'phone_number' => '17',
            'position' => 1,
            'is_active' => true,
        ]);
    }
}
