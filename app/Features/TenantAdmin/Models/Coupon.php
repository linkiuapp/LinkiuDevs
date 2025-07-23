<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Shared\Models\Store;
use App\Shared\Models\Product;
use App\Shared\Models\Category;
use App\Shared\Models\Order;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_purchase_amount',
        'max_uses',
        'current_uses',
        'uses_per_session',
        'start_date',
        'end_date',
        'days_of_week',
        'start_time',
        'end_time',
        'is_active',
        'is_public',
        'is_automatic',
        'store_id',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'days_of_week' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_automatic' => 'boolean',
    ];

    // Constantes
    const TYPE_GLOBAL = 'global';
    const TYPE_CATEGORIES = 'categories';
    const TYPE_PRODUCTS = 'products';

    const DISCOUNT_TYPE_FIXED = 'fixed';
    const DISCOUNT_TYPE_PERCENTAGE = 'percentage';

    const TYPES = [
        self::TYPE_GLOBAL => 'Global',
        self::TYPE_CATEGORIES => 'Categorías específicas',
        self::TYPE_PRODUCTS => 'Productos específicos',
    ];

    const DISCOUNT_TYPES = [
        self::DISCOUNT_TYPE_FIXED => 'Valor Fijo',
        self::DISCOUNT_TYPE_PERCENTAGE => 'Porcentaje',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar código automáticamente si no se proporciona
        static::creating(function ($coupon) {
            if (empty($coupon->code)) {
                $coupon->code = static::generateCouponCode($coupon->store_id);
            }
            // Asegurar que el código sea único para la tienda
            $coupon->code = strtoupper($coupon->code);
        });
    }

    /**
     * Relaciones
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(CouponUsageLog::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeValidNow($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('max_uses')->orWhereRaw('current_uses < max_uses');
        });
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getDiscountTypeLabelAttribute(): string
    {
        return self::DISCOUNT_TYPES[$this->discount_type] ?? $this->discount_type;
    }

    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === self::DISCOUNT_TYPE_PERCENTAGE) {
            return $this->discount_value . '%';
        }
        return '$' . number_format($this->discount_value, 0, ',', '.');
    }

    public function getUsagePercentageAttribute(): float
    {
        if (!$this->max_uses) {
            return 0;
        }
        return round(($this->current_uses / $this->max_uses) * 100, 1);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getIsValidTodayAttribute(): bool
    {
        $now = now();
        
        // Verificar fechas
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }
        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }
        
        // Verificar días de la semana
        if ($this->days_of_week && is_array($this->days_of_week)) {
            $currentDay = strtolower($now->format('l')); // monday, tuesday, etc.
            if (!in_array($currentDay, $this->days_of_week)) {
                return false;
            }
        }
        
        // Verificar horarios
        if ($this->start_time && $this->end_time) {
            $currentTime = $now->format('H:i:s');
            $startTime = $this->start_time ? $this->start_time->format('H:i:s') : '00:00:00';
            $endTime = $this->end_time ? $this->end_time->format('H:i:s') : '23:59:59';
            
            if ($currentTime < $startTime || $currentTime > $endTime) {
                return false;
            }
        }
        
        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactivo';
        }
        if ($this->is_expired) {
            return 'Expirado';
        }
        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return 'Agotado';
        }
        if (!$this->is_valid_today) {
            return 'Fuera de horario';
        }
        return 'Activo';
    }

    public function getStatusColorAttribute(): string
    {
        if (!$this->is_active || $this->is_expired) {
            return 'error';
        }
        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return 'warning';
        }
        if (!$this->is_valid_today) {
            return 'info';
        }
        return 'success';
    }

    /**
     * Métodos de negocio
     */
    public function canBeUsed(): bool
    {
        return $this->is_active && 
               !$this->is_expired && 
               $this->is_valid_today &&
               ($this->max_uses === null || $this->current_uses < $this->max_uses);
    }

    public function canBeUsedBySession(string $sessionId): bool
    {
        if (!$this->canBeUsed()) {
            return false;
        }

        if ($this->uses_per_session === null) {
            return true;
        }

        $sessionUses = $this->usageLogs()->where('session_id', $sessionId)->count();
        return $sessionUses < $this->uses_per_session;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
            // Para descuentos fijos, aplicar hasta el subtotal (no puede ser mayor)
            return min($this->discount_value, $subtotal);
        }

        // Para porcentajes
        $discount = ($subtotal * $this->discount_value) / 100;
        
        // Aplicar límite máximo si existe
        if ($this->max_discount_amount) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return $discount;
    }

    public function incrementUsage(): void
    {
        $this->increment('current_uses');
    }

    public function decrementUsage(): void
    {
        $this->decrement('current_uses');
    }

    /**
     * Validar si el cupón es aplicable a una orden
     */
    public function isApplicableToOrder(array $orderItems): bool
    {
        if ($this->type === self::TYPE_GLOBAL) {
            return true;
        }

        if ($this->type === self::TYPE_CATEGORIES) {
            $categoryIds = $this->categories->pluck('id')->toArray();
            foreach ($orderItems as $item) {
                if (in_array($item['product']['category_id'], $categoryIds)) {
                    return true;
                }
            }
            return false;
        }

        if ($this->type === self::TYPE_PRODUCTS) {
            $productIds = $this->products->pluck('id')->toArray();
            foreach ($orderItems as $item) {
                if (in_array($item['product_id'], $productIds)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Calcular subtotal aplicable según el tipo de cupón
     */
    public function getApplicableSubtotal(array $orderItems): float
    {
        if ($this->type === self::TYPE_GLOBAL) {
            return collect($orderItems)->sum('item_total');
        }

        $applicableTotal = 0;

        if ($this->type === self::TYPE_CATEGORIES) {
            $categoryIds = $this->categories->pluck('id')->toArray();
            foreach ($orderItems as $item) {
                if (in_array($item['product']['category_id'], $categoryIds)) {
                    $applicableTotal += $item['item_total'];
                }
            }
        }

        if ($this->type === self::TYPE_PRODUCTS) {
            $productIds = $this->products->pluck('id')->toArray();
            foreach ($orderItems as $item) {
                if (in_array($item['product_id'], $productIds)) {
                    $applicableTotal += $item['item_total'];
                }
            }
        }

        return $applicableTotal;
    }

    /**
     * Generar código de cupón aleatorio
     */
    public static function generateCouponCode(int $storeId): string
    {
        $store = Store::find($storeId);
        $prefix = $store ? strtoupper(substr($store->name, 0, 3)) : 'LNK';
        
        do {
            $randomPart = strtoupper(substr(md5(uniqid()), 0, 6));
            $code = $prefix . '-' . $randomPart;
            
            $exists = static::where('code', $code)
                          ->where('store_id', $storeId)
                          ->exists();
        } while ($exists);

        return $code;
    }

    /**
     * Duplicar cupón
     */
    public function duplicate(): self
    {
        $newCoupon = $this->replicate();
        $newCoupon->code = static::generateCouponCode($this->store_id);
        $newCoupon->name = $this->name . ' (Copia)';
        $newCoupon->current_uses = 0;
        $newCoupon->is_active = false; // Crear inactivo por defecto
        $newCoupon->save();

        // Duplicar relaciones
        if ($this->type === self::TYPE_CATEGORIES) {
            $newCoupon->categories()->attach($this->categories->pluck('id'));
        }

        if ($this->type === self::TYPE_PRODUCTS) {
            $newCoupon->products()->attach($this->products->pluck('id'));
        }

        return $newCoupon;
    }
} 