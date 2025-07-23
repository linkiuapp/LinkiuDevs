<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Shared\Services\TenantService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar TenantService como singleton
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::componentNamespace('App\\View\\Components\\Layouts', 'layouts');
    }
}
