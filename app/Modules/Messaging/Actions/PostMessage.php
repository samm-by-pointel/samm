<?php

namespace App\Modules\Messaging\Actions;

use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Messaging\Events\MessagePosted;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Models\Message;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The only way to add a message to a conversation, from either side.
 */
class PostMessage
{
    public function handle(Conversation $conversation, MessageAuthor $author, string $body, ?User $staffMember = null): Message
    {
        $message = DB::transaction(function () use ($conversation, $author, $body, $staffMember): Message {
            $message = $conversation->messages()->create([
                'author_type' => $author,
                'author_user_id' => $author === MessageAuthor::Staff ? $staffMember?->getKey() : null,
                'body' => $body,
                'created_at' => now(),
            ]);

            $conversation->forceFill(['last_message_at' => now()])->save();

            return $message;
        });

        MessagePosted::dispatch($message);

        return $message;
    }
}
