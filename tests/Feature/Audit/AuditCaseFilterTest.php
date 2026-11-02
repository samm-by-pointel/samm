<?php

namespace Tests\Feature\Audit;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Actions\ChangeCaseStatus;
use App\Modules\Workflow\Enums\CaseStatus;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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

        $this->actingAs($supervisor)->get(route('manage.audit.index', ['case' => $case->uuid]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filteredCase.reference', 'SMM-7K4Q-92XD')
                ->where('filteredCase.url', route('manage.cases.show', $case))
                ->has('entries.data', 2)
                ->where('entries.data.0.subject.label', 'SMM-7K4Q-92XD')
                ->where('entries.data.1.subject.label', 'SMM-7K4Q-92XD'));

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))
            ->assertInertia(fn (Assert $page) => $page->where('urls.audit', route('manage.audit.index', ['case' => $case->uuid])));
        $this->actingAs($supervisor)->get(route('manage.audit.index', ['case' => 'inconnu']))->assertSessionHasErrors('case');
    }
}
