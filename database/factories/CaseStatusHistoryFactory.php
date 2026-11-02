<?php

namespace Database\Factories;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Models\CaseStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseStatusHistory>
 */
class CaseStatusHistoryFactory extends Factory
{
    protected $model = CaseStatusHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_id' => CaseFile::factory(),
            'from_status' => CaseStatus::New,
            'to_status' => CaseStatus::InProgress,
            'changed_by_user_id' => null,
            'comment' => null,
            'created_at' => now(),
        ];
    }
}
