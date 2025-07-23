<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Shared\Models\Store;
use App\Shared\Services\TenantService;
use Symfony\Component\HttpFoundation\Response;

class TenantIdentificationMiddleware
{
    public function __construct(
        protected TenantService $tenantService
    ) {}
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el slug de la tienda desde la URL
        $storeSlug = $request->route('store');
        
        if ($storeSlug) {
            // Buscar la tienda por slug sin verificar estado y cargar contadores
            $store = Store::where('slug', $storeSlug)
                ->withCount([
                    'products',
                    'categories',
                    'variables',
                    'sliders'
                ])
                ->with('plan')
                ->first();
            
            if (!$store) {
                abort(404, 'Tienda no encontrada');
            }
            
            // Establecer el tenant actual
            $this->tenantService->setTenant($store);
            
            // Reemplazar el parámetro string con el modelo Store
            $request->route()->setParameter('store', $store);
            
            // Compartir la tienda con todas las vistas
            view()->share('currentStore', $store);
        }
        
        return $next($request);
    }
} 