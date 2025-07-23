<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;

class VariableOption extends Model
{
    protected $fillable = [
        'variable_id',
        'name',
        'price_modifier',
        'color_hex',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_modifier' => 'decimal:2',
    ];

    /**
     * Relación con la variable padre
     */
    public function variable()
    {
        return $this->belongsTo(ProductVariable::class, 'variable_id');
    }

    /**
     * Scope para opciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para opciones de una variable específica
     */
    public function scopeForVariable($query, $variableId)
    {
        return $query->where('variable_id', $variableId);
    }

    /**
     * Obtener el modificador de precio formateado
     */
    public function getFormattedPriceModifierAttribute()
    {
        if ($this->price_modifier == 0) {
            return 'Sin cambio';
        }
        
        $prefix = $this->price_modifier > 0 ? '+' : '';
        return $prefix . '$' . number_format($this->price_modifier, 0, ',', '.');
    }

    /**
     * Verificar si tiene color asignado
     */
    public function hasColor()
    {
        return !empty($this->color_hex);
    }

    /**
     * Obtener el color hex con # si no lo tiene
     */
    public function getColorHexAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        
        return str_starts_with($value, '#') ? $value : '#' . $value;
    }

    /**
     * Establecer el color hex sin # si viene con él
     */
    public function setColorHexAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['color_hex'] = null;
            return;
        }
        
        $this->attributes['color_hex'] = ltrim($value, '#');
    }

    /**
     * Verificar si el modificador es positivo
     */
    public function isPositiveModifier()
    {
        return $this->price_modifier > 0;
    }

    /**
     * Verificar si el modificador es negativo
     */
    public function isNegativeModifier()
    {
        return $this->price_modifier < 0;
    }

    /**
     * Verificar si no tiene modificador
     */
    public function isNeutralModifier()
    {
        return $this->price_modifier == 0;
    }
}
