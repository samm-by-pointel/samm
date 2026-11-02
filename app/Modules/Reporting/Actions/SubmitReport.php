<?php

namespace App\Modules\Reporting\Actions;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\IdentityVault\Models\ReportIdentity;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Privacy\Models\Consent;
use App\Modules\Reporting\Data\SubmittedReport;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Reporting\Enums\ReportStep;
use App\Modules\Reporting\Events\ReportSubmitted;
use App\Modules\Reporting\Models\Report;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Tracking\Models\TrackingCredential;
use App\Modules\Tracking\Services\TrackingCodeGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Turns a completed draft into persistent records, in one transaction:
 * report, identity (confidential mode only), consents, case and hashed secret.
 */
class SubmitReport
{
    public function __construct(private readonly TrackingCodeGenerator $codes) {}

    /**
     * @param  array<string, array<string, mixed>>  $steps  Draft data keyed by step slug.
     */
    public function handle(array $steps, string $locale): SubmittedReport
    {
        $situation = $steps[ReportStep::Situation->value] ?? [];
        $confidentiality = $steps[ReportStep::Confidentiality->value] ?? [];
        $description = $steps[ReportStep::Description->value] ?? [];
        $people = $steps[ReportStep::People->value] ?? [];
        $consents = $steps[ReportStep::Consent->value] ?? [];

        $category = ReportCategory::query()->active()->where('uuid', $situation['category'] ?? null)->firstOrFail();
        $level = ConfidentialityLevel::from($confidentiality['confidentiality_level']);
        $secret = $this->codes->secret();

        $report = DB::transaction(function () use ($category, $level, $confidentiality, $description, $people, $consents, $locale, $secret): Report {
            $report = Report::query()->create([
                'reference' => $this->codes->reference(),
                'report_category_id' => $category->getKey(),
                'confidentiality_level' => $level,
                'pseudonym' => $level->requiresPseudonym() ? ($confidentiality['pseudonym'] ?? null) : null,
                'description' => $description['description'],
                'facts_date' => $description['facts_date'] ?? null,
                'facts_period' => $description['facts_period'] ?? null,
                'location' => $description['location'] ?? null,
                'people_involved' => $people['people_involved'] ?? null,
                'witnesses' => $people['witnesses'] ?? null,
                'is_recurring' => $description['is_recurring'] ?? null,
                'is_ongoing' => $description['is_ongoing'] ?? null,
                'immediate_danger' => $description['immediate_danger'] ?? null,
                'locale' => $locale,
                'submitted_at' => now(),
            ]);

            if ($level->allowsIdentity() && ! empty($confidentiality['identity_full_name'])) {
                ReportIdentity::query()->create([
                    'report_id' => $report->getKey(),
                    'full_name' => $confidentiality['identity_full_name'],
                    'email' => $confidentiality['identity_email'] ?? null,
                    'phone' => $confidentiality['identity_phone'] ?? null,
                    'preferred_contact' => ! empty($confidentiality['identity_email']) ? 'email' : (! empty($confidentiality['identity_phone']) ? 'phone' : null),
                ]);
            }

            foreach (ConsentPurpose::cases() as $purpose) {
                $granted = (bool) ($consents['consent_'.$purpose->value] ?? false);

                Consent::query()->create([
                    'report_id' => $report->getKey(),
                    'purpose' => $purpose,
                    'granted' => $granted,
                    'granted_at' => $granted ? now() : null,
                    'wording_version' => (string) config('samm.consent_wording_version'),
                ]);
            }

            CaseFile::query()->create([
                'report_id' => $report->getKey(),
                'last_activity_at' => now(),
            ]);

            Conversation::query()->create(['report_id' => $report->getKey()]);

            TrackingCredential::query()->create([
                'report_id' => $report->getKey(),
                'secret_hash' => TrackingCredential::hashSecret($secret),
            ]);

            return $report;
        });

        ReportSubmitted::dispatch($report);

        return new SubmittedReport($report, (string) $report->reference, $secret);
    }
}
