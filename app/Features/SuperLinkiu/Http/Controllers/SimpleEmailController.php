<?php

namespace App\Features\SuperLinkiu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SimpleEmailController extends Controller
{
    /**
     * Enviar email de prueba con configuración simple y directa
     */
    public function sendTest(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);
            
            $email = $request->input('email');
            
            Log::info('SuperLinkiu SimpleEmail: Iniciando envío', [
                'email' => $email,
                'timestamp' => now()
            ]);
            
            // CONFIGURACIÓN DIRECTA QUE FUNCIONA
            $this->configureWorkingMail();
            
            // Enviar email simple
            Mail::raw(
                "¡Hola!\n\nEste es un email de prueba desde SuperLinkiu.\n\nSi recibes este mensaje, la configuración de email está funcionando correctamente.\n\nSaludos,\nEquipo SuperLinkiu",
                function ($message) use ($email) {
                    $message->to($email)
                           ->from('soporte@linkiu.email', 'SuperLinkiu')
                           ->subject('✅ Prueba de Email - SuperLinkiu');
                }
            );
            
            Log::info('SuperLinkiu SimpleEmail: Email enviado exitosamente', [
                'email' => $email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Email de prueba enviado correctamente a ' . $email
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email inválido: ' . $e->getMessage()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('SuperLinkiu SimpleEmail: Error al enviar', [
                'email' => $request->input('email'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Configurar Mail con los valores que sabemos que funcionan
     */
    private function configureWorkingMail()
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => 'mail.linkiu.email',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'soporte@linkiu.email',
                'password' => 'Soporte2024!',
                'timeout' => null,
            ],
            'mail.from' => [
                'address' => 'soporte@linkiu.email',
                'name' => 'SuperLinkiu'
            ]
        ]);
    }
}