<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Services\AuditLogger;
use App\Modules\Organizations\Events\EmergencyContactChanged;
use App\Modules\Organizations\Events\OrganizationSettingsChanged;
use App\Modules\Reporting\Events\ReportCategoryChanged;
use Illuminate\Events\Dispatcher;

/**
 * Records administration changes: which fields or items changed, by whom.
 */
class RecordOrganizationEvents
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(OrganizationSettingsChanged::class, [$this, 'handleSettings']);
        $events->listen(EmergencyContactChanged::class, [$this, 'handleEmergencyContact']);
        $events->listen(ReportCategoryChanged::class, [$this, 'handleCategory']);
    }

    public function handleSettings(OrganizationSettingsChanged $event): void
    {
        $this->audit->record(AuditAction::SettingsUpdated, $event->organization, ['fields' => $event->changedFields], $event->actor);
    }

    public function handleEmergencyContact(EmergencyContactChanged $event): void
    {
        $this->audit->record(AuditAction::EmergencyContactChanged, $event->contact, ['operation' => $event->operation, 'contact' => $event->contact->uuid], $event->actor);
    }

    public function handleCategory(ReportCategoryChanged $event): void
    {
        $this->audit->record(AuditAction::CategoryChanged, $event->category, ['operation' => $event->operation, 'category' => $event->category->uuid], $event->actor);
    }
}
