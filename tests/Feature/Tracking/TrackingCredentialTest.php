<?php

namespace Tests\Feature\Tracking;

use App\Modules\Tracking\Models\TrackingCredential;
use Database\Factories\TrackingCredentialFactory;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrackingCredentialTest extends TestCase
{
    use RefreshDatabase;

    public function test_secret_is_verifiable_but_never_stored_in_clear(): void
    {
        $credential = TrackingCredential::factory()->create();

        $this->assertTrue($credential->verify(TrackingCredentialFactory::SECRET));
        $this->assertTrue($credential->verify(strtolower('rf3m w8tz k2pa 6hne')), 'Tirets, espaces et casse sont tolérés.');
        $this->assertFalse($credential->verify('RF3M-W8TZ-K2PA-0000'));

        $raw = DB::table('tracking_credentials')->where('id', $credential->id)->value('secret_hash');
        $this->assertStringNotContainsString('RF3M', $raw);
        $this->assertStringNotContainsString('RF3MW8TZK2PA6HNE', $raw);
    }

    public function test_secret_hash_is_hidden_from_serialization(): void
    {
        $credential = TrackingCredential::factory()->create();

        $this->assertArrayNotHasKey('secret_hash', $credential->toArray());
        $this->assertStringNotContainsString('secret_hash', $credential->toJson());
    }

    public function test_failures_are_counted_and_lock_after_five_attempts(): void
    {
        $credential = TrackingCredential::factory()->create();

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $credential->registerFailure();
            $this->assertFalse($credential->isLocked(), "Pas de verrouillage après {$attempt} échec(s).");
        }

        $credential->registerFailure();

        $this->assertTrue($credential->fresh()->isLocked());
        $this->assertSame(0, $credential->fresh()->failed_attempts);
        $this->assertTrue($credential->fresh()->locked_until->isAfter(now()->addMinutes(14)));
    }

    public function test_success_resets_the_counter_and_the_lock(): void
    {
        $credential = TrackingCredential::factory()->locked()->create(['failed_attempts' => 3]);

        $credential->registerSuccess();

        $credential->refresh();
        $this->assertFalse($credential->isLocked());
        $this->assertSame(0, $credential->failed_attempts);
        $this->assertNotNull($credential->last_used_at);
    }

    public function test_one_credential_per_report(): void
    {
        $credential = TrackingCredential::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        TrackingCredential::factory()->create(['report_id' => $credential->report_id]);
    }

    public function test_lock_expires(): void
    {
        $credential = TrackingCredential::factory()->create(['locked_until' => now()->subMinute()]);

        $this->assertFalse($credential->isLocked());
    }
}
