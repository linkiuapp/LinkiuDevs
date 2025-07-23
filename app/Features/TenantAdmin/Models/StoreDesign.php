<?php

namespace App\Features\TenantAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use App\Shared\Models\Store;

class StoreDesign extends Model
{
    protected $fillable = [
        'store_id',
        'logo_url',
        'logo_webp_url',
        'favicon_url',
        'header_background_color',
        'header_text_color',
        'header_description_color',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /**
     * Get the store that owns the design.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the design history records.
     */
    public function history()
    {
        return $this->hasMany(StoreDesignHistory::class);
    }

    /**
     * Get the background style for the header.
     */
    public function getBackgroundStyleAttribute()
    {
        return $this->header_background_color ?? '#FFFFFF';
    }
} 