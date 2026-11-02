<?php

namespace Tests\Feature\Reporting;

use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Reporting\Enums\ReportStep;
use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportWizardTest extends TestCase
{
    use RefreshDatabase;

    private ReportCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);

        $this->category = ReportCategory::factory()->create(['name' => ['fr' => 'Harcèlement moral', 'en' => 'Psychological harassment'], 'position' => 1]);
    }

    private function step(ReportStep $step): string
    {
        return route('report.step.show', $step);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function complete(ReportStep $step, array $data): void
    {
        $this->post(route('report.step.store', $step), $data)->assertSessionHasNoErrors();
    }

    private function completeFirstTwoSteps(): void
    {
        $this->complete(ReportStep::Situation, ['category' => $this->category->uuid]);
        $this->complete(ReportStep::Confidentiality, ['confidentiality_level' => 'anonymous']);
    }

    public function test_start_page_explains_what_is_and_is_not_collected(): void
    {
        $this->get(route('report.start'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('report/Start')
                ->where('hasDraft', false)
                ->where('beginUrl', route('report.step.show', ReportStep::Situation))
                ->where('translations.report_form.start.not_collected_title', 'Ce que nous ne recueillons pas')
                ->where('site.quickExitUrl', config('samm.quick_exit_url')));
    }

    public function test_home_page_links_to_the_form(): void
    {
        $this->get(route('public.home'))
            ->assertInertia(fn (Assert $page) => $page->where('routes.reportStart', route('report.start')));
    }

    public function test_first_step_lists_only_active_categories_in_order(): void
    {
        ReportCategory::factory()->inactive()->create(['name' => ['fr' => 'Catégorie désactivée', 'en' => 'Disabled'], 'position' => 0]);
        ReportCategory::factory()->create(['name' => ['fr' => 'Discrimination', 'en' => 'Discrimination'], 'position' => 2]);

        $response = $this->get($this->step(ReportStep::Situation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('report/Step')
                ->where('step', 'situation')
                ->has('steps', 6)
                ->has('categories', 2)
                ->where('categories.0.label', 'Harcèlement moral')
                ->where('categories.1.label', 'Discrimination'));

        $this->assertPageMissing($response, 'Catégorie désactivée');
    }

    public function test_inactive_or_unknown_category_is_refused(): void
    {
        $inactive = ReportCategory::factory()->inactive()->create();

        $this->from($this->step(ReportStep::Situation))
            ->post(route('report.step.store', ReportStep::Situation), ['category' => $inactive->uuid])
            ->assertRedirect($this->step(ReportStep::Situation))
            ->assertSessionHasErrors('category');

        $this->post(route('report.step.store', ReportStep::Situation), ['category' => 'inconnue'])
            ->assertSessionHasErrors('category');
    }

    public function test_steps_must_be_completed_in_order(): void
    {
        $this->get($this->step(ReportStep::Description))
            ->assertRedirect($this->step(ReportStep::Situation));

        $this->complete(ReportStep::Situation, ['category' => $this->category->uuid]);

        $this->get($this->step(ReportStep::Confidentiality))->assertOk();
        $this->get($this->step(ReportStep::People))->assertRedirect($this->step(ReportStep::Confidentiality));

        $this->post(route('report.step.store', ReportStep::People), ['witnesses' => 'x'])
            ->assertRedirect($this->step(ReportStep::Confidentiality));
    }

    public function test_unknown_step_returns_not_found(): void
    {
        $this->get('/signaler/etape-inconnue')->assertNotFound();
        $this->post('/signaler/etape-inconnue', [])->assertNotFound();
    }

    public function test_going_back_keeps_the_answers(): void
    {
        $this->complete(ReportStep::Situation, ['category' => $this->category->uuid]);
        $this->complete(ReportStep::Confidentiality, ['confidentiality_level' => 'pseudonymous', 'pseudonym' => 'Alouette']);

        $this->get($this->step(ReportStep::Situation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('values.category', $this->category->uuid));

        $this->get($this->step(ReportStep::Confidentiality))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('values.confidentiality_level', 'pseudonymous')
                ->where('values.pseudonym', 'Alouette'));
    }

    public function test_confidentiality_step_offers_the_three_levels(): void
    {
        $this->complete(ReportStep::Situation, ['category' => $this->category->uuid]);

        $this->get($this->step(ReportStep::Confidentiality))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('levels', 3)
                ->where('levels.0.value', 'anonymous')
                ->where('levels.1.value', 'pseudonymous')
                ->where('levels.2.value', 'confidential'));
    }

    public function test_pseudonym_is_required_only_for_the_pseudonymous_level(): void
    {
        $this->complete(ReportStep::Situation, ['category' => $this->category->uuid]);

        $this->from($this->step(ReportStep::Confidentiality))
            ->post(route('report.step.store', ReportStep::Confidentiality), ['confidentiality_level' => 'pseudonymous'])
            ->assertSessionHasErrors(['pseudonym' => 'Choisissez un pseudonyme pour ce niveau de confidentialité.']);

        $this->post(route('report.step.store', ReportStep::Confidentiality), ['confidentiality_level' => 'anonymous', 'pseudonym' => 'Oublié'])
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->step(ReportStep::Description));

        $this->assertNull(session('report_draft.steps.confidentialite.pseudonym'), 'Un pseudonyme saisi puis abandonné pour l\'anonymat n\'est pas conservé.');
    }

    public function test_name_is_required_only_for_the_confidential_level_and_identity_is_dropped_otherwise(): void
    {
        $this->complete(ReportStep::Situation, ['category' => $this->category->uuid]);

        $this->post(route('report.step.store', ReportStep::Confidentiality), ['confidentiality_level' => 'confidential'])
            ->assertSessionHasErrors(['identity_full_name' => 'Indiquez votre nom pour ce niveau de confidentialité, ou choisissez un autre niveau.']);

        $this->post(route('report.step.store', ReportStep::Confidentiality), ['confidentiality_level' => 'confidential', 'identity_full_name' => 'Nom Fictif', 'identity_email' => 'pas-un-email'])
            ->assertSessionHasErrors('identity_email');

        $this->post(route('report.step.store', ReportStep::Confidentiality), ['confidentiality_level' => 'anonymous', 'identity_full_name' => 'Nom Fictif'])
            ->assertSessionHasNoErrors();

        $this->assertNull(session('report_draft.steps.confidentialite.identity_full_name'));
    }

    public function test_only_the_description_is_required_at_step_three(): void
    {
        $this->completeFirstTwoSteps();

        $this->from($this->step(ReportStep::Description))
            ->post(route('report.step.store', ReportStep::Description), [])
            ->assertSessionHasErrors(['description' => 'Le champ description est obligatoire.'])
            ->assertSessionDoesntHaveErrors(['facts_date', 'location', 'is_recurring', 'immediate_danger']);

        $this->post(route('report.step.store', ReportStep::Description), ['description' => 'Récit fictif de test.'])
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->step(ReportStep::People));
    }

    public function test_future_facts_date_is_refused(): void
    {
        $this->completeFirstTwoSteps();

        $this->post(route('report.step.store', ReportStep::Description), ['description' => 'Récit fictif.', 'facts_date' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors('facts_date');
    }

    public function test_immediate_danger_shows_the_emergency_block_on_the_next_step(): void
    {
        EmergencyContact::factory()->create(['label' => ['fr' => 'Police', 'en' => 'Police'], 'phone_number' => '17']);
        $this->completeFirstTwoSteps();

        $this->complete(ReportStep::Description, ['description' => 'Récit fictif.', 'immediate_danger' => '1']);
        $this->get($this->step(ReportStep::People))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('immediateDanger', true)
                ->where('emergencyContacts.0.dialable', '17'));

        $this->complete(ReportStep::Description, ['description' => 'Récit fictif.', 'immediate_danger' => '0']);
        $this->get($this->step(ReportStep::People))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('immediateDanger', false));

        $this->assertFalse(session('report_draft.steps.description.immediate_danger'));
    }

    public function test_fourth_step_is_fully_optional_and_leads_to_the_attachments_step(): void
    {
        $this->completeFirstTwoSteps();
        $this->complete(ReportStep::Description, ['description' => 'Récit fictif.']);

        $this->post(route('report.step.store', ReportStep::People), [])
            ->assertSessionHasNoErrors()
            ->assertRedirect($this->step(ReportStep::Attachments));

        $this->get($this->step(ReportStep::Attachments))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('step', 'pieces-jointes'));
    }

    public function test_abandon_erases_the_draft(): void
    {
        $this->completeFirstTwoSteps();
        $this->assertTrue(session()->has('report_draft'));

        $this->post(route('report.abandon'))
            ->assertRedirect(route('public.home'))
            ->assertSessionHas('status', 'Votre brouillon a été effacé. Rien n\'a été transmis.');

        $this->assertFalse(session()->has('report_draft'));
        $this->get($this->step(ReportStep::Confidentiality))->assertRedirect($this->step(ReportStep::Situation));
    }

    public function test_start_page_offers_to_resume_an_existing_draft(): void
    {
        $this->completeFirstTwoSteps();

        $this->get(route('report.start'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('hasDraft', true)
                ->where('resumeUrl', $this->step(ReportStep::Description)));
    }
}
