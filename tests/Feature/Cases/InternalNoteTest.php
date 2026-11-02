<?php

namespace Tests\Feature\Cases;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Models\TrackingCredential;
use App\Modules\Users\Models\User;
use Database\Factories\TrackingCredentialFactory;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InternalNoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);
    }

    public function test_note_is_created_encrypted_and_shown_to_the_team_but_never_to_the_person(): void
    {
        $manager = User::factory()->create(['name' => 'Moussa Sarr']);
        $manager->assignRole('case_manager');
        $case = CaseFile::factory()->assignedTo($manager)->create(['report_id' => Report::factory()->create(['reference' => 'SMM-7K4Q-92XD'])->id]);
        TrackingCredential::factory()->create(['report_id' => $case->report_id]);

        $this->actingAs($manager)->post(route('manage.cases.notes.store', $case), ['note' => 'Note interne fictive à vérifier'])
            ->assertRedirect(route('manage.cases.show', $case))
            ->assertSessionHas('status', 'Note interne ajoutée.');

        $this->assertStringNotContainsString('fictive', DB::table('internal_notes')->value('body'));
        $this->actingAs($manager)->get(route('manage.cases.show', $case))->assertSee('Note interne fictive à vérifier')->assertSee('Moussa Sarr');

        auth()->logout();
        $this->post(route('tracking.authenticate'), ['reference' => 'SMM-7K4Q-92XD', 'secret' => TrackingCredentialFactory::SECRET]);
        $this->get(route('tracking.show'))->assertOk()->assertDontSee('Note interne fictive');
    }

    public function test_note_requires_the_permission_and_a_body(): void
    {
        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        $manager = User::factory()->create();
        $manager->assignRole('case_manager');
        $case = CaseFile::factory()->assignedTo($manager)->create();

        $this->actingAs($auditor)->post(route('manage.cases.notes.store', $case), ['note' => 'Interdit'])->assertForbidden();
        $this->actingAs($manager)->from(route('manage.cases.show', $case))->post(route('manage.cases.notes.store', $case), ['note' => ''])
            ->assertSessionHasErrors(['note' => 'Le champ note est obligatoire.']);

        $this->assertDatabaseCount('internal_notes', 0);
    }
}
