<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorePlanExtension extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'plan_id',
        'super_admin_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relaciones
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function superAdmin()
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('end_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<=', now());
    }

    // Métodos de ayuda
    public function isActive(): bool
    {
        return $this->end_date->isFuture();
    }

    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }
} 