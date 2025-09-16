<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Features\TenantAdmin\Models\SimpleShipping;
use App\Features\TenantAdmin\Models\SimpleShippingZone;
use App\Shared\Models\Store;

class SimpleShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunas tiendas para el ejemplo
        $stores = Store::limit(3)->get();
        
        foreach ($stores as $store) {
            // Crear configuración de envío básica
            $shipping = SimpleShipping::create([
                'store_id' => $store->id,
                
                // Recogida habilitada
                'pickup_enabled' => true,
                'pickup_instructions' => 'Puedes recoger tu pedido en nuestra tienda ubicada en ' . ($store->address ?? 'nuestra sede principal'),
                'pickup_preparation_time' => '1h',
                
                // Envío local habilitado
                'local_enabled' => true,
                'local_cost' => 3000,
                'local_free_from' => 50000, // Envío gratis desde $50,000
                'local_city' => $store->city ?? 'Sincelejo',
                'local_instructions' => 'Entregamos en toda la ciudad en horario de 8AM a 6PM',
                'local_preparation_time' => '2h',
                
                // Envío nacional habilitado (varía según el tipo de negocio)
                'national_enabled' => true,
                'national_free_from' => 100000, // Envío gratis desde $100,000
                'national_instructions' => 'Enviamos a nivel nacional mediante empresas de mensajería confiables',
                
                // Ciudades no listadas permitidas
                'allow_unlisted_cities' => true,
                'unlisted_cities_cost' => 12000,
                'unlisted_cities_message' => 'Contacta para confirmar disponibilidad en tu ciudad'
            ]);

            // Crear zonas de ejemplo basadas en el tipo de negocio
            $this->createZonesForStore($shipping, $store);
        }
    }

    /**
     * Crear zonas específicas según el tipo de tienda
     */
    private function createZonesForStore(SimpleShipping $shipping, Store $store): void
    {
        // Detectar tipo de negocio por nombre o crear zonas genéricas
        $storeName = strtolower($store->name ?? '');
        
        if (str_contains($storeName, 'restaurante') || str_contains($storeName, 'comida') || str_contains($storeName, 'pizza')) {
            // Restaurante: Solo zonas cercanas
            $this->createRestaurantZones($shipping);
        } elseif (str_contains($storeName, 'ropa') || str_contains($storeName, 'tienda') || str_contains($storeName, 'moda')) {
            // Tienda de ropa: Nacional completo
            $this->createClothingStoreZones($shipping);
        } else {
            // Negocio genérico: Zonas estándar
            $this->createGenericZones($shipping);
        }
    }

    /**
     * Zonas para restaurantes (solo regionales)
     */
    private function createRestaurantZones(SimpleShipping $shipping): void
    {
        // Zona 1: Ciudades cercanas
        SimpleShippingZone::create([
            'simple_shipping_id' => $shipping->id,
            'name' => 'Región Cercana',
            'cost' => 5000,
            'delivery_time' => '2-4h',
            'cities' => ['Montería', 'Corozal', 'Sampués', 'Since'],
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Zonas para tiendas de ropa (nacional completo)
     */
    private function createClothingStoreZones(SimpleShipping $shipping): void
    {
        // Zona 1: Ciudades principales
        SimpleShippingZone::create([
            'simple_shipping_id' => $shipping->id,
            'name' => 'Ciudades Principales',
            'cost' => 8000,
            'delivery_time' => '2-3dias',
            'cities' => ['Bogotá', 'Medellín', 'Cali', 'Bucaramanga', 'Pereira'],
            'sort_order' => 0,
            'is_active' => true,
        ]);

        // Zona 2: Costa Caribe
        SimpleShippingZone::create([
            'simple_shipping_id' => $shipping->id,
            'name' => 'Costa Caribe',
            'cost' => 10000,
            'delivery_time' => '3-5dias',
            'cities' => ['Barranquilla', 'Cartagena', 'Santa Marta', 'Montería', 'Valledupar', 'Riohacha'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Zona 3: Ciudades especiales
        SimpleShippingZone::create([
            'simple_shipping_id' => $shipping->id,
            'name' => 'Ciudades Especiales',
            'cost' => 15000,
            'delivery_time' => '5-7dias',
            'cities' => ['San Andrés', 'Leticia', 'Providencia'],
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }

    /**
     * Zonas genéricas para cualquier tipo de negocio
     */
    private function createGenericZones(SimpleShipping $shipping): void
    {
        // Zona 1: Nacional estándar
        SimpleShippingZone::create([
            'simple_shipping_id' => $shipping->id,
            'name' => 'Nacional Estándar',
            'cost' => 8000,
            'delivery_time' => '3-5dias',
            'cities' => [
                'Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Bucaramanga',
                'Pereira', 'Manizales', 'Ibagué', 'Cúcuta', 'Armenia'
            ],
            'sort_order' => 0,
            'is_active' => true,
        ]);

        // Zona 2: Costa y otros
        SimpleShippingZone::create([
            'simple_shipping_id' => $shipping->id,
            'name' => 'Costa y Regiones',
            'cost' => 10000,
            'delivery_time' => '5-7dias',
            'cities' => [
                'Cartagena', 'Santa Marta', 'Montería', 'Valledupar',
                'Sincelejo', 'Riohacha', 'Quibdó', 'Popayán'
            ],
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}

