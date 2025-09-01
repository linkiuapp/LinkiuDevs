<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use App\Services\EmailService;
use Exception;

/**
 * Gestor que usa directamente el método sendRaw que funciona en CLI
 */
class DirectSMTPManager
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
     * Aplicar configuración SMTP exactamente como EmailConfiguration
     */
    private function applyToMail()
    {
        \Illuminate\Support\Facades\Config::set([
            'mail.mailers.smtp.host' => $this->config['host'],
            'mail.mailers.smtp.port' => $this->config['port'],
            'mail.mailers.smtp.username' => $this->config['username'],
            'mail.mailers.smtp.password' => $this->config['password'],
            'mail.mailers.smtp.encryption' => $this->config['encryption'] === 'none' ? null : $this->config['encryption'],
            'mail.from.address' => $this->config['from_email'],
            'mail.from.name' => $this->config['from_name'],
        ]);
        
        return true;
    }
    
    /**
     * Enviar email usando exactamente el mismo método que EmailConfiguration
     */
    public function send(string $to, string $subject, string $body): array
    {
        try {
            Log::info('DirectSMTP: Iniciando envío', [
                'to' => $to,
                'subject' => $subject,
                'method' => 'direct_sendraw_exact'
            ]);
            
            // Aplicar configuración exactamente como EmailConfiguration
            $this->applyToMail();
            
            // Usar EmailService::sendRaw exactamente como EmailConfiguration
            EmailService::sendRaw(
                $body,
                [$to],
                $subject,
                'support'
            );
            
            Log::info('DirectSMTP: Email enviado exitosamente', [
                'to' => $to,
                'subject' => $subject
            ]);
            
            return [
                'success' => true,
                'message' => 'Email enviado correctamente'
            ];
            
        } catch (Exception $e) {
            Log::error('DirectSMTP: Error enviando email', [
                'to' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Probar conexión enviando email de prueba
     */
    public function testConnection(string $testEmail = null): array
    {
        $testEmail = $testEmail ?: $this->config['from_email'];
        
        $subject = 'Prueba de configuración SMTP - Linkiu.bio';
        $body = 'Esta es una prueba de configuración SMTP desde Linkiu.bio usando método directo. Si recibes este mensaje, la configuración está funcionando correctamente.';
        
        return $this->send($testEmail, $subject, $body);
    }
    
    /**
     * Validar configuración
     */
    public function validateConfig(): array
    {
        try {
            // Verificar que tenemos todos los datos necesarios
            $required = ['host', 'port', 'username', 'password', 'from_email'];
            
            foreach ($required as $field) {
                if (empty($this->config[$field])) {
                    return [
                        'success' => false,
                        'message' => "Campo requerido faltante: {$field}"
                    ];
                }
            }
            
            return [
                'success' => true,
                'message' => 'Configuración válida'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error validando configuración: ' . $e->getMessage()
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
            'method' => 'direct_sendraw'
        ];
    }
}