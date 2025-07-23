<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Shared\Models\Store;

class BankAccount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_method_id',
        'bank_name',
        'account_type',
        'account_number',
        'account_holder',
        'document_number',
        'is_active',
        'store_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the payment method that owns the bank account.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the store that owns the bank account.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope a query to only include active bank accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format the account number for display (hiding part of it).
     * 
     * @return string
     */
    public function getFormattedAccountNumber(): string
    {
        $accountNumber = $this->account_number;
        $length = strlen($accountNumber);
        
        if ($length <= 4) {
            return $accountNumber;
        }
        
        $visiblePart = substr($accountNumber, -4);
        $hiddenPart = str_repeat('*', $length - 4);
        
        return $hiddenPart . $visiblePart;
    }

    /**
     * Get the full account information for display.
     * 
     * @return string
     */
    public function getFullAccountInfo(): string
    {
        return "{$this->bank_name} - {$this->account_type} - {$this->account_number}";
    }

    /**
     * Get the account holder with document number if available.
     * 
     * @return string
     */
    public function getAccountHolderWithDocument(): string
    {
        if (empty($this->document_number)) {
            return $this->account_holder;
        }
        
        return "{$this->account_holder} ({$this->document_number})";
    }

    /**
     * Validate account number format.
     * 
     * @param string $accountNumber
     * @return bool
     */
    public static function validateAccountNumber(string $accountNumber): bool
    {
        // Check if account number contains only digits
        if (!ctype_digit($accountNumber)) {
            return false;
        }
        
        // Check if account number length is between 10 and 20 digits
        $length = strlen($accountNumber);
        return $length >= 10 && $length <= 20;
    }
}