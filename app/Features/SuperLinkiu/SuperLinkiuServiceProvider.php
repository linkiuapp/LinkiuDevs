<?php

namespace App\Features\SuperLinkiu;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class SuperLinkiuServiceProvider extends ServiceProvider
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
        // Cargar rutas de SuperLinkiu
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        
        // Cargar vistas de SuperLinkiu
        $this->loadViewsFrom(__DIR__ . '/Views', 'superlinkiu');
        
        // Registrar namespace para componentes de SuperLinkiu
        Blade::anonymousComponentNamespace('superlinkiu::components', 'superlinkiu');
    }
} 