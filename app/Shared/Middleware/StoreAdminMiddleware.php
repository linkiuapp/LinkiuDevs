<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('StoreAdminMiddleware - Iniciando validación', [
            'url' => $request->url(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role,
        ]);
        
        // Verificar que el usuario esté autenticado y sea admin de tienda
        if (!auth()->check() || auth()->user()->role !== 'store_admin') {
            $storeSlug = $request->route('store');
            \Log::info('StoreAdminMiddleware - Usuario no autorizado', [
                'authenticated' => auth()->check(),
                'role' => auth()->user()?->role,
                'store_slug' => $storeSlug
            ]);
            return redirect()->route('tenant.admin.login', ['store' => $storeSlug])->with('error', 'Acceso denegado. Solo administradores de tienda.');
        }

        // Verificar que el usuario sea admin de esta tienda específica
        $store = view()->shared('currentStore');
        \Log::info('StoreAdminMiddleware - Verificando tienda', [
            'user_store_id' => auth()->user()->store_id,
            'current_store_id' => $store?->id,
            'store' => $store
        ]);
        
        if (auth()->user()->store_id !== $store->id) {
            \Log::info('StoreAdminMiddleware - Usuario no pertenece a esta tienda');
            return redirect()->route('tenant.admin.login', ['store' => $store->slug])->with('error', 'No tienes permisos para administrar esta tienda.');
        }

        \Log::info('StoreAdminMiddleware - Acceso autorizado');
        return $next($request);
    }
} 