<?php

namespace App\Features\SuperLinkiu\Controllers;

use App\Http\Controllers\Controller;
use App\Shared\Models\EmailConfiguration;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Services\EmailSecurityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmailConfigurationController extends Controller
{
    /**
     * Mostrar configuración de email
     */
    public function index(): View
    {
        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $defaultTemplates = EmailConfiguration::getDefaultTemplates();
        
        return view('superlinkiu::email.index', compact('config', 'defaultTemplates'));
    }

    /**
     * Actualizar configuración SMTP
     */
    public function updateSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|between:1,65535',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        // Obtener configuración existente o crear nueva
        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        
        // Si no se proporciona password, mantener el existente
        if (empty($validated['smtp_password']) && $config->exists) {
            unset($validated['smtp_password']);
        }

        $config->fill($validated);
        $config->save();

        // Activar esta configuración
        $config->activate();

        return redirect()
            ->route('superlinkiu.email.index')
            ->with('success', 'Configuración SMTP actualizada exitosamente.');
    }

    /**
     * Actualizar plantillas de email
     */
    public function updateTemplates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_created_template' => 'nullable|string',
            'ticket_response_template' => 'nullable|string',
            'ticket_status_changed_template' => 'nullable|string',
            'ticket_assigned_template' => 'nullable|string',
        ]);

        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $config->fill($validated);
        $config->save();

        return redirect()
            ->route('superlinkiu.email.index')
            ->with('success', 'Plantillas de email actualizadas exitosamente.');
    }

    /**
     * Actualizar configuración de eventos
     */
    public function updateEvents(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'send_on_ticket_created' => 'boolean',
            'send_on_ticket_response' => 'boolean',
            'send_on_status_change' => 'boolean',
            'send_on_ticket_assigned' => 'boolean',
        ]);

        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $config->fill($validated);
        $config->save();

        return redirect()
            ->route('superlinkiu.email.index')
            ->with('success', 'Configuración de eventos actualizada exitosamente.');
    }

    /**
     * Probar conexión SMTP
     */
    public function testConnection(Request $request): JsonResponse
    {
        $config = EmailConfiguration::getActive();
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No hay configuración SMTP activa.'
            ]);
        }

        $testEmail = $request->input('test_email');
        $result = $config->testConnection($testEmail);

        return response()->json($result);
    }

    /**
     * Restaurar plantillas por defecto
     */
    public function restoreDefaultTemplates(): JsonResponse
    {
        $config = EmailConfiguration::getActive() ?: new EmailConfiguration();
        $defaultTemplates = EmailConfiguration::getDefaultTemplates();
        
        $config->update([
            'ticket_created_template' => $defaultTemplates['ticket_created'],
            'ticket_response_template' => $defaultTemplates['ticket_response'],
            'ticket_status_changed_template' => $defaultTemplates['ticket_status_changed'],
            'ticket_assigned_template' => $defaultTemplates['ticket_assigned'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plantillas restauradas a valores por defecto.',
            'templates' => $defaultTemplates
        ]);
    }

    /**
     * Alternar estado activo de la configuración
     */
    public function toggleActive(): JsonResponse
    {
        $config = EmailConfiguration::getActive();
        
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No hay configuración para activar/desactivar.'
            ]);
        }

        $config->update(['is_active' => !$config->is_active]);

        return response()->json([
            'success' => true,
            'message' => $config->is_active ? 'Configuración activada.' : 'Configuración desactivada.',
            'is_active' => $config->is_active
        ]);
    }

    /**
     * Show email settings configuration
     */
    public function emailSettings(): View
    {
        $settings = EmailSetting::all()->keyBy('context');
        
        // Ensure all contexts exist with defaults
        $contexts = [
            'store_management' => [
                'name' => 'Gestión de Tiendas',
                'description' => 'Creación de tiendas, cambios de contraseña, notificaciones admin-tienda',
                'default_email' => 'no-responder@linkiudev.co'
            ],
            'support' => [
                'name' => 'Soporte',
                'description' => 'CRUD de tickets, notificaciones de soporte',
                'default_email' => 'soporte@linkiudev.co'
            ],
            'billing' => [
                'name' => 'Facturación',
                'description' => 'Todo relacionado con facturación',
                'default_email' => 'contabilidad@linkiudev.co'
            ]
        ];

        return view('superlinkiu::email.settings.index', compact('settings', 'contexts'));
    }

    /**
     * Update email settings
     */
    public function updateEmailSettings(Request $request): RedirectResponse
    {
        // Rate limiting
        $key = 'email-config:' . $request->ip() . ':' . ($request->user()->id ?? 'guest');
        
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.");
        }
        
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);
        
        // Enhanced validation with custom rules
        $validated = $request->validate([
            'store_management_email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    $validation = EmailSecurityService::validateEmailAddress($value);
                    if (!$validation['valid']) {
                        $fail(implode(', ', $validation['issues']));
                    }
                }
            ],
            'support_email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    $validation = EmailSecurityService::validateEmailAddress($value);
                    if (!$validation['valid']) {
                        $fail(implode(', ', $validation['issues']));
                    }
                }
            ],
            'billing_email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    $validation = EmailSecurityService::validateEmailAddress($value);
                    if (!$validation['valid']) {
                        $fail(implode(', ', $validation['issues']));
                    }
                }
            ],
        ], [
            '*.email' => 'El formato del email no es válido.',
            '*.required' => 'Este campo es obligatorio.',
            '*.max' => 'El email no puede tener más de 255 caracteres.',
            '*.regex' => 'El formato del email no es válido.',
        ]);

        try {
            // Audit configuration change attempt
            EmailSecurityService::auditConfigurationChange('email_settings_update_attempted', $validated, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Update each context
            EmailSetting::updateContext('store_management', $validated['store_management_email']);
            EmailSetting::updateContext('support', $validated['support_email']);
            EmailSetting::updateContext('billing', $validated['billing_email']);

            // Audit successful configuration change
            EmailSecurityService::auditConfigurationChange('email_settings_updated', $validated);

            return redirect()
                ->route('superlinkiu.email.settings')
                ->with('success', 'Configuración de emails actualizada exitosamente.');

        } catch (\Exception $e) {
            // Log configuration change failure
            \Log::error('Email configuration update failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $validated
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la configuración: ' . $e->getMessage());
        }
    }

    /**
     * Show templates list
     */
    public function templateIndex(): View
    {
        $templates = EmailTemplate::with('emailSetting')
            ->orderBy('context')
            ->orderBy('name')
            ->get()
            ->groupBy('context');

        $contexts = [
            'store_management' => 'Gestión de Tiendas',
            'support' => 'Soporte',
            'billing' => 'Facturación'
        ];

        return view('superlinkiu::email.templates.index', compact('templates', 'contexts'));
    }

    /**
     * Show template edit form
     */
    public function templateEdit(EmailTemplate $template): View
    {
        $availableVariables = $template->getAvailableVariables();
        
        return view('superlinkiu::email.templates.edit', compact('template', 'availableVariables'));
    }

    /**
     * Update template
     */
    public function templateUpdate(Request $request, EmailTemplate $template): RedirectResponse
    {
        // Rate limiting
        $key = 'email-config:' . $request->ip() . ':' . ($request->user()->id ?? 'guest');
        
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.");
        }
        
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_áéíóúñÁÉÍÓÚÑ]+$/'
            ],
            'subject' => [
                'required',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    // Validate template variables exist
                    preg_match_all('/\{\{([^}]+)\}\}/', $value, $matches);
                    if (!empty($matches[1])) {
                        $template = EmailTemplate::find(request()->route('template')->id);
                        $availableVars = array_keys($template->getAvailableVariables());
                        foreach ($matches[1] as $variable) {
                            $fullVar = '{{' . $variable . '}}';
                            if (!in_array($fullVar, $availableVars)) {
                                $fail("La variable {$fullVar} no está disponible para este contexto.");
                            }
                        }
                    }
                }
            ],
            'body_html' => [
                'nullable',
                'string',
                'max:65535',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        // Check for potentially dangerous HTML
                        $dangerousTags = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button'];
                        foreach ($dangerousTags as $tag) {
                            if (stripos($value, "<{$tag}") !== false) {
                                $fail("El contenido HTML contiene etiquetas no permitidas: {$tag}");
                            }
                        }
                        
                        // Validate template variables exist
                        preg_match_all('/\{\{([^}]+)\}\}/', $value, $matches);
                        if (!empty($matches[1])) {
                            $template = EmailTemplate::find(request()->route('template')->id);
                            $availableVars = array_keys($template->getAvailableVariables());
                            foreach ($matches[1] as $variable) {
                                $fullVar = '{{' . $variable . '}}';
                                if (!in_array($fullVar, $availableVars)) {
                                    $fail("La variable {$fullVar} no está disponible para este contexto.");
                                }
                            }
                        }
                    }
                }
            ],
            'body_text' => [
                'nullable',
                'string',
                'max:65535',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        // Validate template variables exist
                        preg_match_all('/\{\{([^}]+)\}\}/', $value, $matches);
                        if (!empty($matches[1])) {
                            $template = EmailTemplate::find(request()->route('template')->id);
                            $availableVars = array_keys($template->getAvailableVariables());
                            foreach ($matches[1] as $variable) {
                                $fullVar = '{{' . $variable . '}}';
                                if (!in_array($fullVar, $availableVars)) {
                                    $fail("La variable {$fullVar} no está disponible para este contexto.");
                                }
                            }
                        }
                    }
                }
            ],
            'is_active' => 'boolean'
        ], [
            'name.required' => 'El nombre de la plantilla es obligatorio.',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos.',
            'subject.required' => 'El asunto es obligatorio.',
            'subject.max' => 'El asunto no puede tener más de 500 caracteres.',
            'body_html.max' => 'El contenido HTML es demasiado largo.',
            'body_text.max' => 'El contenido de texto es demasiado largo.',
        ]);

        try {
            // Audit template update attempt
            $changes = array_diff_assoc($validated, $template->only(array_keys($validated)));
            EmailSecurityService::auditConfigurationChange('template_update_attempted', $changes, [
                'template_id' => $template->id,
                'template_key' => $template->template_key,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Enhanced HTML sanitization using security service
            if (!empty($validated['body_html'])) {
                // Validate content for security issues first
                $securityIssues = EmailSecurityService::validateTemplateContent($validated['body_html']);
                if (!empty($securityIssues)) {
                    EmailSecurityService::logSecurityEvent('template_security_violation', [
                        'template_id' => $template->id,
                        'issues' => $securityIssues
                    ]);
                    
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Contenido HTML contiene elementos de seguridad no permitidos: ' . implode(', ', $securityIssues));
                }
                
                $validated['body_html'] = EmailSecurityService::sanitizeHtmlContent($validated['body_html']);
            }

            // Sanitize text content (remove any potential HTML)
            if (!empty($validated['body_text'])) {
                $validated['body_text'] = strip_tags($validated['body_text']);
            }

            $template->update($validated);

            // Audit successful template update
            EmailSecurityService::auditConfigurationChange('template_updated', $changes, [
                'template_id' => $template->id,
                'template_key' => $template->template_key
            ]);

            return redirect()
                ->route('superlinkiu.email.templates.index')
                ->with('success', 'Plantilla actualizada exitosamente.');

        } catch (\Exception $e) {
            // Log template update failure
            \Log::error('Email template update failed', [
                'template_id' => $template->id,
                'template_key' => $template->template_key,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $validated
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la plantilla: ' . $e->getMessage());
        }
    }



    /**
     * Validate email configuration
     */
    public function validateConfiguration(): JsonResponse
    {
        $validation = EmailService::validateEmailConfiguration();
        
        return response()->json([
            'valid' => $validation['valid'],
            'issues' => $validation['issues'],
            'message' => $validation['valid'] 
                ? 'Configuración válida' 
                : 'Se encontraron ' . count($validation['issues']) . ' problemas'
        ]);
    }

    /**
     * Preview template with sample data
     */
    public function templatePreview(Request $request, EmailTemplate $template): JsonResponse
    {
        // If form data is provided, use it for preview
        if ($request->has('subject') || $request->has('body_html') || $request->has('body_text')) {
            // Create a temporary template instance with form data
            $tempTemplate = clone $template;
            $tempTemplate->subject = $request->input('subject', $template->subject);
            $tempTemplate->body_html = $request->input('body_html', $template->body_html);
            $tempTemplate->body_text = $request->input('body_text', $template->body_text);
            
            // Sanitize HTML content
            if (!empty($tempTemplate->body_html)) {
                $tempTemplate->body_html = strip_tags($tempTemplate->body_html, '<p><br><strong><em><ul><li><ol><a><div><span>');
            }
            
            $template = $tempTemplate;
        }

        // Sample data for preview
        $sampleData = [
            'store_management' => [
                'store_name' => 'Mi Tienda Demo',
                'admin_name' => 'Juan Pérez',
                'admin_email' => 'admin@mitienda.com',
                'password' => '********',
                'login_url' => 'https://mitienda.linkiu.bio/admin',
                'support_email' => 'soporte@linkiudev.co'
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

        $data = $sampleData[$template->context] ?? [];
        $rendered = $template->replaceVariables($data);

        return response()->json([
            'success' => true,
            'preview' => $rendered,
            'sample_data' => $data
        ]);
    }

    /**
     * Send test email with template
     */
    public function sendTestEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'template_id' => 'nullable|exists:email_templates,id',
            'context' => 'nullable|in:store_management,support,billing'
        ]);

        try {
            $testEmail = $validated['email'];
            
            // If template_id is provided, send specific template
            if (!empty($validated['template_id'])) {
                $template = EmailTemplate::findOrFail($validated['template_id']);
                
                // Sample data for the template
                $sampleData = $this->getSampleDataForContext($template->context);
                
                $result = EmailService::sendTestEmailWithTemplate($testEmail, $template, $sampleData);
            } 
            // If context is provided, send test email for that context
            elseif (!empty($validated['context'])) {
                $result = EmailService::sendTestEmailForContext($testEmail, $validated['context']);
            }
            // Send general test email
            else {
                $result = EmailService::sendTestEmail($testEmail);
            }

            if ($result['success']) {
                // Log successful test email
                EmailSecurityService::auditConfigurationChange('test_email_sent', [
                    'recipient' => $testEmail,
                    'template_id' => $validated['template_id'] ?? null,
                    'context' => $validated['context'] ?? 'general'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Email de prueba enviado exitosamente a ' . $testEmail
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar email: ' . $result['message']
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Test email sending failed', [
                'user_id' => auth()->id(),
                'recipient' => $validated['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al enviar el email de prueba'
            ]);
        }
    }

    /**
     * Get sample data for template context
     */
    private function getSampleDataForContext(string $context): array
    {
        $sampleData = [
            'store_management' => [
                'store_name' => 'Mi Tienda Demo',
                'admin_name' => 'Juan Pérez',
                'admin_email' => 'admin@mitienda.com',
                'password' => '********',
                'login_url' => 'https://mitienda.linkiu.bio/admin',
                'support_email' => 'soporte@linkiudev.co'
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