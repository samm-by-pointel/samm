<?php

namespace Database\Factories;

use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseFile>
 */
class CaseFileFactory extends Factory
{
    protected $model = CaseFile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'status' => CaseStatus::New,
            'priority' => CasePriority::Normal,
            'assigned_to_user_id' => null,
            'assigned_at' => null,
            'closed_at' => null,
            'closure_reason' => null,
            'archived_at' => null,
            'last_activity_at' => now(),
        ];
    }

    public function withStatus(CaseStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
            'closed_at' => $status->isOpen() ? null : now(),
            'archived_at' => $status->isArchived() ? now() : null,
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'assigned_to_user_id' => $user->getKey(),
            'assigned_at' => now(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes): array => ['priority' => CasePriority::High]);
    }
}
