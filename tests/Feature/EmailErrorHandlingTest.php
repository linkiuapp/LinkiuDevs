<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Services\EmailSecurityService;
use App\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmailErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@linkiudev.co',
            'password' => bcrypt('password'),
            'role' => 'super_admin'
        ]);

        // Create basic email settings
        EmailSetting::create([
            'context' => 'store_management',
            'email' => 'store@example.com',
            'name' => 'Store Management',
            'is_active' => true
        ]);
    }

    public function test_email_service_handles_missing_template()
    {
        Mail::fake();
        Log::shouldReceive('warning')->once();

        $result = EmailService::sendWithTemplate('nonexistent_template', ['test@example.com'], []);

        $this->assertFalse($result);
        Mail::assertNothingSent();
    }

    public function test_email_service_handles_invalid_recipients()
    {
        Mail::fake();
        Log::shouldReceive('warning')->once();

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'body_html' => '<p>Test</p>',
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('test_template', ['invalid-email', 'another-invalid'], []);

        $this->assertFalse($result);
        Mail::assertNothingSent();
    }

    public function test_email_service_handles_mail_sending_exception()
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('SMTP connection failed'));
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

    public function test_email_service_handles_invalid_from_email()
    {
        Mail::fake();
        Log::shouldReceive('error')->once();

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
        Mail::assertNothingSent();
    }

    public function test_controller_handles_email_settings_update_exception()
    {
        $this->actingAs($this->superAdmin);

        // Mock database exception
        DB::shouldReceive('transaction')->andThrow(new \Exception('Database connection lost'));

        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'test@example.com',
            'support_email' => 'support@example.com',
            'billing_email' => 'billing@example.com'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_controller_handles_template_update_exception()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        // Mock database exception
        DB::shouldReceive('transaction')->andThrow(new \Exception('Database error'));

        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Updated Template',
            'subject' => 'Updated Subject',
            'body_html' => '<p>Updated content</p>',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_template_validation_with_dangerous_html()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $dangerousHtml = '
            <script>alert("xss")</script>
            <iframe src="http://evil.com"></iframe>
            <object data="malicious.swf"></object>
            <embed src="malicious.swf">
            <form action="http://evil.com"><input type="password"></form>
        ';

        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => $dangerousHtml,
            'is_active' => true
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_template_validation_with_invalid_variables()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Hello {{invalid_variable}} and {{another_invalid}}',
            'body_html' => '<p>Ticket: {{ticket_id}}</p>', // Not valid for store_management
            'body_text' => 'Invoice: {{invoice_number}}', // Not valid for store_management
            'is_active' => true
        ]);

        $response->assertSessionHasErrors(['subject', 'body_html', 'body_text']);
    }

    public function test_email_settings_validation_with_suspicious_domains()
    {
        $this->actingAs($this->superAdmin);

        $suspiciousDomains = [
            'test@tempmail.org',
            'user@10minutemail.com',
            'fake@guerrillamail.com',
            'spam@mailinator.com'
        ];

        foreach ($suspiciousDomains as $email) {
            $response = $this->post(route('superlinkiu.email.settings.update'), [
                'store_management_email' => $email,
                'support_email' => 'valid@example.com',
                'billing_email' => 'valid@example.com'
            ]);

            $response->assertSessionHasErrors('store_management_email');
        }
    }

    public function test_email_settings_validation_with_invalid_formats()
    {
        $this->actingAs($this->superAdmin);

        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'test@',
            'test..test@example.com',
            'test@example',
            'test@.example.com',
            str_repeat('a', 250) . '@example.com' // Too long
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->post(route('superlinkiu.email.settings.update'), [
                'store_management_email' => $email,
                'support_email' => 'valid@example.com',
                'billing_email' => 'valid@example.com'
            ]);

            $response->assertSessionHasErrors('store_management_email');
        }
    }

    public function test_template_name_validation_with_invalid_characters()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $invalidNames = [
            'Template<script>alert("xss")</script>',
            'Template with / slash',
            'Template with | pipe',
            'Template with * asterisk',
            'Template with ? question',
            'Template with " quote'
        ];

        foreach ($invalidNames as $name) {
            $response = $this->put(route('superlinkiu.email.templates.update', $template), [
                'name' => $name,
                'subject' => 'Test Subject',
                'body_html' => '<p>Content</p>',
                'is_active' => true
            ]);

            $response->assertSessionHasErrors('name');
        }
    }

    public function test_template_content_length_validation()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        // Test subject too long
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => str_repeat('a', 501), // Max 500 characters
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('subject');

        // Test HTML body too long
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => str_repeat('a', 65536), // Max 65535 characters
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('body_html');

        // Test text body too long
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_text' => str_repeat('a', 65536), // Max 65535 characters
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('body_text');
    }

    public function test_database_constraint_violations()
    {
        // Test duplicate context in email settings
        EmailSetting::create([
            'context' => 'test_context',
            'email' => 'first@example.com',
            'name' => 'First Setting',
            'is_active' => true
        ]);

        // This should update, not create duplicate
        $result = EmailSetting::updateContext('test_context', 'second@example.com');
        $this->assertTrue($result);

        // Verify only one record exists
        $this->assertEquals(1, EmailSetting::where('context', 'test_context')->count());
        $this->assertEquals('second@example.com', EmailSetting::where('context', 'test_context')->first()->email);
    }

    public function test_template_key_uniqueness()
    {
        EmailTemplate::create([
            'template_key' => 'unique_template',
            'context' => 'store_management',
            'name' => 'First Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        // Attempting to create another template with same key should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        EmailTemplate::create([
            'template_key' => 'unique_template',
            'context' => 'support',
            'name' => 'Second Template',
            'subject' => 'Test',
            'is_active' => true
        ]);
    }

    public function test_email_service_with_corrupted_template_data()
    {
        Mail::fake();

        $template = EmailTemplate::create([
            'template_key' => 'corrupted_template',
            'context' => 'store_management',
            'name' => 'Corrupted Template',
            'subject' => null, // Null subject
            'body_html' => null, // Null body
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('corrupted_template', ['test@example.com'], []);

        // Should handle gracefully and still attempt to send
        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);
    }

    public function test_template_variable_replacement_with_malformed_placeholders()
    {
        $template = EmailTemplate::create([
            'template_key' => 'malformed_template',
            'context' => 'store_management',
            'name' => 'Malformed Template',
            'subject' => 'Hello {{admin_name} and {store_name}} and {{incomplete',
            'body_html' => '<p>Content with {{}} empty placeholder</p>',
            'is_active' => true
        ]);

        $result = $template->replaceVariables([
            'admin_name' => 'John Doe',
            'store_name' => 'My Store'
        ]);

        // Malformed placeholders should remain unchanged
        $this->assertStringContainsString('{{admin_name}', $result['subject']);
        $this->assertStringContainsString('{store_name}}', $result['subject']);
        $this->assertStringContainsString('{{incomplete', $result['subject']);
        $this->assertStringContainsString('{{}}', $result['body_html']);
    }

    public function test_email_service_handles_empty_recipient_list()
    {
        Mail::fake();
        Log::shouldReceive('warning')->once();

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'body_html' => '<p>Test</p>',
            'is_active' => true
        ]);

        $result = EmailService::sendWithTemplate('test_template', [], []);

        $this->assertFalse($result);
        Mail::assertNothingSent();
    }

    public function test_security_service_handles_edge_cases()
    {
        // Test with null input
        $result = EmailSecurityService::validateEmailAddress(null);
        $this->assertFalse($result['valid']);

        // Test with empty string
        $result = EmailSecurityService::validateEmailAddress('');
        $this->assertFalse($result['valid']);

        // Test HTML sanitization with null
        $sanitized = EmailSecurityService::sanitizeHtmlContent(null);
        $this->assertEquals('', $sanitized);

        // Test template variable validation with empty arrays
        $issues = EmailSecurityService::validateTemplateVariables('{{test}}', []);
        $this->assertNotEmpty($issues);
    }

    public function test_controller_handles_nonexistent_template_edit()
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get('/superlinkiu/email/templates/99999/edit');
        $response->assertNotFound();
    }

    public function test_controller_handles_nonexistent_template_update()
    {
        $this->actingAs($this->superAdmin);

        $response = $this->put('/superlinkiu/email/templates/99999', [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertNotFound();
    }

    public function test_template_preview_with_invalid_template()
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post('/superlinkiu/email/templates/99999/preview');
        $response->assertNotFound();
    }

    public function test_email_configuration_validation_with_missing_templates()
    {
        // Remove all templates
        EmailTemplate::truncate();

        $validation = EmailService::validateEmailConfiguration();

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['issues']);
        $this->assertStringContainsString('Missing template', $validation['issues'][0]);
    }

    public function test_rate_limiting_on_configuration_changes()
    {
        $this->actingAs($this->superAdmin);

        // This would require implementing rate limiting middleware
        // For now, we'll test that multiple rapid requests don't cause issues
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('superlinkiu.email.settings.update'), [
                'store_management_email' => "test{$i}@example.com",
                'support_email' => 'support@example.com',
                'billing_email' => 'billing@example.com'
            ]);

            $response->assertRedirect();
        }

        // Verify final state
        $this->assertEquals('test4@example.com', EmailSetting::getEmail('store_management'));
    }
}