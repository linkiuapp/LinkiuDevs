<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Shared\Models\Store;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'type',
        'sku',
        'main_image_id',
        'is_active',
        'store_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Constantes para tipos de productos
    const TYPE_SIMPLE = 'simple';
    const TYPE_VARIABLE = 'variable';

    const TYPES = [
        self::TYPE_SIMPLE => 'Producto Simple',
        self::TYPE_VARIABLE => 'Producto Variable',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generar slug y SKU al crear
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->store_id);
            }
            
            if (empty($product->sku)) {
                $product->sku = static::generateUniqueSku($product->store_id);
            }
        });

        // Regenerar slug si cambia el nombre
        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->store_id, $product->id);
            }
        });
    }

    /**
     * Relación con la tienda
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relación con las imágenes
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Relación con la imagen principal
     */
    public function mainImage(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'main_image_id');
    }

    /**
     * Relación con las categorías (muchos a muchos)
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Features\TenantAdmin\Models\Category::class,
            'product_categories',
            'product_id',
            'category_id'
        )->withTimestamps();
    }

    /**
     * Relación con las variantes
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Relación con las variantes activas
     */
    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSimple($query)
    {
        return $query->where('type', self::TYPE_SIMPLE);
    }

    public function scopeVariable($query)
    {
        return $query->where('type', self::TYPE_VARIABLE);
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Métodos de ayuda
     */
    public function isSimple(): bool
    {
        return $this->type === self::TYPE_SIMPLE;
    }

    public function isVariable(): bool
    {
        return $this->type === self::TYPE_VARIABLE;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Obtener el precio formateado
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Obtener URL del producto
     */
    public function getUrlAttribute(): string
    {
        return route('tenant.admin.products.show', [
            'store' => $this->store->slug,
            'product' => $this->id
        ]);
    }

    /**
     * Obtener imagen principal o placeholder
     */
    public function getMainImageUrlAttribute(): string
    {
        if ($this->mainImage) {
            return asset('storage/' . $this->mainImage->image_path);
        }
        
        return asset('images/product-placeholder.jpg');
    }

    /**
     * Obtener thumbnail de imagen principal
     */
    public function getMainImageThumbnailAttribute(): string
    {
        if ($this->mainImage && $this->mainImage->thumbnail_path) {
            return asset('storage/' . $this->mainImage->thumbnail_path);
        }
        
        return asset('images/product-placeholder-thumb.jpg');
    }

    /**
     * Generar slug único
     */
    public static function generateUniqueSlug(string $name, int $storeId, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        $query = static::where('store_id', $storeId)->where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            
            $query = static::where('store_id', $storeId)->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Generar SKU único
     */
    public static function generateUniqueSku(int $storeId): string
    {
        $store = Store::find($storeId);
        $storePrefix = strtoupper(substr($store->slug, 0, 3));
        
        do {
            $randomNumber = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $sku = $storePrefix . '-' . $randomNumber;
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Duplicar producto
     */
    public function duplicate(): static
    {
        $newProduct = $this->replicate();
        $newProduct->name = $this->name . ' - Copia';
        $newProduct->slug = null; // Se generará automáticamente
        $newProduct->sku = null; // Se generará automáticamente
        $newProduct->main_image_id = null; // Se asignará después
        $newProduct->save();

        // Duplicar relaciones con categorías
        $newProduct->categories()->attach($this->categories->pluck('id'));

        // Duplicar imágenes (solo referencias)
        $imageMapping = [];
        foreach ($this->images as $image) {
            $newImage = $image->replicate();
            $newImage->product_id = $newProduct->id;
            $newImage->save();
            
            $imageMapping[$image->id] = $newImage->id;
        }

        // Asignar imagen principal si existe
        if ($this->main_image_id && isset($imageMapping[$this->main_image_id])) {
            $newProduct->main_image_id = $imageMapping[$this->main_image_id];
            $newProduct->save();
        }

        // Duplicar variantes si es producto variable
        if ($this->isVariable()) {
            foreach ($this->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->sku = null; // Se generará automáticamente en ProductVariant
                $newVariant->save();
            }
        }

        return $newProduct;
    }
}
