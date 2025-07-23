<?php

namespace App\Features\Tenant;

use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/Views', 'tenant');
        
        // No cargamos rutas aquí porque se manejan dinámicamente
        // en el RouteServiceProvider principal
    }
} 