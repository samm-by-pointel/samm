<?php

namespace App\Modules\Users\Providers;

use App\Modules\Users\Actions\Fortify\ResetUserPassword;
use App\Modules\Users\Actions\Fortify\UpdateUserPassword;
use App\Modules\Users\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap Fortify: views, authentication rules and rate limiters.
     * Public registration is disabled; accounts are created by an administrator.
     */
    public function boot(): void
    {
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::authenticateUsing(function (Request $request): ?User {
            $user = User::query()->where('email', $request->input(Fortify::username()))->first();

            if ($user === null || ! $user->is_active || ! Hash::check($request->input('password'), $user->password)) {
                return null;
            }

            return $user;
        });

        /*
         * Internal accounts only: 12 characters, mixed case and digits. The
         * "uncompromised" check is deliberately absent, as it calls an external service.
         */
        Password::defaults(fn (): Password => Password::min(12)->mixedCase()->numbers());

        Fortify::loginView(fn () => Inertia::render('auth/Login', [
            'action' => route('login'),
            'forgotUrl' => Route::has('password.request') ? route('password.request') : null,
        ]));
        Fortify::requestPasswordResetLinkView(fn () => Inertia::render('auth/ForgotPassword', [
            'action' => route('password.email'),
            'loginUrl' => route('login'),
        ]));
        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'action' => route('password.update'),
            'token' => $request->route('token'),
            'email' => (string) $request->query('email', ''),
        ]));
        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge', [
            'action' => route('two-factor.login'),
        ]));
        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword', [
            'action' => route('password.confirm'),
        ]));

        RateLimiter::for('two-factor', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
