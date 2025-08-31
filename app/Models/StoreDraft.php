<?php

namespace App\Models;

use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StoreDraft extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'form_data',
        'template',
        'current_step',
        'expires_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'expires_at' => 'datetime',
        'current_step' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($draft) {
            // Set expiration to 7 days from now if not set
            if (!$draft->expires_at) {
                $draft->expires_at = Carbon::now()->addDays(7);
            }
        });
    }

    /**
     * Get the user that owns the draft.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the store associated with the draft (if any).
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope to get non-expired drafts.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired drafts.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if the draft is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Extend the expiration date by the given number of days.
     */
    public function extend(int $days = 7): bool
    {
        return $this->update([
            'expires_at' => Carbon::now()->addDays($days)
        ]);
    }

    /**
     * Update the form data while preserving existing data.
     */
    public function updateFormData(array $newData): bool
    {
        $currentData = $this->form_data ?? [];
        $mergedData = array_merge_recursive($currentData, $newData);
        
        return $this->update([
            'form_data' => $mergedData,
            'expires_at' => Carbon::now()->addDays(7) // Extend expiration on update
        ]);
    }

    /**
     * Get a specific field from form data.
     */
    public function getFormField(string $key, $default = null)
    {
        return data_get($this->form_data, $key, $default);
    }

    /**
     * Set a specific field in form data.
     */
    public function setFormField(string $key, $value): bool
    {
        $formData = $this->form_data ?? [];
        data_set($formData, $key, $value);
        
        return $this->update(['form_data' => $formData]);
    }

    /**
     * Clean up expired drafts.
     */
    public static function cleanupExpired(): int
    {
        return static::expired()->delete();
    }

    /**
     * Get the most recent draft for a user.
     */
    public static function getLatestForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->active()
            ->latest()
            ->first();
    }

    /**
     * Create or update a draft for a user.
     */
    public static function createOrUpdate(int $userId, array $formData, ?string $template = null, int $currentStep = 1): self
    {
        $draft = static::where('user_id', $userId)
            ->active()
            ->latest()
            ->first();

        if ($draft) {
            $draft->updateFormData($formData);
            if ($template) {
                $draft->template = $template;
            }
            $draft->current_step = $currentStep;
            $draft->save();
            
            return $draft;
        }

        return static::create([
            'user_id' => $userId,
            'form_data' => $formData,
            'template' => $template,
            'current_step' => $currentStep,
            'expires_at' => Carbon::now()->addDays(7),
        ]);
    }

    /**
     * Convert draft to store creation data.
     */
    public function toStoreData(): array
    {
        $formData = $this->form_data ?? [];
        
        // Transform the form data structure to match store creation requirements
        return [
            'owner' => $formData['owner'] ?? [],
            'store' => $formData['store'] ?? [],
            'fiscal' => $formData['fiscal'] ?? [],
            'billing' => $formData['billing'] ?? [],
            'seo' => $formData['seo'] ?? [],
            'advanced' => $formData['advanced'] ?? [],
            'template' => $this->template,
        ];
    }
}
