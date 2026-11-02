<?php

namespace Database\Factories;

use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Models\TrackingCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Tracking credential with a known fictional secret, for tests only.
 *
 * @extends Factory<TrackingCredential>
 */
class TrackingCredentialFactory extends Factory
{
    public const SECRET = 'RF3M-W8TZ-K2PA-6HNE';

    protected $model = TrackingCredential::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'secret_hash' => TrackingCredential::hashSecret(self::SECRET),
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_used_at' => null,
        ];
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes): array => ['locked_until' => now()->addMinutes(10)]);
    }
}
