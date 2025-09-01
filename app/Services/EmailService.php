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

            // Configurar SMTP
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

            Log::info('Email de prueba enviado exitosamente', [
                'recipient' => $recipientEmail,
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
     * Configurar SMTP usando la configuración de producción
     */
    private static function configureSMTP(): void
    {
        Config::set([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => env('MAIL_HOST', self::$smtpConfig['host']),
                'port' => env('MAIL_PORT', self::$smtpConfig['port']),
                'encryption' => env('MAIL_ENCRYPTION', self::$smtpConfig['encryption']),
                'username' => env('MAIL_USERNAME', self::$smtpConfig['username']),
                'password' => env('MAIL_PASSWORD'),
                'verify_peer' => env('MAIL_VERIFY_PEER', self::$smtpConfig['verify_peer']),
                'verify_peer_name' => env('MAIL_VERIFY_PEER_NAME', self::$smtpConfig['verify_peer_name']),
                'allow_self_signed' => env('MAIL_ALLOW_SELF_SIGNED', self::$smtpConfig['allow_self_signed']),
            ],
            'mail.from' => [
                'address' => env('MAIL_FROM_ADDRESS', self::$smtpConfig['username']),
                'name' => env('MAIL_FROM_NAME', 'LinkiuBio')
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

        // Verificar variables de entorno
        if (!env('MAIL_HOST')) {
            $issues[] = 'MAIL_HOST no configurado';
        }

        if (!env('MAIL_USERNAME')) {
            $issues[] = 'MAIL_USERNAME no configurado';
        }

        if (!env('MAIL_PASSWORD')) {
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
                'host' => env('MAIL_HOST', 'No configurado'),
                'port' => env('MAIL_PORT', 'No configurado'),
                'username' => env('MAIL_USERNAME', 'No configurado'),
                'encryption' => env('MAIL_ENCRYPTION', 'No configurado')
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
