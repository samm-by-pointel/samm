<?php

namespace Database\Factories;

use App\Modules\Organizations\Models\EmergencyContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fictional emergency contacts for tests only; real numbers come from the administrator.
 *
 * @extends Factory<EmergencyContact>
 */
class EmergencyContactFactory extends Factory
{
    protected $model = EmergencyContact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => ['fr' => 'Service fictif', 'en' => 'Fictional service'],
            'phone_number' => (string) fake()->numberBetween(100, 999),
            'position' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
