<?php

namespace Tests\Feature\Audit;

use App\Modules\AccessControl\Models\Role;
use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Audit\Services\AuditLogger;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_records_action_actor_subject_and_context(): void
    {
        $actor = User::factory()->create();
        $role = Role::factory()->create();

        $entry = app(AuditLogger::class)->record(AuditAction::RoleAssigned, $role, ['role' => $role->key], $actor);

        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertSame(AuditAction::RoleAssigned, $entry->action);
        $this->assertTrue($entry->actor->is($actor));
        $this->assertSame(ActorKind::User, $entry->actor_kind);
        $this->assertTrue($entry->subject->is($role));
        $this->assertSame(['role' => $role->key], $entry->context);
        $this->assertNotNull($entry->created_at);
        $this->assertNotNull($entry->uuid);
    }

    public function test_system_entry_has_no_actor_and_no_ip_address(): void
    {
        $entry = app(AuditLogger::class)->record(AuditAction::LoginFailed, null, ['reason' => 'unknown_account']);

        $this->assertNull($entry->actor_id);
        $this->assertSame(ActorKind::System, $entry->actor_kind);
        $this->assertNull($entry->ip_address);
    }

    public function test_entries_cannot_be_modified(): void
    {
        $entry = AuditLog::factory()->create();

        $this->expectException(LogicException::class);

        $entry->update(['context' => ['tampered' => true]]);
    }

    public function test_entries_cannot_be_deleted(): void
    {
        $entry = AuditLog::factory()->create();

        $this->expectException(LogicException::class);

        $entry->delete();
    }

    public function test_context_keys_that_could_carry_report_content_are_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(AuditLogger::class)->record(AuditAction::LoginFailed, null, ['description' => 'récit confidentiel']);
    }

    public function test_long_free_text_values_are_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(AuditLogger::class)->record(AuditAction::LoginFailed, null, ['reference' => str_repeat('a', 200)]);
    }

    public function test_non_scalar_context_values_are_refused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(AuditLogger::class)->record(AuditAction::LoginFailed, null, ['nested' => ['deep' => ['x' => 1]]]);
    }
}
