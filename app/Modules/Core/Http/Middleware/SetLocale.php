<?php

namespace App\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the locale chosen by the visitor, falling back to the application default.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (array_key_exists($locale, config('samm.locales'))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
