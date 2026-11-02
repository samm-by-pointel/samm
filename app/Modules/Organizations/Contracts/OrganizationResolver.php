<?php

namespace App\Modules\Organizations\Contracts;

use App\Modules\Organizations\Models\Organization;

/**
 * The only place in the code that knows which organization the request belongs to.
 *
 * Today one installation serves one organization; a Cloud implementation may later
 * resolve it from the sub-domain without touching the rest of the application.
 */
interface OrganizationResolver
{
    public function current(): ?Organization;
}
