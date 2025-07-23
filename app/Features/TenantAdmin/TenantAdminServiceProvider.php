<?php

namespace App\Features\TenantAdmin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class TenantAdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar vistas con namespace
        $this->loadViewsFrom(__DIR__ . '/Views', 'tenant-admin');
        
        // No cargamos rutas aquí porque se manejan dinámicamente
        // en el RouteServiceProvider principal
        
        // Registrar componentes específicos del admin de tienda
        Blade::componentNamespace('App\\Features\\TenantAdmin\\Views\\Components', 'tenant-admin');
    }
} 