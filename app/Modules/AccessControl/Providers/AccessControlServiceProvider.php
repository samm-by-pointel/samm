<?php

namespace App\Modules\AccessControl\Providers;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AccessControlServiceProvider extends ServiceProvider
{
    /**
     * Register the AccessControl module bindings.
     */
    public function register(): void
    {
        //
    }

    /**
     * One gate per permission, so that `can('audit.view')`, the `can:` middleware
     * and `@can` all go through the roles. Deliberately no `Gate::before`:
     * nobody bypasses the checks, not even the technical administrator.
     */
    public function boot(): void
    {
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user): bool => $user->hasPermission($permission));
        }
    }
}
