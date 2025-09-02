<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;

class EmailService
{
    /**
     * Configuración SMTP desde .env (configuración correcta cPanel)
     */
    private static array $smtpConfig = [
        'host' => 'mail.linkiu.email',
        'port' => 465,
        'username' => 'no-responder@linkiu.email',
        'encryption' => 'ssl',
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ];

    /**
     * Enviar email usando plantilla
     */
    public static function sendWithTemplate(
        string $templateKey,
        string $recipientEmail,
        array $variables = [],
        array $options = []
    ): array {
        try {
            // Validar email del destinatario
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Email del destinatario inválido'
                ];
            }

            // Obtener plantilla
            $template = EmailTemplate::getByKey($templateKey);
            if (!$template) {
                return [
                    'success' => false,
                    'message' => "Plantilla '$templateKey' no encontrada"
                ];
            }

            // Validar variables de la plantilla
            $validationIssues = $template->validateVariables();
            if (!empty($validationIssues)) {
                Log::warning("Problemas de validación en plantilla $templateKey", $validationIssues);
            }

            // Renderizar plantilla
            $rendered = $template->render($variables);

            // Obtener email del contexto
            $fromEmail = $template->getContextEmail();

            // Configurar SMTP
            self::configureSMTP();

            // Enviar email
            Mail::send([], [], function ($message) use ($recipientEmail, $fromEmail, $rendered) {
                $message->to($recipientEmail)
                       ->from($fromEmail, config('mail.from.name', 'LinkiuBio'))
                       ->subject($rendered['subject']);

                if (!empty($rendered['body_html'])) {
                    $message->html($rendered['body_html']);
                }

                if (!empty($rendered['body_text'])) {
                    $message->text($rendered['body_text']);
                }
            });

            // Log del envío exitoso
            self::logEmailSent($templateKey, $recipientEmail, $template->context);

