<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Shared\Models\Ticket;
use App\Shared\Models\Store;
use App\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class TicketEmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed email settings and templates
        $this->artisan('db:seed', ['--class' => 'EmailSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    public function test_ticket_email_notification_methods_work()
    {
        Mail::fake();

        // Create minimal test data for email testing
        $ticketData = [
            'ticket_id' => 'TK-001',
            'ticket_subject' => 'Test Support Ticket',
            'customer_name' => 'Test Store',
            'status' => 'Abierto'
        ];

        // Test ticket created email
        $result = EmailService::sendWithTemplate(
            'ticket_created',
            ['test@example.com'],
            $ticketData
        );
        $this->assertTrue($result);

        // Test ticket response email
        $responseData = array_merge($ticketData, [
            'response' => 'This is a test response to your ticket.'
        ]);

        $result = EmailService::sendWithTemplate(
            'ticket_response',
            ['test@example.com'],
            $responseData
        );
        $this->assertTrue($result);
    }

    public function test_support_context_uses_correct_email_address()
    {
        $contextEmail = EmailService::getContextEmail('support');
        
        // Should use the configured email or default
        $expectedEmail = EmailSetting::where('context', 'support')->first()->email ?? 'soporte@linkiudev.co';
        
        $this->assertEquals($expectedEmail, $contextEmail);
    }

    public function test_ticket_templates_exist_and_are_configured()
    {
        // Verify ticket created template exists
        $ticketCreatedTemplate = EmailTemplate::where('template_key', 'ticket_created')->first();
        $this->assertNotNull($ticketCreatedTemplate);
        $this->assertEquals('support', $ticketCreatedTemplate->context);

        // Verify ticket response template exists
        $ticketResponseTemplate = EmailTemplate::where('template_key', 'ticket_response')->first();
        $this->assertNotNull($ticketResponseTemplate);
        $this->assertEquals('support', $ticketResponseTemplate->context);
    }

    public function test_ticket_email_sending_with_template()
    {
        Mail::fake();

        // Test data
        $email = 'test@example.com';
        $ticketData = [
            'ticket_id' => 'TK-001',
            'ticket_subject' => 'Test Support Ticket',
            'customer_name' => 'Test Store',
            'status' => 'Abierto'
        ];

        // Send email using the template system
        $result = EmailService::sendWithTemplate(
            'ticket_created',
            [$email],
            $ticketData
        );

        $this->assertTrue($result);
    }
}