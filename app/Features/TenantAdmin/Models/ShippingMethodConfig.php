<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Models\Store;

class ShippingMethodConfig extends Model
{
    protected $table = 'shipping_method_config';

    protected $fillable = [
        'store_id',
        'default_method_id',
        'min_active_methods',
    ];

    protected $casts = [
        'min_active_methods' => 'integer',
    ];

    /**
     * Relación con la tienda
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relación con el método por defecto
     */
    public function defaultMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'default_method_id');
    }

    /**
     * Obtener o crear configuración para una tienda
     */
    public static function getOrCreateForStore(int $storeId): self
    {
        return self::firstOrCreate(
            ['store_id' => $storeId],
            [
                'default_method_id' => null,
                'min_active_methods' => 1,
            ]
        );
    }

    /**
     * Verificar si se puede desactivar un método
     */
    public function canDeactivateMethod(int $methodId): bool
    {
        $activeMethodsCount = ShippingMethod::where('store_id', $this->store_id)
            ->where('is_active', true)
            ->count();

        // Si es el método que queremos desactivar y solo queda ese activo
        if ($activeMethodsCount <= $this->min_active_methods) {
            $isTheOnlyActive = ShippingMethod::where('store_id', $this->store_id)
                ->where('is_active', true)
                ->where('id', $methodId)
                ->exists();

            return !$isTheOnlyActive;
        }

        return true;
    }

    /**
     * Actualizar método por defecto si el actual se desactiva
     */
    public function updateDefaultIfNeeded(int $deactivatedMethodId): void
    {
        if ($this->default_method_id === $deactivatedMethodId) {
            // Buscar otro método activo
            $newDefault = ShippingMethod::where('store_id', $this->store_id)
                ->where('is_active', true)
                ->where('id', '!=', $deactivatedMethodId)
                ->first();

            $this->default_method_id = $newDefault?->id;
            $this->save();
        }
    }
} 