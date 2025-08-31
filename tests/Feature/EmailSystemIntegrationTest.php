<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class EmailSystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed email settings and templates
        $this->artisan('db:seed', ['--class' => 'EmailSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    public function test_all_email_contexts_are_configured()
    {
        // Test all three contexts have email settings
        $storeEmail = EmailService::getContextEmail('store_management');
        $supportEmail = EmailService::getContextEmail('support');
        $billingEmail = EmailService::getContextEmail('billing');

        $this->assertNotEmpty($storeEmail);
        $this->assertNotEmpty($supportEmail);
        $this->assertNotEmpty($billingEmail);

        // Verify they use configured addresses or defaults
        $this->assertStringContainsString('@', $storeEmail);
        $this->assertStringContainsString('@', $supportEmail);
        $this->assertStringContainsString('@', $billingEmail);
    }

    public function test_all_required_templates_exist()
    {
        $requiredTemplates = [
            // Store management templates
            'store_credentials' => 'store_management',
            'store_welcome' => 'store_management',
            'password_changed' => 'store_management',
            
            // Support templates
            'ticket_created' => 'support',
            'ticket_response' => 'support',
            
            // Billing templates
            'invoice_created' => 'billing',
            'invoice_paid' => 'billing',
            'subscription_renewal_reminder' => 'billing',
            'subscription_expiration_notice' => 'billing',
            'subscription_grace_period_ending' => 'billing'
        ];

        foreach ($requiredTemplates as $templateKey => $expectedContext) {
            $template = EmailTemplate::where('template_key', $templateKey)->first();
            
            $this->assertNotNull($template, "Template '{$templateKey}' should exist");
            $this->assertEquals($expectedContext, $template->context, "Template '{$templateKey}' should have context '{$expectedContext}'");
            $this->assertTrue($template->is_active, "Template '{$templateKey}' should be active");
        }
    }

    public function test_email_service_can_send_all_template_types()
    {
        Mail::fake();

        $testEmail = 'test@example.com';

        // Test store management emails
        $this->assertTrue(EmailService::sendWithTemplate('store_credentials', [$testEmail], [
            'store_name' => 'Test Store',
            'admin_name' => 'John Doe',
            'password' => 'temp123',
            'admin_url' => 'https://test.com/admin',
            'frontend_url' => 'https://test.com',
            'support_email' => 'support@test.com'
        ]));

        $this->assertTrue(EmailService::sendWithTemplate('store_welcome', [$testEmail], [
            'app_name' => 'SuperLinkiu',
            'store_name' => 'Test Store',
            'admin_name' => 'John Doe',
            'admin_email' => $testEmail,
            'login_url' => 'https://test.com/admin',
            'support_email' => 'support@test.com'
        ]));

        // Test support emails
        $this->assertTrue(EmailService::sendWithTemplate('ticket_created', [$testEmail], [
            'ticket_id' => 'TK-001',
            'ticket_subject' => 'Test Ticket',
            'customer_name' => 'Test Store',
            'status' => 'Abierto'
        ]));

        $this->assertTrue(EmailService::sendWithTemplate('ticket_response', [$testEmail], [
            'ticket_id' => 'TK-001',
            'ticket_subject' => 'Test Ticket',
            'customer_name' => 'Test Store',
            'response' => 'Test response',
            'status' => 'En Progreso'
        ]));

        // Test billing emails
        $this->assertTrue(EmailService::sendWithTemplate('invoice_created', [$testEmail], [
            'invoice_number' => 'INV-001',
            'amount' => '99.99',
            'due_date' => '31/12/2024',
            'store_name' => 'Test Store',
            'plan_name' => 'Premium Plan'
        ]));

        $this->assertTrue(EmailService::sendWithTemplate('invoice_paid', [$testEmail], [
            'invoice_number' => 'INV-001',
            'amount' => '99.99',
            'store_name' => 'Test Store',
            'plan_name' => 'Premium Plan'
        ]));

        // Test subscription emails
        $this->assertTrue(EmailService::sendWithTemplate('subscription_renewal_reminder', [$testEmail], [
            'user_name' => 'John Doe',
            'store_name' => 'Test Store',
            'days' => '7',
            'amount' => '99.99',
            'plan_name' => 'Premium Plan',
            'billing_url' => 'https://test.com/billing'
        ]));
    }

    public function test_backward_compatibility_with_sendWithView()
    {
        Mail::fake();

        // Test that sendWithView still works for backward compatibility
        $result = EmailService::sendWithView(
            'test-view',
            ['test@example.com'],
            ['test' => 'data'],
            'Test Subject',
            'store_management'
        );

        // Should return true even if view doesn't exist (Mail::send handles that)
        $this->assertTrue($result);
    }

    public function test_email_configuration_validation()
    {
        // Test that invalid context returns default
        $invalidEmail = EmailService::getContextEmail('invalid_context');
        $this->assertEquals('no-responder@linkiudev.co', $invalidEmail);

        // Test that empty context returns default
        $emptyEmail = EmailService::getContextEmail('');
        $this->assertEquals('no-responder@linkiudev.co', $emptyEmail);
    }
}