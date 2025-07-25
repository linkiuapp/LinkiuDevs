<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EnsureStorageLinked
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo ejecutar en producción y solo en requests GET (no APIs)
        if (app()->environment('production') && $request->isMethod('GET') && !$request->is('api/*')) {
            $this->ensureStorageIsLinked();
        }

        return $next($request);
    }

    /**
     * Ensure storage symlink exists and repair if needed
     */
    private function ensureStorageIsLinked(): void
    {
        try {
            $publicStorage = public_path('storage');
            
            // Verificar si el symlink existe y es válido
            if (!file_exists($publicStorage) || !is_link($publicStorage)) {
                Log::warning('Storage symlink missing or broken, attempting repair');
                
                // Ejecutar el comando de reparación
                Artisan::call('post-deploy:fix-storage');
                
                Log::info('Storage symlink repair attempted');
            }
        } catch (\Exception $e) {
            // No hacer nada crítico, solo log para no romper el request
            Log::error('Failed to check/repair storage symlink: ' . $e->getMessage());
        }
    }
} 