<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OrderStatusHistory extends Model
{
    use HasFactory;

    // Desactivar timestamps automáticos ya que solo usamos created_at
    public $timestamps = false;

    protected $table = 'order_status_history';

    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'changed_by',
        'user_id',
        'notes',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Asignar created_at automáticamente si no se proporciona
        static::creating(function ($history) {
            if (!$history->created_at) {
                $history->created_at = now();
            }
        });
    }

    /**
     * Relaciones
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessors
     */
    public function getOldStatusLabelAttribute(): ?string
    {
        if (!$this->old_status) {
            return null;
        }

        return Order::STATUSES[$this->old_status] ?? $this->old_status;
    }

    public function getNewStatusLabelAttribute(): string
    {
        return Order::STATUSES[$this->new_status] ?? $this->new_status;
    }

    public function getStatusChangeAttribute(): string
    {
        if (!$this->old_status) {
            return "Pedido creado con estado: {$this->new_status_label}";
        }

        return "Cambió de {$this->old_status_label} a {$this->new_status_label}";
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getRelativeDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Métodos de utilidad
     */
    public function isCreation(): bool
    {
        return $this->old_status === null;
    }

    public function isStatusChange(): bool
    {
        return $this->old_status !== null && $this->old_status !== $this->new_status;
    }

    public function isCancellation(): bool
    {
        return $this->new_status === Order::STATUS_CANCELLED;
    }

    public function isCompletion(): bool
    {
        return $this->new_status === Order::STATUS_DELIVERED;
    }

    /**
     * Scopes
     */
    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('new_status', $status);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Crear registro de historial
     */
    public static function recordChange(
        int $orderId,
        ?string $oldStatus,
        string $newStatus,
        string $changedBy,
        ?int $userId = null,
        ?string $notes = null
    ): self {
        return static::create([
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'user_id' => $userId,
            'notes' => $notes,
            'created_at' => now()
        ]);
    }

    /**
     * Obtener estadísticas de cambios
     */
    public static function getStatsForOrder(int $orderId): array
    {
        $history = static::where('order_id', $orderId)->ordered()->get();

        return [
            'total_changes' => $history->count(),
            'created_at' => $history->first()?->created_at,
            'last_updated' => $history->last()?->created_at,
            'time_to_delivery' => $history->where('new_status', Order::STATUS_DELIVERED)->first()?->created_at?->diffInHours($history->first()->created_at),
            'unique_users' => $history->where('user_id', '!=', null)->pluck('user_id')->unique()->count()
        ];
    }
} 