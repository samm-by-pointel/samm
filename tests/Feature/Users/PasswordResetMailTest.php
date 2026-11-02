<?php

namespace Tests\Feature\Users;

use App\Modules\Users\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_email_is_rendered_in_french(): void
    {
        app()->setLocale('fr');
        $user = User::factory()->make();

        $mail = (new ResetPassword('jeton-fictif'))->toMail($user);
        $html = (string) $mail->render();

        $this->assertSame('Réinitialiser votre mot de passe', $mail->subject);
        $this->assertStringContainsString('Bonjour', $html);
        $this->assertStringContainsString('Réinitialiser le mot de passe', $html);
        $this->assertStringContainsString('Cordialement', $html);
        $this->assertStringNotContainsString('Hello!', $html);
        $this->assertStringNotContainsString('Regards', $html);
    }
}
