<?php

return [
    App\Core\Providers\AppServiceProvider::class,
    App\Core\Providers\AuthServiceProvider::class,
    App\Core\Providers\ComponentsServiceProvider::class,
    App\Core\Providers\EventServiceProvider::class,
    App\Features\SuperLinkiu\SuperLinkiuServiceProvider::class,
    App\Core\Providers\RouteServiceProvider::class,
    App\Features\Tenant\TenantServiceProvider::class,
    App\Features\TenantAdmin\TenantAdminServiceProvider::class,

];
