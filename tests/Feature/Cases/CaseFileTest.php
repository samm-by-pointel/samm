<?php

namespace Tests\Feature\Cases;

use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CaseFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_case_starts_new_unassigned_with_normal_priority(): void
    {
        $case = CaseFile::factory()->create();

        $this->assertSame(CaseStatus::New, $case->status);
        $this->assertSame(CasePriority::Normal, $case->priority);
        $this->assertFalse($case->isAssigned());
        $this->assertNull($case->closed_at);
        $this->assertTrue($case->report->case->is($case));
    }

    public function test_one_case_per_report(): void
    {
        $report = Report::factory()->create();
        CaseFile::factory()->create(['report_id' => $report->id]);

        $this->expectException(UniqueConstraintViolationException::class);

        CaseFile::factory()->create(['report_id' => $report->id]);
    }

    public function test_deleting_the_assigned_account_keeps_the_case_unassigned(): void
    {
        $manager = User::factory()->create();
        $case = CaseFile::factory()->assignedTo($manager)->create();

        $this->assertTrue($case->isAssignedTo($manager));

        $manager->delete();

        $this->assertDatabaseCount('cases', 1);
        $this->assertNull($case->fresh()->assigned_to_user_id);
    }

    public function test_scopes_open_unassigned_and_assigned_to(): void
    {
        $manager = User::factory()->create();
        CaseFile::factory()->assignedTo($manager)->create();
        CaseFile::factory()->create();
        CaseFile::factory()->withStatus(CaseStatus::Closed)->create();
        CaseFile::factory()->withStatus(CaseStatus::Archived)->create();

        $this->assertSame(2, CaseFile::query()->open()->count());
        $this->assertSame(1, CaseFile::query()->assignedTo($manager)->count());
        $this->assertSame(3, CaseFile::query()->unassigned()->count());
    }

    public function test_closure_reason_is_encrypted_at_rest(): void
    {
        $case = CaseFile::factory()->withStatus(CaseStatus::Closed)->create(['closure_reason' => 'Motif fictif']);

        $raw = DB::table('cases')->where('id', $case->id)->value('closure_reason');

        $this->assertNotSame('Motif fictif', $raw);
        $this->assertSame('Motif fictif', $case->fresh()->closure_reason);
    }

    public function test_priority_labels_exist_in_both_languages(): void
    {
        foreach (CasePriority::cases() as $priority) {
            foreach (['fr', 'en'] as $locale) {
                $this->assertNotSame('cases.priorities.'.$priority->value, __('cases.priorities.'.$priority->value, [], $locale));
            }
        }
    }
}
