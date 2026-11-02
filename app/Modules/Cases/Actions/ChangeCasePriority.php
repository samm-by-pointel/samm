<?php

namespace App\Modules\Cases\Actions;

use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Events\CasePriorityChanged;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;

class ChangeCasePriority
{
    public function handle(CaseFile $case, CasePriority $priority, User $actor): CaseFile
    {
        $from = $case->priority;

        if ($from === $priority) {
            return $case;
        }

        $case->forceFill(['priority' => $priority, 'last_activity_at' => now()])->save();

        CasePriorityChanged::dispatch($case, $from, $priority, $actor);

        return $case;
    }
}
