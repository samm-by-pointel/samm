<?php

namespace App\Modules\Messaging\Enums;

use App\Modules\Reporting\Models\Report;

/**
 * Who wrote a message: the reporting person or a member of the organization.
 */
enum MessageAuthor: string
{
    case Reporter = 'reporter';
    case Staff = 'staff';

    /**
     * Signature shown in the public tracking space: never a staff member's name (charter §12.8).
     */
    public function publicLabel(): string
    {
        return match ($this) {
            self::Reporter => __('messaging.authors.public.reporter'),
            self::Staff => __('messaging.authors.public.staff'),
        };
    }

    /**
     * Signature shown in the manager's space: the pseudonym or a neutral wording for
     * the reporting person, the role for the team (never the full name by default).
     */
    public function staffLabel(Report $report): string
    {
        return match ($this) {
            self::Reporter => $report->displayName(),
            self::Staff => __('messaging.authors.staff.staff'),
        };
    }
}
