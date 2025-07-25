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

// Rutas de productos
Route::get('/producto/{productSlug}', [StorefrontController::class, 'product'])->name('product');

// Carrito y Checkout Routes
Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [StorefrontController::class, 'cart'])->name('index');
    Route::post('/agregar', [OrderController::class, 'addToCart'])->name('add');
    Route::get('/contenido', [OrderController::class, 'getCart'])->name('get');
    Route::delete('/eliminar', [OrderController::class, 'removeFromCart'])->name('remove');
    Route::delete('/limpiar', [OrderController::class, 'clearCart'])->name('clear');
});

// Checkout Routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [OrderController::class, 'create'])->name('create');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::post('/shipping-cost', [OrderController::class, 'getShippingCost'])->name('shipping-cost');
});

// Order Routes
Route::prefix('pedido')->name('order.')->group(function () {
    Route::get('/exito/{orderNumber}', [OrderController::class, 'success'])->name('success');
    Route::get('/seguimiento', [OrderController::class, 'tracking'])->name('tracking');
});

// Rutas de categorías
Route::get('/categorias', [StorefrontController::class, 'categories'])->name('categories');
Route::get('/categoria/{categorySlug}', [StorefrontController::class, 'category'])->name('category');

// Más rutas del frontend se añadirán aquí en el futuro...
// Route::get('/buscar', [StorefrontController::class, 'search'])->name('search'); 