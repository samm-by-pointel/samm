<?php

namespace Tests\Feature\Messaging;

use App\Modules\Messaging\Actions\PostMessage;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Models\Message;
use App\Modules\Reporting\Actions\SubmitReport;
use App\Modules\Reporting\Models\ReportCategory;
use App\Modules\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_conversation_is_created_with_the_report_at_submission(): void
    {
        $category = ReportCategory::factory()->create();

        $result = app(SubmitReport::class)->handle([
            'situation' => ['category' => $category->uuid],
            'confidentialite' => ['confidentiality_level' => 'anonymous'],
            'description' => ['description' => 'Récit fictif.'],
            'personnes' => [],
            'consentement' => ['consent_processing' => true],
        ], 'fr');

        $this->assertNotNull($result->report->conversation);
        $this->assertDatabaseCount('conversations', 1);
        $this->assertSame(0, $result->report->conversation->messages()->count());
    }

    public function test_one_conversation_per_report(): void
    {
        $conversation = Conversation::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        Conversation::factory()->create(['report_id' => $conversation->report_id]);
    }

    public function test_posting_stores_an_encrypted_body_and_updates_the_conversation(): void
    {
        $conversation = Conversation::factory()->create();
        $manager = User::factory()->create();

        $first = app(PostMessage::class)->handle($conversation, MessageAuthor::Reporter, 'Message fictif de la personne');
        $second = app(PostMessage::class)->handle($conversation, MessageAuthor::Staff, 'Réponse fictive', $manager);

        $this->assertNull($first->author_user_id);
        $this->assertTrue($second->author->is($manager));
        $this->assertNotNull($conversation->fresh()->last_message_at);
        $this->assertSame(['Message fictif de la personne', 'Réponse fictive'], $conversation->messages()->get()->pluck('body')->all());

        $raw = DB::table('messages')->pluck('body')->implode(' ');
        $this->assertStringNotContainsString('fictif', $raw);
        $this->assertStringNotContainsString('Réponse', $raw);
    }

    public function test_unread_counters_and_read_markers_are_kept_per_side(): void
    {
        $conversation = Conversation::factory()->create();
        $manager = User::factory()->create();
        Message::factory()->fromStaff($manager)->count(2)->create(['conversation_id' => $conversation->id]);
        Message::factory()->fromReporter()->create(['conversation_id' => $conversation->id]);

        $this->assertSame(2, $conversation->unreadForReporter());
        $this->assertSame(1, $conversation->unreadForStaff());

        $conversation->markReadByReporter();

        $this->assertSame(0, $conversation->unreadForReporter());
        $this->assertSame(1, $conversation->unreadForStaff(), 'Marquer côté personne ne touche pas le côté équipe.');

        $conversation->markReadByStaff();
        $this->assertSame(0, $conversation->unreadForStaff());
    }

    public function test_messages_cannot_be_modified_nor_deleted(): void
    {
        $message = Message::factory()->create();

        try {
            $message->update(['body' => 'modifié']);
            $this->fail('La modification doit être refusée.');
        } catch (LogicException) {
            // attendu
        }

        $this->expectException(LogicException::class);
        $message->delete();
    }

    public function test_public_signature_never_shows_a_staff_name(): void
    {
        app()->setLocale('fr');

        $this->assertSame('Gestionnaire du dossier', MessageAuthor::Staff->publicLabel());
        $this->assertSame('Vous', MessageAuthor::Reporter->publicLabel());
    }
}
