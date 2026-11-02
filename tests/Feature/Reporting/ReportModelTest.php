<?php

namespace Tests\Feature\Reporting;

use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Reporting\Models\Report;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_report_can_be_created_in_each_confidentiality_mode(): void
    {
        $anonymous = Report::factory()->anonymous()->create();
        $pseudonymous = Report::factory()->pseudonymous('Alouette')->create();
        $confidential = Report::factory()->confidential()->create();

        $this->assertSame(ConfidentialityLevel::Anonymous, $anonymous->confidentiality_level);
        $this->assertTrue($anonymous->isAnonymous());
        $this->assertNull($anonymous->pseudonym);
        $this->assertSame('Alouette', $pseudonymous->pseudonym);
        $this->assertSame('Alouette', $pseudonymous->displayName());
        $this->assertFalse($confidential->isAnonymous());
        $this->assertNotNull($anonymous->uuid);
        $this->assertNull($anonymous->reference, 'La référence publique est générée au dépôt (lot 5).');
    }

    public function test_free_text_fields_are_encrypted_at_rest(): void
    {
        $report = Report::factory()->detailed()->create(['description' => 'Texte fictif confidentiel']);

        $raw = DB::table('reports')->where('id', $report->id)->first();

        foreach (['description', 'facts_period', 'location', 'people_involved', 'witnesses'] as $column) {
            $this->assertNotSame($report->{$column}, $raw->{$column}, "La colonne {$column} doit être chiffrée.");
            $this->assertStringNotContainsString('fictif', (string) $raw->{$column});
        }

        $this->assertSame('Texte fictif confidentiel', $report->fresh()->description);
        $this->assertSame('Lieu fictif de test', $report->fresh()->location);
    }

    public function test_only_the_description_is_required(): void
    {
        $report = Report::factory()->create([
            'facts_date' => null, 'facts_period' => null, 'location' => null, 'people_involved' => null,
            'witnesses' => null, 'is_recurring' => null, 'is_ongoing' => null, 'immediate_danger' => null,
        ]);

        $this->assertNull($report->fresh()->location);
        $this->assertNull($report->fresh()->immediate_danger);
        $this->assertNotEmpty($report->description);
    }

    public function test_report_tables_have_no_ip_address_nor_user_agent_column(): void
    {
        foreach (['reports', 'report_identities', 'consents'] as $table) {
            $columns = Schema::getColumnListing($table);

            $this->assertNotContains('ip_address', $columns, $table);
            $this->assertNotContains('user_agent', $columns, $table);
        }
    }

    public function test_anonymous_person_is_named_neutrally(): void
    {
        app()->setLocale('fr');

        $this->assertSame('Personne anonyme', Report::factory()->anonymous()->make()->displayName());
    }

    public function test_category_cannot_be_deleted_while_a_report_uses_it(): void
    {
        $report = Report::factory()->create();

        $this->expectException(QueryException::class);

        $report->category->delete();
    }
}
