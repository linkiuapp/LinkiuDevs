<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Shared\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form for store admin
     */
    public function showLogin(Request $request)
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');
        
        return view('tenant-admin::auth.login', compact('store'));
    }

    /**
     * Handle login attempt for store admin
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');

        // Buscar el usuario admin de esta tienda específica
        $user = User::where('email', $credentials['email'])
            ->where('role', 'store_admin')
            ->where('store_id', $store->id)
            ->first();

        // Verificar si existe el usuario pero con problemas de asignación
        if (!$user) {
            $userExists = User::where('email', $credentials['email'])
                ->where('role', 'store_admin')
                ->first();
                
            if ($userExists && !$userExists->store_id) {
                // Usuario existe pero no tiene store_id asignado
                \Log::warning('Usuario store_admin sin store_id intentando acceder', [
                    'email' => $credentials['email'],
                    'user_id' => $userExists->id,
                    'store_slug' => $store->slug,
                    'store_id' => $store->id
                ]);
                
                return back()->withErrors([
                    'email' => 'Tu cuenta no está correctamente asignada a una tienda. Contacta al administrador del sistema.',
                ])->withInput();
            }
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros para esta tienda.',
            ])->withInput();
        }

        // Autenticar al usuario
        Auth::login($user);
        
        // Actualizar último login
        $user->updateLastLogin();

        // Regenerar sesión por seguridad
        $request->session()->regenerate();

        return redirect()->intended(route('tenant.admin.dashboard', ['store' => $store->slug]));
    }

    /**
     * Handle logout for store admin
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');
        
        return redirect()->route('tenant.admin.login', $store->slug);
    }
} 