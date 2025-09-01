<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Exception;

/**
 * Gestor de correo unificado que maneja SMTP directamente
 * Evita problemas de configuración dinámica de Laravel
 */
class MailManager
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
     * Configurar Laravel Mail temporalmente
     */
    private function configureMailTemporarily()
    {
        // Backup configuración actual
        $backup = [
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'password' => config('mail.mailers.smtp.password'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
        
        // Aplicar nueva configuración
        Config::set([
            'mail.mailers.smtp.host' => $this->config['host'],
            'mail.mailers.smtp.port' => $this->config['port'],
            'mail.mailers.smtp.username' => $this->config['username'],
            'mail.mailers.smtp.password' => $this->config['password'],
            'mail.mailers.smtp.encryption' => $this->config['encryption'] === 'none' ? null : $this->config['encryption'],
            'mail.from.address' => $this->config['from_email'],
            'mail.from.name' => $this->config['from_name'],
        ]);
        
        // Limpiar instancias de Mail para forzar recarga
        app()->forgetInstance('mailer');
        app()->forgetInstance('mail.manager');
        
        return $backup;
    }
    
    /**
     * Restaurar configuración de Mail
     */
    private function restoreMailConfig(array $backup)
    {
        Config::set([
            'mail.mailers.smtp.host' => $backup['host'],
            'mail.mailers.smtp.port' => $backup['port'],
            'mail.mailers.smtp.username' => $backup['username'],
            'mail.mailers.smtp.password' => $backup['password'],
            'mail.mailers.smtp.encryption' => $backup['encryption'],
            'mail.from.address' => $backup['from_address'],
            'mail.from.name' => $backup['from_name'],
        ]);
        
        app()->forgetInstance('mailer');
        app()->forgetInstance('mail.manager');
    }
    
    /**
     * Enviar email usando Laravel Mail con configuración temporal
     */
    public function send(string $to, string $subject, string $body, bool $isHtml = false): array
    {
        $backup = null;
        
        try {
            // Configurar Mail temporalmente
            $backup = $this->configureMailTemporarily();
            
            // Enviar email
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)
                       ->from($this->config['from_email'], $this->config['from_name'])
                       ->subject($subject);
            });
            
            Log::info('Email enviado exitosamente (Laravel Mail)', [
                'to' => $to,
                'subject' => $subject,
                'method' => 'laravel_mail_temp_config'
            ]);
            
            return [
                'success' => true,
                'message' => 'Email enviado correctamente'
            ];
            
        } catch (Exception $e) {
            Log::error('Error enviando email (Laravel Mail)', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'config_host' => $this->config['host'],
                'config_port' => $this->config['port'],
                'config_encryption' => $this->config['encryption']
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        } finally {
            // Restaurar configuración original
            if ($backup) {
                $this->restoreMailConfig($backup);
            }
        }
    }
    
    /**
     * Probar conexión SMTP
     */
    public function testConnection(string $testEmail = null): array
    {
        $testEmail = $testEmail ?: $this->config['from_email'];
        
        $subject = 'Prueba de configuración SMTP - Linkiu.bio';
        $body = 'Esta es una prueba de configuración SMTP desde Linkiu.bio. Si recibes este mensaje, la configuración está funcionando correctamente.';
        
        return $this->send($testEmail, $subject, $body);
    }
    
    /**
     * Validar configuración sin enviar email
     */
    public function validateConfig(): array
    {
        try {
            // Usar PHPMailerManager para validación
            $phpMailer = new \App\Mail\PHPMailerManager($this->config);
            return $phpMailer->validateConfig();
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Configuración SMTP inválida: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener información de configuración (sin contraseña)
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
        ];
    }
}