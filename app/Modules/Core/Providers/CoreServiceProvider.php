<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Session\PrivacyAwareDatabaseSessionHandler;
use App\Modules\Core\Support\AnonymizedClientKey;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register the Core module bindings.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap the Core module.
     */
    public function boot(): void
    {
        /*
         * Public endpoints are throttled by an anonymized key (HMAC of the IP
         * address, cache only): a gentle limit for navigating the form, a strict
         * one for submitting a report.
         */
        RateLimiter::for('public-form', fn (Request $request): Limit => Limit::perMinute(60)->by(AnonymizedClientKey::for($request, 'form')));
        RateLimiter::for('public-submission', fn (Request $request): Limit => Limit::perMinutes(10, 5)->by(AnonymizedClientKey::for($request, 'submission')));
        RateLimiter::for('public-tracking', fn (Request $request): Limit => Limit::perMinute(10)->by(AnonymizedClientKey::for($request, 'tracking')));

        /*
         * The "database" session driver is replaced by a handler that stores
         * the IP address and user agent only for authenticated internal accounts.
         */
        Session::extend('database', function (Application $app): PrivacyAwareDatabaseSessionHandler {
            return new PrivacyAwareDatabaseSessionHandler(
                DB::connection(config('session.connection')),
                config('session.table', 'sessions'),
                (int) config('session.lifetime', 120),
                $app,
            );
        });
    }
}
