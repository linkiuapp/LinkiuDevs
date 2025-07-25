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
            'slug' => 'required|string|max:255|unique:stores,slug',
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
        ]);

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

        return redirect()
            ->route('superlinkiu.stores.index')
            ->with('success', 'Tienda creada exitosamente.')
            ->with('admin_credentials', $adminCredentials);
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

        // Verificar si puede cambiar el slug (upgrade de Explorer a plan superior)
        $oldPlan = $store->plan;
        $newPlan = Plan::find($request->plan_id);
        
        if ($oldPlan && $newPlan && 
            strtolower($oldPlan->name) === 'explorer' && 
            $newPlan->allow_custom_slug && 
            $request->has('slug') && 
            $request->slug !== $store->slug) {
            
            $rules['slug'] = 'required|string|max:255|unique:stores,slug,' . $store->id;
        }

        $validated = $request->validate($rules);

        // Si verified viene como checkbox, convertir a boolean
        if ($request->has('verified')) {
            $validated['verified'] = $request->boolean('verified');
        }

        // Verificar slug reservado si se está cambiando
        if (isset($validated['slug']) && RouteServiceProvider::isReservedSlug($validated['slug'])) {
            return back()->withErrors(['slug' => 'Este slug está reservado por el sistema.'])->withInput();
        }

        $store->update($validated);

        return redirect()
            ->route('superlinkiu.stores.index')
            ->with('success', 'Tienda actualizada exitosamente.');
    }

    public function destroy(Store $store)
    {
        $store->delete();

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