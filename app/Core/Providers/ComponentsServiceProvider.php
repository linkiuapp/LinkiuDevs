<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Shared\Views\Components\AdminLayout;
use App\Shared\Views\Components\TenantAdminLayout;

class ComponentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar vistas compartidas
        $this->loadViewsFrom(app_path('Shared/Views/Components'), 'shared');
    }

    public function boot(): void
    {
        // Admin Components
        Blade::component('admin-layout', AdminLayout::class);
        Blade::component('tenant-admin-layout', TenantAdminLayout::class);
        
        // Registrar componentes individuales
        Blade::component('shared::admin.sidebar', 'admin-sidebar');
        Blade::component('shared::admin.navbar', 'admin-navbar');
        Blade::component('shared::admin.footer', 'admin-footer');
        Blade::component('shared::admin.tenant-sidebar', 'tenant-admin-sidebar');
        Blade::component('shared::admin.tenant-navbar', 'tenant-admin-navbar');
        
        // Registrar vistas compartidas
        $this->loadViewsFrom(app_path('Shared/Views/Components'), 'shared');
    }
}