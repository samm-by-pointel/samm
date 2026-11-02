<?php

namespace App\Modules\Core\Session;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Session\DatabaseSessionHandler;

/**
 * Database session handler that never records the IP address or user agent of
 * a visitor. Only the session of an authenticated internal account keeps them,
 * for security purposes (CLAUDE.md, "Sessions et adresses IP").
 */
class PrivacyAwareDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * Add the request information to the session payload for authenticated users only.
     *
     * @param  array<string, mixed>  $payload
     * @return $this
     */
    protected function addRequestInformation(&$payload)
    {
        if ($this->sessionBelongsToAuthenticatedUser()) {
            return parent::addRequestInformation($payload);
        }

        $payload['ip_address'] = null;
        $payload['user_agent'] = null;

        return $this;
    }

    private function sessionBelongsToAuthenticatedUser(): bool
    {
        return $this->container->bound(Guard::class)
            && $this->container->make(Guard::class)->check();
    }
}
