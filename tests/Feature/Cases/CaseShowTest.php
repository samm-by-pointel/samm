<?php

namespace Tests\Feature\Cases;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Messaging\Actions\PostMessage;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Actions\ChangeCaseStatus;
use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CaseShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
    }

    private function userWith(Permission ...$permissions): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::factory()->withPermissions(...$permissions)->create());

        return $user;
    }

    public function test_unassigned_manager_is_forbidden_and_assigned_manager_sees_the_case(): void
    {
        $manager = $this->userWith(Permission::ViewAssignedCases);
        $case = CaseFile::factory()->create(['report_id' => Report::factory()->pseudonymous('Alouette')->detailed()->create(['description' => 'Récit fictif détaillé.'])->id]);

        $this->actingAs($manager)->get(route('manage.cases.show', $case))->assertForbidden();

        $case->forceFill(['assigned_to_user_id' => $manager->id])->save();

        $this->actingAs($manager)->get(route('manage.cases.show', $case))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('manage/cases/Show')
                ->where('report.description', 'Récit fictif détaillé.')
                ->where('report.location', 'Lieu fictif de test')
                ->where('case.reporter', 'Alouette')
                ->where('case.confidentiality', 'Pseudonyme')
                ->where('case.priority.isHigh', false));
    }

    public function test_anonymous_reporter_is_named_neutrally_and_identity_block_says_none(): void
    {
        $supervisor = $this->userWith(Permission::ViewAssignedCases, Permission::ViewUnassignedCases, Permission::ViewIdentities);
        $case = CaseFile::factory()->highPriority()->create(['report_id' => Report::factory()->anonymous()->immediateDanger()->create()->id]);

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('case.reporter', 'Personne anonyme')
                ->where('case.priority.label', 'Priorité haute')
                ->where('case.priority.isHigh', true)
                ->where('case.immediateDanger', true)
                ->where('identity.allowed', false)
                ->where('identity.level', 'Anonyme'));
    }

    public function test_viewing_a_case_is_audited_and_marks_reporter_messages_read(): void
    {
        $supervisor = $this->userWith(Permission::ViewAssignedCases, Permission::ViewUnassignedCases);
        $case = CaseFile::factory()->create();
        $conversation = Conversation::factory()->create(['report_id' => $case->report_id]);
        app(PostMessage::class)->handle($conversation, MessageAuthor::Reporter, 'Précision fictive de la personne');
        app(PostMessage::class)->handle($conversation, MessageAuthor::Staff, 'Réponse fictive', User::factory()->create(['name' => 'Moussa Sarr']));

        $this->assertSame(1, $conversation->unreadForStaff());

        $response = $this->actingAs($supervisor)->get(route('manage.cases.show', $case))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('messages.0.body', 'Précision fictive de la personne')
                ->where('messages.0.fromReporter', true)
                ->where('messages.1.author', 'Gestionnaire du dossier'));

        $this->assertPageMissing($response, 'Moussa Sarr');

        $this->assertSame(0, $conversation->unreadForStaff());

        $entry = AuditLog::query()->where('action', AuditAction::CaseViewed->value)->sole();
        $this->assertTrue($entry->subject->is($case));
        $this->assertTrue($entry->actor->is($supervisor));
        $this->assertNull($entry->context);
    }

    public function test_status_history_is_displayed(): void
    {
        $supervisor = $this->userWith(Permission::ViewAssignedCases, Permission::ViewUnassignedCases);
        $case = CaseFile::factory()->create();
        app(ChangeCaseStatus::class)->handle($case, CaseStatus::InProgress, $supervisor);

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('histories', fn ($histories) => collect($histories)->contains(fn ($history) => $history['to'] === 'En cours de traitement'
                    && $history['from'] === 'Nouveau'
                    && $history['by'] === $supervisor->name)));
    }

    public function test_header_link_to_cases_follows_the_permission(): void
    {
        $this->actingAs($this->userWith(Permission::ViewAssignedCases))->get(route('manage.dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('auth.can.viewCases', true));
        $this->actingAs($this->userWith(Permission::ViewAuditLog))->get(route('manage.dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('auth.can.viewCases', false));
    }
}
