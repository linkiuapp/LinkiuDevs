<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class StoreEmailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed email settings and templates
        $this->artisan('db:seed', ['--class' => 'EmailSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    /** @test */
    public function store_credentials_email_uses_correct_template_and_context()
    {
        Mail::fake();

        // Test data
        $email = 'test@example.com';
        $credentials = [
            'store_name' => 'Test Store',
            'name' => 'John Doe',
            'password' => 'temp123',
            'admin_url' => 'https://teststore.linkiu.bio/admin',
            'frontend_url' => 'https://teststore.linkiu.bio'
        ];

        // Send email using the new template system
        $result = EmailService::sendWithTemplate(
            'store_credentials',
            [$email],
            [
                'store_name' => $credentials['store_name'],
                'admin_name' => $credentials['name'],
                'password' => $credentials['password'],
                'admin_url' => $credentials['admin_url'],
                'frontend_url' => $credentials['frontend_url'],
                'support_email' => EmailService::getContextEmail('support')
            ]
        );

        $this->assertTrue($result);
        
        // Verify template exists
        $template = EmailTemplate::where('template_key', 'store_credentials')->first();
        $this->assertNotNull($template);
        $this->assertEquals('store_management', $template->context);
    }

    /** @test */
    public function store_welcome_email_uses_correct_template_and_context()
    {
        Mail::fake();

        // Test data
        $email = 'test@example.com';
        $storeData = ['name' => 'Test Store'];
        $credentials = ['name' => 'John Doe', 'admin_url' => 'https://teststore.linkiu.bio/admin'];

        // Send email using the new template system
        $result = EmailService::sendWithTemplate(
            'store_welcome',
            [$email],
            [
                'app_name' => config('app.name', 'SuperLinkiu'),
                'store_name' => $storeData['name'],
                'admin_name' => $credentials['name'],
                'admin_email' => $email,
                'login_url' => $credentials['admin_url'],
                'support_email' => EmailService::getContextEmail('support')
            ]
        );

        $this->assertTrue($result);
        
        // Verify template exists
        $template = EmailTemplate::where('template_key', 'store_welcome')->first();
        $this->assertNotNull($template);
        $this->assertEquals('store_management', $template->context);
    }

    /** @test */
    public function store_management_context_uses_correct_email_address()
    {
        $contextEmail = EmailService::getContextEmail('store_management');
        
        // Should use the configured email or default
        $expectedEmail = EmailSetting::where('context', 'store_management')->first()->email ?? 'no-responder@linkiudev.co';
        
        $this->assertEquals($expectedEmail, $contextEmail);
    }
}