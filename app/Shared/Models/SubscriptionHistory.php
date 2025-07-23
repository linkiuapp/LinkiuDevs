<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionHistory extends Model
{
    use HasFactory;

    protected $table = 'subscription_history';

    protected $fillable = [
        'store_id',
        'subscription_id',
        'old_plan_id',
        'new_plan_id',
        'old_billing_cycle',
        'new_billing_cycle',
        'old_status',
        'new_status',
        'change_type',
        'change_reason',
        'changed_by_user_id',
        'changed_by_role',
        'old_amount',
        'new_amount',
        'proration_amount',
        'metadata',
        'changed_at'
    ];

    protected $casts = [
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
        'proration_amount' => 'decimal:2',
        'metadata' => 'array',
        'changed_at' => 'datetime'
    ];

    // Constants for change types
    const CHANGE_TYPE_PLAN_UPGRADE = 'plan_upgrade';
    const CHANGE_TYPE_PLAN_DOWNGRADE = 'plan_downgrade';
    const CHANGE_TYPE_BILLING_CYCLE_CHANGE = 'billing_cycle_change';
    const CHANGE_TYPE_CANCELLATION = 'cancellation';
    const CHANGE_TYPE_REACTIVATION = 'reactivation';
    const CHANGE_TYPE_SUSPENSION = 'suspension';
    const CHANGE_TYPE_EXPIRATION = 'expiration';
    const CHANGE_TYPE_CREATION = 'creation';

    /**
     * Relaciones
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'old_plan_id');
    }

    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'new_plan_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeByChangeType($query, string $changeType)
    {
        return $query->where('change_type', $changeType);
    }

    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeBySubscription($query, int $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    public function scopePlanChanges($query)
    {
        return $query->whereIn('change_type', [
            self::CHANGE_TYPE_PLAN_UPGRADE,
            self::CHANGE_TYPE_PLAN_DOWNGRADE
        ]);
    }

    /**
     * Accessors
     */
    public function getChangeTypeLabelAttribute(): string
    {
        return match($this->change_type) {
            self::CHANGE_TYPE_PLAN_UPGRADE => 'Upgrade de Plan',
            self::CHANGE_TYPE_PLAN_DOWNGRADE => 'Downgrade de Plan',
            self::CHANGE_TYPE_BILLING_CYCLE_CHANGE => 'Cambio de Ciclo de Facturación',
            self::CHANGE_TYPE_CANCELLATION => 'Cancelación',
            self::CHANGE_TYPE_REACTIVATION => 'Reactivación',
            self::CHANGE_TYPE_SUSPENSION => 'Suspensión',
            self::CHANGE_TYPE_EXPIRATION => 'Expiración',
            self::CHANGE_TYPE_CREATION => 'Creación',
            default => 'Cambio Desconocido'
        };
    }

    public function getChangedByRoleLabelAttribute(): string
    {
        return match($this->changed_by_role) {
            'store_admin' => 'Administrador de Tienda',
            'super_admin' => 'Super Administrador',
            'system' => 'Sistema',
            default => 'Desconocido'
        };
    }

    public function getIsPlanChangeAttribute(): bool
    {
        return in_array($this->change_type, [
            self::CHANGE_TYPE_PLAN_UPGRADE,
            self::CHANGE_TYPE_PLAN_DOWNGRADE
        ]);
    }

    public function getIsUpgradeAttribute(): bool
    {
        return $this->change_type === self::CHANGE_TYPE_PLAN_UPGRADE;
    }

    public function getIsDowngradeAttribute(): bool
    {
        return $this->change_type === self::CHANGE_TYPE_PLAN_DOWNGRADE;
    }

    public function getAmountDifferenceAttribute(): ?float
    {
        if ($this->new_amount && $this->old_amount) {
            return $this->new_amount - $this->old_amount;
        }
        
        return null;
    }

    public function getFormattedAmountDifferenceAttribute(): ?string
    {
        $diff = $this->amount_difference;
        
        if ($diff === null) {
            return null;
        }
        
        $sign = $diff >= 0 ? '+' : '';
        return $sign . '$' . number_format(abs($diff), 0, ',', '.');
    }

    public function getChangeDescriptionAttribute(): string
    {
        switch ($this->change_type) {
            case self::CHANGE_TYPE_PLAN_UPGRADE:
                return "Upgrade de {$this->oldPlan?->name} a {$this->newPlan?->name}";
                
            case self::CHANGE_TYPE_PLAN_DOWNGRADE:
                return "Downgrade de {$this->oldPlan?->name} a {$this->newPlan?->name}";
                
            case self::CHANGE_TYPE_BILLING_CYCLE_CHANGE:
                return "Cambio de facturación de {$this->old_billing_cycle} a {$this->new_billing_cycle}";
                
            case self::CHANGE_TYPE_CANCELLATION:
                return "Suscripción cancelada";
                
            case self::CHANGE_TYPE_REACTIVATION:
                return "Suscripción reactivada";
                
            case self::CHANGE_TYPE_CREATION:
                return "Suscripción creada con plan {$this->newPlan?->name}";
                
            default:
                return $this->change_type_label;
        }
    }

    /**
     * Métodos estáticos para crear registros de historial
     */
    public static function recordPlanChange(
        Subscription $subscription, 
        Plan $oldPlan, 
        Plan $newPlan, 
        string $reason = null
    ): self {
        $isUpgrade = $oldPlan->price < $newPlan->price;
        
        return self::create([
            'store_id' => $subscription->store_id,
            'subscription_id' => $subscription->id,
            'old_plan_id' => $oldPlan->id,
            'new_plan_id' => $newPlan->id,
            'change_type' => $isUpgrade ? self::CHANGE_TYPE_PLAN_UPGRADE : self::CHANGE_TYPE_PLAN_DOWNGRADE,
            'old_amount' => $oldPlan->getPriceForPeriod($subscription->billing_cycle),
            'new_amount' => $newPlan->getPriceForPeriod($subscription->billing_cycle),
            'change_reason' => $reason,
            'changed_by_user_id' => auth()->id(),
            'changed_by_role' => auth()->user()?->role ?? 'system',
            'changed_at' => now(),
            'metadata' => [
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent()
            ]
        ]);
    }

    public static function recordCancellation(
        Subscription $subscription, 
        string $reason = null
    ): self {
        return self::create([
            'store_id' => $subscription->store_id,
            'subscription_id' => $subscription->id,
            'change_type' => self::CHANGE_TYPE_CANCELLATION,
            'old_status' => $subscription->getOriginal('status'),
            'new_status' => Subscription::STATUS_CANCELLED,
            'change_reason' => $reason,
            'changed_by_user_id' => auth()->id(),
            'changed_by_role' => auth()->user()?->role ?? 'system',
            'changed_at' => now(),
            'metadata' => [
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent()
            ]
        ]);
    }

    public static function recordReactivation(Subscription $subscription): self
    {
        return self::create([
            'store_id' => $subscription->store_id,
            'subscription_id' => $subscription->id,
            'change_type' => self::CHANGE_TYPE_REACTIVATION,
            'old_status' => $subscription->getOriginal('status'),
            'new_status' => Subscription::STATUS_ACTIVE,
            'changed_by_user_id' => auth()->id(),
            'changed_by_role' => auth()->user()?->role ?? 'system',
            'changed_at' => now(),
            'metadata' => [
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent()
            ]
        ]);
    }
}
