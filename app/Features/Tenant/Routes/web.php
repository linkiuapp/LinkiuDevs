<?php

use Illuminate\Support\Facades\Route;
use App\Features\Tenant\Controllers\StorefrontController;
use App\Features\Tenant\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Tenant (Store Frontend) Routes
|--------------------------------------------------------------------------
|
| Rutas para el frontend público de las tiendas
| URL: linkiu.bio/{tienda}
|
*/

// Página principal de la tienda (Próximamente)
Route::get('/', [StorefrontController::class, 'index'])->name('home');

// API para verificar estado de verificación en tiempo real
Route::get('/verification-status', [StorefrontController::class, 'verificationStatus'])->name('verification-status');

// Catálogo de productos con buscador
Route::get('/catalogo', [StorefrontController::class, 'catalog'])->name('catalog');

// API para búsqueda en tiempo real
Route::get('/api/search', [StorefrontController::class, 'searchProducts'])->name('search.api');

// Rutas de productos
Route::get('/producto/{productSlug}', [StorefrontController::class, 'product'])->name('product');

// Carrito y Checkout Routes
Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [StorefrontController::class, 'cart'])->name('index');
    Route::post('/agregar', [OrderController::class, 'addToCart'])->name('add');
    Route::get('/contenido', [OrderController::class, 'getCart'])->name('get');
    Route::put('/actualizar', [OrderController::class, 'updateCartItem'])->name('update');
    Route::delete('/eliminar', [OrderController::class, 'removeFromCart'])->name('remove');
    Route::delete('/limpiar', [OrderController::class, 'clearCart'])->name('clear');
    Route::post('/aplicar-cupon', [OrderController::class, 'applyCoupon'])->name('apply-coupon');
});

// Checkout Routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [OrderController::class, 'create'])->name('create');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::post('/shipping-cost', [OrderController::class, 'getShippingCost'])->name('shipping-cost');
    Route::get('/shipping-methods', [OrderController::class, 'getShippingMethods'])->name('shipping-methods');
    Route::get('/payment-methods', [OrderController::class, 'getPaymentMethods'])->name('payment-methods');
    Route::get('/exito', [OrderController::class, 'success'])->name('success');
    Route::get('/error', [OrderController::class, 'error'])->name('error');
    
    // API Routes para estado de pedidos
    Route::prefix('api')->group(function () {
        Route::get('/order-status', [OrderController::class, 'getOrderStatusSimple'])->name('api.order.status.simple');
    });
});

// Order Routes
Route::prefix('pedido')->name('order.')->group(function () {
    Route::get('/seguimiento', [OrderController::class, 'tracking'])->name('tracking');
});

// DEBUG Routes (temporal)
Route::prefix('debug')->name('debug.')->group(function () {
    Route::get('/cart', [OrderController::class, 'debugCart'])->name('cart');
    Route::post('/cart/add', [OrderController::class, 'debugAddToCart'])->name('cart.add');
    Route::match(['GET', 'POST'], '/shipping-cost', [OrderController::class, 'debugShippingCost'])->name('shipping-cost');
});

// Rutas de categorías
Route::get('/categorias', [StorefrontController::class, 'categories'])->name('categories');
Route::get('/categoria/{categorySlug}', [StorefrontController::class, 'category'])->name('category');

// Ruta de contacto/sedes
Route::get('/contacto', [StorefrontController::class, 'contact'])->name('contact');

// Ruta de promociones/cupones
Route::get('/promociones', [StorefrontController::class, 'promotions'])->name('promotions');

// Más rutas del frontend se añadirán aquí en el futuro...
// Route::get('/buscar', [StorefrontController::class, 'search'])->name('search'); 