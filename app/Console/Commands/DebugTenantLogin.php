<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use App\Shared\Services\TenantService;
use App\Shared\Middleware\TenantIdentificationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugTenantLogin extends Command
{
    protected $signature = 'debug:tenant-login {store_slug} {--email=}';
    protected $description = 'Debug specific tenant login issues';

    public function handle()
    {
        $storeSlug = $this->argument('store_slug');
        $email = $this->option('email');

        $this->info("🔍 DEBUG TENANT LOGIN: {$storeSlug}");
        $this->newLine();

        // 1. Verificar que la tienda existe
        $this->info('📋 Paso 1: Verificar tienda...');
        $store = Store::where('slug', $storeSlug)->first();
        
        if (!$store) {
            $this->error("❌ Tienda '{$storeSlug}' no encontrada");
            return 1;
        }
        
        $this->line("✅ Tienda encontrada: {$store->name} (ID: {$store->id})");
        $this->line("   Status: {$store->status}");
        $this->line("   Verified: " . ($store->verified ? 'Sí' : 'No'));
        $this->newLine();

        // 2. Verificar admins de la tienda
        $this->info('👤 Paso 2: Verificar administradores...');
        $admins = $store->admins()->get();
        
        if ($admins->isEmpty()) {
            $this->error('❌ La tienda NO tiene administradores');
            return 1;
        }
        
        $this->line("✅ Administradores encontrados: {$admins->count()}");
        foreach ($admins as $admin) {
            $marker = $email && $admin->email === $email ? '👉' : '  ';
            $this->line("{$marker} - {$admin->email} (ID: {$admin->id})");
        }
        $this->newLine();

        // 3. Probar TenantIdentificationMiddleware
        $this->info('🔧 Paso 3: Probar TenantIdentificationMiddleware...');
        try {
            // Simular request
            $request = Request::create("/{$storeSlug}/admin/login", 'GET');
            $request->setRouteResolver(function () use ($storeSlug) {
                $route = new \Illuminate\Routing\Route(['GET'], "/{store}/admin/login", []);
                $route->setParameter('store', $storeSlug);
                return $route;
            });

            // Probar middleware
            $tenantService = app(TenantService::class);
            $middleware = new TenantIdentificationMiddleware($tenantService);
            
            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            });
            
            $this->line('✅ TenantIdentificationMiddleware funciona correctamente');
            $this->line('   Respuesta: ' . $response->getContent());
            
        } catch (\Exception $e) {
            $this->error('❌ Error en TenantIdentificationMiddleware:');
            $this->error('   ' . $e->getMessage());
            $this->line('   Archivo: ' . $e->getFile() . ':' . $e->getLine());
        }
        $this->newLine();

        // 4. Probar autenticación específica
        if ($email) {
            $this->info("🔐 Paso 4: Probar autenticación para {$email}...");
            
            $user = User::where('email', $email)
                ->where('role', 'store_admin')
                ->where('store_id', $store->id)
                ->first();
                
            if (!$user) {
                $this->error("❌ Usuario {$email} no encontrado como admin de esta tienda");
                
                // Buscar si existe pero en otra tienda
                $userExists = User::where('email', $email)->first();
                if ($userExists) {
                    $this->warn("⚠️  Usuario existe pero:");
                    $this->line("   - Role: {$userExists->role}");
                    $this->line("   - Store ID: {$userExists->store_id}");
                    $this->line("   - Esperado Store ID: {$store->id}");
                }
            } else {
                $this->line("✅ Usuario {$email} válido para esta tienda");
                $this->line("   - ID: {$user->id}");
                $this->line("   - Role: {$user->role}");
                $this->line("   - Store ID: {$user->store_id}");
                $this->line("   - Last Login: " . ($user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Nunca'));
            }
        }
        $this->newLine();

        // 5. Verificar rutas
        $this->info('🛣️  Paso 5: Verificar configuración de rutas...');
        try {
            $loginUrl = route('tenant.admin.login', ['store' => $storeSlug]);
            $this->line("✅ Ruta de login: {$loginUrl}");
            
            $dashboardUrl = route('tenant.admin.dashboard', ['store' => $storeSlug]);
            $this->line("✅ Ruta de dashboard: {$dashboardUrl}");
            
        } catch (\Exception $e) {
            $this->error('❌ Error generando rutas:');
            $this->error('   ' . $e->getMessage());
        }
        $this->newLine();

        // 6. Verificar logs recientes
        $this->info('📝 Paso 6: Verificar logs recientes...');
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            $this->line('✅ Archivo de log encontrado');
            
            // Buscar errores recientes relacionados con esta tienda
            $logContent = file_get_contents($logFile);
            $lines = explode("\n", $logContent);
            $recentErrors = [];
            
            foreach (array_reverse(array_slice($lines, -100)) as $line) {
                if (strpos($line, $storeSlug) !== false || 
                    strpos($line, 'TenantIdentificationMiddleware') !== false ||
                    strpos($line, 'StoreAdminMiddleware') !== false) {
                    $recentErrors[] = $line;
                }
            }
            
            if (!empty($recentErrors)) {
                $this->warn("⚠️  Errores recientes relacionados:");
                foreach (array_slice($recentErrors, -5) as $error) {
                    $this->line("   " . substr($error, 0, 100) . '...');
                }
            } else {
                $this->line('✅ No se encontraron errores recientes relacionados');
            }
        } else {
            $this->warn('⚠️  Archivo de log no encontrado');
        }
        $this->newLine();

        $this->info('✅ Debug completado');
        
        if ($email) {
            $this->newLine();
            $this->info('💡 Para probar el login manualmente:');
            $this->line("   URL: /{$storeSlug}/admin/login");
            $this->line("   Email: {$email}");
            $this->line("   Debug URL: /{$storeSlug}/admin/debug");
        }

        return 0;
    }
} 