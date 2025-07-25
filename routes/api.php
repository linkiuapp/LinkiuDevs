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

// 🔍 SIMULAR TENANT IDENTIFICATION MIDDLEWARE PASO A PASO
Route::get('/debug-middleware/{slug}', function($slug) {
    $debugInfo = [
        'timestamp' => now()->toISOString(),
        'middleware_simulation' => true,
        'slug' => $slug,
        'steps' => []
    ];

    try {
        // Paso 1: Buscar tienda básica
        $debugInfo['steps']['step_1_basic_store'] = 'Starting...';
        $basicStore = \App\Shared\Models\Store::where('slug', $slug)->first();
        
        if (!$basicStore) {
            $debugInfo['steps']['step_1_basic_store'] = 'FAILED - Store not found';
            return response()->json($debugInfo, 404);
        }
        
        $debugInfo['steps']['step_1_basic_store'] = 'SUCCESS';
        $debugInfo['basic_store'] = [
            'id' => $basicStore->id,
            'name' => $basicStore->name,
            'slug' => $basicStore->slug
        ];

        // Paso 2: Probar withCount individual
        $debugInfo['steps']['step_2_products_count'] = 'Starting...';
        try {
            $productsCount = \App\Shared\Models\Store::where('slug', $slug)->withCount('products')->first();
            $debugInfo['steps']['step_2_products_count'] = 'SUCCESS';
            $debugInfo['products_count'] = $productsCount->products_count ?? 0;
        } catch (\Exception $e) {
            $debugInfo['steps']['step_2_products_count'] = 'FAILED: ' . $e->getMessage();
        }

        $debugInfo['steps']['step_3_categories_count'] = 'Starting...';
        try {
            $categoriesCount = \App\Shared\Models\Store::where('slug', $slug)->withCount('categories')->first();
            $debugInfo['steps']['step_3_categories_count'] = 'SUCCESS';
            $debugInfo['categories_count'] = $categoriesCount->categories_count ?? 0;
        } catch (\Exception $e) {
            $debugInfo['steps']['step_3_categories_count'] = 'FAILED: ' . $e->getMessage();
        }

        $debugInfo['steps']['step_4_variables_count'] = 'Starting...';
        try {
            $variablesCount = \App\Shared\Models\Store::where('slug', $slug)->withCount('variables')->first();
            $debugInfo['steps']['step_4_variables_count'] = 'SUCCESS';
            $debugInfo['variables_count'] = $variablesCount->variables_count ?? 0;
        } catch (\Exception $e) {
            $debugInfo['steps']['step_4_variables_count'] = 'FAILED: ' . $e->getMessage();
        }

        $debugInfo['steps']['step_5_sliders_count'] = 'Starting...';
        try {
            $slidersCount = \App\Shared\Models\Store::where('slug', $slug)->withCount('sliders')->first();
            $debugInfo['steps']['step_5_sliders_count'] = 'SUCCESS';
            $debugInfo['sliders_count'] = $slidersCount->sliders_count ?? 0;
        } catch (\Exception $e) {
            $debugInfo['steps']['step_5_sliders_count'] = 'FAILED: ' . $e->getMessage();
        }

        // Paso 6: Probar with('plan')
        $debugInfo['steps']['step_6_with_plan'] = 'Starting...';
        try {
            $storeWithPlan = \App\Shared\Models\Store::where('slug', $slug)->with('plan')->first();
            $debugInfo['steps']['step_6_with_plan'] = 'SUCCESS';
            $debugInfo['plan_data'] = $storeWithPlan->plan ? [
                'id' => $storeWithPlan->plan->id,
                'name' => $storeWithPlan->plan->name
            ] : null;
        } catch (\Exception $e) {
            $debugInfo['steps']['step_6_with_plan'] = 'FAILED: ' . $e->getMessage();
        }

        // Paso 7: Probar query completa como en el middleware
        $debugInfo['steps']['step_7_full_query'] = 'Starting...';
        try {
            $fullStore = \App\Shared\Models\Store::where('slug', $slug)
                ->withCount([
                    'products',
                    'categories', 
                    'variables',
                    'sliders'
                ])
                ->with('plan')
                ->first();
            
            $debugInfo['steps']['step_7_full_query'] = 'SUCCESS';
            $debugInfo['full_store_data'] = [
                'id' => $fullStore->id,
                'name' => $fullStore->name,
                'products_count' => $fullStore->products_count,
                'categories_count' => $fullStore->categories_count,
                'variables_count' => $fullStore->variables_count,
                'sliders_count' => $fullStore->sliders_count,
                'plan_name' => $fullStore->plan?->name
            ];
        } catch (\Exception $e) {
            $debugInfo['steps']['step_7_full_query'] = 'FAILED: ' . $e->getMessage();
            $debugInfo['full_query_error'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }

        // Paso 8: Probar TenantService->setTenant()
        $debugInfo['steps']['step_8_set_tenant'] = 'Starting...';
        try {
            $tenantService = app(\App\Shared\Services\TenantService::class);
            $tenantService->setTenant($basicStore);
            $debugInfo['steps']['step_8_set_tenant'] = 'SUCCESS';
        } catch (\Exception $e) {
            $debugInfo['steps']['step_8_set_tenant'] = 'FAILED: ' . $e->getMessage();
        }

        // Paso 9: Probar view()->share()
        $debugInfo['steps']['step_9_view_share'] = 'Starting...';
        try {
            view()->share('currentStore', $basicStore);
            $debugInfo['steps']['step_9_view_share'] = 'SUCCESS';
        } catch (\Exception $e) {
            $debugInfo['steps']['step_9_view_share'] = 'FAILED: ' . $e->getMessage();
        }

    } catch (\Exception $e) {
        $debugInfo['critical_error'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
    }

    return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
}); 