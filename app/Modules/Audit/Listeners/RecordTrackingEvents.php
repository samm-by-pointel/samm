<?php

namespace App\Modules\Audit\Listeners;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Services\AuditLogger;
use App\Modules\Tracking\Events\TrackingAccessFailed;
use App\Modules\Tracking\Events\TrackingAccessSucceeded;
use Illuminate\Events\Dispatcher;

/**
 * Records accesses to the public tracking space: the report when known,
 * never the codes typed, never an IP address.
 */
class RecordTrackingEvents
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(TrackingAccessSucceeded::class, [$this, 'handleSucceeded']);
        $events->listen(TrackingAccessFailed::class, [$this, 'handleFailed']);
    }

    public function handleSucceeded(TrackingAccessSucceeded $event): void
    {
        $this->audit->record(AuditAction::TrackingAccessSucceeded, $event->report, [], null, ActorKind::Reporter);
    }

    public function handleFailed(TrackingAccessFailed $event): void
    {
        $this->audit->record(
            AuditAction::TrackingAccessFailed,
            $event->report,
            ['known_reference' => $event->report !== null],
            null,
            ActorKind::Reporter,
        );
    }
}
