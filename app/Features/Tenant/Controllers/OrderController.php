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
                ->where('is_active', true)
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
                'customer_address' => 'required_if:delivery_type,local,nacional,domicilio|string|max:500',
                'department' => 'required_if:delivery_type,nacional|string|max:100',
                'city' => 'required_if:delivery_type,local,nacional,domicilio|string|max:100',
                'delivery_type' => 'required|in:pickup,local,nacional,domicilio',
                'payment_method' => 'required|in:efectivo,transferencia,contra_entrega',
                'payment_method_id' => 'required|exists:payment_methods,id',
                'cash_amount' => 'nullable|numeric|min:1',
                'coupon_code' => 'nullable|string|max:50',
                'shipping_zone_id' => 'nullable|exists:simple_shipping_zones,id',
                'notes' => 'nullable|string|max:500',
                'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB máximo
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
                'order_number' => Order::generateOrderNumber($store->id),
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

            // Mapear delivery_type para la base de datos (ENUM solo permite 'domicilio' y 'pickup')
            $dbDeliveryType = $validated['delivery_type'];
            if ($validated['delivery_type'] === 'local' || $validated['delivery_type'] === 'nacional') {
                $dbDeliveryType = 'domicilio'; // En BD se guarda como 'domicilio'
            }
            
            // Determinar el tipo real para la lógica de shipping
            $realDeliveryType = $validated['delivery_type'];
            if ($validated['delivery_type'] === 'domicilio') {
                // Si viene 'domicilio', determinar si es local o nacional según la ciudad
                if (isset($validated['department']) && !empty($validated['department'])) {
                    $realDeliveryType = 'nacional';
                } else {
                    $realDeliveryType = 'local';
                }
            }
            
            // Actualizar en la orden (usar el valor compatible con ENUM)
            // Agregar metadata del tipo real en las notas si es necesario
            $orderNotes = $validated['notes'] ?? '';
            if ($realDeliveryType !== $dbDeliveryType) {
                $orderNotes = "[SHIPPING_TYPE:{$realDeliveryType}] " . $orderNotes;
            }
            
            $order->update([
                'delivery_type' => $dbDeliveryType,
                'notes' => $orderNotes
            ]);
            
            // Calcular subtotal primero
            $order->recalculateTotals();
            
            // Calcular shipping cost si es envío nacional
            $shippingCost = 0;
            if ($realDeliveryType === 'nacional' && isset($validated['city'])) {
                $simpleShipping = SimpleShipping::getOrCreateForStore($store->id);
                $simpleShipping->load('activeZones');
                
                $shippingResult = $simpleShipping->calculateShippingCost($validated['city'], $order->subtotal);
                if ($shippingResult['available']) {
                    $shippingCost = $shippingResult['cost'];
                }
            } elseif ($realDeliveryType === 'local') {
                $simpleShipping = SimpleShipping::getOrCreateForStore($store->id);
                if ($simpleShipping->local_enabled) {
                    $shippingCost = $simpleShipping->local_cost;
                    
                    // Aplicar envío gratis si aplica
                    if ($simpleShipping->local_free_from > 0 && $order->subtotal >= $simpleShipping->local_free_from) {
                        $shippingCost = 0;
                    }
                }
            }
            
            // Actualizar shipping cost
            $order->update(['shipping_cost' => $shippingCost]);
            $order->recalculateTotals();
            
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
                $order->update(['payment_proof_path' => 'orders/payment-proofs/' . $filename]);
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
            'department' => 'nullable|string|max:100',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $store = $request->route('store');
        
        \Log::info('🚚 SHIPPING COST REQUEST:', [
            'store_id' => $store->id,
            'validated_data' => $validated,
            'request_data' => $request->all()
        ]);
        
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('🚚 VALIDATION ERROR:', [
                'store_id' => $store->id,
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('🔥 ERROR CALCULANDO COSTO DE ENVÍO:', [
                'store_id' => $store->id,
                'city' => $validated['city'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error calculando costo de envío',
                'debug_error' => $e->getMessage()
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
     * Add product to cart (SIMPLIFICADO)
     */
    public function addToCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            \Log::info('🛒 ADD TO CART REQUEST:', [
                'store_id' => $store->id,
                'request_data' => $request->all(),
                'session_id' => session()->getId()
            ]);
        
        $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'required|integer|min:1|max:100',
            'variants' => 'nullable|array'
        ]);

            // Verificar que el producto pertenece a la tienda y está activo
        $product = Product::where('id', $validated['product_id'])
            ->where('store_id', $store->id)
                ->where('is_active', true)
                ->with('mainImage')
            ->first();

        if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
        }

        // Obtener carrito actual
        $cart = $request->session()->get('cart', []);
            \Log::info('🛒 CART BEFORE:', ['cart' => $cart]);
            
            // Crear clave única para el producto (incluye variantes)
            $cartKey = $validated['product_id'];
            if (!empty($validated['variants'])) {
                $cartKey .= '_' . md5(json_encode($validated['variants']));
            }
            
            // Si el producto ya existe, sumar cantidad
            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['quantity'] += $validated['quantity'];
        } else {
                // Agregar nuevo producto
                $cart[$cartKey] = [
                'product_id' => $validated['product_id'],
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                'quantity' => $validated['quantity'],
                    'variants' => $validated['variants'] ?? [],
                    'image_url' => $product->main_image_url ?? null,
                    'added_at' => now()->toISOString()
            ];
        }

            // Guardar en sesión con múltiples métodos
        $request->session()->put('cart', $cart);
            $request->session()->save(); // Forzar guardar
            
            \Log::info('🛒 CART AFTER SAVE:', [
                'cart' => $cart,
                'session_id' => session()->getId(),
                'cart_verification' => $request->session()->get('cart')
            ]);
            
            // Calcular totales SIMPLES
        $cartCount = array_sum(array_column($cart, 'quantity'));
            $cartTotal = 0;
            foreach ($cart as $item) {
                $cartTotal += $item['product_price'] * $item['quantity'];
            }

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
                'product_name' => $product->name,
                'quantity_added' => $validated['quantity'],
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
                'formatted_cart_total' => '$' . number_format($cartTotal, 0, ',', '.'),
                'debug_session_id' => session()->getId(),
                'debug_cart_key' => $cartKey
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('🔥 ERROR AGREGANDO AL CARRITO:', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error agregando producto al carrito',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cart contents (SIMPLIFICADO)
     */
    public function getCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
        $cart = $request->session()->get('cart', []);
            
            \Log::info('🛒 GET CART REQUEST:', [
                'store_id' => $store->id,
                'session_id' => session()->getId(),
                'cart_raw' => $cart,
                'cart_count' => count($cart)
            ]);
            
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
                // Si el item ya tiene product_name (del nuevo formato), usarlo directamente
                if (isset($item['product_name']) && isset($item['product_price'])) {
                    $itemTotal = $item['product_price'] * $item['quantity'];
                    
                    $items[] = [
                        'key' => $key,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'product_price' => $item['product_price'],
                        'quantity' => $item['quantity'],
                        'item_total' => $itemTotal,
                        'formatted_price' => '$' . number_format($item['product_price'], 0, ',', '.'),
                        'formatted_total' => '$' . number_format($itemTotal, 0, ',', '.'),
                        'variants' => $item['variants'] ?? [],
                        'variant_display' => $this->formatVariants($item['variants'] ?? []),
                        'image_url' => $item['image_url'] ?? null,
                        // Para retrocompatibilidad con frontend
                        'product' => [
                            'id' => $item['product_id'],
                            'name' => $item['product_name'],
                            'price' => $item['product_price'],
                            'main_image_url' => $item['image_url'] ?? null
                        ]
                    ];
                    
                    $total += $itemTotal;
                } else {
                    // Formato antiguo - buscar producto en BD
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $store->id)
                        ->where('is_active', true)
                ->with('mainImage')
                ->first();
                
            if ($product) {
                        $itemTotal = $product->price * $item['quantity'];
                        
                        $items[] = [
                    'key' => $key,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'product_price' => $product->price,
                            'quantity' => $item['quantity'],
                            'item_total' => $itemTotal,
                            'formatted_price' => '$' . number_format($product->price, 0, ',', '.'),
                            'formatted_total' => '$' . number_format($itemTotal, 0, ',', '.'),
                            'variants' => $item['variants'] ?? [],
                            'variant_display' => $this->formatVariants($item['variants'] ?? []),
                            'image_url' => $product->main_image_url ?? null,
                            // Para retrocompatibilidad con frontend
                            'product' => [
                                'id' => $product->id,
                                'name' => $product->name,
                                'price' => $product->price,
                                'main_image_url' => $product->main_image_url ?? null
                            ]
                        ];
                        
                        $total += $itemTotal;
                    } else {
                        \Log::warning('🔍 PRODUCTO NO ENCONTRADO EN CARRITO:', [
                            'product_id' => $item['product_id'],
                            'store_id' => $store->id,
                            'cart_key' => $key
                        ]);
                    }
                }
            }

            $cartCount = array_sum(array_column($cart, 'quantity'));
            
            $response = [
            'success' => true,
            'items' => $items,
                'subtotal' => $total,
            'total' => $total,
            'formatted_total' => '$' . number_format($total, 0, ',', '.'),
                'count' => $cartCount,
                'cart_count' => $cartCount,
                'cart_total' => $total,
                'formatted_cart_total' => '$' . number_format($total, 0, ',', '.'),
                'debug_session_id' => session()->getId(),
                'debug_cart_items' => count($cart)
            ];
            
            \Log::info('🛒 GET CART RESPONSE:', [
                'items_count' => count($items),
                'total' => $total,
                'cart_count' => $cartCount
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            \Log::error('🔥 ERROR OBTENIENDO CARRITO:', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error cargando carrito',
                'debug_error' => $e->getMessage()
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
            
            // Verificar producto
            $item = $cart[$validated['item_key']];
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $store->id)
                ->where('is_active', true)
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
            
            // Stock validation omitido por ahora
            
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
            \Log::info('🗑️ REMOVE FROM CART DEBUG:', [
                'store_id' => $store->id,
                'raw_request' => $request->all(),
                'item_key' => $request->input('item_key'),
                'item_key_type' => gettype($request->input('item_key'))
            ]);
            
        $validated = $request->validate([
                'item_key' => 'required'  // Removemos |string para aceptar números también
        ]);
            
            // Asegurar que item_key sea string
            $itemKey = (string) $validated['item_key'];

        $cart = $request->session()->get('cart', []);
        
            \Log::info('🗑️ CART BEFORE REMOVE:', [
                'cart_keys' => array_keys($cart),
                'looking_for' => $itemKey,
                'exists' => isset($cart[$itemKey])
            ]);
            
            if (isset($cart[$itemKey])) {
                unset($cart[$itemKey]);
            $request->session()->put('cart', $cart);
                \Log::info('✅ Item removed from cart');
            } else {
                \Log::warning('⚠️ Item key not found in cart');
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
            $product = \App\Features\TenantAdmin\Models\Product::where('id', $item['product_id'])
                ->where('store_id', $storeId)
                ->where('is_active', true)
                ->first();
                
            if ($product) {
                $total += $product->price * $item['quantity'];
            }
        }

        return $total;
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
     * DEBUG: Test shipping endpoint WITHOUT CSRF
     */
    public function debugShippingCost(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            // Validación simple
            $city = $request->input('city');
            $subtotal = (float) $request->input('subtotal', 0);
            
            \Log::info('🔥 DEBUG SHIPPING ENDPOINT HIT:', [
                'method' => $request->method(),
                'city' => $city,
                'subtotal' => $subtotal,
                'store' => $store->slug ?? 'no-store'
            ]);
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City required'
                ], 422);
            }
            
            // Usar la lógica REAL de cálculo de envío
            $shipping = SimpleShipping::getOrCreateForStore($store->id);
            $shipping->load('activeZones');
            
            $result = $shipping->calculateShippingCost($city, $subtotal);
            
            \Log::info('🚚 DEBUG SHIPPING CALCULATION:', [
                'city' => $city,
                'subtotal' => $subtotal,
                'result' => $result
            ]);
            
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
                'total' => $subtotal + $result['cost'],
                'formatted_total' => '$' . number_format($subtotal + $result['cost'], 0, ',', '.'),
                'zone_id' => $result['zone_id'] ?? null,
                'zone_name' => $result['zone_name'] ?? null,
                'estimated_time' => $result['estimated_time'] ?? null,
                'debug' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('🔥 DEBUG SHIPPING ERROR:', [
                'store_id' => $store->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error calculando costo de envío',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order status for success page (simple API)
     */
    public function getOrderStatusSimple(Request $request): JsonResponse
    {
        $store = $request->route('store');
        $orderId = $request->get('id');
        
        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'Order ID required'
            ], 400);
        }
        
        try {
            $order = Order::where('id', $orderId)
            ->where('store_id', $store->id)
            ->first();

            if (!$order) {
            return response()->json([
                'success' => false,
                    'message' => 'Order not found'
                ], 404);
        }

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'updated_at' => $order->updated_at->toISOString(),
                    'total' => $order->total,
                    'formatted_total' => '$' . number_format($order->total, 0, ',', '.'),
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting order status:', [
                'store_id' => $store->id,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order status'
            ], 500);
        }
    }

    /**
     * DEBUG: Test endpoint for cart debugging
     */
    public function debugCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            \Log::info('🔍 CART DEBUG:', [
                'store_id' => $store->id,
                'store_slug' => $store->slug,
                'session_id' => session()->getId(),
                'session_cart' => session('cart'),
                'session_all' => session()->all(),
                'request_method' => $request->method(),
                'request_headers' => $request->headers->all(),
                'csrf_token' => $request->header('X-CSRF-TOKEN'),
                'csrf_session' => session()->token()
            ]);
            
            $cart = session('cart', []);

        return response()->json([
            'success' => true,
                'debug_info' => [
                    'store_id' => $store->id,
                    'store_slug' => $store->slug,
                    'session_id' => session()->getId(),
                    'cart_raw' => $cart,
                    'cart_count' => count($cart),
                    'session_token' => session()->token(),
                    'request_token' => $request->header('X-CSRF-TOKEN'),
                    'tokens_match' => session()->token() === $request->header('X-CSRF-TOKEN')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * DEBUG: Simple add to cart test
     */
    public function debugAddToCart(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            \Log::info('🛒 DEBUG ADD TO CART:', [
                'store_id' => $store->id,
                'request_data' => $request->all(),
                'session_before' => session('cart', [])
            ]);
            
            // Obtener carrito actual
            $cart = session('cart', []);
            
            // Agregar item de prueba
            $testItem = [
                'product_id' => 1,
                'quantity' => 1,
                'variants' => [],
                'added_at' => now()->toISOString()
            ];
            
            $cart['test_item_' . time()] = $testItem;
            
            // Guardar en sesión
            session()->put('cart', $cart);
            session()->save(); // Forzar guardar
            
            \Log::info('🛒 DEBUG CART SAVED:', [
                'cart_after' => session('cart'),
                'session_id' => session()->getId()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Test item added',
                'cart_before' => $cart,
                'cart_after' => session('cart'),
                'session_id' => session()->getId()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('🔥 DEBUG ADD ERROR:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
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
