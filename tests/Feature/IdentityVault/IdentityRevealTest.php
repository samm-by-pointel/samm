<?php

namespace Tests\Feature\IdentityVault;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Cases\Models\CaseFile;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Privacy\Models\Consent;
use App\Modules\Reporting\Models\Report;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IdentityRevealTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    private CaseFile $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);

        $this->supervisor = User::factory()->create();
        $this->supervisor->assignRole(Role::factory()->withPermissions(Permission::ViewAssignedCases, Permission::ViewUnassignedCases, Permission::ViewIdentities)->create());

        $report = Report::factory()->withIdentity()->create();
        $report->identity->forceFill(['full_name' => 'Nom Fictif Révélé', 'email' => 'revele@example.test'])->save();
        Consent::factory()->forPurpose(ConsentPurpose::IdentityDisclosure)->create(['report_id' => $report->id]);
        $this->case = CaseFile::factory()->create(['report_id' => $report->id]);
    }

    public function test_identity_is_masked_by_default_with_a_reveal_form(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('manage.cases.show', $this->case))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('identity.allowed', true)
                ->where('identity.canView', true)
                ->where('identity.revealUrl', route('manage.cases.identity.reveal', $this->case)));

        $this->assertPageMissing($response, 'Nom Fictif Révélé');
        $this->assertPageMissing($response, 'revele@example.test');
    }

    public function test_reveal_requires_a_reason_of_reasonable_length(): void
    {
        $this->actingAs($this->supervisor)->from(route('manage.cases.show', $this->case))
            ->post(route('manage.cases.identity.reveal', $this->case), ['reason' => 'court'])
            ->assertRedirect(route('manage.cases.show', $this->case))
            ->assertSessionHasErrors(['reason' => 'Le champ motif doit contenir au moins 10 caractères.']);

        $this->assertSame(0, AuditLog::query()->where('action', AuditAction::IdentityViewed->value)->count());
    }

    public function test_reveal_shows_the_identity_once_and_records_the_reason(): void
    {
        $this->actingAs($this->supervisor)
            ->postJson(route('manage.cases.identity.reveal', $this->case), ['reason' => 'Prise de contact demandée par la personne'])
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('identity.fullName', 'Nom Fictif Révélé')
            ->assertJsonPath('identity.email', 'revele@example.test');

        $entry = AuditLog::query()->where('action', AuditAction::IdentityViewed->value)->sole();
        $this->assertTrue($entry->subject->is($this->case));
        $this->assertTrue($entry->actor->is($this->supervisor));
        $this->assertSame('Prise de contact demandée par la personne', $entry->context['reason']);
        $this->assertStringNotContainsString('Nom Fictif', json_encode($entry->context));

        $response = $this->actingAs($this->supervisor)->get(route('manage.cases.show', $this->case))->assertOk();
        $this->assertPageMissing($response, 'Nom Fictif Révélé', 'Recharger la page masque à nouveau l\'identité.');
        $this->assertFalse(collect(session()->all())->flatten()->contains('Nom Fictif Révélé'), 'L\'identité n\'est jamais gardée en session.');
    }

    public function test_reveal_is_forbidden_without_the_permission_or_outside_the_rules(): void
    {
        $third = User::factory()->create();
        $third->assignRole(Role::factory()->withPermissions(Permission::ViewAssignedCases)->create());
        $this->case->forceFill(['assigned_to_user_id' => $third->id])->save();

        $this->actingAs($third)->get(route('manage.cases.show', $this->case))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('identity.canView', false)
                ->where('identity.consentGranted', true));
        $this->actingAs($third)->post(route('manage.cases.identity.reveal', $this->case), ['reason' => 'Motif suffisamment long'])->assertForbidden();

        $anonymousCase = CaseFile::factory()->create(['report_id' => Report::factory()->anonymous()->create()->id]);
        $this->actingAs($this->supervisor)->post(route('manage.cases.identity.reveal', $anonymousCase), ['reason' => 'Motif suffisamment long'])->assertForbidden();

        $noConsent = Report::factory()->withIdentity()->create();
        Consent::factory()->forPurpose(ConsentPurpose::IdentityDisclosure)->refused()->create(['report_id' => $noConsent->id]);
        $noConsentCase = CaseFile::factory()->create(['report_id' => $noConsent->id]);
        $this->actingAs($this->supervisor)->get(route('manage.cases.show', $noConsentCase))
            ->assertInertia(fn (Assert $page) => $page
                ->where('identity.canView', false)
                ->where('identity.consentGranted', false));
        $this->actingAs($this->supervisor)->post(route('manage.cases.identity.reveal', $noConsentCase), ['reason' => 'Motif suffisamment long'])->assertForbidden();
    }
}
