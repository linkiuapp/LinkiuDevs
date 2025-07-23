<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'status',
        'customer_name',
        'customer_phone',
        'customer_address',
        'department',
        'city',
        'delivery_type',
        'shipping_cost',
        'payment_method',
        'payment_proof_path',
        'subtotal',
        'coupon_discount',
        'total',
        'notes',
        'store_id'
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Constantes de estado
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_CONFIRMED => 'Confirmado',
        self::STATUS_PREPARING => 'Preparando',
        self::STATUS_SHIPPED => 'Enviado',
        self::STATUS_DELIVERED => 'Entregado',
        self::STATUS_CANCELLED => 'Cancelado'
    ];

    // Constantes de tipo de entrega
    const DELIVERY_TYPE_DOMICILIO = 'domicilio';
    const DELIVERY_TYPE_PICKUP = 'pickup';

    const DELIVERY_TYPES = [
        self::DELIVERY_TYPE_DOMICILIO => 'Envío a Domicilio',
        self::DELIVERY_TYPE_PICKUP => 'Recoger en Tienda'
    ];

    // Constantes de método de pago
    const PAYMENT_METHOD_TRANSFERENCIA = 'transferencia';
    const PAYMENT_METHOD_CONTRA_ENTREGA = 'contra_entrega';
    const PAYMENT_METHOD_EFECTIVO = 'efectivo';

    const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_TRANSFERENCIA => 'Transferencia Bancaria',
        self::PAYMENT_METHOD_CONTRA_ENTREGA => 'Pago Contra Entrega',
        self::PAYMENT_METHOD_EFECTIVO => 'Efectivo'
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar order_number automáticamente al crear
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber($order->store_id);
            }
        });

        // Registrar cambio de estado en el historial
        static::created(function ($order) {
            $order->recordStatusChange(null, $order->status, 'Sistema', null, 'Pedido creado');
        });

        static::updating(function ($order) {
            if ($order->isDirty('status')) {
                $oldStatus = $order->getOriginal('status');
                $order->recordStatusChange($oldStatus, $order->status, auth()->user()->name ?? 'Sistema', auth()->id());
            }
        });
    }

    /**
     * Relaciones
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'asc');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(\App\Features\TenantAdmin\Models\Coupon::class);
    }

    /**
     * Scopes
     */
    public function scopeByStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDeliveryType(Builder $query, string $type): Builder
    {
        return $query->where('delivery_type', $type);
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
              ->orWhere('customer_name', 'like', "%{$search}%")
              ->orWhere('customer_phone', 'like', "%{$search}%");
        });
    }

    /**
     * Generar número de pedido único por tienda
     */
    public static function generateOrderNumber(int $storeId): string
    {
        $store = Store::find($storeId);
        if (!$store) {
            throw new \Exception('Tienda no encontrada');
        }

        // Obtener iniciales de la tienda (3 caracteres)
        $storeInitials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $store->name), 0, 3));
        if (strlen($storeInitials) < 3) {
            $storeInitials = str_pad($storeInitials, 3, 'X');
        }

        // Fecha actual en formato ymd
        $date = now()->format('ymd');

        // Obtener el último número secuencial del día para esta tienda
        $lastOrder = static::where('store_id', $storeId)
            ->where('order_number', 'like', $storeInitials . $date . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        $sequential = 1;
        if ($lastOrder) {
            $lastSequential = (int) substr($lastOrder->order_number, -3);
            $sequential = $lastSequential + 1;
        }

        return $storeInitials . $date . str_pad($sequential, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Registrar cambio de estado en el historial
     */
    public function recordStatusChange(?string $oldStatus, string $newStatus, string $changedBy, ?int $userId = null, ?string $notes = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $this->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'user_id' => $userId,
            'notes' => $notes,
            'created_at' => now()
        ]);
    }

    /**
     * Métodos de estado
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPreparing(): bool
    {
        return $this->status === self::STATUS_PREPARING;
    }

    public function isShipped(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }

    /**
     * Métodos de cálculo
     */
    public function calculateSubtotal(): float
    {
        return $this->items->sum('item_total');
    }

    public function calculateTotal(): float
    {
        return $this->subtotal + $this->shipping_cost - $this->coupon_discount;
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->total = $this->calculateTotal();
        $this->save();
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_PREPARING => 'primary',
            self::STATUS_SHIPPED => 'secondary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'error',
            default => 'black'
        };
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return self::DELIVERY_TYPES[$this->delivery_type] ?? $this->delivery_type;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedShippingCostAttribute(): string
    {
        return '$' . number_format($this->shipping_cost, 0, ',', '.');
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 0, ',', '.');
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof_path) {
            return null;
        }

        return asset('storage/orders/payment-proofs/' . $this->payment_proof_path);
    }

    /**
     * Get items count accessor
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Métodos de utilidad
     */
    public function hasPaymentProof(): bool
    {
        return !empty($this->payment_proof_path);
    }

    public function requiresPaymentProof(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_TRANSFERENCIA;
    }
} 