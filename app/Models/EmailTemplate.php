<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'context',
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
     * Contextos disponibles
     */
    const CONTEXTS = [
        'store_management' => [
            'name' => 'Gestión de Tiendas',
            'email' => 'no-responder@linkiu.email',
            'description' => 'Creación de tiendas, credenciales, notificaciones admin-tienda'
        ],
        'support' => [
            'name' => 'Soporte',
            'email' => 'soporte@linkiu.email',
            'description' => 'Tickets, respuestas, cambios de estado'
        ],
        'billing' => [
            'name' => 'Facturación',
            'email' => 'contabilidad@linkiu.email',
            'description' => 'Facturas, pagos, suscripciones'
        ]
    ];

    /**
     * Variables disponibles por contexto
     */
    const CONTEXT_VARIABLES = [
        'store_management' => [
            'store_name', 'admin_name', 'admin_email', 'login_url', 'password', 
            'store_url', 'plan_name', 'app_name', 'support_email'
        ],
        'support' => [
            'ticket_id', 'ticket_subject', 'customer_name', 'admin_name', 
            'ticket_url', 'status', 'response', 'app_name', 'support_email'
        ],
        'billing' => [
            'invoice_number', 'amount', 'due_date', 'store_name', 'plan_name',
            'invoice_url', 'app_name', 'billing_email'
        ]
    ];

    /**
     * Obtener plantilla por clave
     */
    public static function getByKey(string $key): ?self
    {
        return static::where('key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obtener plantillas por contexto
     */
    public static function getByContext(string $context)
    {
        return static::where('context', $context)
                    ->where('is_active', true)
                    ->get();
    }

    /**
     * Reemplazar variables en la plantilla
     */
    public function render(array $data = []): array
    {
        // Variables comunes siempre disponibles
        $commonVariables = [
            'app_name' => config('app.name', 'Linkiu.bio'),
            'app_url' => config('app.url'),
            'current_year' => date('Y'),
            'support_email' => self::CONTEXTS['support']['email'],
            'billing_email' => self::CONTEXTS['billing']['email']
        ];

        // Combinar variables
        $allData = array_merge($commonVariables, $data);

        // Reemplazar en subject
        $subject = $this->replaceVariables($this->subject, $allData);
        
        // Reemplazar en body_html
        $bodyHtml = $this->replaceVariables($this->body_html, $allData);
        
        // Reemplazar en body_text
        $bodyText = $this->replaceVariables($this->body_text, $allData);

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText
        ];
    }

    /**
     * Reemplazar variables en texto
     */
    private function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Validar variables de la plantilla
     */
    public function validateVariables(): array
    {
        $issues = [];
        $content = $this->subject . ' ' . $this->body_html . ' ' . $this->body_text;
        
        // Encontrar todas las variables usadas
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        if (!empty($matches[1])) {
            $allowedVariables = self::CONTEXT_VARIABLES[$this->context] ?? [];
            
            // Agregar variables comunes
            $allowedVariables = array_merge($allowedVariables, [
                'app_name', 'app_url', 'current_year', 'support_email', 'billing_email'
            ]);
            
            foreach ($matches[1] as $variable) {
                $variable = trim($variable);
                if (!in_array($variable, $allowedVariables)) {
                    $issues[] = "Variable '{{{$variable}}}' no está disponible para el contexto '{$this->context}'";
                }
            }
        }
        
        return $issues;
    }

    /**
     * Obtener información del contexto
     */
    public function getContextInfo(): array
    {
        return self::CONTEXTS[$this->context] ?? [];
    }

    /**
     * Obtener email del contexto
     */
    public function getContextEmail(): string
    {
        return self::CONTEXTS[$this->context]['email'] ?? 'no-responder@linkiu.email';
    }

    /**
     * Crear plantillas por defecto
     */
    public static function createDefaults(): void
    {
        $defaults = [
            [
                'key' => 'store_welcome',
                'name' => 'Bienvenida Nueva Tienda',
                'context' => 'store_management',
                'subject' => '🎉 ¡Bienvenido a {{app_name}}! Tu tienda {{store_name}} está lista',
                'body_html' => '
                    <h1>¡Hola {{admin_name}}!</h1>
                    <p>¡Excelentes noticias! Tu tienda <strong>{{store_name}}</strong> ha sido creada exitosamente en {{app_name}}.</p>
                    
                    <h2>Tus credenciales de acceso:</h2>
                    <ul>
                        <li><strong>Email:</strong> {{admin_email}}</li>
                        <li><strong>Contraseña:</strong> {{password}}</li>
                        <li><strong>URL de acceso:</strong> <a href="{{login_url}}">{{login_url}}</a></li>
                    </ul>
                    
                    <p>Tu tienda estará disponible en: <a href="{{store_url}}">{{store_url}}</a></p>
                    
                    <p>Si tienes alguna pregunta, no dudes en contactarnos en {{support_email}}</p>
                    
                    <p>¡Que tengas mucho éxito con tu tienda!</p>
                    <p>El equipo de {{app_name}}</p>
                ',
                'body_text' => '
¡Hola {{admin_name}}!

¡Excelentes noticias! Tu tienda {{store_name}} ha sido creada exitosamente en {{app_name}}.

Tus credenciales de acceso:
- Email: {{admin_email}}
- Contraseña: {{password}}
- URL de acceso: {{login_url}}

Tu tienda estará disponible en: {{store_url}}

Si tienes alguna pregunta, no dudes en contactarnos en {{support_email}}

¡Que tengas mucho éxito con tu tienda!
El equipo de {{app_name}}
                ',
                'variables' => ['store_name', 'admin_name', 'admin_email', 'password', 'login_url', 'store_url'],
                'is_active' => true
            ],
            [
                'key' => 'password_changed',
                'name' => 'Contraseña Cambiada',
                'context' => 'store_management',
                'subject' => 'Contraseña actualizada para {{store_name}}',
                'body_html' => '
                    <h1>Contraseña actualizada</h1>
                    <p>Hola {{admin_name}},</p>
                    <p>Te confirmamos que la contraseña para tu tienda <strong>{{store_name}}</strong> ha sido actualizada exitosamente.</p>
                    <p>Puedes acceder con tus nuevas credenciales en: <a href="{{login_url}}">{{login_url}}</a></p>
                    <p>Si no realizaste este cambio, contacta inmediatamente a {{support_email}}</p>
                    <p>Equipo de {{app_name}}</p>
                ',
                'body_text' => 'Contraseña actualizada\n\nHola {{admin_name}},\n\nTe confirmamos que la contraseña para tu tienda {{store_name}} ha sido actualizada exitosamente.\n\nPuedes acceder con tus nuevas credenciales en: {{login_url}}\n\nSi no realizaste este cambio, contacta inmediatamente a {{support_email}}\n\nEquipo de {{app_name}}',
                'variables' => ['admin_name', 'store_name', 'login_url'],
                'is_active' => true
            ],
            [
                'key' => 'ticket_created',
                'name' => 'Nuevo Ticket Creado',
                'context' => 'support',
                'subject' => 'Nuevo ticket #{{ticket_id}}: {{ticket_subject}}',
                'body_html' => '
                    <h1>Nuevo ticket de soporte</h1>
                    <p>Se ha creado un nuevo ticket de soporte:</p>
                    
                    <ul>
                        <li><strong>Ticket ID:</strong> #{{ticket_id}}</li>
                        <li><strong>Asunto:</strong> {{ticket_subject}}</li>
                        <li><strong>Cliente:</strong> {{customer_name}}</li>
                        <li><strong>Estado:</strong> {{status}}</li>
                    </ul>
                    
                    <p><a href="{{ticket_url}}">Ver ticket completo</a></p>
                    
                    <p>Equipo de {{app_name}}</p>
                ',
                'body_text' => 'Nuevo ticket de soporte\n\nSe ha creado un nuevo ticket de soporte:\n\n- Ticket ID: #{{ticket_id}}\n- Asunto: {{ticket_subject}}\n- Cliente: {{customer_name}}\n- Estado: {{status}}\n\nVer ticket completo: {{ticket_url}}\n\nEquipo de {{app_name}}',
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'status', 'ticket_url'],
                'is_active' => true
            ],
            [
                'key' => 'invoice_created',
                'name' => 'Nueva Factura',
                'context' => 'billing',
                'subject' => 'Factura #{{invoice_number}} - {{store_name}}',
                'body_html' => '
                    <h1>Nueva factura generada</h1>
                    <p>Hola,</p>
                    <p>Se ha generado una nueva factura para la tienda <strong>{{store_name}}</strong>:</p>
                    
                    <ul>
                        <li><strong>Número de factura:</strong> {{invoice_number}}</li>
                        <li><strong>Monto:</strong> ${{amount}}</li>
                        <li><strong>Fecha de vencimiento:</strong> {{due_date}}</li>
                        <li><strong>Plan:</strong> {{plan_name}}</li>
                    </ul>
                    
                    <p><a href="{{invoice_url}}">Ver factura completa</a></p>
                    
                    <p>Equipo de Facturación - {{app_name}}</p>
                ',
                'body_text' => 'Nueva factura generada\n\nHola,\n\nSe ha generado una nueva factura para la tienda {{store_name}}:\n\n- Número de factura: {{invoice_number}}\n- Monto: ${{amount}}\n- Fecha de vencimiento: {{due_date}}\n- Plan: {{plan_name}}\n\nVer factura completa: {{invoice_url}}\n\nEquipo de Facturación - {{app_name}}',
                'variables' => ['invoice_number', 'amount', 'due_date', 'store_name', 'plan_name', 'invoice_url'],
                'is_active' => true
            ],
            [
                'key' => 'ticket_response',
                'name' => 'Respuesta de Ticket',
                'context' => 'support',
                'subject' => 'Respuesta a tu ticket #{{ticket_id}}: {{ticket_subject}}',
                'body_html' => '
                    <h1>Respuesta a tu ticket de soporte</h1>
                    <p>Hola {{customer_name}},</p>
                    <p>Hemos respondido a tu ticket de soporte:</p>
                    
                    <ul>
                        <li><strong>Ticket ID:</strong> #{{ticket_id}}</li>
                        <li><strong>Asunto:</strong> {{ticket_subject}}</li>
                        <li><strong>Estado:</strong> {{status}}</li>
                    </ul>
                    
                    <div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-left: 4px solid #007cba;">
                        <h3>Respuesta:</h3>
                        <p>{{response}}</p>
                    </div>
                    
                    <p><a href="{{ticket_url}}">Ver ticket completo</a></p>
                    
                    <p>Equipo de Soporte - {{app_name}}</p>
                ',
                'body_text' => 'Respuesta a tu ticket de soporte\n\nHola {{customer_name}},\n\nHemos respondido a tu ticket de soporte:\n\n- Ticket ID: #{{ticket_id}}\n- Asunto: {{ticket_subject}}\n- Estado: {{status}}\n\nRespuesta:\n{{response}}\n\nVer ticket completo: {{ticket_url}}\n\nEquipo de Soporte - {{app_name}}',
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'status', 'response', 'ticket_url'],
                'is_active' => true
            ],
            [
                'key' => 'ticket_updated',
                'name' => 'Actualizacion de Ticket',
                'context' => 'support',
                'subject' => 'Actualizacion en tu ticket #{{ticket_id}}',
                'body_html' => '
                    <h1>Actualizacion en tu ticket de soporte</h1>
                    <p>Hola {{customer_name}},</p>
                    <p>Tu ticket de soporte ha sido actualizado:</p>
                    
                    <ul>
                        <li><strong>Ticket ID:</strong> #{{ticket_id}}</li>
                        <li><strong>Asunto:</strong> {{ticket_subject}}</li>
                        <li><strong>Estado:</strong> {{status}}</li>
                        <li><strong>Prioridad:</strong> {{priority}}</li>
                    </ul>
                    
                    <div style="background: #e8f5e8; padding: 15px; margin: 15px 0; border-left: 4px solid #28a745;">
                        <h3>Actualizacion:</h3>
                        <p>{{update_description}}</p>
                    </div>
                    
                    <p><a href="{{ticket_url}}">Ver ticket completo</a></p>
                    
                    <p>Equipo de Soporte - {{app_name}}</p>
                ',
                'body_text' => 'Actualizacion en tu ticket de soporte\n\nHola {{customer_name}},\n\nTu ticket de soporte ha sido actualizado:\n\n- Ticket ID: #{{ticket_id}}\n- Asunto: {{ticket_subject}}\n- Estado: {{status}}\n- Prioridad: {{priority}}\n\nActualizacion:\n{{update_description}}\n\nVer ticket completo: {{ticket_url}}\n\nEquipo de Soporte - {{app_name}}',
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'status', 'priority', 'update_description', 'ticket_url'],
                'is_active' => true
            ]
        ];

        foreach ($defaults as $template) {
            static::updateOrCreate(
                ['key' => $template['key']],
                $template
            );
        }

        Log::info('Plantillas de email por defecto creadas/actualizadas');
    }
}
