<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Models\Store;

class PaymentMethodConfig extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_method_config';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'cash_change_available',
        'default_method_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cash_change_available' => 'boolean',
    ];

    /**
     * Get the store that owns the payment method configuration.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the default payment method.
     */
    public function defaultMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'default_method_id');
    }

    /**
     * Check if cash change is available.
     * 
     * @return bool
     */
    public function isCashChangeAvailable(): bool
    {
        return $this->cash_change_available;
    }

    /**
     * Get the configuration for a specific store or create a default one if it doesn't exist.
     * 
     * @param int $storeId
     * @return self
     */
    public static function getForStore(int $storeId): self
    {
        return self::firstOrCreate(
            ['store_id' => $storeId],
            ['cash_change_available' => true]
        );
    }

    /**
     * Set the default payment method.
     * 
     * @param int $paymentMethodId
     * @return bool
     */
    public function setDefaultMethod(int $paymentMethodId): bool
    {
        $this->default_method_id = $paymentMethodId;
        return $this->save();
    }

    /**
     * Toggle cash change availability.
     * 
     * @return bool
     */
    public function toggleCashChangeAvailable(): bool
    {
        $this->cash_change_available = !$this->cash_change_available;
        return $this->save();
    }
}