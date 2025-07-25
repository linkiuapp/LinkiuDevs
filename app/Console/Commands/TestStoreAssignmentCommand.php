<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\User;
use App\Shared\Models\Store;
use Illuminate\Support\Facades\DB;

class TestStoreAssignmentCommand extends Command
{
    protected $signature = 'auth:test-store-assignment {--cleanup : Limpiar datos de prueba}';
    protected $description = 'Probar el sistema automático de asignación de store_id';

    public function handle()
    {
        if ($this->option('cleanup')) {
            return $this->cleanup();
        }

        $this->info("🧪 PROBANDO SISTEMA AUTOMÁTICO DE ASIGNACIÓN DE STORE_ID");
        $this->line("");

        // 1. Verificar estado inicial
        $this->info("📊 ESTADO INICIAL:");
        $this->showCurrentState();

        // 2. Crear usuario de prueba sin store_id
        $this->line("");
        $this->info("🔧 CREANDO USUARIO DE PRUEBA...");
        
        $testUser = $this->createTestUser();
        
        $this->line("✅ Usuario de prueba creado:");
        $this->table(['ID', 'Email', 'Role', 'Store ID'], [
            [$testUser->id, $testUser->email, $testUser->role, $testUser->store_id ?? 'NULL']
        ]);

        // 3. Verificar si el Observer asignó automáticamente
        $testUser->refresh();
        
        $this->line("");
        if ($testUser->store_id) {
            $store = Store::find($testUser->store_id);
            $this->info("🎉 ¡ÉXITO! El Observer asignó automáticamente:");
            $this->line("   Store ID: {$testUser->store_id}");
            $this->line("   Store Name: {$store->name}");
        } else {
            $this->warn("⚠️ El Observer NO asignó automáticamente el store_id");
            $this->line("   Esto puede ocurrir si no hay tiendas sin admin disponibles");
        }

        // 4. Probar comando de corrección
        $this->line("");
        $this->info("🔧 PROBANDO COMANDO DE CORRECCIÓN...");
        
        // Si el Observer no asignó, usar el comando de corrección
        if (!$testUser->store_id) {
            $this->call('auth:fix-store-admins', ['--force' => true]);
            $testUser->refresh();
            
            if ($testUser->store_id) {
                $store = Store::find($testUser->store_id);
                $this->info("✅ Comando de corrección funcionó:");
                $this->line("   Store ID: {$testUser->store_id}");
                $this->line("   Store Name: {$store->name}");
            } else {
                $this->error("❌ El comando de corrección también falló");
            }
        } else {
            $this->info("ℹ️ No es necesario usar el comando de corrección (Observer ya funcionó)");
        }

        // 5. Mostrar estado final
        $this->line("");
        $this->info("📊 ESTADO FINAL:");
        $this->showCurrentState();

        // 6. Opción de limpieza
        $this->line("");
        $this->warn("🧹 Para limpiar los datos de prueba, ejecuta:");
        $this->line("php artisan auth:test-store-assignment --cleanup");

        return 0;
    }

    private function createTestUser(): User
    {
        $email = 'test-store-admin-' . time() . '@example.com';
        
        return User::create([
            'name' => 'Test Store Admin',
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'store_admin',
            // NO asignar store_id intencionalmente para probar el Observer
        ]);
    }

    private function showCurrentState(): void
    {
        $storeAdmins = User::where('role', 'store_admin')->with('store')->get();
        $storesWithoutAdmin = Store::whereDoesntHave('admins')->count();
        $totalStores = Store::count();

        $this->line("• Total usuarios store_admin: " . $storeAdmins->count());
        $this->line("• Con store_id asignado: " . $storeAdmins->whereNotNull('store_id')->count());
        $this->line("• Sin store_id: " . $storeAdmins->whereNull('store_id')->count());
        $this->line("• Total tiendas: {$totalStores}");
        $this->line("• Tiendas sin admin: {$storesWithoutAdmin}");

        if ($storeAdmins->whereNull('store_id')->count() > 0) {
            $this->line("");
            $this->warn("Usuarios sin store_id:");
            $problematic = $storeAdmins->whereNull('store_id');
            $this->table(['ID', 'Email', 'Store ID'], 
                $problematic->map(fn($user) => [
                    $user->id, 
                    $user->email, 
                    $user->store_id ?? 'NULL ❌'
                ])->toArray()
            );
        }
    }

    private function cleanup(): int
    {
        $this->info("🧹 LIMPIANDO DATOS DE PRUEBA...");
        
        $testUsers = User::where('email', 'like', 'test-store-admin-%@example.com')->get();
        
        if ($testUsers->isEmpty()) {
            $this->info("No hay datos de prueba para limpiar");
            return 0;
        }

        $this->line("Usuarios de prueba encontrados:");
        $this->table(['ID', 'Email', 'Created'], 
            $testUsers->map(fn($user) => [
                $user->id,
                $user->email,
                $user->created_at->format('Y-m-d H:i:s')
            ])->toArray()
        );

        if ($this->confirm('¿Eliminar estos usuarios de prueba?')) {
            DB::beginTransaction();
            try {
                $count = $testUsers->count();
                User::whereIn('id', $testUsers->pluck('id'))->delete();
                DB::commit();
                
                $this->info("✅ {$count} usuarios de prueba eliminados");
            } catch (\Exception $e) {
                DB::rollback();
                $this->error("❌ Error al eliminar usuarios: " . $e->getMessage());
                return 1;
            }
        }

        return 0;
    }
}
