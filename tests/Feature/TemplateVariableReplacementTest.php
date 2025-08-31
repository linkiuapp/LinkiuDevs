<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailTemplate;
use App\Models\EmailSetting;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TemplateVariableReplacementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create email settings
        EmailSetting::create([
            'context' => 'store_management',
            'email' => 'store@example.com',
            'name' => 'Store Management',
            'is_active' => true
        ]);

        EmailSetting::create([
            'context' => 'support',
            'email' => 'support@example.com',
            'name' => 'Support',
            'is_active' => true
        ]);

        EmailSetting::create([
            'context' => 'billing',
            'email' => 'billing@example.com',
            'name' => 'Billing',
            'is_active' => true
        ]);
    }

    public function test_store_management_variable_replacement()
    {
        $template = EmailTemplate::create([
            'template_key' => 'store_credentials',
            'context' => 'store_management',
            'name' => 'Store Credentials',
            'subject' => 'Credenciales para {{store_name}} - {{admin_name}}',
            'body_html' => '
                <h1>Bienvenido {{admin_name}}</h1>
                <p>Tu tienda <strong>{{store_name}}</strong> está lista.</p>
                <p>Email: {{admin_email}}</p>
                <p>Contraseña temporal: {{password}}</p>
                <p>Accede en: <a href="{{login_url}}">{{login_url}}</a></p>
                <p>Soporte: {{support_email}}</p>
            ',
            'body_text' => '
                Bienvenido {{admin_name}}
                Tu tienda {{store_name}} está lista.
                Email: {{admin_email}}
                Contraseña temporal: {{password}}
                Accede en: {{login_url}}
                Soporte: {{support_email}}
            ',
            'is_active' => true
        ]);

        $data = [
            'store_name' => 'Mi Tienda Ejemplo',
            'admin_name' => 'Juan Pérez',
            'admin_email' => 'juan@mitienda.com',
            'password' => 'temp123456',
            'login_url' => 'https://mitienda.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ];

        $result = $template->replaceVariables($data);

        // Test subject replacement
        $this->assertEquals('Credenciales para Mi Tienda Ejemplo - Juan Pérez', $result['subject']);

        // Test HTML body replacement
        $this->assertStringContainsString('Bienvenido Juan Pérez', $result['body_html']);
        $this->assertStringContainsString('<strong>Mi Tienda Ejemplo</strong>', $result['body_html']);
        $this->assertStringContainsString('juan@mitienda.com', $result['body_html']);
        $this->assertStringContainsString('temp123456', $result['body_html']);
        $this->assertStringContainsString('https://mitienda.linkiu.bio/admin', $result['body_html']);
        $this->assertStringContainsString('soporte@linkiudev.co', $result['body_html']);

        // Test text body replacement
        $this->assertStringContainsString('Bienvenido Juan Pérez', $result['body_text']);
        $this->assertStringContainsString('Mi Tienda Ejemplo', $result['body_text']);
        $this->assertStringContainsString('juan@mitienda.com', $result['body_text']);
    }

    public function test_support_variable_replacement()
    {
        $template = EmailTemplate::create([
            'template_key' => 'ticket_response',
            'context' => 'support',
            'name' => 'Ticket Response',
            'subject' => 'Respuesta a ticket {{ticket_id}}: {{ticket_subject}}',
            'body_html' => '
                <h2>Hola {{customer_name}}</h2>
                <p>Hemos respondido a tu ticket <strong>{{ticket_id}}</strong>:</p>
                <h3>{{ticket_subject}}</h3>
                <div class="response">{{response}}</div>
                <p>Estado actual: <span class="status">{{status}}</span></p>
            ',
            'body_text' => '
                Hola {{customer_name}}
                Hemos respondido a tu ticket {{ticket_id}}: {{ticket_subject}}
                Respuesta: {{response}}
                Estado actual: {{status}}
            ',
            'is_active' => true
        ]);

        $data = [
            'ticket_id' => 'TK-2025-001',
            'ticket_subject' => 'Problema con el diseño de mi tienda',
            'customer_name' => 'María García',
            'response' => 'Hemos revisado tu solicitud y hemos aplicado las correcciones necesarias al diseño.',
            'status' => 'Resuelto'
        ];

        $result = $template->replaceVariables($data);

        // Test subject replacement
        $this->assertEquals('Respuesta a ticket TK-2025-001: Problema con el diseño de mi tienda', $result['subject']);

        // Test HTML body replacement
        $this->assertStringContainsString('Hola María García', $result['body_html']);
        $this->assertStringContainsString('<strong>TK-2025-001</strong>', $result['body_html']);
        $this->assertStringContainsString('Problema con el diseño de mi tienda', $result['body_html']);
        $this->assertStringContainsString('hemos aplicado las correcciones', $result['body_html']);
        $this->assertStringContainsString('<span class="status">Resuelto</span>', $result['body_html']);

        // Test text body replacement
        $this->assertStringContainsString('Hola María García', $result['body_text']);
        $this->assertStringContainsString('TK-2025-001', $result['body_text']);
        $this->assertStringContainsString('Estado actual: Resuelto', $result['body_text']);
    }

    public function test_billing_variable_replacement()
    {
        $template = EmailTemplate::create([
            'template_key' => 'subscription_renewal_reminder',
            'context' => 'billing',
            'name' => 'Subscription Renewal Reminder',
            'subject' => 'Renovación de {{plan_name}} en {{days}} días - {{store_name}}',
            'body_html' => '
                <h2>Hola {{user_name}}</h2>
                <p>Tu suscripción al <strong>{{plan_name}}</strong> para la tienda <em>{{store_name}}</em> vence en <strong>{{days}} días</strong>.</p>
                <p>Monto de renovación: <span class="amount">${{amount}}</span></p>
                <p><a href="{{billing_url}}" class="btn">Renovar ahora</a></p>
                <p>Días restantes: {{days_left}}</p>
            ',
            'body_text' => '
                Hola {{user_name}}
                Tu suscripción al {{plan_name}} para la tienda {{store_name}} vence en {{days}} días.
                Monto de renovación: ${{amount}}
                Renovar en: {{billing_url}}
                Días restantes: {{days_left}}
            ',
            'is_active' => true
        ]);

        $data = [
            'user_name' => 'Carlos Rodríguez',
            'store_name' => 'Tienda Carlos',
            'plan_name' => 'Plan Premium',
            'days' => '7',
            'amount' => '29.99',
            'billing_url' => 'https://linkiu.bio/billing/renew',
            'days_left' => '7'
        ];

        $result = $template->replaceVariables($data);

        // Test subject replacement
        $this->assertEquals('Renovación de Plan Premium en 7 días - Tienda Carlos', $result['subject']);

        // Test HTML body replacement
        $this->assertStringContainsString('Hola Carlos Rodríguez', $result['body_html']);
        $this->assertStringContainsString('<strong>Plan Premium</strong>', $result['body_html']);
        $this->assertStringContainsString('<em>Tienda Carlos</em>', $result['body_html']);
        $this->assertStringContainsString('<strong>7 días</strong>', $result['body_html']);
        $this->assertStringContainsString('<span class="amount">$29.99</span>', $result['body_html']);
        $this->assertStringContainsString('https://linkiu.bio/billing/renew', $result['body_html']);

        // Test text body replacement
        $this->assertStringContainsString('Hola Carlos Rodríguez', $result['body_text']);
        $this->assertStringContainsString('Plan Premium', $result['body_text']);
        $this->assertStringContainsString('$29.99', $result['body_text']);
    }

    public function test_variable_replacement_with_html_sanitization()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Store: {{store_name}}',
            'body_html' => '<p>Admin: {{admin_name}}</p>',
            'is_active' => true
        ]);

        $data = [
            'store_name' => '<script>alert("xss")</script>Malicious Store',
            'admin_name' => '<b>Bold Admin</b> & Special Chars'
        ];

        $result = $template->replaceVariables($data);

        // Test HTML sanitization
        $this->assertStringNotContainsString('<script>', $result['subject']);
        $this->assertStringNotContainsString('<script>', $result['body_html']);
        $this->assertStringContainsString('&lt;script&gt;', $result['subject']);
        $this->assertStringContainsString('&amp;', $result['body_html']);
    }

    public function test_variable_replacement_ignores_invalid_variables()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Valid: {{admin_name}} Invalid: {{invalid_var}}',
            'body_html' => '<p>Store: {{store_name}} Ticket: {{ticket_id}}</p>', // ticket_id not valid for store_management
            'is_active' => true
        ]);

        $data = [
            'admin_name' => 'John Doe',
            'store_name' => 'My Store',
            'invalid_var' => 'Should not replace',
            'ticket_id' => 'Should not replace'
        ];

        $result = $template->replaceVariables($data);

        // Valid variables should be replaced
        $this->assertStringContainsString('Valid: John Doe', $result['subject']);
        $this->assertStringContainsString('Store: My Store', $result['body_html']);

        // Invalid variables should remain as placeholders
        $this->assertStringContainsString('Invalid: {{invalid_var}}', $result['subject']);
        $this->assertStringContainsString('Ticket: {{ticket_id}}', $result['body_html']);
    }

    public function test_variable_replacement_logs_unreplaced_variables()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Unreplaced template variables found', \Mockery::type('array'));

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{admin_name}} and {{unreplaced_var}}',
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $template->replaceVariables(['admin_name' => 'John']);
    }

    public function test_variable_replacement_with_empty_values()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Store: {{store_name}} Admin: {{admin_name}}',
            'body_html' => '<p>Email: {{admin_email}}</p>',
            'is_active' => true
        ]);

        $data = [
            'store_name' => '',
            'admin_name' => null,
            'admin_email' => 'test@example.com'
        ];

        $result = $template->replaceVariables($data);

        // Empty and null values should still replace the placeholders
        $this->assertEquals('Store:  Admin: ', $result['subject']);
        $this->assertStringContainsString('<p>Email: test@example.com</p>', $result['body_html']);
    }

    public function test_variable_replacement_with_numeric_values()
    {
        $template = EmailTemplate::create([
            'template_key' => 'invoice_template',
            'context' => 'billing',
            'name' => 'Invoice Template',
            'subject' => 'Invoice {{invoice_number}} for ${{amount}}',
            'body_html' => '<p>Days: {{days}}</p>',
            'is_active' => true
        ]);

        $data = [
            'invoice_number' => 12345,
            'amount' => 99.99,
            'days' => 30
        ];

        $result = $template->replaceVariables($data);

        $this->assertEquals('Invoice 12345 for $99.99', $result['subject']);
        $this->assertStringContainsString('<p>Days: 30</p>', $result['body_html']);
    }

    public function test_variable_replacement_with_special_characters()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Store: {{store_name}}',
            'body_html' => '<p>Admin: {{admin_name}}</p>',
            'is_active' => true
        ]);

        $data = [
            'store_name' => 'Tienda José & María',
            'admin_name' => 'José María Rodríguez-García'
        ];

        $result = $template->replaceVariables($data);

        $this->assertStringContainsString('José &amp; María', $result['subject']);
        $this->assertStringContainsString('José María Rodríguez-García', $result['body_html']);
    }

    public function test_email_service_template_rendering_integration()
    {
        Mail::fake();

        $template = EmailTemplate::create([
            'template_key' => 'integration_test',
            'context' => 'store_management',
            'name' => 'Integration Test',
            'subject' => 'Welcome {{admin_name}} to {{store_name}}',
            'body_html' => '<p>Your store {{store_name}} is ready! Login at {{login_url}}</p>',
            'body_text' => 'Your store {{store_name}} is ready! Login at {{login_url}}',
            'is_active' => true
        ]);

        $data = [
            'admin_name' => 'Integration User',
            'store_name' => 'Integration Store',
            'login_url' => 'https://integration.test/admin',
            'admin_email' => 'integration@test.com',
            'support_email' => 'support@test.com'
        ];

        $result = EmailService::sendWithTemplate('integration_test', ['test@example.com'], $data);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);
    }

    public function test_template_rendering_with_common_variables()
    {
        $template = EmailTemplate::create([
            'template_key' => 'common_vars_test',
            'context' => 'store_management',
            'name' => 'Common Variables Test',
            'subject' => 'From {{app_name}} - Year {{current_year}}',
            'body_html' => '<p>Visit {{app_url}} for support at {{support_email}}</p>',
            'is_active' => true
        ]);

        // Test through EmailService which adds common variables
        $reflection = new \ReflectionClass(EmailService::class);
        $method = $reflection->getMethod('prepareMailData');
        $method->setAccessible(true);

        $result = $method->invoke(null, $template, ['admin_name' => 'Test']);

        $this->assertStringContainsString(config('app.name', 'Linkiu.bio'), $result['subject']);
        $this->assertStringContainsString(date('Y'), $result['subject']);
        $this->assertStringContainsString(config('app.url'), $result['body_html']);
    }

    public function test_template_validation_with_context_specific_variables()
    {
        // Store management template with valid variables
        $storeTemplate = EmailTemplate::create([
            'template_key' => 'store_valid',
            'context' => 'store_management',
            'name' => 'Store Valid',
            'subject' => 'Welcome {{admin_name}} to {{store_name}}',
            'body_html' => '<p>Login: {{login_url}} Support: {{support_email}}</p>',
            'is_active' => true
        ]);

        $issues = $storeTemplate->validateTemplateVariables();
        $this->assertEmpty($issues);

        // Store management template with invalid variables
        $storeInvalid = EmailTemplate::create([
            'template_key' => 'store_invalid',
            'context' => 'store_management',
            'name' => 'Store Invalid',
            'subject' => 'Ticket {{ticket_id}} for {{admin_name}}', // ticket_id not valid for store_management
            'body_html' => '<p>Invoice {{invoice_number}}</p>', // invoice_number not valid for store_management
            'is_active' => true
        ]);

        $issues = $storeInvalid->validateTemplateVariables();
        $this->assertCount(2, $issues);
        $this->assertStringContainsString('{{ticket_id}}', $issues[0]);
        $this->assertStringContainsString('{{invoice_number}}', $issues[1]);
    }
}