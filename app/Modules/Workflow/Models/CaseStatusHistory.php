<?php

namespace App\Modules\Workflow\Models;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use Database\Factories\CaseStatusHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One status change of a case. Append-only, like the audit log.
 *
 * @property int $case_id
 * @property CaseStatus|null $from_status
 * @property CaseStatus $to_status
 * @property int|null $changed_by_user_id
 * @property string|null $comment
 */
#[Fillable(['case_id', 'from_status', 'to_status', 'changed_by_user_id', 'comment', 'created_at'])]
#[UseFactory(CaseStatusHistoryFactory::class)]
class CaseStatusHistory extends Model
{
    /** @use HasFactory<CaseStatusHistoryFactory> */
    use HasFactory, HasPublicIdentifier;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => CaseStatus::class,
            'to_status' => CaseStatus::class,
            'comment' => 'encrypted',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Status history entries cannot be modified.');
        });

        static::deleting(function (): never {
            throw new LogicException('Status history entries cannot be deleted.');
        });
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseFile::class, 'case_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
