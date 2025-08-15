<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PlatformAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'banner_image',
        'banner_link',
        'show_as_banner',
        'target_plans',
        'target_stores',
        'published_at',
        'expires_at',
        'is_active',
        'show_popup',
        'send_email',
        'auto_mark_read_after'
    ];

    protected $casts = [
        'target_plans' => 'array',
        'target_stores' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'show_popup' => 'boolean',
        'send_email' => 'boolean',
        'show_as_banner' => 'boolean',
        'priority' => 'integer',
        'auto_mark_read_after' => 'integer'
    ];

    // Constants for types
    const TYPE_CRITICAL = 'critical';
    const TYPE_IMPORTANT = 'important';
    const TYPE_INFO = 'info';

    // Relationships
    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class, 'announcement_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForPlan(Builder $query, string $plan): Builder
    {
        return $query->where(function ($q) use ($plan) {
            $q->whereNull('target_plans')
              ->orWhereJsonContains('target_plans', $plan);
        });
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where(function ($q) use ($storeId) {
            $q->whereNull('target_stores')
              ->orWhereJsonContains('target_stores', $storeId);
        });
    }

    public function scopeBanners(Builder $query): Builder
    {
        return $query->where('show_as_banner', true);
    }

    public function scopePopups(Builder $query): Builder
    {
        return $query->where('show_popup', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CRITICAL => 'Crítico',
            self::TYPE_IMPORTANT => 'Importante',
            self::TYPE_INFO => 'Información',
            default => 'Información'
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CRITICAL => '🚨',
            self::TYPE_IMPORTANT => '⭐',
            self::TYPE_INFO => 'ℹ️',
            default => 'ℹ️'
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CRITICAL => 'error',
            self::TYPE_IMPORTANT => 'warning',
            self::TYPE_INFO => 'info',
            default => 'info'
        };
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        if (!$this->banner_image) {
            return null;
        }

        // ✅ Usar método estándar para generar URLs
        return asset('storage/announcements/banners/' . $this->banner_image);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->is_active && 
               (!$this->published_at || $this->published_at->isPast()) &&
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function getShouldAutoMarkReadAttribute(): bool
    {
        if (!$this->auto_mark_read_after) {
            return false;
        }

        return $this->created_at->addDays($this->auto_mark_read_after)->isPast();
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->is_published;
    }

    /**
     * Check if announcement is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if announcement is read by specific store
     */
    public function isReadBy(int $storeId): bool
    {
        return $this->reads()->where('store_id', $storeId)->exists();
    }

    /**
     * Mark announcement as read by specific store
     */
    public function markAsReadBy(int $storeId): AnnouncementRead
    {
        return AnnouncementRead::markAsRead($this->id, $storeId);
    }
} 