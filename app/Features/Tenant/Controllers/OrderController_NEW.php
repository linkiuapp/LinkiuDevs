<?php

namespace App\Features\Tenant\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Order;
use App\Shared\Models\OrderItem;
use App\Shared\Models\Store;
use App\Features\TenantAdmin\Models\Product;
use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Features\TenantAdmin\Models\BankAccount;
use App\Features\TenantAdmin\Models\SimpleShipping;
use App\Features\TenantAdmin\Models\SimpleShippingZone;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ===============================
    // CHECKOUT METHODS (NO TOCAR)
    // ===============================
    
    /**
     * Show checkout form
     */
    public function create(Request $request): View|RedirectResponse
    {
        $store = $request->route('store');
        
        // Obtener productos del carrito desde sesión
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
                ->where('status', 'active')
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

        // Obtener configuración de envíos (NUEVO SISTEMA)
        $simpleShipping = SimpleShipping::getOrCreateForStore($store->id);
        $shippingMethods = $simpleShipping->getAvailableOptions();

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
                'cash_amount' => 'nullable|numeric|min:1',
                'coupon_code' => 'nullable|string|max:50',
                'shipping_zone_id' => 'nullable|exists:simple_shipping_zones,id',
                'notes' => 'nullable|string|max:500'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        try {
            // Obtener carrito
            $cartItems = $request->session()->get('cart', []);
            
            if (empty($cartItems)) {
                throw new \Exception('El carrito está vacío');
            }

            // Crear orden
            $order = Order::create([
                'store_id' => $store->id,
                'order_number' => $this->generateOrderNumber($store->id),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'] ?? null,
                'department' => $validated['department'] ?? null,
                'city' => $validated['city'] ?? null,
                'delivery_type' => $validated['delivery_type'],
                'payment_method' => $validated['payment_method'],
                'payment_method_id' => $validated['payment_method_id'],
                'cash_amount' => $validated['cash_amount'] ?? null,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'subtotal' => 0, // Se calculará después
                'shipping_cost' => 0, // Se calculará después
                'coupon_discount' => 0, // Se calculará después
                'total' => 0, // Se calculará después
                'created_at' => now(),
            ]);

            // Agregar productos a la orden
            foreach ($cartItems as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->active()
                    ->first();

                if (!$product) continue;

                $orderItemData = OrderItem::createFromProduct(
                    $product, 
                    $item['quantity'], 
                    $item['variants'] ?? null
                );

                $order->items()->create($orderItemData);
            }

            // Recalcular totales considerando envío gratis si aplica
            $order->recalculateTotals();
            
            if ($validated['delivery_type'] === 'domicilio' && isset($validated['shipping_zone_id'])) {
                $zone = SimpleShippingZone::find($validated['shipping_zone_id']);
                if ($zone) {
                    $finalShippingCost = $zone->getFinalCost($order->subtotal);
                    $order->update(['shipping_cost' => $finalShippingCost]);
                    $order->recalculateTotals();
                }
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
                
                $file->move($directory, $filename);
                $order->update(['payment_proof' => 'orders/payment-proofs/' . $filename]);
            }

            // Limpiar carrito
            $request->session()->forget('cart');

            DB::commit();

            // Respuesta para AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido creado exitosamente',
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total' => $order->total,
                        'formatted_total' => '$' . number_format($order->total, 0, ',', '.')
                    ]
                ]);
            }

            return redirect()
                ->route('tenant.checkout.success', $store->slug)
                ->with('order_id', $order->id);

        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Error creando orden:', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error procesando el pedido: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->with('error', 'Error procesando el pedido. Por favor intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Get shipping cost via AJAX (NEW SIMPLE SYSTEM)
     */
    public function getShippingCost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city' => 'required|string|max:100',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $store = $request->route('store');
        
        try {
            // Usar el NUEVO sistema de envíos simple
            $shipping = SimpleShipping::getOrCreateForStore($store->id);
            $shipping->load('activeZones');
            
            $result = $shipping->calculateShippingCost($validated['city'], $validated['subtotal']);
            
            if (!$result['available']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Envío no disponible para esta ubicación'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'cost' => $result['cost'],
                'formatted_cost' => '$' . number_format($result['cost'], 0, ',', '.'),
                'has_free_shipping' => $result['is_free'],
                'free_shipping_message' => $result['is_free'] ? '¡Envío GRATIS!' : null,
                'total' => $validated['subtotal'] + $result['cost'],
                'formatted_total' => '$' . number_format($validated['subtotal'] + $result['cost'], 0, ',', '.'),
                'zone_id' => $result['zone_id'] ?? null,
                'zone_name' => $result['zone_name'] ?? null,
                'estimated_time' => $result['estimated_time'] ?? null,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error calculando costo de envío:', [
                'store_id' => $store->id,
                'city' => $validated['city'],
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error calculando costo de envío'
            ], 500);
        }
    }

    /**
     * Get shipping methods for checkout
     */
    public function getShippingMethods(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            // Usar el NUEVO sistema
            $shipping = SimpleShipping::getOrCreateForStore($store->id);
            $shipping->load('activeZones');
            
            $methods = $shipping->getAvailableOptions();
            
            return response()->json([
                'success' => true,
                'methods' => $methods
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo métodos de envío:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error cargando métodos de envío'
            ], 500);
        }
    }

    /**
     * Get payment methods for checkout
     */
    public function getPaymentMethods(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
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
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo métodos de pago:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error cargando métodos de pago'
            ], 500);
        }
    }

    /**
     * Show success page
     */
    public function success(Request $request): View
    {
        $store = $request->route('store');
        $orderId = $request->get('order') ?? $request->session()->get('order_id');
        
        $order = null;
        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('store_id', $store->id)
                ->with(['items.product'])
                ->first();
        }
        
        return view('tenant::checkout.success', compact('store', 'order'));
    }

    /**
     * Show error page
     */
    public function error(Request $request): View
    {
        $store = $request->route('store');
        
        $errorMessage = $request->session()->get('checkout_error', 'Ocurrió un error inesperado');
        $technicalError = $request->session()->get('technical_error');
        
        return view('tenant::checkout.error', compact('store', 'errorMessage', 'technicalError'));
    }

    // ===============================
    // CART METHODS (REESCRITO LIMPIO)
    // ===============================

    /**
     * Add product to cart
     */
    public function addToCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'required|integer|min:1|max:100',
                'variants' => 'nullable|array'
            ]);
            
            // Verificar que el producto pertenece a la tienda y está activo
            $product = Product::where('id', $validated['product_id'])
                ->where('store_id', $store->id)
                ->where('status', 'active')
                ->first();
                
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }
            
            // Verificar stock disponible
            if ($product->stock !== null && $product->stock < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente. Disponible: ' . $product->stock
                ], 422);
            }
            
            // Obtener carrito actual
            $cart = $request->session()->get('cart', []);
            
            // Crear clave única para el producto (incluye variantes)
            $cartKey = $validated['product_id'];
            if (!empty($validated['variants'])) {
                $cartKey .= '_' . md5(json_encode($validated['variants']));
            }
            
            // Si el producto ya existe, sumar cantidad
            if (isset($cart[$cartKey])) {
                $newQuantity = $cart[$cartKey]['quantity'] + $validated['quantity'];
                
                // Verificar stock total
                if ($product->stock !== null && $product->stock < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuficiente. Ya tienes ' . $cart[$cartKey]['quantity'] . ' en el carrito. Disponible: ' . $product->stock
                    ], 422);
                }
                
                $cart[$cartKey]['quantity'] = $newQuantity;
            } else {
                // Agregar nuevo producto
                $cart[$cartKey] = [
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'variants' => $validated['variants'] ?? [],
                    'added_at' => now()->toISOString()
                ];
            }
            
            // Guardar en sesión
            $request->session()->put('cart', $cart);
            
            // Calcular totales
            $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = $this->calculateCartTotal($cart, $store->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'product_name' => $product->name,
                'quantity_added' => $validated['quantity'],
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'formatted_cart_total' => '$' . number_format($cartTotal, 0, ',', '.')
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error agregando al carrito:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error agregando producto al carrito'
            ], 500);
        }
    }

    /**
     * Get cart contents
     */
    public function getCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $cart = $request->session()->get('cart', []);
            
            if (empty($cart)) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'subtotal' => 0,
                    'total' => 0,
                    'formatted_total' => '$0',
                    'count' => 0,
                    'cart_count' => 0,
                    'cart_total' => 0,
                    'formatted_cart_total' => '$0'
                ]);
            }
            
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

            $cartCount = array_sum(array_column($cart, 'quantity'));
            
            return response()->json([
                'success' => true,
                'items' => $items,
                'subtotal' => $total,
                'total' => $total,
                'formatted_total' => '$' . number_format($total, 0, ',', '.'),
                'count' => $cartCount,
                'cart_count' => $cartCount,
                'cart_total' => $total,
                'formatted_cart_total' => '$' . number_format($total, 0, ',', '.')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo carrito:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error cargando carrito'
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $validated = $request->validate([
                'item_key' => 'required|string',
                'quantity' => 'required|integer|min:1|max:100'
            ]);
            
            $cart = $request->session()->get('cart', []);
            
            if (!isset($cart[$validated['item_key']])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado en el carrito'
                ], 404);
            }
            
            // Verificar stock
            $item = $cart[$validated['item_key']];
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $store->id)
                ->where('status', 'active')
                ->first();
                
            if (!$product) {
                // Eliminar producto que ya no existe
                unset($cart[$validated['item_key']]);
                $request->session()->put('cart', $cart);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no disponible, eliminado del carrito'
                ], 404);
            }
            
            if ($product->stock !== null && $product->stock < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente. Disponible: ' . $product->stock
                ], 422);
            }
            
            // Actualizar cantidad
            $cart[$validated['item_key']]['quantity'] = $validated['quantity'];
            $request->session()->put('cart', $cart);
            
            // Calcular totales
            $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = $this->calculateCartTotal($cart, $store->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Carrito actualizado',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'formatted_cart_total' => '$' . number_format($cartTotal, 0, ',', '.')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error actualizando carrito:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando carrito'
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $validated = $request->validate([
                'item_key' => 'required|string'
            ]);

            $cart = $request->session()->get('cart', []);
            
            if (isset($cart[$validated['item_key']])) {
                unset($cart[$validated['item_key']]);
                $request->session()->put('cart', $cart);
            }

            $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = $this->calculateCartTotal($cart, $store->id);

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'formatted_cart_total' => '$' . number_format($cartTotal, 0, ',', '.')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error eliminando del carrito:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error eliminando producto del carrito'
            ], 500);
        }
    }

    /**
     * Clear cart
     */
    public function clearCart(Request $request): JsonResponse
    {
        try {
            $request->session()->forget('cart');
            
            return response()->json([
                'success' => true,
                'message' => 'Carrito vaciado',
                'cart_count' => 0,
                'cart_total' => 0,
                'formatted_cart_total' => '$0'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error limpiando carrito:', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error limpiando carrito'
            ], 500);
        }
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $validated = $request->validate([
                'coupon_code' => 'required|string|max:50'
            ]);
            
            // Buscar cupón
            $coupon = \App\Features\TenantAdmin\Models\Coupon::where('code', $validated['coupon_code'])
                ->where('store_id', $store->id)
                ->active()
                ->first();
                
            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no válido o expirado'
                ], 422);
            }
            
            // Calcular subtotal del carrito
            $cart = $request->session()->get('cart', []);
            $cartTotal = $this->calculateCartTotal($cart, $store->id);
            
            if ($cartTotal <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El carrito está vacío'
                ], 422);
            }
            
            // Verificar si el cupón es aplicable
            if (!$coupon->isApplicable($cartTotal)) {
                $minAmount = $coupon->min_amount ? '$' . number_format($coupon->min_amount, 0, ',', '.') : null;
                $message = $minAmount 
                    ? "Este cupón requiere una compra mínima de {$minAmount}"
                    : "Este cupón no es aplicable a tu carrito actual";
                    
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            
            // Calcular descuento
            $discountAmount = $coupon->calculateDiscount($cartTotal);
            
            return response()->json([
                'success' => true,
                'message' => 'Cupón aplicado correctamente',
                'coupon_code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'formatted_discount' => '$' . number_format($discountAmount, 0, ',', '.'),
                'cart_total' => $cartTotal,
                'final_total' => $cartTotal - $discountAmount,
                'formatted_final_total' => '$' . number_format($cartTotal - $discountAmount, 0, ',', '.')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error aplicando cupón:', [
                'store_id' => $store->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error aplicando cupón'
            ], 500);
        }
    }

    // ===============================
    // HELPER METHODS
    // ===============================

    /**
     * Calculate cart total for a store
     */
    private function calculateCartTotal(array $cart, int $storeId): float
    {
        $total = 0;
        
        foreach ($cart as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $storeId)
                ->where('status', 'active')
                ->first();
                
            if ($product) {
                $total += $product->price * $item['quantity'];
            }
        }
        
        return $total;
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(int $storeId): string
    {
        $prefix = 'ORD';
        $timestamp = now()->format('ymdHis');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$storeId}-{$timestamp}-{$random}";
    }

    /**
     * Format product variants for display
     */
    private function formatVariants(array $variants): string
    {
        if (empty($variants)) {
            return '';
        }
        
        $formatted = [];
        foreach ($variants as $key => $value) {
            $formatted[] = ucfirst($key) . ': ' . $value;
        }
        
        return implode(', ', $formatted);
    }

    /**
     * Order tracking (preserved from original)
     */
    public function tracking(Request $request): View
    {
        $store = $request->route('store');
        $orderNumber = $request->get('order');
        
        $order = null;
        if ($orderNumber) {
            $order = Order::where('order_number', $orderNumber)
                ->where('store_id', $store->id)
                ->with(['items.product'])
                ->first();
        }

        return view('tenant::orders.tracking', compact('order', 'store'));
    }
}

