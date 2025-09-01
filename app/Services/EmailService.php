<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailService
{
    /**
     * Send email using template
     */
    public static function sendWithTemplate(
        string $templateKey, 
        array $recipients, 
        array $data = []
    ): bool {
        try {
            // Validate recipients
            $validatedRecipients = static::validateRecipients($recipients);
            if (empty($validatedRecipients)) {
                Log::warning("No valid recipients for email template: {$templateKey}", [
                    'original_recipients' => $recipients
                ]);
                return false;
            }

            // Get template
            $template = EmailTemplate::getTemplate($templateKey);
            
            if (!$template) {
                Log::warning("Email template not found: {$templateKey}");
                return false;
            }

            // Validate template variables
            $templateIssues = $template->validateTemplateVariables();
            if (!empty($templateIssues)) {
                Log::warning("Template validation issues found", [
                    'template_key' => $templateKey,
                    'issues' => $templateIssues
                ]);
            }

            // Get context email
            $fromEmail = static::getContextEmail($template->context);
            if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                Log::error("Invalid from email address", [
                    'template_key' => $templateKey,
                    'context' => $template->context,
                    'from_email' => $fromEmail
                ]);
                return false;
            }
            
            // Prepare mail data with sanitized variables
            $mailData = static::prepareMailData($template, $data);
            
            // Send email to each validated recipient
            foreach ($validatedRecipients as $recipient) {
                Mail::send([], [], function ($message) use ($recipient, $fromEmail, $mailData) {
                    $message->to($recipient)
                           ->from($fromEmail)
                           ->subject($mailData['subject']);
                    
                    if (!empty($mailData['body_html'])) {
                        $message->html($mailData['body_html']);
                    }
                    
                    if (!empty($mailData['body_text'])) {
                        $message->text($mailData['body_text']);
                    }
                });
            }
            
            // Log successful send
            static::logEmailSent($templateKey, $validatedRecipients, $data);
            
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send email with template {$templateKey}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'recipients' => $recipients,
                'data' => static::sanitizeLogData($data)
            ]);
            
            return false;
        }
    }

    /**
     * Validate email recipients
     */
    private static function validateRecipients(array $recipients): array
    {
        $validRecipients = [];
        $suspiciousDomains = ['tempmail.org', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
        
        foreach ($recipients as $recipient) {
            // Basic email validation
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Invalid email recipient: {$recipient}");
                continue;
            }
            
            // Check for suspicious domains
            $domain = substr(strrchr($recipient, "@"), 1);
            if (in_array($domain, $suspiciousDomains)) {
                Log::warning("Suspicious email domain blocked: {$recipient}");
                continue;
            }
            
            // Additional security checks
            if (strlen($recipient) > 254) { // RFC 5321 limit
                Log::warning("Email address too long: {$recipient}");
                continue;
            }
            
            $validRecipients[] = $recipient;
        }
        
        return $validRecipients;
    }

    /**
     * Sanitize log data to prevent sensitive information exposure
     */
    public static function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'credential'];
        $sanitized = $data;
        
        foreach ($sensitiveKeys as $sensitiveKey) {
            if (isset($sanitized[$sensitiveKey])) {
                $sanitized[$sensitiveKey] = '***REDACTED***';
            }
        }
        
        return $sanitized;
    }

    /**
     * Get email address for context
     */
    public static function getContextEmail(string $context): string
    {
        return EmailSetting::getEmail($context);
    }

    /**
     * Validate email configuration
     */
    public static function validateEmailConfiguration(): array
    {
        $issues = [];
        
        // Check required contexts exist
        $requiredContexts = ['store_management', 'support', 'billing'];
        
        foreach ($requiredContexts as $context) {
            $email = EmailSetting::getEmail($context);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $issues[] = "Invalid email for context '{$context}': {$email}";
            }
        }
        
        // Check if we have basic templates
        $requiredTemplates = [
            'store_welcome',
            'password_changed', 
            'invoice_created',
            'ticket_created'
        ];
        
        foreach ($requiredTemplates as $templateKey) {
            $template = EmailTemplate::getTemplate($templateKey);
            if (!$template) {
                $issues[] = "Missing template: {$templateKey}";
            }
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Prepare mail data from template and variables
     */
    private static function prepareMailData(EmailTemplate $template, array $data): array
    {
        // Add common variables
        $data = array_merge([
            'app_name' => config('app.name', 'Linkiu.bio'),
            'app_url' => config('app.url'),
            'support_email' => static::getContextEmail('support'),
            'current_year' => date('Y')
        ], $data);
        
        return $template->replaceVariables($data);
    }

    /**
     * Log email sent for audit
     */
    private static function logEmailSent(string $templateKey, array $recipients, array $data = []): void
    {
        Log::info("Email sent successfully", [
            'template' => $templateKey,
            'recipients_count' => count($recipients),
            'recipients_hash' => hash('sha256', implode(',', $recipients)), // Hash for privacy
            'data_keys' => array_keys($data),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()
        ]);
    }

    /**
     * Send simple email without template (fallback)
     */
    public static function sendSimple(
        string $context,
        array $recipients,
        string $subject,
        string $body,
        bool $isHtml = false
    ): bool {
        try {
            $fromEmail = static::getContextEmail($context);
            
            foreach ($recipients as $recipient) {
                Mail::send([], [], function ($message) use ($recipient, $fromEmail, $subject, $body, $isHtml) {
                    $message->to($recipient)
                           ->from($fromEmail)
                           ->subject($subject);
                    
                    if ($isHtml) {
                        $message->html($body);
                    } else {
                        $message->text($body);
                    }
                });
            }
            
            Log::info("Simple email sent successfully", [
                'context' => $context,
                'recipients_count' => count($recipients),
                'subject' => $subject
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to send simple email", [
                'context' => $context,
                'error' => $e->getMessage(),
                'recipients' => $recipients
            ]);
            
            return false;
        }
    }

    /**
     * Send email with view template (backward compatibility)
     */
    public static function sendWithView(
        string $view,
        array $recipients,
        array $data = [],
        string $subject = null,
        string $context = 'store_management'
    ): bool {
        try {
            // Get context email
            $fromEmail = static::getContextEmail($context);
            if (!$fromEmail) {
                Log::warning("No email configured for context: {$context}");
                return false;
            }

            // Send email to each recipient
            foreach ($recipients as $recipient) {
                Mail::send($view, $data, function ($message) use ($recipient, $fromEmail, $subject) {
                    $message->to($recipient)
                           ->from($fromEmail);
                    
                    if ($subject) {
                        $message->subject($subject);
                    }
                });
            }

            // Log successful sending
            Log::info("Email sent with view", [
                'view' => $view,
                'context' => $context,
                'recipients_count' => count($recipients),
                'from' => $fromEmail
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Failed to send email with view", [
                'view' => $view,
                'context' => $context,
                'recipients' => $recipients,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send raw email (backward compatibility)
     */
    public static function sendRaw(
        string $content,
        array $recipients,
        string $subject,
        string $context = 'store_management'
    ): bool {
        try {
            // Get context email
            $fromEmail = static::getContextEmail($context);
            if (!$fromEmail) {
                Log::warning("No email configured for context: {$context}");
                return false;
            }

            // Send email to each recipient
            foreach ($recipients as $recipient) {
                Mail::raw($content, function ($message) use ($recipient, $fromEmail, $subject) {
                    $message->to($recipient)
                           ->from($fromEmail)
                           ->subject($subject);
                });
            }

            // Log successful sending
            Log::info("Raw email sent", [
                'context' => $context,
                'recipients_count' => count($recipients),
                'from' => $fromEmail,
                'subject' => $subject
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Failed to send raw email", [
                'context' => $context,
                'recipients' => $recipients,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send test email - Usando método directo sendRaw que funciona
     */
    public static function sendTestEmail(string $email): array
    {
        try {
            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Email inválido'
                ];
            }
            
            Log::info('Iniciando test de email', [
                'email' => $email,
                'method' => 'direct_sendraw'
            ]);
            
            // Usar DirectSMTPManager que usa sendRaw internamente
            $directMailer = new \App\Mail\DirectSMTPManager();
            return $directMailer->testConnection($email);
            
        } catch (Exception $e) {
            Log::error('Test email failed', [
                'email' => $email,
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
     * Send test email - BACKUP: Configurar entorno como CLI y usar Mail::raw
     */
    public static function sendTestEmailBackup(string $email): array
    {
        try {
            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Email inválido'
                ];
            }
            
            // Obtener configuración SMTP
            $emailConfig = \App\Shared\Models\EmailConfiguration::getActive();
            if (!$emailConfig || !$emailConfig->isComplete()) {
                return [
                    'success' => false,
                    'message' => 'No hay configuración SMTP completa disponible'
                ];
            }
            
            // Configurar variables de entorno temporalmente (como CLI)
            $originalEnv = [];
            $envVars = [
                'MAIL_MAILER' => 'smtp',
                'MAIL_HOST' => $emailConfig->smtp_host,
                'MAIL_PORT' => $emailConfig->smtp_port,
                'MAIL_USERNAME' => $emailConfig->smtp_username,
                'MAIL_PASSWORD' => $emailConfig->smtp_password,
                'MAIL_ENCRYPTION' => $emailConfig->smtp_encryption,
                'MAIL_FROM_ADDRESS' => $emailConfig->from_email,
                'MAIL_FROM_NAME' => $emailConfig->from_name,
                'MAIL_VERIFY_PEER' => 'false'
            ];
            
            // Guardar valores originales y establecer nuevos
            foreach ($envVars as $key => $value) {
                $originalEnv[$key] = env($key);
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
            
            // Limpiar configuración de Mail para forzar recarga
            app()->forgetInstance('mailer');
            app()->forgetInstance('mail.manager');
            
            // Usar Mail::raw con configuración limpia
            Mail::raw(
                'Esta es una prueba de configuración SMTP desde Linkiu.bio. Si recibes este mensaje, la configuración está funcionando correctamente.',
                function ($message) use ($email, $emailConfig) {
                    $message->to($email)
                           ->from($emailConfig->from_email, $emailConfig->from_name)
                           ->subject('Prueba de configuración SMTP - Linkiu.bio');
                }
            );
            
            // Restaurar variables de entorno originales
            foreach ($originalEnv as $key => $value) {
                if ($value !== null) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                } else {
                    putenv($key);
                    unset($_ENV[$key], $_SERVER[$key]);
                }
            }
            
            return [
                'success' => true,
                'message' => 'Email de prueba enviado correctamente'
            ];
            
        } catch (Exception $e) {
            // Restaurar variables de entorno en caso de error
            if (isset($originalEnv)) {
                foreach ($originalEnv as $key => $value) {
                    if ($value !== null) {
                        putenv("{$key}={$value}");
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                    } else {
                        putenv($key);
                        unset($_ENV[$key], $_SERVER[$key]);
                    }
                }
            }
            
            Log::error('Test email failed (env config)', [
                'email' => $email,
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
     * Send test email with specific template
     */
    public static function sendTestEmailWithTemplate(string $email, EmailTemplate $template, array $data = []): array
    {
        try {
            $rendered = $template->replaceVariables($data);
            
            Mail::send([], [], function ($message) use ($email, $rendered) {
                $message->to($email)
                        ->subject($rendered['subject'])
                        ->from(config('mail.from.address'), config('mail.from.name'));
                
                if (!empty($rendered['body_html'])) {
                    $message->html($rendered['body_html']);
                }
                
                if (!empty($rendered['body_text'])) {
                    $message->text($rendered['body_text']);
                }
            });

            return [
                'success' => true,
                'message' => 'Email de prueba con plantilla enviado exitosamente'
            ];
        } catch (Exception $e) {
            Log::error('Test email with template failed', [
                'email' => $email,
                'template_id' => $template->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar email con plantilla: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send test email for specific context
     */
    public static function sendTestEmailForContext(string $email, string $context): array
    {
        try {
            // Get a template for this context
            $template = EmailTemplate::where('context', $context)
                                   ->where('is_active', true)
                                   ->first();
            
            if (!$template) {
                return [
                    'success' => false,
                    'message' => "No hay plantillas activas para el contexto: {$context}"
                ];
            }

            // Get sample data for context
            $sampleData = self::getSampleDataForContext($context);
            
            return self::sendTestEmailWithTemplate($email, $template, $sampleData);
            
        } catch (Exception $e) {
            Log::error('Test email for context failed', [
                'email' => $email,
                'context' => $context,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al enviar email de contexto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get sample data for context
     */
    private static function getSampleDataForContext(string $context): array
    {
        $sampleData = [
            'store_management' => [
                'store_name' => 'Mi Tienda Demo',
                'admin_name' => 'Juan Pérez',
                'admin_email' => 'admin@mitienda.com',
                'password' => '********',
                'login_url' => 'https://mitienda.linkiu.bio/admin',
                'support_email' => static::getContextEmail('support')
            ],
            'support' => [
                'ticket_id' => '12345',
                'ticket_subject' => 'Problema con mi tienda',
                'customer_name' => 'María García',
                'response' => 'Hemos revisado tu solicitud y encontramos la solución...',
                'status' => 'Resuelto'
            ],
            'billing' => [
                'invoice_number' => 'INV-2025-001',
                'amount' => '29.99',
                'due_date' => '15/09/2025',
                'store_name' => 'Mi Tienda Demo',
                'plan_name' => 'Plan Básico'
            ]
        ];

        return $sampleData[$context] ?? [];
    }
}