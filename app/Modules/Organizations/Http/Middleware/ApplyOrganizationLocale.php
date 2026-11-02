<?php

namespace App\Modules\Organizations\Http\Middleware;

use App\Modules\Organizations\Contracts\OrganizationResolver;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The organization's default language is the interface language until the
 * visitor chooses one (SetLocale then applies the session choice).
 */
class ApplyOrganizationLocale
{
    public function __construct(private readonly OrganizationResolver $organizations) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->session()->has('locale')) {
            try {
                $locale = $this->organizations->current()?->default_locale;
            } catch (QueryException) {
                $locale = null; // Database not migrated yet (first installation).
            }

            if ($locale !== null && array_key_exists($locale, config('samm.locales'))) {
                config(['app.locale' => $locale]);
            }
        }

        return $next($request);
    }
}
