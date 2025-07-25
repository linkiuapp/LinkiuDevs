<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Support\Facades\DB;

class FixStoreAdminsCommand extends Command
{
    protected $signature = 'fix:store-admins {--store-slug=} {--create-missing} {--dry-run}';
    protected $description = 'Fix stores without administrators';

    public function handle()
    {
        $this->info('🔧 ARREGLANDO TIENDAS SIN ADMINISTRADORES');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $createMissing = $this->option('create-missing');
        $specificSlug = $this->option('store-slug');

        if ($isDryRun) {
            $this->warn('🔍 MODO DRY-RUN - Solo mostrando qué haría');
            $this->newLine();
        }

        // Buscar tiendas sin administradores
        $query = Store::whereDoesntHave('admins');
        
        if ($specificSlug) {
            $query->where('slug', $specificSlug);
        }

        $storesWithoutAdmins = $query->get();

        if ($storesWithoutAdmins->isEmpty()) {
            $this->info('✅ Todas las tiendas tienen administradores asignados');
            return 0;
        }

        $this->warn("⚠️  Encontradas {$storesWithoutAdmins->count()} tiendas sin administradores:");
        $this->newLine();

        foreach ($storesWithoutAdmins as $store) {
            $this->line("🏪 Tienda: {$store->name} (slug: {$store->slug}, ID: {$store->id})");
            
            if ($createMissing) {
                if (!$isDryRun) {
                    $this->createAdminForStore($store);
                } else {
                    $this->line("   🔨 Crearía admin para esta tienda");
                }
            } else {
                // Buscar usuarios store_admin sin tienda asignada
                $orphanAdmins = User::where('role', 'store_admin')
                    ->whereNull('store_id')
                    ->get();

                if ($orphanAdmins->isNotEmpty()) {
                    $this->line("   💡 Usuarios store_admin disponibles para asignar:");
                    foreach ($orphanAdmins as $admin) {
                        $this->line("      - {$admin->email} (ID: {$admin->id})");
                    }
                    
                    if ($this->confirm("¿Asignar {$orphanAdmins->first()->email} a {$store->slug}?")) {
                        if (!$isDryRun) {
                            $this->assignAdminToStore($orphanAdmins->first(), $store);
                        } else {
                            $this->line("   🔨 Asignaría {$orphanAdmins->first()->email} a {$store->slug}");
                        }
                    }
                } else {
                    $this->line("   ❌ No hay usuarios store_admin disponibles para asignar");
                    if ($this->confirm("¿Crear nuevo administrador para {$store->slug}?")) {
                        if (!$isDryRun) {
                            $this->createAdminForStore($store);
                        } else {
                            $this->line("   🔨 Crearía nuevo admin para {$store->slug}");
                        }
                    }
                }
            }
            $this->newLine();
        }

        if (!$isDryRun) {
            $this->info('✅ Proceso completado');
        } else {
            $this->info('✅ Análisis completado (usar sin --dry-run para ejecutar)');
        }

        return 0;
    }

    private function createAdminForStore(Store $store): void
    {
        $email = $this->ask("Email para el nuevo administrador de {$store->slug}", "admin@{$store->slug}.com");
        $name = $this->ask("Nombre del administrador", "Admin {$store->name}");
        $password = $this->secret("Contraseña (mínimo 8 caracteres)") ?: 'password123';

        try {
            DB::beginTransaction();

            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
                'role' => 'store_admin',
                'store_id' => $store->id,
            ]);

            DB::commit();

            $this->info("✅ Usuario {$email} creado y asignado a {$store->slug}");
            $this->line("   📧 Email: {$email}");
            $this->line("   🔑 Contraseña: {$password}");
            $this->line("   🌐 Login: /{$store->slug}/admin/login");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error creando usuario: " . $e->getMessage());
        }
    }

    private function assignAdminToStore(User $admin, Store $store): void
    {
        try {
            DB::beginTransaction();

            $admin->update(['store_id' => $store->id]);

            DB::commit();

            $this->info("✅ Usuario {$admin->email} asignado a {$store->slug}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error asignando usuario: " . $e->getMessage());
        }
    }
}
