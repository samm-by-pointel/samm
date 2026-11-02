<?php

namespace Tests\Feature\Workflow;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Actions\ChangeCaseStatus;
use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Exceptions\InvalidStatusTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class ChangeCaseStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_transition_updates_the_case_and_writes_history_and_audit(): void
    {
        $manager = User::factory()->create();
        $case = CaseFile::factory()->create();

        app(ChangeCaseStatus::class)->handle($case, CaseStatus::InProgress, $manager);

        $case->refresh();
        $this->assertSame(CaseStatus::InProgress, $case->status);
        $this->assertNotNull($case->last_activity_at);

        $history = $case->statusHistories()->sole();
        $this->assertSame(CaseStatus::New, $history->from_status);
        $this->assertSame(CaseStatus::InProgress, $history->to_status);
        $this->assertTrue($history->changedBy->is($manager));

        $audit = AuditLog::query()->where('action', AuditAction::CaseStatusChanged->value)->sole();
        $this->assertTrue($audit->subject->is($case));
        $this->assertTrue($audit->actor->is($manager));
        $this->assertEquals(['from' => 'new', 'to' => 'in_progress'], $audit->context);
    }

    public function test_closing_records_the_closure_reason_without_copying_it_to_the_audit(): void
    {
        $case = CaseFile::factory()->withStatus(CaseStatus::InProgress)->create();

        app(ChangeCaseStatus::class)->handle($case, CaseStatus::Closed, null, 'Motif de clôture fictif');

        $case->refresh();
        $this->assertSame(CaseStatus::Closed, $case->status);
        $this->assertNotNull($case->closed_at);
        $this->assertSame('Motif de clôture fictif', $case->closure_reason);
        $this->assertSame('Motif de clôture fictif', $case->statusHistories()->latest('id')->first()->comment);

        $audit = AuditLog::query()->where('action', AuditAction::CaseStatusChanged->value)->sole();
        $this->assertStringNotContainsString('Motif', json_encode($audit->context));
        $this->assertNotSame('Motif de clôture fictif', DB::table('case_status_histories')->value('comment'));
    }

    public function test_archiving_then_reopening_from_closed(): void
    {
        $case = CaseFile::factory()->withStatus(CaseStatus::Closed)->create();

        app(ChangeCaseStatus::class)->handle($case, CaseStatus::Archived);
        $this->assertNotNull($case->fresh()->archived_at);

        $reopened = CaseFile::factory()->withStatus(CaseStatus::Closed)->create(['closure_reason' => 'Ancien motif']);
        app(ChangeCaseStatus::class)->handle($reopened, CaseStatus::InProgress);

        $reopened->refresh();
        $this->assertTrue($reopened->status->isOpen());
        $this->assertNull($reopened->closed_at);
        $this->assertNull($reopened->closure_reason);
    }

    public function test_invalid_transition_throws_and_writes_nothing(): void
    {
        $case = CaseFile::factory()->withStatus(CaseStatus::Archived)->create();

        try {
            app(ChangeCaseStatus::class)->handle($case, CaseStatus::New);
            $this->fail('Une transition invalide doit lever une exception.');
        } catch (InvalidStatusTransition) {
            // attendu
        }

        $this->assertSame(CaseStatus::Archived, $case->fresh()->status);
        $this->assertDatabaseCount('case_status_histories', 0);
        $this->assertSame(0, AuditLog::query()->where('action', AuditAction::CaseStatusChanged->value)->count());
    }

    public function test_history_entries_cannot_be_modified_nor_deleted(): void
    {
        $case = CaseFile::factory()->create();
        app(ChangeCaseStatus::class)->handle($case, CaseStatus::ToQualify);
        $entry = $case->statusHistories()->sole();

        try {
            $entry->update(['to_status' => CaseStatus::Closed]);
            $this->fail('La modification doit être refusée.');
        } catch (LogicException) {
            // attendu
        }

        $this->expectException(LogicException::class);
        $entry->delete();
    }
}
