<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AnnouncementRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'store_id',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    // Relationships
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(PlatformAnnouncement::class, 'announcement_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // Static helper methods
    public static function markAsRead(int $announcementId, int $storeId): self
    {
        return self::firstOrCreate(
            [
                'announcement_id' => $announcementId,
                'store_id' => $storeId
            ],
            [
                'read_at' => now()
            ]
        );
    }

    public static function getUnreadCount(int $storeId): int
    {
        $store = Store::find($storeId);
        if (!$store) {
            return 0;
        }

        $storePlan = strtolower($store->plan->name ?? $store->plan->slug ?? 'explorer');
        
        return PlatformAnnouncement::active()
            ->forPlan($storePlan)
            ->forStore($storeId)
            ->whereDoesntHave('reads', function ($q) use ($storeId) {
                $q->where('store_id', $storeId);
            })
            ->count();
    }

    public static function isAnnouncementReadByStore(int $announcementId, int $storeId): bool
    {
        return self::where('announcement_id', $announcementId)
                   ->where('store_id', $storeId)
                   ->exists();
    }
} 