<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Features\TenantAdmin\Models\ProductVariable;
use App\Features\TenantAdmin\Models\VariableOption;
use App\Shared\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VariableController extends Controller
{
    /**
     * Display a listing of variables
     */
    public function index(Request $request)
    {
        $store = $request->route('store');
        
        // Obtener límite según el plan
        $variableLimit = $this->getVariableLimit($store);
        
        // Obtener variables con relaciones
        $variables = ProductVariable::where('store_id', $store->id)
            ->with(['options'])
            ->withCount(['assignments as products_count'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);
            
        // Contar total de variables
        $totalVariables = ProductVariable::where('store_id', $store->id)->count();
        
        return view('tenant-admin::variables.index', compact(
            'store',
            'variables',
            'totalVariables',
            'variableLimit'
        ));
    }

    /**
     * Show the form for creating a new variable
     */
    public function create(Request $request)
    {
        $store = $request->route('store');
        
        // Verificar límite
        $variableLimit = $this->getVariableLimit($store);
        $totalVariables = ProductVariable::where('store_id', $store->id)->count();
        
        if ($totalVariables >= $variableLimit) {
            return redirect()->route('tenant.admin.variables.index', $store->slug)
                ->with('error', 'Has alcanzado el límite de variables para tu plan.');
        }
        
        $types = ProductVariable::TYPES;
        
        return view('tenant-admin::variables.create', compact(
            'store',
            'types',
            'totalVariables',
            'variableLimit'
        ));
    }

    /**
     * Store a newly created variable
     */
    public function store(Request $request)
    {
        $store = $request->route('store');
        
        // Verificar límite nuevamente
        $variableLimit = $this->getVariableLimit($store);
        $totalVariables = ProductVariable::where('store_id', $store->id)->count();
        
        if ($totalVariables >= $variableLimit) {
            return response()->json([
                'error' => "Has alcanzado el límite de variables para tu plan."
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_variables')->where(function ($query) use ($store) {
                    return $query->where('store_id', $store->id);
                })
            ],
            'type' => 'required|in:radio,checkbox,text,numeric',
            'is_required_default' => 'boolean',
            'min_value' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'options' => 'array',
            'options.*.name' => 'required|string|max:255',
            'options.*.price_modifier' => 'numeric',
            'options.*.color_hex' => 'nullable|string|max:7',
        ]);
        
        // Validaciones específicas por tipo
        if (in_array($validated['type'], ['radio', 'checkbox'])) {
            if (empty($validated['options']) || count($validated['options']) < 1) {
                return back()->withErrors(['options' => 'Las variables de selección requieren al menos una opción.']);
            }
        }
        
        if ($validated['type'] === 'numeric') {
            if (isset($validated['min_value']) && isset($validated['max_value'])) {
                if ($validated['min_value'] >= $validated['max_value']) {
                    return back()->withErrors(['max_value' => 'El valor máximo debe ser mayor al mínimo.']);
                }
            }
        }
        
        // Crear la variable
        $variable = ProductVariable::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'store_id' => $store->id,
            'is_required_default' => $request->boolean('is_required_default', false),
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
            'sort_order' => $totalVariables,
        ]);
        
        // Crear opciones si las hay
        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $index => $optionData) {
                VariableOption::create([
                    'variable_id' => $variable->id,
                    'name' => $optionData['name'],
                    'price_modifier' => $optionData['price_modifier'] ?? 0,
                    'color_hex' => $optionData['color_hex'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }
        
        return redirect()
            ->route('tenant.admin.variables.index', $store->slug)
            ->with('success', 'Variable creada exitosamente');
    }

    /**
     * Display the specified variable
     */
    public function show(Request $request, $storeSlug, ProductVariable $variable)
    {
        $store = $request->route('store');
        
        // Verificar que la variable pertenezca a la tienda
        if ($variable->store_id !== $store->id) {
            abort(404);
        }
        
        // Cargar relaciones necesarias
        $variable->load(['options', 'assignments.product']);
        
        return view('tenant-admin::variables.show', compact('store', 'variable'));
    }

    /**
     * Show the form for editing the specified variable
     */
    public function edit(Request $request, $storeSlug, ProductVariable $variable)
    {
        $store = $request->route('store');
        
        // Verificar que la variable pertenezca a la tienda
        if ($variable->store_id !== $store->id) {
            abort(404);
        }
        
        // Cargar opciones
        $variable->load(['options']);
        
        $types = ProductVariable::TYPES;
        $totalVariables = ProductVariable::where('store_id', $store->id)->count();
        $variableLimit = $this->getVariableLimit($store);
        
        return view('tenant-admin::variables.edit', compact(
            'store',
            'variable',
            'types',
            'totalVariables',
            'variableLimit'
        ));
    }

    /**
     * Update the specified variable
     */
    public function update(Request $request, $storeSlug, ProductVariable $variable)
    {
        $store = $request->route('store');
        
        // Verificar que la variable pertenece a la tienda
        if ($variable->store_id !== $store->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_variables')->where(function ($query) use ($store) {
                    return $query->where('store_id', $store->id);
                })->ignore($variable->id)
            ],
            'type' => 'required|in:radio,checkbox,text,numeric',
            'is_required_default' => 'boolean',
            'min_value' => 'nullable|numeric|min:0',
            'max_value' => 'nullable|numeric|min:0',
            'options' => 'array',
            'options.*.name' => 'required|string|max:255',
            'options.*.price_modifier' => 'numeric',
            'options.*.color_hex' => 'nullable|string|max:7',
        ]);
        
        // Validaciones específicas por tipo
        if (in_array($validated['type'], ['radio', 'checkbox'])) {
            if (empty($validated['options']) || count($validated['options']) < 1) {
                return back()->withErrors(['options' => 'Las variables de selección requieren al menos una opción.']);
            }
        }
        
        // Actualizar la variable
        $variable->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_required_default' => $request->boolean('is_required_default', false),
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
        ]);
        
        // Actualizar opciones
        if (!empty($validated['options'])) {
            // Eliminar opciones existentes
            $variable->options()->delete();
            
            // Crear nuevas opciones
            foreach ($validated['options'] as $index => $optionData) {
                VariableOption::create([
                    'variable_id' => $variable->id,
                    'name' => $optionData['name'],
                    'price_modifier' => $optionData['price_modifier'] ?? 0,
                    'color_hex' => $optionData['color_hex'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        } else {
            // Si no hay opciones, eliminar las existentes
            $variable->options()->delete();
        }
        
        return redirect()
            ->route('tenant.admin.variables.index', $store->slug)
            ->with('success', 'Variable actualizada exitosamente');
    }

    /**
     * Remove the specified variable from storage
     */
    public function destroy(Request $request, $storeSlug, ProductVariable $variable)
    {
        $store = $request->route('store');
        
        // Verificar que la variable pertenece a la tienda
        if ($variable->store_id !== $store->id) {
            abort(404);
        }
        
        // Verificar que no esté siendo usada
        if (!$variable->canBeDeleted()) {
            return response()->json([
                'error' => 'No se puede eliminar la variable porque está siendo usada en productos.'
            ], 400);
        }
        
        $variable->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Toggle variable status
     */
    public function toggleStatus(Request $request, $storeSlug, ProductVariable $variable)
    {
        $store = $request->route('store');
        
        // Verificar que la variable pertenece a la tienda
        if ($variable->store_id !== $store->id) {
            abort(404);
        }
        
        $variable->update(['is_active' => !$variable->is_active]);
        
        return response()->json([
            'success' => true,
            'is_active' => $variable->is_active
        ]);
    }

    /**
     * Duplicate a variable
     */
    public function duplicate(Request $request, $storeSlug, ProductVariable $variable)
    {
        $store = $request->route('store');
        
        // Verificar que la variable pertenece a la tienda
        if ($variable->store_id !== $store->id) {
            abort(404);
        }
        
        // Verificar límite
        $variableLimit = $this->getVariableLimit($store);
        $totalVariables = ProductVariable::where('store_id', $store->id)->count();
        
        if ($totalVariables >= $variableLimit) {
            return response()->json([
                'error' => 'Has alcanzado el límite de variables para tu plan.'
            ], 403);
        }
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_variables')->where(function ($query) use ($store) {
                    return $query->where('store_id', $store->id);
                })
            ],
        ]);
        
        // Duplicar la variable
        $newVariable = $variable->replicate();
        $newVariable->name = $validated['name'];
        $newVariable->sort_order = $totalVariables;
        $newVariable->save();
        
        // Duplicar opciones
        foreach ($variable->options as $option) {
            $newOption = $option->replicate();
            $newOption->variable_id = $newVariable->id;
            $newOption->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Variable duplicada exitosamente'
        ]);
    }

    /**
     * Get variable limit based on store plan
     */
    private function getVariableLimit($store)
    {
        return match($store->plan->name) {
            'Explorer' => 5,
            'Master' => 20,
            'Legend' => 50,
            default => 5,
        };
    }
}
