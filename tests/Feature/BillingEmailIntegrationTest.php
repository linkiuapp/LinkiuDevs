<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class BillingEmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed email settings and templates
        $this->artisan('db:seed', ['--class' => 'EmailSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    public function test_billing_context_uses_correct_email_address()
    {
        $contextEmail = EmailService::getContextEmail('billing');
        
        // Should use the configured email or default
        $expectedEmail = EmailSetting::where('context', 'billing')->first()->email ?? 'contabilidad@linkiudev.co';
        
        $this->assertEquals($expectedEmail, $contextEmail);
    }

    public function test_invoice_templates_exist_and_are_configured()
    {
        // Verify invoice created template exists
        $invoiceCreatedTemplate = EmailTemplate::where('template_key', 'invoice_created')->first();
        $this->assertNotNull($invoiceCreatedTemplate);
        $this->assertEquals('billing', $invoiceCreatedTemplate->context);

        // Verify invoice paid template exists
        $invoicePaidTemplate = EmailTemplate::where('template_key', 'invoice_paid')->first();
        $this->assertNotNull($invoicePaidTemplate);
        $this->assertEquals('billing', $invoicePaidTemplate->context);
    }

    public function test_subscription_templates_exist_and_are_configured()
    {
        // Verify subscription templates exist
        $renewalTemplate = EmailTemplate::where('template_key', 'subscription_renewal_reminder')->first();
        $this->assertNotNull($renewalTemplate);
        $this->assertEquals('billing', $renewalTemplate->context);

        $expirationTemplate = EmailTemplate::where('template_key', 'subscription_expiration_notice')->first();
        $this->assertNotNull($expirationTemplate);
        $this->assertEquals('billing', $expirationTemplate->context);

        $graceTemplate = EmailTemplate::where('template_key', 'subscription_grace_period_ending')->first();
        $this->assertNotNull($graceTemplate);
        $this->assertEquals('billing', $graceTemplate->context);
    }

    public function test_invoice_email_sending_with_template()
    {
        Mail::fake();

        // Test data
        $email = 'test@example.com';
        $invoiceData = [
            'invoice_number' => 'INV-001',
            'amount' => '99.99',
            'due_date' => '31/12/2024',
            'store_name' => 'Test Store',
            'plan_name' => 'Premium Plan'
        ];

        // Send invoice created email
        $result = EmailService::sendWithTemplate(
            'invoice_created',
            [$email],
            $invoiceData
        );
        $this->assertTrue($result);

        // Send invoice paid email
        $result = EmailService::sendWithTemplate(
            'invoice_paid',
            [$email],
            $invoiceData
        );
        $this->assertTrue($result);
    }

    public function test_subscription_email_sending_with_template()
    {
        Mail::fake();

        // Test data
        $email = 'test@example.com';
        $subscriptionData = [
            'user_name' => 'John Doe',
            'store_name' => 'Test Store',
            'plan_name' => 'Premium Plan',
            'billing_url' => 'https://example.com/billing',
            'days' => '7',
            'amount' => '99.99'
        ];

        // Send renewal reminder email
        $result = EmailService::sendWithTemplate(
            'subscription_renewal_reminder',
            [$email],
            $subscriptionData
        );
        $this->assertTrue($result);

        // Send expiration notice email
        $result = EmailService::sendWithTemplate(
            'subscription_expiration_notice',
            [$email],
            $subscriptionData
        );
        $this->assertTrue($result);

        // Send grace period ending email
        $graceData = array_merge($subscriptionData, ['days_left' => '3']);
        $result = EmailService::sendWithTemplate(
            'subscription_grace_period_ending',
            [$email],
            $graceData
        );
        $this->assertTrue($result);
    }
}