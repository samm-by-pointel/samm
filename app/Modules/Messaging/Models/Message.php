<?php

namespace App\Modules\Messaging\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Users\Models\User;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * One message of a conversation. The body is encrypted at rest; a message is
 * never edited after being sent (read markers are set through the query builder).
 *
 * @property int $conversation_id
 * @property MessageAuthor $author_type
 * @property int|null $author_user_id
 * @property string $body
 * @property Carbon|null $read_by_reporter_at
 * @property Carbon|null $read_by_staff_at
 */
#[Fillable(['conversation_id', 'author_type', 'author_user_id', 'body', 'read_by_reporter_at', 'read_by_staff_at', 'created_at'])]
#[UseFactory(MessageFactory::class)]
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory, HasPublicIdentifier;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'author_type' => MessageAuthor::class,
            'body' => 'encrypted',
            'read_by_reporter_at' => 'datetime',
            'read_by_staff_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Messages cannot be modified once sent.');
        });

        static::deleting(function (): never {
            throw new LogicException('Messages cannot be deleted.');
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function isFromReporter(): bool
    {
        return $this->author_type === MessageAuthor::Reporter;
    }
}
