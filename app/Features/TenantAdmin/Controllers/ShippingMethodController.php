<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Features\TenantAdmin\Models\ShippingMethod;
use App\Features\TenantAdmin\Models\ShippingZone;
use App\Features\TenantAdmin\Models\ShippingMethodConfig;
use Illuminate\Support\Facades\DB;

class ShippingMethodController extends Controller
{
    /**
     * Display the shipping methods index
     */
    public function index(Request $request)
    {
        $store = $request->route('store');
        
        // Obtener métodos de envío ordenados
        $methods = ShippingMethod::where('store_id', $store->id)
            ->with(['zones' => function($query) {
                $query->where('is_active', true)->orderBy('cost');
            }])
            ->ordered()
            ->get();
            
        // Obtener configuración
        $config = ShippingMethodConfig::getOrCreateForStore($store->id);
        
        // Contar zonas actuales
        $zonesCount = ShippingZone::where('store_id', $store->id)->count();
        
        // Límites según el plan
        $zoneLimits = [
            'explorer' => 1,
            'master' => 2,
            'legend' => 4
        ];
        
        $planSlug = strtolower($store->plan->slug);
        $maxZones = $zoneLimits[$planSlug] ?? 1;
        
        // Obtener método de domicilio para mostrar zonas
        $domicilioMethod = $methods->where('type', ShippingMethod::TYPE_DOMICILIO)->first();
        
        return view('tenant-admin::shipping-methods.index', compact(
            'store',
            'methods',
            'config',
            'zonesCount',
            'maxZones',
            'domicilioMethod'
        ));
    }
    
    /**
     * Toggle active status of a shipping method
     */
    public function toggleActive(Request $request, $store, ShippingMethod $method)
    {
        $store = $request->route('store');
        
        // Verificar que el método pertenece a esta tienda
        if ($method->store_id !== $store->id) {
            return response()->json([
                'success' => false,
                'message' => 'Método no encontrado'
            ], 404);
        }
        
        // Obtener configuración
        $config = ShippingMethodConfig::getOrCreateForStore($store->id);
        
        // Si se está desactivando, verificar que no sea el único activo
        if ($method->is_active && !$config->canDeactivateMethod($method->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe mantener al menos un método de envío activo'
            ], 422);
        }
        
        // Toggle estado
        $method->is_active = !$method->is_active;
        $method->save();
        
        // Si se desactivó y era el método por defecto, actualizar
        if (!$method->is_active) {
            $config->updateDefaultIfNeeded($method->id);
        }
        
