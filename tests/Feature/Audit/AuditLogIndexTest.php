<?php

namespace Tests\Feature\Audit;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuditLogIndexTest extends TestCase
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

    public function test_guest_is_redirected_and_user_without_permission_is_forbidden(): void
    {
        $this->get(route('manage.audit.index'))->assertRedirect(route('login'));

        $this->actingAs($this->userWith(Permission::ViewAssignedCases))
            ->get(route('manage.audit.index'))
            ->assertForbidden();
    }

    public function test_entries_are_listed_in_plain_words_without_ip_address(): void
    {
        $viewer = $this->userWith(Permission::ViewAuditLog);
        $actor = User::factory()->create(['name' => 'Moussa Sarr']);
        AuditLog::query()->toBase()->delete();
        AuditLog::factory()->withIp('203.0.113.77')->create(['action' => AuditAction::LoginSucceeded, 'actor_id' => $actor->id, 'subject_type' => $actor->getMorphClass(), 'subject_id' => $actor->id]);
        AuditLog::factory()->create(['action' => AuditAction::LoginFailed, 'context' => ['reason' => 'unknown_account']]);

        $response = $this->actingAs($viewer)->get(route('manage.audit.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('manage/audit/Index')
                ->has('entries.data', 2)
                ->where('entries.data.0.action', 'Échec de connexion')
                ->where('entries.data.0.actor', 'Système')
                ->where('entries.data.1.action', 'Connexion réussie')
                ->where('entries.data.1.actor', 'Moussa Sarr'));

        $this->assertPageMissing($response, '203.0.113.77');
        $this->assertPageMissing($response, 'unknown_account');
    }

    public function test_case_subjects_show_the_case_reference(): void
    {
        $viewer = $this->userWith(Permission::ViewAuditLog, Permission::ViewAssignedCases, Permission::ViewUnassignedCases);
        $case = CaseFile::factory()->create(['report_id' => Report::factory()->create(['reference' => 'SMM-7K4Q-92XD'])->id]);
        AuditLog::query()->toBase()->delete();
        AuditLog::factory()->create(['action' => AuditAction::CaseViewed, 'subject_type' => $case->getMorphClass(), 'subject_id' => $case->id]);
        AuditLog::factory()->create(['action' => AuditAction::ReportSubmitted, 'subject_type' => $case->report->getMorphClass(), 'subject_id' => $case->report_id]);

        $this->actingAs($viewer)->get(route('manage.audit.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('entries.data.0.subject', ['kind' => 'Signalement', 'label' => 'SMM-7K4Q-92XD', 'url' => null, 'code' => true])
                ->where('entries.data.1.subject', ['kind' => 'Dossier', 'label' => 'SMM-7K4Q-92XD', 'url' => route('manage.cases.show', $case), 'code' => true]));
    }

    public function test_entries_can_be_filtered_by_action(): void
    {
        $viewer = $this->userWith(Permission::ViewAuditLog);
        AuditLog::query()->toBase()->delete();
        AuditLog::factory()->create(['action' => AuditAction::LoginSucceeded]);
        AuditLog::factory()->create(['action' => AuditAction::Logout]);

        $this->actingAs($viewer)
            ->get(route('manage.audit.index', ['action' => AuditAction::Logout->value]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('selectedAction', AuditAction::Logout->value)
                ->has('entries.data', 1)
                ->where('entries.data.0.action', 'Déconnexion'));

        $this->actingAs($viewer)
            ->get(route('manage.audit.index', ['action' => 'inconnue']))
            ->assertSessionHasErrors('action');
    }

    public function test_header_link_appears_only_with_the_permission(): void
    {
        $this->actingAs($this->userWith(Permission::ViewAuditLog))
            ->get(route('manage.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('auth.can.viewAudit', true));

        $this->actingAs($this->userWith(Permission::ViewAssignedCases))
            ->get(route('manage.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('auth.can.viewAudit', false));
    }

    public function test_empty_log_shows_a_neutral_message(): void
    {
        $viewer = $this->userWith(Permission::ViewAuditLog);
        AuditLog::query()->toBase()->delete();

        $this->actingAs($viewer)
            ->get(route('manage.audit.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('entries.data', 0)
                ->where('translations.manage.audit.empty', 'Aucune entrée pour le moment.'));
    }
}
