<?php

namespace Database\Factories;

use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Privacy\Models\Consent;
use App\Modules\Reporting\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consent>
 */
class ConsentFactory extends Factory
{
    protected $model = Consent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'purpose' => ConsentPurpose::Processing,
            'granted' => true,
            'granted_at' => now(),
            'wording_version' => '2026-11',
        ];
    }

    public function refused(): static
    {
        return $this->state(fn (array $attributes): array => ['granted' => false, 'granted_at' => null]);
    }

    public function forPurpose(ConsentPurpose $purpose): static
    {
        return $this->state(fn (array $attributes): array => ['purpose' => $purpose]);
    }
}
