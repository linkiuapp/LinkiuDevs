<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'store_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected $attributes = [
        'read_at' => null, // Se asigna automáticamente en el create
    ];

    // Relaciones
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(PlatformAnnouncement::class, 'announcement_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // Métodos estáticos de conveniencia
    public static function markAsRead(int $announcementId, int $storeId): self
    {
        return self::firstOrCreate([
            'announcement_id' => $announcementId,
            'store_id' => $storeId,
        ], [
            'read_at' => now(),
        ]);
    }

    public static function getUnreadCount(int $storeId): int
    {
        return PlatformAnnouncement::active()
            ->whereDoesntHave('reads', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->count();
    }
}
