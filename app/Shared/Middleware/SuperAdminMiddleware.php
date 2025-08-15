<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('🔐 SUPER ADMIN MIDDLEWARE: Verificando acceso', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'is_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role
        ]);
        
        if (!auth()->check()) {
            \Log::warning('🔐 SUPER ADMIN MIDDLEWARE: Usuario no autenticado');
            return redirect('/superlinkiu/login')->with('error', 'Acceso denegado. Solo administradores.');
        }

        if (Auth::user()->role !== 'super_admin') {
            \Log::warning('🔐 SUPER ADMIN MIDDLEWARE: Usuario no es super_admin', [
                'user_role' => Auth::user()->role
            ]);
            Auth::logout();
            return redirect('/superlinkiu/login')->with('error', 'Acceso denegado. Solo administradores.');
        }
        
        \Log::info('🔐 SUPER ADMIN MIDDLEWARE: Acceso autorizado, continuando...');
        return $next($request);
    }
} 