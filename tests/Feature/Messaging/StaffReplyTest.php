<?php

namespace Tests\Feature\Messaging;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Models\TrackingCredential;
use App\Modules\Users\Models\User;
use Database\Factories\TrackingCredentialFactory;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StaffReplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);
    }

    public function test_reply_from_the_case_page_reaches_the_tracking_space_signed_by_role(): void
    {
        $manager = User::factory()->create(['name' => 'Moussa Sarr']);
        $manager->assignRole('case_manager');
        $case = CaseFile::factory()->assignedTo($manager)->create(['report_id' => Report::factory()->create(['reference' => 'SMM-7K4Q-92XD'])->id]);
        TrackingCredential::factory()->create(['report_id' => $case->report_id]);
        Conversation::factory()->create(['report_id' => $case->report_id]);

        $this->actingAs($manager)->post(route('manage.cases.messages.store', $case), ['body' => 'Bonjour, pouvez-vous préciser la période des faits ?'])
            ->assertRedirect(route('manage.cases.show', $case))
            ->assertSessionHas('status');

        $conversation = $case->report->conversation->fresh();
        $this->assertSame(1, $conversation->unreadForReporter());
        $this->assertTrue($conversation->messages()->sole()->author->is($manager));

        auth()->logout();
        $this->post(route('tracking.authenticate'), ['reference' => 'SMM-7K4Q-92XD', 'secret' => TrackingCredentialFactory::SECRET]);
        $response = $this->get(route('tracking.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('messages.0.body', 'Bonjour, pouvez-vous préciser la période des faits ?')
                ->where('messages.0.author', 'Gestionnaire du dossier'));
        $this->assertPageMissing($response, 'Moussa Sarr');
    }

    public function test_reply_creates_the_conversation_if_missing_and_requires_the_permission(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('case_manager');
        $case = CaseFile::factory()->assignedTo($manager)->create();

        $this->actingAs($manager)->post(route('manage.cases.messages.store', $case), ['body' => 'Première réponse'])->assertRedirect();
        $this->assertDatabaseCount('conversations', 1);

        $auditor = User::factory()->create();
        $auditor->assignRole('auditor');
        $this->actingAs($auditor)->post(route('manage.cases.messages.store', $case), ['body' => 'Interdit'])->assertForbidden();
    }
}
