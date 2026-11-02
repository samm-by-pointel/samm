<?php

namespace Tests\Feature\Audit;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_successful_sign_in_is_recorded_with_the_account_and_its_ip_address(): void
    {
        $user = User::factory()->create(['email' => 'gestionnaire@example.test']);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'password'])
            ->assertRedirect('/gestion');

        $entry = AuditLog::query()->where('action', AuditAction::LoginSucceeded->value)->sole();

        $this->assertTrue($entry->actor->is($user));
        $this->assertSame(ActorKind::User, $entry->actor_kind);
        $this->assertTrue($entry->subject->is($user));
        $this->assertSame('203.0.113.10', $entry->ip_address);
    }

    public function test_failed_sign_in_is_recorded_without_the_typed_email_address(): void
    {
        $user = User::factory()->create(['email' => 'gestionnaire@example.test']);

        $this->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'mauvais']);

        $entry = AuditLog::query()->where('action', AuditAction::LoginFailed->value)->sole();

        $this->assertNull($entry->actor_id);
        $this->assertTrue($entry->subject->is($user));
        $this->assertSame('invalid_credentials', $entry->context['reason']);
        $this->assertStringNotContainsString('gestionnaire@example.test', json_encode($entry->context));
        $this->assertNull($entry->ip_address);
    }

    public function test_failed_sign_in_on_an_unknown_account_keeps_no_trace_of_the_address(): void
    {
        $this->post(route('login'), ['email' => 'inconnu@example.test', 'password' => 'mauvais']);

        $entry = AuditLog::query()->where('action', AuditAction::LoginFailed->value)->sole();

        $this->assertNull($entry->subject_id);
        $this->assertSame('unknown_account', $entry->context['reason']);
        $this->assertStringNotContainsString('inconnu', json_encode($entry->context));
    }

    public function test_failed_sign_in_on_an_inactive_account_gives_the_reason(): void
    {
        $user = User::factory()->inactive()->create(['email' => 'inactif@example.test']);

        $this->post(route('login'), ['email' => 'inactif@example.test', 'password' => 'password']);

        $entry = AuditLog::query()->where('action', AuditAction::LoginFailed->value)->sole();

        $this->assertTrue($entry->subject->is($user));
        $this->assertSame('inactive', $entry->context['reason']);
    }

    public function test_lockout_is_recorded(): void
    {
        User::factory()->create(['email' => 'gestionnaire@example.test']);

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            $this->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'mauvais']);
        }

        $this->assertSame(5, AuditLog::query()->where('action', AuditAction::LoginFailed->value)->count());
        $this->assertSame(1, AuditLog::query()->where('action', AuditAction::LockedOut->value)->count());
    }

    public function test_sign_out_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'));

        $entry = AuditLog::query()->where('action', AuditAction::Logout->value)->sole();

        $this->assertTrue($entry->actor->is($user));
    }
}
