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

// ⚠️ RUTA DE EMERGENCIA - Mantener para futuros problemas de producción
// Solo usar cuando hay problemas críticos que requieran diagnóstico sin middleware 