<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Features\TenantAdmin\Models\Product;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_price',
        'quantity',
        'item_total',
        'variant_details'
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'item_total' => 'decimal:2',
        'quantity' => 'integer',
        'variant_details' => 'array'
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Calcular total automáticamente al crear/actualizar
        static::saving(function ($orderItem) {
            $orderItem->item_total = $orderItem->calculateItemTotal();
        });

        // Recalcular totales del pedido después de cambios
        static::saved(function ($orderItem) {
            $orderItem->order->recalculateTotals();
        });

        static::deleted(function ($orderItem) {
            $orderItem->order->recalculateTotals();
        });
    }

    /**
     * Relaciones
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Métodos de cálculo
     */
    public function calculateItemTotal(): float
    {
        $basePrice = $this->product_price;
        
        // Agregar modificadores de precio por variantes si existen
        if ($this->variant_details && isset($this->variant_details['precio_modificador'])) {
            $basePrice += $this->variant_details['precio_modificador'];
        }

        return $basePrice * $this->quantity;
    }

    /**
     * Accessors
     */
    public function getFormattedProductPriceAttribute(): string
    {
        return '$' . number_format($this->product_price, 0, ',', '.');
    }

    public function getFormattedItemTotalAttribute(): string
    {
        return '$' . number_format($this->item_total, 0, ',', '.');
    }

    public function getFinalPriceAttribute(): float
    {
        $basePrice = $this->product_price;
        
        if ($this->variant_details && isset($this->variant_details['precio_modificador'])) {
            $basePrice += $this->variant_details['precio_modificador'];
        }

        return $basePrice;
    }

    public function getFormattedFinalPriceAttribute(): string
    {
        return '$' . number_format($this->final_price, 0, ',', '.');
    }

    /**
     * Métodos de variantes
     */
    public function hasVariants(): bool
    {
        return !empty($this->variant_details) && count($this->variant_details) > 0;
    }

    public function getVariantDisplayAttribute(): string
    {
        if (!$this->hasVariants()) {
            return '';
        }

        $display = [];
        foreach ($this->variant_details as $key => $value) {
            // Omitir el precio_modificador del display
            if ($key !== 'precio_modificador') {
                $display[] = ucfirst($key) . ': ' . $value;
            }
        }

        return implode(', ', $display);
    }

    public function getVariantSummaryAttribute(): string
    {
        if (!$this->hasVariants()) {
            return 'Sin variantes';
        }

        return $this->variant_display;
    }

    /**
     * Métodos de utilidad
     */
    public function duplicate(): self
    {
        $newItem = $this->replicate();
        $newItem->save();
        
        return $newItem;
    }

    /**
     * Get formatted variants for display
     */
    public function getFormattedVariantsAttribute(): string
    {
        if (!$this->variant_details || !is_array($this->variant_details)) {
            return '';
        }

        $display = [];
        foreach ($this->variant_details as $key => $value) {
            if ($key !== 'precio_modificador') {
                $display[] = ucfirst($key) . ': ' . $value;
            }
        }

        return implode(', ', $display);
    }

    /**
     * Get unit price including variant modifiers
     */
    public function getUnitPriceAttribute(): float
    {
        $price = $this->product_price;
        
        if ($this->variant_details && isset($this->variant_details['precio_modificador'])) {
            $price += $this->variant_details['precio_modificador'];
        }
        
        return $price;
    }

    /**
     * Crear item desde producto
     */
    public static function createFromProduct(Product $product, int $quantity = 1, ?array $variantDetails = null): array
    {
        // Calcular precio final considerando variantes
        $basePrice = $product->price;
        $priceModifier = 0;

        if ($variantDetails) {
            // Si hay variantes seleccionadas, buscar modificadores de precio
            foreach ($variantDetails as $key => $value) {
                // Buscar en las variantes del producto si hay modificadores
                $variant = $product->variants()
                    ->where('variant_options', 'like', "%\"$key\":\"%$value%")
                    ->first();
                
                if ($variant) {
                    $priceModifier += $variant->price_modifier;
                }
            }
            
            // Agregar el modificador al array de detalles
            if ($priceModifier != 0) {
                $variantDetails['precio_modificador'] = $priceModifier;
            }
        }

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $basePrice,
            'quantity' => $quantity,
            'variant_details' => $variantDetails,
            'item_total' => ($basePrice + $priceModifier) * $quantity
        ];
    }
} 