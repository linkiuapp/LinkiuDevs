<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = Plan::orderBy('sort_order')
            ->orderBy('created_at')
            ->paginate(10);
            
        return view('superlinkiu::plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superlinkiu::plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:plans,name',
            'description' => 'nullable|string',
            'allow_custom_slug' => 'boolean',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_in_days' => 'required|integer|min:1',
            
            // Precios por período
            'prices.monthly' => 'nullable|numeric|min:0',
            'prices.quarterly' => 'nullable|numeric|min:0',
            'prices.semester' => 'nullable|numeric|min:0',
            
            // Límites
            'max_products' => 'required|integer|min:1',
            'max_slider' => 'required|integer|min:0',
            'max_active_promotions' => 'required|integer|min:0',
            'max_active_coupons' => 'required|integer|min:0',
            'max_categories' => 'required|integer|min:1',
            'max_sedes' => 'required|integer|min:1',
            'max_admins' => 'required|integer|min:1',
            'max_delivery_zones' => 'required|integer|min:1',
            
            // Soporte
            'support_level' => 'required|in:basic,priority,premium',
            'support_response_time' => 'required|integer|min:1',
            
            // Configuración
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
            'trial_days' => 'nullable|integer|min:0',
            
            // Features
            'features_list' => 'nullable|array',
            'features_list.*' => 'string',
        ]);

        // Preparar datos
        $validated['allow_custom_slug'] = $request->boolean('allow_custom_slug');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_public'] = $request->boolean('is_public');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['version'] = '1.0';
        
        // Preparar precios
        if (isset($validated['prices'])) {
            $validated['prices'] = array_filter($validated['prices'], function($price) {
                return $price !== null && $price !== '';
            });
        }

        Plan::create($validated);

        return redirect()
            ->route('superlinkiu.plans.index')
            ->with('success', 'Plan creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        $plan->loadCount('stores');
        return view('superlinkiu::plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        return view('superlinkiu::plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:plans,name,' . $plan->id,
            'description' => 'nullable|string',
            'allow_custom_slug' => 'boolean',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'duration_in_days' => 'required|integer|min:1',
            
            // Precios por período
            'prices.monthly' => 'nullable|numeric|min:0',
            'prices.quarterly' => 'nullable|numeric|min:0',
            'prices.semester' => 'nullable|numeric|min:0',
            
            // Límites
            'max_products' => 'required|integer|min:1',
            'max_slider' => 'required|integer|min:0',
            'max_active_promotions' => 'required|integer|min:0',
            'max_active_coupons' => 'required|integer|min:0',
            'max_categories' => 'required|integer|min:1',
            'max_sedes' => 'required|integer|min:1',
            'max_admins' => 'required|integer|min:1',
            'max_delivery_zones' => 'required|integer|min:1',
            
            // Soporte
            'support_level' => 'required|in:basic,priority,premium',
            'support_response_time' => 'required|integer|min:1',
            
            // Configuración
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
            'trial_days' => 'nullable|integer|min:0',
            
            // Features
            'features_list' => 'nullable|array',
            'features_list.*' => 'string',
        ]);

        // Preparar datos
        $validated['allow_custom_slug'] = $request->boolean('allow_custom_slug');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_public'] = $request->boolean('is_public');
        $validated['is_featured'] = $request->boolean('is_featured');
        
        // Incrementar versión si hay cambios significativos
        $significantChanges = ['price', 'max_products', 'max_categories', 'max_sedes'];
        $hasSignificantChanges = false;
        
        foreach ($significantChanges as $field) {
            if (isset($validated[$field]) && $plan->$field != $validated[$field]) {
                $hasSignificantChanges = true;
                break;
            }
        }
        
        if ($hasSignificantChanges) {
            $currentVersion = floatval($plan->version);
            $validated['version'] = number_format($currentVersion + 0.1, 1);
        }
        
        // Preparar precios
        if (isset($validated['prices'])) {
            $validated['prices'] = array_filter($validated['prices'], function($price) {
                return $price !== null && $price !== '';
            });
        }

        $plan->update($validated);

        return redirect()
            ->route('superlinkiu.plans.index')
            ->with('success', 'Plan actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        // Verificar si tiene tiendas activas
        if ($plan->hasActiveStores()) {
            return back()->with('error', 'No se puede eliminar un plan con tiendas activas.');
        }

        $plan->delete();

        return redirect()
            ->route('superlinkiu.plans.index')
            ->with('success', 'Plan eliminado exitosamente.');
    }
}
