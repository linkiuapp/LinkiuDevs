<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Models\Store;
use Carbon\Carbon;

class ShippingZone extends Model
{
    protected $fillable = [
        'shipping_method_id',
        'name',
        'description',
        'cost',
        'free_shipping_from',
        'estimated_time',
        'delivery_days',
        'start_time',
        'end_time',
        'is_active',
        'store_id',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'free_shipping_from' => 'decimal:2',
        'delivery_days' => 'array',
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Tiempos estimados disponibles
    const ESTIMATED_TIMES = [
        '1-2h' => '1-2 horas',
        '2-4h' => '2-4 horas',
        '4-8h' => '4-8 horas',
        'mismo-dia' => 'Mismo día',
        '24h' => '24 horas',
    ];

    // Días de la semana
    const DAYS = [
        'L' => 'Lunes',
        'M' => 'Martes',
        'X' => 'Miércoles',
        'J' => 'Jueves',
        'V' => 'Viernes',
        'S' => 'Sábado',
        'D' => 'Domingo',
    ];

    /**
     * Relación con el método de envío
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * Relación con la tienda
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
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
        return $query->orderBy('cost')->orderBy('name');
    }

    /**
     * Verificar si aplica envío gratis para un monto
     */
    public function hasFreeShipping(float $amount): bool
    {
        if (!$this->free_shipping_from) {
            return false;
        }

        return $amount >= $this->free_shipping_from;
    }

    /**
     * Obtener el costo final considerando envío gratis
     */
    public function getFinalCost(float $subtotal): float
    {
        if ($this->hasFreeShipping($subtotal)) {
            return 0;
        }

        return $this->cost;
    }

    /**
     * Verificar si entrega hoy
     */
    public function deliversToday(): bool
    {
        $today = strtoupper(substr(Carbon::now()->locale('es')->dayName, 0, 1));
        
        // Mapeo especial para miércoles
        if (Carbon::now()->dayOfWeek === 3) {
            $today = 'X';
        }

        return $this->delivery_days[$today] ?? false;
    }

    /**
     * Verificar si está en horario de entrega
     */
    public function isInDeliveryHours(): bool
    {
        $now = Carbon::now();
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return $now->between($startTime, $endTime);
    }

    /**
     * Verificar si está disponible ahora
     */
    public function isAvailableNow(): bool
    {
        return $this->is_active && $this->deliversToday() && $this->isInDeliveryHours();
    }

    /**
     * Obtener días de entrega formateados
     */
    public function getDeliveryDaysFormatted(): string
    {
        $activeDays = [];
        
        foreach ($this->delivery_days as $day => $active) {
            if ($active) {
                $activeDays[] = self::DAYS[$day] ?? $day;
            }
        }

        if (count($activeDays) === 7) {
            return 'Todos los días';
        }

        if (count($activeDays) === 0) {
            return 'No disponible';
        }

        return implode(', ', $activeDays);
    }

    /**
     * Obtener horario formateado
     */
    public function getScheduleFormatted(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A') . ' - ' . 
               Carbon::parse($this->end_time)->format('g:i A');
    }

    /**
     * Obtener mensaje de envío gratis
     */
    public function getFreeShippingMessage(): ?string
    {
        if (!$this->free_shipping_from) {
            return null;
        }

        return "Envío GRATIS en compras desde $" . number_format($this->free_shipping_from, 0, ',', '.');
    }

    /**
     * Obtener label del tiempo estimado
     */
    public function getEstimatedTimeLabel(): string
    {
        return self::ESTIMATED_TIMES[$this->estimated_time] ?? $this->estimated_time;
    }

    /**
     * Clonar zona
     */
    public function duplicate(string $newName): self
    {
        $clone = $this->replicate();
        $clone->name = $newName;
        $clone->is_active = false;
        $clone->save();

        return $clone;
    }
} 