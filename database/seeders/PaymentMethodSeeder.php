<?php

namespace Database\Seeders;

use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Shared\Models\Store;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las tiendas
        $stores = Store::all();

        foreach ($stores as $store) {
            // Crear método de pago en efectivo
            PaymentMethod::create([
                'store_id' => $store->id,
                'type' => 'cash',
                'name' => 'Efectivo',
                'is_active' => true,
                'sort_order' => 1,
                'instructions' => 'Paga en efectivo al momento de la entrega.',
                'available_for_pickup' => true,
                'available_for_delivery' => true,
            ]);

            // Crear método de pago por transferencia bancaria
            PaymentMethod::create([
                'store_id' => $store->id,
                'type' => 'bank_transfer',
                'name' => 'Transferencia Bancaria',
                'is_active' => true,
                'sort_order' => 2,
                'instructions' => 'Realiza una transferencia a nuestra cuenta bancaria y envía el comprobante.',
                'available_for_pickup' => true,
                'available_for_delivery' => true,
            ]);

            // Crear método de pago con datáfono
            PaymentMethod::create([
                'store_id' => $store->id,
                'type' => 'card_terminal',
                'name' => 'Datáfono',
                'is_active' => true,
                'sort_order' => 3,
                'instructions' => 'Paga con tarjeta de crédito o débito al momento de la entrega.',
                'available_for_pickup' => true,
                'available_for_delivery' => false,
            ]);
        }
    }
}