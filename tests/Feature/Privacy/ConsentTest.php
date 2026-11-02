<?php

namespace Tests\Feature\Privacy;

use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Privacy\Models\Consent;
use App\Modules\Reporting\Models\Report;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_purpose_is_recorded_separately_with_the_wording_version(): void
    {
        $report = Report::factory()->create();

        foreach (ConsentPurpose::cases() as $purpose) {
            Consent::factory()->forPurpose($purpose)->create(['report_id' => $report->id, 'wording_version' => '2026-11']);
        }

        $consents = $report->consents()->get();

        $this->assertCount(3, $consents);
        $this->assertEqualsCanonicalizing(
            array_map(fn (ConsentPurpose $purpose): string => $purpose->value, ConsentPurpose::cases()),
            $consents->map(fn (Consent $consent): string => $consent->purpose->value)->all(),
        );
        $this->assertSame('2026-11', $consents->first()->wording_version);
    }

    public function test_a_refusal_is_kept_as_such(): void
    {
        $consent = Consent::factory()->forPurpose(ConsentPurpose::IdentityDisclosure)->refused()->create();

        $this->assertFalse($consent->fresh()->granted);
        $this->assertNull($consent->fresh()->granted_at);
    }

    public function test_one_decision_per_purpose_and_report(): void
    {
        $report = Report::factory()->create();
        Consent::factory()->forPurpose(ConsentPurpose::Contact)->create(['report_id' => $report->id]);

        $this->expectException(UniqueConstraintViolationException::class);

        Consent::factory()->forPurpose(ConsentPurpose::Contact)->create(['report_id' => $report->id]);
    }

    public function test_purposes_have_labels_and_descriptions_in_both_languages(): void
    {
        foreach (ConsentPurpose::cases() as $purpose) {
            foreach (['fr', 'en'] as $locale) {
                $this->assertNotSame('reporting.consents.'.$purpose->value.'.label', __('reporting.consents.'.$purpose->value.'.label', [], $locale));
                $this->assertNotSame('reporting.consents.'.$purpose->value.'.description', __('reporting.consents.'.$purpose->value.'.description', [], $locale));
            }
        }
    }
}
