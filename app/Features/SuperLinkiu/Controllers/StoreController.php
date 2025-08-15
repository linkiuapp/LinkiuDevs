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
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Store::with('plan');

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

    public function store(Request $request)
    {
        \Log::info('🏪 STORE CREATE: Iniciando creación de tienda', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        try {
            $validated = $request->validate([
            // Información del propietario
            'owner_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'owner_document_type' => 'required|string|in:cedula,nit,pasaporte',
            'owner_document_number' => 'required|string|max:20',
            'owner_country' => 'required|string|max:100',
            'owner_department' => 'required|string|max:100',
            'owner_city' => 'required|string|max:100',
            'admin_password' => 'required|string|min:8',
            
            // Información de la tienda
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'slug' => 'required|string|max:255|unique:stores,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'email' => 'nullable|email|unique:stores,email',
            'document_type' => 'nullable|string|in:nit,cedula',
            'document_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'verified' => 'nullable|boolean',
            'billing_period' => 'nullable|in:monthly,quarterly,biannual',
            'initial_payment_status' => 'nullable|in:pending,paid',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ], [
            'slug.regex' => 'La URL debe contener solo letras minúsculas, números y guiones. No se permiten espacios ni caracteres especiales.',
            'slug.unique' => 'Esta URL ya está en uso por otra tienda.',
            'slug.required' => 'La URL de la tienda es obligatoria.',
        ]);

        // 🔍 VALIDACIÓN DE SLUG SEGÚN PLAN
        $plan = Plan::findOrFail($validated['plan_id']);
        
        // Si el plan NO permite slug personalizado, generar uno automático
        if (!$plan->allow_custom_slug) {
            $validated['slug'] = $this->generateRandomSlug();
        } else {
            // Si permite personalización, sanitizar el slug por si acaso
            $validated['slug'] = $this->sanitizeSlug($validated['slug']);
        }

        // Verificar que el slug no sea reservado
        if (RouteServiceProvider::isReservedSlug($validated['slug'])) {
            return back()->withErrors(['slug' => 'Este slug está reservado por el sistema.'])->withInput();
        }

        // Preparar datos de la tienda (sin los campos del propietario)
        $storeData = collect($validated)->except([
            'owner_name', 'admin_email', 'owner_document_type', 'owner_document_number',
            'owner_country', 'owner_department', 'owner_city', 'admin_password'
        ])->filter(function ($value) {
            return $value !== null && $value !== '';
        })->toArray();

        // 🔒 CREAR TIENDA Y ADMIN EN TRANSACCIÓN ATÓMICA
        try {
            \DB::beginTransaction();

            // Crear la tienda
            $store = Store::create([
                ...$storeData,
                'status' => $validated['status'] ?? 'active',
                'verified' => false,
            ]);

            // 🔧 ASEGURAR QUE billing_period esté disponible para el Observer
            // El Observer usa request('billing_period') para crear la suscripción automática
            if (!$request->has('billing_period') && isset($validated['billing_period'])) {
                $request->merge(['billing_period' => $validated['billing_period']]);
            }

            // 🔧 ASEGURAR QUE initial_payment_status esté disponible para el Observer
            // El Observer usa request('initial_payment_status') para establecer el estado de la primera factura
            if (!$request->has('initial_payment_status') && isset($validated['initial_payment_status'])) {
                $request->merge(['initial_payment_status' => $validated['initial_payment_status']]);
            }

            // 🔧 PASAR CONTEXTO DE TIENDA CREADA AL UserObserver
            $request->merge(['_created_store' => $store, 'store_id' => $store->id]);

            // Crear el usuario administrador de la tienda
            $storeAdmin = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['admin_email'],
                'password' => bcrypt($validated['admin_password']),
                'role' => 'store_admin',
                'store_id' => $store->id,
            ]);

            // ✅ VERIFICAR QUE EL ADMIN SE CREÓ CORRECTAMENTE
            if (!$storeAdmin || !$storeAdmin->store_id) {
                throw new \Exception('Failed to create store admin with store_id');
            }

            // ✅ VERIFICAR QUE LA TIENDA TIENE AL MENOS UN ADMIN
            $adminCount = $store->admins()->count();
            if ($adminCount === 0) {
                throw new \Exception('Store created but no admin was assigned');
            }

            \DB::commit();

            // Log de éxito
            Log::info('Store created successfully with admin', [
                'store_id' => $store->id,
                'store_slug' => $store->slug,
                'admin_id' => $storeAdmin->id,
                'admin_email' => $storeAdmin->email,
                'admin_count' => $adminCount
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            // Log del error
            Log::error('Failed to create store with admin', [
                'error' => $e->getMessage(),
                'store_data' => $storeData,
                'admin_email' => $validated['admin_email'],
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors(['general' => 'Error al crear la tienda: ' . $e->getMessage()])
                ->withInput();
        }

        // Preparar datos para el modal de éxito
        $adminCredentials = [
            'name' => $validated['owner_name'],
            'email' => $validated['admin_email'],
            'password' => $validated['admin_password'], // Solo para mostrar una vez
            'store_name' => $store->name,
            'store_slug' => $store->slug,
            'frontend_url' => url('/' . $store->slug),
            'admin_url' => url('/' . $store->slug . '/admin'),
        ];

        \Log::info('🏪 STORE CREATE: Tienda creada exitosamente', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'store_slug' => $store->slug
        ]);

        return redirect()
            ->route('superlinkiu.stores.index')
            ->with('success', 'Tienda creada exitosamente.')
            ->with('admin_credentials', $adminCredentials);
            
        } catch (\Exception $e) {
            \Log::error('🏪 STORE CREATE: Error crítico', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withErrors(['general' => 'Error interno: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Generar un slug aleatorio para planes que no permiten personalización
     */
    private function generateRandomSlug(): string
    {
        do {
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $slug = 'tienda-';
            
            for ($i = 0; $i < 8; $i++) {
                $slug .= $characters[rand(0, strlen($characters) - 1)];
            }
            
            // Verificar que no exista en la BD
            $exists = Store::where('slug', $slug)->exists();
            
        } while ($exists || RouteServiceProvider::isReservedSlug($slug));
        
        return $slug;
    }

    /**
     * Sanitizar slug personalizado para asegurar formato correcto
     */
    private function sanitizeSlug(string $slug): string
    {
        // Convertir a minúsculas
        $slug = strtolower($slug);
        
        // Eliminar acentos usando alternativa que no requiere iconv
        $accents = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        $slug = strtr($slug, $accents);
        
        // Reemplazar espacios y caracteres no permitidos con guiones
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        
        // Eliminar múltiples guiones consecutivos
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Eliminar guiones al inicio y final
        $slug = trim($slug, '-');
        
        // Si queda vacío, generar uno básico
        if (empty($slug)) {
            $slug = 'tienda-' . rand(1000, 9999);
        }
        
        return $slug;
    }

    public function show(Store $store)
    {
        $store->load(['plan', 'planExtensions' => function($query) {
            $query->with('superAdmin')->latest();
        }]);
        
        return view('superlinkiu::stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        $plans = Plan::active()->get();
        return view('superlinkiu::stores.edit', compact('store', 'plans'));
    }

    public function update(Request $request, Store $store)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
            'email' => 'required|email|unique:stores,email,' . $store->id,
            'document_type' => 'nullable|string|in:nit,cedula',
            'document_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended',
            'verified' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ];

        // Verificar si puede cambiar el slug
        $oldPlan = $store->plan;
        $newPlan = Plan::find($request->plan_id);
        
        \Log::info('🔧 STORE UPDATE: Verificando slug editability', [
            'store_id' => $store->id,
            'old_plan_id' => $oldPlan?->id,
            'old_plan_allow_custom' => $oldPlan?->allow_custom_slug,
            'new_plan_id' => $newPlan?->id,
            'new_plan_allow_custom' => $newPlan?->allow_custom_slug,
            'slug_changed' => $request->slug !== $store->slug,
            'request_slug' => $request->slug,
            'current_slug' => $store->slug
        ]);
        
        // Si el plan actual permite personalización O si es un upgrade a plan que permite personalización
        if ($newPlan && ($newPlan->allow_custom_slug || 
            ($oldPlan && !$oldPlan->allow_custom_slug && $newPlan->allow_custom_slug)) &&
            $request->has('slug') && 
            $request->slug !== $store->slug) {
            
            $rules['slug'] = [
                'required',
                'string',
                'max:255',
                'unique:stores,slug,' . $store->id,
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'
            ];
            
            \Log::info('🔧 STORE UPDATE: Slug validation enabled');
        } else if ($request->has('slug') && $request->slug !== $store->slug) {
            // Si intentan cambiar slug sin permiso, usar el slug original
            $request->merge(['slug' => $store->slug]);
            \Log::info('🔧 STORE UPDATE: Slug change blocked - plan does not allow custom slugs');
        }

        $validated = $request->validate($rules);

        // Si verified viene como checkbox, convertir a boolean
        if ($request->has('verified')) {
            $validated['verified'] = $request->boolean('verified');
        }

        // Procesar slug si se está cambiando
        if (isset($validated['slug'])) {
            // Sanitizar slug personalizado
            if ($newPlan && $newPlan->allow_custom_slug) {
                $validated['slug'] = $this->sanitizeSlug($validated['slug']);
            }
            
            // Verificar slug reservado
            if (RouteServiceProvider::isReservedSlug($validated['slug'])) {
                return back()->withErrors(['slug' => 'Este slug está reservado por el sistema.'])->withInput();
            }
            
            \Log::info('🔧 STORE UPDATE: Slug will be updated', [
                'old_slug' => $store->slug,
                'new_slug' => $validated['slug']
            ]);
        }

        $store->update($validated);

        return redirect()
            ->route('superlinkiu.stores.index')
            ->with('success', 'Tienda actualizada exitosamente.');
    }

    public function destroy(Store $store)
    {
        \Log::info('🗑️ STORE DESTROY: Método llamado', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'request_method' => request()->method(),
            'request_url' => request()->fullUrl(),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role
        ]);
        
        $store->delete();
        
        \Log::info('🗑️ STORE DESTROY: Tienda eliminada exitosamente', [
            'store_id' => $store->id
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tienda eliminada exitosamente.'
            ]);
        }

        return redirect()
            ->route('superlinkiu.stores.index')
            ->with('success', 'Tienda eliminada exitosamente.');
    }

    public function toggleVerified(Store $store)
{
    try {
        $store->toggleVerified();
        
        return response()->json([
            'success' => true,
            'verified' => $store->verified,
            'message' => $store->verified ? 'Tienda verificada exitosamente.' : 'Verificación de tienda removida.'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al cambiar verificación: ' . $e->getMessage()
        ], 500);
    }
}

    public function updateStatus(Request $request, Store $store)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);

        $store->updateStatus($validated['status']);

        return response()->json([
            'status' => $store->status,
            'message' => 'Estado de la tienda actualizado exitosamente.'
        ]);
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
} 