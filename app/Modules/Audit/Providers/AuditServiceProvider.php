<?php

namespace App\Modules\Audit\Providers;

use App\Modules\Audit\Console\PurgeAuditIpAddresses;
use App\Modules\Audit\Listeners\RecordAuthenticationEvents;
use App\Modules\Audit\Listeners\RecordCaseEvents;
use App\Modules\Audit\Listeners\RecordOrganizationEvents;
use App\Modules\Audit\Listeners\RecordRoleChanges;
use App\Modules\Audit\Listeners\RecordTrackingEvents;
use App\Modules\Audit\Services\AuditLogger;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register the Audit module bindings.
     */
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);
    }

    /**
     * Subscribe to the events of the other modules. Audit depends on nobody
     * and nobody depends on Audit: everything goes through events.
     */
    public function boot(): void
    {
        Event::subscribe(RecordAuthenticationEvents::class);
        Event::subscribe(RecordRoleChanges::class);
        Event::subscribe(RecordCaseEvents::class);
        Event::subscribe(RecordTrackingEvents::class);
        Event::subscribe(RecordOrganizationEvents::class);

        if ($this->app->runningInConsole()) {
            $this->commands([PurgeAuditIpAddresses::class]);
        }
    }
}
