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
            ->assertSee('Récit fictif détaillé.')
            ->assertSee('Lieu fictif de test')
            ->assertSee('Alouette')
            ->assertSee('Pseudonyme')
            ->assertSee('Faits rapportés')
            ->assertDontSee('Priorité haute');
    }

    public function test_anonymous_reporter_is_named_neutrally_and_identity_block_says_none(): void
    {
        $supervisor = $this->userWith(Permission::ViewAssignedCases, Permission::ViewUnassignedCases, Permission::ViewIdentities);
        $case = CaseFile::factory()->highPriority()->create(['report_id' => Report::factory()->anonymous()->immediateDanger()->create()->id]);

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))
            ->assertOk()
            ->assertSee('Personne anonyme')
            ->assertSee('Priorité haute')
            ->assertSee('Danger immédiat signalé par la personne')
            ->assertSee('Aucune identité')
            ->assertDontSee('Afficher (motif requis)');
    }

    public function test_viewing_a_case_is_audited_and_marks_reporter_messages_read(): void
    {
        $supervisor = $this->userWith(Permission::ViewAssignedCases, Permission::ViewUnassignedCases);
        $case = CaseFile::factory()->create();
        $conversation = Conversation::factory()->create(['report_id' => $case->report_id]);
        app(PostMessage::class)->handle($conversation, MessageAuthor::Reporter, 'Précision fictive de la personne');
        app(PostMessage::class)->handle($conversation, MessageAuthor::Staff, 'Réponse fictive', User::factory()->create(['name' => 'Moussa Sarr']));

        $this->assertSame(1, $conversation->unreadForStaff());

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))
            ->assertOk()
            ->assertSee('Précision fictive de la personne')
            ->assertSee('Gestionnaire du dossier')
            ->assertDontSee('Moussa Sarr');

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
            ->assertSee('Historique des statuts')
            ->assertSee('En cours de traitement')
            ->assertSee('depuis Nouveau')
            ->assertSee($supervisor->name);
    }

    public function test_header_link_to_cases_follows_the_permission(): void
    {
        $this->actingAs($this->userWith(Permission::ViewAssignedCases))->get(route('manage.dashboard'))->assertSee(route('manage.cases.index'));
        $this->actingAs($this->userWith(Permission::ViewAuditLog))->get(route('manage.dashboard'))->assertDontSee(route('manage.cases.index'));
    }
}
