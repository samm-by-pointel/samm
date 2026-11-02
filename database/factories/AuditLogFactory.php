<?php

namespace Database\Factories;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'action' => AuditAction::LoginSucceeded,
            'actor_id' => null,
            'actor_kind' => ActorKind::System,
            'subject_type' => null,
            'subject_id' => null,
            'context' => null,
            'ip_address' => null,
            'created_at' => now(),
        ];
    }

    public function old(int $days = 45): static
    {
        return $this->state(fn (array $attributes): array => ['created_at' => now()->subDays($days)]);
    }

    public function withIp(string $ip = '203.0.113.10'): static
    {
        return $this->state(fn (array $attributes): array => ['ip_address' => $ip, 'actor_kind' => ActorKind::User]);
    }
}
