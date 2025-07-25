<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\User;
use App\Shared\Models\Store;

class FixStoreAdminsCommand extends Command
{
    protected $signature = 'auth:fix-store-admins {--dry-run : Solo mostrar qué se haría sin ejecutar cambios} {--force : Ejecutar sin confirmación}';
    protected $description = 'Arreglar usuarios store_admin sin store_id asignado';

    public function handle()
    {
        $this->info("🔧 REPARANDO USUARIOS STORE_ADMIN SIN STORE_ID");
        $this->line("");

        // 1. Encontrar usuarios problemáticos
        $problematicUsers = User::where('role', 'store_admin')
            ->whereNull('store_id')
            ->get();

        if ($problematicUsers->isEmpty()) {
            $this->info("✅ No hay usuarios store_admin sin store_id. Todo está correcto.");
            return 0;
        }

        $this->warn("🚨 Encontrados {$problematicUsers->count()} usuarios store_admin sin store_id:");
        $this->line("");

        // Mostrar usuarios problemáticos
        $this->table(['ID', 'Nombre', 'Email', 'Store ID', 'Creado'], 
            $problematicUsers->map(fn($user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->store_id ?? 'NULL ❌',
                $user->created_at->format('Y-m-d H:i:s')
            ])->toArray()
        );

        // 2. Analizar tiendas disponibles
        $storesWithoutAdmin = Store::whereDoesntHave('admins')->get();
        $allStores = Store::with('admins')->get();

        $this->line("");
        $this->info("📊 ANÁLISIS DE TIENDAS:");
        $this->line("• Total tiendas: " . $allStores->count());
        $this->line("• Tiendas sin admin: " . $storesWithoutAdmin->count());
        $this->line("• Tiendas con admin: " . ($allStores->count() - $storesWithoutAdmin->count()));

        if ($storesWithoutAdmin->isNotEmpty()) {
            $this->line("");
            $this->info("🏪 TIENDAS SIN ADMINISTRADOR:");
            $this->table(['ID', 'Nombre', 'Slug', 'Status'], 
                $storesWithoutAdmin->map(fn($store) => [
                    $store->id,
                    $store->name,
                    $store->slug,
                    $store->status
                ])->toArray()
            );
        }

        // 3. Proponer soluciones
        $this->line("");
        $this->info("💡 ESTRATEGIAS DE ASIGNACIÓN:");

        $solutions = $this->proposeSolutions($problematicUsers, $storesWithoutAdmin, $allStores);

        foreach ($solutions as $solution) {
            $this->line("• Usuario ID {$solution['user']->id} ({$solution['user']->email}) → Tienda ID {$solution['store']->id} ({$solution['store']->name})");
            $this->line("  Razón: {$solution['reason']}");
        }

        // 4. Confirmar ejecución
        if ($this->option('dry-run')) {
            $this->warn("🔍 MODO DRY-RUN: No se ejecutarán cambios");
            return 0;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('¿Ejecutar las asignaciones propuestas?')) {
                $this->info("Operación cancelada");
                return 0;
            }
        }

        // 5. Ejecutar asignaciones
        $this->line("");
        $this->info("⚙️ EJECUTANDO ASIGNACIONES...");
        
        foreach ($solutions as $solution) {
            $user = $solution['user'];
            $store = $solution['store'];
            
            $user->update(['store_id' => $store->id]);
            
            $this->line("✅ Usuario {$user->email} asignado a tienda {$store->name}");
        }

        // 6. Verificar resultados
        $remainingIssues = User::where('role', 'store_admin')->whereNull('store_id')->count();
        
        $this->line("");
        if ($remainingIssues === 0) {
            $this->info("🎉 ÉXITO: Todos los usuarios store_admin tienen store_id asignado");
        } else {
            $this->warn("⚠️ Quedan {$remainingIssues} usuarios sin asignar (requieren intervención manual)");
        }

        return 0;
    }

    private function proposeSolutions($problematicUsers, $storesWithoutAdmin, $allStores): array
    {
        $solutions = [];
        $availableStores = $storesWithoutAdmin->toArray();

        foreach ($problematicUsers as $user) {
            $solution = null;

            // Estrategia 1: Asignar a tienda sin admin
            if (!empty($availableStores)) {
                $store = array_shift($availableStores);
                $solution = [
                    'user' => $user,
                    'store' => Store::find($store['id']),
                    'reason' => 'Tienda sin administrador asignado'
                ];
            }
            // Estrategia 2: Buscar tienda por similitud de email/nombre
            if (!$solution) {
                $emailDomain = explode('@', $user->email)[0];
                $matchingStore = $allStores->first(function ($store) use ($emailDomain, $user) {
                    return str_contains(strtolower($store->name), strtolower($emailDomain)) ||
                           str_contains(strtolower($store->slug), strtolower($emailDomain)) ||
                           str_contains(strtolower($user->name), strtolower($store->name));
                });

                if ($matchingStore) {
                    $solution = [
                        'user' => $user,
                        'store' => $matchingStore,
                        'reason' => 'Similitud detectada entre nombre/email y tienda'
                    ];
                }
            }
            
            // Estrategia 3: Asignar a primera tienda disponible
            if (!$solution && $allStores->isNotEmpty()) {
                $solution = [
                    'user' => $user,
                    'store' => $allStores->first(),
                    'reason' => 'Asignación por defecto a primera tienda disponible'
                ];
            }

            if ($solution) {
                $solutions[] = $solution;
            }
        }

        return $solutions;
    }
}
