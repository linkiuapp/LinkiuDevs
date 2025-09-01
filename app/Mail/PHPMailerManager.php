<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Gestor de correo usando sockets PHP nativos
 * Máxima compatibilidad sin dependencias externas
 */
class PHPMailerManager
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
     * Enviar email usando sockets PHP nativos
     */
    public function send(string $to, string $subject, string $body, bool $isHtml = false): array
    {
        try {
            // Crear conexión SMTP
            $socket = $this->createConnection();
            
            // Proceso SMTP
            $this->smtpCommand($socket, null, '220'); // Esperar saludo
            $this->smtpCommand($socket, "EHLO " . $this->config['host'], '250');
            
            // STARTTLS si es necesario
            if ($this->config['encryption'] === 'tls') {
                $this->smtpCommand($socket, "STARTTLS", '220');
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpCommand($socket, "EHLO " . $this->config['host'], '250');
            }
            
            // Autenticación
            $this->smtpCommand($socket, "AUTH LOGIN", '334');
            $this->smtpCommand($socket, base64_encode($this->config['username']), '334');
            $this->smtpCommand($socket, base64_encode($this->config['password']), '235');
            
            // Envío del email
            $this->smtpCommand($socket, "MAIL FROM: <" . $this->config['from_email'] . ">", '250');
            $this->smtpCommand($socket, "RCPT TO: <{$to}>", '250');
            $this->smtpCommand($socket, "DATA", '354');
            
            // Headers y contenido
            $headers = $this->buildHeaders($to, $subject, $isHtml);
            $message = $headers . "\r\n" . $body . "\r\n.";
            
            $this->smtpCommand($socket, $message, '250');
            $this->smtpCommand($socket, "QUIT", '221');
            
            fclose($socket);
            
            Log::info('Email enviado exitosamente (PHP Native)', [
                'to' => $to,
                'subject' => $subject,
                'method' => 'php_native_smtp'
            ]);
            
            return [
                'success' => true,
                'message' => 'Email enviado correctamente'
            ];
            
        } catch (Exception $e) {
            if (isset($socket) && is_resource($socket)) {
                fclose($socket);
            }
            
            Log::error('Error enviando email (PHP Native)', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'config_host' => $this->config['host'],
                'config_port' => $this->config['port']
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear conexión SMTP
     */
    private function createConnection()
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        
        // Para SSL, usar ssl:// prefix
        if ($this->config['encryption'] === 'ssl') {
            $host = "ssl://{$host}";
        }
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $socket = stream_socket_client(
            "{$host}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$socket) {
            throw new Exception("No se pudo conectar a {$host}:{$port} - {$errstr} ({$errno})");
        }
        
        return $socket;
    }
    
    /**
     * Ejecutar comando SMTP y verificar respuesta
     */
    private function smtpCommand($socket, $command, $expectedCode)
    {
        if ($command !== null) {
            fwrite($socket, $command . "\r\n");
        }
        
        // Leer respuesta completa (puede ser multilinea)
        $response = '';
        $code = '';
        
        do {
            $line = fgets($socket, 512);
            $response .= $line;
            
            if (empty($code)) {
                $code = substr($line, 0, 3);
            }
            
            // Continuar leyendo si hay más líneas (formato: "220-mensaje" vs "220 mensaje")
        } while (strlen($line) >= 4 && $line[3] === '-');
        
        if ($code !== $expectedCode) {
            throw new Exception("SMTP Error: Expected {$expectedCode}, got {$code} - {$response}");
        }
        
        return $response;
    }
    
    /**
     * Construir headers del email
     */
    private function buildHeaders(string $to, string $subject, bool $isHtml): string
    {
        $headers = [];
        $headers[] = "From: {$this->config['from_name']} <{$this->config['from_email']}>";
        $headers[] = "To: {$to}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "Date: " . date('r');
        $headers[] = "Message-ID: <" . uniqid() . "@{$this->config['host']}>";
        
        if ($isHtml) {
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }
        
        $headers[] = "Content-Transfer-Encoding: 8bit";
        $headers[] = "X-Mailer: Linkiu.bio";
        
        return implode("\r\n", $headers);
    }
    
    /**
     * Probar conexión SMTP
     */
    public function testConnection(string $testEmail = null): array
    {
        $testEmail = $testEmail ?: $this->config['from_email'];
        
        $subject = 'Prueba de configuración SMTP - Linkiu.bio';
        $body = 'Esta es una prueba de configuración SMTP desde Linkiu.bio usando PHP nativo. Si recibes este mensaje, la configuración está funcionando correctamente.';
        
        return $this->send($testEmail, $subject, $body);
    }
    
    /**
     * Validar configuración sin enviar email
     */
    public function validateConfig(): array
    {
        try {
            $socket = $this->createConnection();
            $this->smtpCommand($socket, null, '220');
            $this->smtpCommand($socket, "QUIT", '221');
            fclose($socket);
            
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