<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
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
        'auto_mark_read_after',
    ];

    protected $casts = [
        'target_plans' => 'array',
        'target_stores' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'show_as_banner' => 'boolean',
        'show_popup' => 'boolean',
        'send_email' => 'boolean',
    ];

    protected $attributes = [
        'type' => 'info',
        'priority' => 1,
        'is_active' => false,
        'show_as_banner' => false,
        'show_popup' => false,
        'send_email' => false,
    ];

    // Constantes para tipos
    const TYPE_CRITICAL = 'critical';
    const TYPE_IMPORTANT = 'important';
    const TYPE_INFO = 'info';

    const TYPES = [
        self::TYPE_CRITICAL => 'Crítico',
        self::TYPE_IMPORTANT => 'Importante', 
        self::TYPE_INFO => 'Información',
    ];

    // Relaciones
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
                    ->orderBy('published_at', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    // Métodos de conveniencia
    public function isReadBy(int $storeId): bool
    {
        return $this->reads()->where('store_id', $storeId)->exists();
    }

    public function markAsReadBy(int $storeId): void
    {
        $this->reads()->firstOrCreate([
            'store_id' => $storeId,
        ], [
            'read_at' => now(),
        ]);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? 'Desconocido';
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'critical' => '🚨',
            'important' => '⭐',
            'info' => 'ℹ️',
            default => '📢'
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'critical' => 'error',
            'important' => 'warning', 
            'info' => 'info',
            default => 'primary'
        };
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        return $this->banner_image ? asset('storage/announcements/banners/' . $this->banner_image) : null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPublished(): bool
    {
        return $this->is_active && 
               (!$this->published_at || $this->published_at->isPast()) &&
               !$this->isExpired();
    }

    public function shouldAutoMarkRead(): bool
    {
        return $this->auto_mark_read_after && 
               $this->created_at->diffInDays(now()) >= $this->auto_mark_read_after;
    }
}
