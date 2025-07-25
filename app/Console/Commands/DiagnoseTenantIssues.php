<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use App\Shared\Services\TenantService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DiagnoseTenantIssues extends Command
{
    protected $signature = 'diagnose:tenant-issues {store_slug?}';
    protected $description = 'Diagnose tenant identification and authentication issues';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO DEL SISTEMA DE TENANTS');
        $this->newLine();

        // 1. Verificar estructura de base de datos
        $this->checkDatabaseStructure();
        $this->newLine();

        // 2. Verificar tiendas
        $this->checkStores();
        $this->newLine();

        // 3. Verificar usuarios store_admin
        $this->checkStoreAdmins();
        $this->newLine();

        // 4. Verificar TenantService
        $this->checkTenantService();
        $this->newLine();

        // 5. Si se proporciona un slug específico, probarlo
        if ($storeSlug = $this->argument('store_slug')) {
            $this->testSpecificStore($storeSlug);
        }

        $this->info('✅ Diagnóstico completado');
    }

    private function checkDatabaseStructure()
    {
        $this->info('📊 Verificando estructura de base de datos...');

        // Verificar tabla stores
        if (!Schema::hasTable('stores')) {
            $this->error('❌ Tabla stores no existe');
            return;
        }

        $storeColumns = Schema::getColumnListing('stores');
        $this->line('✅ Tabla stores existe');
        $this->line('   Columnas: ' . implode(', ', $storeColumns));

        // Verificar si tenant_id existe
        if (in_array('tenant_id', $storeColumns)) {
            $this->line('✅ Columna tenant_id existe');
        } else {
            $this->warn('⚠️  Columna tenant_id NO existe (TenantService puede fallar)');
        }

        // Verificar tabla users
        if (!Schema::hasTable('users')) {
            $this->error('❌ Tabla users no existe');
            return;
        }

        $userColumns = Schema::getColumnListing('users');
        $this->line('✅ Tabla users existe');
        $this->line('   Columnas: ' . implode(', ', $userColumns));

        // Verificar si store_id existe en users
        if (in_array('store_id', $userColumns)) {
            $this->line('✅ Columna store_id en users existe');
        } else {
            $this->error('❌ Columna store_id en users NO existe');
        }
    }

    private function checkStores()
    {
        $this->info('🏪 Verificando tiendas...');

        $totalStores = Store::count();
        $this->line("📊 Total de tiendas: {$totalStores}");

        if ($totalStores === 0) {
            $this->warn('⚠️  No hay tiendas en la base de datos');
            return;
        }

        // Verificar tiendas recientes
        $recentStores = Store::latest()->take(5)->get();
        $this->line('🕒 Últimas 5 tiendas creadas:');
        
        foreach ($recentStores as $store) {
            $adminCount = $store->admins()->count();
            $status = $adminCount > 0 ? '✅' : '❌';
            $this->line("   {$status} {$store->slug} (ID: {$store->id}) - Admins: {$adminCount}");
            
            if ($adminCount === 0) {
                $this->warn("      ⚠️  La tienda '{$store->slug}' no tiene administradores");
            }
        }

        // Verificar tiendas sin admin
        $storesWithoutAdmin = Store::whereDoesntHave('admins')->count();
        if ($storesWithoutAdmin > 0) {
            $this->warn("⚠️  {$storesWithoutAdmin} tiendas sin administradores");
        }
    }

    private function checkStoreAdmins()
    {
        $this->info('👤 Verificando usuarios store_admin...');

        $totalStoreAdmins = User::where('role', 'store_admin')->count();
        $this->line("📊 Total store_admins: {$totalStoreAdmins}");

        // Verificar store_admins sin store_id
        $adminsWithoutStore = User::where('role', 'store_admin')
            ->whereNull('store_id')
            ->count();

        if ($adminsWithoutStore > 0) {
            $this->error("❌ {$adminsWithoutStore} store_admins sin store_id asignado");
            
            $orphanAdmins = User::where('role', 'store_admin')
                ->whereNull('store_id')
                ->get();
                
            foreach ($orphanAdmins as $admin) {
                $this->line("   - {$admin->email} (ID: {$admin->id})");
            }
        } else {
            $this->line('✅ Todos los store_admins tienen store_id');
        }

        // Verificar store_admins con store_id inválido
        $adminsWithInvalidStore = User::where('role', 'store_admin')
            ->whereNotNull('store_id')
            ->whereDoesntHave('store')
            ->count();

        if ($adminsWithInvalidStore > 0) {
            $this->error("❌ {$adminsWithInvalidStore} store_admins con store_id inválido");
        } else {
            $this->line('✅ Todos los store_admins tienen store_id válido');
        }
    }

    private function checkTenantService()
    {
        $this->info('🔧 Verificando TenantService...');

        try {
            $tenantService = app(TenantService::class);
            $this->line('✅ TenantService se puede instanciar');

            // Verificar si depende de tenant_id
            $reflection = new \ReflectionClass($tenantService);
            $methods = $reflection->getMethods();
            
            $usesTenantId = false;
            foreach ($methods as $method) {
                $source = file_get_contents($method->getFileName());
                if (strpos($source, 'tenant_id') !== false) {
                    $usesTenantId = true;
                    break;
                }
            }

            if ($usesTenantId && !Schema::hasColumn('stores', 'tenant_id')) {
                $this->error('❌ TenantService usa tenant_id pero la columna no existe en stores');
                $this->line('   Sugerencia: Agregar migración para tenant_id o modificar TenantService');
            } else {
                $this->line('✅ TenantService configurado correctamente');
            }

        } catch (\Exception $e) {
            $this->error('❌ Error al instanciar TenantService: ' . $e->getMessage());
        }
    }

    private function testSpecificStore($storeSlug)
    {
        $this->info("🧪 Probando tienda específica: {$storeSlug}");

        // Buscar la tienda
        $store = Store::where('slug', $storeSlug)->first();

        if (!$store) {
            $this->error("❌ Tienda '{$storeSlug}' no encontrada");
            return;
        }

        $this->line("✅ Tienda encontrada: {$store->name} (ID: {$store->id})");

        // Verificar admin de la tienda
        $admins = $store->admins()->get();
        $this->line("👤 Administradores: {$admins->count()}");

        foreach ($admins as $admin) {
            $this->line("   - {$admin->email} (ID: {$admin->id})");
        }

        if ($admins->isEmpty()) {
            $this->error('❌ La tienda no tiene administradores');
            
            // Sugerir solución
            $orphanAdmins = User::where('role', 'store_admin')
                ->whereNull('store_id')
                ->get();
                
            if ($orphanAdmins->isNotEmpty()) {
                $this->line('💡 Usuarios store_admin sin asignar disponibles:');
                foreach ($orphanAdmins as $admin) {
                    $this->line("   - {$admin->email} (usar: php artisan fix:store-admins)");
                }
            }
        }

        // Probar TenantService con esta tienda
        try {
            $tenantService = app(TenantService::class);
            $tenantService->setTenant($store);
            $this->line('✅ TenantService puede establecer esta tienda como tenant');
        } catch (\Exception $e) {
            $this->error('❌ Error al establecer tenant: ' . $e->getMessage());
        }

        // Simular middleware
        $this->line('🔄 Simulando TenantIdentificationMiddleware...');
        try {
            // Buscar con los mismos parámetros que el middleware
            $testStore = Store::where('slug', $storeSlug)
                ->withCount(['products', 'categories', 'variables', 'sliders'])
                ->with('plan')
                ->first();

            if ($testStore) {
                $this->line('✅ Middleware puede encontrar y cargar la tienda');
            } else {
                $this->error('❌ Middleware no puede encontrar la tienda');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error en middleware simulation: ' . $e->getMessage());
        }
    }
}
