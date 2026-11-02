<?php

namespace App\Modules\Tracking\Events;

use App\Modules\Reporting\Models\Report;
use Illuminate\Foundation\Events\Dispatchable;

class TrackingAccessFailed
{
    use Dispatchable;

    /**
     * The report is null when the typed reference matches nothing.
     */
    public function __construct(public readonly ?Report $report) {}
}
