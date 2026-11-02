<?php

namespace Tests\Feature\Cases;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);

        $this->supervisor = User::factory()->create(['name' => 'Fatou Sy']);
        $this->supervisor->assignRole('supervisor');
        $this->manager = User::factory()->create(['name' => 'Moussa Sarr']);
        $this->manager->assignRole('case_manager');
    }

    public function test_supervisor_assigns_reassigns_and_unassigns_with_audit(): void
    {
        $case = CaseFile::factory()->create();
        $other = User::factory()->create(['name' => 'Autre Gestionnaire']);
        $other->assignRole('case_manager');

        $this->actingAs($this->supervisor)->post(route('manage.cases.assignment.store', $case), ['assignee' => $this->manager->uuid])
            ->assertRedirect(route('manage.cases.show', $case))
            ->assertSessionHas('status', 'Dossier affecté à Moussa Sarr.');
        $this->assertTrue($case->fresh()->isAssignedTo($this->manager));
        $this->assertNotNull($case->fresh()->assigned_at);

        $this->actingAs($this->supervisor)->post(route('manage.cases.assignment.store', $case), ['assignee' => $other->uuid])->assertRedirect();
        $this->assertTrue($case->fresh()->isAssignedTo($other));

        $this->actingAs($this->supervisor)->post(route('manage.cases.assignment.store', $case), ['assignee' => ''])->assertRedirect();
        $this->assertFalse($case->fresh()->isAssigned());

        $this->assertSame(1, AuditLog::query()->where('action', AuditAction::CaseAssigned->value)->count());
        $reassigned = AuditLog::query()->where('action', AuditAction::CaseReassigned->value)->sole();
        $this->assertEquals(['from' => $this->manager->uuid, 'to' => $other->uuid], $reassigned->context);
        $this->assertTrue($reassigned->actor->is($this->supervisor));
        $this->assertSame(1, AuditLog::query()->where('action', AuditAction::CaseUnassigned->value)->count());
    }

    public function test_manager_cannot_assign_and_ineligible_accounts_are_refused(): void
    {
        $case = CaseFile::factory()->assignedTo($this->manager)->create();

        $this->actingAs($this->manager)->post(route('manage.cases.assignment.store', $case), ['assignee' => $this->supervisor->uuid])->assertForbidden();

        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        $this->actingAs($this->supervisor)->from(route('manage.cases.show', $case))
            ->post(route('manage.cases.assignment.store', $case), ['assignee' => $auditor->uuid])
            ->assertSessionHasErrors('assignee');

        $inactive = User::factory()->inactive()->create();
        $inactive->assignRole('case_manager');
        $this->actingAs($this->supervisor)->post(route('manage.cases.assignment.store', $case), ['assignee' => $inactive->uuid])
            ->assertSessionHasErrors('assignee');

        $this->assertTrue($case->fresh()->isAssignedTo($this->manager));
    }

    public function test_external_third_party_sees_the_case_once_assigned(): void
    {
        $third = User::factory()->create();
        $third->assignRole('external_third_party');
        $case = CaseFile::factory()->create();

        $this->actingAs($third)->get(route('manage.cases.show', $case))->assertForbidden();

        $this->actingAs($this->supervisor)->post(route('manage.cases.assignment.store', $case), ['assignee' => $third->uuid])->assertRedirect();

        $this->actingAs($third)->get(route('manage.cases.show', $case))->assertOk()->assertDontSee('Affecter à');
        $this->actingAs($this->supervisor)->get(route('manage.cases.show', $case))->assertSee($third->name);
    }
}
