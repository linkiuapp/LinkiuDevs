<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'current_plan_id',
        'requested_plan_id',
        'type',
        'status',
        'reason',
        'admin_notes',
        'requested_at',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    const TYPE_UPGRADE = 'upgrade';
    const TYPE_DOWNGRADE = 'downgrade';

    /**
     * Relationship: Store that made the request
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relationship: Current plan
     */
    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'current_plan_id');
    }

    /**
     * Relationship: Requested plan
     */
    public function requestedPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'requested_plan_id');
    }

    /**
     * Relationship: Admin who processed the request
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Determine request type based on plan prices
     */
    public function determineType(): string
    {
        if ($this->requestedPlan->price > $this->currentPlan->price) {
            return self::TYPE_UPGRADE;
        }

        return self::TYPE_DOWNGRADE;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_REJECTED => 'Rechazada',
            self::STATUS_COMPLETED => 'Completada',
            default => 'Desconocido',
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_UPGRADE => 'Upgrade',
            self::TYPE_DOWNGRADE => 'Downgrade',
            default => 'Cambio',
        };
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Approve the request
     */
    public function approve(?string $adminNotes = null, ?int $processedBy = null): self
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'admin_notes' => $adminNotes,
            'processed_at' => now(),
            'processed_by' => $processedBy,
        ]);

        return $this;
    }

    /**
     * Reject the request
     */
    public function reject(?string $adminNotes = null, ?int $processedBy = null): self
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'admin_notes' => $adminNotes,
            'processed_at' => now(),
            'processed_by' => $processedBy,
        ]);

        return $this;
    }

    /**
     * Mark request as completed
     */
    public function complete(?string $adminNotes = null, ?int $processedBy = null): self
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'admin_notes' => $adminNotes,
            'processed_at' => now(),
            'processed_by' => $processedBy,
        ]);

        return $this;
    }

    /**
     * Scope: Get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Get by store
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }
}
