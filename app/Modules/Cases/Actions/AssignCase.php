<?php

namespace App\Modules\Cases\Actions;

use App\Modules\Cases\Events\CaseAssigned;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;

/**
 * Assigns, reassigns or unassigns a case (`$assignee` null). The only path
 * that changes the assignment, so that every change is announced and audited.
 */
class AssignCase
{
    public function handle(CaseFile $case, ?User $assignee, User $actor): CaseFile
    {
        $previous = $case->assignee;

        if ($previous?->getKey() === $assignee?->getKey()) {
            return $case;
        }

        $case->forceFill([
            'assigned_to_user_id' => $assignee?->getKey(),
            'assigned_at' => $assignee ? now() : null,
            'last_activity_at' => now(),
        ])->save();

        $case->unsetRelation('assignee');

        CaseAssigned::dispatch($case, $previous, $assignee, $actor);

        return $case;
    }
}
