<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CategoryIcon extends Model
{
    protected $fillable = [
        'name',
        'image_path',
        'display_name',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope para obtener solo iconos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener la URL completa de la imagen
     */
    public function getImageUrlAttribute()
    {
        return Storage::disk('s3')->url($this->image_path);
    }
} 