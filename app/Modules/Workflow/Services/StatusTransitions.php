<?php

namespace App\Modules\Workflow\Services;

use App\Modules\Workflow\Enums\CaseStatus;
use App\Modules\Workflow\Exceptions\InvalidStatusTransition;

/**
 * Allowed status transitions (validated 1 November 2026). Archived is terminal;
 * a closed case may be archived or reopened.
 */
class StatusTransitions
{
    /**
     * @var array<string, array<int, CaseStatus>>
     */
    private const ALLOWED = [
        'new' => [CaseStatus::ToQualify, CaseStatus::InProgress, CaseStatus::Closed],
        'to_qualify' => [CaseStatus::InProgress, CaseStatus::AwaitingInformation, CaseStatus::Referred, CaseStatus::Closed],
        'in_progress' => [CaseStatus::AwaitingInformation, CaseStatus::Referred, CaseStatus::Resolving, CaseStatus::Closed],
        'awaiting_information' => [CaseStatus::InProgress, CaseStatus::Referred, CaseStatus::Resolving, CaseStatus::Closed],
        'referred' => [CaseStatus::InProgress, CaseStatus::Resolving, CaseStatus::Closed],
        'resolving' => [CaseStatus::InProgress, CaseStatus::Closed],
        'closed' => [CaseStatus::Archived, CaseStatus::InProgress],
        'archived' => [],
    ];

    /**
     * @return array<int, CaseStatus>
     */
    public function allowedFrom(CaseStatus $from): array
    {
        return self::ALLOWED[$from->value];
    }

    public function canTransition(CaseStatus $from, CaseStatus $to): bool
    {
        return in_array($to, $this->allowedFrom($from), true);
    }

    /**
     * @throws InvalidStatusTransition
     */
    public function assertAllowed(CaseStatus $from, CaseStatus $to): void
    {
        if (! $this->canTransition($from, $to)) {
            throw InvalidStatusTransition::between($from, $to);
        }
    }
}
