<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EmailSecurityService;

class EmailSecurityServiceTest extends TestCase
{
    public function test_validates_email_addresses_with_security_checks()
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

        // Too long email (valid format but too long)
        $longLocalPart = str_repeat('a', 250); // Make local part very long
        $longEmail = $longLocalPart . '@example.com'; // Total length > 254
        $result = EmailSecurityService::validateEmailAddress($longEmail);
        $this->assertFalse($result['valid']);
        // Should have length issue even if format is invalid
        $this->assertNotEmpty($result['issues']);
    }

    public function test_sanitizes_html_content_properly()
    {
        $dangerousHtml = '<script>alert("xss")</script><p>Safe content</p><iframe src="evil.com"></iframe>';
        $sanitized = EmailSecurityService::sanitizeHtmlContent($dangerousHtml);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('<iframe>', $sanitized);
        $this->assertStringContainsString('<p>Safe content</p>', $sanitized);
    }

    public function test_validates_template_variables()
    {
        $allowedVars = ['{{store_name}}', '{{admin_name}}'];
        $content = 'Hello {{admin_name}}, your store {{store_name}} is ready. Invalid: {{invalid_var}}';
        
        $issues = EmailSecurityService::validateTemplateVariables($content, $allowedVars);
        
        $this->assertCount(1, $issues);
        $this->assertStringContainsString('{{invalid_var}}', $issues[0]);
    }

    public function test_validates_template_content_for_security_issues()
    {
        $dangerousContent = '<script>alert("xss")</script><p onclick="evil()">Click me</p>';
        $issues = EmailSecurityService::validateTemplateContent($dangerousContent);
        
        $this->assertNotEmpty($issues);
        $this->assertTrue(collect($issues)->contains(function ($issue) {
            return str_contains($issue, 'script');
        }));
    }
}