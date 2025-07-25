<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class DiagnoseProductionIssues extends Command
{
    protected $signature = 'diagnose:production {--store_slug=} {--clear-cache}';
    protected $description = 'Diagnose production-specific issues that work in localhost';

    public function handle()
    {
        $this->info('🚀 DIAGNÓSTICO DE PROBLEMAS EN PRODUCCIÓN');
        $this->newLine();

        if ($this->option('clear-cache')) {
            $this->clearAllCaches();
            $this->newLine();
        }

        // 1. Verificar entorno
        $this->checkEnvironment();
        $this->newLine();

        // 2. Verificar base de datos
        $this->checkDatabase();
        $this->newLine();

        // 3. Verificar permisos
        $this->checkPermissions();
        $this->newLine();

        // 4. Verificar migraciones
        $this->checkMigrations();
        $this->newLine();

        // 5. Verificar autoload
        $this->checkAutoload();
        $this->newLine();

        // 6. Verificar logs
        $this->checkLogs();
        $this->newLine();

        // 7. Probar tienda específica si se proporciona
        if ($storeSlug = $this->option('store_slug')) {
            $this->testProductionStore($storeSlug);
            $this->newLine();
        }

        $this->info('✅ Diagnóstico de producción completado');
    }

    private function clearAllCaches()
    {
        $this->info('🧹 Limpiando caches...');

        try {
            Artisan::call('config:clear');
            $this->line('✅ Config cache cleared');
        } catch (\Exception $e) {
            $this->error('❌ Error clearing config cache: ' . $e->getMessage());
        }

        try {
            Artisan::call('route:clear');
            $this->line('✅ Route cache cleared');
        } catch (\Exception $e) {
            $this->error('❌ Error clearing route cache: ' . $e->getMessage());
        }

        try {
            Artisan::call('view:clear');
            $this->line('✅ View cache cleared');
        } catch (\Exception $e) {
            $this->error('❌ Error clearing view cache: ' . $e->getMessage());
        }

        try {
            Cache::flush();
            $this->line('✅ Application cache cleared');
        } catch (\Exception $e) {
            $this->error('❌ Error clearing app cache: ' . $e->getMessage());
        }
    }

    private function checkEnvironment()
    {
        $this->info('🌍 Verificando configuración de entorno...');

        $this->line('📊 Variables críticas:');
        $this->line('   APP_ENV: ' . env('APP_ENV', 'NOT_SET'));
        $this->line('   APP_DEBUG: ' . (env('APP_DEBUG') ? 'true' : 'false'));
        $this->line('   APP_URL: ' . env('APP_URL', 'NOT_SET'));
        
        $dbConnection = env('DB_CONNECTION', 'NOT_SET');
        $this->line('   DB_CONNECTION: ' . $dbConnection);
        
        if ($dbConnection !== 'NOT_SET') {
            $this->line('   DB_HOST: ' . env('DB_HOST', 'NOT_SET'));
            $this->line('   DB_DATABASE: ' . env('DB_DATABASE', 'NOT_SET'));
            $this->line('   DB_USERNAME: ' . env('DB_USERNAME', 'NOT_SET'));
        }

        // Verificar diferencias críticas
        if (env('APP_DEBUG') == true) {
            $this->warn('⚠️  APP_DEBUG está en true en producción');
        }

        if (env('APP_ENV') !== 'production') {
            $this->warn('⚠️  APP_ENV no está configurado como production');
        }
    }

    private function checkDatabase()
    {
        $this->info('🗄️  Verificando conexión a base de datos...');

        try {
            $connection = DB::connection();
            $connection->getPdo();
            $this->line('✅ Conexión a BD exitosa');

            // Verificar tablas críticas
            $criticalTables = ['users', 'stores', 'plans', 'subscriptions'];
            foreach ($criticalTables as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    $this->line("✅ Tabla {$table}: {$count} registros");
                } else {
                    $this->error("❌ Tabla {$table} NO existe");
                }
            }

            // Verificar columnas críticas
            if (Schema::hasTable('stores')) {
                $storeColumns = Schema::getColumnListing('stores');
                $requiredColumns = ['id', 'slug', 'name', 'status', 'plan_id'];
                
                foreach ($requiredColumns as $column) {
                    if (!in_array($column, $storeColumns)) {
                        $this->error("❌ Columna stores.{$column} NO existe");
                    }
                }
            }

            if (Schema::hasTable('users')) {
                $userColumns = Schema::getColumnListing('users');
                if (!in_array('store_id', $userColumns)) {
                    $this->error('❌ Columna users.store_id NO existe');
                }
                if (!in_array('role', $userColumns)) {
                    $this->error('❌ Columna users.role NO existe');
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Error de conexión a BD: ' . $e->getMessage());
        }
    }

    private function checkPermissions()
    {
        $this->info('🔐 Verificando permisos de archivos...');

        $criticalPaths = [
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'bootstrap/cache',
            'public/storage'
        ];

        foreach ($criticalPaths as $path) {
            $fullPath = base_path($path);
            
            if (!file_exists($fullPath)) {
                $this->error("❌ Directorio {$path} NO existe");
                continue;
            }

            if (!is_writable($fullPath)) {
                $this->error("❌ Directorio {$path} NO es escribible");
            } else {
                $this->line("✅ {$path} escribible");
            }
        }

        // Verificar symlink de storage
        $storageLink = public_path('storage');
        if (is_link($storageLink)) {
            $this->line('✅ Symlink storage existe');
        } else {
            $this->warn('⚠️  Symlink storage NO existe - ejecutar: php artisan storage:link');
        }
    }

    private function checkMigrations()
    {
        $this->info('📦 Verificando migraciones...');

        try {
            $pendingMigrations = Artisan::call('migrate:status');
            $this->line('✅ Estado de migraciones verificado');
            
            // Verificar si hay migraciones pendientes
            $output = Artisan::output();
            if (strpos($output, 'Pending') !== false) {
                $this->warn('⚠️  Hay migraciones pendientes:');
                $this->line($output);
            } else {
                $this->line('✅ Todas las migraciones están al día');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error verificando migraciones: ' . $e->getMessage());
        }
    }

    private function checkAutoload()
    {
        $this->info('🔄 Verificando autoload...');

        $composerLock = base_path('composer.lock');
        if (!file_exists($composerLock)) {
            $this->error('❌ composer.lock NO existe');
        } else {
            $this->line('✅ composer.lock existe');
        }

        $vendorAutoload = base_path('vendor/autoload.php');
        if (!file_exists($vendorAutoload)) {
            $this->error('❌ vendor/autoload.php NO existe - ejecutar: composer install');
        } else {
            $this->line('✅ vendor/autoload.php existe');
        }

        // Verificar clases críticas
        $criticalClasses = [
            'App\Shared\Models\Store',
            'App\Shared\Models\User',
            'App\Shared\Services\TenantService',
            'App\Shared\Middleware\TenantIdentificationMiddleware'
        ];

        foreach ($criticalClasses as $class) {
            if (class_exists($class)) {
                $this->line("✅ Clase {$class} cargable");
            } else {
                $this->error("❌ Clase {$class} NO encontrada");
            }
        }
    }

    private function checkLogs()
    {
        $this->info('📝 Verificando logs de errores...');

        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            $this->warn('⚠️  No hay archivo de log');
            return;
        }

        $this->line('✅ Archivo de log encontrado');

        // Buscar errores 500 recientes (últimas 50 líneas)
        $logContent = file_get_contents($logFile);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -50);
        
        $errors500 = [];
        $tenantErrors = [];
        $fatalErrors = [];

        foreach ($recentLines as $line) {
            if (strpos($line, '500') !== false || strpos($line, 'ERROR') !== false) {
                $errors500[] = $line;
            }
            if (strpos($line, 'Tenant') !== false || strpos($line, 'Store') !== false) {
                $tenantErrors[] = $line;
            }
            if (strpos($line, 'FATAL') !== false || strpos($line, 'Class not found') !== false) {
                $fatalErrors[] = $line;
            }
        }

        if (!empty($errors500)) {
            $this->warn("⚠️  Errores 500 recientes encontrados:");
            foreach (array_slice($errors500, -3) as $error) {
                $this->line("   " . substr($error, 0, 120) . '...');
            }
        }

        if (!empty($tenantErrors)) {
            $this->warn("⚠️  Errores relacionados con Tenant/Store:");
            foreach (array_slice($tenantErrors, -3) as $error) {
                $this->line("   " . substr($error, 0, 120) . '...');
            }
        }

        if (!empty($fatalErrors)) {
            $this->error("❌ Errores fatales encontrados:");
            foreach (array_slice($fatalErrors, -2) as $error) {
                $this->line("   " . substr($error, 0, 120) . '...');
            }
        }

        if (empty($errors500) && empty($tenantErrors) && empty($fatalErrors)) {
            $this->line('✅ No se encontraron errores críticos recientes');
        }
    }

    private function testProductionStore($storeSlug)
    {
        $this->info("🧪 Probando tienda en producción: {$storeSlug}");

        try {
            // Test 1: Buscar tienda
            $store = Store::where('slug', $storeSlug)->first();
            if (!$store) {
                $this->error("❌ Tienda '{$storeSlug}' NO encontrada");
                return;
            }
            $this->line("✅ Tienda encontrada: {$store->name}");

            // Test 2: Verificar admin
            $adminCount = $store->admins()->count();
            if ($adminCount == 0) {
                $this->error('❌ Tienda sin administradores');
            } else {
                $this->line("✅ Administradores: {$adminCount}");
            }

            // Test 3: Verificar relaciones
            if ($store->plan) {
                $this->line("✅ Plan asignado: {$store->plan->name}");
            } else {
                $this->error('❌ Tienda sin plan asignado');
            }

            // Test 4: Probar query compleja (como el middleware)
            $storeWithRelations = Store::where('slug', $storeSlug)
                ->withCount(['products', 'categories', 'variables', 'sliders'])
                ->with('plan')
                ->first();

            if ($storeWithRelations) {
                $this->line('✅ Query con relaciones exitosa');
                $this->line("   Productos: {$storeWithRelations->products_count}");
                $this->line("   Categorías: {$storeWithRelations->categories_count}");
            } else {
                $this->error('❌ Query con relaciones falló');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error probando tienda: ' . $e->getMessage());
            $this->line('   Archivo: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
} 