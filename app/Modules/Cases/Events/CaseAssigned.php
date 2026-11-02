<?php

namespace App\Modules\Cases\Events;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class CaseAssigned
{
    use Dispatchable;

    public function __construct(
        public readonly CaseFile $case,
        public readonly ?User $previousAssignee,
        public readonly ?User $assignee,
        public readonly User $actor,
    ) {}
}
