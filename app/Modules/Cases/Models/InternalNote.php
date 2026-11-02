<?php

namespace App\Modules\Cases\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Users\Models\User;
use Database\Factories\InternalNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * A note written by the team about a case. Internal only, append-only.
 *
 * @property int $case_id
 * @property int|null $author_user_id
 * @property string $body
 */
#[Fillable(['case_id', 'author_user_id', 'body', 'created_at'])]
#[UseFactory(InternalNoteFactory::class)]
class InternalNote extends Model
{
    /** @use HasFactory<InternalNoteFactory> */
    use HasFactory, HasPublicIdentifier;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'body' => 'encrypted',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Internal notes cannot be modified.');
        });

        static::deleting(function (): never {
            throw new LogicException('Internal notes cannot be deleted.');
        });
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
