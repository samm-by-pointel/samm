<?php

namespace App\Modules\Organizations\Services;

use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Models\Organization;

/**
 * Resolves the single organization of this installation, once per request.
 */
class SingleOrganizationResolver implements OrganizationResolver
{
    private ?Organization $organization = null;

    private bool $resolved = false;

    public function current(): ?Organization
    {
        if (! $this->resolved) {
            $this->organization = Organization::query()->first();
            $this->resolved = true;
        }

        return $this->organization;
    }

    /**
     * Drop the memoized organization (after the settings have been changed, for instance).
     */
    public function forget(): void
    {
        $this->organization = null;
        $this->resolved = false;
    }
}
