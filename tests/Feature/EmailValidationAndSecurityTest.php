<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailSecurityService;
use App\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class EmailValidationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@linkiudev.co',
            'password' => bcrypt('password'),
            'role' => 'super_admin'
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);
    }

    public function test_email_address_format_validation()
    {
        $validEmails = [
            'test@example.com',
            'user.name@domain.co.uk',
            'user+tag@example.org',
            'user123@test-domain.com',
            'a@b.co'
        ];

        foreach ($validEmails as $email) {
            $result = EmailSecurityService::validateEmailAddress($email);
            $this->assertTrue($result['valid'], "Email {$email} should be valid");
            $this->assertEmpty($result['issues'], "Email {$email} should have no issues");
        }

        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'test@',
            'test..test@example.com',
            'test@example',
            'test@.example.com',
            'test@example.',
            'test @example.com',
            'test@exam ple.com'
        ];

        foreach ($invalidEmails as $email) {
            $result = EmailSecurityService::validateEmailAddress($email);
            $this->assertFalse($result['valid'], "Email {$email} should be invalid");
            $this->assertNotEmpty($result['issues'], "Email {$email} should have issues");
        }
    }

    public function test_suspicious_domain_detection()
    {
        $suspiciousDomains = [
            'test@tempmail.org',
            'user@10minutemail.com',
            'fake@guerrillamail.com',
            'spam@mailinator.com'
        ];

        foreach ($suspiciousDomains as $email) {
            $result = EmailSecurityService::validateEmailAddress($email);
            $this->assertFalse($result['valid'], "Email {$email} should be blocked");
            $this->assertContains('Dominio de email no permitido', $result['issues']);
        }
    }

    public function test_email_length_validation()
    {
        // Test maximum length (RFC 5321 limit is 254 characters)
        $longLocalPart = str_repeat('a', 240);
        $longEmail = $longLocalPart . '@example.com'; // Total > 254 characters
        
        $result = EmailSecurityService::validateEmailAddress($longEmail);
        $this->assertFalse($result['valid']);
        $this->assertContains('Email demasiado largo', $result['issues']);

        // Test valid length
        $validEmail = 'test@example.com';
        $result = EmailSecurityService::validateEmailAddress($validEmail);
        $this->assertTrue($result['valid']);
    }

    public function test_html_content_sanitization()
    {
        $dangerousHtml = '
            <script>alert("xss")</script>
            <iframe src="http://evil.com"></iframe>
            <object data="malicious.swf"></object>
            <embed src="malicious.swf">
            <form action="http://evil.com">
                <input type="password" name="pass">
                <button onclick="steal()">Click</button>
            </form>
            <p>Safe content</p>
            <a href="http://safe.com">Safe link</a>
            <strong>Bold text</strong>
        ';

        $sanitized = EmailSecurityService::sanitizeHtmlContent($dangerousHtml);

        // Dangerous elements should be removed
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('<iframe>', $sanitized);
        $this->assertStringNotContainsString('<object>', $sanitized);
        $this->assertStringNotContainsString('<embed>', $sanitized);
        $this->assertStringNotContainsString('<form>', $sanitized);
        $this->assertStringNotContainsString('<input>', $sanitized);
        $this->assertStringNotContainsString('<button>', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);

        // Safe content should remain
        $this->assertStringContainsString('<p>Safe content</p>', $sanitized);
        $this->assertStringContainsString('<strong>Bold text</strong>', $sanitized);
        $this->assertStringContainsString('<a href="http://safe.com">Safe link</a>', $sanitized);
    }

    public function test_template_variable_validation()
    {
        $storeManagementVars = [
            '{{store_name}}',
            '{{admin_name}}',
            '{{admin_email}}',
            '{{password}}',
            '{{login_url}}',
            '{{support_email}}'
        ];

        // Valid variables should pass
        $content = 'Hello {{admin_name}}, your store {{store_name}} is ready!';
        $issues = EmailSecurityService::validateTemplateVariables($content, $storeManagementVars);
        $this->assertEmpty($issues);

        // Invalid variables should be caught
        $content = 'Hello {{admin_name}}, ticket {{ticket_id}} is ready!';
        $issues = EmailSecurityService::validateTemplateVariables($content, $storeManagementVars);
        $this->assertCount(1, $issues);
        $this->assertStringContainsString('{{ticket_id}}', $issues[0]);

        // Multiple invalid variables
        $content = 'Hello {{invalid_var}}, ticket {{ticket_id}} for {{another_invalid}}';
        $issues = EmailSecurityService::validateTemplateVariables($content, $storeManagementVars);
        $this->assertCount(3, $issues);
    }

    public function test_template_content_security_validation()
    {
        $secureContent = '<p>Hello {{admin_name}}, welcome to {{store_name}}!</p>';
        $issues = EmailSecurityService::validateTemplateContent($secureContent);
        $this->assertEmpty($issues);

        $dangerousContent = '
            <script>alert("xss")</script>
            <p onclick="malicious()">Click me</p>
            <iframe src="evil.com"></iframe>
        ';
        $issues = EmailSecurityService::validateTemplateContent($dangerousContent);
        $this->assertNotEmpty($issues);
        $this->assertTrue(collect($issues)->contains(function ($issue) {
            return str_contains($issue, 'script');
        }));
    }

    public function test_access_control_enforcement()
    {
        // Test unauthenticated access
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertRedirect('/superlinkiu/login');

        $response = $this->get(route('superlinkiu.email.templates.index'));
        $response->assertRedirect('/superlinkiu/login');

        // Test regular user access (should be denied)
        $this->actingAs($this->regularUser);
        
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertRedirect('/superlinkiu/login');

        $response = $this->get(route('superlinkiu.email.templates.index'));
        $response->assertRedirect('/superlinkiu/login');

        // Test super admin access (should be allowed)
        $this->actingAs($this->superAdmin);
        
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertOk();

        $response = $this->get(route('superlinkiu.email.templates.index'));
        $response->assertOk();
    }

    public function test_csrf_protection()
    {
        $this->actingAs($this->superAdmin);

        // Test POST request without CSRF token
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('superlinkiu.email.settings.update'), [
                'store_management_email' => 'test@example.com',
                'support_email' => 'support@example.com',
                'billing_email' => 'billing@example.com'
            ], [
                'X-CSRF-TOKEN' => 'invalid-token'
            ]);

        // Without CSRF middleware, this should work
        $response->assertRedirect();

        // With CSRF middleware enabled, invalid token should fail
        $this->withMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'test@example.com',
            'support_email' => 'support@example.com',
            'billing_email' => 'billing@example.com'
        ], [
            'X-CSRF-TOKEN' => 'invalid-token'
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    public function test_input_sanitization_in_forms()
    {
        $this->actingAs($this->superAdmin);

        // Test email settings form with XSS attempts
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'test@example.com',
            'support_email' => '<script>alert("xss")</script>@example.com',
            'billing_email' => 'billing@example.com'
        ]);

        $response->assertSessionHasErrors('support_email');

        // Test template form with XSS attempts
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => '<script>alert("xss")</script>Malicious Name',
            'subject' => 'Test Subject',
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_sql_injection_prevention()
    {
        $this->actingAs($this->superAdmin);

        // Test SQL injection attempts in email settings
        $sqlInjectionAttempts = [
            "test@example.com'; DROP TABLE email_settings; --",
            "test@example.com' OR '1'='1",
            "test@example.com'; INSERT INTO email_settings VALUES ('evil', 'evil@evil.com', 'Evil', 1); --"
        ];

        foreach ($sqlInjectionAttempts as $maliciousInput) {
            $response = $this->post(route('superlinkiu.email.settings.update'), [
                'store_management_email' => $maliciousInput,
                'support_email' => 'support@example.com',
                'billing_email' => 'billing@example.com'
            ]);

            // Should fail validation, not execute SQL
            $response->assertSessionHasErrors('store_management_email');
        }

        // Verify table still exists and is intact
        $this->assertDatabaseMissing('email_settings', ['email' => 'evil@evil.com']);
    }

    public function test_security_event_logging()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Email Security Event: test_security_event', \Mockery::type('array'));

        EmailSecurityService::logSecurityEvent('test_security_event', [
            'user_id' => $this->superAdmin->id,
            'ip_address' => '192.168.1.1',
            'details' => 'Test security event'
        ]);
    }

    public function test_configuration_change_auditing()
    {
        Log::shouldReceive('info')
            ->once()
            ->with(\Mockery::pattern('/Email Configuration Audit/'), \Mockery::type('array'));

        EmailSecurityService::auditConfigurationChange('email_settings_updated', [
            'store_management_email' => 'new@example.com'
        ], [
            'user_id' => $this->superAdmin->id,
            'ip_address' => '192.168.1.1'
        ]);
    }

    public function test_template_security_validation_in_controller()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        // Test dangerous HTML content
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<script>alert("xss")</script><p>Content</p>',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('seguridad', session('error'));
    }

    public function test_email_recipient_validation_in_service()
    {
        $validRecipients = ['valid@example.com', 'another@test.com'];
        $invalidRecipients = [
            'invalid-email',
            'test@tempmail.org', // suspicious domain
            str_repeat('a', 250) . '@example.com', // too long
            '@example.com' // invalid format
        ];

        $allRecipients = array_merge($validRecipients, $invalidRecipients);

        // Use reflection to test private method
        $reflection = new \ReflectionClass(\App\Services\EmailService::class);
        $method = $reflection->getMethod('validateRecipients');
        $method->setAccessible(true);

        Log::shouldReceive('warning')->times(count($invalidRecipients));

        $validatedRecipients = $method->invoke(null, $allRecipients);

        $this->assertCount(2, $validatedRecipients);
        $this->assertContains('valid@example.com', $validatedRecipients);
        $this->assertContains('another@test.com', $validatedRecipients);
    }

    public function test_sensitive_data_sanitization_in_logs()
    {
        $sensitiveData = [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'token' => 'abc123token',
            'secret' => 'secret_value',
            'key' => 'api_key_value',
            'credential' => 'credential_value',
            'normal_field' => 'normal_value',
            'another_field' => 'another_value'
        ];

        $sanitized = \App\Services\EmailService::sanitizeLogData($sensitiveData);

        // Sensitive fields should be redacted
        $this->assertEquals('***REDACTED***', $sanitized['password']);
        $this->assertEquals('***REDACTED***', $sanitized['token']);
        $this->assertEquals('***REDACTED***', $sanitized['secret']);
        $this->assertEquals('***REDACTED***', $sanitized['key']);
        $this->assertEquals('***REDACTED***', $sanitized['credential']);

        // Non-sensitive fields should remain
        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertEquals('normal_value', $sanitized['normal_field']);
        $this->assertEquals('another_value', $sanitized['another_field']);
    }

    public function test_template_variable_context_enforcement()
    {
        $this->actingAs($this->superAdmin);

        // Create templates for different contexts
        $storeTemplate = EmailTemplate::create([
            'template_key' => 'store_template',
            'context' => 'store_management',
            'name' => 'Store Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $supportTemplate = EmailTemplate::create([
            'template_key' => 'support_template',
            'context' => 'support',
            'name' => 'Support Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        // Test store template with support variables (should fail)
        $response = $this->put(route('superlinkiu.email.templates.update', $storeTemplate), [
            'name' => 'Store Template',
            'subject' => 'Ticket {{ticket_id}} for {{admin_name}}', // ticket_id not valid for store_management
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('subject');

        // Test support template with billing variables (should fail)
        $response = $this->put(route('superlinkiu.email.templates.update', $supportTemplate), [
            'name' => 'Support Template',
            'subject' => 'Invoice {{invoice_number}} for ticket {{ticket_id}}', // invoice_number not valid for support
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('subject');

        // Test with valid variables for each context
        $response = $this->put(route('superlinkiu.email.templates.update', $storeTemplate), [
            'name' => 'Store Template',
            'subject' => 'Welcome {{admin_name}} to {{store_name}}',
            'body_html' => '<p>Login at {{login_url}}</p>',
            'is_active' => true
        ]);

        $response->assertRedirect(route('superlinkiu.email.templates.index'));
        $response->assertSessionHas('success');
    }

    public function test_xss_prevention_in_template_preview()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test {{admin_name}}',
            'body_html' => '<p>Hello {{admin_name}}</p>',
            'is_active' => true
        ]);

        $response = $this->post(route('superlinkiu.email.templates.preview', $template), [
            'subject' => 'Test <script>alert("xss")</script>{{admin_name}}',
            'body_html' => '<p>Hello <script>alert("xss")</script>{{admin_name}}</p>'
        ]);

        $response->assertOk();
        $preview = $response->json('preview');

        // XSS should be sanitized in preview
        $this->assertStringNotContainsString('<script>', $preview['subject']);
        $this->assertStringNotContainsString('<script>', $preview['body_html']);
    }

    public function test_mass_assignment_protection()
    {
        // Test EmailSetting mass assignment protection
        $setting = new EmailSetting();
        $fillable = $setting->getFillable();
        
        // Should only allow specific fields
        $expectedFillable = ['context', 'email', 'name', 'is_active'];
        $this->assertEquals($expectedFillable, $fillable);

        // Test EmailTemplate mass assignment protection
        $template = new EmailTemplate();
        $fillable = $template->getFillable();
        
        $expectedFillable = [
            'template_key',
            'context',
            'name',
            'subject',
            'body_html',
            'body_text',
            'variables',
            'is_active'
        ];
        
        $this->assertEquals($expectedFillable, $fillable);
    }
}