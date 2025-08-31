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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'email-config:' . auth()->id() . ':' . $request->ip();
        
        // Allow 10 configuration changes per hour per user
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            
            \Log::warning('Email configuration rate limit exceeded', [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()->getName(),
                'retry_after' => $seconds
            ]);
            
            return response()->json([
                'message' => 'Demasiados intentos de configuración. Intenta nuevamente en ' . ceil($seconds / 60) . ' minutos.',
                'retry_after' => $seconds
            ], 429);
        }
        
        // Hit the rate limiter
        RateLimiter::hit($key, 3600); // 1 hour
        
        return $next($request);
    }
}