<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear métodos de envío para tiendas existentes
        $stores = DB::table('stores')->get();
        
        foreach ($stores as $store) {
            // Verificar si ya tiene métodos
            $hasShippingMethods = DB::table('shipping_methods')
                ->where('store_id', $store->id)
                ->exists();
                
            if (!$hasShippingMethods) {
                // Crear método de domicilio
                $domicilioId = DB::table('shipping_methods')->insertGetId([
                    'type' => 'domicilio',
                    'name' => 'Envío a Domicilio',
                    'is_active' => false,
                    'sort_order' => 1,
                    'instructions' => 'Entrega en la dirección indicada',
                    'store_id' => $store->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Crear método de pickup
                $pickupId = DB::table('shipping_methods')->insertGetId([
                    'type' => 'pickup',
                    'name' => 'Recoger en Tienda',
                    'is_active' => false,
                    'sort_order' => 2,
                    'instructions' => 'Recoger en nuestra tienda principal',
                    'store_id' => $store->id,
                    'preparation_time' => '1h',
                    'notification_enabled' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Crear configuración
                DB::table('shipping_method_config')->insert([
                    'store_id' => $store->id,
                    'default_method_id' => null,
                    'min_active_methods' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se puede revertir fácilmente sin eliminar datos
    }
}; 