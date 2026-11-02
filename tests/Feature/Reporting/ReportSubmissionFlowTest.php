<?php

namespace Tests\Feature\Reporting;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Reporting\Enums\ReportStep;
use App\Modules\Reporting\Models\Report;
use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportSubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    private ReportCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr', 'session.driver' => 'database']);

        $this->category = ReportCategory::factory()->create(['name' => ['fr' => 'Discrimination', 'en' => 'Discrimination']]);
    }

    /**
     * @param  array<string, mixed>  $confidentiality
     */
    private function reachConsentStep(array $confidentiality = ['confidentiality_level' => 'anonymous']): void
    {
        $this->post(route('report.step.store', ReportStep::Situation), ['category' => $this->category->uuid])->assertSessionHasNoErrors();
        $this->post(route('report.step.store', ReportStep::Confidentiality), $confidentiality)->assertSessionHasNoErrors();
        $this->post(route('report.step.store', ReportStep::Description), ['description' => 'Récit fictif de test.', 'immediate_danger' => '1'])->assertSessionHasNoErrors();
        $this->post(route('report.step.store', ReportStep::People), [])->assertSessionHasNoErrors();
        $this->post(route('report.step.store', ReportStep::Attachments), [])->assertSessionHasNoErrors()->assertRedirect(route('report.step.show', ReportStep::Consent));
    }

    public function test_attachments_step_can_be_passed_without_a_file(): void
    {
        $this->reachConsentStep();

        $this->get(route('report.step.show', ReportStep::Attachments))
            ->assertOk()
            ->assertSee('Poursuivre sans pièce jointe')
            ->assertSee('Étape 5 sur 6');

        $this->assertTrue(session('report_draft.steps.pieces-jointes.skipped'));
    }

    public function test_consent_step_shows_a_summary_without_the_narrative(): void
    {
        $this->reachConsentStep();

        $this->get(route('report.step.show', ReportStep::Consent))
            ->assertOk()
            ->assertSee('Récapitulatif de vos choix')
            ->assertSee('Discrimination')
            ->assertSee('Anonyme')
            ->assertSee('Transmettre le signalement')
            ->assertSee(route('report.submit'))
            ->assertDontSee('Récit fictif')
            ->assertDontSee('consent_identity_disclosure');
    }

    public function test_processing_consent_is_required_to_submit(): void
    {
        $this->reachConsentStep();

        $this->from(route('report.step.show', ReportStep::Consent))
            ->post(route('report.submit'), ['consent_contact' => '1'])
            ->assertRedirect(route('report.step.show', ReportStep::Consent))
            ->assertSessionHasErrors(['consent_processing' => 'Votre accord pour le traitement du signalement est nécessaire pour transmettre.']);

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_identity_consent_is_required_for_the_confidential_level_only(): void
    {
        $this->reachConsentStep(['confidentiality_level' => 'confidential', 'identity_full_name' => 'Nom Fictif']);

        $this->get(route('report.step.show', ReportStep::Consent))->assertSee('consent_identity_disclosure');

        $this->post(route('report.submit'), ['consent_processing' => '1'])
            ->assertSessionHasErrors('consent_identity_disclosure');

        $this->post(route('report.submit'), ['consent_processing' => '1', 'consent_identity_disclosure' => '1'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('report.confirmation'));

        $this->assertDatabaseCount('report_identities', 1);
    }

    public function test_full_submission_shows_the_codes_once_and_clears_the_draft(): void
    {
        $this->reachConsentStep();

        $response = $this->post(route('report.submit'), ['consent_processing' => '1', 'consent_contact' => '1'])
            ->assertRedirect(route('report.confirmation'));

        $this->assertFalse(session()->has('report_draft'));

        $report = Report::query()->sole();
        $page = $this->get(route('report.confirmation'))
            ->assertOk()
            ->assertSee('Votre signalement a bien été déposé')
            ->assertSee($report->reference)
            ->assertSee('Secret de récupération')
            ->assertSee('Copier les codes')
            ->assertSee('download="samm-codes.txt"', false);

        preg_match('/id="samm-secret">([A-Z2-9-]+)</', $page->getContent(), $matches);
        $this->assertNotEmpty($matches[1] ?? null, 'Le secret doit être affiché.');
        $this->assertTrue($report->trackingCredential->verify($matches[1]));

        $this->get(route('report.confirmation'))->assertRedirect(route('public.home'));
        $this->assertTrue($report->immediate_danger);
        $this->assertTrue($report->case->status->isOpen());
    }

    public function test_submission_is_refused_when_previous_steps_are_missing(): void
    {
        $this->post(route('report.submit'), ['consent_processing' => '1'])
            ->assertRedirect(route('report.step.show', ReportStep::Situation));

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_sixth_submission_in_ten_minutes_is_throttled_in_french(): void
    {
        // The limiter counts every attempt on the submission route, even without a draft.
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('report.submit'), [])->assertStatus(302);
        }

        $this->reachConsentStep();

        $this->post(route('report.submit'), ['consent_processing' => '1'])
            ->assertStatus(429)
            ->assertSee('Trop de tentatives')
            ->assertSee('Quitter rapidement');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_no_ip_address_is_stored_anywhere_after_a_submission(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);
        $this->reachConsentStep();
        $this->post(route('report.submit'), ['consent_processing' => '1']);

        $this->assertSame(0, DB::table('sessions')->whereNotNull('ip_address')->count());
        $this->assertSame(0, AuditLog::query()->whereNotNull('ip_address')->count());
        $this->assertSame(0, DB::table('sessions')->whereNotNull('user_agent')->count());
    }
}
