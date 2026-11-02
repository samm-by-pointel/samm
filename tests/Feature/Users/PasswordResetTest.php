<?php

namespace Tests\Feature\Users;

use App\Modules\Users\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config(['app.locale' => 'fr']);
    }

    public function test_reset_link_is_sent_to_an_existing_account(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'Nous vous avons envoyé par e-mail le lien de réinitialisation du mot de passe.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/ResetPassword')
                ->where('token', $token)
                ->where('email', $user->email));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NouveauMotDePasse2026',
            'password_confirmation' => 'NouveauMotDePasse2026',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NouveauMotDePasse2026', $user->fresh()->password));
    }

    public function test_weak_password_is_refused_in_french(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->from(route('password.reset', ['token' => $token]))
            ->post(route('password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'court',
                'password_confirmation' => 'court',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['password' => 'Le champ mot de passe doit contenir au moins 12 caractères.']);

        $this->assertFalse(Hash::check('court', $user->fresh()->password));
    }
}
