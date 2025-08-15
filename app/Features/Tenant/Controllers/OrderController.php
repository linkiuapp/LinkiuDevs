<?php

namespace App\Features\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Order;
use App\Shared\Models\OrderItem;
use App\Shared\Models\Store;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\ShippingMethod;
use App\Features\TenantAdmin\Models\ShippingZone;
use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Features\TenantAdmin\Models\BankAccount;
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
    public function create(Request $request): View|RedirectResponse
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
    public function store(Request $request)
    {
        $store = $request->route('store');

        // Debug: Verificar headers de la petición
        \Log::info('OrderController@store - Headers:', [
            'Accept' => $request->header('Accept'),
            'Content-Type' => $request->header('Content-Type'),
            'X-Requested-With' => $request->header('X-Requested-With'),
            'expectsJson' => $request->expectsJson(),
            'wantsJson' => $request->wantsJson(),
            'ajax' => $request->ajax()
        ]);

        // Validación con manejo de errores para AJAX
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_address' => 'required_if:delivery_type,domicilio|string|max:500',
                'department' => 'required_if:delivery_type,domicilio|string|max:100',
                'city' => 'required_if:delivery_type,domicilio|string|max:100',
                'delivery_type' => 'required|in:domicilio,pickup',
                'payment_method' => 'required|in:efectivo,transferencia,contra_entrega',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'cash_amount' => 'nullable|numeric|min:0',
                'shipping_zone_id' => 'nullable|exists:shipping_zones,id',
                'payment_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
                'notes' => 'nullable|string|max:500',
                'coupon_code' => 'nullable|string|max:50'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si es una petición AJAX, devolver errores en JSON
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            
            // Si no es AJAX, lanzar la excepción normalmente
            throw $e;
        }

        // Verificar que hay productos en el carrito
        $cartItems = $request->session()->get('cart', []);
        if (empty($cartItems)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El carrito está vacío'
                ], 400);
            }
            
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

            // Preparar datos del pedido
            $orderData = [
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'] ?? null,
                'department' => $validated['department'] ?? null,
                'city' => $validated['city'] ?? null,
                'delivery_type' => $validated['delivery_type'],
                'payment_method' => $validated['payment_method'],
                'payment_method_id' => $validated['payment_method_id'],
                'shipping_cost' => $shippingCost,
                'subtotal' => 0, // Se calculará con los items
                'coupon_discount' => 0,
                'total' => 0, // Se calculará con los items
                'notes' => $validated['notes'] ?? null,
                'store_id' => $store->id,
                'status' => Order::STATUS_PENDING
            ];
            
            // Agregar cash_amount si está presente
            if (isset($validated['cash_amount']) && $validated['cash_amount'] > 0) {
                $orderData['cash_amount'] = $validated['cash_amount'];
            }
            
            // Crear el pedido
            $order = Order::create($orderData);

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
            
            // Aplicar cupón si se proporcionó
            if (!empty($validated['coupon_code'])) {
                $coupon = \App\Features\TenantAdmin\Models\Coupon::where('code', $validated['coupon_code'])
                    ->where('store_id', $store->id)
                    ->active()
                    ->first();
                    
                if ($coupon && $coupon->isApplicable($order->subtotal)) {
                    $discountAmount = $coupon->calculateDiscount($order->subtotal);
                    $order->update([
                        'coupon_code' => $validated['coupon_code'],
                        'coupon_discount' => $discountAmount
                    ]);
                    $order->recalculateTotals();
                    
                    // Registrar uso del cupón
                    $coupon->usageCount()->create([
                        'order_id' => $order->id,
                        'discount_applied' => $discountAmount,
                        'used_at' => now()
                    ]);
                }
            }

            // Procesar comprobante de pago si se subió
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $filename = $order->order_number . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Crear directorio si no existe
                $directory = public_path('storage/orders/payment-proofs');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Mover archivo usando estándar de imágenes
                $file->move($directory, $filename);
                $order->update(['payment_proof_path' => 'orders/payment-proofs/' . $filename]);
            }

            // Limpiar carrito
            $request->session()->forget('cart');

            // Guardar datos del pedido en sesión para la página de éxito
            $request->session()->put('last_order', [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'delivery_type' => $order->delivery_type,
                'payment_method' => $order->payment_method,
                'subtotal' => $order->subtotal,
                'shipping_cost' => $order->shipping_cost,
                'discount_amount' => $order->coupon_discount,
                'total' => $order->total,
                'items' => $order->items->load('product')
            ]);

            DB::commit();

            // Respuesta para AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido creado exitosamente',
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total' => $order->total
                    ]
                ]);
            }

            return redirect()
                ->route('tenant.checkout.success', $store->slug)
                ->with('success', 'Pedido creado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            // Log del error para debugging
            \Log::error('Error al crear pedido: ' . $e->getMessage(), [
                'store_id' => $store->id,
                'customer_name' => $validated['customer_name'] ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Respuesta para AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el pedido: ' . $e->getMessage()
                ], 500);
            }
            
            // Guardar error en sesión para redirect tradicional
            $request->session()->put('checkout_error', 'Error al procesar el pedido: ' . $e->getMessage());
            if (config('app.debug')) {
                $request->session()->put('technical_error', $e->getTraceAsString());
            }
            
            return redirect()
                ->route('tenant.checkout.error', $store->slug);
        }
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
                ->with(['items.product']) // TODO: 'statusHistory' cuando se implemente
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
            'department' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $store = $request->route('store');
        
        // Obtener método de envío de domicilio activo
        $shippingMethod = ShippingMethod::where('store_id', $store->id)
            ->where('type', 'domicilio')
            ->active()
            ->first();

        if (!$shippingMethod) {
            return response()->json([
                'success' => false, 
                'message' => 'Método de envío no disponible'
            ], 404);
        }

        // Obtener primera zona activa (por ahora no discriminamos por ubicación)
        $zone = $shippingMethod->activeZones()->ordered()->first();

        if (!$zone) {
            return response()->json([
                'success' => false, 
                'message' => 'No hay zonas de envío disponibles'
            ], 404);
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
            'formatted_total' => '$' . number_format($validated['subtotal'] + $finalCost, 0, ',', '.'),
            'zone_id' => $zone->id, // Agregar zone_id para enviarlo en el form
            'zone_name' => $zone->name,
            'estimated_time' => $zone->getEstimatedTimeLabel()
        ]);
    }

    /**
     * Get available shipping methods for the store
     */
    public function getShippingMethods(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        // Obtener métodos de envío activos
        $shippingMethods = ShippingMethod::where('store_id', $store->id)
            ->active()
            ->ordered()
            ->get()
            ->map(function ($method) {
                $data = [
                    'id' => $method->id,
                    'type' => $method->type,
                    'name' => $method->name,
                    'icon' => $method->getIcon(),
                    'type_label' => $method->getTypeLabel(),
                    'instructions' => $method->instructions,
                    'preparation_time' => $method->preparation_time,
                    'preparation_time_label' => $method->getPreparationTimeLabel(),
                ];

                if ($method->isPickup()) {
                    $data['is_free'] = true;
                    $data['cost'] = 0;
                    $data['pickup_address'] = $method->getPickupAddress();
                    $data['pickup_schedule'] = $method->getPickupSchedule();
                } else if ($method->isDomicilio()) {
                    $data['has_zones'] = $method->hasActiveZones();
                    $data['minimum_cost'] = $method->getMinimumCost();
                    $data['free_shipping_info'] = $method->getFreeShippingInfo();
                    
                    // Información de zonas para mostrar
                    $data['zones'] = $method->activeZones()
                        ->ordered()
                        ->get()
                        ->map(function ($zone) {
                            return [
                                'id' => $zone->id,
                                'name' => $zone->name,
                                'cost' => $zone->cost,
                                'formatted_cost' => '$' . number_format($zone->cost, 0, ',', '.'),
                                'estimated_time' => $zone->getEstimatedTimeLabel(),
                                'free_shipping_from' => $zone->free_shipping_from,
                                'free_shipping_message' => $zone->getFreeShippingMessage(),
                            ];
                        });
                }

                return $data;
            });

        return response()->json([
            'success' => true,
            'methods' => $shippingMethods
        ]);
    }

    /**
     * Get available payment methods for the store
     */
    public function getPaymentMethods(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        // Obtener métodos de pago activos
        $paymentMethods = PaymentMethod::where('store_id', $store->id)
            ->active()
            ->ordered()
            ->with('bankAccounts')
            ->get()
            ->map(function ($method) {
                $data = [
                    'id' => $method->id,
                    'type' => $method->type,
                    'name' => $method->name,
                    'instructions' => $method->instructions,
                    'require_proof' => $method->require_proof ?? false,
                    'available_for_pickup' => $method->available_for_pickup,
                    'available_for_delivery' => $method->available_for_delivery,
                ];

                // Agregar icono según el tipo
                $data['icon'] = match($method->type) {
                    'cash' => '💵',
                    'bank_transfer' => '🏦', 
                    'card_terminal' => '💳',
                    default => '💰'
                };

                // Si es transferencia bancaria, agregar datos de cuentas
                if ($method->isBankTransfer() && $method->bankAccounts->isNotEmpty()) {
                    $data['bank_accounts'] = $method->bankAccounts->map(function ($account) {
                        // Mapear tipos de cuenta a español
                        $accountTypeMap = [
                            'savings' => 'Cuenta de Ahorros',
                            'checking' => 'Cuenta Corriente',
                            'ahorros' => 'Cuenta de Ahorros',
                            'corriente' => 'Cuenta Corriente',
                        ];
                        
                        $accountType = $accountTypeMap[strtolower($account->account_type)] ?? $account->account_type ?? 'Cuenta Corriente';
                        
                        return [
                            'id' => $account->id,
                            'bank_name' => $account->bank_name,
                            'account_type' => $accountType,
                            'account_number' => $account->account_number,
                            'account_holder' => $account->account_holder,
                            'document_number' => $account->document_number,
                            'formatted_account_number' => $account->getFormattedAccountNumber(),
                            'full_account_info' => $account->getFullAccountInfo(),
                            'account_holder_with_document' => $account->getAccountHolderWithDocument(),
                        ];
                    });
                }

                return $data;
            });

        return response()->json([
            'success' => true,
            'methods' => $paymentMethods
        ]);
    }

    /**
     * Get order status for API
     */
    public function getOrderStatus(Request $request, Store $store, $orderId): JsonResponse
    {
        // La tienda se inyecta automáticamente desde la ruta
        
        // Buscar el pedido que pertenece a esta tienda
        $order = Order::where('id', (int)$orderId)
            ->where('store_id', $store->id)
            ->first();
            
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]
        ]);
    }

    /**
     * Get order status for API - Simple version with query parameters
     */
    public function getOrderStatusSimple(Request $request): JsonResponse
    {
        $orderId = $request->query('id');
        $store = $request->route('store');
        
        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'Order ID is required'
            ], 400);
        }
        
        // Buscar el pedido que pertenece a esta tienda
        $order = Order::where('id', $orderId)
            ->where('store_id', $store->id)
            ->first();
            
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]
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

    /**
     * Apply coupon code
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $store = $request->route('store');
        
        // Buscar cupón activo
        $coupon = \App\Features\TenantAdmin\Models\Coupon::where('code', $validated['coupon_code'])
            ->where('store_id', $store->id)
            ->active()
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no válido o expirado'
            ]);
        }

        // Verificar si el cupón es aplicable al subtotal
        if (!$coupon->isApplicable($validated['subtotal'])) {
            return response()->json([
                'success' => false,
                'message' => $coupon->getNotApplicableMessage($validated['subtotal'])
            ]);
        }

        // Calcular descuento
        $discountAmount = $coupon->calculateDiscount($validated['subtotal']);

        return response()->json([
            'success' => true,
            'message' => 'Cupón aplicado exitosamente',
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'value' => $coupon->value
            ],
            'discount_amount' => $discountAmount,
            'formatted_discount' => '$' . number_format($discountAmount, 0, ',', '.'),
            'new_total' => $validated['subtotal'] - $discountAmount,
            'formatted_new_total' => '$' . number_format($validated['subtotal'] - $discountAmount, 0, ',', '.')
        ]);
    }

    /**
     * Show checkout success page
     */
    public function success(Request $request): View
    {
        $store = $request->route('store');
        
        // Obtener el pedido por ID desde parámetros URL
        $orderId = $request->get('order');
        $order = null;
        
        if ($orderId) {
            $order = Order::where('id', $orderId)
                         ->where('store_id', $store->id)
                         ->first();
        }
        
        // Si no hay orden específica, intentar obtener de sesión como fallback
        if (!$order) {
            $orderData = $request->session()->get('last_order', []);
            // Crear un objeto temporal con los datos de sesión para compatibilidad
            $order = (object) $orderData;
        }
        
        return view('tenant::checkout.success', compact('store', 'order'));
    }

    /**
     * Show checkout error page
     */
    public function error(Request $request): View
    {
        $store = $request->route('store');
        
        $errorMessage = $request->session()->get('checkout_error', 'Ocurrió un error inesperado');
        $technicalError = $request->session()->get('technical_error');
        
        return view('tenant::checkout.error', compact('store', 'errorMessage', 'technicalError'));
    }


} 