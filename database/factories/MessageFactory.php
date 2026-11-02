<?php

namespace Database\Factories;

use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Models\Message;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'author_type' => MessageAuthor::Reporter,
            'author_user_id' => null,
            'body' => 'Message fictif de test : '.fake()->sentence(8),
            'read_by_reporter_at' => null,
            'read_by_staff_at' => null,
            'created_at' => now(),
        ];
    }

    public function fromReporter(): static
    {
        return $this->state(fn (array $attributes): array => ['author_type' => MessageAuthor::Reporter, 'author_user_id' => null]);
    }

    public function fromStaff(User $user): static
    {
        return $this->state(fn (array $attributes): array => ['author_type' => MessageAuthor::Staff, 'author_user_id' => $user->getKey()]);
    }
}
