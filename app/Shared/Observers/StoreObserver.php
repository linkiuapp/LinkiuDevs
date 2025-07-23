<?php

namespace App\Shared\Observers;

use App\Shared\Models\Store;
use App\Features\TenantAdmin\Models\ShippingMethod;
use App\Features\TenantAdmin\Models\ShippingMethodConfig;

class StoreObserver
{
    /**
     * Handle the Store "created" event.
     */
    public function created(Store $store): void
    {
        // Crear método de domicilio
        $domicilio = ShippingMethod::create([
            'type' => ShippingMethod::TYPE_DOMICILIO,
            'name' => 'Envío a Domicilio',
            'is_active' => false,
            'sort_order' => 1,
            'instructions' => 'Entrega en la dirección indicada',
            'store_id' => $store->id,
        ]);

        // Crear método de pickup
        $pickup = ShippingMethod::create([
            'type' => ShippingMethod::TYPE_PICKUP,
            'name' => 'Recoger en Tienda',
            'is_active' => false,
            'sort_order' => 2,
            'instructions' => 'Recoger en nuestra tienda principal',
            'store_id' => $store->id,
            'preparation_time' => '1h',
            'notification_enabled' => false,
        ]);

        // Crear configuración
        ShippingMethodConfig::create([
            'store_id' => $store->id,
            'default_method_id' => null,
            'min_active_methods' => 1,
        ]);
    }

    /**
     * Handle the Store "deleting" event.
     */
    public function deleting(Store $store): void
    {
        // Las relaciones se eliminan en cascada por las foreign keys
    }
} 