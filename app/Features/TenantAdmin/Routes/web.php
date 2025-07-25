<?php

use Illuminate\Support\Facades\Route;
use App\Features\TenantAdmin\Controllers\PaymentMethodController;
use App\Features\TenantAdmin\Controllers\BankAccountController;
use App\Features\TenantAdmin\Controllers\AuthController;
use App\Features\TenantAdmin\Controllers\DashboardController;
use App\Features\TenantAdmin\Controllers\BusinessProfileController;
use App\Features\TenantAdmin\Controllers\StoreDesignController;
use App\Features\TenantAdmin\Controllers\CategoryController;
use App\Features\TenantAdmin\Controllers\VariableController;
use App\Features\TenantAdmin\Controllers\ProductController;
use App\Features\TenantAdmin\Controllers\SliderController;
use App\Features\TenantAdmin\Controllers\LocationController;
use App\Features\TenantAdmin\Controllers\ShippingMethodController;
use App\Features\TenantAdmin\Controllers\TicketController;
use App\Features\TenantAdmin\Controllers\AnnouncementController;
use App\Features\TenantAdmin\Controllers\OrderController;
use App\Features\TenantAdmin\Controllers\BillingController;


/*
|--------------------------------------------------------------------------
| Tenant Admin Routes
|--------------------------------------------------------------------------
|
| Rutas para el panel de administración de las tiendas
| URL: linkiu.bio/{tienda}/admin
|
*/

// Rutas de autenticación (sin middleware auth)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Ruta de debug expandida para producción
Route::get('/debug', function (\Illuminate\Http\Request $request) {
    $storeSlug = $request->route('store');
    $debugInfo = [
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
        'debug_mode' => config('app.debug'),
        
        // Información de request
        'request' => [
            'route_store' => $storeSlug,
            'segment_1' => $request->segment(1),
            'segment_2' => $request->segment(2),
            'full_url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ],
        
        // Estado del usuario
        'auth' => [
            'is_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role,
            'user_store_id' => auth()->user()?->store_id,
        ],
        
        // Información de la tienda
        'store_check' => [
            'slug_provided' => $storeSlug,
            'store_exists' => false,
            'store_info' => null,
            'admin_count' => 0,
            'error' => null
        ],
        
        // Estado del middleware
        'middleware' => [
            'current_store' => 'NOT_SET',
            'tenant_service' => 'NOT_TESTED',
        ],
        
        // Estado del sistema
        'system' => [
            'database_connected' => false,
            'storage_writable' => is_writable(storage_path()),
            'cache_path_writable' => is_writable(storage_path('framework/cache')),
            'log_path_writable' => is_writable(storage_path('logs')),
        ]
    ];
    
    try {
        // Probar conexión a BD
        \DB::connection()->getPdo();
        $debugInfo['system']['database_connected'] = true;
        
        // Información detallada de la tienda
        if ($storeSlug) {
            $store = \App\Shared\Models\Store::where('slug', $storeSlug)->first();
            if ($store) {
                $debugInfo['store_check']['store_exists'] = true;
                $debugInfo['store_check']['store_info'] = [
                    'id' => $store->id,
                    'name' => $store->name,
                    'status' => $store->status,
                    'verified' => $store->verified,
                    'plan_id' => $store->plan_id,
                    'created_at' => $store->created_at?->toISOString(),
                ];
                $debugInfo['store_check']['admin_count'] = $store->admins()->count();
                
                // Probar TenantService
                try {
                    $tenantService = app(\App\Shared\Services\TenantService::class);
                    $tenantService->setTenant($store);
                    $debugInfo['middleware']['tenant_service'] = 'OK';
                } catch (\Exception $e) {
                    $debugInfo['middleware']['tenant_service'] = 'ERROR: ' . $e->getMessage();
                }
            } else {
                $debugInfo['store_check']['error'] = 'Store not found in database';
            }
        }
        
        // Información del currentStore del middleware
        $currentStore = view()->shared('currentStore', null);
        if ($currentStore && is_object($currentStore)) {
            $debugInfo['middleware']['current_store'] = [
                'id' => $currentStore->id ?? 'NO_ID',
                'slug' => $currentStore->slug ?? 'NO_SLUG',
                'name' => $currentStore->name ?? 'NO_NAME',
            ];
        } else {
            $debugInfo['middleware']['current_store'] = $currentStore;
        }
        
    } catch (\Exception $e) {
        $debugInfo['system']['database_connected'] = false;
        $debugInfo['store_check']['error'] = 'Database error: ' . $e->getMessage();
    }
    
    // Si es una request AJAX o quiere JSON
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
    }
    
    // Sino, mostrar HTML bonito
    $html = '<html><head><title>Debug Info - ' . ($storeSlug ?? 'No Store') . '</title>';
    $html .= '<style>body{font-family:monospace;margin:20px;} .ok{color:green;} .error{color:red;} .warn{color:orange;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style></head><body>';
    $html .= '<h1>🔍 Debug Info - ' . ($storeSlug ?? 'No Store') . '</h1>';
    
    foreach ($debugInfo as $section => $data) {
        $html .= '<h2>' . ucfirst($section) . '</h2>';
        $html .= '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
    }
    
    $html .= '<hr><p><small>Generated at: ' . $debugInfo['timestamp'] . '</small></p>';
    $html .= '</body></html>';
    
    return response($html)->header('Content-Type', 'text/html');
})->name('debug');

