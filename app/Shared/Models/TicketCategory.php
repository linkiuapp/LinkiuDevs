<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relaciones
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category', 'slug');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Métodos de utilidad
    public static function getActiveOptions(): array
    {
        return static::active()->ordered()->pluck('name', 'slug')->toArray();
    }

    public function getColorClassAttribute(): string
    {
        return match($this->color) {
            'primary' => 'bg-primary-300 text-accent-50',
            'secondary' => 'bg-secondary-300 text-accent-50',
            'success' => 'bg-success-300 text-accent-50',
            'error' => 'bg-error-300 text-accent-50',
            'warning' => 'bg-warning-300 text-black-500',
            'info' => 'bg-info-300 text-accent-50',
            default => 'bg-info-300 text-accent-50'
        };
    }

    public function getTicketsCountAttribute(): int
    {
        return $this->tickets()->count();
    }
}
