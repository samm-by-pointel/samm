<?php

namespace Tests\Feature\Audit;

use App\Modules\Audit\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeAuditIpAddressesTest extends TestCase
{
    use RefreshDatabase;

    public function test_ip_addresses_older_than_the_retention_period_are_removed_and_recent_ones_kept(): void
    {
        config(['samm.security.ip_retention_days' => 30]);

        $old = AuditLog::factory()->withIp('203.0.113.10')->old(45)->create();
        $recent = AuditLog::factory()->withIp('203.0.113.11')->old(10)->create();
        $oldWithoutIp = AuditLog::factory()->old(60)->create();

        $this->artisan('audit:purge-ip-addresses')
            ->expectsOutputToContain('Adresses IP effacées : 1')
            ->assertSuccessful();

        $this->assertNull($old->fresh()->ip_address);
        $this->assertSame('203.0.113.11', $recent->fresh()->ip_address);
        $this->assertNull($oldWithoutIp->fresh()->ip_address);
        $this->assertSame($old->action, $old->fresh()->action);
        $this->assertSame($old->created_at->toIso8601String(), $old->fresh()->created_at->toIso8601String());
        $this->assertDatabaseCount('audit_logs', 3);
    }

    public function test_retention_period_follows_the_configuration(): void
    {
        config(['samm.security.ip_retention_days' => 7]);

        $entry = AuditLog::factory()->withIp()->old(10)->create();

        $this->artisan('audit:purge-ip-addresses')->assertSuccessful();

        $this->assertNull($entry->fresh()->ip_address);
    }
}
