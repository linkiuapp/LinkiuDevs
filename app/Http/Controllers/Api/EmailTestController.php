<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Shared\Models\EmailConfiguration;

class EmailTestController extends Controller
{
    public function sendTest(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        try {
            $emailConfig = EmailConfiguration::getActive();
            
            if (!$emailConfig || !$emailConfig->isComplete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay configuración SMTP disponible'
                ]);
            }
            
            // Usar testConnection directamente
            $result = $emailConfig->testConnection($request->email);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}