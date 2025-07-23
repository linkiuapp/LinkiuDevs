<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Shared\Models\Store;
use Illuminate\Support\Str;

class ProductVariable extends Model
{
    protected $fillable = [
        'name',
        'type',
        'store_id',
        'is_active',
        'is_required_default',
        'sort_order',
        'min_value',
        'max_value',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_required_default' => 'boolean',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
    ];

    // Constantes para tipos de variables
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_TEXT = 'text';
    const TYPE_NUMERIC = 'numeric';

    const TYPES = [
        self::TYPE_RADIO => 'Selección única',
        self::TYPE_CHECKBOX => 'Selección múltiple',
        self::TYPE_TEXT => 'Texto libre',
        self::TYPE_NUMERIC => 'Numérico',
    ];

    /**
     * Relación con la tienda
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relación con las opciones de la variable
     */
    public function options()
    {
        return $this->hasMany(VariableOption::class, 'variable_id')->orderBy('sort_order');
    }

    /**
     * Relación con las opciones activas
     */
    public function activeOptions()
    {
        return $this->hasMany(VariableOption::class, 'variable_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Relación con las asignaciones a productos
     */
    public function assignments()
    {
        return $this->hasMany(ProductVariableAssignment::class, 'variable_id');
    }

    /**
     * Scope para variables activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para variables por tipo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para variables de una tienda específica
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Obtener el nombre del tipo de variable
     */
    public function getTypeNameAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Obtener el ícono del tipo de variable
     */
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            self::TYPE_RADIO => 'solar-record-circle-outline',
            self::TYPE_CHECKBOX => 'solar-check-square-outline',
            self::TYPE_TEXT => 'solar-text-outline',
            self::TYPE_NUMERIC => 'solar-calculator-outline',
            default => 'solar-settings-outline',
        };
    }

    /**
     * Verificar si la variable requiere opciones
     */
    public function requiresOptions()
    {
        return in_array($this->type, [self::TYPE_RADIO, self::TYPE_CHECKBOX]);
    }

    /**
     * Verificar si la variable es de tipo numérico
     */
    public function isNumeric()
    {
        return $this->type === self::TYPE_NUMERIC;
    }

    /**
     * Verificar si la variable es de tipo texto
     */
    public function isText()
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Contar productos que usan esta variable
     */
    public function getProductsCountAttribute()
    {
        return $this->assignments()->count();
    }

    /**
     * Validar si se puede eliminar la variable
     */
    public function canBeDeleted()
    {
        return $this->products_count === 0;
    }
}
