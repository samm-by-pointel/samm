<?php

use App\Modules\AccessControl\Providers\AccessControlServiceProvider;
use App\Modules\Audit\Providers\AuditServiceProvider;
use App\Modules\Core\Providers\CoreServiceProvider;
use App\Modules\Organizations\Providers\OrganizationsServiceProvider;
use App\Modules\Users\Providers\FortifyServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    CoreServiceProvider::class,
    AccessControlServiceProvider::class,
    AuditServiceProvider::class,
    OrganizationsServiceProvider::class,
    FortifyServiceProvider::class,
];
