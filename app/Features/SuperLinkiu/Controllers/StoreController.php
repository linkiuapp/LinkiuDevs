<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Plan;
use App\Shared\Models\Store;
use App\Shared\Models\StorePlanExtension;
use App\Shared\Models\User;
use App\Core\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Features\SuperLinkiu\Exports\StoresExport;
use App\Features\SuperLinkiu\Services\StoreTemplateService;
use App\Features\SuperLinkiu\Services\LocationService;
use App\Features\SuperLinkiu\Services\BulkImportBatchManager;
use App\Features\SuperLinkiu\Services\ValidationCacheService;
use App\Features\SuperLinkiu\Services\PerformanceMonitoringService;
use App\Features\SuperLinkiu\Models\BulkImportLog;
use Illuminate\Support\Facades\Log;
use App\Features\SuperLinkiu\Requests\CreateStoreRequest;
use App\Features\SuperLinkiu\Requests\UpdateStoreRequest;
use App\Features\SuperLinkiu\Services\StoreService;
use App\Features\SuperLinkiu\Services\StoreValidationService;

class StoreController extends Controller
{
    protected StoreTemplateService $templateService;
    protected LocationService $locationService;
    protected BulkImportBatchManager $batchManager;
    protected ValidationCacheService $cacheService;
    protected PerformanceMonitoringService $performanceService;
    protected StoreService $storeService;
    protected StoreValidationService $validationService;

    public function __construct(
        StoreTemplateService $templateService, 
        LocationService $locationService,
        BulkImportBatchManager $batchManager,
        ValidationCacheService $cacheService,
        PerformanceMonitoringService $performanceService,
        StoreService $storeService,
        StoreValidationService $validationService
    ) {
        $this->templateService = $templateService;
        $this->locationService = $locationService;
        $this->batchManager = $batchManager;
        $this->cacheService = $cacheService;
        $this->performanceService = $performanceService;
        $this->storeService = $storeService;
        $this->validationService = $validationService;
    }
    public function index(Request $request)
    {
        // 🚀 OPTIMIZACIÓN: Eager loading para evitar consultas N+1
        $query = Store::with([
            'plan:id,name,price,allow_custom_slug',
            'admins:id,name,email,store_id',
            'design:id,store_id,logo_url,is_published'
        ]);

        // Búsqueda global
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filtro por plan
        if ($planId = $request->get('plan_id')) {
            $query->where('plan_id', $planId);
        }

        // Filtro por estado
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filtro por verificación
        if ($request->has('verified')) {
            $query->where('verified', $request->boolean('verified'));
        }

        // Filtro por rango de fechas
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Exportar si se solicita
        if ($request->get('export') === 'excel') {
            return $this->exportToExcel($query->get());
        }
        if ($request->get('export') === 'csv') {
            return $this->exportToCsv($query->get());
        }

        // Paginación
        $perPage = $request->get('per_page', 12);
        $stores = $query->paginate($perPage)->withQueryString();

        // Obtener todos los planes para el filtro
        $plans = Plan::select('id', 'name')->get();

        // Vista (tabla o cards)
        $viewType = $request->get('view', 'table');

        // Calcular estadísticas para las cards
        $totalStores = Store::count();
        $activeStores = Store::where('status', 'active')->count();
        $newThisMonth = Store::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->count();
        $verifiedStores = Store::where('verified', true)->count();

        return view('superlinkiu::stores.index', compact(
            'stores',
            'plans',
            'viewType',
            'totalStores',
            'activeStores',
            'newThisMonth',
            'verifiedStores'
        ));
    }

    public function create()
    {
        $plans = Plan::active()->get();
        return view('superlinkiu::stores.create', compact('plans'));
    }

    public function createWizard()
    {
        $plans = Plan::active()->get();
        $templates = $this->templateService->getAllTemplates();
        return view('superlinkiu::stores.create-wizard', compact('plans', 'templates'));
    }

