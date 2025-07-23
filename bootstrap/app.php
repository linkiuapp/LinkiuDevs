<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrar middlewares personalizados
        $middleware->alias([
            'super.admin' => \App\Shared\Middleware\SuperAdminMiddleware::class,
            'store.admin' => \App\Shared\Middleware\StoreAdminMiddleware::class,
            'tenant.identify' => \App\Shared\Middleware\TenantIdentificationMiddleware::class,
        ]);
        
        // Configurar redirects de autenticación según el contexto
        $middleware->redirectGuestsTo(function ($request) {
            try {
                // Si es una ruta de SuperLinkiu
                if ($request->is('superlinkiu/*')) {
                    return route('superlinkiu.login');
                }
                
                // Si es una ruta de admin de tienda
                if ($request->is('*/admin/*')) {
                    $storeSlug = $request->segment(1);
                    return route('tenant.admin.login', $storeSlug);
                }
                
                // Por defecto, redirigir a la página principal
                return '/';
            } catch (\Exception $e) {
                // Fallback en caso de error con rutas
                \Log::error('Auth redirect error: ' . $e->getMessage());
                return '/';
            }
        });      //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
