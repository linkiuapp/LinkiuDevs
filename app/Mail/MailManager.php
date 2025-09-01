<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
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
     * Crear transporte SMTP usando Symfony Mailer directamente
     */
    private function createTransport(): EsmtpTransport
    {
        $dsn = sprintf(
            'smtp://%s:%s@%s:%d',
            urlencode($this->config['username']),
            urlencode($this->config['password']),
            $this->config['host'],
            $this->config['port']
        );
        
        $transport = EsmtpTransport::fromDsn($dsn);
        
        // Configurar encriptación
        if ($this->config['encryption'] === 'tls') {
            $transport->setPort(587);
        } elseif ($this->config['encryption'] === 'ssl') {
            $transport->setPort(465);
        }
        
        return $transport;
    }
    
    /**
     * Enviar email usando Symfony Mailer directamente
     */
    public function send(string $to, string $subject, string $body, bool $isHtml = false): array
    {
        try {
            $transport = $this->createTransport();
            $mailer = new Mailer($transport);
            
            $email = (new Email())
                ->from($this->config['from_email'])
                ->to($to)
                ->subject($subject);
            
            if ($isHtml) {
                $email->html($body);
            } else {
                $email->text($body);
            }
            
            $mailer->send($email);
            
            Log::info('Email enviado exitosamente', [
                'to' => $to,
                'subject' => $subject,
                'method' => 'symfony_direct'
            ]);
            
            return [
                'success' => true,
                'message' => 'Email enviado correctamente'
            ];
            
        } catch (Exception $e) {
            Log::error('Error enviando email', [
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
            $transport = $this->createTransport();
            $transport->start();
            $transport->stop();
            
            return [
                'success' => true,
                'message' => 'Configuración SMTP válida'
            ];
            
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