    public function store(CreateStoreRequest $request)
    {
        try {
            $result = $this->storeService->createStore($request->validated(), $request);
            
            return redirect()
                ->route('superlinkiu.stores.index')
                ->with('success', 'Tienda creada exitosamente.')
                ->with('admin_credentials', $result['admin_credentials']);
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => 'Error interno: ' . $e->getMessage()])
                ->withInput();
        }
    }



    public function show(Store $store)
    {
        $store->load([
            'plan', 
            'admins', // 👤 CARGAR ADMINISTRADORES
            'planExtensions' => function($query) {
                $query->with('superAdmin')->latest();
            }
        ]);
        
        return view('superlinkiu::stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        $store->load(['plan', 'admins']); // 👤 CARGAR ADMINISTRADORES
        $plans = Plan::active()->get();
        return view('superlinkiu::stores.edit', compact('store', 'plans'));
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        try {
            $result = $this->storeService->updateStore($store, $request->validated());
            
            return redirect()
                ->route('superlinkiu.stores.index')
                ->with('success', 'Tienda actualizada exitosamente.');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => 'Error interno: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Store $store)
    {
        try {
            $this->storeService->deleteStore($store);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tienda eliminada exitosamente.'
                ]);
            }

            return redirect()
                ->route('superlinkiu.stores.index')
                ->with('success', 'Tienda eliminada exitosamente.');
                
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar tienda: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['general' => 'Error al eliminar tienda: ' . $e->getMessage()]);
        }
    }

    public function toggleVerified(Store $store)
    {
        try {
            $result = $this->storeService->toggleVerified($store);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar verificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maneja la lógica de actualización del slug según el cambio de plan
     */
    private function handleSlugUpdate($store, $oldPlan, $newPlan, $request)
    {
        $requestedSlug = $request->input('slug');
        $currentSlug = $store->slug;
        
        // Obtener el slug original del usuario (preservado en meta o usando el actual si el plan original permitía personalización)
        $originalUserSlug = $this->getOriginalUserSlug($store, $oldPlan);
        
        \Log::info('🔧 STORE UPDATE: Slug update analysis', [
            'store_id' => $store->id,
            'current_slug' => $currentSlug,
            'requested_slug' => $requestedSlug,
            'original_user_slug' => $originalUserSlug,
            'old_plan_allows_custom' => $oldPlan->allow_custom_slug,
            'new_plan_allows_custom' => $newPlan->allow_custom_slug
        ]);
        
        // Si no hay cambio de plan
        if ($oldPlan->id === $newPlan->id) {
            // Si el plan permite personalización y el usuario quiere cambiar el slug
            if ($newPlan->allow_custom_slug && $requestedSlug !== $currentSlug) {
                return $this->validateCustomSlug($requestedSlug, $store->id);
            }
            // Sin cambios
            return ['slug' => $currentSlug, 'error' => false, 'validate' => false];
        }
        
        // Downgrade: de plan con personalización a plan sin personalización
        if ($oldPlan->allow_custom_slug && !$newPlan->allow_custom_slug) {
            // Guardar el slug actual como el slug original del usuario para futuras restauraciones
            $this->saveOriginalUserSlug($store, $currentSlug);
            
            $newSlug = $this->generatePlanBasedSlug($newPlan, $store);
            \Log::info('🔧 STORE UPDATE: Downgrade detected - generating random slug', [
                'preserved_user_slug' => $currentSlug,
                'new_random_slug' => $newSlug
            ]);
            return ['slug' => $newSlug, 'error' => false, 'validate' => false];
        }
        
        // Upgrade: de plan sin personalización a plan con personalización
        if (!$oldPlan->allow_custom_slug && $newPlan->allow_custom_slug) {
            // Restaurar el slug original del usuario si existe, sino usar el solicitado o actual
            $slug = $requestedSlug ?: ($originalUserSlug ?: $currentSlug);
            
            \Log::info('🔧 STORE UPDATE: Upgrade detected - restoring user slug', [
                'requested_slug' => $requestedSlug,
                'original_user_slug' => $originalUserSlug,
                'current_slug' => $currentSlug,
                'final_slug' => $slug
            ]);
            
            if ($slug !== $currentSlug) {
                return $this->validateCustomSlug($slug, $store->id);
            }
            return ['slug' => $slug, 'error' => false, 'validate' => false];
        }
        
        // Plan permite personalización y usuario quiere cambiar
        if ($newPlan->allow_custom_slug && $requestedSlug !== $currentSlug) {
            return $this->validateCustomSlug($requestedSlug, $store->id);
        }
        
        // Sin cambios
        return ['slug' => $currentSlug, 'error' => false, 'validate' => false];
    }
    
    /**
     * Obtiene el slug original del usuario (antes de downgrades)
     * Por ahora usamos una lógica simple: si el slug actual no parece aleatorio, es del usuario
     */
    private function getOriginalUserSlug($store, $currentPlan)
    {
        // Si el plan actual permite personalización, el slug actual es del usuario
        if ($currentPlan->allow_custom_slug) {
            return $store->slug;
        }
        
        // Si el slug actual no parece ser aleatorio (no tiene patrón tienda-xxxxxx), 
        // probablemente es un slug personalizado del usuario
        if (!preg_match('/^tienda-[a-z0-9]{6}$/', $store->slug)) {
            return $store->slug;
        }
        
        return null;
    }
    
    /**
     * Guarda el slug original del usuario para futuras restauraciones
     * Por ahora solo loggeamos, en el futuro se puede implementar persistencia
     */
    private function saveOriginalUserSlug($store, $userSlug)
    {
        \Log::info('💾 Would save original user slug (not implemented yet)', [
            'store_id' => $store->id,
            'original_user_slug' => $userSlug
        ]);
        
        // TODO: Implementar persistencia del slug original
        // Opciones: 
        // 1. Agregar campo original_slug a stores table
        // 2. Usar tabla separada store_slug_history
        // 3. Usar campo JSON meta_data
    }
    
    /**
     * Valida un slug personalizado
     */
    private function validateCustomSlug($slug, $storeId)
    {
        // Sanitizar
        $slug = $this->validationService->sanitizeSlug($slug);
        
        // Validar formato
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return [
                'error' => true, 
                'message' => 'El slug debe contener solo letras minúsculas, números y guiones.',
                'validate' => false
            ];
        }
        
        // Verificar si está reservado
        if (RouteServiceProvider::isReservedSlug($slug)) {
            return [
                'error' => true, 
                'message' => 'Este slug está reservado por el sistema.',
                'validate' => false
            ];
        }
        
        // Verificar unicidad
        if (Store::where('slug', $slug)->where('id', '!=', $storeId)->exists()) {
            return [
                'error' => true, 
                'message' => 'Este slug ya está en uso.',
                'validate' => false
            ];
        }
        
        return ['slug' => $slug, 'error' => false, 'validate' => true];
    }
    
    /**
     * Genera un slug aleatorio como en el wizard
     */
    private function generatePlanBasedSlug($plan, $store)
    {
        // Generar slug aleatorio como en el wizard
        do {
            $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
            $slug = 'tienda-' . $randomString;
        } while (Store::where('slug', $slug)->where('id', '!=', $store->id)->exists());
        
        \Log::info('🎲 Generated random slug for plan downgrade', [
            'store_id' => $store->id,
            'generated_slug' => $slug,
            'plan_name' => $plan->name
        ]);
        
        return $slug;
    }

    public function updateStatus(Request $request, Store $store)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);

        try {
            $result = $this->storeService->updateStatus($store, $validated['status']);
            
            return response()->json([
                'success' => true,
                'status' => $result['status'],
                'message' => $result['message']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function extendPlan(Request $request, Store $store)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ]);

        $extension = StorePlanExtension::create([
            'store_id' => $store->id,
            'plan_id' => $store->plan_id,
            'super_admin_id' => auth()->id(),
            'start_date' => now(),
            'end_date' => now()->addDays($validated['days']),
            'reason' => $validated['reason']
        ]);

        return redirect()
            ->route('superlinkiu.stores.show', $store)
            ->with('success', 'Plan extendido exitosamente por ' . $validated['days'] . ' días.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'store_ids' => 'required|array',
            'store_ids.*' => 'exists:stores,id',
            'action' => 'required|in:activate,deactivate,suspend,delete,verify,unverify'
        ]);

        $stores = Store::whereIn('id', $validated['store_ids'])->get();

        switch ($validated['action']) {
            case 'activate':
                $stores->each->update(['status' => 'active']);
                $message = count($stores) . ' tiendas activadas exitosamente.';
                break;
            
            case 'deactivate':
                $stores->each->update(['status' => 'inactive']);
                $message = count($stores) . ' tiendas desactivadas exitosamente.';
                break;
            
            case 'suspend':
                $stores->each->update(['status' => 'suspended']);
                $message = count($stores) . ' tiendas suspendidas exitosamente.';
                break;
            
            case 'verify':
                $stores->each->update(['verified' => true]);
                $message = count($stores) . ' tiendas verificadas exitosamente.';
                break;
            
            case 'unverify':
                $stores->each->update(['verified' => false]);
                $message = count($stores) . ' tiendas marcadas como no verificadas.';
                break;
            
            case 'delete':
                $stores->each->delete();
                $message = count($stores) . ' tiendas eliminadas exitosamente.';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    private function exportToExcel($stores)
    {
        return Excel::download(new StoresExport($stores), 'tiendas_' . date('Y-m-d') . '.xlsx');
    }

    private function exportToCsv($stores)
    {
        return Excel::download(new StoresExport($stores), 'tiendas_' . date('Y-m-d') . '.csv');
    }

    /**
     * Get all available templates
     */
    public function getTemplates()
    {
        try {
            $templates = $this->templateService->getAllTemplates();
            
            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching templates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las plantillas'
            ], 500);
        }
    }

    /**
     * Get template configuration by ID
     */
    public function getTemplateConfig(string $templateId)
    {
        try {
            $config = $this->templateService->getTemplateConfig($templateId);
            
            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching template config', [
                'template_id' => $templateId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración de la plantilla'
            ], 500);
        }
    }

    /**
     * Get template validation rules
     */
    public function getTemplateValidationRules(string $templateId)
    {
        try {
            $rules = $this->templateService->getTemplateValidationRules($templateId);
            
            return response()->json([
                'success' => true,
                'data' => $rules
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching template validation rules', [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reglas de validación'
            ], 500);
        }
    }

    /**
     * Get template field mapping
     */
    public function getTemplateFieldMapping(string $templateId)
    {
        try {
            $mapping = $this->templateService->getTemplateFieldMapping($templateId);
            
            return response()->json([
                'success' => true,
                'data' => $mapping
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching template field mapping', [
                'template_id' => $templateId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el mapeo de campos'
            ], 500);
        }
    }

    /**
     * Get templates by capability
     */
    public function getTemplatesByCapability(string $capability)
    {
        try {
            $templates = $this->templateService->getTemplatesByCapability($capability);
            
            return response()->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching templates by capability', [
                'capability' => $capability,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener plantillas por capacidad'
            ], 500);
        }
    }

    /**
     * Validate email uniqueness
     */
    public function validateEmail(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->input('email');
            $storeId = $request->input('store_id'); // For updates

            // Check cache first
            $cached = $this->cacheService->getCachedEmailValidation($email, $storeId);
            if ($cached) {
                $responseTime = (microtime(true) - $startTime) * 1000;
                $this->performanceService->recordValidationPerformance('validateEmail', $responseTime, true);
                
                return response()->json([
                    'success' => true,
                    'data' => $cached
                ]);
            }

            // Use validation service
            $result = $this->validationService->validateEmailAvailability($email, $storeId);
            
            // Adapt result format for backwards compatibility
            $adaptedResult = [
                'is_valid' => $result['available'],
                'message' => $result['message'],
                'field' => 'email'
            ];

            // Cache the result
            $this->cacheService->cacheEmailValidation($email, $storeId, $adaptedResult);

            // Record performance metrics
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->performanceService->recordValidationPerformance('validateEmail', $responseTime, false);

            return response()->json([
                'success' => true,
                'data' => $adaptedResult
            ]);

        } catch (\Exception $e) {
            // Record error
            $this->performanceService->recordError('validation_error', 'validateEmail', [
                'email' => $request->input('email'),
                'error' => $e->getMessage()
            ]);

            Log::error('Error validating email', [
                'email' => $request->input('email'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al validar el email'
            ], 500);
        }
    }

    /**
     * Validate slug availability
     */
    public function validateSlug(Request $request)
    {
        $startTime = microtime(true);
        $cacheHit = false;
        
        try {
            $request->validate([
                'slug' => 'required|string'
            ]);

            $slug = $this->validationService->sanitizeSlug($request->input('slug'));
            $storeId = $request->input('store_id'); // For updates

            // Check cache first
            $cached = $this->cacheService->getCachedSlugValidation($slug, $storeId);
            if ($cached) {
                $cacheHit = true;
                $responseTime = (microtime(true) - $startTime) * 1000;
                $this->performanceService->recordValidationPerformance('validateSlug', $responseTime, true);
                
                return response()->json([
                    'success' => true,
                    'data' => $cached
                ]);
            }

            // Use validation service
            $validationResult = $this->validationService->validateSlugAvailability($slug, $storeId);
            
            // Adapt result format for backwards compatibility
            $result = [
                'is_valid' => $validationResult['available'],
                'message' => $validationResult['message'],
                'field' => 'slug',
                'sanitized_value' => $validationResult['clean'] ?? $slug
            ];

            // Cache the result
            $this->cacheService->cacheSlugValidation($slug, $storeId, $result);

            // Record performance metrics
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->performanceService->recordValidationPerformance('validateSlug', $responseTime, false);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            // Record error
            $this->performanceService->recordError('validation_error', 'validateSlug', [
                'slug' => $request->input('slug'),
                'error' => $e->getMessage()
            ]);

            Log::error('Error validating slug', [
                'slug' => $request->input('slug'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al validar el slug'
            ], 500);
        }
    }

    /**
     * Suggest alternative slugs with intelligent algorithms
     */
    public function suggestSlug(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $request->validate([
                'slug' => 'required|string'
            ]);

            $baseSlug = $this->validationService->sanitizeSlug($request->input('slug'));

            // Check cache first
            $cached = $this->cacheService->getCachedSlugSuggestions($baseSlug);
            if ($cached) {
                $responseTime = (microtime(true) - $startTime) * 1000;
                $this->performanceService->recordValidationPerformance('suggestSlug', $responseTime, true);
                
                return response()->json([
                    'success' => true,
                    'data' => $cached
                ]);
            }

            // Use validation service for suggestions
            $suggestions = $this->validationService->suggestSlugFromName($baseSlug);
            
            $result = [
                'suggestions' => array_column($suggestions, 'slug'),
                'base_slug' => $baseSlug,
                'total_generated' => count($suggestions)
            ];

            // Cache the result
            $this->cacheService->cacheSlugSuggestions($baseSlug, $result);

            // Record performance metrics
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->performanceService->recordValidationPerformance('suggestSlug', $responseTime, false);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            // Record error
            $this->performanceService->recordError('validation_error', 'suggestSlug', [
                'slug' => $request->input('slug'),
                'error' => $e->getMessage()
            ]);

            Log::error('Error generating slug suggestions', [
                'slug' => $request->input('slug'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar sugerencias de slug'
            ], 500);
        }
    }

    /**
     * Check if a slug is available
     */
    private function isSlugAvailable(string $slug): bool
    {
        return !Store::where('slug', $slug)->exists() && 
               !RouteServiceProvider::isReservedSlug($slug);
    }

    /**
     * Calculate billing for store creation
     */
    public function calculateBilling(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $request->validate([
                'plan_id' => 'required|exists:plans,id',
                'billing_period' => 'required|in:monthly,quarterly,biannual',
                'discount_code' => 'nullable|string'
            ]);

            $planId = $request->input('plan_id');
            $period = $request->input('billing_period');
            $discountCode = $request->input('discount_code');

            // Check cache first
            $cached = $this->cacheService->getCachedBillingCalculation($planId, $period, $discountCode);
            if ($cached) {
                $responseTime = (microtime(true) - $startTime) * 1000;
                $this->performanceService->recordValidationPerformance('calculateBilling', $responseTime, true);
                
                return response()->json([
                    'success' => true,
                    'data' => $cached
                ]);
            }

            $plan = Plan::findOrFail($planId);

            // Calculate base amount based on period
            $baseAmount = match($period) {
                'monthly' => $plan->monthly_price ?? $plan->price,
                'quarterly' => ($plan->quarterly_price ?? $plan->price * 3),
                'biannual' => ($plan->biannual_price ?? $plan->price * 6),
                default => $plan->price
            };

            $discountAmount = 0;
            $discountPercentage = 0;
            $discountDescription = null;

            // Apply discount if provided
            if ($discountCode) {
                // Here you would implement discount code validation
                // For now, we'll simulate some basic discount logic
                $validDiscounts = [
                    'WELCOME10' => ['percentage' => 10, 'description' => 'Descuento de bienvenida 10%'],
                    'SAVE20' => ['percentage' => 20, 'description' => 'Descuento especial 20%'],
                    'FIRST50' => ['percentage' => 50, 'description' => 'Primer mes 50% descuento']
                ];

                if (isset($validDiscounts[$discountCode])) {
                    $discount = $validDiscounts[$discountCode];
                    $discountPercentage = $discount['percentage'];
                    $discountAmount = ($baseAmount * $discountPercentage) / 100;
                    $discountDescription = $discount['description'];
                }
            }

            $finalAmount = $baseAmount - $discountAmount;
            $tax = $finalAmount * 0.19; // 19% IVA
            $totalAmount = $finalAmount + $tax;

            // Calculate next billing dates
            $nextBillingDate = match($period) {
                'monthly' => now()->addMonth(),
                'quarterly' => now()->addMonths(3),
                'biannual' => now()->addMonths(6),
                default => now()->addMonth()
            };

            $result = [
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'features' => $plan->features ?? []
                ],
                'billing' => [
                    'period' => $period,
                    'base_amount' => $baseAmount,
                    'discount_code' => $discountCode,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                    'discount_description' => $discountDescription,
                    'subtotal' => $finalAmount,
                    'tax' => $tax,
                    'total' => $totalAmount,
                    'currency' => 'COP',
                    'next_billing_date' => $nextBillingDate->format('Y-m-d')
                ]
            ];

            // Cache the result
            $this->cacheService->cacheBillingCalculation($planId, $period, $discountCode, $result);

            // Record performance metrics
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->performanceService->recordValidationPerformance('calculateBilling', $responseTime, false);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            // Record error
            $this->performanceService->recordError('validation_error', 'calculateBilling', [
                'plan_id' => $request->input('plan_id'),
                'billing_period' => $request->input('billing_period'),
                'error' => $e->getMessage()
            ]);

            Log::error('Error calculating billing', [
                'plan_id' => $request->input('plan_id'),
                'billing_period' => $request->input('billing_period'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al calcular la facturación'
            ], 500);
        }
    }

    /**
     * Suggest email domain corrections
     */
    public function suggestEmailDomain(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->input('email');
            $emailParts = explode('@', $email);
            
            if (count($emailParts) !== 2) {
                return response()->json([
                    'success' => true,
                    'data' => ['suggestions' => []]
                ]);
            }

            $localPart = $emailParts[0];
            $domain = strtolower($emailParts[1]);

            // Common email domains and their typos
            $domainSuggestions = [
                'gmail.com' => ['gmai.com', 'gmial.com', 'gmail.co', 'gmaill.com', 'gmeil.com'],
                'hotmail.com' => ['hotmai.com', 'hotmial.com', 'hotmil.com', 'hotmall.com'],
                'yahoo.com' => ['yaho.com', 'yahho.com', 'yahooo.com', 'yahoo.co'],
                'outlook.com' => ['outlok.com', 'outlook.co', 'outloook.com'],
                'icloud.com' => ['iclod.com', 'icloud.co', 'iclooud.com'],
                'empresa.com' => ['empres.com', 'empresa.co'],
                'negocio.com' => ['negoci.com', 'negocio.co']
            ];

            $suggestions = [];

            // Check if current domain is a typo of a common domain
            foreach ($domainSuggestions as $correctDomain => $typos) {
                if (in_array($domain, $typos) || $this->calculateLevenshteinDistance($domain, $correctDomain) <= 2) {
                    $suggestions[] = $localPart . '@' . $correctDomain;
                }
            }

            // If no suggestions found, suggest common domains
            if (empty($suggestions) && !in_array($domain, array_keys($domainSuggestions))) {
                $commonDomains = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com'];
                foreach ($commonDomains as $commonDomain) {
                    $suggestions[] = $localPart . '@' . $commonDomain;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'suggestions' => array_slice($suggestions, 0, 3),
                    'original_email' => $email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating email suggestions', [
                'email' => $request->input('email'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar sugerencias de email'
            ], 500);
        }
    }

    /**
     * Location autocomplete search
     */
    public function searchLocations(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'type' => 'nullable|in:country,department,city'
            ]);

            $query = strtolower(trim($request->input('query')));
            $type = $request->input('type', 'all');

            // Colombian geographic data
            $locations = $this->getColombianLocations();
            $results = [];

            foreach ($locations as $location) {
                // Skip if type filter doesn't match
                if ($type !== 'all' && $location['type'] !== $type) {
                    continue;
                }

                // Check if query matches
                if (stripos($location['name'], $query) !== false || 
                    stripos($location['name_normalized'], $query) !== false) {
                    
                    $results[] = [
                        'id' => $location['id'],
                        'name' => $location['name'],
                        'type' => $location['type'],
                        'parent' => $location['parent'] ?? null,
                        'full_name' => $location['full_name'] ?? $location['name']
                    ];
                }

                // Limit results
                if (count($results) >= 10) {
                    break;
                }
            }

            // Sort by relevance (exact matches first)
            usort($results, function($a, $b) use ($query) {
                $aExact = stripos($a['name'], $query) === 0 ? 1 : 0;
                $bExact = stripos($b['name'], $query) === 0 ? 1 : 0;
                
                if ($aExact !== $bExact) {
                    return $bExact - $aExact;
                }
                
                return strlen($a['name']) - strlen($b['name']);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'query' => $query,
                    'total' => count($results)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching locations', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al buscar ubicaciones'
            ], 500);
        }
    }

    /**
     * Get validation error recovery suggestions
     */
    public function getValidationSuggestions(Request $request)
    {
        try {
            $request->validate([
                'field' => 'required|string',
                'value' => 'required|string',
                'error_type' => 'required|string'
            ]);

            $field = $request->input('field');
            $value = $request->input('value');
            $errorType = $request->input('error_type');

            $suggestions = [];

            switch ($field) {
                case 'slug':
                    if ($errorType === 'taken') {
                        // Get slug suggestions
                        $slugSuggestions = $this->generateSlugSuggestions($value);
                        $suggestions = array_map(function($slug) {
                            return [
                                'type' => 'replacement',
                                'value' => $slug,
                                'description' => "Usar '{$slug}' como URL"
                            ];
                        }, $slugSuggestions);
                    } elseif ($errorType === 'format') {
                        $sanitized = $this->validationService->sanitizeSlug($value);
                        $suggestions[] = [
                            'type' => 'replacement',
                            'value' => $sanitized,
                            'description' => "Formato corregido: '{$sanitized}'"
                        ];
                    }
                    break;

                case 'email':
                case 'admin_email':
                    if ($errorType === 'taken') {
                        $suggestions[] = [
                            'type' => 'action',
                            'value' => 'use_different_email',
                            'description' => 'Usar un email diferente'
                        ];
                        
                        // Suggest variations
                        $emailParts = explode('@', $value);
                        if (count($emailParts) === 2) {
                            $variations = [
                                $emailParts[0] . '1@' . $emailParts[1],
                                $emailParts[0] . '.admin@' . $emailParts[1],
                                $emailParts[0] . '.store@' . $emailParts[1]
                            ];
                            
                            foreach ($variations as $variation) {
                                $suggestions[] = [
                                    'type' => 'replacement',
                                    'value' => $variation,
                                    'description' => "Probar con '{$variation}'"
                                ];
                            }
                        }
                    } elseif ($errorType === 'format') {
                        // Get domain suggestions
                        $domainSuggestions = $this->getEmailDomainSuggestions($value);
                        foreach ($domainSuggestions as $suggestion) {
                            $suggestions[] = [
                                'type' => 'replacement',
                                'value' => $suggestion,
                                'description' => "¿Quisiste decir '{$suggestion}'?"
                            ];
                        }
                    }
                    break;

                case 'document_number':
                    if ($errorType === 'format') {
                        $suggestions[] = [
                            'type' => 'info',
                            'value' => 'format_help',
                            'description' => 'Verificar que el número de documento sea válido'
                        ];
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'suggestions' => $suggestions,
                    'field' => $field,
                    'error_type' => $errorType
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting validation suggestions', [
                'field' => $request->input('field'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener sugerencias'
            ], 500);
        }
    }

    /**
     * Calculate Levenshtein distance between two strings
     */
    private function calculateLevenshteinDistance(string $str1, string $str2): int
    {
        return levenshtein($str1, $str2);
    }

    /**
     * Show bulk import page - Requirements: 7.1
     */
    public function showBulkImport()
    {
        return view('superlinkiu::stores.bulk-import');
    }

    /**
     * Download import template - Requirements: 7.1, 7.2
     */
    public function downloadImportTemplate(Request $request)
    {
        try {
            $type = $request->get('type', 'basic');
            
            $templates = [
                'basic' => [
                    'filename' => 'plantilla_tiendas_basica.csv',
                    'headers' => [
                        'owner_name' => 'Nombre del Propietario',
                        'admin_email' => 'Email del Administrador',
                        'name' => 'Nombre de la Tienda',
                        'plan_id' => 'ID del Plan',
                        'owner_country' => 'País',
                        'owner_department' => 'Departamento',
                        'owner_city' => 'Ciudad'
                    ]
                ],
                'complete' => [
                    'filename' => 'plantilla_tiendas_completa.csv',
                    'headers' => [
                        'owner_name' => 'Nombre del Propietario',
                        'admin_email' => 'Email del Administrador',
                        'owner_document_type' => 'Tipo de Documento',
                        'owner_document_number' => 'Número de Documento',
                        'owner_country' => 'País del Propietario',
                        'owner_department' => 'Departamento del Propietario',
                        'owner_city' => 'Ciudad del Propietario',
                        'name' => 'Nombre de la Tienda',
                        'plan_id' => 'ID del Plan',
                        'slug' => 'URL de la Tienda',
                        'email' => 'Email de la Tienda',
                        'phone' => 'Teléfono',
                        'description' => 'Descripción',
                        'country' => 'País de la Tienda',
                        'department' => 'Departamento de la Tienda',
                        'city' => 'Ciudad de la Tienda',
                        'billing_period' => 'Período de Facturación',
                        'initial_payment_status' => 'Estado de Pago Inicial'
                    ]
                ],
                'enterprise' => [
                    'filename' => 'plantilla_tiendas_empresarial.csv',
                    'headers' => [
                        'owner_name' => 'Nombre del Propietario',
                        'admin_email' => 'Email del Administrador',
                        'owner_document_type' => 'Tipo de Documento del Propietario',
                        'owner_document_number' => 'Número de Documento del Propietario',
                        'owner_country' => 'País del Propietario',
                        'owner_department' => 'Departamento del Propietario',
                        'owner_city' => 'Ciudad del Propietario',
                        'name' => 'Nombre de la Tienda',
                        'plan_id' => 'ID del Plan',
                        'slug' => 'URL de la Tienda',
                        'email' => 'Email de la Tienda',
                        'phone' => 'Teléfono',
                        'description' => 'Descripción',
                        'document_type' => 'Tipo de Documento Fiscal',
                        'document_number' => 'Número de Documento Fiscal',
                        'country' => 'País de la Tienda',
                        'department' => 'Departamento de la Tienda',
                        'city' => 'Ciudad de la Tienda',
                        'address' => 'Dirección',
                        'billing_period' => 'Período de Facturación',
                        'initial_payment_status' => 'Estado de Pago Inicial',
                        'meta_title' => 'Meta Título',
                        'meta_description' => 'Meta Descripción'
                    ]
                ]
            ];

            if (!isset($templates[$type])) {
                return response()->json(['success' => false, 'message' => 'Tipo de plantilla no válido'], 400);
            }

            $template = $templates[$type];
            $headers = array_keys($template['headers']);
            $descriptions = array_values($template['headers']);

            // Create CSV content
            $csvContent = implode(',', $descriptions) . "\n";
            $csvContent .= implode(',', array_map(function($header) {
                return "ejemplo_$header";
            }, $headers)) . "\n";

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $template['filename'] . '"');

        } catch (\Exception $e) {
            Log::error('Error downloading template', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al descargar la plantilla'
            ], 500);
        }
    }

    /**
     * Upload and parse bulk file - Requirements: 7.1, 7.3
     */
    public function uploadBulkFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
            ]);

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Parse file based on type
            if ($extension === 'csv') {
                $data = $this->parseCsvFile($file);
            } else {
                $data = $this->parseExcelFile($file);
            }

            // Store file data in session for later use
            session(['bulk_import_data' => $data]);

            return response()->json([
                'success' => true,
                'data' => [
                    'columns' => $data['columns'],
                    'sample_data' => array_slice($data['rows'], 0, 5), // First 5 rows as sample
                    'total_rows' => count($data['rows']),
                    'file_name' => $file->getClientOriginalName()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading bulk file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate bulk data - Requirements: 7.3
     */
    public function validateBulkData(Request $request)
    {
        try {
            $fileData = $request->input('file_data');
            $columnMapping = $request->input('column_mapping');

            $bulkData = session('bulk_import_data');
            if (!$bulkData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos del archivo no encontrados'
                ], 400);
            }

            $validationResults = $this->validateBulkRows($bulkData['rows'], $columnMapping);

            return response()->json([
                'success' => true,
                'data' => $validationResults
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating bulk data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la validación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process bulk import - Requirements: 7.4, 7.5
     */
    public function processBulkImport(Request $request)
    {
        try {
            $fileData = session('bulk_import_data');
            if (!$fileData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos del archivo no encontrados'
                ], 400);
            }

            $columnMapping = $request->input('column_mapping', []);
            
            // Create batch using batch manager
            $batchId = $this->batchManager->createBatch($fileData, $columnMapping, auth()->id());

            return response()->json([
                'success' => true,
                'data' => ['batch_id' => $batchId]
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting bulk import', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar el procesamiento'
            ], 500);
        }
    }

    /**
     * Get bulk import status - Requirements: 7.4, 7.5
     */
    public function getBulkImportStatus(string $batchId)
    {
        try {
            $status = $this->batchManager->getBatchStatus($batchId);
            
            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lote no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting bulk import status', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado'
            ], 500);
        }
    }

    /**
     * Get bulk import results - Requirements: 7.6
     */
    public function getBulkImportResults(string $batchId)
    {
        try {
            $results = $this->batchManager->getBatchResults($batchId);
            
            if (!$results) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resultados no encontrados'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting bulk import results', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los resultados'
            ], 500);
        }
    }

    /**
     * Download bulk import results - Requirements: 7.6
     */
    public function downloadBulkResults(string $batchId, Request $request)
    {
        try {
            $results = cache()->get("bulk_import_results_{$batchId}");
            
            if (!$results) {
                abort(404, 'Resultados no encontrados');
            }

            $type = $request->get('type', 'results');
            
            if ($type === 'credentials') {
                return Excel::download(
                    new \App\Features\SuperLinkiu\Exports\BulkImportCredentialsExport($results, $batchId),
                    "credenciales_importacion_{$batchId}.xlsx"
                );
            } else {
                return Excel::download(
                    new \App\Features\SuperLinkiu\Exports\BulkImportResultsExport($results),
                    "resultados_importacion_{$batchId}.xlsx"
                );
            }

        } catch (\Exception $e) {
            Log::error('Error downloading bulk results', [
                'batch_id' => $batchId,
                'type' => $request->get('type', 'results'),
                'error' => $e->getMessage()
            ]);

            abort(500, 'Error al descargar los resultados');
        }
    }

    /**
     * Cancel bulk import - Requirements: 7.5
     */
    public function cancelBulkImport(string $batchId)
    {
        try {
            $cancelled = $this->batchManager->cancelBatch($batchId);
            
            if (!$cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo cancelar la importación'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Importación cancelada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling bulk import', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la importación'
            ], 500);
        }
    }

    /**
     * Retry failed bulk import - Requirements: 7.5
     */
    public function retryBulkImport(string $batchId)
    {
        try {
            $newBatchId = $this->batchManager->retryBatch($batchId);
            
            if (!$newBatchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo reintentar la importación'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => ['batch_id' => $newBatchId],
                'message' => 'Importación reintentada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrying bulk import', [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al reintentar la importación'
            ], 500);
        }
    }

    /**
     * Get bulk import queue health - Requirements: 7.5
     */
    public function getBulkImportQueueHealth()
    {
        try {
            $health = $this->batchManager->getQueueHealth();

            return response()->json([
                'success' => true,
                'data' => $health
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting queue health', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado de la cola'
            ], 500);
        }
    }

    /**
     * Show bulk import dashboard - Requirements: 7.6
     */
    public function bulkImportDashboard(Request $request)
    {
        try {
            // Get statistics
            $statistics = $this->getBulkImportStatistics();
            
            // Get recent imports (placeholder - would typically query database)
            $recentImports = $this->getRecentBulkImports();
            
            // Get chart data
            $chartData = $this->getBulkImportChartData();
            
            // If AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'statistics' => $statistics,
                        'recentImports' => $recentImports,
                        'chartData' => $chartData
                    ]
                ]);
            }
            
            return view('superlinkiu::stores.bulk-import-dashboard', compact(
                'statistics',
                'recentImports', 
                'chartData'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading bulk import dashboard', [
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar el dashboard'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error al cargar el dashboard']);
        }
    }

    /**
     * Get bulk import statistics
     */
    private function getBulkImportStatistics(): array
    {
        try {
            return BulkImportLog::getStatistics(30);
        } catch (\Exception $e) {
            Log::error('Error getting bulk import statistics', [
                'error' => $e->getMessage()
            ]);
            
            // Return placeholder data if database query fails
            return [
                'total_imports' => 0,
                'successful_imports' => 0,
                'processing_imports' => 0,
                'failed_imports' => 0,
                'total_stores_created' => 0,
                'average_success_rate' => 0,
                'last_import_date' => null
            ];
        }
    }

    /**
     * Get recent bulk imports
     */
    private function getRecentBulkImports(): array
    {
        try {
            return BulkImportLog::with('user')
                ->recent(7)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'batch_id' => $log->batch_id,
                        'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                        'user_name' => $log->user->name ?? 'Usuario Desconocido',
                        'total_rows' => $log->total_rows,
                        'success_count' => $log->success_count,
                        'error_count' => $log->error_count,
                        'status' => $log->status,
                        'processing_duration' => $log->processing_duration,
                        'success_rate' => $log->success_rate
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting recent bulk imports', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get chart data for dashboard
     */
    private function getBulkImportChartData(): array
    {
        try {
            return BulkImportLog::getChartData(7);
        } catch (\Exception $e) {
            Log::error('Error getting chart data', [
                'error' => $e->getMessage()
            ]);
            
            // Return placeholder data if database query fails
            $dates = [];
            $imports = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $dates[] = now()->subDays($i)->format('M d');
                $imports[] = 0;
            }
            
            return [
                'dates' => $dates,
                'imports' => $imports,
                'status' => [0, 0, 0]
            ];
        }
    }

    /**
     * Parse CSV file
     */
    private function parseCsvFile($file): array
    {
        $content = file_get_contents($file->getRealPath());
        $lines = explode("\n", $content);
        $lines = array_filter($lines, 'trim'); // Remove empty lines

        if (empty($lines)) {
            throw new \Exception('El archivo CSV está vacío');
        }

        // Get headers from first line
        $headers = str_getcsv($lines[0]);
        $rows = [];

        // Parse data rows
        for ($i = 1; $i < count($lines); $i++) {
            $row = str_getcsv($lines[$i]);
            if (count($row) === count($headers)) {
                $rows[] = $row;
            }
        }

        return [
            'columns' => $headers,
            'rows' => $rows
        ];
    }

    /**
     * Parse Excel file
     */
    private function parseExcelFile($file): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        if (empty($data)) {
            throw new \Exception('El archivo Excel está vacío');
        }

        $headers = array_shift($data); // First row as headers
        $rows = array_filter($data, function($row) {
            return !empty(array_filter($row)); // Remove empty rows
        });

        return [
            'columns' => $headers,
            'rows' => array_values($rows)
        ];
    }

    /**
     * Validate bulk rows
     */
    private function validateBulkRows(array $rows, array $columnMapping): array
    {
        $validCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because index starts at 0 and we skip header
            $mappedData = $this->mapRowData($row, $columnMapping);
            
            $validator = \Validator::make($mappedData, [
                'owner_name' => 'required|string|max:255',
                'admin_email' => 'required|email|unique:users,email',
                'name' => 'required|string|max:255',
                'plan_id' => 'required|exists:plans,id'
            ]);

            if ($validator->fails()) {
                $errorCount++;
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => implode(', ', $validator->errors()->all()),
                    'data' => $mappedData
                ];
            } else {
                $validCount++;
            }
        }

        return [
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'warning_count' => 0,
            'errors' => $errors
        ];
    }

    /**
     * Map row data according to column mapping
     */
    private function mapRowData(array $row, array $columnMapping): array
    {
        $mappedData = [];
        
        foreach ($columnMapping as $sourceColumn => $targetField) {
            if ($targetField && isset($row[array_search($sourceColumn, array_keys($columnMapping))])) {
                $mappedData[$targetField] = $row[array_search($sourceColumn, array_keys($columnMapping))];
            }
        }

        return $mappedData;
    }

    /**
     * Generate slug suggestions (helper method)
     */
    private function generateSlugSuggestions(string $baseSlug): array
    {
        $suggestions = [];
        $sanitized = $this->validationService->sanitizeSlug($baseSlug);
        
        for ($i = 1; $i <= 3; $i++) {
            $suggestion = $sanitized . '-' . $i;
            if ($this->isSlugAvailable($suggestion)) {
                $suggestions[] = $suggestion;
            }
        }
        
        return $suggestions;
    }

    /**
     * Get email domain suggestions (helper method)
     */
    private function getEmailDomainSuggestions(string $email): array
    {
        $emailParts = explode('@', $email);
        if (count($emailParts) !== 2) {
            return [];
        }

        $localPart = $emailParts[0];
        $domain = strtolower($emailParts[1]);

        $commonDomains = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com'];
        $suggestions = [];

        foreach ($commonDomains as $commonDomain) {
            if ($this->calculateLevenshteinDistance($domain, $commonDomain) <= 2) {
                $suggestions[] = $localPart . '@' . $commonDomain;
            }
        }

        return $suggestions;
    }

    /**
     * Get Colombian locations data
     */
    private function getColombianLocations(): array
    {
        // This would typically come from a database or external service
        // For now, we'll return a sample of Colombian locations
        return [
            // Countries
            ['id' => 'co', 'name' => 'Colombia', 'name_normalized' => 'colombia', 'type' => 'country', 'full_name' => 'Colombia'],
            
            // Departments
            ['id' => 'co-dc', 'name' => 'Bogotá D.C.', 'name_normalized' => 'bogota dc', 'type' => 'department', 'parent' => 'co', 'full_name' => 'Bogotá D.C., Colombia'],
            ['id' => 'co-ant', 'name' => 'Antioquia', 'name_normalized' => 'antioquia', 'type' => 'department', 'parent' => 'co', 'full_name' => 'Antioquia, Colombia'],
            ['id' => 'co-val', 'name' => 'Valle del Cauca', 'name_normalized' => 'valle del cauca', 'type' => 'department', 'parent' => 'co', 'full_name' => 'Valle del Cauca, Colombia'],
            ['id' => 'co-atl', 'name' => 'Atlántico', 'name_normalized' => 'atlantico', 'type' => 'department', 'parent' => 'co', 'full_name' => 'Atlántico, Colombia'],
            ['id' => 'co-san', 'name' => 'Santander', 'name_normalized' => 'santander', 'type' => 'department', 'parent' => 'co', 'full_name' => 'Santander, Colombia'],
            
            // Cities
            ['id' => 'co-dc-bog', 'name' => 'Bogotá', 'name_normalized' => 'bogota', 'type' => 'city', 'parent' => 'co-dc', 'full_name' => 'Bogotá, Bogotá D.C., Colombia'],
            ['id' => 'co-ant-med', 'name' => 'Medellín', 'name_normalized' => 'medellin', 'type' => 'city', 'parent' => 'co-ant', 'full_name' => 'Medellín, Antioquia, Colombia'],
            ['id' => 'co-val-cal', 'name' => 'Cali', 'name_normalized' => 'cali', 'type' => 'city', 'parent' => 'co-val', 'full_name' => 'Cali, Valle del Cauca, Colombia'],
            ['id' => 'co-atl-bar', 'name' => 'Barranquilla', 'name_normalized' => 'barranquilla', 'type' => 'city', 'parent' => 'co-atl', 'full_name' => 'Barranquilla, Atlántico, Colombia'],
            ['id' => 'co-san-buc', 'name' => 'Bucaramanga', 'name_normalized' => 'bucaramanga', 'type' => 'city', 'parent' => 'co-san', 'full_name' => 'Bucaramanga, Santander, Colombia'],
            ['id' => 'co-ant-itu', 'name' => 'Itagüí', 'name_normalized' => 'itagui', 'type' => 'city', 'parent' => 'co-ant', 'full_name' => 'Itagüí, Antioquia, Colombia'],
            ['id' => 'co-ant-env', 'name' => 'Envigado', 'name_normalized' => 'envigado', 'type' => 'city', 'parent' => 'co-ant', 'full_name' => 'Envigado, Antioquia, Colombia'],
        ];
    }

    /**
     * Validate fiscal document
     * Requirements: 3.3, 3.4 - Business document validation with country-specific rules
     */
    public function validateFiscalDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|in:nit,rut,rfc,cedula',
            'document_number' => 'required|string|max:20',
            'country' => 'nullable|string|max:100'
        ]);

        $fiscalService = app(\App\Features\SuperLinkiu\Services\FiscalValidationService::class);
        
        $result = $fiscalService->validateFiscalDocument(
            $request->document_type,
            $request->document_number,
            $request->country
        );

        return response()->json($result);
    }

    /**
     * Get tax regimes for country
     * Requirements: 3.3, 3.4 - Country-specific tax information
     */
    public function getTaxRegimes(Request $request)
    {
        $request->validate([
            'country' => 'required|string|max:100'
        ]);

        $fiscalService = app(\App\Features\SuperLinkiu\Services\FiscalValidationService::class);
        $taxRegimes = $fiscalService->getTaxRegimes($request->country);

        return response()->json([
            'success' => true,
            'tax_regimes' => $taxRegimes
        ]);
    }

    /**
     * Get document types for country
     * Requirements: 3.3, 3.4 - Country-specific document types
     */
    public function getDocumentTypes(Request $request)
    {
        $request->validate([
            'country' => 'required|string|max:100'
        ]);

        $fiscalService = app(\App\Features\SuperLinkiu\Services\FiscalValidationService::class);
        $documentTypes = $fiscalService->getDocumentTypesByCountry($request->country);

        return response()->json([
            'success' => true,
            'document_types' => $documentTypes
        ]);
    }

    /**
     * Validate complete fiscal information
     * Requirements: 3.3, 3.4 - Complete fiscal information validation
     */
    public function validateFiscalInformation(Request $request)
    {
        $fiscalService = app(\App\Features\SuperLinkiu\Services\FiscalValidationService::class);
        $result = $fiscalService->validateFiscalInformation($request->all());

        return response()->json($result);
    }

    /**
     * Save draft data
     */
    public function saveDraft(Request $request)
    {
        try {
            $request->validate([
                'form_data' => 'required|array',
                'template' => 'nullable|string',
                'current_step' => 'nullable|integer|min:1'
            ]);

            $userId = auth()->id();
            $formData = $request->input('form_data');
            $template = $request->input('template');
            $currentStep = $request->input('current_step', 1);

            // Create or update draft
            $draft = \App\Models\StoreDraft::createOrUpdate(
                $userId,
                $formData,
                $template,
                $currentStep
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'draft_id' => $draft->id,
                    'saved_at' => $draft->updated_at->toISOString(),
                    'expires_at' => $draft->expires_at->toISOString(),
                    'current_step' => $draft->current_step
                ],
                'message' => 'Borrador guardado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving draft', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'form_data_keys' => array_keys($request->input('form_data', []))
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el borrador'
            ], 500);
        }
    }

    /**
     * Get user's latest draft
     */
    public function getDraft(Request $request)
    {
        try {
            $userId = auth()->id();
            $draft = \App\Models\StoreDraft::getLatestForUser($userId);

            if (!$draft) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No hay borradores disponibles'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $draft->id,
                    'form_data' => $draft->form_data,
                    'template' => $draft->template,
                    'current_step' => $draft->current_step,
                    'created_at' => $draft->created_at->toISOString(),
                    'updated_at' => $draft->updated_at->toISOString(),
                    'expires_at' => $draft->expires_at->toISOString(),
                    'is_expired' => $draft->isExpired()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting draft', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el borrador'
            ], 500);
        }
    }

    /**
     * Delete a draft
     */
    public function deleteDraft(Request $request, $draftId = null)
    {
        try {
            $userId = auth()->id();
            
            if ($draftId) {
                $draft = \App\Models\StoreDraft::where('id', $draftId)
                    ->where('user_id', $userId)
                    ->first();
            } else {
                $draft = \App\Models\StoreDraft::getLatestForUser($userId);
            }

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrador no encontrado'
                ], 404);
            }

            $draft->delete();

            return response()->json([
                'success' => true,
                'message' => 'Borrador eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting draft', [
                'user_id' => auth()->id(),
                'draft_id' => $draftId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el borrador'
            ], 500);
        }
    }

    /**
     * Check for draft conflicts (concurrent editing)
     */
    public function checkDraftConflict(Request $request)
    {
        try {
            $request->validate([
                'draft_id' => 'required|integer',
                'last_known_update' => 'required|date'
            ]);

            $userId = auth()->id();
            $draftId = $request->input('draft_id');
            $lastKnownUpdate = $request->input('last_known_update');

            $draft = \App\Models\StoreDraft::where('id', $draftId)
                ->where('user_id', $userId)
                ->first();

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrador no encontrado'
                ], 404);
            }

            $hasConflict = $draft->updated_at->gt($lastKnownUpdate);

            return response()->json([
                'success' => true,
                'data' => [
                    'has_conflict' => $hasConflict,
                    'server_updated_at' => $draft->updated_at->toISOString(),
                    'client_last_known' => $lastKnownUpdate,
                    'draft_data' => $hasConflict ? $draft->form_data : null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking draft conflict', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar conflictos'
            ], 500);
        }
    }

    /**
     * Extend draft expiration
     */
    public function extendDraft(Request $request, $draftId)
    {
        try {
            $request->validate([
                'days' => 'nullable|integer|min:1|max:30'
            ]);

            $userId = auth()->id();
            $days = $request->input('days', 7);

            $draft = \App\Models\StoreDraft::where('id', $draftId)
                ->where('user_id', $userId)
                ->first();

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrador no encontrado'
                ], 404);
            }

            $draft->extend($days);

            return response()->json([
                'success' => true,
                'data' => [
                    'new_expires_at' => $draft->expires_at->toISOString(),
                    'extended_days' => $days
                ],
                'message' => "Borrador extendido por {$days} días"
            ]);

        } catch (\Exception $e) {
            Log::error('Error extending draft', [
                'user_id' => auth()->id(),
                'draft_id' => $draftId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al extender el borrador'
            ], 500);
        }
    }
    /**
     * Send credentials by email from creation modal
     * Requirements: 4.2 - Credential email delivery option
     */
    public function sendCredentialsByEmail(Request $request)
    {
        try {
            // 📧 MODAL DE CREACIÓN - Envío con credenciales completas
            if ($request->has('credentials')) {
                $request->validate([
                    'email' => 'required|email',
                    'credentials' => 'required|array',
                    'credentials.store_name' => 'required|string',
                    'credentials.name' => 'required|string',
                    'credentials.password' => 'required|string',
                    'credentials.store_slug' => 'required|string',
                    'credentials.frontend_url' => 'required|url',
                    'credentials.admin_url' => 'required|url'
                ]);

                $email = $request->input('email');
                $credentials = $request->input('credentials');

                \App\Jobs\SendEmailJob::dispatch('template', $email, [
                    'template_key' => 'store_credentials',
                    'variables' => [
                        'store_name' => $credentials['store_name'],
                        'admin_name' => $credentials['name'],
                        'admin_email' => $email,
                        'password' => $credentials['password'],
                        'dashboard_url' => $credentials['admin_url'],
                        'support_email' => 'soporte@linkiu.email'
                    ]
                ]);

                Log::info('Store credentials sent by email from creation modal', [
                    'email' => $email,
                    'store_name' => $credentials['store_name']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Credenciales enviadas exitosamente'
                ]);
            }

            // 🔄 REENVÍO - Desde vista de edición con store_id y admin_id
            elseif ($request->has('store_id') && $request->has('admin_id')) {
                $request->validate([
                    'store_id' => 'required|exists:stores,id',
                    'admin_id' => 'required|exists:users,id'
                ]);

                $store = Store::with(['plan', 'admins'])->findOrFail($request->store_id);
                $admin = $store->admins()->findOrFail($request->admin_id);

                \App\Jobs\SendEmailJob::dispatch('template', $admin->email, [
                    'template_key' => 'store_credentials',
                    'variables' => [
                        'store_name' => $store->name,
                        'admin_name' => $admin->name,
                        'admin_email' => $admin->email,
                        'password' => 'Para nueva contraseña, contacta soporte',
                        'login_url' => route('tenant.admin.login', $store->slug),
                        'store_url' => url($store->slug),
                        'support_email' => 'soporte@linkiu.email'
                    ]
                ]);

                Log::info('Store credentials resent by email', [
                    'admin_email' => $admin->email,
                    'admin_name' => $admin->name,
                    'store_name' => $store->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Credenciales reenviadas a {$admin->email}"
                ]);
            }

            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos insuficientes para enviar credenciales'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error sending credentials by email', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar las credenciales por email'
            ], 500);
        }
    }

    /**
     * Send welcome email with setup guide
     * Requirements: 4.5, 4.6 - Welcome email automation and setup checklist
     */
    public function sendWelcomeEmail(Request $request)
    {
        try {
            $request->validate([
                'store_id' => 'required|exists:stores,id',
                'email' => 'required|email',
                'store_data' => 'required|array',
                'credentials' => 'required|array',
                'setup_tasks' => 'required|array'
            ]);

            $storeId = $request->input('store_id');
            $email = $request->input('email');
            $storeData = $request->input('store_data');
            $credentials = $request->input('credentials');
            $setupTasks = $request->input('setup_tasks');

            // Get store instance
            $store = Store::findOrFail($storeId);

            // Send welcome email using EmailService template system
            \App\Services\EmailService::sendWithTemplate(
                'store_welcome',
                [$email],
                [
                    'app_name' => config('app.name', 'SuperLinkiu'),
                    'store_name' => $storeData['name'],
                    'admin_name' => $credentials['name'] ?? 'Administrador',
                    'admin_email' => $email,
                    'login_url' => $credentials['admin_url'] ?? $storeData['admin_url'] ?? '',
                    'support_email' => \App\Services\EmailService::getContextEmail('support')
                ]
            );

            Log::info('Welcome email sent', [
                'store_id' => $storeId,
                'email' => $email,
                'store_name' => $storeData['name']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email de bienvenida enviado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending welcome email', [
                'store_id' => $request->input('store_id'),
                'email' => $request->input('email'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email de bienvenida'
            ], 500);
        }
    }
}
