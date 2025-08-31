<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EmailTemplate;
use App\Models\EmailSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create email settings for testing
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

    public function test_get_template_returns_active_template()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<p>Test content</p>',
            'is_active' => true
        ]);

        $found = EmailTemplate::getTemplate('test_template');
        $this->assertNotNull($found);
        $this->assertEquals($template->id, $found->id);
    }

    public function test_get_template_ignores_inactive_template()
    {
        EmailTemplate::create([
            'template_key' => 'inactive_template',
            'context' => 'store_management',
            'name' => 'Inactive Template',
            'subject' => 'Test Subject',
            'body_html' => '<p>Test content</p>',
            'is_active' => false
        ]);

        $found = EmailTemplate::getTemplate('inactive_template');
        $this->assertNull($found);
    }

    public function test_get_template_returns_null_for_nonexistent()
    {
        $found = EmailTemplate::getTemplate('nonexistent_template');
        $this->assertNull($found);
    }

    public function test_render_template_with_existing_template()
    {
        EmailTemplate::create([
            'template_key' => 'welcome_template',
            'context' => 'store_management',
            'name' => 'Welcome Template',
            'subject' => 'Welcome {{admin_name}}',
            'body_html' => '<p>Hello {{admin_name}}, your store {{store_name}} is ready!</p>',
            'body_text' => 'Hello {{admin_name}}, your store {{store_name}} is ready!',
            'is_active' => true
        ]);

        $result = EmailTemplate::renderTemplate('welcome_template', [
            'admin_name' => 'John Doe',
            'store_name' => 'My Store'
        ]);

        $this->assertEquals('Welcome John Doe', $result['subject']);
        $this->assertStringContainsString('Hello John Doe', $result['body_html']);
        $this->assertStringContainsString('My Store', $result['body_html']);
        $this->assertStringContainsString('Hello John Doe', $result['body_text']);
    }

    public function test_render_template_with_nonexistent_template()
    {
        $result = EmailTemplate::renderTemplate('nonexistent_template', []);

        $this->assertEquals('Notificación', $result['subject']);
        $this->assertEquals('Contenido no disponible', $result['body_html']);
        $this->assertEquals('Contenido no disponible', $result['body_text']);
    }

    public function test_replace_variables_with_valid_data()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{admin_name}}',
            'body_html' => '<p>Store: {{store_name}}</p>',
            'body_text' => 'Store: {{store_name}}',
            'is_active' => true
        ]);

        $result = $template->replaceVariables([
            'admin_name' => 'John Doe',
            'store_name' => 'My Store'
        ]);

        $this->assertEquals('Hello John Doe', $result['subject']);
        $this->assertStringContainsString('Store: My Store', $result['body_html']);
        $this->assertStringContainsString('Store: My Store', $result['body_text']);
    }

    public function test_replace_variables_sanitizes_html()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{admin_name}}',
            'body_html' => '<p>Store: {{store_name}}</p>',
            'is_active' => true
        ]);

        $result = $template->replaceVariables([
            'admin_name' => '<script>alert("xss")</script>John',
            'store_name' => '<b>My Store</b>'
        ]);

        $this->assertStringNotContainsString('<script>', $result['subject']);
        $this->assertStringNotContainsString('<script>', $result['body_html']);
        $this->assertStringContainsString('&lt;script&gt;', $result['subject']);
    }

    public function test_replace_variables_ignores_invalid_variables()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{admin_name}} and {{invalid_var}}',
            'body_html' => '<p>Store: {{store_name}}</p>',
            'is_active' => true
        ]);

        $result = $template->replaceVariables([
            'admin_name' => 'John Doe',
            'store_name' => 'My Store',
            'invalid_var' => 'Should not replace'
        ]);

        $this->assertEquals('Hello John Doe and {{invalid_var}}', $result['subject']);
        $this->assertStringContainsString('Store: My Store', $result['body_html']);
    }

    public function test_replace_variables_handles_non_string_values()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'billing',
            'name' => 'Test Template',
            'subject' => 'Amount: {{amount}}',
            'body_html' => '<p>Days: {{days}}</p>',
            'is_active' => true
        ]);

        $result = $template->replaceVariables([
            'amount' => 99.99,
            'days' => 30
        ]);

        $this->assertEquals('Amount: 99.99', $result['subject']);
        $this->assertStringContainsString('Days: 30', $result['body_html']);
    }

    public function test_validate_template_variables_with_valid_variables()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{admin_name}}',
            'body_html' => '<p>Store: {{store_name}}</p>',
            'body_text' => 'Login: {{login_url}}',
            'is_active' => true
        ]);

        $issues = $template->validateTemplateVariables();
        $this->assertEmpty($issues);
    }

    public function test_validate_template_variables_with_invalid_variables()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Hello {{invalid_var}}',
            'body_html' => '<p>Store: {{another_invalid}}</p>',
            'body_text' => 'Valid: {{admin_name}} Invalid: {{third_invalid}}',
            'is_active' => true
        ]);

        $issues = $template->validateTemplateVariables();
        $this->assertCount(3, $issues);
        $this->assertStringContainsString('{{invalid_var}}', $issues[0]);
        $this->assertStringContainsString('{{another_invalid}}', $issues[1]);
        $this->assertStringContainsString('{{third_invalid}}', $issues[2]);
    }

    public function test_get_available_variables_for_store_management()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $variables = $template->getAvailableVariables();
        
        $expectedVariables = [
            '{{store_name}}',
            '{{admin_name}}',
            '{{admin_email}}',
            '{{password}}',
            '{{login_url}}',
            '{{support_email}}'
        ];

        foreach ($expectedVariables as $var) {
            $this->assertArrayHasKey($var, $variables);
        }
    }

    public function test_get_available_variables_for_support()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'support',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $variables = $template->getAvailableVariables();
        
        $expectedVariables = [
            '{{ticket_id}}',
            '{{ticket_subject}}',
            '{{customer_name}}',
            '{{response}}',
            '{{status}}'
        ];

        foreach ($expectedVariables as $var) {
            $this->assertArrayHasKey($var, $variables);
        }
    }

    public function test_get_available_variables_for_billing()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'billing',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $variables = $template->getAvailableVariables();
        
        $expectedVariables = [
            '{{invoice_number}}',
            '{{amount}}',
            '{{due_date}}',
            '{{store_name}}',
            '{{plan_name}}',
            '{{user_name}}',
            '{{days}}',
            '{{days_left}}',
            '{{billing_url}}'
        ];

        foreach ($expectedVariables as $var) {
            $this->assertArrayHasKey($var, $variables);
        }
    }

    public function test_get_available_variables_for_unknown_context()
    {
        // Create a setting for unknown context first
        EmailSetting::create([
            'context' => 'unknown_context',
            'email' => 'unknown@example.com',
            'name' => 'Unknown Context',
            'is_active' => true
        ]);

        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'unknown_context',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $variables = $template->getAvailableVariables();
        $this->assertEmpty($variables);
    }

    public function test_email_setting_relationship()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $emailSetting = $template->emailSetting;
        $this->assertNotNull($emailSetting);
        $this->assertEquals('store_management', $emailSetting->context);
    }

    public function test_active_scope()
    {
        EmailTemplate::create([
            'template_key' => 'active_template',
            'context' => 'store_management',
            'name' => 'Active Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        EmailTemplate::create([
            'template_key' => 'inactive_template',
            'context' => 'store_management',
            'name' => 'Inactive Template',
            'subject' => 'Test',
            'is_active' => false
        ]);

        $activeTemplates = EmailTemplate::active()->get();
        $this->assertCount(1, $activeTemplates);
        $this->assertEquals('active_template', $activeTemplates->first()->template_key);
    }

    public function test_by_context_scope()
    {
        EmailTemplate::create([
            'template_key' => 'store_template',
            'context' => 'store_management',
            'name' => 'Store Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        EmailTemplate::create([
            'template_key' => 'support_template',
            'context' => 'support',
            'name' => 'Support Template',
            'subject' => 'Test',
            'is_active' => true
        ]);

        $storeTemplates = EmailTemplate::byContext('store_management')->get();
        $this->assertCount(1, $storeTemplates);
        $this->assertEquals('store_template', $storeTemplates->first()->template_key);
    }

    public function test_fillable_attributes()
    {
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

    public function test_variables_cast_to_array()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'variables' => ['test' => 'value'], // Pass as array, not JSON string
            'is_active' => true
        ]);

        $this->assertIsArray($template->variables);
        $this->assertEquals(['test' => 'value'], $template->variables);
    }

    public function test_is_active_cast_to_boolean()
    {
        $template = EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test',
            'is_active' => '1'
        ]);

        $this->assertIsBool($template->is_active);
        $this->assertTrue($template->is_active);
    }

    public function test_log_unreplaced_variables()
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
}