<?php

namespace App\Features\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Store;
use App\Features\TenantAdmin\Models\Category;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\Slider;
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

        // Cargar sliders activos y visibles para esta tienda
        $sliders = Slider::forStore($store->id)
            ->currentlyVisible()
            ->ordered()
            ->get();

        return view('tenant::storefront.home', compact('store', 'sliders'));
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

    /**
     * Show all categories page
     */
    public function categories(Request $request)
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');
        $store->load('design');

        // Si la tienda está inactiva, mostrar mensaje
        if ($store->status !== 'active') {
            return view('tenant::storefront.inactive', compact('store'));
        }

        // Obtener categorías principales activas con iconos
        $categories = Category::where('store_id', $store->id)
            ->active()
            ->main() // Solo categorías principales (sin padre)
            ->with(['icon', 'children' => function($query) {
                $query->active()->with('icon');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('tenant::storefront.categories', compact('store', 'categories'));
    }

    /**
     * Show category with products and subcategories
     */
    public function category(Request $request, $store, $categorySlug = null)
    {
        // Si $categorySlug es null, significa que $store contiene el categorySlug
        if ($categorySlug === null && is_string($store)) {
            $categorySlug = $store;
        }

        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');
        $store->load('design');

        // Si la tienda está inactiva, mostrar mensaje
        if ($store->status !== 'active') {
            return view('tenant::storefront.inactive', compact('store'));
        }

        // Buscar la categoría por slug
        $category = Category::where('store_id', $store->id)
            ->where('slug', $categorySlug)
            ->active()
            ->with(['icon', 'parent'])
            ->first();

        if (!$category) {
            abort(404, 'Categoría no encontrada');
        }

        // Obtener subcategorías si las tiene
        $subcategories = $category->children()
            ->active()
            ->with('icon')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Obtener productos de esta categoría
        $products = Product::whereHas('categories', function($query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->with(['images', 'categories'])
            ->orderBy('name')
            ->get();

        // Construir breadcrumbs
        $breadcrumbs = $this->buildBreadcrumbs($category);

        return view('tenant::storefront.category', compact(
            'store', 
            'category', 
            'subcategories', 
            'products',
            'breadcrumbs'
        ));
    }

    /**
     * Build breadcrumbs for category navigation
     */
    private function buildBreadcrumbs($category)
    {
        $store = view()->shared('currentStore');
        $breadcrumbs = [];
        
        // Agregar "Inicio"
        $breadcrumbs[] = [
            'name' => 'Inicio',
            'url' => route('tenant.home', $store->slug)
        ];

        // Agregar "Categorías"
        $breadcrumbs[] = [
            'name' => 'Categorías',
            'url' => route('tenant.categories', $store->slug)
        ];

        // Si tiene padre, agregarlo
        if ($category->parent) {
            $breadcrumbs[] = [
                'name' => $category->parent->name,
                'url' => route('tenant.category', [$store->slug, $category->parent->slug])
            ];
        }

        // Agregar categoría actual (sin link)
        $breadcrumbs[] = [
            'name' => $category->name,
            'url' => null
        ];

        return $breadcrumbs;
    }
} 