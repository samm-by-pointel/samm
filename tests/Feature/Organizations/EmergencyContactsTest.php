<?php

namespace Tests\Feature\Organizations;

use App\Modules\Organizations\Models\EmergencyContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->get(route('public.home'))->assertOk();

        $response->assertSee('En cas de danger immédiat')
            ->assertSeeInOrder(['Police', '17', 'Second service', '18'])
            ->assertSee('href="tel:17"', false)
            ->assertDontSee('Service désactivé')
            ->assertDontSee('tel:99', false);
    }

    public function test_label_follows_the_current_language(): void
    {
        EmergencyContact::factory()->create(['label' => ['fr' => 'Pompiers', 'en' => 'Fire brigade'], 'phone_number' => '18']);

        $this->withSession(['locale' => 'en'])
            ->get(route('public.home'))
            ->assertOk()
            ->assertSee('In case of immediate danger')
            ->assertSee('Fire brigade')
            ->assertDontSee('Pompiers');
    }

    public function test_block_is_hidden_without_any_contact(): void
    {
        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('En cas de danger immédiat');
    }

    public function test_dialable_number_keeps_digits_and_plus_sign_only(): void
    {
        $contact = EmergencyContact::factory()->make(['phone_number' => '+221 33 123-45.67']);

        $this->assertSame('+221331234567', $contact->dialable());
    }
}