// Rutas protegidas (con middleware auth)
Route::middleware(['auth', 'store.admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Business Profile Routes
    Route::prefix('business-profile')->name('business-profile.')->group(function () {
        Route::get('/', [BusinessProfileController::class, 'index'])->name('index');
        Route::post('/update-owner', [BusinessProfileController::class, 'updateOwner'])->name('update-owner');
        Route::post('/update-store', [BusinessProfileController::class, 'updateStore'])->name('update-store');
        Route::post('/update-fiscal', [BusinessProfileController::class, 'updateFiscal'])->name('update-fiscal');
        Route::post('/update-seo', [BusinessProfileController::class, 'updateSeo'])->name('update-seo');
        Route::post('/update-policies', [BusinessProfileController::class, 'updatePolicies'])->name('update-policies');
        Route::post('/update-about', [BusinessProfileController::class, 'updateAbout'])->name('update-about');
    });

    // Store Design Routes
    Route::prefix('store-design')->name('store-design.')->group(function () {
        Route::get('/', [StoreDesignController::class, 'index'])->name('index');
        Route::post('/update', [StoreDesignController::class, 'update'])->name('update');
        Route::post('/upload-logo', [StoreDesignController::class, 'uploadLogo'])->name('upload-logo');
        Route::post('/upload-favicon', [StoreDesignController::class, 'uploadFavicon'])->name('upload-favicon');
        Route::post('/publish', [StoreDesignController::class, 'publish'])->name('publish');
        Route::post('/revert', [StoreDesignController::class, 'revert'])->name('revert');
    });
    
    // Categories Routes
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/update-order', [CategoryController::class, 'updateOrder'])->name('update-order');
    });
    
    // Variables Routes
    Route::prefix('variables')->name('variables.')->group(function () {
        Route::get('/', [VariableController::class, 'index'])->name('index');
        Route::get('/create', [VariableController::class, 'create'])->name('create');
        Route::post('/', [VariableController::class, 'store'])->name('store');
        Route::get('/{variable}', [VariableController::class, 'show'])->name('show');
        Route::get('/{variable}/edit', [VariableController::class, 'edit'])->name('edit');
        Route::put('/{variable}', [VariableController::class, 'update'])->name('update');
        Route::delete('/{variable}', [VariableController::class, 'destroy'])->name('destroy');
        Route::post('/{variable}/toggle-status', [VariableController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{variable}/duplicate', [VariableController::class, 'duplicate'])->name('duplicate');
    });

    // Products Routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{product}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');
        Route::post('/{product}/set-main-image', [ProductController::class, 'setMainImage'])->name('set-main-image');
    });

    // Sliders Routes
    Route::prefix('sliders')->name('sliders.')->group(function () {
        Route::get('/', [SliderController::class, 'index'])->name('index');
        Route::get('/create', [SliderController::class, 'create'])->name('create');
        Route::post('/', [SliderController::class, 'store'])->name('store');
        Route::get('/{slider}', [SliderController::class, 'show'])->name('show');
        Route::get('/{slider}/edit', [SliderController::class, 'edit'])->name('edit');
        Route::put('/{slider}', [SliderController::class, 'update'])->name('update');
        Route::delete('/{slider}', [SliderController::class, 'destroy'])->name('destroy');
        Route::post('/{slider}/duplicate', [SliderController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{slider}/toggle-status', [SliderController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/reorder', [SliderController::class, 'updateOrder'])->name('reorder');
    });

    // Rutas para métodos de pago
    Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
        Route::get('/create', [PaymentMethodController::class, 'create'])->name('create');
        Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
        Route::get('/{paymentMethod}', [PaymentMethodController::class, 'show'])->name('show');
        Route::get('/{paymentMethod}/edit', [PaymentMethodController::class, 'edit'])->name('edit');
        Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('update');
        Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
        Route::post('/{paymentMethod}/toggle-active', [PaymentMethodController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/update-order', [PaymentMethodController::class, 'updateOrder'])->name('update-order');
        
        // Rutas para cuentas bancarias (anidadas bajo payment-methods)
        Route::prefix('{paymentMethod}/bank-accounts')->name('bank-accounts.')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('index');
            Route::get('/create', [BankAccountController::class, 'create'])->name('create');
            Route::post('/', [BankAccountController::class, 'store'])->name('store');
            Route::get('/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('edit');
            Route::put('/{bankAccount}', [BankAccountController::class, 'update'])->name('update');
            Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');
            Route::post('/{bankAccount}/toggle-active', [BankAccountController::class, 'toggleActive'])->name('toggle-active');
        });
    });

    // Rutas directas para cuentas bancarias (para compatibilidad con las vistas existentes)
    Route::prefix('bank-accounts')->name('bank-accounts.')->group(function () {
        Route::get('/{paymentMethod}', [BankAccountController::class, 'index'])->name('index');
        Route::get('/{paymentMethod}/create', [BankAccountController::class, 'create'])->name('create');
        Route::post('/{paymentMethod}', [BankAccountController::class, 'store'])->name('store');
        Route::get('/{paymentMethod}/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('edit');
        Route::put('/{paymentMethod}/{bankAccount}', [BankAccountController::class, 'update'])->name('update');
        Route::delete('/{paymentMethod}/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');
        Route::post('/{paymentMethod}/{bankAccount}/toggle-active', [BankAccountController::class, 'toggleActive'])->name('toggle-active');
    });

    // Locations Routes
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::get('/create', [LocationController::class, 'create'])->name('create');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::get('/{location}', [LocationController::class, 'show'])->name('show');
        Route::get('/{location}/edit', [LocationController::class, 'edit'])->name('edit');
        Route::put('/{location}', [LocationController::class, 'update'])->name('update');
        Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
        Route::post('/{location}/toggle-status', [LocationController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{location}/set-as-main', [LocationController::class, 'setAsMain'])->name('set-as-main');
        Route::post('/{location}/increment-whatsapp-clicks', [LocationController::class, 'incrementWhatsAppClicks'])->name('increment-whatsapp-clicks');
    });

    // Shipping Methods Routes
    Route::prefix('shipping-methods')->name('shipping-methods.')->group(function () {
        Route::get('/', [ShippingMethodController::class, 'index'])->name('index');
        Route::post('/toggle-active/{method}', [ShippingMethodController::class, 'toggleActive'])->name('toggle-active');
        Route::put('/update-order', [ShippingMethodController::class, 'updateOrder'])->name('update-order');
        Route::put('/update-pickup/{method}', [ShippingMethodController::class, 'updatePickup'])->name('update-pickup');
        
        // Shipping Zones Routes
        Route::prefix('{method}/zones')->name('zones.')->group(function () {
            Route::get('/create', [ShippingMethodController::class, 'createZone'])->name('create');
            Route::post('/', [ShippingMethodController::class, 'storeZone'])->name('store');
            Route::get('/{zone}/edit', [ShippingMethodController::class, 'editZone'])->name('edit');
            Route::put('/{zone}', [ShippingMethodController::class, 'updateZone'])->name('update');
            Route::delete('/{zone}', [ShippingMethodController::class, 'destroyZone'])->name('destroy');
            Route::post('/{zone}/toggle-active', [ShippingMethodController::class, 'toggleZoneActive'])->name('toggle-active');
        });
    });

    // Tickets Routes
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/add-response', [TicketController::class, 'addResponse'])->name('add-response');
        Route::post('/{ticket}/update-status', [TicketController::class, 'updateStatus'])->name('update-status');
    });

    // Announcements Routes
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('show');
        Route::post('/{announcement}/mark-as-read', [AnnouncementController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [AnnouncementController::class, 'markAllAsRead'])->name('mark-all-as-read');
        
        // API Routes for AJAX
        Route::get('/api/banners', [AnnouncementController::class, 'getBanners'])->name('api.banners');
        Route::get('/api/notification-count', [AnnouncementController::class, 'getNotificationCount'])->name('api.notification-count');
        Route::get('/api/recent', [AnnouncementController::class, 'getRecentAnnouncements'])->name('api.recent');
    });

    // Orders Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
        
        // Order Management Actions
        Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/duplicate', [OrderController::class, 'duplicate'])->name('duplicate');
        Route::get('/{order}/download-payment-proof', [OrderController::class, 'downloadPaymentProof'])->name('download-payment-proof');
        
        // AJAX Routes
        Route::post('/get-shipping-cost', [OrderController::class, 'getShippingCost'])->name('get-shipping-cost');
    });

    // Billing Routes (Plan y Facturación)
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        
        // Plan management
        Route::post('/change-plan', [BillingController::class, 'changePlan'])->name('change-plan');
        Route::post('/change-billing-cycle', [BillingController::class, 'changeBillingCycle'])->name('change-billing-cycle');
        
        // Subscription management
        Route::post('/cancel-subscription', [BillingController::class, 'cancelSubscription'])->name('cancel-subscription');
        Route::post('/reactivate-subscription', [BillingController::class, 'reactivateSubscription'])->name('reactivate-subscription');
        
        // Invoice downloads
        Route::get('/invoices/{invoice}/download', [BillingController::class, 'downloadInvoice'])->name('download-invoice');
    });

    // Ruta para manejar bajada de plan
    Route::post('/handle-plan-downgrade', [BankAccountController::class, 'handlePlanDowngrade'])->name('handle-plan-downgrade');
});


// Ruta por defecto que redirige
Route::get('/', function (\Illuminate\Http\Request $request) {
    // Obtener la tienda desde el segmento de URL
    $storeSlug = $request->segment(1); // Primer segmento será el slug de la tienda
    
    if (auth()->check() && auth()->user()->role === 'store_admin') {
        return redirect()->route('tenant.admin.dashboard', ['store' => $storeSlug]);
    }
    return redirect()->route('tenant.admin.login', ['store' => $storeSlug]);
})->name('index'); 