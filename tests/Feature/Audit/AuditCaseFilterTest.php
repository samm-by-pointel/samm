<?php

namespace Tests\Feature\Audit;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Actions\ChangeCaseStatus;
use App\Modules\Workflow\Enums\CaseStatus;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditCaseFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_filter_lists_only_the_entries_of_that_case_and_its_report(): void
    {
        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);

        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');
        $case = CaseFile::factory()->create(['report_id' => Report::factory()->create(['reference' => 'SMM-7K4Q-92XD'])->id]);
        $otherCase = CaseFile::factory()->create();

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))->assertOk();
        $this->actingAs($supervisor)->get(route('manage.cases.show', $otherCase))->assertOk();
        app(ChangeCaseStatus::class)->handle($case, CaseStatus::InProgress, $supervisor);
        app(ChangeCaseStatus::class)->handle($otherCase, CaseStatus::Closed, $supervisor, 'Motif fictif');

        $response = $this->actingAs($supervisor)->get(route('manage.audit.index', ['case' => $case->uuid]))
            ->assertOk()
            ->assertSee('Entrées du dossier SMM-7K4Q-92XD')
            ->assertSee(route('manage.cases.show', $case));

        $entries = $response->viewData('entries');
        $this->assertCount(2, $entries, 'Une consultation et un changement de statut.');
        foreach ($entries as $entry) {
            $this->assertTrue($entry->subject->is($case));
        }

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))->assertSee(route('manage.audit.index', ['case' => $case->uuid]));
        $this->actingAs($supervisor)->get(route('manage.audit.index', ['case' => 'inconnu']))->assertSessionHasErrors('case');
    }
}
