<?php

namespace Tests\Feature\Organizations;

use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Reporting\Models\ReportCategory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_seed_creates_one_organization_one_emergency_contact_and_eight_categories(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, Organization::query()->count());
        $this->assertSame('Organisation de démonstration', Organization::query()->first()->name);

        $contacts = EmergencyContact::query()->get();
        $this->assertCount(1, $contacts);
        $this->assertSame('Police', $contacts->first()->translate('label', 'fr'));
        $this->assertSame('17', $contacts->first()->phone_number);

        $this->assertSame(8, ReportCategory::query()->count());
    }

    public function test_seeding_twice_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, Organization::query()->count());
        $this->assertSame(1, EmergencyContact::query()->count());
        $this->assertSame(8, ReportCategory::query()->count());
    }
}
