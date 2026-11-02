<?php

namespace Tests\Feature\Cases;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasePriorityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);
    }

    public function test_priority_change_is_recorded_and_shown_to_the_team_only(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('case_manager');
        $case = CaseFile::factory()->assignedTo($manager)->create();

        $this->actingAs($manager)->post(route('manage.cases.priority.store', $case), ['priority' => 'high'])
            ->assertRedirect(route('manage.cases.show', $case))
            ->assertSessionHas('status', 'Priorité : Priorité haute.');

        $this->assertSame(CasePriority::High, $case->fresh()->priority);
        $entry = AuditLog::query()->where('action', AuditAction::CasePriorityChanged->value)->sole();
        $this->assertEquals(['from' => 'normal', 'to' => 'high'], $entry->context);

        $this->actingAs($manager)->get(route('manage.cases.show', $case))->assertSee('Priorité haute');

        $this->actingAs($manager)->post(route('manage.cases.priority.store', $case), ['priority' => 'high'])->assertRedirect();
        $this->assertSame(1, AuditLog::query()->where('action', AuditAction::CasePriorityChanged->value)->count(), 'Sans changement, pas d\'entrée.');
    }

    public function test_auditor_cannot_change_priority(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        $case = CaseFile::factory()->create();

        $this->actingAs($auditor)->post(route('manage.cases.priority.store', $case), ['priority' => 'high'])->assertForbidden();
    }
}
