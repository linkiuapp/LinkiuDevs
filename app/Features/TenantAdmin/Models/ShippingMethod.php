<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Shared\Models\Store;
use App\Shared\Models\Location;

class ShippingMethod extends Model
{
    protected $fillable = [
        'type',
        'name',
        'is_active',
        'sort_order',
        'instructions',
        'store_id',
        'preparation_time',
        'notification_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notification_enabled' => 'boolean',
    ];

    // Constantes para tipos
    const TYPE_DOMICILIO = 'domicilio';
    const TYPE_PICKUP = 'pickup';

    const TYPES = [
        self::TYPE_DOMICILIO => 'Envío a Domicilio',
        self::TYPE_PICKUP => 'Recoger en Tienda',
    ];

    // Constantes para tiempos de preparación
    const PREPARATION_TIMES = [
        '30min' => '30 minutos',
        '1h' => '1 hora',
        '2h' => '2 horas',
        '4h' => '4 horas',
    ];

    /**
     * Relación con la tienda
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relación con las zonas de envío
     */
    public function zones(): HasMany
    {
        return $this->hasMany(ShippingZone::class);
    }

    /**
     * Relación con las zonas activas
     */
    public function activeZones(): HasMany
    {
        return $this->zones()->where('is_active', true);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Verificar si es método de domicilio
     */
    public function isDomicilio(): bool
    {
        return $this->type === self::TYPE_DOMICILIO;
    }

    /**
     * Verificar si es método de pickup
     */
    public function isPickup(): bool
    {
        return $this->type === self::TYPE_PICKUP;
    }

    /**
     * Obtener la dirección de pickup (sede principal)
     */
    public function getPickupAddress(): ?string
    {
        if (!$this->isPickup()) {
            return null;
        }

        $mainLocation = Location::where('store_id', $this->store_id)
            ->where('is_main', true)
            ->first();

        if ($mainLocation) {
            return $mainLocation->address . ', ' . $mainLocation->city;
        }

        return null;
    }

    /**
     * Obtener horario de pickup (de la sede principal)
     */
    public function getPickupSchedule(): ?array
    {
        if (!$this->isPickup()) {
            return null;
        }

        $mainLocation = Location::where('store_id', $this->store_id)
            ->where('is_main', true)
            ->with('schedules')
            ->first();

        if ($mainLocation) {
            return $mainLocation->schedules->toArray();
        }

        return null;
    }

    /**
     * Obtener el ícono según el tipo
     */
    public function getIcon(): string
    {
        return match($this->type) {
            self::TYPE_DOMICILIO => '🚚',
            self::TYPE_PICKUP => '🏪',
            default => '📦'
        };
    }

    /**
     * Obtener el label del tipo
     */
    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Obtener el label del tiempo de preparación
     */
    public function getPreparationTimeLabel(): string
    {
        return self::PREPARATION_TIMES[$this->preparation_time] ?? $this->preparation_time;
    }

    /**
     * Verificar si tiene zonas activas (solo para domicilio)
     */
    public function hasActiveZones(): bool
    {
        if (!$this->isDomicilio()) {
            return true; // Pickup siempre está disponible si está activo
        }

        return $this->activeZones()->exists();
    }

    /**
     * Obtener el costo mínimo de las zonas
     */
    public function getMinimumCost(): ?float
    {
        if (!$this->isDomicilio()) {
            return 0; // Pickup es gratis
        }

        return $this->activeZones()->min('cost');
    }

    /**
     * Obtener información de envío gratis
     */
    public function getFreeShippingInfo(): ?array
    {
        if (!$this->isDomicilio()) {
            return null;
        }

        $minFreeShipping = $this->activeZones()
            ->whereNotNull('free_shipping_from')
            ->min('free_shipping_from');

        if ($minFreeShipping) {
            return [
                'enabled' => true,
                'from_amount' => $minFreeShipping,
                'message' => "Envío GRATIS en compras desde $" . number_format($minFreeShipping, 0, ',', '.')
            ];
        }

        return null;
    }
} 