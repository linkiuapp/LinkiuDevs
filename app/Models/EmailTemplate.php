<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_key',
        'context',
        'name',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_active'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get template by key
     */
    public static function getTemplate(string $key): ?EmailTemplate
    {
        return static::where('template_key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Render template with data
     */
    public static function renderTemplate(string $key, array $data): array
    {
        $template = static::getTemplate($key);
        
        if (!$template) {
            return [
                'subject' => 'Notificación',
                'body_html' => 'Contenido no disponible',
                'body_text' => 'Contenido no disponible'
            ];
        }

        return $template->replaceVariables($data);
    }

    /**
     * Replace variables in template content
     */
    public function replaceVariables(array $data): array
    {
        $subject = $this->subject;
        $bodyHtml = $this->body_html ?? '';
        $bodyText = $this->body_text ?? '';

        // Get available variables for validation
        $availableVariables = array_keys($this->getAvailableVariables());

        // Validate and replace variables
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            
            // Only replace if the variable is available for this context
            if (in_array($placeholder, $availableVariables)) {
                // Sanitize the replacement value to prevent XSS
                $sanitizedValue = $this->sanitizeVariableValue($value);
                
                $subject = str_replace($placeholder, $sanitizedValue, $subject);
                $bodyHtml = str_replace($placeholder, $sanitizedValue, $bodyHtml);
                $bodyText = str_replace($placeholder, strip_tags($sanitizedValue), $bodyText);
            }
        }

        // Log any unreplaced variables for debugging
        $this->logUnreplacedVariables($subject . ' ' . $bodyHtml . ' ' . $bodyText);

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText
        ];
    }

    /**
     * Sanitize variable value to prevent XSS
     */
    private function sanitizeVariableValue($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        // Basic HTML encoding for safety
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Log unreplaced variables for debugging
     */
    private function logUnreplacedVariables(string $content): void
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        if (!empty($matches[1])) {
            $unreplacedVars = array_unique($matches[1]);
            \Log::warning('Unreplaced template variables found', [
                'template_key' => $this->template_key,
                'template_id' => $this->id,
                'unreplaced_variables' => $unreplacedVars,
                'available_variables' => array_keys($this->getAvailableVariables())
            ]);
        }
    }

    /**
     * Validate template variables exist before replacement
     */
    public function validateTemplateVariables(): array
    {
        $issues = [];
        $availableVariables = array_keys($this->getAvailableVariables());
        
        // Check subject
        preg_match_all('/\{\{([^}]+)\}\}/', $this->subject, $matches);
        foreach ($matches[1] as $variable) {
            $fullVar = '{{' . $variable . '}}';
            if (!in_array($fullVar, $availableVariables)) {
                $issues[] = "Variable '{$fullVar}' en el asunto no está disponible para el contexto '{$this->context}'";
            }
        }
        
        // Check HTML body
        if ($this->body_html) {
            preg_match_all('/\{\{([^}]+)\}\}/', $this->body_html, $matches);
            foreach ($matches[1] as $variable) {
                $fullVar = '{{' . $variable . '}}';
                if (!in_array($fullVar, $availableVariables)) {
                    $issues[] = "Variable '{$fullVar}' en el contenido HTML no está disponible para el contexto '{$this->context}'";
                }
            }
        }
        
        // Check text body
        if ($this->body_text) {
            preg_match_all('/\{\{([^}]+)\}\}/', $this->body_text, $matches);
            foreach ($matches[1] as $variable) {
                $fullVar = '{{' . $variable . '}}';
                if (!in_array($fullVar, $availableVariables)) {
                    $issues[] = "Variable '{$fullVar}' en el contenido de texto no está disponible para el contexto '{$this->context}'";
                }
            }
        }
        
        return $issues;
    }

    /**
     * Get available variables for this template's context
     */
    public function getAvailableVariables(): array
    {
        $contextVariables = [
            'store_management' => [
                '{{store_name}}' => 'Nombre de la tienda',
                '{{admin_name}}' => 'Nombre del administrador',
                '{{admin_email}}' => 'Email del administrador',
                '{{password}}' => 'Contraseña temporal',
                '{{login_url}}' => 'URL de acceso',
                '{{support_email}}' => 'Email de soporte'
            ],
            'support' => [
                '{{ticket_id}}' => 'ID del ticket',
                '{{ticket_subject}}' => 'Asunto del ticket',
                '{{customer_name}}' => 'Nombre del cliente',
                '{{response}}' => 'Respuesta del ticket',
                '{{status}}' => 'Estado del ticket'
            ],
            'billing' => [
                '{{invoice_number}}' => 'Número de factura',
                '{{amount}}' => 'Monto de la factura',
                '{{due_date}}' => 'Fecha de vencimiento',
                '{{store_name}}' => 'Nombre de la tienda',
                '{{plan_name}}' => 'Nombre del plan',
                '{{user_name}}' => 'Nombre del usuario',
                '{{days}}' => 'Días hasta vencimiento',
                '{{days_left}}' => 'Días restantes',
                '{{billing_url}}' => 'URL de facturación'
            ]
        ];

        return $contextVariables[$this->context] ?? [];
    }

    /**
     * Relationship with email setting
     */
    public function emailSetting(): BelongsTo
    {
        return $this->belongsTo(EmailSetting::class, 'context', 'context');
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by context
     */
    public function scopeByContext($query, string $context)
    {
        return $query->where('context', $context);
    }
}
