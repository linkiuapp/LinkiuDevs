<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Features\TenantAdmin\Models\PaymentMethod;
use App\Features\TenantAdmin\Models\PaymentMethodConfig;
use App\Shared\Models\Store;

class PaymentMethodDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Initializes the 4 predefined payment methods for all existing stores.
     */
    public function run(): void
    {
        $this->command->info('🏗️ Inicializando métodos de pago predefinidos...');
        
        // Get all stores
        $stores = Store::all();
        $this->command->info("📊 Encontradas {$stores->count()} tiendas");
        
        foreach ($stores as $store) {
            $this->initializePaymentMethodsForStore($store);
        }
        
        $this->command->info('✅ Métodos de pago inicializados exitosamente');
    }
    
    /**
     * Initialize the 4 predefined payment methods for a specific store.
     *
     * @param Store $store
     * @return void
     */
    private function initializePaymentMethodsForStore(Store $store): void
    {
        $this->command->info("🏪 Configurando tienda: {$store->name} ({$store->slug})");
        
        // Define the 4 predefined payment methods
        $predefinedMethods = [
            [
                'type' => 'bank_transfer',
                'name' => 'Transferencia Bancaria',
                'instructions' => 'Realiza tu pago mediante transferencia bancaria a nuestras cuentas registradas',
                'is_active' => true, // Active by default
                'available_for_pickup' => true,
                'available_for_delivery' => true,
                'sort_order' => 1
            ],
            [
                'type' => 'cash',
                'name' => 'Efectivo',
                'instructions' => 'Paga en efectivo al momento de recibir tu pedido',
                'is_active' => false,
                'available_for_pickup' => true,
                'available_for_delivery' => true,
                'sort_order' => 2
            ],
            [
                'type' => 'card_terminal',
                'name' => 'Datáfono',
                'instructions' => 'Paga con tarjeta de crédito o débito usando nuestro datáfono',
                'is_active' => false,
                'available_for_pickup' => true,
                'available_for_delivery' => false, // Usually only for pickup
                'sort_order' => 3
            ],
            [
                'type' => 'cash_on_delivery',
                'name' => 'Contra Entrega',
                'instructions' => 'Paga cuando recibas tu pedido en la puerta de tu casa',
                'is_active' => false,
                'available_for_pickup' => false, // Only for delivery
                'available_for_delivery' => true,
                'sort_order' => 4
            ]
        ];
        
        $createdCount = 0;
        $updatedCount = 0;
        
        foreach ($predefinedMethods as $methodData) {
            $method = PaymentMethod::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'type' => $methodData['type']
                ],
                array_merge($methodData, ['store_id' => $store->id])
            );
            
            if ($method->wasRecentlyCreated) {
                $createdCount++;
                $this->command->info("  ✨ Creado: {$method->name}");
            } else {
                $updatedCount++;
                $this->command->info("  🔄 Actualizado: {$method->name}");
            }
        }
        
        // Ensure payment method config exists
        $config = PaymentMethodConfig::firstOrCreate(
            ['store_id' => $store->id],
            [
                'default_method_id' => null,
                'cash_change_available' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        // Set bank transfer as default if no default is set
        if (!$config->default_method_id) {
            $bankTransferMethod = PaymentMethod::where('store_id', $store->id)
                ->where('type', 'bank_transfer')
                ->where('is_active', true)
                ->first();
                
            if ($bankTransferMethod) {
                $config->update(['default_method_id' => $bankTransferMethod->id]);
                $this->command->info("  ⭐ Transferencia Bancaria establecida como predeterminada");
            }
        }
        
        $this->command->info("  📈 Resumen: {$createdCount} creados, {$updatedCount} actualizados");
    }
    
    /**
     * Initialize payment methods for a newly created store.
     * This method can be called from other parts of the application.
     *
     * @param Store $store
     * @return void
     */
    public static function initializeForNewStore(Store $store): void
    {
        $seeder = new self();
        $seeder->initializePaymentMethodsForStore($store);
    }
}

