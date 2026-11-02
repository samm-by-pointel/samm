<?php

namespace Tests\Feature\Users;

use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\RecoveryCode;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /** @var array<int, string> */
    private array $recoveryCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config(['app.locale' => 'fr']);

        $this->recoveryCodes = [RecoveryCode::generate(), RecoveryCode::generate()];

        $this->user = User::factory()->create([
            'email' => 'gestionnaire@example.test',
            'two_factor_secret' => encrypt('ABCDEFGHIJKLMNOP'),
            'two_factor_recovery_codes' => encrypt(json_encode($this->recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_account_with_confirmed_two_factor_is_sent_to_the_challenge_instead_of_being_signed_in(): void
    {
        $this->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'password'])
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertSee('Vérification en deux étapes')
            ->assertSee('Code de récupération');
    }

    public function test_valid_recovery_code_signs_in_and_is_consumed(): void
    {
        $this->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'password']);

        $this->post(route('two-factor.login'), ['recovery_code' => $this->recoveryCodes[0]])
            ->assertRedirect('/gestion');

        $this->assertAuthenticatedAs($this->user);

        $remaining = json_decode(decrypt($this->user->fresh()->two_factor_recovery_codes), true);
        $this->assertNotContains($this->recoveryCodes[0], $remaining);
        $this->assertContains($this->recoveryCodes[1], $remaining);
    }

    public function test_wrong_code_is_refused_with_a_french_message(): void
    {
        $this->post(route('login'), ['email' => 'gestionnaire@example.test', 'password' => 'password']);

        $this->from(route('two-factor.login'))
            ->post(route('two-factor.login'), ['code' => '000000'])
            ->assertRedirect(route('two-factor.login'))
            ->assertSessionHasErrors(['code' => 'Le code d\'authentification fourni n\'est pas valide.']);

        $this->assertGuest();
    }

    public function test_account_without_two_factor_signs_in_directly(): void
    {
        User::factory()->create(['email' => 'simple@example.test']);

        $this->post(route('login'), ['email' => 'simple@example.test', 'password' => 'password'])
            ->assertRedirect('/gestion');

        $this->assertAuthenticated();
    }
}
