<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Features\TenantAdmin\Models\SimpleShipping;
use App\Features\TenantAdmin\Models\SimpleShippingZone;
use App\Shared\Models\Store;

class SimpleShippingProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚚 Iniciando configuración de envíos para todas las tiendas...');

        $stores = Store::all();
        $processed = 0;
        $skipped = 0;

        foreach ($stores as $store) {
            // Verificar si ya tiene configuración
            $existingShipping = SimpleShipping::where('store_id', $store->id)->first();
            
            if ($existingShipping) {
                $this->command->warn("⚠️  Tienda '{$store->name}' ya tiene configuración de envíos - omitiendo");
                $skipped++;
                continue;
            }

            // Crear configuración básica
            $shipping = SimpleShipping::create([
                'store_id' => $store->id,
                'pickup_enabled' => true,
                'pickup_instructions' => 'Recoge tu pedido en nuestra tienda.',
                'pickup_preparation_time' => '2h',
                
                'local_enabled' => true,
                'local_cost' => 5000.00,
                'local_free_from' => 50000.00,
                'local_city' => $this->getCityForStore($store),
                'local_instructions' => 'Envío local disponible en el área metropolitana.',
                'local_preparation_time' => '24h',
                
                'national_enabled' => false, // Deshabilitado por defecto
                'national_free_from' => 100000.00,
                'national_instructions' => 'Envío nacional disponible a principales ciudades.',
                
                'allow_unlisted_cities' => false,
                'unlisted_cities_cost' => 15000.00,
                'unlisted_cities_message' => 'Consulta disponibilidad y costos de envío para tu ciudad.',
            ]);

            $this->command->info("✅ Configuración creada para tienda: {$store->name}");
            $processed++;
        }

        $this->command->info("🎉 Configuración completada:");
        $this->command->info("   - Tiendas procesadas: {$processed}");
        $this->command->info("   - Tiendas omitidas: {$skipped}");
        $this->command->info("   - Total tiendas: " . $stores->count());
    }

    /**
     * Obtener ciudad probable para la tienda
     */
    private function getCityForStore(Store $store): string
    {
        // Intentar deducir la ciudad desde diferentes fuentes
        if ($store->city) {
            return $store->city;
        }

        // Buscar en ubicaciones si existen
        $location = $store->locations()->first();
        if ($location && $location->city) {
            return $location->city;
        }

        // Ciudades colombianas comunes como fallback
        $commonCities = [
            'Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena',
            'Bucaramanga', 'Pereira', 'Santa Marta', 'Ibagué', 'Manizales'
        ];

        return $commonCities[array_rand($commonCities)];
    }
}