<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado y sea admin de tienda
        if (!auth()->check() || auth()->user()->role !== 'store_admin') {
            $storeSlug = $request->route('store');
            return redirect()->route('tenant.admin.login', ['store' => $storeSlug])
                ->with('error', 'Acceso denegado. Solo administradores de tienda.');
        }

        // Verificar que el usuario sea admin de esta tienda específica
        $store = view()->shared('currentStore');
        
        if (auth()->user()->store_id !== $store->id) {
            return redirect()->route('tenant.admin.login', ['store' => $store->slug])
                ->with('error', 'No tienes permisos para administrar esta tienda.');
        }

        return $next($request);
    }
} 