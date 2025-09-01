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

// API para test de email (sistema unificado) - sin auth para testing
Route::post('/email/test', [\App\Http\Controllers\Api\EmailTestController::class, 'sendTest']);
Route::post('/email/validate', [\App\Http\Controllers\Api\EmailTestController::class, 'validateConfig']);
Route::get('/email/config', [\App\Http\Controllers\Api\EmailTestController::class, 'getConfig']); 