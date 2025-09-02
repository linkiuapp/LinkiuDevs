<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_key',
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
            'store_url', 'plan_name', 'app_name', 'support_email', 'store_id',
            'old_value', 'new_value', 'change_type', 'changed_by',
            'change_date', 'store_status', 'verification_status'
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
        return static::where('template_key', $key)
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
            // ================================================================ 
            // 🏪 PLANTILLAS HERMOSAS PARA GESTIÓN DE TIENDAS
            // ================================================================
            [
                'template_key' => 'store_welcome',
                'name' => 'Bienvenida Nueva Tienda',
                'context' => 'store_management',
                'subject' => '🎉 ¡Bienvenido a {{app_name}}! Tu tienda {{store_name}} está lista',
                'body_html' => '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Bienvenido a {{app_name}}</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}.container{max-width:600px;margin:0 auto;background:#ffffff}.header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:30px 20px;text-align:center}.header h1{color:white;margin:0;font-size:28px;font-weight:600}.content{padding:30px}.welcome-box{background:#f8f9ff;border-left:4px solid #667eea;padding:20px;margin:20px 0;border-radius:8px}.store-info{background:#ffffff;border:2px solid #e2e8f0;border-radius:12px;padding:25px;margin:25px 0}.store-info h2{color:#667eea;margin-top:0;font-size:20px}.store-link{display:inline-block;background:#667eea;color:white;padding:15px 30px;text-decoration:none;border-radius:8px;margin:15px 0;font-weight:600}.store-link:hover{background:#5a6fd8}.footer{background:#f7fafc;padding:20px;text-align:center;font-size:14px;color:#64748b;border-top:1px solid #e2e8f0}.emoji{font-size:24px}</style></head><body><div class="container"><div class="header"><div class="emoji">🎉</div><h1>¡Bienvenido a {{app_name}}!</h1></div><div class="content"><div class="welcome-box"><h2>¡Hola {{admin_name}}!</h2><p><strong>¡Excelentes noticias!</strong> Tu tienda <strong>{{store_name}}</strong> ha sido creada exitosamente en {{app_name}}.</p></div><div class="store-info"><h2>🏪 Información de tu tienda:</h2><p><strong>📦 Plan contratado:</strong> {{plan_name}}</p><p><strong>🌐 Tu tienda estará disponible en:</strong></p><a href="{{store_url}}" class="store-link">🚀 Ver mi tienda: {{store_url}}</a></div><p>📧 En breve recibirás un email adicional con tus credenciales de acceso al panel de administración.</p><p>💬 Si tienes alguna pregunta, no dudes en contactarnos en <a href="mailto:{{support_email}}">{{support_email}}</a></p><p><strong>¡Que tengas mucho éxito con tu tienda!</strong> 🚀</p></div><div class="footer"><p>El equipo de {{app_name}} • {{current_year}}</p><p>Este es un email automático, por favor no respondas a esta dirección.</p></div></div></body></html>',
                'body_text' => '🎉 ¡Bienvenido a {{app_name}}!\n\n¡Hola {{admin_name}}!\n\n¡Excelentes noticias! Tu tienda {{store_name}} ha sido creada exitosamente en {{app_name}}.\n\n🏪 Información de tu tienda:\n- Plan contratado: {{plan_name}}\n- Tu tienda estará disponible en: {{store_url}}\n\n📧 En breve recibirás un email adicional con tus credenciales de acceso al panel de administración.\n\n💬 Si tienes alguna pregunta, no dudes en contactarnos en {{support_email}}\n\n¡Que tengas mucho éxito con tu tienda! 🚀\n\nEl equipo de {{app_name}}',
                'variables' => ['store_name', 'admin_name', 'store_url', 'plan_name', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'store_credentials',
                'name' => 'Credenciales de Acceso',
                'context' => 'store_management', 
                'subject' => '🔑 Credenciales de acceso para {{store_name}}',
                'body_html' => '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Credenciales de acceso</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}.container{max-width:600px;margin:0 auto;background:#ffffff}.header{background:linear-gradient(135deg,#10b981 0%,#059669 100%);padding:30px 20px;text-align:center}.header h1{color:white;margin:0;font-size:26px;font-weight:600}.content{padding:30px}.credentials-box{background:#f0fdf4;border:2px solid #10b981;border-radius:12px;padding:25px;margin:25px 0}.credentials-box h2{color:#059669;margin-top:0;font-size:18px}.credential-item{background:white;padding:15px;margin:10px 0;border-radius:8px;border-left:4px solid #10b981}.credential-label{font-weight:600;color:#374151;display:block}.credential-value{font-family:monospace;background:#f9fafb;padding:8px 12px;border-radius:6px;margin-top:5px;word-break:break-all}.login-button{display:inline-block;background:#10b981;color:white;padding:15px 30px;text-decoration:none;border-radius:8px;margin:20px 0;font-weight:600;font-size:16px}.login-button:hover{background:#059669}.security-notice{background:#fef3c7;border-left:4px solid #f59e0b;padding:15px;margin:20px 0;border-radius:8px}.footer{background:#f7fafc;padding:20px;text-align:center;font-size:14px;color:#64748b;border-top:1px solid #e2e8f0}.emoji{font-size:24px}</style></head><body><div class="container"><div class="header"><div class="emoji">🔑</div><h1>Credenciales de Acceso</h1></div><div class="content"><p>Hola <strong>{{admin_name}}</strong>,</p><p>Aquí tienes las credenciales para acceder al panel de administración de tu tienda <strong>{{store_name}}</strong>:</p><div class="credentials-box"><h2>🔐 Datos de acceso:</h2><div class="credential-item"><span class="credential-label">📧 Email de administrador:</span><div class="credential-value">{{admin_email}}</div></div><div class="credential-item"><span class="credential-label">🔒 Contraseña:</span><div class="credential-value">{{password}}</div></div><div class="credential-item"><span class="credential-label">🌐 URL del panel de administración:</span><div class="credential-value">{{login_url}}</div></div></div><div style="text-align:center;"><a href="{{login_url}}" class="login-button">🚀 Acceder a mi panel</a></div><div class="security-notice"><h3>⚠️ Importante por tu seguridad:</h3><ul><li>📝 <strong>Guarda estas credenciales</strong> en un lugar seguro</li><li>🔄 <strong>Cambia tu contraseña</strong> después del primer acceso</li><li>🚫 <strong>No compartas</strong> estas credenciales con terceros</li><li>📞 <strong>Contacta soporte</strong> si tienes problemas de acceso</li></ul></div><p>💬 ¿Necesitas ayuda? Escríbenos a <a href="mailto:{{support_email}}">{{support_email}}</a></p></div><div class="footer"><p>El equipo de {{app_name}} • {{current_year}}</p><p>Este es un email automático, por favor no respondas a esta dirección.</p></div></div></body></html>',
                'body_text' => '🔑 Credenciales de Acceso - {{store_name}}\n\nHola {{admin_name}},\n\nAquí tienes las credenciales para acceder al panel de administración de tu tienda {{store_name}}:\n\n🔐 DATOS DE ACCESO:\n- Email: {{admin_email}}\n- Contraseña: {{password}}\n- Panel de administración: {{login_url}}\n\n⚠️ IMPORTANTE POR TU SEGURIDAD:\n- Guarda estas credenciales en un lugar seguro\n- Cambia tu contraseña después del primer acceso\n- No compartas estas credenciales con terceros\n- Contacta soporte si tienes problemas de acceso\n\n💬 ¿Necesitas ayuda? Escríbenos a {{support_email}}\n\nEl equipo de {{app_name}}',
                'variables' => ['store_name', 'admin_name', 'admin_email', 'password', 'login_url', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'store_status_changed',
                'name' => 'Cambio de Estado de Tienda',
                'context' => 'store_management',
                'subject' => '📊 Estado de tu tienda {{store_name}} actualizado',
                'body_html' => '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Estado actualizado</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}.container{max-width:600px;margin:0 auto;background:#ffffff}.header{background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);padding:30px 20px;text-align:center}.header h1{color:white;margin:0;font-size:26px;font-weight:600}.content{padding:30px}.change-box{background:#fffbeb;border:2px solid #f59e0b;border-radius:12px;padding:25px;margin:25px 0}.change-item{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e5e7eb}.change-item:last-child{border-bottom:none}.status-badge{padding:6px 16px;border-radius:20px;font-weight:600;font-size:14px}.footer{background:#f7fafc;padding:20px;text-align:center;font-size:14px;color:#64748b;border-top:1px solid #e2e8f0}.emoji{font-size:24px}</style></head><body><div class="container"><div class="header"><div class="emoji">📊</div><h1>Estado Actualizado</h1></div><div class="content"><p>Hola <strong>{{admin_name}}</strong>,</p><p>Te notificamos que el estado de tu tienda <strong>{{store_name}}</strong> ha sido actualizado.</p><div class="change-box"><h2>🔄 Detalles del cambio:</h2><div class="change-item"><span><strong>Tienda:</strong></span><span>{{store_name}}</span></div><div class="change-item"><span><strong>Estado anterior:</strong></span><span class="status-badge">{{old_value}}</span></div><div class="change-item"><span><strong>Nuevo estado:</strong></span><span class="status-badge">{{new_value}}</span></div><div class="change-item"><span><strong>Fecha del cambio:</strong></span><span>{{change_date}}</span></div><div class="change-item"><span><strong>Actualizado por:</strong></span><span>{{changed_by}}</span></div></div><p>🔗 <a href="{{login_url}}">Acceder a mi panel de administración</a></p><p>💬 ¿Tienes preguntas? Escríbenos a <a href="mailto:{{support_email}}">{{support_email}}</a></p></div><div class="footer"><p>El equipo de {{app_name}} • {{current_year}}</p><p>Este es un email automático, por favor no respondas a esta dirección.</p></div></div></body></html>',
                'body_text' => '📊 Estado Actualizado - {{store_name}}\n\nHola {{admin_name}},\n\nTe notificamos que el estado de tu tienda {{store_name}} ha sido actualizado.\n\n🔄 DETALLES DEL CAMBIO:\n- Tienda: {{store_name}}\n- Estado anterior: {{old_value}}\n- Nuevo estado: {{new_value}}\n- Fecha del cambio: {{change_date}}\n- Actualizado por: {{changed_by}}\n\n🔗 Panel de administración: {{login_url}}\n💬 ¿Tienes preguntas? Escríbenos a {{support_email}}\n\nEl equipo de {{app_name}}',
                'variables' => ['store_name', 'admin_name', 'old_value', 'new_value', 'change_date', 'changed_by', 'login_url', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'store_plan_changed',
                'name' => 'Cambio de Plan de Tienda',
                'context' => 'store_management',
                'subject' => '⚡ Plan actualizado para {{store_name}}',
                'body_html' => '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Plan actualizado</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}.container{max-width:600px;margin:0 auto;background:#ffffff}.header{background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);padding:30px 20px;text-align:center}.header h1{color:white;margin:0;font-size:26px;font-weight:600}.content{padding:30px}.plan-comparison{display:flex;gap:20px;margin:25px 0}.plan-box{flex:1;padding:20px;border-radius:12px;text-align:center}.old-plan{background:#fee2e2;border:2px solid #ef4444}.new-plan{background:#dcfce7;border:2px solid #10b981}.plan-name{font-weight:600;font-size:18px;margin-bottom:10px}.plan-features{background:#f8f9ff;padding:20px;border-radius:8px;margin:20px 0}.login-button{display:inline-block;background:#8b5cf6;color:white;padding:15px 30px;text-decoration:none;border-radius:8px;margin:20px 0;font-weight:600;font-size:16px}.login-button:hover{background:#7c3aed}.footer{background:#f7fafc;padding:20px;text-align:center;font-size:14px;color:#64748b;border-top:1px solid #e2e8f0}.emoji{font-size:24px}@media (max-width:600px){.plan-comparison{flex-direction:column}}</style></head><body><div class="container"><div class="header"><div class="emoji">⚡</div><h1>Plan Actualizado</h1></div><div class="content"><p>Hola <strong>{{admin_name}}</strong>,</p><p>¡Excelente! El plan de tu tienda <strong>{{store_name}}</strong> ha sido actualizado exitosamente.</p><div class="plan-comparison"><div class="plan-box old-plan"><div class="plan-name">📦 Plan anterior</div><div>{{old_value}}</div></div><div style="display:flex;align-items:center;font-size:24px;">→</div><div class="plan-box new-plan"><div class="plan-name">🚀 Nuevo plan</div><div>{{new_value}}</div></div></div><div class="plan-features"><h3>✨ ¿Qué puedes hacer ahora?</h3><p>Con tu nuevo plan <strong>{{new_value}}</strong> tienes acceso a nuevas funcionalidades y beneficios.</p><p>🔗 <strong>Explora las nuevas características en tu panel de administración.</strong></p></div><div style="text-align:center;"><a href="{{login_url}}" class="login-button">🎯 Explorar mi panel</a></div><p>📅 <strong>Fecha de actualización:</strong> {{change_date}}</p><p>👤 <strong>Actualizado por:</strong> {{changed_by}}</p><p>💬 ¿Tienes preguntas sobre tu nuevo plan? Escríbenos a <a href="mailto:{{support_email}}">{{support_email}}</a></p></div><div class="footer"><p>El equipo de {{app_name}} • {{current_year}}</p><p>Este es un email automático, por favor no respondas a esta dirección.</p></div></div></body></html>',
                'body_text' => '⚡ Plan Actualizado - {{store_name}}\n\nHola {{admin_name}},\n\n¡Excelente! El plan de tu tienda {{store_name}} ha sido actualizado exitosamente.\n\n🔄 CAMBIO REALIZADO:\n- Plan anterior: {{old_value}}\n- Nuevo plan: {{new_value}}\n- Fecha: {{change_date}}\n- Actualizado por: {{changed_by}}\n\n✨ Con tu nuevo plan {{new_value}} tienes acceso a nuevas funcionalidades y beneficios.\n\n🎯 Panel de administración: {{login_url}}\n💬 ¿Preguntas sobre tu nuevo plan? Escríbenos a {{support_email}}\n\nEl equipo de {{app_name}}',
                'variables' => ['store_name', 'admin_name', 'old_value', 'new_value', 'change_date', 'changed_by', 'login_url', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'store_verified',
                'name' => 'Tienda Verificada',
                'context' => 'store_management',
                'subject' => '✅ ¡Tu tienda {{store_name}} ha sido verificada!',
                'body_html' => '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Tienda verificada</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}.container{max-width:600px;margin:0 auto;background:#ffffff}.header{background:linear-gradient(135deg,#10b981 0%,#059669 100%);padding:30px 20px;text-align:center}.header h1{color:white;margin:0;font-size:26px;font-weight:600}.content{padding:30px}.verification-badge{background:#dcfce7;border:2px solid #10b981;border-radius:12px;padding:25px;margin:25px 0;text-align:center}.verification-badge h2{color:#059669;margin:0 0 15px 0;font-size:24px}.benefits-box{background:#f0f9ff;border-left:4px solid #0ea5e9;padding:20px;margin:20px 0;border-radius:8px}.benefit-item{display:flex;align-items:center;margin:10px 0}.benefit-item .emoji{margin-right:10px;font-size:18px}.footer{background:#f7fafc;padding:20px;text-align:center;font-size:14px;color:#64748b;border-top:1px solid #e2e8f0}.emoji{font-size:24px}</style></head><body><div class="container"><div class="header"><div class="emoji">✅</div><h1>¡Tienda Verificada!</h1></div><div class="content"><p>¡Felicidades <strong>{{admin_name}}</strong>!</p><div class="verification-badge"><h2>🎉 {{store_name}}</h2><p><strong>¡Ha sido verificada exitosamente!</strong></p><p>Tu tienda ahora cuenta con el sello de verificación oficial.</p></div><div class="benefits-box"><h3>🚀 Beneficios de la verificación:</h3><div class="benefit-item"><span class="emoji">🛡️</span><span><strong>Mayor confianza</strong> de tus clientes</span></div><div class="benefit-item"><span class="emoji">🔍</span><span><strong>Mejor posicionamiento</strong> en búsquedas</span></div><div class="benefit-item"><span class="emoji">✨</span><span><strong>Distintivo visual</strong> en tu tienda</span></div><div class="benefit-item"><span class="emoji">💎</span><span><strong>Acceso a funciones premium</strong></span></div></div><p>🌐 <strong>Tu tienda verificada:</strong> <a href="{{store_url}}">{{store_url}}</a></p><p>⚙️ <strong>Panel de administración:</strong> <a href="{{login_url}}">{{login_url}}</a></p><p>📅 <strong>Verificado el:</strong> {{change_date}}</p><p>👤 <strong>Verificado por:</strong> {{changed_by}}</p><p>💬 Si tienes preguntas, escríbenos a <a href="mailto:{{support_email}}">{{support_email}}</a></p></div><div class="footer"><p>El equipo de {{app_name}} • {{current_year}}</p></div></div></body></html>',
                'body_text' => '✅ ¡Tienda Verificada! - {{store_name}}\n\n¡Felicidades {{admin_name}}!\n\nTu tienda {{store_name}} ha sido verificada exitosamente y ahora cuenta con el sello de verificación oficial.\n\n🚀 BENEFICIOS DE LA VERIFICACIÓN:\n- 🛡️ Mayor confianza de tus clientes\n- 🔍 Mejor posicionamiento en búsquedas\n- ✨ Distintivo visual en tu tienda\n- 💎 Acceso a funciones premium\n\n🌐 Tu tienda verificada: {{store_url}}\n⚙️ Panel de administración: {{login_url}}\n\n📅 Verificado el: {{change_date}}\n👤 Verificado por: {{changed_by}}\n\n💬 ¿Preguntas? Escríbenos a {{support_email}}\n\nEl equipo de {{app_name}}',
                'variables' => ['store_name', 'admin_name', 'store_url', 'login_url', 'change_date', 'changed_by', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'store_unverified',
                'name' => 'Verificación Removida',
                'context' => 'store_management',
                'subject' => '⚠️ Verificación removida de {{store_name}}',
                'body_html' => '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Verificación removida</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}.container{max-width:600px;margin:0 auto;background:#ffffff}.header{background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);padding:30px 20px;text-align:center}.header h1{color:white;margin:0;font-size:26px;font-weight:600}.content{padding:30px}.warning-box{background:#fef3c7;border:2px solid #f59e0b;border-radius:12px;padding:25px;margin:25px 0}.next-steps{background:#f0f9ff;border-left:4px solid #0ea5e9;padding:20px;margin:20px 0;border-radius:8px}.footer{background:#f7fafc;padding:20px;text-align:center;font-size:14px;color:#64748b;border-top:1px solid #e2e8f0}.emoji{font-size:24px}</style></head><body><div class="container"><div class="header"><div class="emoji">⚠️</div><h1>Verificación Actualizada</h1></div><div class="content"><p>Hola <strong>{{admin_name}}</strong>,</p><p>Te notificamos que la verificación de tu tienda <strong>{{store_name}}</strong> ha sido modificada.</p><div class="warning-box"><h2>📋 Detalles del cambio:</h2><p><strong>Estado anterior:</strong> {{old_value}}</p><p><strong>Estado actual:</strong> {{new_value}}</p><p><strong>Fecha:</strong> {{change_date}}</p><p><strong>Modificado por:</strong> {{changed_by}}</p></div><div class="next-steps"><h3>📞 ¿Qué puedes hacer?</h3><ul><li>💬 <strong>Contacta soporte</strong> si tienes dudas sobre este cambio</li><li>📋 <strong>Revisa los requisitos</strong> de verificación</li><li>🔄 <strong>Solicita re-verificación</strong> si corresponde</li></ul></div><p>🔗 <a href="{{login_url}}">Acceder a mi panel de administración</a></p><p>💬 Para más información, escríbenos a <a href="mailto:{{support_email}}">{{support_email}}</a></p></div><div class="footer"><p>El equipo de {{app_name}} • {{current_year}}</p></div></div></body></html>',
                'body_text' => '⚠️ Verificación Actualizada - {{store_name}}\n\nHola {{admin_name}},\n\nTe notificamos que la verificación de tu tienda {{store_name}} ha sido modificada.\n\n📋 DETALLES DEL CAMBIO:\n- Estado anterior: {{old_value}}\n- Estado actual: {{new_value}}\n- Fecha: {{change_date}}\n- Modificado por: {{changed_by}}\n\n📞 ¿QUÉ PUEDES HACER?\n- 💬 Contacta soporte si tienes dudas sobre este cambio\n- 📋 Revisa los requisitos de verificación\n- 🔄 Solicita re-verificación si corresponde\n\n🔗 Panel de administración: {{login_url}}\n💬 Más información: {{support_email}}\n\nEl equipo de {{app_name}}',
                'variables' => ['store_name', 'admin_name', 'old_value', 'new_value', 'change_date', 'changed_by', 'login_url', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'ticket_created',
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
                'template_key' => 'invoice_created',
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
                'template_key' => 'ticket_response',
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
                'template_key' => 'ticket_updated',
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
                ['template_key' => $template['template_key']],
                $template
            );
        }

        Log::info('Plantillas de email por defecto creadas/actualizadas');
    }
}
