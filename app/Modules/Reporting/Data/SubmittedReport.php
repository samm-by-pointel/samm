<?php

namespace App\Modules\Reporting\Data;

use App\Modules\Reporting\Models\Report;

/**
 * Result of a submission. The plain secret exists only here, for the single
 * response that shows it to the reporting person.
 */
final readonly class SubmittedReport
{
    public function __construct(
        public Report $report,
        public string $reference,
        public string $secret,
    ) {}
}
