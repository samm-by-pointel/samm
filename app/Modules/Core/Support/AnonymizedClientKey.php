<?php

namespace App\Modules\Core\Support;

use Illuminate\Http\Request;

/**
 * Rate-limiting key that does not keep the visitor's IP address: an HMAC of
 * the address with a key derived from APP_KEY, truncated. It lives in the cache
 * for the duration of the limiter window only, and is never written to the
 * database nor to the logs (CLAUDE.md, "Rate limiting public").
 */
class AnonymizedClientKey
{
    public static function for(Request $request, string $scope = 'public'): string
    {
        $address = $request->ip() ?? 'unknown';

        return substr(hash_hmac('sha256', $scope.'|'.$address, self::secret()), 0, 32);
    }

    private static function secret(): string
    {
        $appKey = (string) config('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            $appKey = base64_decode(substr($appKey, 7), true) ?: $appKey;
        }

        return hash_hmac('sha256', 'samm-anonymized-client-key', $appKey, true);
    }
}