        return response()->json([
            'success' => true,
            'is_active' => $method->is_active,
            'message' => $method->is_active 
                ? 'Método de envío activado' 
                : 'Método de envío desactivado'
        ]);
    }
    
    /**
     * Update pickup method configuration
     */
    public function updatePickup(Request $request, $storeSlug, ShippingMethod $method)
    {
        $store = $request->route('store');
        
        // Verificar que el método pertenece a esta tienda y es pickup
        if ($method->store_id !== $store->id || $method->type !== ShippingMethod::TYPE_PICKUP) {
            return response()->json(['error' => 'Método no encontrado'], 404);
        }
        
        $request->validate([
            'preparation_time' => 'required|string|max:50',
            'notification_enabled' => 'boolean',
            'instructions' => 'nullable|string|max:500'
        ]);
        
        $method->update([
            'preparation_time' => $request->preparation_time,
            'notification_enabled' => $request->boolean('notification_enabled'),
            'instructions' => $request->instructions
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente'
        ]);
    }

    /**
     * Update methods order
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'methods' => 'required|array',
            'methods.*.id' => 'required|integer|exists:shipping_methods,id',
            'methods.*.sort_order' => 'required|integer|min:1'
        ]);
        
        $store = $request->route('store');
        
        DB::transaction(function() use ($request, $store) {
            foreach ($request->methods as $methodData) {
                ShippingMethod::where('id', $methodData['id'])
                    ->where('store_id', $store->id)
                    ->update(['sort_order' => $methodData['sort_order']]);
            }
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado correctamente'
        ]);
    }
    
    /**
     * Show edit form for shipping method
     */
    public function edit(ShippingMethod $method)
    {
        $store = $method->store;
        
        // Verificar que el método pertenece a esta tienda
        if ($method->store_id !== $store->id) {
            abort(404);
        }
        
        return view('tenant-admin::shipping-methods.edit', compact('store', 'method'));
    }
    
    /**
     * Update shipping method
     */
    public function update(Request $request, ShippingMethod $method)
    {
        $store = $method->store;
        
        // Verificar que el método pertenece a esta tienda
        if ($method->store_id !== $store->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'nullable|string|max:500',
            'preparation_time' => $method->type === ShippingMethod::TYPE_PICKUP ? 'required|in:30min,1h,2h,4h' : 'nullable',
            'notification_enabled' => 'boolean'
        ]);
        
        $method->update($validated);
        
        return redirect()
            ->route('tenant.admin.shipping-methods.index', $store->slug)
            ->with('success', 'Método de envío actualizado correctamente');
    }
    
    /**
     * Show create zone form
     */
    public function createZone($storeSlug, ShippingMethod $method)
    {
        $store = $method->store;
        
        // Verificar que el método pertenece a esta tienda
        if ($method->store_id !== $store->id || $method->type !== ShippingMethod::TYPE_DOMICILIO) {
            abort(404);
        }
        
        // Verificar límite de zonas
        $currentZones = ShippingZone::where('store_id', $store->id)->count();
        $zoneLimits = [
            'explorer' => 1,
            'master' => 2,
            'legend' => 4
        ];
        
        $planSlug = strtolower($store->plan->slug);
        $maxZones = $zoneLimits[$planSlug] ?? 1;
        
        if ($currentZones >= $maxZones) {
            return redirect()
                ->route('tenant.admin.shipping-methods.index', $store->slug)
                ->with('error', "Has alcanzado el límite de zonas para tu plan {$store->plan->name} ({$maxZones} zonas)");
        }
        
        return view('tenant-admin::shipping-methods.zones.create', compact('store', 'method'));
    }
    
    /**
     * Store new zone
     */
    public function storeZone(Request $request, $storeSlug, ShippingMethod $method)
    {
        $store = $method->store;
        
        // Verificar que el método pertenece a esta tienda
        if ($method->store_id !== $store->id || $method->type !== ShippingMethod::TYPE_DOMICILIO) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shipping_zones,name,NULL,id,shipping_method_id,' . $method->id,
            'description' => 'nullable|string|max:500',
            'cost' => 'required|numeric|min:0',
            'free_shipping_from' => 'nullable|numeric|min:0',
            'estimated_time' => 'required|in:' . implode(',', array_keys(\App\Features\TenantAdmin\Models\ShippingZone::ESTIMATED_TIMES)),
            'delivery_days' => 'required|array',
            'delivery_days.*' => 'in:L,M,X,J,V,S,D',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean'
        ]);
        
        // Verificar que al menos un día esté seleccionado
        if (empty($validated['delivery_days'])) {
            return back()
                ->withErrors(['delivery_days' => 'Debe seleccionar al menos un día de entrega'])
                ->withInput();
        }
        
        $zone = new ShippingZone($validated);
        $zone->shipping_method_id = $method->id;
        $zone->store_id = $store->id;
        $zone->save();
        
        return redirect()
            ->route('tenant.admin.shipping-methods.index', $store->slug)
            ->with('success', 'Zona de envío creada correctamente');
    }
    
    /**
     * Show edit zone form
     */
    public function editZone($storeSlug, $method, ShippingZone $zone)
    {
        $store = $zone->store;
        
        // Verificar que la zona pertenece a esta tienda
        if ($zone->store_id !== $store->id) {
            abort(404);
        }
        
        $methodModel = $zone->shippingMethod;
        
        return view('tenant-admin::shipping-methods.zones.edit', compact('store', 'zone', 'methodModel'));
    }
    
    /**
     * Update zone
     */
    public function updateZone(Request $request, $storeSlug, $method, ShippingZone $zone)
    {
        $store = $zone->store;
        
        // Verificar que la zona pertenece a esta tienda
        if ($zone->store_id !== $store->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shipping_zones,name,' . $zone->id . ',id,shipping_method_id,' . $zone->shipping_method_id,
            'description' => 'nullable|string|max:500',
            'cost' => 'required|numeric|min:0',
            'free_shipping_from' => 'nullable|numeric|min:0',
            'estimated_time' => 'required|in:' . implode(',', array_keys(\App\Features\TenantAdmin\Models\ShippingZone::ESTIMATED_TIMES)),
            'delivery_days' => 'required|array',
            'delivery_days.*' => 'in:L,M,X,J,V,S,D',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean'
        ]);
        
        // Verificar que al menos un día esté seleccionado
        if (empty($validated['delivery_days'])) {
            return back()
                ->withErrors(['delivery_days' => 'Debe seleccionar al menos un día de entrega'])
                ->withInput();
        }
        
        $zone->update($validated);
        
        return redirect()
            ->route('tenant.admin.shipping-methods.index', $store->slug)
            ->with('success', 'Zona de envío actualizada correctamente');
    }
    
    /**
     * Delete zone
     */
    public function destroyZone($storeSlug, $method, ShippingZone $zone)
    {
        $store = $zone->store;
        
        // Verificar que la zona pertenece a esta tienda
        if ($zone->store_id !== $store->id) {
            abort(404);
        }
        
        $zone->delete();
        
        return redirect()
            ->route('tenant.admin.shipping-methods.index', $store->slug)
            ->with('success', 'Zona de envío eliminada correctamente');
    }
    
    /**
     * Toggle zone active status
     */
    public function toggleZoneActive(Request $request, ShippingZone $zone)
    {
        $store = $zone->store;
        
        // Verificar que la zona pertenece a esta tienda
        if ($zone->store_id !== $store->id) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada'
            ], 404);
        }
        
        $zone->is_active = !$zone->is_active;
        $zone->save();
        
        return response()->json([
            'success' => true,
            'is_active' => $zone->is_active,
            'message' => $zone->is_active 
                ? 'Zona activada' 
                : 'Zona desactivada'
        ]);
    }
} 
