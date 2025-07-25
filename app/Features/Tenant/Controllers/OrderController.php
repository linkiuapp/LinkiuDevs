<?php

namespace App\Features\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Order;
use App\Shared\Models\OrderItem;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\ShippingMethod;
use App\Features\TenantAdmin\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Show checkout form
     */
    public function create(Request $request): View
    {
        $store = $request->route('store');
        
        // Obtener productos del carrito (esto debería venir de sesión/localStorage)
        // Por ahora simulamos que viene del request
        $cartItems = $request->session()->get('cart', []);
        
        if (empty($cartItems)) {
            return redirect()
                ->route('tenant.home', $store->slug)
                ->with('error', 'El carrito está vacío');
        }

        // Obtener productos completos del carrito
        $products = [];
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $store->id)
                ->active()
                ->first();
                
            if ($product) {
                $itemData = OrderItem::createFromProduct(
                    $product, 
                    $item['quantity'], 
                    $item['variants'] ?? null
                );
                
                $products[] = array_merge($itemData, [
                    'product' => $product,
                    'variant_display' => $this->formatVariants($item['variants'] ?? [])
                ]);
                
                $subtotal += $itemData['item_total'];
            }
        }

        // Obtener métodos de envío activos
        $shippingMethods = ShippingMethod::where('store_id', $store->id)
            ->active()
            ->with('activeZones')
            ->ordered()
            ->get();

        // Departamentos de Colombia (estático para MVP)
        $departments = [
            'Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 
            'Caldas', 'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 
            'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare', 'Huila', 'La Guajira', 
            'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 
            'Risaralda', 'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 
            'Valle del Cauca', 'Vaupés', 'Vichada'
        ];

        return view('tenant::checkout.create', compact('products', 'subtotal', 'shippingMethods', 'departments', 'store'));
    }

    /**
     * Process checkout and create order
     */
    public function store(Request $request): RedirectResponse
    {
        $store = $request->route('store');

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'department' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'delivery_type' => 'required|in:domicilio,pickup',
            'payment_method' => 'required|in:transferencia,contra_entrega,efectivo',
            'shipping_zone_id' => 'nullable|exists:shipping_zones,id',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
            'notes' => 'nullable|string|max:500',
            'terms_accepted' => 'required|accepted'
        ]);

        // Verificar que hay productos en el carrito
        $cartItems = $request->session()->get('cart', []);
        if (empty($cartItems)) {
            return redirect()
                ->route('tenant.home', $store->slug)
                ->with('error', 'El carrito está vacío');
        }

        try {
            DB::beginTransaction();

            // Calcular costos de envío
            $shippingCost = 0;
            if ($validated['delivery_type'] === 'domicilio' && isset($validated['shipping_zone_id'])) {
                $zone = ShippingZone::where('id', $validated['shipping_zone_id'])
                    ->where('store_id', $store->id)
                    ->first();
                    
                if ($zone) {
                    // El costo se calculará después con el subtotal
                    $shippingCost = $zone->cost;
                }
            }

            // Crear el pedido
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'department' => $validated['department'],
                'city' => $validated['city'],
                'delivery_type' => $validated['delivery_type'],
                'payment_method' => $validated['payment_method'],
                'shipping_cost' => $shippingCost,
                'subtotal' => 0, // Se calculará con los items
                'coupon_discount' => 0,
                'total' => 0, // Se calculará con los items
                'notes' => $validated['notes'],
                'store_id' => $store->id,
                'status' => Order::STATUS_PENDING
            ]);

            // Agregar items del carrito al pedido
            foreach ($cartItems as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->active()
                    ->first();
                    
                if (!$product) {
                    throw new \Exception('Producto no disponible: ' . $item['product_id']);
                }

                $orderItemData = OrderItem::createFromProduct(
                    $product, 
                    $item['quantity'], 
                    $item['variants'] ?? null
                );

                $order->items()->create($orderItemData);
            }

            // Recalcular totales considerando envío gratis si aplica
            $order->recalculateTotals();
            
            if ($validated['delivery_type'] === 'domicilio' && isset($zone)) {
                $finalShippingCost = $zone->getFinalCost($order->subtotal);
                $order->update(['shipping_cost' => $finalShippingCost]);
                $order->recalculateTotals();
            }

            // Procesar comprobante de pago si se subió
            if ($request->hasFile('payment_proof')) {
                $filename = $order->order_number . '_' . time() . '.' . $request->file('payment_proof')->getClientOriginalExtension();
                $request->file('payment_proof')->storeAs('orders/payment-proofs', $filename, 'public');
                $order->update(['payment_proof_path' => $filename]);
            }

            // Limpiar carrito
            $request->session()->forget('cart');

            DB::commit();

            return redirect()
                ->route('tenant.order.success', ['store' => $store->slug, 'order' => $order->order_number])
                ->with('success', 'Pedido creado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withErrors(['error' => 'Error al procesar el pedido: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show order success page
     */
    public function success(Request $request, $storeSlug, $orderNumber): View
    {
        $store = $request->route('store');
        
        $order = Order::where('order_number', $orderNumber)
            ->where('store_id', $store->id)
            ->with(['items.product', 'statusHistory'])
            ->firstOrFail();

        return view('tenant::checkout.success', compact('order', 'store'));
    }

    /**
     * Show order tracking page
     */
    public function tracking(Request $request): View
    {
        $store = $request->route('store');
        $order = null;

        if ($request->filled('order_number')) {
            $order = Order::where('order_number', $request->order_number)
                ->where('store_id', $store->id)
                ->with(['items.product', 'statusHistory' => function($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->first();
        }

        return view('tenant::orders.tracking', compact('order', 'store'));
    }

    /**
     * Get shipping cost via AJAX
     */
    public function getShippingCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:shipping_zones,id',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $store = $request->route('store');
        $zone = ShippingZone::where('id', $validated['zone_id'])
            ->where('store_id', $store->id)
            ->first();

        if (!$zone) {
            return response()->json(['success' => false, 'message' => 'Zona no encontrada'], 404);
        }

        $finalCost = $zone->getFinalCost($validated['subtotal']);
        $hasFreeShipping = $zone->hasFreeShipping($validated['subtotal']);

        return response()->json([
            'success' => true,
            'cost' => $finalCost,
            'formatted_cost' => '$' . number_format($finalCost, 0, ',', '.'),
            'has_free_shipping' => $hasFreeShipping,
            'free_shipping_message' => $zone->getFreeShippingMessage(),
            'total' => $validated['subtotal'] + $finalCost,
            'formatted_total' => '$' . number_format($validated['subtotal'] + $finalCost, 0, ',', '.')
        ]);
    }

    /**
     * Add product to cart (AJAX)
     */
    public function addToCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variants' => 'nullable|array'
        ]);

        // Verificar que el producto pertenece a la tienda
        $product = Product::where('id', $validated['product_id'])
            ->where('store_id', $store->id)
            ->active()
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Producto no disponible'], 404);
        }

        // Obtener carrito actual
        $cart = $request->session()->get('cart', []);
        
        // Crear clave única para el item (producto + variantes)
        $itemKey = $validated['product_id'] . '_' . md5(serialize($validated['variants'] ?? []));
        
        // Agregar o actualizar item en carrito
        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $validated['quantity'];
        } else {
            $cart[$itemKey] = [
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'variants' => $validated['variants'] ?? null
            ];
        }

        // Guardar carrito en sesión
        $request->session()->put('cart', $cart);

        // Calcular datos del carrito
        $cartCount = array_sum(array_column($cart, 'quantity'));
        $cartTotal = $this->calculateCartTotal($cart, $store->id);

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'formatted_cart_total' => '$' . number_format($cartTotal, 0, ',', '.')
        ]);
    }

    /**
     * Get cart contents
     */
    public function getCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        $cart = $request->session()->get('cart', []);
        
        $items = [];
        $total = 0;

        foreach ($cart as $key => $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $store->id)
                ->with('mainImage')
                ->first();
                
            if ($product) {
                $itemData = OrderItem::createFromProduct(
                    $product, 
                    $item['quantity'], 
                    $item['variants'] ?? null
                );
                
                $items[] = array_merge($itemData, [
                    'key' => $key,
                    'product' => $product,
                    'variant_display' => $this->formatVariants($item['variants'] ?? [])
                ]);
                
                $total += $itemData['item_total'];
            }
        }

        return response()->json([
            'success' => true,
            'items' => $items,
            'total' => $total,
            'formatted_total' => '$' . number_format($total, 0, ',', '.'),
            'count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_key' => 'required|string'
        ]);

        $cart = $request->session()->get('cart', []);
        
        if (isset($cart[$validated['item_key']])) {
            unset($cart[$validated['item_key']]);
            $request->session()->put('cart', $cart);
        }

        $cartCount = array_sum(array_column($cart, 'quantity'));
        $cartTotal = $this->calculateCartTotal($cart, $request->route('store')->id);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'formatted_cart_total' => '$' . number_format($cartTotal, 0, ',', '.')
        ]);
    }

    /**
     * Clear cart
     */
    public function clearCart(Request $request): JsonResponse
    {
        $request->session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Carrito vaciado',
            'cart_count' => 0,
            'cart_total' => 0,
            'formatted_cart_total' => '$0'
        ]);
    }

    /**
     * Calculate cart total
     */
    private function calculateCartTotal(array $cart, int $storeId): float
    {
        $total = 0;

        foreach ($cart as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $storeId)
                ->with('mainImage')
                ->first();
                
            if ($product) {
                $itemData = OrderItem::createFromProduct(
                    $product, 
                    $item['quantity'], 
                    $item['variants'] ?? null
                );
                
                $total += $itemData['item_total'];
            }
        }

        return $total;
    }

    /**
     * Format variants for display
     */
    private function formatVariants(array $variants): string
    {
        if (empty($variants)) {
            return '';
        }

        $display = [];
        foreach ($variants as $key => $value) {
            if ($key !== 'precio_modificador') {
                $display[] = ucfirst($key) . ': ' . $value;
            }
        }

        return implode(', ', $display);
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_key' => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = $request->session()->get('cart', []);
        
        if (isset($cart[$validated['item_key']])) {
            $cart[$validated['item_key']]['quantity'] = $validated['quantity'];
            $request->session()->put('cart', $cart);
        }

        $cartCount = array_sum(array_column($cart, 'quantity'));
        $cartTotal = $this->calculateCartTotal($cart, $request->route('store')->id);

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada',
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'formatted_cart_total' => '$' . number_format($cartTotal, 0, ',', '.')
        ]);
    }
} 