<?php

namespace Tests\Feature\Messaging;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Messaging\Actions\PostMessage;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Reporting\Models\Report;
use App\Modules\Tracking\Models\TrackingCredential;
use App\Modules\Users\Models\User;
use Database\Factories\TrackingCredentialFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicMessagingTest extends TestCase
{
    use RefreshDatabase;

    private Report $report;

    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);

        $this->report = Report::factory()->pseudonymous('Alouette')->create(['reference' => 'SMM-7K4Q-92XD']);
        CaseFile::factory()->create(['report_id' => $this->report->id]);
        TrackingCredential::factory()->create(['report_id' => $this->report->id]);
        $this->conversation = Conversation::factory()->create(['report_id' => $this->report->id]);

        $this->post(route('tracking.authenticate'), ['reference' => 'SMM-7K4Q-92XD', 'secret' => TrackingCredentialFactory::SECRET]);
    }

    public function test_reporting_person_can_send_a_message_which_appears_in_the_thread_encrypted_at_rest(): void
    {
        $this->post(route('tracking.messages.store'), ['body' => "Précision fictive.\nSeconde ligne."])
            ->assertRedirect(route('tracking.show'))
            ->assertSessionHas('status');

        $this->get(route('tracking.show'))
            ->assertOk()
            ->assertSee('Précision fictive.')
            ->assertSee('Seconde ligne.')
            ->assertSee('samm-msg-me', false)
            ->assertSee('Vous ·');

        $this->assertStringNotContainsString('Précision', DB::table('messages')->value('body'));
        $this->assertSame(MessageAuthor::Reporter, $this->conversation->messages()->sole()->author_type);
    }

    public function test_staff_messages_are_signed_by_role_and_marked_read_when_viewed(): void
    {
        $manager = User::factory()->create(['name' => 'Moussa Sarr']);
        app(PostMessage::class)->handle($this->conversation, MessageAuthor::Staff, 'Bonjour, pouvez-vous préciser la période ?', $manager);

        $this->assertSame(1, $this->conversation->unreadForReporter());

        $this->get(route('tracking.show'))
            ->assertOk()
            ->assertSee('pouvez-vous préciser la période')
            ->assertSee('Gestionnaire du dossier')
            ->assertSee('samm-msg-them', false)
            ->assertDontSee('Moussa Sarr');

        $this->assertSame(0, $this->conversation->unreadForReporter());
    }

    public function test_empty_or_too_long_message_is_refused_in_french(): void
    {
        $this->from(route('tracking.show'))
            ->post(route('tracking.messages.store'), ['body' => ''])
            ->assertRedirect(route('tracking.show'))
            ->assertSessionHasErrors(['body' => 'Le champ message est obligatoire.']);

        $this->post(route('tracking.messages.store'), ['body' => str_repeat('a', 5001)])
            ->assertSessionHasErrors('body');

        $this->assertSame(0, $this->conversation->messages()->count());
    }

    public function test_sending_requires_an_open_tracking_session(): void
    {
        $this->post(route('tracking.logout'));

        $this->post(route('tracking.messages.store'), ['body' => 'Sans session'])
            ->assertRedirect(route('tracking.login'));

        $this->assertSame(0, $this->conversation->messages()->count());
    }
}
