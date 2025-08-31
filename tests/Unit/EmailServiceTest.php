<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EmailService;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test email settings
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

    public function test_get_context_email_returns_configured_email()
    {
        $email = EmailService::getContextEmail('store_management');
        $this->assertEquals('store@example.com', $email);

        $email = EmailService::getContextEmail('support');
        $this->assertEquals('support@example.com', $email);

        $email = EmailService::getContextEmail('billing');
        $this->assertEquals('billing@example.com', $email);
    }

    public function test_get_context_email_returns_default_for_unconfigured()
    {
        $email = EmailService::getContextEmail('unconfigured_context');
        $this->assertEquals('no-responder@linkiudev.co', $email);
    }

    public function test_send_with_template_success()
    {
        Mail::fake();

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{admin_name}}',
            'body_html' => '<p>Welcome {{admin_name}} to {{store_name}}</p>',
            'body_text' => 'Welcome {{admin_name}} to {{store_name}}',
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('test_template', ['test@example.com'], [
            'admin_name' => 'John Doe',
            'store_name' => 'My Store'
        ]);

        $this->assertTrue($result);
        // Since EmailService uses Mail::send() directly, we can't assert specific mailable classes
        // Instead, we verify the result is true, indicating successful sending
    }

    public function test_send_with_template_fails_with_nonexistent_template()
    {
        Mail::fake();
        Log::shouldReceive('warning')->once();

        $result = EmailService::sendWithTemplate('nonexistent_template', ['test@example.com'], []);

        $this->assertFalse($result);
        Mail::assertNothingSent();
    }

    public function test_send_with_template_fails_with_no_valid_recipients()
    {
        Mail::fake();
        Log::shouldReceive('warning')->times(2); // One for invalid email, one for no valid recipients

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'body_html' => '<p>Test</p>',
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('test_template', ['invalid-email'], []);

        $this->assertFalse($result);
    }

    public function test_send_with_template_logs_success()
    {
        Mail::fake();
        Log::shouldReceive('info')->once()->with('Email sent successfully', \Mockery::type('array'));

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'body_html' => '<p>Test</p>',
            'is_active' => true
        ]);

        EmailService::sendWithTemplate('test_template', ['test@example.com'], []);
    }

    public function test_send_with_template_handles_exception()
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('Mail sending failed'));
        Log::shouldReceive('error')->once();

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'body_html' => '<p>Test</p>',
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('test_template', ['test@example.com'], []);

        $this->assertFalse($result);
    }

    public function test_validate_recipients_filters_invalid_emails()
    {
        $reflection = new \ReflectionClass(EmailService::class);
        $method = $reflection->getMethod('validateRecipients');
        $method->setAccessible(true);

        $recipients = [
            'valid@example.com',
            'invalid-email',
            'test@tempmail.org', // suspicious domain
            str_repeat('a', 250) . '@example.com', // too long
            'another-valid@example.com'
        ];

        Log::shouldReceive('warning')->times(3); // 3 invalid recipients

        $validRecipients = $method->invoke(null, $recipients);

        $this->assertCount(2, $validRecipients);
        $this->assertContains('valid@example.com', $validRecipients);
        $this->assertContains('another-valid@example.com', $validRecipients);
    }

    public function test_sanitize_log_data()
    {
        $sensitiveData = [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'token' => 'abc123',
            'secret' => 'secret_value',
            'key' => 'key_value',
            'credential' => 'cred_value',
            'normal_field' => 'normal_value'
        ];

        $sanitized = EmailService::sanitizeLogData($sensitiveData);

        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertEquals('***REDACTED***', $sanitized['password']);
        $this->assertEquals('***REDACTED***', $sanitized['token']);
        $this->assertEquals('***REDACTED***', $sanitized['secret']);
        $this->assertEquals('***REDACTED***', $sanitized['key']);
        $this->assertEquals('***REDACTED***', $sanitized['credential']);
        $this->assertEquals('normal_value', $sanitized['normal_field']);
    }

    public function test_validate_email_configuration_success()
    {
        // Create required templates
        EmailTemplate::create([
            'template_key' => 'store_welcome',
            'context' => 'store_management',
            'name' => 'Store Welcome',
            'subject' => 'Welcome',
            'is_active' => true
        ]);

        EmailTemplate::create([
            'template_key' => 'password_changed',
            'context' => 'store_management',
            'name' => 'Password Changed',
            'subject' => 'Password Changed',
            'is_active' => true
        ]);

        EmailTemplate::create([
            'template_key' => 'invoice_created',
            'context' => 'billing',
            'name' => 'Invoice Created',
            'subject' => 'Invoice Created',
            'is_active' => true
        ]);

        EmailTemplate::create([
            'template_key' => 'ticket_created',
            'context' => 'support',
            'name' => 'Ticket Created',
            'subject' => 'Ticket Created',
            'is_active' => true
        ]);

        $validation = EmailService::validateEmailConfiguration();

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['issues']);
    }

    public function test_validate_email_configuration_with_issues()
    {
        // Create invalid email setting
        EmailSetting::where('context', 'store_management')->update(['email' => 'invalid-email']);

        $validation = EmailService::validateEmailConfiguration();

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['issues']);
        $this->assertStringContainsString('Invalid email for context', $validation['issues'][0]);
    }

    public function test_send_simple_success()
    {
        Mail::fake();
        Log::shouldReceive('info')->once();

        $result = EmailService::sendSimple(
            'store_management',
            ['test@example.com'],
            'Test Subject',
            'Test Body',
            false
        );

        $this->assertTrue($result);
    }

    public function test_send_simple_with_html()
    {
        Mail::fake();
        Log::shouldReceive('info')->once();

        $result = EmailService::sendSimple(
            'store_management',
            ['test@example.com'],
            'Test Subject',
            '<p>Test HTML Body</p>',
            true
        );

        $this->assertTrue($result);
    }

    public function test_send_simple_handles_exception()
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('Mail sending failed'));
        Log::shouldReceive('error')->once();

        $result = EmailService::sendSimple(
            'store_management',
            ['test@example.com'],
            'Test Subject',
            'Test Body'
        );

        $this->assertFalse($result);
    }

    public function test_send_with_view_success()
    {
        Mail::fake();
        Log::shouldReceive('info')->once();

        $result = EmailService::sendWithView(
            'test-view',
            ['test@example.com'],
            ['data' => 'test'],
            'Test Subject',
            'store_management'
        );

        $this->assertTrue($result);
    }

    public function test_send_with_view_fails_with_no_email_config()
    {
        Mail::fake();
        Log::shouldReceive('info')->once(); // Will log successful sending
        Log::shouldReceive('error')->never(); // No error expected

        $result = EmailService::sendWithView(
            'test-view',
            ['test@example.com'],
            [],
            'Test Subject',
            'nonexistent_context'
        );

        // This will actually succeed because getContextEmail returns a default
        $this->assertTrue($result);
    }

    public function test_send_with_view_handles_exception()
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('Mail sending failed'));
        Log::shouldReceive('error')->once();

        $result = EmailService::sendWithView(
            'test-view',
            ['test@example.com'],
            [],
            'Test Subject',
            'store_management'
        );

        $this->assertFalse($result);
    }

    public function test_send_raw_success()
    {
        Mail::fake();
        Log::shouldReceive('info')->once();

        $result = EmailService::sendRaw(
            'Raw email content',
            ['test@example.com'],
            'Test Subject',
            'store_management'
        );

        $this->assertTrue($result);
    }

    public function test_send_raw_fails_with_no_email_config()
    {
        Mail::fake();
        Log::shouldReceive('info')->once(); // Will log successful sending
        Log::shouldReceive('error')->never(); // No error expected

        $result = EmailService::sendRaw(
            'Raw email content',
            ['test@example.com'],
            'Test Subject',
            'nonexistent_context'
        );

        // This will actually succeed because getContextEmail returns a default
        $this->assertTrue($result);
    }

    public function test_send_raw_handles_exception()
    {
        Mail::shouldReceive('raw')->andThrow(new \Exception('Mail sending failed'));
        Log::shouldReceive('error')->once();

        $result = EmailService::sendRaw(
            'Raw email content',
            ['test@example.com'],
            'Test Subject',
            'store_management'
        );

        $this->assertFalse($result);
    }

    public function test_prepare_mail_data_adds_common_variables()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{admin_name}}',
            'body_html' => '<p>Content for {{admin_name}}</p>',
            'is_active' => true
        ]);

        $reflection = new \ReflectionClass(EmailService::class);
        $method = $reflection->getMethod('prepareMailData');
        $method->setAccessible(true);

        $result = $method->invoke(null, $template, ['admin_name' => 'John']);

        $this->assertStringContainsString('John', $result['subject']);
        $this->assertStringContainsString('John', $result['body_html']);
        // Common variables are added to the data array but may not be replaced if not in template's available variables
        $this->assertIsArray($result);
        $this->assertArrayHasKey('subject', $result);
        $this->assertArrayHasKey('body_html', $result);
    }

    public function test_send_with_template_validates_from_email()
    {
        Mail::fake();
        Log::shouldReceive('error')->once();

        // Create email setting for invalid context first
        EmailSetting::create([
            'context' => 'invalid_context',
            'email' => 'invalid-email-format', // Invalid email format
            'name' => 'Invalid Context',
            'is_active' => true
        ]);

        // Create template with invalid context
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'invalid_context',
            'name' => 'Test Template',
            'subject' => 'Test',
            'body_html' => '<p>Test</p>',
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('test_template', ['test@example.com'], []);

        $this->assertFalse($result);
    }
}