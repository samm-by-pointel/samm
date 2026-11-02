<?php

namespace Database\Factories;

use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fictional organization data only: never a real customer.
 *
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'service_name' => null,
            'logo_path' => null,
            'primary_color' => null,
            'secondary_color' => null,
            'neutral_title' => null,
            'quick_exit_url' => null,
            'default_locale' => 'fr',
        ];
    }

    public function withBranding(string $primary = '#0E5A55', string $secondary = '#A84B14'): static
    {
        return $this->state(fn (array $attributes): array => [
            'primary_color' => $primary,
            'secondary_color' => $secondary,
        ]);
    }

    public function withNeutralTitle(string $title = 'Espace information'): static
    {
        return $this->state(fn (array $attributes): array => ['neutral_title' => $title]);
    }

    public function withServiceName(): static
    {
        return $this->state(fn (array $attributes): array => [
            'service_name' => ['fr' => 'Cellule d\'écoute', 'en' => 'Listening unit'],
        ]);
    }

    public function withQuickExitUrl(string $url = 'https://exit.organisation.example'): static
    {
        return $this->state(fn (array $attributes): array => ['quick_exit_url' => $url]);
    }
}
