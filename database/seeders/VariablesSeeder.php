<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Features\TenantAdmin\Models\ProductVariable;
use App\Features\TenantAdmin\Models\VariableOption;
use App\Shared\Models\Store;

class VariablesSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Obtener tiendas existentes
        $stores = Store::all();
        
        if ($stores->isEmpty()) {
            $this->command->info('No hay tiendas disponibles para crear variables.');
            return;
        }

        foreach ($stores as $store) {
            $this->createVariablesForStore($store);
        }
    }

    private function createVariablesForStore($store)
    {
        $this->command->info("Creando variables para tienda: {$store->name}");

        // Variables para restaurantes
        $this->createSizeVariable($store);
        $this->createExtrasVariable($store);
        $this->createDedicationVariable($store);
        $this->createQuantityVariable($store);
        
        // Variable para ecommerce (ropa)
        if (rand(1, 2) === 1) {
            $this->createColorVariable($store);
        }
    }

    private function createSizeVariable($store)
    {
        $variable = ProductVariable::create([
            'name' => 'Tamaño',
            'type' => 'radio',
            'store_id' => $store->id,
            'is_active' => true,
            'is_required_default' => true,
            'sort_order' => 0,
        ]);

        $sizes = [
            ['name' => 'Personal', 'price_modifier' => -5000],
            ['name' => 'Mediana', 'price_modifier' => 0],
            ['name' => 'Familiar', 'price_modifier' => 12000],
            ['name' => 'Extra Grande', 'price_modifier' => 18000],
        ];

        foreach ($sizes as $index => $size) {
            VariableOption::create([
                'variable_id' => $variable->id,
                'name' => $size['name'],
                'price_modifier' => $size['price_modifier'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    private function createExtrasVariable($store)
    {
        $variable = ProductVariable::create([
            'name' => 'Ingredientes Extra',
            'type' => 'checkbox',
            'store_id' => $store->id,
            'is_active' => true,
            'is_required_default' => false,
            'sort_order' => 1,
        ]);

        $extras = [
            ['name' => 'Queso Extra', 'price_modifier' => 2000],
            ['name' => 'Pepperoni', 'price_modifier' => 3000],
            ['name' => 'Champiñones', 'price_modifier' => 1500],
            ['name' => 'Aceitunas', 'price_modifier' => 1000],
            ['name' => 'Pimientos', 'price_modifier' => 1200],
            ['name' => 'Cebolla', 'price_modifier' => 800],
        ];

        foreach ($extras as $index => $extra) {
            VariableOption::create([
                'variable_id' => $variable->id,
                'name' => $extra['name'],
                'price_modifier' => $extra['price_modifier'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }

    private function createDedicationVariable($store)
    {
        ProductVariable::create([
            'name' => 'Dedicatoria',
            'type' => 'text',
            'store_id' => $store->id,
            'is_active' => true,
            'is_required_default' => false,
            'sort_order' => 2,
        ]);
    }

    private function createQuantityVariable($store)
    {
        ProductVariable::create([
            'name' => 'Cantidad de Porciones',
            'type' => 'numeric',
            'store_id' => $store->id,
            'is_active' => true,
            'is_required_default' => true,
            'sort_order' => 3,
            'min_value' => 1,
            'max_value' => 10,
        ]);
    }

    private function createColorVariable($store)
    {
        $variable = ProductVariable::create([
            'name' => 'Color',
            'type' => 'radio',
            'store_id' => $store->id,
            'is_active' => true,
            'is_required_default' => true,
            'sort_order' => 4,
        ]);

        $colors = [
            ['name' => 'Rojo', 'price_modifier' => 0, 'color_hex' => 'FF0000'],
            ['name' => 'Azul', 'price_modifier' => 0, 'color_hex' => '0000FF'],
            ['name' => 'Verde', 'price_modifier' => 0, 'color_hex' => '00FF00'],
            ['name' => 'Negro', 'price_modifier' => 2000, 'color_hex' => '000000'],
            ['name' => 'Blanco', 'price_modifier' => 0, 'color_hex' => 'FFFFFF'],
            ['name' => 'Amarillo', 'price_modifier' => 0, 'color_hex' => 'FFFF00'],
        ];

        foreach ($colors as $index => $color) {
            VariableOption::create([
                'variable_id' => $variable->id,
                'name' => $color['name'],
                'price_modifier' => $color['price_modifier'],
                'color_hex' => $color['color_hex'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
