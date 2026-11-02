<?php

namespace Tests\Feature\Organizations;

use App\Modules\Organizations\Models\EmergencyContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmergencyContactsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config(['app.locale' => 'fr']);
    }

    public function test_active_contacts_are_listed_in_order_with_phone_links(): void
    {
        EmergencyContact::factory()->create(['label' => ['fr' => 'Second service', 'en' => 'Second service'], 'phone_number' => '18', 'position' => 2]);
        EmergencyContact::factory()->create(['label' => ['fr' => 'Police', 'en' => 'Police'], 'phone_number' => '17', 'position' => 1]);
        EmergencyContact::factory()->inactive()->create(['label' => ['fr' => 'Service désactivé', 'en' => 'Disabled service'], 'phone_number' => '99', 'position' => 0]);

        $response = $this->get(route('public.home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('emergencyContacts', 2)
                ->where('emergencyContacts.0.label', 'Police')
                ->where('emergencyContacts.0.number', '17')
                ->where('emergencyContacts.0.dialable', '17')
                ->where('emergencyContacts.1.label', 'Second service')
                ->where('emergencyContacts.1.number', '18'));

        $this->assertPageMissing($response, 'Service désactivé');
    }

    public function test_label_follows_the_current_language(): void
    {
        EmergencyContact::factory()->create(['label' => ['fr' => 'Pompiers', 'en' => 'Fire brigade'], 'phone_number' => '18']);

        $response = $this->withSession(['locale' => 'en'])
            ->get(route('public.home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('translations.public.emergency.title', 'In case of immediate danger')
                ->where('emergencyContacts.0.label', 'Fire brigade'));

        $this->assertPageMissing($response, 'Pompiers');
    }

    public function test_block_is_hidden_without_any_contact(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('emergencyContacts', 0));
    }

    public function test_dialable_number_keeps_digits_and_plus_sign_only(): void
    {
        $contact = EmergencyContact::factory()->make(['phone_number' => '+221 33 123-45.67']);

        $this->assertSame('+221331234567', $contact->dialable());
    }
}
