<?php

namespace Tests\Feature\Cases;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Models\TrackingCredential;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use Database\Factories\TrackingCredentialFactory;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseStatusChangeTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private CaseFile $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('case_manager');
        $this->case = CaseFile::factory()->assignedTo($this->manager)->create(['report_id' => Report::factory()->create(['reference' => 'SMM-7K4Q-92XD'])->id]);
        TrackingCredential::factory()->create(['report_id' => $this->case->report_id]);
    }

    public function test_allowed_transition_from_the_page_updates_history_and_audit(): void
    {
        $this->actingAs($this->manager)->post(route('manage.cases.status.store', $this->case), ['status' => 'in_progress', 'comment' => 'Prise en charge fictive'])
            ->assertRedirect(route('manage.cases.show', $this->case))
            ->assertSessionHas('status', 'Statut changé en « En cours de traitement ».');

        $this->assertSame(CaseStatus::InProgress, $this->case->fresh()->status);
        $history = $this->case->statusHistories()->sole();
        $this->assertSame('Prise en charge fictive', $history->comment);
        $this->assertTrue($history->changedBy->is($this->manager));
        $this->assertSame(1, AuditLog::query()->where('action', AuditAction::CaseStatusChanged->value)->count());
    }

    public function test_forbidden_transition_and_missing_closure_reason_are_refused(): void
    {
        $this->actingAs($this->manager)->from(route('manage.cases.show', $this->case))
            ->post(route('manage.cases.status.store', $this->case), ['status' => 'archived'])
            ->assertSessionHasErrors(['status' => 'Ce changement de statut n\'est pas autorisé depuis le statut actuel.']);

        $this->actingAs($this->manager)->from(route('manage.cases.show', $this->case))
            ->post(route('manage.cases.status.store', $this->case), ['status' => 'closed', 'comment' => 'court'])
            ->assertSessionHasErrors(['comment' => 'Indiquez le motif de clôture (dix caractères au minimum).']);

        $this->assertSame(CaseStatus::New, $this->case->fresh()->status);
        $this->assertSame(0, $this->case->statusHistories()->count());
    }

    public function test_closing_requires_the_closing_permission(): void
    {
        $third = User::factory()->create();
        $third->assignRole('external_third_party');
        $case = CaseFile::factory()->assignedTo($third)->create();

        $this->actingAs($third)->post(route('manage.cases.status.store', $case), ['status' => 'in_progress'])->assertRedirect();
        $this->actingAs($third)->post(route('manage.cases.status.store', $case), ['status' => 'closed', 'comment' => 'Motif de clôture fictif'])->assertForbidden();

        $this->assertSame(CaseStatus::InProgress, $case->fresh()->status);
    }

    public function test_closed_case_can_be_reopened_and_the_person_sees_the_public_status(): void
    {
        $this->actingAs($this->manager)->post(route('manage.cases.status.store', $this->case), ['status' => 'closed', 'comment' => 'Motif de clôture fictif']);
        $this->assertSame(CaseStatus::Closed, $this->case->fresh()->status);
        $this->assertSame('Motif de clôture fictif', $this->case->fresh()->closure_reason);

        $this->actingAs($this->manager)->get(route('manage.cases.show', $this->case))->assertSee('Clôturé')->assertSee('Motif de clôture fictif');

        $this->actingAs($this->manager)->post(route('manage.cases.status.store', $this->case), ['status' => 'in_progress'])->assertRedirect();
        $this->assertTrue($this->case->fresh()->status->isOpen());

        auth()->logout();
        $this->post(route('tracking.authenticate'), ['reference' => 'SMM-7K4Q-92XD', 'secret' => TrackingCredentialFactory::SECRET]);
        $this->get(route('tracking.show'))->assertOk()->assertSee('En cours de traitement')->assertDontSee('Motif de clôture fictif');
    }
}
