<?php

namespace App\Modules\Cases\Events;

use App\Modules\Cases\Enums\CasePriority;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class CasePriorityChanged
{
    use Dispatchable;

    public function __construct(
        public readonly CaseFile $case,
        public readonly CasePriority $from,
        public readonly CasePriority $to,
        public readonly User $actor,
    ) {}
}
