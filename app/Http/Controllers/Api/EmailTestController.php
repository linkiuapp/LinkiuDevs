<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\MailManager;
use Illuminate\Support\Facades\Log;

class EmailTestController extends Controller
{
    /**
     * Enviar email de prueba usando MailManager
     */
    public function sendTest(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);
            
            $email = $request->input('email');
            
            Log::info('API: Iniciando test de email', [
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            // Usar MailManager directamente
            $mailManager = new MailManager();
            $result = $mailManager->testConnection($email);
            
            Log::info('API: Resultado test de email', [
                'email' => $email,
                'success' => $result['success'],
                'message' => $result['message']
            ]);
            
            return response()->json($result);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email inválido'
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('API: Error en test de email', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
    
    /**
     * Validar configuración SMTP sin enviar email
     */
    public function validateConfig()
    {
        try {
            $mailManager = new MailManager();
            $result = $mailManager->validateConfig();
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('API: Error validando configuración', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al validar configuración: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener información de configuración actual
     */
    public function getConfig()
    {
        try {
            $mailManager = new MailManager();
            $config = $mailManager->getConfigInfo();
            
            return response()->json([
                'success' => true,
                'config' => $config
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No hay configuración disponible'
            ], 404);
        }
    }
}