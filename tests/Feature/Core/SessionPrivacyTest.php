<?php

namespace Tests\Feature\Core;

use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private const IP = '203.0.113.10';

    private const USER_AGENT = 'NavigateurDeTest/1.0';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config(['session.driver' => 'database']);
    }

    public function test_visitor_session_stores_neither_ip_address_nor_user_agent(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => self::IP])
            ->withHeaders(['User-Agent' => self::USER_AGENT])
            ->get(route('public.home'))
            ->assertOk();

        $sessions = DB::table('sessions')->get();

        $this->assertCount(1, $sessions);
        $this->assertNull($sessions->first()->ip_address);
        $this->assertNull($sessions->first()->user_agent);
        $this->assertNull($sessions->first()->user_id);
    }

    public function test_login_page_leaves_no_ip_address_before_authentication(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => self::IP])
            ->withHeaders(['User-Agent' => self::USER_AGENT])
            ->get(route('login'))
            ->assertOk();

        $this->assertSame(0, DB::table('sessions')->whereNotNull('ip_address')->count());
        $this->assertSame(0, DB::table('sessions')->whereNotNull('user_agent')->count());
    }

    public function test_authenticated_internal_account_session_records_ip_address_and_user_agent(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::IP])
            ->withHeaders(['User-Agent' => self::USER_AGENT])
            ->get(route('manage.dashboard'))
            ->assertOk();

        $session = DB::table('sessions')->where('user_id', $user->id)->first();

        $this->assertNotNull($session);
        $this->assertSame(self::IP, $session->ip_address);
        $this->assertSame(self::USER_AGENT, $session->user_agent);
    }

    public function test_ip_retention_period_is_configurable_with_a_thirty_day_default(): void
    {
        $this->assertSame(30, config('samm.security.ip_retention_days'));
    }
}
