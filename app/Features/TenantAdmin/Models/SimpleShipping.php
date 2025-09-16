<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Shared\Models\Store;

class SimpleShipping extends Model
{
    protected $table = 'simple_shipping';
    
    protected $fillable = [
        'store_id',
        
        // Recogida en tienda
        'pickup_enabled',
        'pickup_instructions',
        'pickup_preparation_time',
        
        // Envío local 
        'local_enabled',
        'local_cost',
        'local_free_from',
        'local_city', // Ciudad donde está el negocio
        'local_instructions',
        'local_preparation_time',
        
        // Envío nacional
        'national_enabled',
        'national_free_from',
        'national_instructions',
        
        // Configuración para ciudades no encontradas
        'allow_unlisted_cities',
        'unlisted_cities_cost',
        'unlisted_cities_message',
    ];

    protected $casts = [
        'pickup_enabled' => 'boolean',
        'local_enabled' => 'boolean',
        'national_enabled' => 'boolean',
        'local_cost' => 'decimal:2',
        'local_free_from' => 'decimal:2',
        'national_free_from' => 'decimal:2',
        'allow_unlisted_cities' => 'boolean',
        'unlisted_cities_cost' => 'decimal:2',
    ];

    // Tiempos de preparación disponibles
    const PREPARATION_TIMES = [
        '30min' => '30 minutos',
        '1h' => '1 hora',
        '2h' => '2 horas',
        '4h' => '4 horas',
        '8h' => '8 horas',
        '24h' => '24 horas',
    ];
    
    // Tiempos de entrega para zonas nacionales
    const DELIVERY_TIMES = [
        '1-2h' => '1-2 horas',
        '2-4h' => '2-4 horas',
        'mismo-dia' => 'Mismo día',
        '24h' => '24 horas',
        '2-3dias' => '2-3 días',
        '3-5dias' => '3-5 días',
        '5-7dias' => '5-7 días',
        '1-2semanas' => '1-2 semanas',
    ];

    /**
     * Relación con la tienda
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relación con las zonas de envío nacional
     */
    public function zones(): HasMany
    {
        return $this->hasMany(SimpleShippingZone::class);
    }

    /**
     * Relación con las zonas activas ordenadas
     */
    public function activeZones(): HasMany
    {
        return $this->zones()->active()->ordered();
    }

    /**
     * Obtener o crear configuración para una tienda
     */
    public static function getOrCreateForStore(int $storeId, string $storeCity = null): self
    {
        return self::firstOrCreate(
            ['store_id' => $storeId],
            [
                // Valores por defecto
                'pickup_enabled' => true,
                'pickup_instructions' => 'Puedes recoger tu pedido en nuestra tienda',
                'pickup_preparation_time' => '1h',
                
                'local_enabled' => true,
                'local_cost' => 3000,
                'local_free_from' => null,
                'local_city' => $storeCity ?? 'Tu Ciudad',
                'local_instructions' => 'Entregamos en toda la ciudad',
                'local_preparation_time' => '2h',
                
                'national_enabled' => false,
                'national_cost' => 8000,
                'national_free_from' => null,
                'national_instructions' => 'Envío a nivel nacional',
                'national_preparation_time' => '3-5dias',
            ]
        );
    }

    /**
     * Calcular costo de envío según ubicación del cliente
     */
    public function calculateShippingCost(string $customerCity, float $subtotal): array
    {
        $customerCity = $this->normalizeCity($customerCity);
        $localCity = $this->normalizeCity($this->local_city);
        
        // 1. Verificar si es ciudad local
        if ($customerCity === $localCity && $this->local_enabled) {
            return $this->getLocalShippingInfo($subtotal);
        }
        
        // 2. Verificar si está en alguna zona nacional
        if ($this->national_enabled) {
            $zone = $this->findZoneForCity($customerCity);
            if ($zone) {
                return $this->getZoneShippingInfo($zone, $subtotal);
            }
            
            // 3. Verificar si permite ciudades no listadas
            if ($this->allow_unlisted_cities) {
                return $this->getUnlistedCityShippingInfo($subtotal);
            }
        }
        
        // 4. No disponible
        return [
            'type' => 'unavailable',
            'available' => false,
            'cost' => 0,
            'message' => 'Envío no disponible para esta ubicación',
        ];
    }

    /**
     * Obtener información de envío local
     */
    private function getLocalShippingInfo(float $subtotal): array
    {
        $cost = $this->local_cost;
        $freeFrom = $this->local_free_from;
        
        // Verificar envío gratis
        if ($freeFrom && $subtotal >= $freeFrom) {
            $cost = 0;
        }
        
        return [
            'type' => 'local',
            'available' => true,
            'cost' => $cost,
            'original_cost' => $this->local_cost,
            'is_free' => $cost == 0 && $this->local_cost > 0,
            'free_from' => $freeFrom,
            'instructions' => $this->local_instructions,
            'preparation_time' => $this->local_preparation_time,
            'preparation_label' => self::PREPARATION_TIMES[$this->local_preparation_time] ?? $this->local_preparation_time,
            'location_label' => "Envío en {$this->local_city}",
            'zone_name' => "Local - {$this->local_city}",
        ];
    }

