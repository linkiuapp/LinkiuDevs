<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'price_modifier',
        'is_active',
        'variant_options'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_modifier' => 'decimal:2',
        'variant_options' => 'array'
    ];

    /**
     * Relación con el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope para obtener solo variantes activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener solo variantes inactivas
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Obtener el precio final de la variante
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->product->price + $this->price_modifier;
    }

    /**
     * Obtener el precio final formateado
     */
    public function getFormattedFinalPriceAttribute(): string
    {
        return '$' . number_format($this->final_price, 0, ',', '.');
    }

    /**
     * Obtener el modificador de precio formateado
     */
    public function getFormattedPriceModifierAttribute(): string
    {
        $modifier = $this->price_modifier;
        
        if ($modifier > 0) {
            return '+$' . number_format($modifier, 0, ',', '.');
        } elseif ($modifier < 0) {
            return '-$' . number_format(abs($modifier), 0, ',', '.');
        }
        
        return '$0';
    }

    /**
     * Obtener las opciones de variante como texto legible
     */
    public function getVariantOptionsTextAttribute(): string
    {
        if (empty($this->variant_options)) {
            return '';
        }

        $options = [];
        foreach ($this->variant_options as $variableId => $optionValue) {
            // Aquí podrías buscar el nombre de la variable y opción en la base de datos
            // Por simplicidad, mostramos el ID y valor
            $options[] = "Variable {$variableId}: {$optionValue}";
        }

        return implode(', ', $options);
    }

    /**
     * Verificar si la variante está activa
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Activar la variante
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Desactivar la variante
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Obtener una opción específica de variante
     */
    public function getVariantOption(string $variableId): ?string
    {
        return $this->variant_options[$variableId] ?? null;
    }

    /**
     * Establecer una opción de variante
     */
    public function setVariantOption(string $variableId, string $optionValue): bool
    {
        $options = $this->variant_options ?? [];
        $options[$variableId] = $optionValue;
        
        return $this->update(['variant_options' => $options]);
    }

    /**
     * Generar SKU único para la variante
     */
    public static function generateUniqueSku(Product $product, array $variantOptions): string
    {
        $baseSku = $product->sku;
        
        // Crear sufijo basado en las opciones de variante
        $suffix = '';
        foreach ($variantOptions as $variableId => $optionValue) {
            $suffix .= '-' . strtoupper(substr($optionValue, 0, 3));
        }
        
        $proposedSku = $baseSku . $suffix;
        
        // Verificar si el SKU ya existe
        $counter = 1;
        $finalSku = $proposedSku;
        
        while (self::where('sku', $finalSku)->exists()) {
            $finalSku = $proposedSku . '-' . $counter;
            $counter++;
        }
        
        return $finalSku;
    }

    /**
     * Duplicar la variante para otro producto
     */
    public function duplicate(Product $newProduct): self
    {
        $newVariant = $this->replicate();
        $newVariant->product_id = $newProduct->id;
        $newVariant->sku = self::generateUniqueSku($newProduct, $this->variant_options);
        $newVariant->save();
        
        return $newVariant;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar SKU automáticamente si no se proporciona
        static::creating(function ($variant) {
            if (empty($variant->sku)) {
                $variant->sku = self::generateUniqueSku($variant->product, $variant->variant_options);
            }
        });
    }
} 