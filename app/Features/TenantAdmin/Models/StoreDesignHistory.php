<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;

class StoreDesignHistory extends Model
{
    protected $fillable = [
        'store_design_id',
        'data',
        'created_by',
        'note'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the design that owns this history record.
     */
    public function design()
    {
        return $this->belongsTo(StoreDesign::class, 'store_design_id');
    }

    /**
     * Get the user who created this history record.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Shared\Models\User::class, 'created_by');
    }
} 