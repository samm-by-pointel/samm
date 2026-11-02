<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Services\AuditLogger;
use App\Modules\Cases\Events\CaseAssigned;
use App\Modules\Cases\Events\CasePriorityChanged;
use App\Modules\Cases\Events\CaseViewed;
use App\Modules\IdentityVault\Events\IdentityRevealed;
use App\Modules\Reporting\Events\ReportSubmitted;
use App\Modules\Workflow\Events\CaseStatusChanged;
use Illuminate\Events\Dispatcher;

/**
 * Records case events. Only statuses and references: never the comment, the
 * closure reason or any report content.
 */
class RecordCaseEvents
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(CaseStatusChanged::class, [$this, 'handleStatusChanged']);
        $events->listen(ReportSubmitted::class, [$this, 'handleReportSubmitted']);
        $events->listen(CaseViewed::class, [$this, 'handleCaseViewed']);
        $events->listen(IdentityRevealed::class, [$this, 'handleIdentityRevealed']);
        $events->listen(CaseAssigned::class, [$this, 'handleAssigned']);
        $events->listen(CasePriorityChanged::class, [$this, 'handlePriorityChanged']);
    }

    /**
     * A report was submitted by a reporting person: no account, no IP, the reference only.
     */
    public function handleReportSubmitted(ReportSubmitted $event): void
    {
        $this->audit->record(
            AuditAction::ReportSubmitted,
            $event->report,
            ['reference' => $event->report->reference],
            null,
            ActorKind::Reporter,
        );
    }

    public function handleCaseViewed(CaseViewed $event): void
    {
        $this->audit->record(AuditAction::CaseViewed, $event->case, [], $event->viewer);
    }

    /**
     * The justification is recorded; the identity itself never is.
     */
    public function handleIdentityRevealed(IdentityRevealed $event): void
    {
        $this->audit->record(AuditAction::IdentityViewed, $event->case, ['reason' => $event->reason], $event->viewer);
    }

    public function handleAssigned(CaseAssigned $event): void
    {
        $action = match (true) {
            $event->assignee === null => AuditAction::CaseUnassigned,
            $event->previousAssignee === null => AuditAction::CaseAssigned,
            default => AuditAction::CaseReassigned,
        };

        $this->audit->record($action, $event->case, [
            'from' => $event->previousAssignee?->uuid,
            'to' => $event->assignee?->uuid,
        ], $event->actor);
    }

    public function handlePriorityChanged(CasePriorityChanged $event): void
    {
        $this->audit->record(AuditAction::CasePriorityChanged, $event->case, ['from' => $event->from->value, 'to' => $event->to->value], $event->actor);
    }

    public function handleStatusChanged(CaseStatusChanged $event): void
    {
        $this->audit->record(
            AuditAction::CaseStatusChanged,
            $event->case,
            ['from' => $event->from->value, 'to' => $event->to->value],
            $event->actor,
        );
    }
}
