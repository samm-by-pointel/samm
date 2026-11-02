<?php

namespace App\Modules\Reporting\Events;

use App\Modules\Reporting\Models\Report;
use Illuminate\Foundation\Events\Dispatchable;

class ReportSubmitted
{
    use Dispatchable;

    public function __construct(public readonly Report $report) {}
}
