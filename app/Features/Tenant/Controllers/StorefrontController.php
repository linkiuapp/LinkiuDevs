<?php

namespace App\Features\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Store;
use App\Shared\Models\Location;
use App\Features\TenantAdmin\Models\Category;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\Slider;
use App\Features\TenantAdmin\Models\Coupon;
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

    /**
     * Show contact page with store locations
     */
    public function contact(Request $request)
    {
        // El middleware ya identificó la tienda
        $store = view()->shared('currentStore');

        // Cargar sedes activas con horarios y redes sociales
        $locations = $store->locations()
            ->where('is_active', true)
            ->with(['schedules', 'socialLinks'])
            ->orderByDesc('is_main') // Sede principal primero
            ->orderBy('name')
            ->get();

        // Calcular estado actual para cada sede
        foreach ($locations as $location) {
            $location->currentStatus = $this->calculateLocationStatus($location);
        }

        return view('tenant::storefront.contact', compact('store', 'locations'));
    }

    /**
     * Calculate current status for a location
     */
    private function calculateLocationStatus(Location $location): array
    {
        $now = \Carbon\Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0=Sunday, 6=Saturday
        $currentTime = $now->format('H:i');

        // Buscar horario de hoy
        $todaySchedule = $location->schedules()->where('day_of_week', $dayOfWeek)->first();

        if (!$todaySchedule || $todaySchedule->is_closed) {
            return [
                'status' => 'closed',
                'text' => 'Cerrado hoy',
                'color' => 'text-error-400',
                'bg' => 'bg-error-50',
                'border' => 'border-error-200'
            ];
        }

        // Verificar si está abierto ahora
        $isOpen = false;
        
        // Turno 1
        if ($todaySchedule->open_time_1 && $todaySchedule->close_time_1) {
            if ($currentTime >= $todaySchedule->open_time_1 && $currentTime <= $todaySchedule->close_time_1) {
                $isOpen = true;
            }
        }

        // Turno 2 (si existe)
        if (!$isOpen && $todaySchedule->open_time_2 && $todaySchedule->close_time_2) {
            if ($currentTime >= $todaySchedule->open_time_2 && $currentTime <= $todaySchedule->close_time_2) {
                $isOpen = true;
            }
        }

        if ($isOpen) {
            return [
                'status' => 'open',
                'text' => 'Abierto ahora',
                'color' => 'text-success-400',
                'bg' => 'bg-success-50',
                'border' => 'border-success-200'
            ];
        } else {
            // Está cerrado, calcular próxima apertura
            $nextOpen = $this->getNextOpenTime($location);
            
            return [
                'status' => 'closed',
                'text' => $nextOpen ? "Abre {$nextOpen}" : 'Cerrado',
                'color' => 'text-warning-400',
                'bg' => 'bg-warning-50',
                'border' => 'border-warning-200'
            ];
        }
    }

    /**
     * Get next opening time for a location
     */
    private function getNextOpenTime(Location $location): ?string
    {
        $now = \Carbon\Carbon::now();
        
        // Buscar en los próximos 7 días
        for ($i = 0; $i < 7; $i++) {
            $checkDate = $now->copy()->addDays($i);
            $dayOfWeek = $checkDate->dayOfWeek;
            
            $schedule = $location->schedules()->where('day_of_week', $dayOfWeek)->first();
            
            if (!$schedule || $schedule->is_closed) {
                continue;
            }

            // Si es hoy, verificar si aún puede abrir
            if ($i === 0) {
                $currentTime = $now->format('H:i');
                
                // Verificar turno 2 si el turno 1 ya pasó
                if ($schedule->open_time_2 && $schedule->open_time_2 > $currentTime) {
                    return "hoy a las {$schedule->open_time_2}";
                }
            } else {
                // Días futuros
                $dayName = $checkDate->locale('es')->dayName;
                return "{$dayName} a las {$schedule->open_time_1}";
            }
        }

        return null;
    }

    /**
     * Show the promotions page with all public and active coupons
     */
    public function promotions(Request $request)
    {
        $store = view()->shared('currentStore');
        
        // Si la tienda está inactiva o suspendida, mostrar mensaje
        if ($store->status !== 'active') {
            return view('tenant::storefront.inactive', compact('store'));
        }

        // Obtener cupones públicos, activos y disponibles
        $coupons = Coupon::where('store_id', $store->id)
            ->where('is_public', true)
            ->where('is_active', true)
            ->where(function ($query) {
                $now = now();
                // Verificar fechas de validez
                $query->where(function ($q) use ($now) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                });
            })
            ->where(function ($query) {
                // Verificar que no esté agotado
                $query->whereNull('max_uses')->orWhereRaw('current_uses < max_uses');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Agregar información de estado y formateo a cada cupón
        foreach ($coupons as $coupon) {
            $coupon->status_info = $this->getCouponStatusForFrontend($coupon);
            $coupon->formatted_discount = $this->formatDiscount($coupon);
            $coupon->conditions_text = $this->getConditionsText($coupon);
            $coupon->expiry_text = $this->getExpiryText($coupon);
        }

        return view('tenant::storefront.promotions', compact('store', 'coupons'));
    }

    /**
     * Get coupon status information for frontend display
     */
    private function getCouponStatusForFrontend(Coupon $coupon): array
    {
        $now = now();
        
        // Verificar si está próximo a vencer (menos de 3 días)
        if ($coupon->end_date && $coupon->end_date->diffInDays($now) <= 3) {
            return [
                'status' => 'expiring',
                'text' => '¡Últimos días!',
                'color' => 'text-warning-300',
                'bg' => 'bg-warning-50',
                'border' => 'border-warning-200'
            ];
        }

        // Verificar si está próximo a agotarse (menos del 20% de usos)
        if ($coupon->max_uses && $coupon->current_uses >= ($coupon->max_uses * 0.8)) {
            return [
                'status' => 'limited',
                'text' => '¡Pocas unidades!',
                'color' => 'text-error-300',
                'bg' => 'bg-error-50',
                'border' => 'border-error-200'
            ];
        }

        return [
            'status' => 'available',
            'text' => 'Disponible',
            'color' => 'text-success-300',
            'bg' => 'bg-success-50',
            'border' => 'border-success-200'
        ];
    }

    /**
     * Format discount value for display
     */
    private function formatDiscount(Coupon $coupon): string
    {
        if ($coupon->discount_type === 'percentage') {
            return $coupon->discount_value . '%';
        } else {
            return '$' . number_format($coupon->discount_value, 0, ',', '.');
        }
    }

    /**
     * Get conditions text for display
     */
    private function getConditionsText(Coupon $coupon): ?string
    {
        $conditions = [];

        if ($coupon->min_purchase_amount) {
            $conditions[] = 'Compra mínima: $' . number_format($coupon->min_purchase_amount, 0, ',', '.');
        }

        if ($coupon->max_discount_amount && $coupon->discount_type === 'percentage') {
            $conditions[] = 'Descuento máx: $' . number_format($coupon->max_discount_amount, 0, ',', '.');
        }

        if ($coupon->max_uses) {
            $remaining = max(0, $coupon->max_uses - $coupon->current_uses);
            $conditions[] = "Quedan {$remaining} usos";
        }

        return empty($conditions) ? null : implode(' • ', $conditions);
    }

    /**
     * Get expiry text for display
     */
    private function getExpiryText(Coupon $coupon): ?string
    {
        if ($coupon->end_date) {
            $diffInDays = $coupon->end_date->diffInDays(now());
            
            if ($diffInDays === 0) {
                return 'Vence hoy a las ' . $coupon->end_date->format('H:i');
            } elseif ($diffInDays === 1) {
                return 'Vence mañana';
            } elseif ($diffInDays <= 7) {
                return "Vence en {$diffInDays} días";
            } else {
                return 'Válido hasta ' . $coupon->end_date->format('d/m/Y');
            }
        }

        return null;
    }
} 