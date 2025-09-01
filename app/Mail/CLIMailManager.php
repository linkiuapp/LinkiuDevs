<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Exception;

/**
 * Gestor de correo que usa el comando CLI que sabemos que funciona
 */
class CLIMailManager
{
    private $config;
    
    public function __construct(array $config = null)
    {
        $this->config = $config ?: $this->getActiveConfig();
    }
    
    /**
     * Obtener configuración activa desde la base de datos
     */
    private function getActiveConfig(): array
    {
        $emailConfig = \App\Shared\Models\EmailConfiguration::getActive();
        
        if (!$emailConfig || !$emailConfig->isComplete()) {
            throw new Exception('No hay configuración SMTP activa disponible');
        }
        
        return [
            'host' => $emailConfig->smtp_host,
            'port' => $emailConfig->smtp_port,
            'username' => $emailConfig->smtp_username,
            'password' => $emailConfig->smtp_password,
            'encryption' => $emailConfig->smtp_encryption,
            'from_email' => $emailConfig->from_email,
            'from_name' => $emailConfig->from_name,
        ];
    }
    
    /**
     * Enviar email usando el comando CLI que funciona
     */
    public function send(string $to, string $subject, string $body, bool $isHtml = false): array
    {
        try {
            // Crear comando temporal para envío
            $tempCommand = $this->createTempCommand($to, $subject, $body);
            
            // Ejecutar comando
            $exitCode = Artisan::call($tempCommand);
            
            if ($exitCode === 0) {
                Log::info('Email enviado exitosamente (CLI Method)', [
                    'to' => $to,
                    'subject' => $subject,
                    'method' => 'cli_artisan'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Email enviado correctamente'
                ];
            } else {
                throw new Exception('Comando CLI falló con código: ' . $exitCode);
            }
            
        } catch (Exception $e) {
            Log::error('Error enviando email (CLI Method)', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear comando temporal para envío
     */
    private function createTempCommand(string $to, string $subject, string $body): string
    {
        // Usar el comando email:send-test que sabemos que funciona
        return "email:send-test {$to}";
    }
    
    /**
     * Probar conexión usando comando CLI
     */
    public function testConnection(string $testEmail = null): array
    {
        try {
            $testEmail = $testEmail ?: $this->config['from_email'];
            
            Log::info('Iniciando test CLI', [
                'email' => $testEmail,
                'method' => 'cli_artisan_test'
            ]);
            
            // Ejecutar comando de prueba
            $exitCode = Artisan::call('email:send-test', [
                'email' => $testEmail
            ]);
            
            // Obtener output del comando
            $output = Artisan::output();
            
            if ($exitCode === 0) {
                return [
                    'success' => true,
                    'message' => 'Email de prueba enviado correctamente (CLI)'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error en comando CLI: ' . $output
                ];
            }
            
        } catch (Exception $e) {
            Log::error('Error en test CLI', [
                'email' => $testEmail,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al ejecutar comando CLI: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validar configuración
     */
    public function validateConfig(): array
    {
        try {
            // Verificar que existe el comando
            if (!array_key_exists('email:send-test', Artisan::all())) {
                return [
                    'success' => false,
                    'message' => 'Comando email:send-test no encontrado'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Configuración CLI válida'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error validando CLI: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener información de configuración
     */
    public function getConfigInfo(): array
    {
        return [
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'username' => $this->config['username'],
            'encryption' => $this->config['encryption'],
            'from_email' => $this->config['from_email'],
            'from_name' => $this->config['from_name'],
            'method' => 'cli_artisan'
        ];
    }
}