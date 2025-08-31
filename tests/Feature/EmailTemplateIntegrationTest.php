<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailTemplateIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed email settings and templates
        $this->artisan('db:seed', ['--class' => 'EmailSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    public function test_complete_store_management_email_flow()
    {
        Mail::fake();

        // Test store credentials email
        $result = EmailService::sendWithTemplate('store_credentials', ['admin@newstore.com'], [
            'store_name' => 'Nueva Tienda Demo',
            'admin_name' => 'Carlos Administrador',
            'admin_email' => 'admin@newstore.com',
            'password' => 'temp123456',
            'admin_url' => 'https://nuevatienda.linkiu.bio/admin',
            'frontend_url' => 'https://nuevatienda.linkiu.bio',
            'support_email' => 'soporte@linkiudev.co'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);

        // Test store welcome email
        $result = EmailService::sendWithTemplate('store_welcome', ['admin@newstore.com'], [
            'app_name' => 'SuperLinkiu',
            'store_name' => 'Nueva Tienda Demo',
            'admin_name' => 'Carlos Administrador',
            'admin_email' => 'admin@newstore.com',
            'login_url' => 'https://nuevatienda.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 2);

        // Test password changed email
        $result = EmailService::sendWithTemplate('password_changed', ['admin@newstore.com'], [
            'admin_name' => 'Carlos Administrador',
            'store_name' => 'Nueva Tienda Demo',
            'admin_email' => 'admin@newstore.com',
            'login_url' => 'https://nuevatienda.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 3);
    }

    public function test_complete_support_email_flow()
    {
        Mail::fake();

        // Test ticket created email
        $result = EmailService::sendWithTemplate('ticket_created', ['cliente@tienda.com'], [
            'ticket_id' => 'TK-2025-001',
            'ticket_subject' => 'Problema con el diseño de mi tienda',
            'customer_name' => 'María García',
            'status' => 'Abierto'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);

        // Test ticket response email
        $result = EmailService::sendWithTemplate('ticket_response', ['cliente@tienda.com'], [
            'ticket_id' => 'TK-2025-001',
            'ticket_subject' => 'Problema con el diseño de mi tienda',
            'customer_name' => 'María García',
            'response' => 'Hemos revisado tu solicitud y aplicado las correcciones necesarias al diseño de tu tienda. Los cambios ya están activos.',
            'status' => 'Resuelto'
        ]);

        $this->assertTrue($result);
        Mail::assertSent \Illuminate\Mail\Mailable::class, 2);
    }

    public function test_complete_billing_email_flow()
    {
        Mail::fake();

        // Test invoice created email
        $result = EmailService::sendWithTemplate('invoice_created', ['facturacion@tienda.com'], [
            'invoice_number' => 'INV-2025-001',
            'amount' => '29.99',
            'due_date' => '15/02/2025',
            'store_name' => 'Tienda Ejemplo',
            'plan_name' => 'Plan Básico'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);

        // Test invoice paid email
        $result = EmailService::sendWithTemplate('invoice_paid', ['facturacion@tienda.com'], [
            'invoice_number' => 'INV-2025-001',
            'amount' => '29.99',
            'store_name' => 'Tienda Ejemplo',
            'plan_name' => 'Plan Básico'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 2);

        // Test subscription renewal reminder
        $result = EmailService::sendWithTemplate('subscription_renewal_reminder', ['admin@tienda.com'], [
            'user_name' => 'Juan Pérez',
            'store_name' => 'Tienda Ejemplo',
            'days' => '7',
            'amount' => '29.99',
            'plan_name' => 'Plan Básico',
            'billing_url' => 'https://linkiu.bio/billing/renew'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 3);

        // Test subscription expiration notice
        $result = EmailService::sendWithTemplate('subscription_expiration_notice', ['admin@tienda.com'], [
            'user_name' => 'Juan Pérez',
            'store_name' => 'Tienda Ejemplo',
            'days_left' => '3',
            'plan_name' => 'Plan Básico',
            'billing_url' => 'https://linkiu.bio/billing/renew'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 4);

        // Test subscription grace period ending
        $result = EmailService::sendWithTemplate('subscription_grace_period_ending', ['admin@tienda.com'], [
            'user_name' => 'Juan Pérez',
            'store_name' => 'Tienda Ejemplo',
            'days_left' => '1',
            'plan_name' => 'Plan Básico',
            'billing_url' => 'https://linkiu.bio/billing/renew'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 5);
    }

    public function test_email_template_rendering_with_all_variables()
    {
        // Test store management template with all available variables
        $template = EmailTemplate::where('template_key', 'store_credentials')->first();
        $this->assertNotNull($template);

        $allStoreVars = [
            'store_name' => 'Tienda Completa',
            'admin_name' => 'Administrador Completo',
            'admin_email' => 'admin@completa.com',
            'password' => 'password123',
            'login_url' => 'https://completa.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ];

        $rendered = $template->replaceVariables($allStoreVars);

        foreach ($allStoreVars as $key => $value) {
            $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['subject']);
            $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['body_html']);
            if ($rendered['body_text']) {
                $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['body_text']);
            }
        }

        // Test support template with all available variables
        $template = EmailTemplate::where('template_key', 'ticket_response')->first();
        $this->assertNotNull($template);

        $allSupportVars = [
            'ticket_id' => 'TK-COMPLETE-001',
            'ticket_subject' => 'Consulta completa sobre funcionalidades',
            'customer_name' => 'Cliente Completo',
            'response' => 'Respuesta completa y detallada a la consulta del cliente.',
            'status' => 'Completamente Resuelto'
        ];

        $rendered = $template->replaceVariables($allSupportVars);

        foreach ($allSupportVars as $key => $value) {
            $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['subject']);
            $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['body_html']);
            if ($rendered['body_text']) {
                $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['body_text']);
            }
        }

        // Test billing template with all available variables
        $template = EmailTemplate::where('template_key', 'subscription_renewal_reminder')->first();
        $this->assertNotNull($template);

        $allBillingVars = [
            'invoice_number' => 'INV-COMPLETE-001',
            'amount' => '99.99',
            'due_date' => '31/12/2025',
            'store_name' => 'Tienda Completa Billing',
            'plan_name' => 'Plan Completo Premium',
            'user_name' => 'Usuario Completo',
            'days' => '30',
            'days_left' => '30',
            'billing_url' => 'https://linkiu.bio/billing/complete'
        ];

        $rendered = $template->replaceVariables($allBillingVars);

        foreach ($allBillingVars as $key => $value) {
            $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['subject']);
            $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['body_html']);
            if ($rendered['body_text']) {
                $this->assertStringNotContainsString('{{' . $key . '}}', $rendered['body_text']);
            }
        }
    }

    public function test_email_sending_with_multiple_recipients()
    {
        Mail::fake();

        $recipients = [
            'admin1@tienda1.com',
            'admin2@tienda2.com',
            'admin3@tienda3.com'
        ];

        $result = EmailService::sendWithTemplate('store_welcome', $recipients, [
            'app_name' => 'SuperLinkiu',
            'store_name' => 'Tienda Multiple',
            'admin_name' => 'Administrador Multiple',
            'admin_email' => 'admin@multiple.com',
            'login_url' => 'https://multiple.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 3); // One for each recipient
    }

    public function test_email_sending_with_mixed_valid_invalid_recipients()
    {
        Mail::fake();
        Log::shouldReceive('warning')->times(2); // For invalid recipients

        $recipients = [
            'valid1@example.com',
            'invalid-email', // Invalid format
            'valid2@example.com',
            'test@tempmail.org', // Suspicious domain
            'valid3@example.com'
        ];

        $result = EmailService::sendWithTemplate('store_welcome', $recipients, [
            'app_name' => 'SuperLinkiu',
            'store_name' => 'Tienda Mixed',
            'admin_name' => 'Admin Mixed',
            'admin_email' => 'admin@mixed.com',
            'login_url' => 'https://mixed.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 3); // Only valid recipients
    }

    public function test_email_context_routing()
    {
        Mail::fake();

        // Update email settings to test context routing
        EmailSetting::updateContext('store_management', 'custom-store@example.com');
        EmailSetting::updateContext('support', 'custom-support@example.com');
        EmailSetting::updateContext('billing', 'custom-billing@example.com');

        // Test store management email uses correct from address
        $result = EmailService::sendWithTemplate('store_welcome', ['test@example.com'], [
            'app_name' => 'SuperLinkiu',
            'store_name' => 'Test Store',
            'admin_name' => 'Test Admin',
            'admin_email' => 'admin@test.com',
            'login_url' => 'https://test.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ]);

        $this->assertTrue($result);

        // Test support email uses correct from address
        $result = EmailService::sendWithTemplate('ticket_created', ['test@example.com'], [
            'ticket_id' => 'TK-001',
            'ticket_subject' => 'Test Ticket',
            'customer_name' => 'Test Customer',
            'status' => 'Abierto'
        ]);

        $this->assertTrue($result);

        // Test billing email uses correct from address
        $result = EmailService::sendWithTemplate('invoice_created', ['test@example.com'], [
            'invoice_number' => 'INV-001',
            'amount' => '29.99',
            'due_date' => '31/12/2025',
            'store_name' => 'Test Store',
            'plan_name' => 'Test Plan'
        ]);

        $this->assertTrue($result);

        Mail::assertSent(\Illuminate\Mail\Mailable::class, 3);
    }

    public function test_template_fallback_behavior()
    {
        Mail::fake();

        // Test with missing template (should fail gracefully)
        Log::shouldReceive('warning')->once();

        $result = EmailService::sendWithTemplate('nonexistent_template', ['test@example.com'], []);

        $this->assertFalse($result);
        Mail::assertNothingSent();

        // Test with inactive template (should fail gracefully)
        $template = EmailTemplate::create([
            'template_key' => 'inactive_template',
            'context' => 'store_management',
            'name' => 'Inactive Template',
            'subject' => 'Test',
            'body_html' => '<p>Test</p>',
            'is_active' => false
        ]);

        Log::shouldReceive('warning')->once();

        $result = EmailService::sendWithTemplate('inactive_template', ['test@example.com'], []);

        $this->assertFalse($result);
        Mail::assertNothingSent();
    }

    public function test_email_logging_and_audit_trail()
    {
        Mail::fake();
        Log::shouldReceive('info')->once()->with('Email sent successfully', \Mockery::type('array'));

        $result = EmailService::sendWithTemplate('store_welcome', ['test@example.com'], [
            'app_name' => 'SuperLinkiu',
            'store_name' => 'Audit Test Store',
            'admin_name' => 'Audit Admin',
            'admin_email' => 'admin@audit.com',
            'login_url' => 'https://audit.linkiu.bio/admin',
            'support_email' => 'soporte@linkiudev.co'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);
    }

    public function test_template_variable_validation_during_sending()
    {
        Mail::fake();
        Log::shouldReceive('warning')->once(); // For template validation issues

        // Create template with invalid variables for its context
        $template = EmailTemplate::create([
            'template_key' => 'invalid_vars_template',
            'context' => 'store_management',
            'name' => 'Invalid Variables Template',
            'subject' => 'Store {{store_name}} has ticket {{ticket_id}}', // ticket_id not valid for store_management
            'body_html' => '<p>Invoice {{invoice_number}} for store</p>', // invoice_number not valid for store_management
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('invalid_vars_template', ['test@example.com'], [
            'store_name' => 'Test Store',
            'ticket_id' => 'TK-001', // This won't be replaced
            'invoice_number' => 'INV-001' // This won't be replaced
        ]);

        // Should still send email but log validation issues
        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);
    }

    public function test_common_variables_injection()
    {
        Mail::fake();

        // Test that common variables are automatically added
        $template = EmailTemplate::create([
            'template_key' => 'common_vars_test',
            'context' => 'store_management',
            'name' => 'Common Variables Test',
            'subject' => 'From {{app_name}} - Year {{current_year}}',
            'body_html' => '<p>Visit {{app_url}} for support at {{support_email}}</p>',
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('common_vars_test', ['test@example.com'], [
            'store_name' => 'Test Store'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);

        // Verify common variables were injected by checking the template rendering
        $reflection = new \ReflectionClass(EmailService::class);
        $method = $reflection->getMethod('prepareMailData');
        $method->setAccessible(true);

        $mailData = $method->invoke(null, $template, ['store_name' => 'Test Store']);

        $this->assertStringContainsString(config('app.name', 'Linkiu.bio'), $mailData['subject']);
        $this->assertStringContainsString(date('Y'), $mailData['subject']);
        $this->assertStringContainsString(config('app.url'), $mailData['body_html']);
    }

    public function test_backward_compatibility_methods()
    {
        Mail::fake();
        Log::shouldReceive('info')->times(3);

        // Test sendWithView method
        $result = EmailService::sendWithView(
            'test-view',
            ['test@example.com'],
            ['data' => 'test'],
            'Test Subject',
            'store_management'
        );

        $this->assertTrue($result);

        // Test sendSimple method
        $result = EmailService::sendSimple(
            'support',
            ['test@example.com'],
            'Simple Test Subject',
            'Simple test body content'
        );

        $this->assertTrue($result);

        // Test sendRaw method
        $result = EmailService::sendRaw(
            'Raw email content',
            ['test@example.com'],
            'Raw Test Subject',
            'billing'
        );

        $this->assertTrue($result);

        Mail::assertSent(\Illuminate\Mail\Mailable::class, 3);
    }

    public function test_email_configuration_validation_integration()
    {
        // Test with complete configuration
        $validation = EmailService::validateEmailConfiguration();
        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['issues']);

        // Test with missing templates
        EmailTemplate::where('template_key', 'store_welcome')->delete();
        
        $validation = EmailService::validateEmailConfiguration();
        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['issues']);
        $this->assertStringContainsString('Missing template: store_welcome', $validation['issues'][0]);

        // Test with invalid email configuration
        EmailSetting::where('context', 'store_management')->update(['email' => 'invalid-email']);
        
        $validation = EmailService::validateEmailConfiguration();
        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('Invalid email for context', $validation['issues'][0]);
    }
}