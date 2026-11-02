<?php

namespace Database\Factories;

use App\Modules\IdentityVault\Models\ReportIdentity;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Reporting\Models\Report;
use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fictional reports for tests and demonstrations only. Wording stays neutral
 * and generic: no real situation is ever described here.
 *
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => null,
            'report_category_id' => ReportCategory::factory(),
            'confidentiality_level' => ConfidentialityLevel::Anonymous,
            'pseudonym' => null,
            'description' => 'Description fictive d\'une situation, rédigée pour les tests : '.fake()->sentence(12),
            'facts_date' => null,
            'facts_period' => null,
            'location' => null,
            'people_involved' => null,
            'witnesses' => null,
            'is_recurring' => null,
            'is_ongoing' => null,
            'immediate_danger' => null,
            'locale' => 'fr',
            'submitted_at' => now(),
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes): array => [
            'confidentiality_level' => ConfidentialityLevel::Anonymous,
            'pseudonym' => null,
        ]);
    }

    public function pseudonymous(string $pseudonym = 'Alouette'): static
    {
        return $this->state(fn (array $attributes): array => [
            'confidentiality_level' => ConfidentialityLevel::Pseudonymous,
            'pseudonym' => $pseudonym,
        ]);
    }

    public function confidential(): static
    {
        return $this->state(fn (array $attributes): array => [
            'confidentiality_level' => ConfidentialityLevel::Confidential,
            'pseudonym' => null,
        ]);
    }

    public function withIdentity(): static
    {
        return $this->confidential()->has(ReportIdentity::factory(), 'identity');
    }

    public function detailed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'facts_date' => now()->subMonth()->toDateString(),
            'facts_period' => 'Entre juin et août, surtout le soir (fictif).',
            'location' => 'Lieu fictif de test',
            'people_involved' => 'Personne mise en cause fictive (fonction fictive)',
            'witnesses' => 'Aucun témoin (fictif)',
            'is_recurring' => true,
            'is_ongoing' => true,
        ]);
    }

    public function immediateDanger(): static
    {
        return $this->state(fn (array $attributes): array => ['immediate_danger' => true]);
    }
}
