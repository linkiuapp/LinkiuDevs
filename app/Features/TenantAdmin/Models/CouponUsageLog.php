<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Models\Order;
use App\Shared\Models\User;

class CouponUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'order_id',
        'session_id',
        'user_id',
        'discount_applied',
        'order_subtotal',
        'metadata',
    ];

    protected $casts = [
        'discount_applied' => 'decimal:2',
        'order_subtotal' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Relaciones
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByCoupon($query, $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Accessors
     */
    public function getFormattedDiscountAttribute(): string
    {
        return '$' . number_format($this->discount_applied, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->order_subtotal, 0, ',', '.');
    }

    public function getDiscountPercentageAttribute(): float
    {
        if ($this->order_subtotal <= 0) {
            return 0;
        }
        return round(($this->discount_applied / $this->order_subtotal) * 100, 2);
    }

    /**
     * Crear log de uso
     */
    public static function createFromOrder(Coupon $coupon, Order $order, string $sessionId, float $discountApplied): self
    {
        return static::create([
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'session_id' => $sessionId,
            'user_id' => null, // Para futuro uso
            'discount_applied' => $discountApplied,
            'order_subtotal' => $order->subtotal,
            'metadata' => [
                'coupon_code' => $coupon->code,
                'coupon_type' => $coupon->type,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
} 