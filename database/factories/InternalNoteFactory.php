<?php

namespace Database\Factories;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Cases\Models\InternalNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternalNote>
 */
class InternalNoteFactory extends Factory
{
    protected $model = InternalNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_id' => CaseFile::factory(),
            'author_user_id' => null,
            'body' => 'Note interne fictive : '.fake()->sentence(6),
            'created_at' => now(),
        ];
    }
}
