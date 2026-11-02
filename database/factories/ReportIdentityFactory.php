<?php

namespace Database\Factories;

use App\Modules\IdentityVault\Models\ReportIdentity;
use App\Modules\Reporting\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fictional identities only.
 *
 * @extends Factory<ReportIdentity>
 */
class ReportIdentityFactory extends Factory
{
    protected $model = ReportIdentity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => Report::factory()->confidential(),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+221 7# ### ## ##'),
            'preferred_contact' => 'email',
        ];
    }
}
