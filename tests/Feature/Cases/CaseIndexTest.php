<?php

namespace Tests\Feature\Cases;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Models\Message;
use App\Modules\Reporting\Models\Report;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CaseIndexTest extends TestCase
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

    public function test_access_requires_a_case_permission(): void
    {
        $this->get(route('manage.cases.index'))->assertRedirect(route('login'));
        $this->actingAs($this->userWith(Permission::ViewAuditLog))->get(route('manage.cases.index'))->assertForbidden();
        $this->actingAs($this->userWith(Permission::ViewAssignedCases))->get(route('manage.cases.index'))->assertOk();
    }

    public function test_manager_sees_only_assigned_cases_and_supervisor_sees_all(): void
    {
        $manager = $this->userWith(Permission::ViewCaseList, Permission::ViewAssignedCases);
        $supervisor = $this->userWith(Permission::ViewCaseList, Permission::ViewAssignedCases, Permission::ViewUnassignedCases);
        $mine = CaseFile::factory()->assignedTo($manager)->create(['report_id' => Report::factory()->create(['reference' => 'SMM-MINE-0001'])->id]);
        $other = CaseFile::factory()->create(['report_id' => Report::factory()->create(['reference' => 'SMM-OTHR-0002'])->id]);

        $this->actingAs($manager)->get(route('manage.cases.index'))
            ->assertOk()
            ->assertSee('SMM-MINE-0001')
            ->assertDontSee('SMM-OTHR-0002');

        $this->actingAs($supervisor)->get(route('manage.cases.index'))
            ->assertOk()
            ->assertSee('SMM-MINE-0001')
            ->assertSee('SMM-OTHR-0002');
    }

    public function test_filters_narrow_the_list(): void
    {
        $supervisor = $this->userWith(Permission::ViewCaseList, Permission::ViewAssignedCases, Permission::ViewUnassignedCases);
        $categoryA = ReportCategory::factory()->create();
        $categoryB = ReportCategory::factory()->create();
        $open = CaseFile::factory()->assignedTo($supervisor)->create(['report_id' => Report::factory()->create(['reference' => 'SMM-OPEN-0001', 'report_category_id' => $categoryA->id, 'submitted_at' => now()->subDays(10)])->id]);
        $closed = CaseFile::factory()->withStatus(CaseStatus::Closed)->highPriority()->create(['report_id' => Report::factory()->create(['reference' => 'SMM-CLOS-0002', 'report_category_id' => $categoryB->id, 'submitted_at' => now()])->id]);

        $this->actingAs($supervisor)->get(route('manage.cases.index', ['status' => 'closed']))->assertSee('SMM-CLOS-0002')->assertDontSee('SMM-OPEN-0001');
        $this->actingAs($supervisor)->get(route('manage.cases.index', ['category' => $categoryA->uuid]))->assertSee('SMM-OPEN-0001')->assertDontSee('SMM-CLOS-0002');
        $this->actingAs($supervisor)->get(route('manage.cases.index', ['assignment' => 'mine']))->assertSee('SMM-OPEN-0001')->assertDontSee('SMM-CLOS-0002');
        $this->actingAs($supervisor)->get(route('manage.cases.index', ['assignment' => 'unassigned']))->assertSee('SMM-CLOS-0002')->assertDontSee('SMM-OPEN-0001');
        $this->actingAs($supervisor)->get(route('manage.cases.index', ['priority' => 'high']))->assertSee('SMM-CLOS-0002')->assertInertia(fn (Assert $page) => $page->where('cases.data.0.priority.label', 'Priorité haute'))->assertDontSee('SMM-OPEN-0001');
        $this->actingAs($supervisor)->get(route('manage.cases.index', ['from' => now()->subDay()->toDateString()]))->assertSee('SMM-CLOS-0002')->assertDontSee('SMM-OPEN-0001');
        $this->actingAs($supervisor)->get(route('manage.cases.index', ['status' => 'inconnu']))->assertSessionHasErrors('status');
    }

    public function test_list_shows_unread_messages_but_never_the_narrative(): void
    {
        $supervisor = $this->userWith(Permission::ViewCaseList, Permission::ViewAssignedCases, Permission::ViewUnassignedCases);
        $case = CaseFile::factory()->create(['report_id' => Report::factory()->create(['description' => 'Récit confidentiel fictif'])->id]);
        $conversation = Conversation::factory()->create(['report_id' => $case->report_id]);
        Message::factory()->fromReporter()->count(2)->create(['conversation_id' => $conversation->id, 'body' => 'Message fictif']);

        $response = $this->actingAs($supervisor)->get(route('manage.cases.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('cases.data.0.unread', 2));

        $this->assertPageMissing($response, 'Récit confidentiel');
        $this->assertPageMissing($response, 'Message fictif');
    }

    public function test_dashboard_counters_and_links_follow_visibility(): void
    {
        $manager = $this->userWith(Permission::ViewCaseList, Permission::ViewAssignedCases);
        CaseFile::factory()->assignedTo($manager)->count(2)->create();
        CaseFile::factory()->create();

        $this->actingAs($manager)->get(route('manage.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('manage/Dashboard')
                ->where('canViewCases', true)
                ->where('auth.can.viewCases', true)
                ->where('urls.all', route('manage.cases.index'))
                ->where('counters', ['mine_open' => 2, 'unassigned_open' => 0, 'visible_total' => 2]));

        $this->actingAs($this->userWith(Permission::ViewAuditLog))->get(route('manage.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canViewCases', false)
                ->where('counters', null)
                ->where('auth.can.viewCases', false));
    }
}
