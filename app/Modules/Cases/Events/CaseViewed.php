<?php

namespace App\Modules\Cases\Events;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class CaseViewed
{
    use Dispatchable;

    public function __construct(public readonly CaseFile $case, public readonly User $viewer) {}
}
