<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_email_returns_configured_email()
    {
        EmailSetting::create([
            'context' => 'store_management',
            'email' => 'custom@example.com',
            'name' => 'Custom Store Management',
            'is_active' => true
        ]);

        $email = EmailSetting::getEmail('store_management');
        $this->assertEquals('custom@example.com', $email);
    }

    public function test_get_email_returns_default_when_no_configuration()
    {
        $email = EmailSetting::getEmail('store_management');
        $this->assertEquals('no-responder@linkiudev.co', $email);

        $email = EmailSetting::getEmail('support');
        $this->assertEquals('soporte@linkiudev.co', $email);

        $email = EmailSetting::getEmail('billing');
        $this->assertEquals('contabilidad@linkiudev.co', $email);
    }

    public function test_get_email_returns_default_for_invalid_context()
    {
        $email = EmailSetting::getEmail('invalid_context');
        $this->assertEquals('no-responder@linkiudev.co', $email);
    }

    public function test_get_email_ignores_inactive_settings()
    {
        EmailSetting::create([
            'context' => 'support',
            'email' => 'inactive@example.com',
            'name' => 'Inactive Support',
            'is_active' => false
        ]);

        $email = EmailSetting::getEmail('support');
        $this->assertEquals('soporte@linkiudev.co', $email);
    }

    public function test_get_active_settings_returns_only_active()
    {
        EmailSetting::create([
            'context' => 'store_management',
            'email' => 'active@example.com',
            'name' => 'Active Setting',
            'is_active' => true
        ]);

        EmailSetting::create([
            'context' => 'support',
            'email' => 'inactive@example.com',
            'name' => 'Inactive Setting',
            'is_active' => false
        ]);

        $activeSettings = EmailSetting::getActiveSettings();
        $this->assertCount(1, $activeSettings);
        $this->assertEquals('active@example.com', $activeSettings->first()->email);
    }

    public function test_update_context_creates_new_setting()
    {
        $result = EmailSetting::updateContext('billing', 'new@example.com');
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('email_settings', [
            'context' => 'billing',
            'email' => 'new@example.com',
            'name' => 'Facturación',
            'is_active' => true
        ]);
    }

    public function test_update_context_updates_existing_setting()
    {
        EmailSetting::create([
            'context' => 'billing',
            'email' => 'old@example.com',
            'name' => 'Old Billing',
            'is_active' => false
        ]);

        $result = EmailSetting::updateContext('billing', 'updated@example.com');
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('email_settings', [
            'context' => 'billing',
            'email' => 'updated@example.com',
            'name' => 'Facturación',
            'is_active' => true
        ]);
        
        // Should only have one record for this context
        $this->assertEquals(1, EmailSetting::where('context', 'billing')->count());
    }

    public function test_templates_relationship()
    {
        $setting = EmailSetting::create([
            'context' => 'store_management',
            'email' => 'test@example.com',
            'name' => 'Test Setting',
            'is_active' => true
        ]);

        EmailTemplate::create([
            'template_key' => 'test_template',
            'context' => 'store_management',
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<p>Test</p>',
            'is_active' => true
        ]);

        $templates = $setting->templates;
        $this->assertCount(1, $templates);
        $this->assertEquals('test_template', $templates->first()->template_key);
    }

    public function test_active_scope()
    {
        EmailSetting::create([
            'context' => 'store_management',
            'email' => 'active@example.com',
            'name' => 'Active Setting',
            'is_active' => true
        ]);

        EmailSetting::create([
            'context' => 'support',
            'email' => 'inactive@example.com',
            'name' => 'Inactive Setting',
            'is_active' => false
        ]);

        $activeSettings = EmailSetting::active()->get();
        $this->assertCount(1, $activeSettings);
        $this->assertEquals('active@example.com', $activeSettings->first()->email);
    }

    public function test_fillable_attributes()
    {
        $setting = new EmailSetting();
        $fillable = $setting->getFillable();
        
        $expectedFillable = ['context', 'email', 'name', 'is_active'];
        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_is_active_cast_to_boolean()
    {
        $setting = EmailSetting::create([
            'context' => 'test',
            'email' => 'test@example.com',
            'name' => 'Test',
            'is_active' => '1'
        ]);

        $this->assertIsBool($setting->is_active);
        $this->assertTrue($setting->is_active);
    }

    public function test_get_default_email_method()
    {
        // Test through reflection since it's private
        $reflection = new \ReflectionClass(EmailSetting::class);
        $method = $reflection->getMethod('getDefaultEmail');
        $method->setAccessible(true);

        $this->assertEquals('no-responder@linkiudev.co', $method->invoke(null, 'store_management'));
        $this->assertEquals('soporte@linkiudev.co', $method->invoke(null, 'support'));
        $this->assertEquals('contabilidad@linkiudev.co', $method->invoke(null, 'billing'));
        $this->assertEquals('no-responder@linkiudev.co', $method->invoke(null, 'unknown'));
    }

    public function test_get_context_name_method()
    {
        // Test through reflection since it's private
        $reflection = new \ReflectionClass(EmailSetting::class);
        $method = $reflection->getMethod('getContextName');
        $method->setAccessible(true);

        $this->assertEquals('Gestión de Tiendas', $method->invoke(null, 'store_management'));
        $this->assertEquals('Soporte', $method->invoke(null, 'support'));
        $this->assertEquals('Facturación', $method->invoke(null, 'billing'));
        $this->assertEquals('Unknown', $method->invoke(null, 'unknown'));
    }
}