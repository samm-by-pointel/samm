<?php

namespace App\Modules\IdentityVault\Events;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class IdentityRevealed
{
    use Dispatchable;

    public function __construct(public readonly CaseFile $case, public readonly User $viewer, public readonly string $reason) {}
}
