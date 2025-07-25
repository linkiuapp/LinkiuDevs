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
        return asset('storage/' . $this->image_path);
    }

    /**
     * Obtener la URL completa de la imagen thumbnail
     */
    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : $this->getImageUrlAttribute();
    }

    /**
     * Obtener la URL completa de la imagen medium
     */
    public function getMediumUrlAttribute(): string
    {
        return $this->medium_path ? asset('storage/' . $this->medium_path) : $this->getImageUrlAttribute();
    }

    /**
     * Obtener la URL completa de la imagen large
     */
    public function getLargeUrlAttribute(): string
    {
        return $this->large_path ? asset('storage/' . $this->large_path) : $this->getImageUrlAttribute();
    }

    /**
     * Eliminar archivos físicos cuando se elimina el modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            // Eliminar archivos físicos usando el patrón Laravel Cloud
            $paths = [
                $image->image_path,
                $image->thumbnail_path,
                $image->medium_path,
                $image->large_path
            ];

            foreach ($paths as $path) {
                if ($path) {
                    $fullPath = public_path('storage/' . $path);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
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