<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API para verificar estado de tienda
Route::get('/store/{slug}/status', function($slug) {
    try {
        $store = \App\Shared\Models\Store::where('slug', $slug)->first();
        
        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }
        
        return response()->json([
            'verified' => (bool) $store->verified,
            'status' => $store->status
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server error'], 500);
    }
});

// 🆘 REPARACIÓN REMOTA DE STORAGE - EMERGENCIA ACTIVA
Route::get('/fix-storage-emergency', function() {
    $results = [
        'timestamp' => now()->toISOString(),
        'action' => 'emergency_storage_fix',
        'environment' => app()->environment(),
        'steps' => []
    ];

    try {
        // Ejecutar el comando directamente
        \Artisan::call('post-deploy:fix-storage');
        $commandOutput = \Artisan::output();
        
        $results['steps'][] = "✅ Comando post-deploy:fix-storage ejecutado";
        $results['command_output'] = $commandOutput;
        
        // Verificar resultado
        $publicStorage = public_path('storage');
        $results['final_verification'] = [
            'public_storage_exists' => file_exists($publicStorage),
            'public_storage_is_link' => is_link($publicStorage),
            'symlink_target' => is_link($publicStorage) ? readlink($publicStorage) : null
        ];
        
        $results['success'] = true;
        $results['message'] = "Reparación de emergencia completada";
        
    } catch (\Exception $e) {
        $results['success'] = false;
        $results['error'] = $e->getMessage();
        $results['steps'][] = "❌ Error: " . $e->getMessage();
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});

// 🔥 REPARACIÓN SÚPER AGRESIVA - ÚLTIMO RECURSO
Route::get('/force-storage-repair', function() {
    $results = [
        'timestamp' => now()->toISOString(),
        'action' => 'force_aggressive_repair',
        'environment' => app()->environment(),
    ];

    try {
        // Ejecutar el comando súper agresivo
        \Artisan::call('storage:force-repair');
        $commandOutput = \Artisan::output();
        
        $results['command_output'] = $commandOutput;
        $results['success'] = true;
        
        // Verificación final
        $publicStorage = public_path('storage');
        $results['final_check'] = [
            'public_storage_exists' => file_exists($publicStorage),
            'public_storage_is_link' => is_link($publicStorage),
            'symlink_target' => is_link($publicStorage) ? readlink($publicStorage) : null
        ];
        
    } catch (\Exception $e) {
        $results['success'] = false;
        $results['error'] = $e->getMessage();
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});

// 🖼️ DEBUG ESPECÍFICO PARA IMÁGENES Y STORAGE
Route::get('/debug-images', function() {
    $debugInfo = [
        'timestamp' => now()->toISOString(),
        'image_debug' => true,
        'environment' => app()->environment(),
    ];

    try {
        // 1. Verificar estructura de directorios
        $debugInfo['directories'] = [
            'public_path' => public_path(),
            'storage_path' => storage_path(),
            'public_storage_exists' => file_exists(public_path('storage')),
            'public_storage_is_link' => is_link(public_path('storage')),
            'public_storage_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
        ];

        // 2. Verificar directorios específicos de imágenes
        $imageDirectories = [
            'storage/avatars',
            'storage/system', 
            'storage/stores/logos',
            'storage/store-design',
            'storage/products'
        ];

        $debugInfo['image_directories'] = [];
        foreach ($imageDirectories as $dir) {
            $fullPath = public_path($dir);
            $debugInfo['image_directories'][$dir] = [
                'exists' => file_exists($fullPath),
                'writable' => file_exists($fullPath) ? is_writable($fullPath) : false,
                'files_count' => file_exists($fullPath) ? count(glob($fullPath . '/*')) : 0,
                'permissions' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : null
            ];
        }

        // 3. Probar creación de archivo de prueba
        $testDir = public_path('storage/test');
        $debugInfo['write_test'] = [
            'test_dir_created' => false,
            'test_file_written' => false,
            'test_file_readable' => false,
            'test_url' => null,
            'error' => null
        ];

        try {
            if (!file_exists($testDir)) {
                mkdir($testDir, 0755, true);
            }
            $debugInfo['write_test']['test_dir_created'] = true;

            $testFile = $testDir . '/test.txt';
            file_put_contents($testFile, 'Test file created at ' . now());
            $debugInfo['write_test']['test_file_written'] = true;

            $debugInfo['write_test']['test_file_readable'] = file_exists($testFile);
            $debugInfo['write_test']['test_url'] = asset('storage/test/test.txt');

            // Limpiar archivo de prueba
            if (file_exists($testFile)) {
                unlink($testFile);
            }
            if (file_exists($testDir)) {
                rmdir($testDir);
            }

        } catch (\Exception $e) {
            $debugInfo['write_test']['error'] = $e->getMessage();
        }

        // 4. Verificar imágenes existentes de usuarios
        $debugInfo['existing_images'] = [
            'users_with_avatars' => 0,
            'avatar_samples' => [],
            'stores_with_logos' => 0,
            'logo_samples' => []
        ];

        // Verificar avatares de usuarios
        $usersWithAvatars = \App\Shared\Models\User::whereNotNull('avatar_path')->take(3)->get();
        $debugInfo['existing_images']['users_with_avatars'] = $usersWithAvatars->count();
        
        foreach ($usersWithAvatars as $user) {
            $avatarPath = public_path('storage/' . $user->avatar_path);
            $debugInfo['existing_images']['avatar_samples'][] = [
                'user_id' => $user->id,
                'avatar_path' => $user->avatar_path,
                'file_exists' => file_exists($avatarPath),
                'url' => asset('storage/' . $user->avatar_path),
                'file_size' => file_exists($avatarPath) ? filesize($avatarPath) : null
            ];
        }

        // Verificar logos de tiendas
        $storesWithLogos = \App\Shared\Models\Store::whereNotNull('logo_url')->take(3)->get();
        $debugInfo['existing_images']['stores_with_logos'] = $storesWithLogos->count();

        foreach ($storesWithLogos as $store) {
            // Extraer path del logo_url
            $logoPath = str_replace(asset('storage/'), '', $store->logo_url);
            $fullLogoPath = public_path('storage/' . $logoPath);
            
            $debugInfo['existing_images']['logo_samples'][] = [
                'store_id' => $store->id,
                'logo_url' => $store->logo_url,
                'extracted_path' => $logoPath,
                'file_exists' => file_exists($fullLogoPath),
                'file_size' => file_exists($fullLogoPath) ? filesize($fullLogoPath) : null
            ];
        }

        // 5. Información del servidor web
        $debugInfo['server_info'] = [
            'php_version' => PHP_VERSION,
            'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
            'app_url' => config('app.url'),
            'asset_url' => config('app.asset_url', 'not_set')
        ];

        // 6. Test de asset() helper
        $debugInfo['asset_test'] = [
            'asset_storage_test' => asset('storage/test/example.jpg'),
            'url_helper_test' => url('storage/test/example.jpg'),
            'config_app_url' => config('app.url')
        ];

    } catch (\Exception $e) {
        $debugInfo['critical_error'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }

    return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
});

// 🆘 RUTA DE EMERGENCIA - DEBUG SIN MIDDLEWARE
Route::get('/emergency-debug/{slug?}', function($slug = null) {
    $debugInfo = [
        'timestamp' => now()->toISOString(),
        'emergency_debug' => true,
        'slug_provided' => $slug,
        'environment' => app()->environment(),
        'debug_mode' => config('app.debug'),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ];

    try {
        // Test 1: Database connection
        \DB::connection()->getPdo();
        $debugInfo['database_connected'] = true;
        
        // Test 2: Check if stores table exists
        $debugInfo['stores_table_exists'] = \Schema::hasTable('stores');
        
        if ($debugInfo['stores_table_exists']) {
            $debugInfo['total_stores'] = \DB::table('stores')->count();
            
            if ($slug) {
                // Test 3: Check specific store
                $store = \DB::table('stores')->where('slug', $slug)->first();
                $debugInfo['store_found'] = $store ? true : false;
                
                if ($store) {
                    $debugInfo['store_data'] = [
                        'id' => $store->id,
                        'name' => $store->name,
                        'status' => $store->status,
                        'plan_id' => $store->plan_id
                    ];
                    
                    // Test 4: Check store admins
                    $debugInfo['admin_count'] = \DB::table('users')
                        ->where('store_id', $store->id)
                        ->where('role', 'store_admin')
                        ->count();
                }
            }
        }
        
        // Test 5: Check critical classes
        $criticalClasses = [
            'App\Shared\Models\Store',
            'App\Shared\Models\User', 
            'App\Shared\Services\TenantService',
            'App\Shared\Middleware\TenantIdentificationMiddleware'
        ];
        
        $debugInfo['classes_exist'] = [];
        foreach ($criticalClasses as $class) {
            $debugInfo['classes_exist'][$class] = class_exists($class);
        }
        
        // Test 6: Try to instantiate TenantService
        try {
            $tenantService = app(\App\Shared\Services\TenantService::class);
            $debugInfo['tenant_service_instantiable'] = true;
        } catch (\Exception $e) {
            $debugInfo['tenant_service_instantiable'] = false;
            $debugInfo['tenant_service_error'] = $e->getMessage();
        }
        
        // Test 7: Check if TenantIdentificationMiddleware can be instantiated
        try {
            $middleware = app(\App\Shared\Middleware\TenantIdentificationMiddleware::class);
            $debugInfo['middleware_instantiable'] = true;
        } catch (\Exception $e) {
            $debugInfo['middleware_instantiable'] = false;
            $debugInfo['middleware_error'] = $e->getMessage();
        }
        
    } catch (\Exception $e) {
        $debugInfo['database_connected'] = false;
        $debugInfo['database_error'] = $e->getMessage();
        $debugInfo['error_file'] = $e->getFile();
        $debugInfo['error_line'] = $e->getLine();
    }
    
    // Test 8: Check recent log errors
    try {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $lines = explode("\n", $logContent);
            $recentLines = array_slice($lines, -10);
            
            $debugInfo['recent_errors'] = [];
            foreach ($recentLines as $line) {
                if (strpos($line, 'ERROR') !== false || strpos($line, 'FATAL') !== false) {
                    $debugInfo['recent_errors'][] = substr($line, 0, 200);
                }
            }
        }
    } catch (\Exception $e) {
        $debugInfo['log_check_error'] = $e->getMessage();
    }
    
    return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
}); 