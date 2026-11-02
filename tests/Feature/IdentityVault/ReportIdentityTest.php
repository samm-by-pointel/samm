<?php

namespace Tests\Feature\IdentityVault;

use App\Modules\IdentityVault\Models\ReportIdentity;
use App\Modules\Reporting\Models\Report;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_identity_lives_in_its_own_table_and_is_encrypted(): void
    {
        $report = Report::factory()->withIdentity()->create();
        $identity = $report->identity;

        $this->assertNotNull($identity);
        $this->assertDatabaseCount('report_identities', 1);

        $raw = DB::table('report_identities')->where('report_id', $report->id)->first();
        $this->assertNotSame($identity->full_name, $raw->full_name);
        $this->assertNotSame($identity->email, $raw->email);
        $this->assertStringNotContainsString('@', (string) $raw->email);

        $this->assertSame($identity->full_name, ReportIdentity::query()->findOrFail($identity->id)->full_name);
    }

    public function test_report_content_table_never_holds_the_identity(): void
    {
        $report = Report::factory()->withIdentity()->create();

        $row = (array) DB::table('reports')->where('id', $report->id)->first();

        $this->assertArrayNotHasKey('full_name', $row);
        $this->assertArrayNotHasKey('email', $row);
        $this->assertArrayNotHasKey('phone', $row);
    }

    public function test_at_most_one_identity_per_report(): void
    {
        $report = Report::factory()->withIdentity()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        ReportIdentity::factory()->create(['report_id' => $report->id]);
    }

    public function test_anonymous_report_has_no_identity_and_relation_is_not_loaded_by_default(): void
    {
        $report = Report::factory()->anonymous()->create();

        $this->assertNull($report->identity);
        $this->assertFalse(Report::query()->findOrFail($report->id)->relationLoaded('identity'));
    }

    public function test_deleting_the_report_deletes_its_identity(): void
    {
        $report = Report::factory()->withIdentity()->create();

        $report->delete();

        $this->assertDatabaseCount('report_identities', 0);
    }

    public function test_masked_representation_reveals_nothing(): void
    {
        $identity = ReportIdentity::factory()->make(['full_name' => 'Nom Fictif']);

        $this->assertStringNotContainsString('Fictif', $identity->masked());
    }
}
