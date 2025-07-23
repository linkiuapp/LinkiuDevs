<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'thumbnail_path',
        'medium_path',
        'large_path',
        'alt_text',
        'sort_order',
        'is_main'
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Relación con el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope para obtener solo imágenes principales
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * Scope para ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Obtener la URL completa de la imagen original
     */
    public function getImageUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }

    /**
     * Obtener la URL completa de la imagen thumbnail
     */
    public function getThumbnailUrlAttribute(): string
    {
        return Storage::url($this->thumbnail_path);
    }

    /**
     * Obtener la URL completa de la imagen medium
     */
    public function getMediumUrlAttribute(): string
    {
        return Storage::url($this->medium_path);
    }

    /**
     * Obtener la URL completa de la imagen large
     */
    public function getLargeUrlAttribute(): string
    {
        return Storage::url($this->large_path);
    }

    /**
     * Eliminar archivos físicos cuando se elimina el modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            // Eliminar archivos físicos
            if (Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }
            if (Storage::exists($image->thumbnail_path)) {
                Storage::delete($image->thumbnail_path);
            }
            if (Storage::exists($image->medium_path)) {
                Storage::delete($image->medium_path);
            }
            if (Storage::exists($image->large_path)) {
                Storage::delete($image->large_path);
            }
        });
    }

    /**
     * Marcar esta imagen como principal
     */
    public function setAsMain(): bool
    {
        // Desmarcar todas las imágenes del producto como principales
        $this->product->images()->update(['is_main' => false]);
        
        // Marcar esta imagen como principal
        return $this->update(['is_main' => true]);
    }

    /**
     * Verificar si es la imagen principal
     */
    public function isMain(): bool
    {
        return $this->is_main;
    }

    /**
     * Obtener el tamaño del archivo en bytes
     */
    public function getFileSize(): int
    {
        if (Storage::exists($this->image_path)) {
            return Storage::size($this->image_path);
        }
        return 0;
    }

    /**
     * Obtener el tamaño del archivo formateado
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->getFileSize();
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
} 