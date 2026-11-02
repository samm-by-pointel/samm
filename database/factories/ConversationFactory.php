<?php

namespace Database\Factories;

use App\Modules\Messaging\Models\Conversation;
use App\Modules\Reporting\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'last_message_at' => null,
        ];
    }
}
