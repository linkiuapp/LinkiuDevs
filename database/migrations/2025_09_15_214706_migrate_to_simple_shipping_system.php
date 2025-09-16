<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Log de la migración
        Schema::create('migration_log', function (Blueprint $table) {
            $table->id();
            $table->string('migration_name');
            $table->text('action');
            $table->json('details')->nullable();
            $table->timestamps();
        });

        // Registrar inicio de migración
        DB::table('migration_log')->insert([
            'migration_name' => 'migrate_to_simple_shipping_system',
            'action' => 'Migration started',
            'details' => json_encode(['timestamp' => now()]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 1. Eliminar tablas del sistema viejo de shipping si existen
        $oldTables = [
            'shipping_methods',
            'shipping_zones', 
            'shipping_method_configs',
            'shipping_method_config'
        ];

        foreach ($oldTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
                DB::table('migration_log')->insert([
                    'migration_name' => 'migrate_to_simple_shipping_system',
                    'action' => "Dropped old table: {$table}",
                    'details' => json_encode(['table' => $table, 'timestamp' => now()]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // 2. Verificar que las nuevas tablas existen
        if (!Schema::hasTable('simple_shipping')) {
            throw new Exception('Table simple_shipping does not exist. Please run the simple shipping migrations first.');
        }

        if (!Schema::hasTable('simple_shipping_zones')) {
            throw new Exception('Table simple_shipping_zones does not exist. Please run the simple shipping migrations first.');
        }

        // 3. Crear configuración por defecto para stores existentes
        $stores = DB::table('stores')->get();
        
        foreach ($stores as $store) {
            // Verificar si ya tiene configuración
            $existingConfig = DB::table('simple_shipping')
                ->where('store_id', $store->id)
                ->first();

            if (!$existingConfig) {
                DB::table('simple_shipping')->insert([
                    'store_id' => $store->id,
                    'pickup_enabled' => true,
                    'pickup_instructions' => 'Recoge tu pedido en nuestra tienda.',
                    'pickup_preparation_time' => '2h',
                    'local_enabled' => true,
                    'local_cost' => 5000.00,
                    'local_free_from' => 50000.00,
                    'local_city' => 'Tu Ciudad',
                    'local_instructions' => 'Envío local disponible.',
                    'local_preparation_time' => '24h',
                    'national_enabled' => false,
                    'national_free_from' => 100000.00,
                    'national_instructions' => 'Envío nacional disponible.',
                    'allow_unlisted_cities' => false,
                    'unlisted_cities_cost' => 15000.00,
                    'unlisted_cities_message' => 'Consulta costos de envío.',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('migration_log')->insert([
                    'migration_name' => 'migrate_to_simple_shipping_system',
                    'action' => "Created default shipping config for store: {$store->name}",
                    'details' => json_encode([
                        'store_id' => $store->id,
                        'store_name' => $store->name,
                        'timestamp' => now()
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Registrar fin de migración
        DB::table('migration_log')->insert([
            'migration_name' => 'migrate_to_simple_shipping_system',
            'action' => 'Migration completed successfully',
            'details' => json_encode([
                'stores_processed' => $stores->count(),
                'timestamp' => now()
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En caso de rollback, no recrear las tablas viejas
        // Solo limpiar la data del nuevo sistema
        
        DB::table('migration_log')->insert([
            'migration_name' => 'migrate_to_simple_shipping_system',
            'action' => 'Rollback started',
            'details' => json_encode(['timestamp' => now()]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Limpiar configuraciones
        DB::table('simple_shipping_zones')->truncate();
        DB::table('simple_shipping')->truncate();

        // Drop migration log
        Schema::dropIfExists('migration_log');
    }
};