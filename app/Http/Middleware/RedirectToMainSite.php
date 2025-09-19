<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToMainSite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $path = $request->getPathInfo();
        
        // Solo redirigir si es acceso directo a linkiu.bio (sin subdominio)
        if ($host === 'linkiu.bio') {
            // Excluir rutas específicas de admin y superadmin
            $excludedPaths = [
                '/admin',           // SuperAdmin
                '/superlinkiu',     // SuperAdmin routes
                '/login',           // Auth
                '/register',        // Auth
                '/password',        // Auth
                '/api',             // API
                '/debug',           // Debug
                '/test',            // Test
                '/storage',         // Assets
                '/build',           // Assets
                '/favicon.ico',     // Favicon
                '/robots.txt'       // Robots
            ];
            
            // También excluir rutas de tiendas (ej: /tienda/admin)
            if (preg_match('/^\/[a-zA-Z0-9\-_]+\/admin/', $path)) {
                return $next($request);
            }
            
            // No redirigir si la ruta empieza con alguna de las excluidas
            foreach ($excludedPaths as $excludedPath) {
                if (str_starts_with($path, $excludedPath)) {
                    return $next($request);
                }
            }
            
            // Redirigir a linkiu.com.co
            $redirectUrl = 'https://linkiu.com.co' . ($path === '/' ? '' : $path);
            if ($request->getQueryString()) {
                $redirectUrl .= '?' . $request->getQueryString();
            }
            
            \Log::info('Redirecting from linkiu.bio to linkiu.com.co', [
                'original_url' => $request->fullUrl(),
                'redirect_url' => $redirectUrl,
                'host' => $host,
                'path' => $path
            ]);
            
            return redirect($redirectUrl, 301); // Redirección permanente
        }
        
        return $next($request);
    }
}
