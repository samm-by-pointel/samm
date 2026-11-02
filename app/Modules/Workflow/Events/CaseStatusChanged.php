<?php

namespace App\Modules\Workflow\Events;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use App\Modules\Workflow\Enums\CaseStatus;
use Illuminate\Foundation\Events\Dispatchable;

class CaseStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly CaseFile $case,
        public readonly CaseStatus $from,
        public readonly CaseStatus $to,
        public readonly ?User $actor,
    ) {}
}
