<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'plan_id',
        'status',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'trial_start',
        'trial_end',
        'cancelled_at',
        'grace_period_end',
        'next_billing_date',
        'next_billing_amount',
        'metadata'
    ];

    protected $casts = [
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'trial_start' => 'datetime',
        'trial_end' => 'datetime',
        'cancelled_at' => 'datetime',
        'grace_period_end' => 'datetime',
        'next_billing_date' => 'date',
        'next_billing_amount' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Constants
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_GRACE_PERIOD = 'grace_period';

    const BILLING_CYCLE_MONTHLY = 'monthly';
    const BILLING_CYCLE_QUARTERLY = 'quarterly';
    const BILLING_CYCLE_BIANNUAL = 'biannual';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear una nueva suscripción, registrar en el historial
        static::created(function ($subscription) {
            $subscription->recordChange('creation', [
                'new_plan_id' => $subscription->plan_id,
                'new_status' => $subscription->status,
                'new_billing_cycle' => $subscription->billing_cycle,
                'new_amount' => $subscription->plan->getPriceForPeriod($subscription->billing_cycle),
                'change_reason' => 'Suscripción creada'
            ]);
        });

        // Al actualizar, detectar cambios y registrarlos
        static::updating(function ($subscription) {
            if ($subscription->isDirty(['plan_id', 'status', 'billing_cycle'])) {
                $subscription->recordChanges();
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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(SubscriptionHistory::class)->orderBy('changed_at', 'desc');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeInGracePeriod($query)
    {
        return $query->where('status', self::STATUS_GRACE_PERIOD);
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('current_period_end', '<=', now()->addDays($days))
                    ->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_EXPIRED => 'Expirada',
            self::STATUS_SUSPENDED => 'Suspendida',
            self::STATUS_GRACE_PERIOD => 'Período de Gracia',
            default => 'Desconocido'
        };
    }

    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            self::BILLING_CYCLE_MONTHLY => 'Mensual',
            self::BILLING_CYCLE_QUARTERLY => 'Trimestral',
            self::BILLING_CYCLE_BIANNUAL => 'Semestral',
            default => 'Desconocido'
        };
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function getIsInTrialAttribute(): bool
    {
        return $this->trial_start && $this->trial_end && 
               now()->between($this->trial_start, $this->trial_end);
    }

    public function getIsInGracePeriodAttribute(): bool
    {
        return $this->status === self::STATUS_GRACE_PERIOD &&
               $this->grace_period_end &&
               now()->lt($this->grace_period_end);
    }

    public function getDaysUntilExpirationAttribute(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->current_period_end, false));
    }

    public function getDaysUntilNextBillingAttribute(): int
    {
        if (!$this->next_billing_date) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->next_billing_date, false));
    }

    /**
     * Métodos de negocio
     */
    public function cancel(string $reason = null, Carbon $endDate = null): bool
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'grace_period_end' => $endDate ?? $this->current_period_end,
            'metadata' => array_merge($this->metadata ?? [], [
                'cancellation_reason' => $reason,
                'cancelled_by' => auth()->id()
            ])
        ]);

        return true;
    }

    public function reactivate(): bool
    {
        if (!$this->is_cancelled) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACTIVE,
            'cancelled_at' => null,
            'grace_period_end' => null,
            'metadata' => array_merge($this->metadata ?? [], [
                'reactivated_at' => now(),
                'reactivated_by' => auth()->id()
            ])
        ]);

        return true;
    }

    public function changePlan(Plan $newPlan, string $reason = null): bool
    {
        $oldPlan = $this->plan;
        
        $this->update([
            'plan_id' => $newPlan->id,
            'next_billing_amount' => $newPlan->getPriceForPeriod($this->billing_cycle)
        ]);

        $this->recordChange($oldPlan->id < $newPlan->id ? 'plan_upgrade' : 'plan_downgrade', [
            'old_plan_id' => $oldPlan->id,
            'new_plan_id' => $newPlan->id,
            'old_amount' => $oldPlan->getPriceForPeriod($this->billing_cycle),
            'new_amount' => $newPlan->getPriceForPeriod($this->billing_cycle),
            'change_reason' => $reason
        ]);

        return true;
    }

    public function changeBillingCycle(string $newCycle): bool
    {
        if (!in_array($newCycle, [self::BILLING_CYCLE_MONTHLY, self::BILLING_CYCLE_QUARTERLY, self::BILLING_CYCLE_BIANNUAL])) {
            return false;
        }

        $oldCycle = $this->billing_cycle;
        $oldAmount = $this->plan->getPriceForPeriod($oldCycle);
        $newAmount = $this->plan->getPriceForPeriod($newCycle);

        $this->update([
            'billing_cycle' => $newCycle,
            'next_billing_amount' => $newAmount
        ]);

        $this->recordChange('billing_cycle_change', [
            'old_billing_cycle' => $oldCycle,
            'new_billing_cycle' => $newCycle,
            'old_amount' => $oldAmount,
            'new_amount' => $newAmount
        ]);

        return true;
    }

    public function renew(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $periodDays = match($this->billing_cycle) {
            self::BILLING_CYCLE_MONTHLY => 30,
            self::BILLING_CYCLE_QUARTERLY => 90,
            self::BILLING_CYCLE_BIANNUAL => 180,
            default => 30
        };

        $this->update([
            'current_period_start' => $this->current_period_end->addDay(),
            'current_period_end' => $this->current_period_end->addDays($periodDays),
            'next_billing_date' => $this->current_period_end->addDays($periodDays)
        ]);

        return true;
    }

    /**
     * Registrar cambios en el historial
     */
    protected function recordChange(string $changeType, array $data = []): void
    {
        SubscriptionHistory::create([
            'store_id' => $this->store_id,
            'subscription_id' => $this->id,
            'change_type' => $changeType,
            'changed_at' => now(),
            'changed_by_user_id' => auth()->id(),
            'changed_by_role' => auth()->user()?->role ?? 'system',
            'metadata' => array_merge($data, [
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent()
            ]),
            ...$data
        ]);
    }

    protected function recordChanges(): void
    {
        $changes = $this->getDirty();
        
        if (isset($changes['plan_id'])) {
            $this->recordChange('plan_change', [
                'old_plan_id' => $this->getOriginal('plan_id'),
                'new_plan_id' => $changes['plan_id']
            ]);
        }

        if (isset($changes['status'])) {
            $this->recordChange('status_change', [
                'old_status' => $this->getOriginal('status'),
                'new_status' => $changes['status']
            ]);
        }

        if (isset($changes['billing_cycle'])) {
            $this->recordChange('billing_cycle_change', [
                'old_billing_cycle' => $this->getOriginal('billing_cycle'),
                'new_billing_cycle' => $changes['billing_cycle']
            ]);
        }
    }

    /**
     * Verificar si puede cambiar de plan
     */
    public function canChangePlan(): bool
    {
        return $this->is_active || $this->is_in_grace_period;
    }

    /**
     * Verificar si puede cancelar
     */
    public function canCancel(): bool
    {
        return $this->is_active;
    }

    /**
     * Verificar si puede reactivar
     */
    public function canReactivate(): bool
    {
        return $this->is_cancelled && ($this->is_in_grace_period || now()->lt($this->current_period_end));
    }
}
