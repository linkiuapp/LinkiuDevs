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
        if (!auth()->check()) {
            return redirect('/superlinkiu/login')->with('error', 'Acceso denegado. Solo administradores.');
        }

        if (Auth::user()->role !== 'super_admin') {
            Auth::logout();
            return redirect('/superlinkiu/login')->with('error', 'Acceso denegado. Solo administradores.');
        }

        return $next($request);
    }
} 