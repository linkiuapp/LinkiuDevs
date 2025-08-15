<?php

namespace App\Features\TenantAdmin\Controllers;

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with advanced filters
     */
    public function index(Request $request): View
    {
        $store = $request->route('store');
        
        // Query base con relaciones
        $query = Order::byStore($store->id)
            ->with(['items.product']) // TODO: , 'statusHistory' cuando se implemente
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        if ($request->filled('delivery_type')) {
            $query->byDeliveryType($request->delivery_type);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('amount_from')) {
            $query->where('total', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('total', '<=', $request->amount_to);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Estadísticas para el dashboard
        $stats = [
            'total' => Order::byStore($store->id)->count(),
            'pending' => Order::byStore($store->id)->byStatus('pending')->count(),
            'confirmed' => Order::byStore($store->id)->byStatus('confirmed')->count(),
            'preparing' => Order::byStore($store->id)->byStatus('preparing')->count(),
            'shipped' => Order::byStore($store->id)->byStatus('shipped')->count(),
            'delivered' => Order::byStore($store->id)->byStatus('delivered')->count(),
            'cancelled' => Order::byStore($store->id)->byStatus('cancelled')->count(),
            'total_revenue' => Order::byStore($store->id)->whereIn('status', ['delivered'])->sum('total'),
            'avg_order_value' => Order::byStore($store->id)->whereIn('status', ['delivered'])->avg('total'),
        ];

        return view('tenant-admin::orders.index', compact('orders', 'stats', 'store'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create(Request $request): View
    {
        $store = $request->route('store');
        
        // Obtener productos activos de la tienda
        $products = Product::byStore($store->id)
            ->active()
            ->with(['variants', 'mainImage'])
            ->orderBy('name')
            ->get();

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

        return view('tenant-admin::orders.create', compact('products', 'shippingMethods', 'departments', 'store'));
    }

    /**
     * Store a newly created order
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
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variants' => 'nullable|array',
            'shipping_zone_id' => 'nullable|exists:shipping_zones,id',
            'payment_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120' // 5MB
        ]);

        try {
            DB::beginTransaction();

            // Calcular costos de envío
            $shippingCost = 0;
            if ($validated['delivery_type'] === 'domicilio' && isset($validated['shipping_zone_id'])) {
                $zone = ShippingZone::find($validated['shipping_zone_id']);
                if ($zone && $zone->store_id === $store->id) {
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

            // Agregar items al pedido
            foreach ($validated['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                
                // Verificar que el producto pertenece a la tienda
                if ($product->store_id !== $store->id) {
                    throw new \Exception('Producto no válido para esta tienda');
                }

                $orderItemData = OrderItem::createFromProduct(
                    $product, 
                    $itemData['quantity'], 
                    $itemData['variants'] ?? null
                );

                $order->items()->create($orderItemData);
            }

            // Recalcular totales del pedido
            $order->recalculateTotals();

            // Procesar comprobante de pago si se subió
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $this->handlePaymentProofUpload($request->file('payment_proof'), $order->order_number);
                $order->update(['payment_proof_path' => $paymentProofPath]);
            }

            DB::commit();

            return redirect()
                ->route('tenant.admin.orders.show', ['store' => $store->slug, 'order' => $order->id])
                ->with('success', 'Pedido creado exitosamente. Número: ' . $order->order_number);

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withErrors(['error' => 'Error al crear el pedido: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, $storeSlug, Order $order): View
    {
        $store = $request->route('store');
        
        // Verificar que el pedido pertenece a la tienda
        if ($order->store_id !== $store->id) {
            abort(404);
        }

        // Cargar relaciones
        $order->load([
            'items.product.mainImage'
            // TODO: 'statusHistory' cuando se implemente
        ]);

        // Obtener métodos de envío para cambios
        $shippingMethods = ShippingMethod::where('store_id', $store->id)
            ->active()
            ->with('activeZones')
            ->get();

        return view('tenant-admin::orders.show', compact('order', 'store', 'shippingMethods'));
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Request $request, $storeSlug, Order $order): View
    {
        $store = $request->route('store');
        
        // Verificar que el pedido pertenece a la tienda
        if ($order->store_id !== $store->id) {
            abort(404);
        }

        // Solo permitir edición en estados tempranos
        if (!$order->canBeEdited()) {
            return redirect()
                ->route('tenant.admin.orders.show', ['store' => $store->slug, 'order' => $order->id])
                ->with('error', 'No se puede editar un pedido en estado: ' . $order->status_label);
        }

        // Cargar datos necesarios
        $order->load('items.product.mainImage');
        
        $products = Product::byStore($store->id)
            ->active()
            ->with(['variants', 'mainImage'])
            ->orderBy('name')
            ->get();

        $shippingMethods = ShippingMethod::where('store_id', $store->id)
            ->active()
            ->with('activeZones')
            ->ordered()
            ->get();

        $departments = [
            'Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 
            'Caldas', 'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 
            'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare', 'Huila', 'La Guajira', 
            'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 
            'Risaralda', 'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 
            'Valle del Cauca', 'Vaupés', 'Vichada'
        ];

        return view('tenant-admin::orders.edit', compact('order', 'products', 'shippingMethods', 'departments', 'store'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, $storeSlug, Order $order): RedirectResponse
    {
        $store = $request->route('store');
        
        // Verificar que el pedido pertenece a la tienda
        if ($order->store_id !== $store->id) {
            abort(404);
        }

        // Solo permitir edición en estados tempranos
        if (!$order->canBeEdited()) {
            return redirect()
                ->route('tenant.admin.orders.show', ['store' => $store->slug, 'order' => $order->id])
                ->with('error', 'No se puede editar un pedido en estado: ' . $order->status_label);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'department' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'delivery_type' => 'required|in:domicilio,pickup',
            'payment_method' => 'required|in:transferencia,contra_entrega,efectivo',
            'notes' => 'nullable|string|max:1000',
            'payment_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'remove_payment_proof' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar datos básicos del pedido
            $order->update([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'department' => $validated['department'],
                'city' => $validated['city'],
                'delivery_type' => $validated['delivery_type'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes']
            ]);

            // Gestionar comprobante de pago
            if ($request->boolean('remove_payment_proof') && $order->payment_proof_path) {
                $this->deletePaymentProof($order->payment_proof_path);
                $order->update(['payment_proof_path' => null]);
            }

            if ($request->hasFile('payment_proof')) {
                // Eliminar comprobante anterior si existe
                if ($order->payment_proof_path) {
                    $this->deletePaymentProof($order->payment_proof_path);
                }
                
                $paymentProofPath = $this->handlePaymentProofUpload($request->file('payment_proof'), $order->order_number);
                $order->update(['payment_proof_path' => $paymentProofPath]);
            }

            DB::commit();

            return redirect()
                ->route('tenant.admin.orders.show', ['store' => $store->slug, 'order' => $order->id])
                ->with('success', 'Pedido actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withErrors(['error' => 'Error al actualizar el pedido: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(Request $request, $storeSlug, Order $order)
    {
        $store = $request->route('store');
        
        // Verificar que el pedido pertenece a la tienda
        if ($order->store_id !== $store->id) {
            abort(404);
        }

        // Solo permitir eliminación en estado pendiente
        if ($order->status !== Order::STATUS_PENDING) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar pedidos en estado pendiente'
                ], 422);
            }
            
            return redirect()
                ->route('tenant.admin.orders.index', $store->slug)
                ->with('error', 'Solo se pueden eliminar pedidos en estado pendiente');
        }

        try {
            // Eliminar comprobante de pago si existe
            if ($order->payment_proof_path) {
                $this->deletePaymentProof($order->payment_proof_path);
            }

            $orderNumber = $order->order_number;
            $order->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pedido {$orderNumber} eliminado exitosamente"
                ]);
            }

            return redirect()
                ->route('tenant.admin.orders.index', $store->slug)
                ->with('success', "Pedido {$orderNumber} eliminado exitosamente");

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el pedido: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->route('tenant.admin.orders.index', $store->slug)
                ->with('error', 'Error al eliminar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $storeSlug, Order $order): JsonResponse
    {
        $store = $request->route('store');
        
        // Verificar que el pedido pertenece a la tienda
        if ($order->store_id !== $store->id) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $oldStatus = $order->status;
            $order->update(['status' => $validated['status']]);

            // TODO: El historial se registra automáticamente en el boot del modelo
            // if ($validated['notes']) {
            //     $order->statusHistory()->latest()->first()->update(['notes' => $validated['notes']]);
            // }

            return response()->json([
                'success' => true,
                'message' => "Estado cambiado de {$order::STATUSES[$oldStatus]} a {$order->status_label}",
                'new_status' => $validated['status'],
                'new_status_label' => $order->status_label,
                'status_color' => $order->status_color
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get shipping cost for a zone
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
            'free_shipping_message' => $zone->getFreeShippingMessage()
        ]);
    }

    /**
     * Duplicate an order
     */
    public function duplicate(Request $request, $storeSlug, Order $order): RedirectResponse
    {
        $store = $request->route('store');
        
        // Verificar que el pedido pertenece a la tienda
        if ($order->store_id !== $store->id) {
            abort(404);
        }

        try {
            DB::beginTransaction();

            // Duplicar pedido
            $newOrder = $order->replicate();
            $newOrder->order_number = null; // Se generará automáticamente
            $newOrder->status = Order::STATUS_PENDING;
            $newOrder->payment_proof_path = null; // No duplicar comprobante
            $newOrder->save();

            // Duplicar items
            foreach ($order->items as $item) {
                $newItem = $item->replicate();
                $newItem->order_id = $newOrder->id;
                $newItem->save();
            }

            // Recalcular totales
            $newOrder->recalculateTotals();

            DB::commit();

            return redirect()
                ->route('tenant.admin.orders.edit', ['store' => $store->slug, 'order' => $newOrder->id])
                ->with('success', "Pedido duplicado exitosamente. Nuevo número: {$newOrder->order_number}");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al duplicar pedido: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment proof upload
     */
    private function handlePaymentProofUpload($file, string $orderNumber): string
    {
        $filename = $orderNumber . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('orders/payment-proofs', $filename, 'public');
        return 'orders/payment-proofs/' . $filename; // Devolver path completo como en frontend
    }

    /**
     * Delete payment proof file
     */
    private function deletePaymentProof(string $filePath): void
    {
        Storage::disk('public')->delete($filePath); // Asumir que $filePath ya contiene el path completo
    }

    /**
     * Download payment proof
     */
    public function downloadPaymentProof($storeSlug, Order $order)
    {
        $store = request()->route('store');
        
        // Verificar que el pedido pertenece a la tienda
        if ($order->store_id !== $store->id || !$order->payment_proof_path) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $order->payment_proof_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, 'Comprobante_' . $order->order_number . '.' . pathinfo($order->payment_proof_path, PATHINFO_EXTENSION));
    }
} 