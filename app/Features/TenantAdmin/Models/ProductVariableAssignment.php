<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariableAssignment extends Model
{
    protected $fillable = [
        'product_id',
        'variable_id',
        'is_required',
        'custom_label',
        'display_order',
        'group_name',
        'group_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Relación con el producto
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /**
     * Relación con la variable
     */
    public function variable()
    {
        return $this->belongsTo(ProductVariable::class, 'variable_id');
    }

    /**
     * Scope para asignaciones de un producto específico
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope para asignaciones de una variable específica
     */
    public function scopeForVariable($query, $variableId)
    {
        return $query->where('variable_id', $variableId);
    }

    /**
     * Scope para asignaciones requeridas
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope para asignaciones opcionales
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    /**
     * Scope para asignaciones de un grupo específico
     */
    public function scopeInGroup($query, $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    /**
     * Scope ordenado por display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Scope ordenado por grupo y luego por display_order
     */
    public function scopeOrderedByGroup($query)
    {
        return $query->orderBy('group_order')->orderBy('display_order');
    }

    /**
     * Obtener la etiqueta a mostrar (custom_label o nombre de variable)
     */
    public function getDisplayLabelAttribute()
    {
        return $this->custom_label ?: $this->variable->name;
    }

    /**
     * Verificar si tiene etiqueta personalizada
     */
    public function hasCustomLabel()
    {
        return !empty($this->custom_label);
    }

    /**
     * Verificar si pertenece a un grupo
     */
    public function isGrouped()
    {
        return !empty($this->group_name);
    }

    /**
     * Obtener el nombre del grupo o un valor por defecto
     */
    public function getGroupNameOrDefaultAttribute()
    {
        return $this->group_name ?: 'General';
    }
}
