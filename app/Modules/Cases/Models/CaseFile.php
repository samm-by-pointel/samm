<?php

namespace App\Modules\Cases\Models;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Policies\CasePolicy;
use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Models\CaseStatusHistory;
use Database\Factories\CaseFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The organization's handling of one report (table `cases`; the class is
 * named CaseFile because `case` is a reserved word in PHP).
 *
 * @property int $report_id
 * @property CaseStatus $status
 * @property CasePriority $priority
 * @property int|null $assigned_to_user_id
 * @property Carbon|null $assigned_at
 * @property Carbon|null $closed_at
 * @property string|null $closure_reason
 * @property Carbon|null $archived_at
 * @property Carbon|null $last_activity_at
 */
#[Table('cases')]
#[Fillable(['report_id', 'status', 'priority', 'assigned_to_user_id', 'assigned_at', 'closed_at', 'closure_reason', 'archived_at', 'last_activity_at'])]
#[UseFactory(CaseFileFactory::class)]
#[UsePolicy(CasePolicy::class)]
class CaseFile extends Model
{
    /** @use HasFactory<CaseFileFactory> */
    use HasFactory, HasPublicIdentifier;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
        'priority' => 'normal',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
            'priority' => CasePriority::class,
            'assigned_at' => 'datetime',
            'closed_at' => 'datetime',
            'closure_reason' => 'encrypted',
            'archived_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CaseStatusHistory::class, 'case_id')->orderBy('id');
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(InternalNote::class, 'case_id')->orderBy('id');
    }

    public function isAssigned(): bool
    {
        return $this->assigned_to_user_id !== null;
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->assigned_to_user_id === $user->getKey();
    }

    #[Scope]
    protected function open(Builder $query): Builder
    {
        return $query->whereNotIn('status', [CaseStatus::Closed->value, CaseStatus::Archived->value]);
    }

    #[Scope]
    protected function assignedTo(Builder $query, User $user): Builder
    {
        return $query->where('assigned_to_user_id', $user->getKey());
    }

    #[Scope]
    protected function unassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to_user_id');
    }

    /**
     * Cases the user may see, mirroring CasePolicy::view(): everything with
     * `cases.view_unassigned`, only one's own with `cases.view_assigned`, nothing otherwise.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasPermission(Permission::ViewUnassignedCases)) {
            return $query;
        }

        if ($user->hasPermission(Permission::ViewAssignedCases)) {
            return $query->where('assigned_to_user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }
}
