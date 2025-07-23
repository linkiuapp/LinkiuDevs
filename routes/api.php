<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Shared\Models\Ticket;
use App\Shared\Models\TicketResponse;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas API del sistema principal
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para polling de notificaciones
Route::middleware(['auth'])->group(function () {
    
    // SuperLinkiu - Ruta movida a web.php para usar autenticación por sesión
    
    // TenantAdmin - Ruta movida a web.php para usar autenticación por sesión
}); 