            return [
                'success' => true,
                'message' => 'Email enviado correctamente'
            ];

        } catch (Exception $e) {
            // Log del error
            Log::error("Error enviando email con plantilla $templateKey", [
                'recipient' => $recipientEmail,
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
     * Enviar email simple (sin plantilla)
     */
    public static function sendSimple(
        string $context,
        string $recipientEmail,
        string $subject,
        string $body,
        bool $isHtml = true
    ): array {
        try {
            // Validar email
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Email del destinatario inválido'
                ];
            }

            // Obtener email del contexto
            $fromEmail = self::getContextEmail($context);

            // Configurar SMTP
            self::configureSMTP();

            // Enviar email
            Mail::send([], [], function ($message) use ($recipientEmail, $fromEmail, $subject, $body, $isHtml) {
                $message->to($recipientEmail)
                       ->from($fromEmail, config('mail.from.name', 'LinkiuBio'))
                       ->subject($subject);

                if ($isHtml) {
                    $message->html($body);
                } else {
                    $message->text($body);
                }
            });

            // Log del envío
            self::logEmailSent('simple', $recipientEmail, $context);

            return [
                'success' => true,
                'message' => 'Email enviado correctamente'
            ];

        } catch (Exception $e) {
            Log::error("Error enviando email simple", [
                'context' => $context,
                'recipient' => $recipientEmail,
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
     * Enviar email de prueba
     */
    public static function sendTestEmail(string $recipientEmail): array
    {
        try {
            // Validar email
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Email inválido'
                ];
            }

            // Detectar contexto y usar método apropiado
            if (php_sapi_name() === 'cli') {
                // CLI: usar Mail::send (funciona)
                self::configureSMTP();

                $subject = 'Prueba de configuración SMTP - ' . config('app.name');
                $body = '
                    <h1>¡Configuración SMTP funcionando correctamente!</h1>
                    <p>Este es un email de prueba desde <strong>' . config('app.name') . '</strong></p>
                    <p><strong>Fecha:</strong> ' . now()->format('d/m/Y H:i:s') . '</p>
                    <p><strong>Servidor SMTP:</strong> ' . self::$smtpConfig['host'] . '</p>
                    <p><strong>Puerto:</strong> ' . self::$smtpConfig['port'] . '</p>
                    <p>Si recibes este mensaje, la configuración de email está funcionando perfectamente.</p>
                    <hr>
                    <p style="color: #666; font-size: 12px;">
                        Sistema de emails de LinkiuBio - ' . config('app.url') . '
                    </p>
                ';

                // Enviar desde la cuenta autenticada
                Mail::send([], [], function ($message) use ($recipientEmail, $subject, $body) {
                    $message->to($recipientEmail)
                           ->from(env('MAIL_FROM_ADDRESS', 'no-responder@linkiu.email'), 'LinkiuBio Sistema')
                           ->subject($subject)
                           ->html($body);
                });
            } else {
                // WEB: usar implementación SMTP manual
                $result = self::sendEmailManual(
                    $recipientEmail,
                    'Prueba de configuración SMTP - ' . config('app.name'),
                    'Este es un email de prueba desde ' . config('app.name') . ' enviado el ' . now()->format('d/m/Y H:i:s')
                );
                
                if (!$result['success']) {
                    return $result;
                }
            }

            Log::info('Email de prueba enviado exitosamente', [
                'recipient' => $recipientEmail,
                'method' => php_sapi_name() === 'cli' ? 'Laravel Mail' : 'Manual SMTP',
                'smtp_host' => self::$smtpConfig['host']
            ]);

            return [
                'success' => true,
                'message' => 'Email de prueba enviado correctamente. Revisa tu bandeja de entrada.'
            ];

        } catch (Exception $e) {
            Log::error('Error enviando email de prueba', [
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
                'smtp_config' => self::$smtpConfig
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar email de prueba: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envío SMTP manual para contexto WEB (evita problemas con Symfony Mailer)
     */
    private static function sendEmailManual(string $to, string $subject, string $body): array
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ]);
            
            $socket = stream_socket_client('ssl://mail.linkiu.email:465', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
            
            if (!$socket) {
                return ['success' => false, 'message' => "Error conectando: $errstr"];
            }
            
            // Leer banner completo (múltiples líneas 220-)
            do {
                $line = fgets($socket);
            } while (substr($line, 3, 1) !== ' ');
            
            // EHLO completo
            fwrite($socket, "EHLO linkiu.bio\r\n");
            do {
                $line = fgets($socket);
            } while (substr($line, 3, 1) !== ' ');
            
            // AUTH LOGIN
            fwrite($socket, "AUTH LOGIN\r\n");
            $auth_response = fgets($socket);
            if (strpos($auth_response, '334') !== 0) {
                fclose($socket);
                return ['success' => false, 'message' => "AUTH LOGIN falló: $auth_response"];
            }
            
            // Username
            fwrite($socket, base64_encode('no-responder@linkiu.email') . "\r\n");
            $user_response = fgets($socket);
            if (strpos($user_response, '334') !== 0) {
                fclose($socket);
                return ['success' => false, 'message' => "Username falló: $user_response"];
            }
            
            // Password
            fwrite($socket, base64_encode('t1fChP1pYbDYVt80e6') . "\r\n");
            $pass_response = fgets($socket);
            if (strpos($pass_response, '235') !== 0) {
                fclose($socket);
                return ['success' => false, 'message' => "Password falló: $pass_response"];
            }
            
            // MAIL FROM
            fwrite($socket, "MAIL FROM: <no-responder@linkiu.email>\r\n");
            $from_response = fgets($socket);
            if (strpos($from_response, '250') !== 0) {
                fclose($socket);
                return ['success' => false, 'message' => "MAIL FROM falló: $from_response"];
            }
            
            // RCPT TO
            fwrite($socket, "RCPT TO: <$to>\r\n");
            $rcpt_response = fgets($socket);
            if (strpos($rcpt_response, '250') !== 0) {
                fclose($socket);
                return ['success' => false, 'message' => "RCPT TO falló: $rcpt_response"];
            }
            
            // DATA
            fwrite($socket, "DATA\r\n");
            $data_response = fgets($socket);
            if (strpos($data_response, '354') !== 0) {
                fclose($socket);
                return ['success' => false, 'message' => "DATA falló: $data_response"];
            }
            
            // Email content
            $email = "From: no-responder@linkiu.email\r\n";
            $email .= "To: $to\r\n";
            $email .= "Subject: $subject\r\n";
            $email .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $email .= "\r\n";
            $email .= "$body\r\n";
            $email .= ".\r\n";
            
            fwrite($socket, $email);
            $send_response = fgets($socket);
            if (strpos($send_response, '250') !== 0) {
                fclose($socket);
                return ['success' => false, 'message' => "Envío falló: $send_response"];
            }
            
            // QUIT
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
            
            return ['success' => true, 'message' => 'Email enviado exitosamente via SMTP manual'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Error SMTP manual: " . $e->getMessage()];
        }
    }

    /**
     * Configurar SMTP usando la configuración de producción
     */
    private static function configureSMTP(): void
    {
        Config::set([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => config('mail.mailers.smtp.host', self::$smtpConfig['host']),
                'port' => config('mail.mailers.smtp.port', self::$smtpConfig['port']),
                'encryption' => config('mail.mailers.smtp.encryption', self::$smtpConfig['encryption']),
                'username' => config('mail.mailers.smtp.username', self::$smtpConfig['username']),
                'password' => config('mail.mailers.smtp.password'),
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'stream' => [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ]
                ]
            ],
            'mail.from' => [
                'address' => config('mail.from.address', self::$smtpConfig['username']),
                'name' => config('mail.from.name', 'LinkiuBio')
            ]
        ]);

        // Limpiar instancias de mail para forzar reconfiguración
        app()->forgetInstance('mailer');
        app()->forgetInstance('mail.manager');
    }

    /**
     * Obtener email por contexto
     */
    public static function getContextEmail(string $context): string
    {
        return EmailTemplate::CONTEXTS[$context]['email'] ?? 'no-responder@linkiu.email';
    }

    /**
     * Obtener información de todos los contextos
     */
    public static function getContexts(): array
    {
        return EmailTemplate::CONTEXTS;
    }

    /**
     * Validar configuración SMTP
     */
    public static function validateConfiguration(): array
    {
        $issues = [];

        // Verificar configuración SMTP (usando config() para soporte de cache)
        if (!config('mail.mailers.smtp.host')) {
            $issues[] = 'MAIL_HOST no configurado';
        }

        if (!config('mail.mailers.smtp.username')) {
            $issues[] = 'MAIL_USERNAME no configurado';
        }

        if (!config('mail.mailers.smtp.password')) {
            $issues[] = 'MAIL_PASSWORD no configurado';
        }

        // Verificar que los emails de contexto sean válidos
        foreach (EmailTemplate::CONTEXTS as $context => $config) {
            if (!filter_var($config['email'], FILTER_VALIDATE_EMAIL)) {
                $issues[] = "Email inválido para contexto '$context': {$config['email']}";
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'config' => [
                'host' => config('mail.mailers.smtp.host', 'No configurado'),
                'port' => config('mail.mailers.smtp.port', 'No configurado'),
                'username' => config('mail.mailers.smtp.username', 'No configurado'),
                'encryption' => config('mail.mailers.smtp.encryption', 'No configurado')
            ]
        ];
    }

    /**
     * Obtener estadísticas de emails (opcional para dashboard)
     */
    public static function getEmailStats(): array
    {
        // Aquí podrías consultar logs o una tabla de email_logs
        // Por ahora retornamos stats básicas
        return [
            'templates_count' => EmailTemplate::where('is_active', true)->count(),
            'contexts' => array_keys(EmailTemplate::CONTEXTS),
            'last_test' => cache('last_email_test', 'Nunca'),
            'config_valid' => self::validateConfiguration()['valid']
        ];
    }

    /**
     * Log del envío de email
     */
    private static function logEmailSent(string $type, string $recipient, string $context): void
    {
        Log::info('Email enviado exitosamente', [
            'type' => $type,
            'recipient_hash' => hash('sha256', $recipient), // Por privacidad
            'context' => $context,
            'from_email' => self::getContextEmail($context),
            'timestamp' => now(),
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        // Guardar timestamp del último email enviado
        cache(['last_email_sent' => now()], now()->addDays(30));
    }

    /**
     * Métodos de conveniencia para diferentes tipos de emails
     */

    /**
     * Enviar email de bienvenida para nueva tienda
     */
    public static function sendStoreWelcome(array $storeData): array
    {
        return self::sendWithTemplate('store_welcome', $storeData['admin_email'], $storeData);
    }

    /**
     * Enviar notificación de cambio de contraseña
     */
    public static function sendPasswordChanged(array $storeData): array
    {
        return self::sendWithTemplate('password_changed', $storeData['admin_email'], $storeData);
    }

    /**
     * Enviar notificación de nuevo ticket
     */
    public static function sendTicketCreated(array $ticketData): array
    {
        $adminEmails = ['soporte@linkiu.email']; // Aquí podrías obtener emails de admins
        $results = [];

        foreach ($adminEmails as $email) {
            $results[] = self::sendWithTemplate('ticket_created', $email, $ticketData);
        }

        return [
            'success' => collect($results)->every('success'),
            'results' => $results
        ];
    }

    /**
     * Enviar nueva factura
     */
    public static function sendInvoiceCreated(array $invoiceData): array
    {
        return self::sendWithTemplate('invoice_created', $invoiceData['store_email'], $invoiceData);
    }
}
