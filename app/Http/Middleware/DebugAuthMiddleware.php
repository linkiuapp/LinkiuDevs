<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if debug access is enabled
        if (!env('DEBUG_ACCESS_ENABLED', false)) {
            abort(404);
        }

        // Check if already authenticated in session
        if ($request->session()->get('debug_authenticated')) {
            return $next($request);
        }

        // Check for authentication attempt
        if ($request->isMethod('post')) {
            $username = $request->input('username');
            $password = $request->input('password');
            
            $validUsername = env('DEBUG_USERNAME');
            $validPassword = env('DEBUG_PASSWORD');
            
            if ($username === $validUsername && $password === $validPassword) {
                $request->session()->put('debug_authenticated', true);
                return redirect()->to($request->url());
            }
            
            return back()->withErrors(['auth' => 'Credenciales incorrectas']);
        }

        // Show login form
        return response()->view('system-debug.login');
    }
}