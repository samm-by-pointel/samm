<?php

namespace App\Modules\Audit\Console;

use App\Modules\Audit\Models\AuditLog;
use Illuminate\Console\Command;

class PurgeAuditIpAddresses extends Command
{
    protected $signature = 'audit:purge-ip-addresses';

    protected $description = 'Remove IP addresses from audit entries older than the configured retention period';

    /**
     * The only write ever performed on existing audit rows: nulling the IP
     * address once the retention period (samm.security.ip_retention_days) is over.
     */
    public function handle(): int
    {
        $days = max(1, (int) config('samm.security.ip_retention_days', 30));

        $purged = AuditLog::query()
            ->whereNotNull('ip_address')
            ->where('created_at', '<', now()->subDays($days))
            ->toBase()
            ->update(['ip_address' => null]);

        $this->info("Adresses IP effacées : {$purged} (entrées de plus de {$days} jours).");

        return self::SUCCESS;
    }
}
