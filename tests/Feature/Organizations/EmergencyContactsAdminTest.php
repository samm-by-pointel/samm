<?php

namespace Tests\Feature\Organizations;

use App\Modules\Audit\Enums\AuditAction;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Users\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmergencyContactsAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['app.locale' => 'fr']);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('organization_admin');
    }

    public function test_contacts_can_be_added_edited_reordered_deactivated_and_deleted_with_public_effect(): void
    {
        $this->actingAs($this->admin)->post(route('manage.organization.emergency.store'), ['label' => ['fr' => 'Pompiers', 'en' => 'Fire brigade'], 'phone_number' => '18', 'position' => 2, 'is_active' => '1'])
            ->assertRedirect(route('manage.organization.edit'))
            ->assertSessionHas('status', 'Numéro d\'urgence enregistré.');
        $this->actingAs($this->admin)->post(route('manage.organization.emergency.store'), ['label' => ['fr' => 'Police'], 'phone_number' => '17', 'position' => 1, 'is_active' => '1'])->assertRedirect();

        $pompiers = EmergencyContact::query()->where('phone_number', '18')->sole();
        $this->assertEquals(['fr' => 'Pompiers', 'en' => 'Fire brigade'], $pompiers->label);

        auth()->logout();
        $this->get(route('public.home'))->assertInertia(fn (Assert $page) => $page
            ->where('emergencyContacts.0.label', 'Police')
            ->where('emergencyContacts.0.number', '17')
            ->where('emergencyContacts.1.label', 'Pompiers')
            ->where('emergencyContacts.1.number', '18'));

        $this->actingAs($this->admin)->put(route('manage.organization.emergency.update', $pompiers), ['label' => ['fr' => 'Sapeurs-pompiers'], 'phone_number' => '18', 'position' => 0])->assertRedirect();
        $pompiers->refresh();
        $this->assertSame('Sapeurs-pompiers', $pompiers->translate('label', 'fr'));
        $this->assertFalse($pompiers->is_active, 'Case non cochée = désactivé.');

        auth()->logout();
        $response = $this->get(route('public.home'))->assertInertia(fn (Assert $page) => $page->has('emergencyContacts', 1)->where('emergencyContacts.0.label', 'Police'));
        $this->assertPageMissing($response, 'Sapeurs-pompiers');

        $this->actingAs($this->admin)->delete(route('manage.organization.emergency.destroy', $pompiers))->assertRedirect()->assertSessionHas('status', 'Numéro d\'urgence supprimé.');
        $this->assertDatabaseCount('emergency_contacts', 1);

        $entries = AuditLog::query()->where('action', AuditAction::EmergencyContactChanged->value)->pluck('context')->map(fn (array $context): string => $context['operation'])->all();
        $this->assertSame(['created', 'created', 'updated', 'deleted'], $entries);
    }

    public function test_label_and_number_are_validated_and_permission_is_required(): void
    {
        $this->actingAs($this->admin)->from(route('manage.organization.edit'))
            ->post(route('manage.organization.emergency.store'), ['label' => ['fr' => '', 'en' => ''], 'phone_number' => '17'])
            ->assertSessionHasErrorsIn('emergency', ['label' => 'Indiquez un libellé dans au moins une langue.']);

        $this->actingAs($this->admin)->post(route('manage.organization.emergency.store'), ['label' => ['fr' => 'Police'], 'phone_number' => 'abc'])
            ->assertSessionHasErrorsIn('emergency', 'phone_number');

        $manager = User::factory()->create();
        $manager->assignRole('case_manager');
        $this->actingAs($manager)->post(route('manage.organization.emergency.store'), ['label' => ['fr' => 'Police'], 'phone_number' => '17'])->assertForbidden();

        $this->assertDatabaseCount('emergency_contacts', 0);
    }
}
