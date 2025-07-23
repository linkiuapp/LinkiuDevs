<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Shared\Models\CategoryIcon;
use App\Shared\Models\Store;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_id',
        'parent_id',
        'store_id',
        'is_active',
        'sort_order',
        'products_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'products_count' => 'integer',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generar slug si no existe
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Relación con el icono
     */
    public function icon()
    {
        return $this->belongsTo(CategoryIcon::class);
    }

    /**
     * Relación con la tienda
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relación con la categoría padre
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relación con las subcategorías
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para categorías principales (sin padre)
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Verificar si es categoría principal
     */
    public function isMain()
    {
        return is_null($this->parent_id);
    }

    /**
     * Obtener el nombre completo (con padre si es subcategoría)
     */
    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }
} 