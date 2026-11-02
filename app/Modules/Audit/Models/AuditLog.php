<?php

namespace App\Modules\Audit\Models;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Users\Models\User;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * One line of the append-only audit trail.
 *
 * @property AuditAction $action
 * @property int|null $actor_id
 * @property ActorKind $actor_kind
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property array<string, mixed>|null $context
 * @property string|null $ip_address
 * @property Carbon $created_at
 */
#[Fillable(['action', 'actor_id', 'actor_kind', 'subject_type', 'subject_id', 'context', 'ip_address', 'created_at'])]
#[UseFactory(AuditLogFactory::class)]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory, HasPublicIdentifier;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'actor_kind' => ActorKind::class,
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The audit trail is append-only: rows are never modified or removed.
     * Only the scheduled IP purge may write through the query builder.
     */
    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Audit log entries cannot be modified.');
        });

        static::deleting(function (): never {
            throw new LogicException('Audit log entries cannot be deleted.');
        });
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
