<?php

namespace Database\Factories;

use App\Modules\Reporting\Models\ReportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCategory>
 */
class ReportCategoryFactory extends Factory
{
    protected $model = ReportCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $word = ucfirst(fake()->unique()->word());

        return [
            'name' => ['fr' => 'Catégorie '.$word, 'en' => 'Category '.$word],
            'description' => null,
            'position' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
