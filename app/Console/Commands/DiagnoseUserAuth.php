<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\User;
use App\Shared\Models\Store;
use Illuminate\Support\Facades\Hash;

class DiagnoseUserAuth extends Command
{
    protected $signature = 'auth:diagnose-user {email} {store_slug?}';
    protected $description = 'Diagnosticar datos de usuario y autenticación';

    public function handle()
    {
        $email = $this->argument('email');
        $storeSlug = $this->argument('store_slug');

        $this->info("🔍 DIAGNÓSTICO DE AUTENTICACIÓN");
        $this->line("Email: {$email}");
        if ($storeSlug) {
            $this->line("Store Slug: {$storeSlug}");
        }
        $this->line("");

        // 1. Verificar si el usuario existe
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario NO encontrado con email: {$email}");
            return 1;
        }

        $this->info("✅ Usuario encontrado:");
        $this->table(['Campo', 'Valor'], [
            ['ID', $user->id],
            ['Nombre', $user->name],
            ['Email', $user->email],
            ['Role', $user->role],
            ['Store ID', $user->store_id],
            ['Email Verified', $user->email_verified_at ? 'Sí' : 'No'],
            ['Created At', $user->created_at],
        ]);

        // 2. Si se proporciona store_slug, verificar la tienda
        if ($storeSlug) {
            $store = Store::where('slug', $storeSlug)->first();
            
            if (!$store) {
                $this->error("❌ Tienda NO encontrada con slug: {$storeSlug}");
                return 1;
            }

            $this->info("✅ Tienda encontrada:");
            $this->table(['Campo', 'Valor'], [
                ['ID', $store->id],
                ['Nombre', $store->name],
                ['Slug', $store->slug],
                ['Status', $store->status],
            ]);

            // 3. Verificar validaciones de autenticación
            $this->line("");
            $this->info("🔍 VALIDACIONES DE AUTENTICACIÓN:");
            
            $validations = [
                ['Criterio', 'Esperado', 'Actual', 'Estado'],
                ['Email coincide', $email, $user->email, $email === $user->email ? '✅' : '❌'],
                ['Role es store_admin', 'store_admin', $user->role, $user->role === 'store_admin' ? '✅' : '❌'],
                ['Store ID coincide', $store->id, $user->store_id, $user->store_id === $store->id ? '✅' : '❌'],
            ];
            
            $this->table($validations[0], array_slice($validations, 1));

            // 4. Verificar query exacta que usa el AuthController
            $authQuery = User::where('email', $email)
                ->where('role', 'store_admin')
                ->where('store_id', $store->id)
                ->first();

            $this->line("");
            if ($authQuery) {
                $this->info("✅ Query de autenticación: EXITOSA - Usuario válido para login");
            } else {
                $this->error("❌ Query de autenticación: FALLA - Usuario NO puede hacer login");
                
                // Sugerir soluciones
                $this->line("");
                $this->warn("💡 POSIBLES SOLUCIONES:");
                
                if ($user->role !== 'store_admin') {
                    $this->line("• Cambiar role del usuario a 'store_admin'");
                    $this->line("  UPDATE users SET role = 'store_admin' WHERE id = {$user->id};");
                }
                
                if ($user->store_id !== $store->id) {
                    $this->line("• Asignar usuario a la tienda correcta");
                    $this->line("  UPDATE users SET store_id = {$store->id} WHERE id = {$user->id};");
                }
            }
        }

        // 5. Mostrar todos los usuarios con role store_admin
        $this->line("");
        $this->info("👥 USUARIOS CON ROLE 'store_admin':");
        $admins = User::where('role', 'store_admin')->get();
        
        if ($admins->count() === 0) {
            $this->warn("No hay usuarios con role 'store_admin'");
        } else {
            $adminData = $admins->map(function ($admin) {
                return [
                    $admin->id,
                    $admin->name,
                    $admin->email,
                    $admin->store_id,
                    $admin->store ? $admin->store->name : 'Sin tienda'
                ];
            })->toArray();
            
            $this->table(['ID', 'Nombre', 'Email', 'Store ID', 'Tienda'], $adminData);
        }

        return 0;
    }
}
