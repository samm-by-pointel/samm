<?php

namespace App\Modules\Organizations\Providers;

use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Services\SingleOrganizationResolver;
use Illuminate\Support\ServiceProvider;

class OrganizationsServiceProvider extends ServiceProvider
{
    /**
     * Register the Organizations module bindings.
     */
    public function register(): void
    {
        $this->app->scoped(OrganizationResolver::class, SingleOrganizationResolver::class);
    }
}
