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

class EmailSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a super admin user
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@linkiudev.co',
            'password' => bcrypt('password'),
            'role' => 'super_admin'
        ]);
    }

    /** @test */
    public function it_validates_email_addresses_with_security_checks()
    {
        // Valid email
        $result = EmailSecurityService::validateEmailAddress('valid@example.com');
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['issues']);

        // Invalid format
        $result = EmailSecurityService::validateEmailAddress('invalid-email');
        $this->assertFalse($result['valid']);
        $this->assertContains('Formato de email inválido', $result['issues']);

        // Suspicious domain
        $result = EmailSecurityService::validateEmailAddress('test@tempmail.org');
        $this->assertFalse($result['valid']);
        $this->assertContains('Dominio de email no permitido', $result['issues']);

        // Too long email
        $longEmail = str_repeat('a', 250) . '@example.com';
        $result = EmailSecurityService::validateEmailAddress($longEmail);
        $this->assertFalse($result['valid']);
        $this->assertContains('Email demasiado largo', $result['issues']);
    }

    /** @test */
    public function it_sanitizes_html_content_properly()
    {
        $dangerousHtml = '<script>alert("xss")</script><p>Safe content</p><iframe src="evil.com"></iframe>';
        $sanitized = EmailSecurityService::sanitizeHtmlContent($dangerousHtml);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('<iframe>', $sanitized);
        $this->assertStringContainsString('<p>Safe content</p>', $sanitized);
    }

    /** @test */
    public function it_validates_template_variables()
    {
        $allowedVars = ['{{store_name}}', '{{admin_name}}'];
        $content = 'Hello {{admin_name}}, your store {{store_name}} is ready. Invalid: {{invalid_var}}';
        
        $issues = EmailSecurityService::validateTemplateVariables($content, $allowedVars);
        
        $this->assertCount(1, $issues);
        $this->assertStringContainsString('{{invalid_var}}', $issues[0]);
    }

    /** @test */
    public function it_validates_template_content_for_security_issues()
    {
        $dangerousContent = '<script>alert("xss")</script><p onclick="evil()">Click me</p>';
        $issues = EmailSecurityService::validateTemplateContent($dangerousContent);
        
        $this->assertNotEmpty($issues);
        $this->assertTrue(collect($issues)->contains(function ($issue) {
            return str_contains($issue, 'script');
        }));
    }

    /** @test */
    public function it_prevents_email_configuration_updates_with_invalid_emails()
    {
        $this->actingAs($this->superAdmin);

        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'invalid-email',
            'support_email' => 'valid@example.com',
            'billing_email' => 'valid@example.com'
        ]);

        $response->assertSessionHasErrors('store_management_email');
    }

    /** @test */
    public function it_prevents_template_updates_with_dangerous_html()
    {
        $this->actingAs($this->superAdmin);
        
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<p>Test content</p>',
            'is_active' => true
        ]);

        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<script>alert("xss")</script><p>Content</p>',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function it_validates_template_variables_in_forms()
    {
        $this->actingAs($this->superAdmin);
        
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<p>Test content</p>',
            'is_active' => true
        ]);

        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Hello {{invalid_variable}}',
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('subject');
    }

    /** @test */
    public function it_logs_security_events()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Email Security Event: test_event', \Mockery::type('array'));

        EmailSecurityService::logSecurityEvent('test_event', ['test' => 'data']);
    }

    /** @test */
    public function it_audits_configuration_changes()
    {
        $this->actingAs($this->superAdmin);

        Log::shouldReceive('info')
            ->once()
            ->with(\Mockery::pattern('/Email Configuration Audit/'), \Mockery::type('array'));

        EmailSecurityService::auditConfigurationChange('test_action', ['test' => 'change']);
    }

    /** @test */
    public function it_enforces_csrf_protection()
    {
        $this->actingAs($this->superAdmin);

        // Attempt to update without CSRF token
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'test@example.com',
            'support_email' => 'support@example.com',
            'billing_email' => 'billing@example.com'
        ], [
            'X-CSRF-TOKEN' => 'invalid-token'
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /** @test */
    public function it_requires_super_admin_role()
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);
        $this->actingAs($regularUser);

        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertRedirect('/superlinkiu/login');
    }

    /** @test */
    public function it_sanitizes_log_data()
    {
        $sensitiveData = [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'token' => 'abc123',
            'normal_field' => 'normal_value'
        ];

        $sanitized = \App\Services\EmailService::sanitizeLogData($sensitiveData);

        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertEquals('***REDACTED***', $sanitized['password']);
        $this->assertEquals('***REDACTED***', $sanitized['token']);
        $this->assertEquals('normal_value', $sanitized['normal_field']);
    }

    /** @test */
    public function it_validates_email_recipients_in_service()
    {
        // This tests the private method through the public sendWithTemplate method
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test {{store_name}}',
            'body_html' => '<p>Hello {{admin_name}}</p>',
            'is_active' => true
        ]);

        EmailSetting::create([
            'context' => 'store_management',
            'email' => 'from@example.com',
            'name' => 'Store Management',
            'is_active' => true
        ]);

        // Mock Mail facade to prevent actual sending
        \Mail::fake();

        $result = \App\Services\EmailService::sendWithTemplate(
            'test_template',
            ['valid@example.com', 'invalid-email', 'test@tempmail.org'],
            ['store_name' => 'Test Store', 'admin_name' => 'Admin']
        );

        // Should return true even with some invalid recipients
        $this->assertTrue($result);
    }
}