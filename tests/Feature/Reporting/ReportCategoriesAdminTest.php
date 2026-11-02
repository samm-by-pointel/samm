<?php

namespace Tests\Feature\Reporting;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Enums\ReportStep;
use App\Modules\Reporting\Models\Report;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportCategoriesAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('organization_admin');
    }

    public function test_categories_can_be_added_renamed_reordered_and_deactivated(): void
    {
        $this->actingAs($this->admin)->post(route('manage.organization.categories.store'), ['name' => ['fr' => 'Cyberharcèlement', 'en' => 'Cyberbullying'], 'description' => ['fr' => 'Par messages ou réseaux sociaux'], 'position' => 1, 'is_active' => '1'])
            ->assertRedirect(route('manage.organization.edit'))
            ->assertSessionHas('status', 'Catégorie enregistrée.');
        $this->actingAs($this->admin)->post(route('manage.organization.categories.store'), ['name' => ['fr' => 'Autre situation'], 'position' => 2, 'is_active' => '1'])->assertRedirect();

        $category = ReportCategory::query()->where('position', 1)->sole();
        $this->assertSame('Cyberbullying', $category->translate('name', 'en'));
        $this->assertSame('Par messages ou réseaux sociaux', $category->translate('description', 'fr'));

        auth()->logout();
        $this->get(route('report.step.show', ReportStep::Situation))
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories.0.label', 'Cyberharcèlement')
                ->where('categories.1.label', 'Autre situation'));

        $this->actingAs($this->admin)->put(route('manage.organization.categories.update', $category), ['name' => ['fr' => 'Harcèlement en ligne'], 'position' => 5])->assertRedirect();
        $category->refresh();
        $this->assertSame('Harcèlement en ligne', $category->translate('name', 'fr'));
        $this->assertFalse($category->is_active);

        auth()->logout();
        $response = $this->get(route('report.step.show', ReportStep::Situation))
            ->assertInertia(fn (Assert $page) => $page->has('categories', 1)->where('categories.0.label', 'Autre situation'));
        $this->assertPageMissing($response, 'Harcèlement en ligne');

        $operations = AuditLog::query()->where('action', AuditAction::CategoryChanged->value)->pluck('context')->map(fn (array $context): string => $context['operation'])->all();
        $this->assertSame(['created', 'created', 'updated'], $operations);
    }

    public function test_deactivated_category_remains_readable_on_existing_cases(): void
    {
        $category = ReportCategory::factory()->create(['name' => ['fr' => 'Ancienne catégorie', 'en' => 'Former category']]);
        $supervisor = User::factory()->create();
        $supervisor->assignRole('supervisor');
        $case = CaseFile::factory()->create(['report_id' => Report::factory()->create(['report_category_id' => $category->id])->id]);

        $this->actingAs($this->admin)->put(route('manage.organization.categories.update', $category), ['name' => ['fr' => 'Ancienne catégorie'], 'position' => 1])->assertRedirect();

        $this->actingAs($supervisor)->get(route('manage.cases.show', $case))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('case.category', 'Ancienne catégorie'));
    }

    public function test_name_is_required_in_at_least_one_language_and_permission_is_required(): void
    {
        $this->actingAs($this->admin)->post(route('manage.organization.categories.store'), ['name' => ['fr' => '', 'en' => ''], 'position' => 1])
            ->assertSessionHasErrorsIn('categories', ['name' => 'Indiquez un nom dans au moins une langue.']);

        $manager = User::factory()->create();
        $manager->assignRole('case_manager');
        $this->actingAs($manager)->post(route('manage.organization.categories.store'), ['name' => ['fr' => 'Interdit']])->assertForbidden();

        $this->assertDatabaseCount('report_categories', 0);
    }
}
