<?php

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'privacy_policy',
        'terms_conditions',
        'shipping_policy',
        'return_policy',
        'about_us',
    ];

    // Relaciones
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
