<?php

namespace App\Features\TenantAdmin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Features\TenantAdmin\Models\SimpleShipping;
use App\Features\TenantAdmin\Models\SimpleShippingZone;
use App\Shared\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SimpleShippingController extends Controller
{
    /**
     * Mostrar la configuración de envíos
     */
    public function index(Request $request)
    {
        $store = $request->route('store');
        
        // Obtener o crear configuración de envío
        $shipping = SimpleShipping::getOrCreateForStore($store->id, $store->city);
        $shipping->load('activeZones');
        
        // Límites según el plan (consistente con sidebar)
        $zoneLimits = [
            'explorer' => 2,
            'master' => 3,
            'legend' => 4
        ];
        
        $planSlug = strtolower($store->plan->slug ?? 'explorer');
        $maxZones = $zoneLimits[$planSlug] ?? 2;
        $currentZoneCount = $shipping->zones()->count();
        
        return view('tenant-admin::simple-shipping.index', compact(
            'store',
            'shipping',
            'maxZones',
            'currentZoneCount'
        ));
    }

    /**
     * Actualizar configuración general
     */
    public function update(Request $request)
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            // Recogida en tienda
            'pickup_enabled' => 'boolean',
            'pickup_instructions' => 'nullable|string|max:500',
            'pickup_preparation_time' => ['nullable', 'string', Rule::in(array_keys(SimpleShipping::PREPARATION_TIMES))],
            
            // Envío local
            'local_enabled' => 'boolean',
            'local_cost' => 'nullable|numeric|min:0|max:999999',
            'local_free_from' => 'nullable|numeric|min:0|max:999999',
            'local_city' => 'nullable|string|max:100',
            'local_instructions' => 'nullable|string|max:500',
            'local_preparation_time' => ['nullable', 'string', Rule::in(array_keys(SimpleShipping::PREPARATION_TIMES))],
            
            // Envío nacional
            'national_enabled' => 'boolean',
            'national_free_from' => 'nullable|numeric|min:0|max:999999',
            'national_instructions' => 'nullable|string|max:500',
            
            // Ciudades no listadas
            'allow_unlisted_cities' => 'boolean',
            'unlisted_cities_cost' => 'nullable|numeric|min:0|max:999999',
            'unlisted_cities_message' => 'nullable|string|max:200',
        ]);

        try {
            $shipping = SimpleShipping::getOrCreateForStore($store->id, $store->city);
            $shipping->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Configuración de envíos actualizada correctamente',
                'shipping' => $shipping->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva zona de envío
     */
    public function createZone(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'cost' => 'required|numeric|min:0|max:999999',
            'delivery_time' => ['required', 'string', Rule::in(array_keys(SimpleShipping::DELIVERY_TIMES))],
            'cities' => 'required|array|min:1|max:20',
            'cities.*' => 'required|string|max:100',
        ]);

        try {
            $shipping = SimpleShipping::getOrCreateForStore($store->id, $store->city);
            
            // Verificar límites del plan (consistente)
            $zoneLimits = [
                'explorer' => 2,
                'master' => 3,
                'legend' => 4
            ];
            
            $planSlug = strtolower($store->plan->slug ?? 'explorer');
            $maxZones = $zoneLimits[$planSlug] ?? 2;
            $currentZoneCount = $shipping->zones()->count();
            
            if ($currentZoneCount >= $maxZones) {
                return response()->json([
                    'success' => false,
                    'message' => "Has alcanzado el límite de {$maxZones} zonas para tu plan {$store->plan->name}"
                ], 422);
            }
            
            // Filtrar ciudades únicas y limpias
            $cities = array_values(array_unique(array_filter(array_map('trim', $validated['cities']))));
            
            // Verificar que no haya ciudades duplicadas en otras zonas
            $existingCities = $this->getExistingCitiesInZones($shipping);
            $duplicatedCities = array_intersect(array_map('strtolower', $cities), array_map('strtolower', $existingCities));
            
            if (!empty($duplicatedCities)) {
                $citiesText = count($duplicatedCities) === 1 ? 'La ciudad' : 'Las ciudades';
                $zonesText = count($duplicatedCities) === 1 ? 'otra zona' : 'otras zonas';
                $cityList = implode(', ', array_map('ucfirst', $duplicatedCities));
                
                return response()->json([
                    'success' => false,
                    'message' => "{$citiesText} {$cityList} ya se encuentra registrada en {$zonesText}. Por favor, eliminala de la otra zona o elige una ciudad diferente."
                ], 422);
            }

            // Crear la zona
            $zone = $shipping->zones()->create([
                'name' => $validated['name'],
                'cost' => $validated['cost'],
                'delivery_time' => $validated['delivery_time'],
                'cities' => $cities,
                'sort_order' => $currentZoneCount,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Zona de envío creada correctamente',
                'zone' => $zone->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la zona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar zona de envío
     */
    public function updateZone(Request $request, $store, $zone_id): JsonResponse
    {
        // Buscar la zona manualmente
        $zone = SimpleShippingZone::where('id', $zone_id)
            ->whereHas('simpleShipping', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })
            ->first();

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'Zona no encontrada'
            ], 404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'cost' => 'required|numeric|min:0|max:999999',
            'delivery_time' => ['required', 'string', Rule::in(array_keys(SimpleShipping::DELIVERY_TIMES))],
            'cities' => 'required',
        ]);
        
        // Manejar cities si viene como string JSON (desde FormData)
        if (is_string($validated['cities'])) {
            $validated['cities'] = json_decode($validated['cities'], true);
        }
        
        // Validar cities después de decodificar
        if (!is_array($validated['cities']) || empty($validated['cities']) || count($validated['cities']) > 20) {
            return response()->json([
                'success' => false,
                'message' => 'Ciudades debe ser un array de 1 a 20 elementos'
            ], 422);
        }

        try {
            // Filtrar ciudades únicas y limpias
            $cities = array_values(array_unique(array_filter(array_map('trim', $validated['cities']))));
            
            // Verificar ciudades duplicadas (excluyendo la zona actual)
            $existingCities = $this->getExistingCitiesInZones($zone->simpleShipping, $zone->id);
            $duplicatedCities = array_intersect(array_map('strtolower', $cities), array_map('strtolower', $existingCities));
            
            if (!empty($duplicatedCities)) {
                $citiesText = count($duplicatedCities) === 1 ? 'La ciudad' : 'Las ciudades';
                $zonesText = count($duplicatedCities) === 1 ? 'otra zona' : 'otras zonas';
                $cityList = implode(', ', array_map('ucfirst', $duplicatedCities));
                
                return response()->json([
                    'success' => false,
                    'message' => "{$citiesText} {$cityList} ya se encuentra registrada en {$zonesText}. Por favor, eliminala de la otra zona o elige una ciudad diferente."
                ], 422);
            }

            $zone->update([
                'name' => $validated['name'],
                'cost' => $validated['cost'],
                'delivery_time' => $validated['delivery_time'],
                'cities' => $cities,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Zona actualizada correctamente',
                'zone' => $zone->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la zona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar zona de envío
     */
    public function deleteZone(Request $request, $store, $zone_id): JsonResponse
    {
        try {
            // Validar que zone_id sea un número
            if (!is_numeric($zone_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de zona inválido'
                ], 400);
            }

            // Buscar la zona manualmente
            $zone = SimpleShippingZone::where('id', $zone_id)
                ->whereHas('simpleShipping', function($query) use ($store) {
                    $query->where('store_id', $store->id);
                })
                ->first();

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona no encontrada'
                ], 404);
            }

            $zone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Zona eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la zona: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reordenar zonas
     */
    public function reorderZones(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'zones' => 'required|array',
            'zones.*.id' => 'required|integer|exists:simple_shipping_zones,id',
            'zones.*.sort_order' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $store) {
                foreach ($validated['zones'] as $zoneData) {
                    SimpleShippingZone::where('id', $zoneData['id'])
                        ->whereHas('simpleShipping', function ($query) use ($store) {
                            $query->where('store_id', $store->id);
                        })
                        ->update(['sort_order' => $zoneData['sort_order']]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Orden actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar las zonas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Calcular costo de envío
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        $validated = $request->validate([
            'city' => 'required|string|max:100',
            'subtotal' => 'required|numeric|min:0'
        ]);

        try {
            $shipping = SimpleShipping::getOrCreateForStore($store->id, $store->city);
            $shipping->load('activeZones');
            $result = $shipping->calculateShippingCost($validated['city'], $validated['subtotal']);

            return response()->json([
                'success' => true,
                'shipping' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular el costo de envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener opciones de envío disponibles
     */
    public function getOptions(Request $request): JsonResponse
    {
        $store = $request->route('store');
        
        try {
            $shipping = SimpleShipping::getOrCreateForStore($store->id, $store->city);
            $shipping->load('activeZones');
            $options = $shipping->getAvailableOptions();

            return response()->json([
                'success' => true,
                'options' => $options
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las opciones de envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ciudades existentes en todas las zonas (excepto la zona excluida)
     */
    private function getExistingCitiesInZones(SimpleShipping $shipping, ?int $excludeZoneId = null): array
    {
        $query = $shipping->zones()->active();
        
        if ($excludeZoneId) {
            $query->where('id', '!=', $excludeZoneId);
        }
        
        $zones = $query->get();
        $cities = [];
        
        foreach ($zones as $zone) {
            if ($zone->cities && is_array($zone->cities)) {
                $cities = array_merge($cities, $zone->cities);
            }
        }
        
        return $cities;
    }
}
