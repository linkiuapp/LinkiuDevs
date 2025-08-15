<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'order_status_history';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'changed_by',
        'user_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Disable updated_at timestamp (only created_at is used)
     */
    const UPDATED_AT = null;

    /**
     * Get the order that this history belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who made the change (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Shared\Models\User::class);
    }

    /**
     * Create a new status history entry
     */
    public static function createEntry(
        int $orderId,
        string $newStatus,
        ?string $oldStatus = null,
        ?string $notes = null,
        ?int $userId = null
    ): self {
        $changedBy = 'Sistema';
        
        if ($userId) {
            $user = \App\Shared\Models\User::find($userId);
            $changedBy = $user ? $user->name : 'Usuario';
        }

        return static::create([
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'user_id' => $userId,
            'notes' => $notes,
        ]);
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->new_status) {
            'pending' => 'bg-warning-300',
            'confirmed' => 'bg-info-200',
            'preparing' => 'bg-secondary-200',
            'shipped' => 'bg-primary-200',
            'delivered' => 'bg-success-300',
            'cancelled' => 'bg-error-200',
            default => 'bg-black-200'
        };
    }

    /**
     * Get status change description
     */
    public function getStatusChangeAttribute(): string
    {
        $labels = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado'
        ];

        $newLabel = $labels[$this->new_status] ?? $this->new_status;
        
        if ($this->old_status) {
            $oldLabel = $labels[$this->old_status] ?? $this->old_status;
            return "Estado cambiado de {$oldLabel} a {$newLabel}";
        }
        
        return "Pedido creado con estado {$newLabel}";
    }
}
