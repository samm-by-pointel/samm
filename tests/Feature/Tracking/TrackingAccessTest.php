<?php

namespace Tests\Feature\Tracking;

use App\Modules\Audit\Enums\ActorKind;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Actions\AuthenticateTracking;
use App\Modules\Tracking\Models\TrackingCredential;
use App\Modules\Workflow\Enums\CaseStatus;
use Database\Factories\TrackingCredentialFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrackingAccessTest extends TestCase
{
    use RefreshDatabase;

    private Report $report;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr', 'session.driver' => 'database']);

        $this->report = Report::factory()->withIdentity()->create(['reference' => 'SMM-7K4Q-92XD']);
        CaseFile::factory()->highPriority()->create(['report_id' => $this->report->id]);
        TrackingCredential::factory()->create(['report_id' => $this->report->id]);
    }

    private function signIn(string $reference = 'SMM-7K4Q-92XD', string $secret = TrackingCredentialFactory::SECRET): TestResponse
    {
        return $this->post(route('tracking.authenticate'), ['reference' => $reference, 'secret' => $secret]);
    }

    public function test_login_page_is_rendered_with_quick_exit(): void
    {
        $this->get(route('tracking.login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tracking/Login')
                ->where('action', route('tracking.authenticate'))
                ->where('translations.tracking.login.secret', 'Secret de récupération')
                ->where('site.quickExitUrl', config('samm.quick_exit_url')));

        $this->get(route('public.home'))
            ->assertInertia(fn (Assert $page) => $page->where('routes.trackingLogin', route('tracking.login')));
    }

    public function test_valid_codes_open_the_case_with_its_public_status_only(): void
    {
        $this->signIn('smm 7k4q 92xd', strtolower('rf3m-w8tz-k2pa-6hne'))->assertRedirect(route('tracking.show'));

        $response = $this->get(route('tracking.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tracking/Show')
                ->where('report.reference', 'SMM-7K4Q-92XD')
                ->where('report.status.label', 'Nouveau')
                ->missing('report.priority'));

        $this->assertPageMissing($response, 'Priorité haute');
        $this->assertPageMissing($response, $this->report->identity->full_name, 'L\'identité n\'apparaît jamais dans l\'espace de suivi.');
    }

    public function test_quick_exit_is_present_in_the_tracking_space(): void
    {
        $this->signIn();

        $this->get(route('tracking.show'))
            ->assertInertia(fn (Assert $page) => $page->where('site.quickExitUrl', config('samm.quick_exit_url')));
    }

    public function test_wrong_secret_unknown_reference_and_locked_credential_give_the_same_message(): void
    {
        $failed = 'Identifiant ou secret incorrect, ou accès temporairement suspendu. Vérifiez vos codes et réessayez dans quelques minutes.';

        $this->from(route('tracking.login'))->signIn(secret: 'RF3M-W8TZ-K2PA-0000')
            ->assertRedirect(route('tracking.login'))
            ->assertSessionHasErrors(['reference' => $failed]);
        $this->assertSame(1, $this->report->trackingCredential->fresh()->failed_attempts);

        $this->from(route('tracking.login'))->signIn(reference: 'SMM-ZZZZ-ZZZZ')
            ->assertSessionHasErrors(['reference' => $failed]);

        $this->report->trackingCredential->fresh()->forceFill(['locked_until' => now()->addMinutes(10)])->save();
        $this->from(route('tracking.login'))->signIn()
            ->assertSessionHasErrors(['reference' => $failed]);

        $this->get(route('tracking.show'))->assertRedirect(route('tracking.login'));
    }

    public function test_five_wrong_secrets_lock_the_credential(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->signIn(secret: 'RF3M-W8TZ-K2PA-0000');
        }

        $this->assertTrue($this->report->trackingCredential->fresh()->isLocked());

        $this->signIn()->assertSessionHasErrors('reference');
        $this->assertFalse(session()->has(AuthenticateTracking::SESSION_REPORT));
    }

    public function test_eleventh_attempt_within_a_minute_is_throttled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->signIn(secret: 'RF3M-W8TZ-K2PA-0000')->assertStatus(302);
        }

        $this->signIn()->assertStatus(429)->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 429));
    }

    public function test_tracking_session_expires_after_idle_period(): void
    {
        config(['samm.tracking.session_minutes' => 30]);
        $this->signIn();
        $this->get(route('tracking.show'))->assertOk();

        $this->travel(31)->minutes();

        $this->get(route('tracking.show'))
            ->assertRedirect(route('tracking.login'))
            ->assertSessionHas('status', 'Votre session de suivi a expiré. Saisissez à nouveau vos codes pour continuer.');
    }

    public function test_logout_closes_the_tracking_space(): void
    {
        $this->signIn();

        $this->post(route('tracking.logout'))->assertRedirect(route('public.home'));

        $this->get(route('tracking.show'))->assertRedirect(route('tracking.login'));
    }

    public function test_archived_case_reads_closed_to_the_reporting_person(): void
    {
        $this->report->case->forceFill(['status' => CaseStatus::Archived])->save();
        $this->signIn();

        $response = $this->get(route('tracking.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('report.status.value', 'closed')
                ->where('report.status.label', 'Clôturé'));

        $this->assertPageMissing($response, 'Archivé');
    }

    public function test_accesses_are_audited_without_ip_nor_codes(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);
        $this->signIn(secret: 'RF3M-W8TZ-K2PA-0000');
        $this->signIn(reference: 'SMM-ZZZZ-ZZZZ');
        $this->signIn();

        $failures = AuditLog::query()->where('action', AuditAction::TrackingAccessFailed->value)->oldest('id')->get();
        $success = AuditLog::query()->where('action', AuditAction::TrackingAccessSucceeded->value)->sole();

        $this->assertCount(2, $failures);
        $this->assertTrue($failures[0]->subject->is($this->report));
        $this->assertTrue($failures[0]->context['known_reference']);
        $this->assertNull($failures[1]->subject_id);
        $this->assertFalse($failures[1]->context['known_reference']);
        $this->assertSame(ActorKind::Reporter, $success->actor_kind);
        $this->assertTrue($success->subject->is($this->report));

        $this->assertSame(0, AuditLog::query()->whereNotNull('ip_address')->count());
        $this->assertSame(0, DB::table('sessions')->whereNotNull('ip_address')->count());
        $this->assertStringNotContainsString('RF3M', json_encode(AuditLog::query()->pluck('context')));
    }
}
