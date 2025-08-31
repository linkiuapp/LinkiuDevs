<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Store Management Templates
            [
                'template_key' => 'store_credentials',
                'context' => 'store_management',
                'name' => 'Credenciales de Tienda',
                'subject' => 'Credenciales de acceso - {{store_name}}',
                'body_html' => $this->getStoreCredentialsHtml(),
                'body_text' => $this->getStoreCredentialsText(),
                'variables' => ['store_name', 'admin_name', 'password', 'admin_url', 'frontend_url', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'store_welcome',
                'context' => 'store_management',
                'name' => 'Bienvenida Nueva Tienda',
                'subject' => 'Bienvenido a {{app_name}} - Tu tienda {{store_name}} está lista',
                'body_html' => $this->getStoreWelcomeHtml(),
                'body_text' => $this->getStoreWelcomeText(),
                'variables' => ['store_name', 'admin_name', 'admin_email', 'login_url', 'support_email'],
                'is_active' => true
            ],
            [
                'template_key' => 'password_changed',
                'context' => 'store_management',
                'name' => 'Contraseña Cambiada',
                'subject' => 'Contraseña actualizada para {{store_name}}',
                'body_html' => $this->getPasswordChangedHtml(),
                'body_text' => $this->getPasswordChangedText(),
                'variables' => ['store_name', 'admin_name', 'login_url', 'support_email'],
                'is_active' => true
            ],
            
            // Support Templates
            [
                'template_key' => 'ticket_created',
                'context' => 'support',
                'name' => 'Ticket Creado',
                'subject' => 'Ticket #{{ticket_id}} creado: {{ticket_subject}}',
                'body_html' => $this->getTicketCreatedHtml(),
                'body_text' => $this->getTicketCreatedText(),
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'status'],
                'is_active' => true
            ],
            [
                'template_key' => 'ticket_response',
                'context' => 'support',
                'name' => 'Respuesta de Ticket',
                'subject' => 'Respuesta a tu ticket #{{ticket_id}}',
                'body_html' => $this->getTicketResponseHtml(),
                'body_text' => $this->getTicketResponseText(),
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'response', 'status'],
                'is_active' => true
            ],
            
            // Billing Templates
            [
                'template_key' => 'invoice_created',
                'context' => 'billing',
                'name' => 'Factura Generada',
                'subject' => 'Nueva factura {{invoice_number}} - {{store_name}}',
                'body_html' => $this->getInvoiceCreatedHtml(),
                'body_text' => $this->getInvoiceCreatedText(),
                'variables' => ['invoice_number', 'amount', 'due_date', 'store_name', 'plan_name'],
                'is_active' => true
            ],
            [
                'template_key' => 'invoice_paid',
                'context' => 'billing',
                'name' => 'Factura Pagada',
                'subject' => 'Pago recibido - Factura {{invoice_number}}',
                'body_html' => $this->getInvoicePaidHtml(),
                'body_text' => $this->getInvoicePaidText(),
                'variables' => ['invoice_number', 'amount', 'store_name', 'plan_name'],
                'is_active' => true
            ],

            // Subscription notification templates
            [
                'template_key' => 'subscription_renewal_reminder',
                'context' => 'billing',
                'name' => 'Recordatorio de Renovación',
                'subject' => 'Tu suscripción vence en {{days}} días - {{store_name}}',
                'body_html' => $this->getSubscriptionRenewalReminderHtml(),
                'body_text' => $this->getSubscriptionRenewalReminderText(),
                'variables' => ['user_name', 'store_name', 'days', 'amount', 'plan_name', 'billing_url'],
                'is_active' => true
            ],

            [
                'template_key' => 'subscription_expiration_notice',
                'context' => 'billing',
                'name' => 'Notificación de Expiración',
                'subject' => 'Tu suscripción ha expirado - {{store_name}}',
                'body_html' => $this->getSubscriptionExpirationNoticeHtml(),
                'body_text' => $this->getSubscriptionExpirationNoticeText(),
                'variables' => ['user_name', 'store_name', 'plan_name', 'billing_url'],
                'is_active' => true
            ],

            [
                'template_key' => 'subscription_grace_period_ending',
                'context' => 'billing',
                'name' => 'Fin del Período de Gracia',
                'subject' => 'Período de gracia terminando en {{days_left}} días - {{store_name}}',
                'body_html' => $this->getSubscriptionGracePeriodEndingHtml(),
                'body_text' => $this->getSubscriptionGracePeriodEndingText(),
                'variables' => ['user_name', 'store_name', 'days_left', 'plan_name', 'billing_url'],
                'is_active' => true
            ]
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['template_key' => $template['template_key']],
                $template
            );
        }
    }

    private function getStoreCredentialsHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">Credenciales de Acceso</h2>
            <p>Hola {{admin_name}},</p>
            <p>Aquí tienes las credenciales de acceso para tu tienda <strong>{{store_name}}</strong>:</p>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <p><strong>Contraseña:</strong> {{password}}</p>
                <p><strong>Panel de Administración:</strong> <a href="{{admin_url}}">{{admin_url}}</a></p>
                <p><strong>Tienda Frontend:</strong> <a href="{{frontend_url}}">{{frontend_url}}</a></p>
            </div>
            <p><strong>Importante:</strong> Guarda estas credenciales en un lugar seguro y cambia la contraseña después del primer acceso.</p>
            <p>Si tienes alguna pregunta, no dudes en contactarnos en {{support_email}}</p>
            <p>El equipo de SuperLinkiu</p>
        </div>';
    }

    private function getStoreCredentialsText(): string
    {
        return 'Credenciales de Acceso

Hola {{admin_name}},

Aquí tienes las credenciales de acceso para tu tienda {{store_name}}:

Contraseña: {{password}}
Panel de Administración: {{admin_url}}
Tienda Frontend: {{frontend_url}}

Importante: Guarda estas credenciales en un lugar seguro y cambia la contraseña después del primer acceso.

Si tienes alguna pregunta, no dudes en contactarnos en {{support_email}}

El equipo de SuperLinkiu';
    }

    private function getStoreWelcomeHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">¡Bienvenido a {{app_name}}!</h2>
            <p>Hola {{admin_name}},</p>
            <p>Tu tienda <strong>{{store_name}}</strong> ha sido creada exitosamente y está lista para usar.</p>
            <p><strong>Datos de acceso:</strong></p>
            <ul>
                <li>Email: {{admin_email}}</li>
                <li>URL de acceso: <a href="{{login_url}}">{{login_url}}</a></li>
            </ul>
            <p>Si tienes alguna pregunta, no dudes en contactarnos en {{support_email}}</p>
            <p>¡Bienvenido a bordo!</p>
            <p>El equipo de {{app_name}}</p>
        </div>';
    }

    private function getStoreWelcomeText(): string
    {
        return '¡Bienvenido a {{app_name}}!

Hola {{admin_name}},

Tu tienda {{store_name}} ha sido creada exitosamente y está lista para usar.

Datos de acceso:
- Email: {{admin_email}}
- URL de acceso: {{login_url}}

Si tienes alguna pregunta, no dudes en contactarnos en {{support_email}}

¡Bienvenido a bordo!
El equipo de {{app_name}}';
    }

    private function getPasswordChangedHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">Contraseña Actualizada</h2>
            <p>Hola {{admin_name}},</p>
            <p>La contraseña de tu tienda <strong>{{store_name}}</strong> ha sido actualizada exitosamente.</p>
            <p>Puedes acceder con tu nueva contraseña en: <a href="{{login_url}}">{{login_url}}</a></p>
            <p>Si no realizaste este cambio, contacta inmediatamente a {{support_email}}</p>
            <p>El equipo de {{app_name}}</p>
        </div>';
    }

    private function getPasswordChangedText(): string
    {
        return 'Contraseña Actualizada

Hola {{admin_name}},

La contraseña de tu tienda {{store_name}} ha sido actualizada exitosamente.

Puedes acceder con tu nueva contraseña en: {{login_url}}

Si no realizaste este cambio, contacta inmediatamente a {{support_email}}

El equipo de {{app_name}}';
    }

    private function getTicketCreatedHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">Ticket Creado</h2>
            <p>Hola {{customer_name}},</p>
            <p>Hemos recibido tu solicitud de soporte.</p>
            <p><strong>Detalles del ticket:</strong></p>
            <ul>
                <li>ID: #{{ticket_id}}</li>
                <li>Asunto: {{ticket_subject}}</li>
                <li>Estado: {{status}}</li>
            </ul>
            <p>Nuestro equipo revisará tu solicitud y te responderemos pronto.</p>
            <p>Equipo de Soporte</p>
        </div>';
    }

    private function getTicketCreatedText(): string
    {
        return 'Ticket Creado

Hola {{customer_name}},

Hemos recibido tu solicitud de soporte.

Detalles del ticket:
- ID: #{{ticket_id}}
- Asunto: {{ticket_subject}}
- Estado: {{status}}

Nuestro equipo revisará tu solicitud y te responderemos pronto.

Equipo de Soporte';
    }

    private function getTicketResponseHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">Respuesta a tu Ticket</h2>
            <p>Hola {{customer_name}},</p>
            <p>Hemos respondido a tu ticket <strong>#{{ticket_id}}</strong>:</p>
            <div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-left: 4px solid #007cba;">
                {{response}}
            </div>
            <p><strong>Estado actual:</strong> {{status}}</p>
            <p>Equipo de Soporte</p>
        </div>';
    }

    private function getTicketResponseText(): string
    {
        return 'Respuesta a tu Ticket

Hola {{customer_name}},

Hemos respondido a tu ticket #{{ticket_id}}:

{{response}}

Estado actual: {{status}}

Equipo de Soporte';
    }

    private function getInvoiceCreatedHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">Nueva Factura Generada</h2>
            <p>Hola,</p>
            <p>Se ha generado una nueva factura para <strong>{{store_name}}</strong>.</p>
            <p><strong>Detalles de la factura:</strong></p>
            <ul>
                <li>Número: {{invoice_number}}</li>
                <li>Plan: {{plan_name}}</li>
                <li>Monto: ${{amount}}</li>
                <li>Fecha de vencimiento: {{due_date}}</li>
            </ul>
            <p>Departamento de Facturación</p>
        </div>';
    }

    private function getInvoiceCreatedText(): string
    {
        return 'Nueva Factura Generada

Hola,

Se ha generado una nueva factura para {{store_name}}.

Detalles de la factura:
- Número: {{invoice_number}}
- Plan: {{plan_name}}
- Monto: ${{amount}}
- Fecha de vencimiento: {{due_date}}

Departamento de Facturación';
    }

    private function getInvoicePaidHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #28a745;">Pago Recibido</h2>
            <p>Hola,</p>
            <p>Hemos recibido el pago de la factura <strong>{{invoice_number}}</strong> de {{store_name}}.</p>
            <p><strong>Detalles del pago:</strong></p>
            <ul>
                <li>Factura: {{invoice_number}}</li>
                <li>Plan: {{plan_name}}</li>
                <li>Monto: ${{amount}}</li>
            </ul>
            <p>¡Gracias por tu pago!</p>
            <p>Departamento de Facturación</p>
        </div>';
    }

    private function getInvoicePaidText(): string
    {
        return 'Pago Recibido

Hola,

Hemos recibido el pago de la factura {{invoice_number}} de {{store_name}}.

Detalles del pago:
- Factura: {{invoice_number}}
- Plan: {{plan_name}}
- Monto: ${{amount}}

¡Gracias por tu pago!

Departamento de Facturación';
    }

    private function getSubscriptionRenewalReminderHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #f39c12;">Recordatorio de Renovación</h2>
            <p>Hola {{user_name}},</p>
            <p>Tu suscripción para <strong>{{store_name}}</strong> vence en <strong>{{days}} días</strong>.</p>
            <p><strong>Detalles de renovación:</strong></p>
            <ul>
                <li>Plan: {{plan_name}}</li>
                <li>Monto a renovar: ${{amount}}</li>
            </ul>
            <p><a href="{{billing_url}}" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Renovar Ahora</a></p>
            <p>Departamento de Facturación</p>
        </div>';
    }

    private function getSubscriptionRenewalReminderText(): string
    {
        return 'Recordatorio de Renovación

Hola {{user_name}},

Tu suscripción para {{store_name}} vence en {{days}} días.

Detalles de renovación:
- Plan: {{plan_name}}
- Monto a renovar: ${{amount}}

Renueva en: {{billing_url}}

Departamento de Facturación';
    }

    private function getSubscriptionExpirationNoticeHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #e74c3c;">Suscripción Expirada</h2>
            <p>Hola {{user_name}},</p>
            <p>Tu suscripción para <strong>{{store_name}}</strong> ha expirado.</p>
            <p><strong>Plan:</strong> {{plan_name}}</p>
            <p>Para continuar usando nuestros servicios, renueva tu suscripción:</p>
            <p><a href="{{billing_url}}" style="background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Renovar Suscripción</a></p>
            <p>Departamento de Facturación</p>
        </div>';
    }

    private function getSubscriptionExpirationNoticeText(): string
    {
        return 'Suscripción Expirada

Hola {{user_name}},

Tu suscripción para {{store_name}} ha expirado.

Plan: {{plan_name}}

Para continuar usando nuestros servicios, renueva tu suscripción en: {{billing_url}}

Departamento de Facturación';
    }

    private function getSubscriptionGracePeriodEndingHtml(): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #e67e22;">Período de Gracia Terminando</h2>
            <p>Hola {{user_name}},</p>
            <p>El período de gracia para <strong>{{store_name}}</strong> termina en <strong>{{days_left}} días</strong>.</p>
            <p><strong>Plan:</strong> {{plan_name}}</p>
            <p><strong>¡Importante!</strong> Si no renuevas antes de que termine el período de gracia, tu tienda será suspendida.</p>
            <p><a href="{{billing_url}}" style="background: #e67e22; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Renovar Ahora para Evitar Suspensión</a></p>
            <p>Departamento de Facturación</p>
        </div>';
    }

    private function getSubscriptionGracePeriodEndingText(): string
    {
        return 'Período de Gracia Terminando

Hola {{user_name}},

El período de gracia para {{store_name}} termina en {{days_left}} días.

Plan: {{plan_name}}

¡Importante! Si no renuevas antes de que termine el período de gracia, tu tienda será suspendida.

Renueva ahora para evitar suspensión: {{billing_url}}

Departamento de Facturación';
    }
}
