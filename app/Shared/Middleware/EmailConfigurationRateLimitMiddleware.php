<?php

namespace App\Shared\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class EmailConfigurationRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'email-config:' . $request->ip() . ':' . ($request->user()->id ?? 'guest');
        
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.",
                'retry_after' => $seconds
            ], 429);
        }
        
        RateLimiter::hit($key, 60); // 10 intentos por minuto
        
        return $next($request);
    }
}