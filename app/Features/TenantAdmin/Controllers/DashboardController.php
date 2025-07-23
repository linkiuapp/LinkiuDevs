<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Shared\Models\Order;

class DashboardController extends Controller
{
    /**
     * Show the dashboard for store admin
     */
    public function index(Request $request)
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');
        
        // Verificar que el usuario autenticado sea admin de esta tienda
        if (!auth()->check() || 
            auth()->user()->role !== 'store_admin' || 
            auth()->user()->store_id !== $store->id) {
            return redirect()->route('tenant.admin.login', ['store' => $store->slug]);
        }

        $user = auth()->user();
        
        // Obtener pedidos recientes (últimos 5)
        $recentOrders = Order::where('store_id', $store->id)
            ->with(['items.product.mainImage'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Por ahora, datos básicos para el dashboard
        $stats = [
            'store_name' => $store->name,
            'plan_name' => $store->plan->name,
            'store_status' => $store->status,
            'admin_name' => $user->name,
            'admin_email' => $user->email,
        ];

        return view('tenant-admin::dashboard', compact('store', 'stats', 'recentOrders'));
    }
} 