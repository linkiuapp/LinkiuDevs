<?php

namespace App\Core\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Definir el patrón para el parámetro store
        Route::pattern('store', '[a-z0-9-]+');

        $this->routes(function () {
            // 1. Rutas de API (primera prioridad)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
            
            // 2. Rutas de SuperLinkiu (segunda prioridad - ANTES que las rutas generales)
            // Las rutas de SuperLinkiu ya están cargadas por el SuperLinkiuServiceProvider
            
            // 3. Rutas del sistema web (tercera prioridad)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
            
            // 4. Rutas de tiendas (última prioridad - catch-all)
            Route::middleware('web')
                ->group(function () {
                    // Frontend de tienda
                    Route::prefix('{store}')
                        ->name('tenant.')
                        ->middleware('tenant.identify')
                        ->group(function () {
                            if (file_exists(base_path('app/Features/Tenant/Routes/web.php'))) {
                                require base_path('app/Features/Tenant/Routes/web.php');
                            }
                        });
                    
                    // Admin de tienda
                    Route::prefix('{store}/admin')
                        ->name('tenant.admin.')
                        ->middleware('tenant.identify')
                        ->group(function () {
                            if (file_exists(base_path('app/Features/TenantAdmin/Routes/web.php'))) {
                                require base_path('app/Features/TenantAdmin/Routes/web.php');
                            }
                        });
                });
        });
    }

    /**
     * Check if a slug is reserved by the system
     */
    public static function isReservedSlug(string $slug): bool
    {
        $reservedSlugs = [
            'superlinkiu',
            'admin',
            'api',
            'login',
            'logout',
            'register',
            'password',
            'email',
            'verify',
            'home',
            'dashboard',
            'profile',
            'settings',
            'support',
            'help',
            'about',
            'contact',
            'terms',
            'privacy',
            'cookies',
            'sitemap',
            'robots',
            'favicon',
            'assets',
            'images',
            'css',
            'js',
            'fonts',
            'vendor',
            'storage',
            'public'
        ];

        return in_array(strtolower($slug), $reservedSlugs);
    }
} 