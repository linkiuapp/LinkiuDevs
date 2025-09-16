<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimpleShippingZone extends Model
{
    protected $fillable = [
        'simple_shipping_id',
        'name',
        'cost',
        'delivery_time',
        'cities',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'cities' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relación con la configuración de envío
     */
    public function simpleShipping(): BelongsTo
    {
        return $this->belongsTo(SimpleShipping::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Verificar si una ciudad está en esta zona
     */
    public function hasCity(string $city): bool
    {
        if (!$this->cities || !is_array($this->cities)) {
            return false;
        }

        $city = $this->normalizeCity($city);
        
        foreach ($this->cities as $zoneCity) {
            if ($this->normalizeCity($zoneCity) === $city) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalizar nombre de ciudad para comparación
     */
    private function normalizeCity(string $city): string
    {
        return trim(strtolower($city));
    }

    /**
     * Obtener ciudades como string separado por comas
     */
    public function getCitiesStringAttribute(): string
    {
        if (!$this->cities || !is_array($this->cities)) {
            return '';
        }

        return implode(', ', $this->cities);
    }

    /**
     * Formatear precio
     */
    public function getFormattedCostAttribute(): string
    {
        return '$' . number_format($this->cost, 0, ',', '.');
    }

    /**
     * Obtener label del tiempo de entrega
     */
    public function getDeliveryTimeLabelAttribute(): string
    {
        $times = SimpleShipping::DELIVERY_TIMES;
        return $times[$this->delivery_time] ?? $this->delivery_time;
    }
}

