<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Features\TenantAdmin\Models\SimpleShipping;
use App\Features\TenantAdmin\Models\SimpleShippingZone;
use App\Shared\Models\Store;
use Illuminate\Support\Facades\Schema;

class DeployShippingSystem extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shipping:deploy {--verify : Solo verificar sin hacer cambios}';

    /**
     * The description of the console command.
     */
    protected $description = 'Deploy y configurar el nuevo sistema de envíos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 DEPLOYMENT: Nuevo Sistema de Envíos');
        $this->info('==========================================');

        if ($this->option('verify')) {
            return $this->verifyOnly();
        }

        // 1. Verificar prerrequisitos
        if (!$this->checkPrerequisites()) {
            return Command::FAILURE;
        }

        // 2. Ejecutar migración
        if (!$this->runMigrations()) {
            return Command::FAILURE;
        }

        // 3. Configurar tiendas
        if (!$this->configureStores()) {
            return Command::FAILURE;
        }

        // 4. Verificar deployment
        if (!$this->verifyDeployment()) {
            return Command::FAILURE;
        }

        $this->info('');
        $this->info('🎉 DEPLOYMENT COMPLETADO EXITOSAMENTE!');
        $this->info('El nuevo sistema de envíos está listo para usar.');
        
        return Command::SUCCESS;
    }

    private function checkPrerequisites(): bool
    {
        $this->info('📋 Verificando prerrequisitos...');

        // Verificar que las migraciones existen
        $migrations = [
            'create_simple_shipping_table',
            'create_simple_shipping_zones_table'
        ];

        foreach ($migrations as $migration) {
            if (!$this->migrationExists($migration)) {
                $this->error("❌ Migración {$migration} no encontrada");
                return false;
            }
            $this->line("✅ Migración {$migration} encontrada");
        }

        return true;
    }

    private function runMigrations(): bool
    {
        $this->info('📊 Ejecutando migraciones...');

        try {
            $this->call('migrate', ['--force' => true]);
            $this->line('✅ Migraciones ejecutadas correctamente');
            return true;
        } catch (\Exception $e) {
            $this->error("❌ Error ejecutando migraciones: " . $e->getMessage());
            return false;
        }
    }

    private function configureStores(): bool
    {
        $this->info('🏪 Configurando tiendas...');

        try {
            $stores = Store::all();
            $configured = 0;
            $skipped = 0;

            $progressBar = $this->output->createProgressBar($stores->count());
            $progressBar->start();

            foreach ($stores as $store) {
                $progressBar->advance();

                // Verificar si ya tiene configuración
                if (SimpleShipping::where('store_id', $store->id)->exists()) {
                    $skipped++;
                    continue;
                }

                // Crear configuración por defecto
                SimpleShipping::create([
                    'store_id' => $store->id,
                    'pickup_enabled' => true,
                    'pickup_instructions' => 'Recoge tu pedido en nuestra tienda.',
                    'pickup_preparation_time' => '2h',
                    'local_enabled' => true,
                    'local_cost' => 5000.00,
                    'local_free_from' => 50000.00,
                    'local_city' => $store->name ?? 'Tu Ciudad',
                    'local_instructions' => 'Envío local disponible.',
                    'local_preparation_time' => '24h',
                    'national_enabled' => false,
                    'national_free_from' => 100000.00,
                    'national_instructions' => 'Envío nacional disponible.',
                    'allow_unlisted_cities' => false,
                    'unlisted_cities_cost' => 15000.00,
                    'unlisted_cities_message' => 'Consulta costos de envío.',
                ]);

                $configured++;
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->line("✅ Tiendas configuradas: {$configured}");
            $this->line("⚠️  Tiendas omitidas (ya configuradas): {$skipped}");
            
            return true;
        } catch (\Exception $e) {
            $this->error("❌ Error configurando tiendas: " . $e->getMessage());
            return false;
        }
    }

    private function verifyDeployment(): bool
    {
        $this->info('🔍 Verificando deployment...');

        $errors = 0;

        // Verificar tablas
        if (!Schema::hasTable('simple_shipping')) {
            $this->error('❌ Tabla simple_shipping no existe');
            $errors++;
        } else {
            $this->line('✅ Tabla simple_shipping existe');
        }

        if (!Schema::hasTable('simple_shipping_zones')) {
            $this->error('❌ Tabla simple_shipping_zones no existe');
            $errors++;
        } else {
            $this->line('✅ Tabla simple_shipping_zones existe');
        }

        // Verificar configuraciones
        $stores = Store::count();
        $configured = SimpleShipping::count();

        if ($configured < $stores) {
            $this->error("❌ Solo {$configured} de {$stores} tiendas configuradas");
            $errors++;
        } else {
            $this->line("✅ Todas las {$stores} tiendas configuradas");
        }

        // Verificar controlador
        $controllerPath = app_path('Features/TenantAdmin/Controllers/SimpleShippingController.php');
        if (!file_exists($controllerPath)) {
            $this->error('❌ SimpleShippingController no existe');
            $errors++;
        } else {
            $this->line('✅ SimpleShippingController existe');
        }

        return $errors === 0;
    }

    private function verifyOnly(): int
    {
        $this->info('🔍 VERIFICACIÓN: Sistema de Envíos');
        $this->info('=================================');

        $this->verifyDeployment();

        // Mostrar estadísticas
        $this->info('');
        $this->info('📊 ESTADÍSTICAS:');
        
        $stores = Store::count();
        $configured = SimpleShipping::count();
        $zones = SimpleShippingZone::count();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Tiendas totales', $stores],
                ['Tiendas configuradas', $configured],
                ['Zonas de envío', $zones],
                ['Cobertura', round(($configured / max($stores, 1)) * 100, 1) . '%']
            ]
        );

        return Command::SUCCESS;
    }

    private function migrationExists(string $migrationName): bool
    {
        $migrationFiles = glob(database_path('migrations/*.php'));
        
        foreach ($migrationFiles as $file) {
            if (strpos(basename($file), $migrationName) !== false) {
                return true;
            }
        }
        
        return false;
    }
}