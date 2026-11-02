<?php

namespace App\Modules\Reporting\Events;

use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class ReportCategoryChanged
{
    use Dispatchable;

    /**
     * @param  'created'|'updated'  $operation
     */
    public function __construct(public readonly ReportCategory $category, public readonly string $operation, public readonly User $actor) {}
}
