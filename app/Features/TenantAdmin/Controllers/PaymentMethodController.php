<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Features\TenantAdmin\Models\PaymentMethodConfig;
use App\Features\TenantAdmin\Services\PaymentMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;

class PaymentMethodController extends Controller
{
    protected $paymentMethodService;
    
    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
    }
    
    /**
     * Toggle the active status of a payment method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $store
     * @param  \App\Features\TenantAdmin\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            return response()->json([
                'success' => false,
                'message' => 'Método de pago no encontrado'
            ], 404);
        }
        
        // Check authorization using policy
        if (Gate::denies('toggleActive', $paymentMethod)) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede desactivar el único método de pago activo.'
            ], 403);
        }
        
        try {
            // Toggle the active status (this includes validation to ensure at least one method remains active)
            $this->paymentMethodService->toggleActive($paymentMethod);
            
            // Clear cache
            $this->paymentMethodService->clearPaymentMethodsCache($store->id);
            
            return response()->json([
                'success' => true,
                'message' => $paymentMethod->is_active ? 'Método de pago activado' : 'Método de pago desactivado',
                'is_active' => $paymentMethod->is_active
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del método de pago: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the order of payment methods.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Check authorization using policy
        if (Gate::denies('updateOrder', PaymentMethod::class)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para actualizar el orden de los métodos de pago.'
            ], 403);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'methods' => 'required|array',
            'methods.*' => 'required|integer|exists:payment_methods,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Update the order of payment methods
            $success = $this->paymentMethodService->updateMethodOrder($request->methods, $store->id);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Orden actualizado exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el orden'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el orden: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Set a payment method as default for the store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $store
     * @param  \App\Features\TenantAdmin\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefault(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            return response()->json([
                'success' => false,
                'message' => 'Método de pago no encontrado'
            ], 404);
        }
        
        // Ensure payment method is active
        if (!$paymentMethod->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede establecer como predeterminado un método inactivo'
            ], 422);
        }
        
        try {
            // Set as default method
            $this->paymentMethodService->setDefaultMethod($store, $paymentMethod->id);
            
            // Clear cache
            $this->paymentMethodService->clearPaymentMethodsCache($store->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Método de pago establecido como predeterminado',
                'default_method_id' => $paymentMethod->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer método predeterminado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle simple payment method (for predefined methods only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleSimple(Request $request)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:cash,bank_transfer,card_terminal,cash_on_delivery',
            'is_active' => 'required|boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Find or create the payment method
            $method = PaymentMethod::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'type' => $request->type
                ],
                [
                    'name' => $this->getDefaultMethodName($request->type),
                    'is_active' => false,
                    'available_for_pickup' => true,
                    'available_for_delivery' => $request->type !== 'cash_on_delivery',
                    'sort_order' => $this->getNextSortOrder($store->id)
                ]
            );
            
            // Update active status
            $method->update(['is_active' => $request->is_active]);
            
            // Clear cache
            $this->paymentMethodService->clearPaymentMethodsCache($store->id);
            
            $message = $request->is_active ? 
                "Método {$method->name} activado exitosamente" : 
                "Método {$method->name} desactivado exitosamente";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'method_id' => $method->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del método: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Set default payment method (simple version).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefaultSimple(Request $request)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:cash,bank_transfer,card_terminal,cash_on_delivery'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de método inválido'
            ], 422);
        }
        
        try {
            // Find the method
            $method = PaymentMethod::where('store_id', $store->id)
                ->where('type', $request->type)
                ->where('is_active', true)
                ->first();
            
            if (!$method) {
                return response()->json([
                    'success' => false,
                    'message' => 'El método debe estar activo para ser predeterminado'
                ], 422);
            }
            
            // Set as default (this will automatically unset others)
            $this->paymentMethodService->setDefaultMethod($store, $method->id);
            
            // Clear cache
            $this->paymentMethodService->clearPaymentMethodsCache($store->id);
            
            return response()->json([
                'success' => true,
                'message' => "Método {$method->name} establecido como predeterminado"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer método predeterminado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Configure simple payment method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configureSimple(Request $request)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:cash,bank_transfer,card_terminal,cash_on_delivery',
            'config' => 'required|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Find the method
            $method = PaymentMethod::where('store_id', $store->id)
                ->where('type', $request->type)
                ->first();
            
            if (!$method) {
                return response()->json([
                    'success' => false,
                    'message' => 'Método de pago no encontrado'
                ], 404);
            }
            
            $config = $request->config;
            
            // Update basic config
            $method->update([
                'available_for_pickup' => $config['available_for_pickup'] ?? true,
                'available_for_delivery' => $config['available_for_delivery'] ?? true
            ]);
            
            // Handle method-specific config
            $methodConfig = PaymentMethodConfig::getForStore($store->id);
            
            if ($request->type === 'cash' && isset($config['allow_change'])) {
                $methodConfig->update(['cash_change_available' => $config['allow_change']]);
            }
            
            if ($request->type === 'card_terminal') {
                $acceptedCards = [];
                if ($config['accept_visa'] ?? false) $acceptedCards[] = 'visa';
                if ($config['accept_mastercard'] ?? false) $acceptedCards[] = 'mastercard';
                if ($config['accept_american_express'] ?? false) $acceptedCards[] = 'amex';
                
                // Store in instructions field for now
                $method->update(['instructions' => 'Tarjetas aceptadas: ' . implode(', ', $acceptedCards)]);
            }
            
            // Clear cache
            $this->paymentMethodService->clearPaymentMethodsCache($store->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get default method name by type.
     *
     * @param string $type
     * @return string
     */
    private function getDefaultMethodName(string $type): string
    {
        return match($type) {
            'cash' => 'Efectivo',
            'bank_transfer' => 'Transferencia Bancaria',
            'card_terminal' => 'Datáfono',
            'cash_on_delivery' => 'Contra Entrega',
            default => ucfirst($type)
        };
    }
    
    /**
     * Get next sort order for payment methods.
     *
     * @param int $storeId
     * @return int
     */
    private function getNextSortOrder(int $storeId): int
    {
        $maxOrder = PaymentMethod::where('store_id', $storeId)->max('sort_order') ?? 0;
        return $maxOrder + 1;
    }
    
    /**
     * Display a listing of the payment methods.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get current store from view shared data (set by middleware)
        $store = view()->shared('currentStore');
        
        // Get payment methods
        $paymentMethods = $this->paymentMethodService->getAvailableMethods($store);
        
        // Get default payment method
        $defaultMethod = $this->paymentMethodService->getDefaultMethod($store);
        
        return view('tenant-admin::payment-methods.index', compact(
            'paymentMethods',
            'store',
            'defaultMethod'
        ));
    }
    
    /**
     * Show the form for creating a new payment method.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        return view('tenant-admin::payment-methods.create', compact('store'));
    }
    
    /**
     * Store a newly created payment method in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'type' => [
                'required',
                'string',
                Rule::in(['cash', 'bank_transfer', 'card_terminal', 'digital_wallet', 'cash_on_delivery']),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'instructions' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'available_for_pickup' => 'nullable|boolean',
            'available_for_delivery' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'cash_change_available' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Prepare data for payment method
            $data = [
                'type' => $request->type,
                'name' => $request->name,
                'instructions' => $request->instructions,
                'is_active' => $request->has('is_active'),
                'available_for_pickup' => $request->has('available_for_pickup'),
                'available_for_delivery' => $request->has('available_for_delivery'),
                'store_id' => $store->id,
            ];
            
            
            // Create payment method
            $paymentMethod = $this->paymentMethodService->createPaymentMethod($data);
            
            // Handle cash specific options
            if ($request->type === 'cash' && $request->has('cash_change_available')) {
                // Get or create payment method config
                $config = PaymentMethodConfig::getForStore($store->id);
                $config->update(['cash_change_available' => true]);
            }
            
            // Set as default if requested
            if ($request->has('is_default')) {
                $this->paymentMethodService->setDefaultMethod($store, $paymentMethod->id);
            }
            
            return redirect()->route('tenant.admin.payment-methods.index', ['store' => $store->slug])
                ->with('success', 'Método de pago creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear el método de pago: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display the specified payment method.
     * 
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Load bank accounts if this is a bank transfer method
        if ($paymentMethod->isBankTransfer()) {
            $paymentMethod->load('bankAccounts');
        }
        
        // Check if this is the default payment method
        $isDefault = $this->paymentMethodService->getDefaultMethod($store)?->id === $paymentMethod->id;
        
        return view('tenant-admin::payment-methods.show', compact(
            'paymentMethod',
            'store',
            'isDefault'
        ));
    }
    
    /**
     * Show the form for editing the specified payment method.
     *
     * @param Request $request
     * @param string $store
     * @param PaymentMethod $paymentMethod
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Check if this is the default payment method
        $isDefault = $this->paymentMethodService->getDefaultMethod($store)?->id === $paymentMethod->id;
        
        // Get payment method config for cash options
        $config = PaymentMethodConfig::getForStore($store->id);
        $cashChangeAvailable = $config->cash_change_available;
        
        return view('tenant-admin::payment-methods.edit', compact(
            'paymentMethod',
            'store',
            'isDefault',
            'cashChangeAvailable'
        ));
    }
    
    /**
     * Update the specified payment method in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $store
     * @param  \App\Features\TenantAdmin\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $store, PaymentMethod $paymentMethod)
    {
        // Get current store from view shared data
        $store = view()->shared('currentStore');
        
        // Ensure payment method belongs to current store
        if ($paymentMethod->store_id !== $store->id) {
            abort(404);
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'instructions' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'available_for_pickup' => 'nullable|boolean',
            'available_for_delivery' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'cash_change_available' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Prepare data for payment method update
            $data = [
                'name' => $request->name,
                'instructions' => $request->instructions,
                'is_active' => $request->has('is_active'),
                'available_for_pickup' => $request->has('available_for_pickup'),
                'available_for_delivery' => $request->has('available_for_delivery'),
            ];
            
            
            // Update payment method
            $this->paymentMethodService->updatePaymentMethod($paymentMethod, $data);
            
            // Handle cash specific options
            if ($paymentMethod->isCash()) {
                // Get or create payment method config
                $config = PaymentMethodConfig::getForStore($store->id);
                $config->update(['cash_change_available' => $request->has('cash_change_available')]);
            }
            
            // Set as default if requested
            if ($request->has('is_default')) {
                $this->paymentMethodService->setDefaultMethod($store, $paymentMethod->id);
            }
            
            return redirect()->route('tenant.admin.payment-methods.show', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id])
                ->with('success', 'Método de pago actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el método de pago: ' . $e->getMessage())->withInput();
        }
    }
}