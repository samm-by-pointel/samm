<?php

namespace App\Modules\Organizations\Providers;

use App\Modules\Organizations\Contracts\OrganizationResolver;
use App\Modules\Organizations\Models\EmergencyContact;
use App\Modules\Organizations\Services\BrandingCss;
use App\Modules\Organizations\Services\SingleOrganizationResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFactory;
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

    /**
     * Give the public layout and its components the organization's settings,
     * so that no view queries the database itself.
     */
    public function boot(): void
    {
        ViewFactory::composer('layouts.public', function (View $view): void {
            $organization = $this->app->make(OrganizationResolver::class)->current();

            $view->with([
                'organization' => $organization,
                'brandingCss' => $this->app->make(BrandingCss::class)->for($organization),
            ]);
        });

        ViewFactory::composer('layouts.manage', function (View $view): void {
            $view->with('organization', $this->app->make(OrganizationResolver::class)->current());
        });

        ViewFactory::composer('components.samm.quick-exit', function (View $view): void {
            $organization = $this->app->make(OrganizationResolver::class)->current();

            $view->with('quickExitUrl', $organization?->effectiveQuickExitUrl() ?? config('samm.quick_exit_url'));
        });

        ViewFactory::composer('components.samm.emergency-contacts', function (View $view): void {
            $view->with('contacts', EmergencyContact::query()->active()->ordered()->get());
        });
    }
}
