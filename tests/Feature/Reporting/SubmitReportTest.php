<?php

namespace Tests\Feature\Reporting;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\IdentityVault\Models\ReportIdentity;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Reporting\Actions\SubmitReport;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Reporting\Models\Report;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubmitReportTest extends TestCase
{
    use RefreshDatabase;

    private ReportCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = ReportCategory::factory()->create();
    }

    /**
     * @param  array<string, mixed>  $confidentiality
     * @return array<string, array<string, mixed>>
     */
    private function draft(array $confidentiality = ['confidentiality_level' => 'anonymous'], bool $identityDisclosure = false): array
    {
        return [
            'situation' => ['category' => $this->category->uuid],
            'confidentialite' => $confidentiality,
            'description' => ['description' => 'Récit fictif de test.', 'location' => 'Lieu fictif', 'immediate_danger' => false],
            'personnes' => ['people_involved' => 'Fonction fictive', 'witnesses' => null],
            'consentement' => ['consent_processing' => true, 'consent_contact' => false, 'consent_identity_disclosure' => $identityDisclosure],
        ];
    }

    public function test_anonymous_submission_creates_report_case_consents_and_secret_without_identity(): void
    {
        $result = app(SubmitReport::class)->handle($this->draft(), 'fr');

        $report = $result->report->fresh();
        $this->assertDatabaseCount('reports', 1);
        $this->assertSame(ConfidentialityLevel::Anonymous, $report->confidentiality_level);
        $this->assertNull($report->pseudonym);
        $this->assertSame('fr', $report->locale);
        $this->assertNotNull($report->submitted_at);
        $this->assertMatchesRegularExpression('/^SMM-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $result->reference);
        $this->assertSame($report->reference, $result->reference);

        $this->assertDatabaseCount('report_identities', 0);
        $this->assertSame(CaseStatus::New, $report->case->status);
        $this->assertSame(3, $report->consents()->count());
        $this->assertTrue($report->consents()->where('purpose', ConsentPurpose::Processing->value)->sole()->granted);
        $this->assertFalse($report->consents()->where('purpose', ConsentPurpose::Contact->value)->sole()->granted);
        $this->assertSame('2026-11', $report->consents()->first()->wording_version);

        $this->assertTrue($report->trackingCredential->verify($result->secret));
        $this->assertStringNotContainsString(str_replace('-', '', $result->secret), DB::table('tracking_credentials')->value('secret_hash'));
    }

    public function test_confidential_submission_stores_the_identity_apart_and_encrypted(): void
    {
        $result = app(SubmitReport::class)->handle($this->draft([
            'confidentiality_level' => 'confidential',
            'identity_full_name' => 'Nom Fictif',
            'identity_email' => 'fictif@example.test',
            'identity_phone' => null,
        ], true), 'fr');

        $identity = ReportIdentity::query()->where('report_id', $result->report->id)->sole();
        $this->assertSame('Nom Fictif', $identity->full_name);
        $this->assertSame('email', $identity->preferred_contact);
        $this->assertNotSame('Nom Fictif', DB::table('report_identities')->value('full_name'));

        $row = (array) DB::table('reports')->where('id', $result->report->id)->first();
        $this->assertStringNotContainsString('Nom Fictif', json_encode($row));
        $this->assertStringNotContainsString('Récit fictif', (string) $row['description']);
    }

    public function test_pseudonymous_submission_keeps_the_pseudonym_only(): void
    {
        $result = app(SubmitReport::class)->handle($this->draft(['confidentiality_level' => 'pseudonymous', 'pseudonym' => 'Alouette', 'identity_full_name' => 'Ignoré']), 'en');

        $this->assertSame('Alouette', $result->report->pseudonym);
        $this->assertSame('en', $result->report->locale);
        $this->assertDatabaseCount('report_identities', 0);
    }

    public function test_submission_is_audited_as_a_reporting_person_without_ip_or_content(): void
    {
        $result = app(SubmitReport::class)->handle($this->draft(), 'fr');

        $entry = AuditLog::query()->where('action', AuditAction::ReportSubmitted->value)->sole();
        $this->assertSame(ActorKind::Reporter, $entry->actor_kind);
        $this->assertNull($entry->actor_id);
        $this->assertNull($entry->ip_address);
        $this->assertTrue($entry->subject->is($result->report));
        $this->assertSame(['reference' => $result->reference], $entry->context);
    }

    public function test_inactive_category_aborts_before_writing_anything(): void
    {
        $this->category->update(['is_active' => false]);

        try {
            app(SubmitReport::class)->handle($this->draft(), 'fr');
            $this->fail('Une catégorie inactive doit être refusée.');
        } catch (ModelNotFoundException) {
            // attendu
        }

        $this->assertDatabaseCount('reports', 0);
        $this->assertDatabaseCount('cases', 0);
        $this->assertDatabaseCount('tracking_credentials', 0);
    }

    public function test_failure_inside_the_transaction_writes_nothing(): void
    {
        $draft = $this->draft();
        $draft['description']['description'] = null;

        try {
            app(SubmitReport::class)->handle($draft, 'fr');
            $this->fail('Une description absente doit échouer en base.');
        } catch (\Throwable) {
            // attendu : contrainte NOT NULL
        }

        $this->assertDatabaseCount('reports', 0);
        $this->assertDatabaseCount('cases', 0);
        $this->assertDatabaseCount('consents', 0);
        $this->assertSame(0, Report::query()->count());
    }
}
