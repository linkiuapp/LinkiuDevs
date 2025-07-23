<?php

namespace App\Features\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Store;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Show the storefront (frontend) for a specific store
     */
    public function index(Request $request)
    {
        // El middleware ya identificó la tienda y la compartió en las vistas
        $store = view()->shared('currentStore');
        
        // Cargar la relación design para el frontend
        $store->load('design');

        // Si la tienda está inactiva o suspendida, mostrar mensaje
        if ($store->status !== 'active') {
            return view('tenant::storefront.inactive', compact('store'));
        }

        // Por ahora mostrar página "Próximamente"
        return view('tenant::storefront.home', compact('store'));
    }

    /**
     * Show specific product page (for future implementation)
     */
    public function product(Request $request, $productSlug)
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');

        // TODO: Implementar lógica de productos
        return view('tenant::storefront.product', compact('store', 'productSlug'));
    }

    /**
     * Show cart page (for future implementation)
     */
    public function cart(Request $request)
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');

        // TODO: Implementar lógica del carrito
        return view('tenant::storefront.cart', compact('store'));
    }

    /**
     * Get verification status for real-time updates
     */
    public function verificationStatus(Request $request)
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');

        return response()->json([
            'verified' => $store->verified
        ]);
    }
} 