<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Broadcast;

// Rutas públicas - Web principal
Route::get('/', function () {
    return view('welcome');
});

// Ruta para autenticación de WebSocket (requerida aunque usemos canales públicos)
Broadcast::routes(['middleware' => ['auth']]);

// API de notificaciones con autenticación por sesión web
Route::middleware(['web', 'auth'])->group(function () {
    // SuperLinkiu - Contadores para super_admin usando autenticación web
    Route::get('/api/superlinkiu/notifications', function () {
        // Verificar que el usuario esté autenticado y sea super_admin
        if (!auth()->check() || auth()->user()->role !== 'super_admin') {
            return response()->json([
                'error' => 'Unauthorized',
                'authenticated' => auth()->check(),
                'role' => auth()->user()?->role
            ], 401);
        }
        
        $openTicketsCount = \App\Shared\Models\Ticket::whereIn('status', ['open', 'in_progress'])->count();
        
        // Contar mensajes no leídos de store_admin usando la nueva lógica
        $newMessagesCount = \App\Shared\Models\Ticket::with('responses.user')
            ->get()
            ->sum(function($ticket) {
                return $ticket->new_store_responses_count;
            });
            
        return response()->json([
            'open_tickets' => $openTicketsCount,
            'new_messages' => $newMessagesCount
        ]);
    });
    
    // TenantAdmin - Contadores para store_admin usando autenticación web
    Route::get('/api/tenant/{store}/notifications', function ($storeSlug) {
        // Verificar que el usuario esté autenticado y sea store_admin
        if (!auth()->check() || auth()->user()->role !== 'store_admin') {
            return response()->json([
                'error' => 'Unauthorized',
                'authenticated' => auth()->check(),
                'role' => auth()->user()?->role
            ], 401);
        }
        
        $store = \App\Shared\Models\Store::where('slug', $storeSlug)->firstOrFail();
        
        // Verificar que el store_admin pertenezca a esta tienda
        if (auth()->user()->store_id !== $store->id) {
            return response()->json([
                'error' => 'Forbidden - User does not belong to this store',
                'user_store_id' => auth()->user()->store_id,
                'store_id' => $store->id
            ], 403);
        }
        
        $openTicketsCount = $store->tickets()->whereIn('status', ['open', 'in_progress'])->count();
        
        // Usar el atributo del modelo Store que ya implementa la lógica correcta
        $newMessagesCount = $store->unread_support_responses_count;
            
        return response()->json([
            'open_tickets' => $openTicketsCount,
            'new_messages' => $newMessagesCount
        ]);
    });
});

// Ruta para servir imágenes de tickets (tanto local como public)
Route::get('/storage/tickets/{store}/{ticket}/{filename}', function ($store, $ticket, $filename) {
    // Intentar encontrar el archivo en diferentes ubicaciones
    $possiblePaths = [
        "tickets/{$store}/{$ticket}/{$filename}",
        "public/tickets/{$store}/{$ticket}/{$filename}",
    ];
    
    foreach ($possiblePaths as $path) {
        if (Storage::disk('local')->exists($path)) {
            $file = Storage::disk('local')->get($path);
            $mimeType = Storage::disk('local')->mimeType($path);
            
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=31536000');
        }
    }
    
    // Intentar en disco public
    $publicPath = "tickets/{$store}/{$ticket}/{$filename}";
    if (Storage::disk('public')->exists($publicPath)) {
        $file = Storage::disk('public')->get($publicPath);
        $mimeType = Storage::disk('public')->mimeType($publicPath);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
    
    abort(404);
})->where([
    'store' => '[a-zA-Z0-9\-_]+',
    'ticket' => '[0-9]+',
    'filename' => '[a-zA-Z0-9\-_\.]+',
]);

// Ruta para servir imágenes de respuestas de tickets
Route::get('/storage/tickets/{store}/{ticket}/responses/{response}/{filename}', function ($store, $ticket, $response, $filename) {
    // Intentar encontrar el archivo en diferentes ubicaciones
    $possiblePaths = [
        "tickets/{$store}/{$ticket}/responses/{$response}/{$filename}",
        "public/tickets/{$store}/{$ticket}/responses/{$response}/{$filename}",
    ];
    
    foreach ($possiblePaths as $path) {
        if (Storage::disk('local')->exists($path)) {
            $file = Storage::disk('local')->get($path);
            $mimeType = Storage::disk('local')->mimeType($path);
            
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=31536000');
        }
    }
    
    // Intentar en disco public
    $publicPath = "tickets/{$store}/{$ticket}/responses/{$response}/{$filename}";
    if (Storage::disk('public')->exists($publicPath)) {
        $file = Storage::disk('public')->get($publicPath);
        $mimeType = Storage::disk('public')->mimeType($publicPath);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
    
    abort(404);
})->where([
    'store' => '[a-zA-Z0-9\-_]+',
    'ticket' => '[0-9]+',
    'response' => '[0-9]+',
    'filename' => '[a-zA-Z0-9\-_\.]+',
]);

// Test temporal para SuperAdmin
Route::get('/test-superlinkiu', function () {
    return 'SuperLinkiu funciona! Rutas cargadas correctamente.';
});

// Las rutas de SuperLinkiu ahora están en:
// app/Features/SuperLinkiu/Routes/web.php
// y se cargan automáticamente mediante SuperLinkiuServiceProvider


// System Debug Routes (Emergency Access - Independent of SuperLinkiu)
Route::middleware(['web', \App\Http\Middleware\DebugAuthMiddleware::class])->group(function () {
    Route::get('/system-debug', [App\Http\Controllers\SystemDebugController::class, 'index']);
    Route::post('/system-debug', [App\Http\Controllers\SystemDebugController::class, 'index']); // Handle login POST
    Route::get('/system-debug/errors', [App\Http\Controllers\SystemDebugController::class, 'getErrors']);
    Route::get('/system-debug/stats', [App\Http\Controllers\SystemDebugController::class, 'getStats']);
    Route::post('/system-debug/clear-logs', [App\Http\Controllers\SystemDebugController::class, 'clearLogs']);
    Route::post('/system-debug/test-notification', [App\Http\Controllers\SystemDebugController::class, 'testNotification']);
    Route::get('/system-debug/logout', [App\Http\Controllers\SystemDebugController::class, 'logout']);
});
