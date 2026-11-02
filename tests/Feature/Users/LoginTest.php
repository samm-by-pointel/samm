<?php

namespace Tests\Feature\Users;

use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config(['app.locale' => 'fr']);
    }

    public function test_login_page_is_rendered_in_french(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="fr">', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/Login')
                ->where('action', route('login'))
                ->where('forgotUrl', route('password.request'))
                ->where('translations.manage.auth.login.title', 'Connexion à l\'espace de gestion')
                ->where('translations.manage.auth.login.submit', 'Se connecter'));
    }

    public function test_active_user_can_sign_in_and_reaches_the_management_area(): void
    {
        $user = User::factory()->create(['email' => 'gestionnaire@example.test']);

        $this->post(route('login'), ['email' => 'Gestionnaire@Example.test', 'password' => 'password'])
            ->assertRedirect('/gestion');

        $this->assertAuthenticatedAs($user);

        $this->get(route('manage.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('manage/Dashboard')
                ->where('auth.name', $user->name));
    }

    public function test_wrong_password_is_refused_with_a_french_message(): void
    {
        User::factory()->create(['email' => 'gestionnaire@example.test']);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'mauvais-mot-de-passe'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Ces identifiants ne correspondent pas à nos enregistrements.']);

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_sign_in_even_with_the_right_password(): void
    {
        User::factory()->inactive()->create(['email' => 'inactif@example.test']);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'inactif@example.test', 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sixth_attempt_within_a_minute_is_locked(): void
    {
        User::factory()->create(['email' => 'gestionnaire@example.test']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'mauvais'])->assertRedirect();
        }

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Trop de tentatives de connexion.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_from_the_management_area(): void
    {
        $this->get(route('manage.dashboard'))->assertRedirect(route('login'));
    }

    public function test_user_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_public_registration_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }
}