    /**
     * Obtener información de envío para una zona específica
     */
    private function getZoneShippingInfo(SimpleShippingZone $zone, float $subtotal): array
    {
        $cost = $zone->cost;
        $freeFrom = $this->national_free_from;
        
        // Solo aplicar envío gratis si está habilitado Y el subtotal califica Y hay costo original
        $isFreeShipping = false;
        if ($freeFrom && $freeFrom > 0 && $subtotal >= $freeFrom && $cost > 0) {
            $cost = 0;
            $isFreeShipping = true;
        }
        
        \Log::info('🚚 SHIPPING DEBUG:', [
            'zone' => $zone->name,
            'original_cost' => $zone->cost,
            'subtotal' => $subtotal,
            'free_from' => $freeFrom,
            'is_free' => $isFreeShipping,
            'final_cost' => $cost
        ]);
        
        return [
            'type' => 'national',
            'available' => true,
            'cost' => $cost,
            'original_cost' => $zone->cost,
            'is_free' => $cost == 0 && $zone->cost > 0,
            'free_from' => $freeFrom,
            'instructions' => $this->national_instructions,
            'preparation_time' => $zone->delivery_time,
            'preparation_label' => self::DELIVERY_TIMES[$zone->delivery_time] ?? $zone->delivery_time,
            'location_label' => "Envío Nacional - {$zone->name}",
            'zone_name' => $zone->name,
            'zone_id' => $zone->id,
        ];
    }

    /**
     * Obtener información para ciudades no listadas
     */
    private function getUnlistedCityShippingInfo(float $subtotal): array
    {
        $cost = $this->unlisted_cities_cost;
        $freeFrom = $this->national_free_from;
        
        // Verificar envío gratis
        if ($freeFrom && $subtotal >= $freeFrom) {
            $cost = 0;
        }
        
        return [
            'type' => 'unlisted',
            'available' => true,
            'cost' => $cost,
            'original_cost' => $this->unlisted_cities_cost,
            'is_free' => $cost == 0 && $this->unlisted_cities_cost > 0,
            'free_from' => $freeFrom,
            'instructions' => $this->unlisted_cities_message,
            'preparation_time' => '3-5dias',
            'preparation_label' => '3-5 días',
            'location_label' => 'Envío Nacional - Otras ciudades',
            'zone_name' => 'Otras ciudades',
        ];
    }

    /**
     * Buscar zona que contenga una ciudad específica
     */
    private function findZoneForCity(string $city): ?SimpleShippingZone
    {
        return $this->activeZones()->get()->first(function ($zone) use ($city) {
            return $zone->hasCity($city);
        });
    }

    /**
     * Normalizar nombre de ciudad
     */
    private function normalizeCity(string $city): string
    {
        return trim(strtolower($city));
    }

    /**
     * Obtener opciones de envío disponibles (formato para checkout)
     */
    public function getAvailableOptions(): array
    {
        $options = [];
        
        // Siempre incluir recogida si está habilitada
        if ($this->pickup_enabled) {
            $options[] = [
                'id' => 'pickup',
                'type' => 'pickup',
                'name' => 'Recogida en Tienda',
                'cost' => 0,
                'formatted_cost' => 'GRATIS',
                'icon' => '🏪',
                'instructions' => $this->pickup_instructions,
                'preparation_time' => $this->pickup_preparation_time,
                'preparation_label' => self::PREPARATION_TIMES[$this->pickup_preparation_time] ?? $this->pickup_preparation_time,
                'is_free' => true,
            ];
        }
        
        // Incluir local si está habilitado  
        if ($this->local_enabled) {
            $options[] = [
                'id' => 'local',
                'type' => 'domicilio', // Para compatibilidad con checkout existente
                'name' => "Envío en {$this->local_city}",
                'cost' => $this->local_cost,
                'formatted_cost' => $this->formatPrice($this->local_cost),
                'icon' => '🚚',
                'instructions' => $this->local_instructions,
                'preparation_time' => $this->local_preparation_time,
                'preparation_label' => self::PREPARATION_TIMES[$this->local_preparation_time] ?? $this->local_preparation_time,
                'free_from' => $this->local_free_from,
                'zones' => [['name' => "Local - {$this->local_city}", 'formatted_cost' => $this->formatPrice($this->local_cost)]]
            ];
        }
        
        // Incluir nacional si está habilitado y tiene zonas
        if ($this->national_enabled && $this->activeZones()->count() > 0) {
            $zones = $this->activeZones()->get()->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'cost' => $zone->cost,
                    'formatted_cost' => $zone->getFormattedCostAttribute(),
                    'estimated_time' => $zone->getDeliveryTimeLabelAttribute(),
                ];
            })->toArray();
            
            $minCost = $this->activeZones()->min('cost');
            
            $options[] = [
                'id' => 'national',
                'type' => 'domicilio', // Para compatibilidad con checkout existente
                'name' => 'Envío Nacional',
                'cost' => $minCost,
                'formatted_cost' => 'Desde ' . $this->formatPrice($minCost),
                'icon' => '📦',
                'instructions' => $this->national_instructions,
                'preparation_time' => '3-5dias',
                'preparation_label' => '3-5 días',
                'free_from' => $this->national_free_from,
                'zones' => $zones
            ];
        }
        
        return $options;
    }

    /**
     * Formatear precio
     */
    public function formatPrice(float $price): string
    {
        return '$' . number_format($price, 0, ',', '.');
    }

}
