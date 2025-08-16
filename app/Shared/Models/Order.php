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
        'payment_method_id',
        'cash_amount',
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
        'cash_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Constantes de estado
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_CONFIRMED => 'Confirmado',
        self::STATUS_PREPARING => 'Preparando',
        self::STATUS_READY_FOR_PICKUP => 'Listo para Recoger',
        self::STATUS_SHIPPED => 'Enviado',
        self::STATUS_OUT_FOR_DELIVERY => 'En Ruta de Entrega',
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
            OrderStatusHistory::createEntry(
                $order->id,
                $order->status,
                null,
                'Pedido creado',
                null
            );
        });

        static::updating(function ($order) {
            if ($order->isDirty('status')) {
                $previousStatus = $order->getOriginal('status');
                OrderStatusHistory::createEntry(
                    $order->id,
                    $order->status,
                    $previousStatus,
                    null,
                    auth()->id()
                );
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
     * Update order status with history tracking and broadcasting
     */
    public function updateStatus(
        string $newStatus, 
        ?string $comment = null, 
        ?\App\Models\User $changedBy = null
    ): void {
        $previousStatus = $this->status;
        
        // Update the order status
        $this->update(['status' => $newStatus]);
        
        // Create history entry
        OrderStatusHistory::createEntry(
            $this->id,
            $newStatus,
            $previousStatus,
            $comment,
            $changedBy?->id
        );
        
        // Broadcast status change (will be implemented later)
        // broadcast(new OrderStatusUpdated($this, $previousStatus, $newStatus));
    }

    /**
     * Get current status
     */
    public function getCurrentStatus(): string
    {
        return $this->status;
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    /**
     * Get estimated delivery time
     */
    public function getEstimatedDeliveryTime(): ?Carbon
    {
        if ($this->status === self::STATUS_DELIVERED || $this->status === self::STATUS_CANCELLED) {
            return null;
        }

        $baseTime = now();
        
        return match($this->status) {
            self::STATUS_PENDING => $baseTime->addMinutes(15),
            self::STATUS_CONFIRMED => $baseTime->addMinutes(45),
            self::STATUS_PREPARING => $this->delivery_type === 'pickup' 
                ? $baseTime->addMinutes(60) 
                : $baseTime->addMinutes(90),
            self::STATUS_READY_FOR_PICKUP => $baseTime, // Available now
            self::STATUS_SHIPPED => $baseTime->addHours(2),
            self::STATUS_OUT_FOR_DELIVERY => $baseTime->addMinutes(30),
            default => null
        };
    }

    /**
     * Get next possible statuses
     */
    public function getNextPossibleStatuses(): array
    {
        return $this->status->getNextPossibleStatuses($this->delivery_type);
    }

    /**
     * Get status timeline for display
     */
    public function getStatusTimeline(): array
    {
        $history = $this->statusHistory()
            ->orderBy('created_at', 'asc')
            ->get();

        $timeline = [];
        $currentStatus = $this->status;
        
        foreach ($history as $entry) {
            $timelineData = $entry->timeline_data;
            $timelineData['is_current'] = $entry->status === $currentStatus;
            $timeline[] = $timelineData;
        }

        return $timeline;
    }

    /**
     * Get status display information
     */
    public function getStatusDisplayAttribute(): array
    {
        $status = (string) $this->status;
        return [
            'value' => $status,
            'name' => self::STATUSES[$status] ?? ucfirst($status),
            'icon' => $this->status_icon,
            'color' => $this->status_color_class,
            'description' => '',
            'estimated_time' => optional($this->getEstimatedDeliveryTime())->toDateTimeString(),
        ];
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

    public function isReadyForPickup(): bool
    {
        return $this->status === self::STATUS_READY_FOR_PICKUP;
    }

    public function isShipped(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function isOutForDelivery(): bool
    {
        return $this->status === self::STATUS_OUT_FOR_DELIVERY;
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

    public function isFinalStatus(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
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
        // Evitar error 500: $this->status es un string desde DB
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusColorAttribute(): string
    {
        // Compatibilidad con código previo que esperaba un método getColor() en un enum
        return $this->getStatusColorClassAttribute();
    }

    public function getStatusIconAttribute(): string
    {
        // Iconos por estado (Solar Icons) – opcional, seguro por defecto
        return match ($this->status) {
            self::STATUS_PENDING => 'clock',
            self::STATUS_CONFIRMED => 'check-circle',
            self::STATUS_PREPARING => 'gear',
            self::STATUS_READY_FOR_PICKUP => 'box',
            self::STATUS_SHIPPED => 'truck',
            self::STATUS_OUT_FOR_DELIVERY => 'navigation',
            self::STATUS_DELIVERED => 'check-circle',
            self::STATUS_CANCELLED => 'close-circle',
            default => 'dot',
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

    /**
     * Tailwind color class for status badges used in views
     */
    public function getStatusColorClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning-300 text-black-300',
            self::STATUS_CONFIRMED => 'bg-info-300 text-accent-300',
            self::STATUS_PREPARING => 'bg-secondary-300 text-accent-300',
            self::STATUS_READY_FOR_PICKUP => 'bg-primary-300 text-accent-300',
            self::STATUS_SHIPPED => 'bg-primary-300 text-accent-300',
            self::STATUS_OUT_FOR_DELIVERY => 'bg-accent-300 text-accent-300',
            self::STATUS_DELIVERED => 'bg-success-300 text-accent-300',
            self::STATUS_CANCELLED => 'bg-error-300 text-accent-300',
            default => 'bg-black-50 text-black-300',
        };
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