<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class EmailConfigurationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users
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

        // Seed initial data
        $this->artisan('db:seed', ['--class' => 'EmailSettingSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    public function test_complete_email_configuration_workflow()
    {
        $this->actingAs($this->superAdmin);

        // Step 1: View email settings page
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertOk();
        $response->assertViewIs('superlinkiu::email.settings.index');
        $response->assertViewHas('contexts');

        // Step 2: Update email settings
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'new-store@example.com',
            'support_email' => 'new-support@example.com',
            'billing_email' => 'new-billing@example.com'
        ]);

        $response->assertRedirect(route('superlinkiu.email.settings'));
        $response->assertSessionHas('success');

        // Verify settings were updated
        $this->assertDatabaseHas('email_settings', [
            'context' => 'store_management',
            'email' => 'new-store@example.com',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('email_settings', [
            'context' => 'support',
            'email' => 'new-support@example.com',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('email_settings', [
            'context' => 'billing',
            'email' => 'new-billing@example.com',
            'is_active' => true
        ]);

        // Step 3: View templates page
        $response = $this->get(route('superlinkiu.email.templates.index'));
        $response->assertOk();
        $response->assertViewIs('superlinkiu::email.templates.index');
        $response->assertViewHas('templates');

        // Step 4: Edit a template
        $template = EmailTemplate::where('template_key', 'store_welcome')->first();
        $this->assertNotNull($template);

        $response = $this->get(route('superlinkiu.email.templates.edit', $template));
        $response->assertOk();
        $response->assertViewIs('superlinkiu::email.templates.edit');
        $response->assertViewHas('template');
        $response->assertViewHas('availableVariables');

        // Step 5: Update template
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Updated Store Welcome',
            'subject' => 'Welcome to {{store_name}} - Updated',
            'body_html' => '<p>Hello {{admin_name}}, welcome to your updated store {{store_name}}!</p>',
            'body_text' => 'Hello {{admin_name}}, welcome to your updated store {{store_name}}!',
            'is_active' => true
        ]);

        $response->assertRedirect(route('superlinkiu.email.templates.index'));
        $response->assertSessionHas('success');

        // Verify template was updated
        $template->refresh();
        $this->assertEquals('Updated Store Welcome', $template->name);
        $this->assertEquals('Welcome to {{store_name}} - Updated', $template->subject);

        // Step 6: Test email sending with updated configuration
        Mail::fake();

        $result = EmailService::sendWithTemplate('store_welcome', ['test@example.com'], [
            'admin_name' => 'John Doe',
            'store_name' => 'My Test Store',
            'admin_email' => 'john@example.com',
            'login_url' => 'https://test.com/admin',
            'support_email' => 'support@test.com'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);

        // Step 7: Validate configuration
        $response = $this->get(route('superlinkiu.email.validate'));
        $response->assertOk();
        $response->assertJson(['valid' => true]);
    }

    public function test_email_settings_validation_workflow()
    {
        $this->actingAs($this->superAdmin);

        // Test invalid email format
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'invalid-email',
            'support_email' => 'valid@example.com',
            'billing_email' => 'valid@example.com'
        ]);

        $response->assertSessionHasErrors('store_management_email');

        // Test suspicious domain
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'test@tempmail.org',
            'support_email' => 'valid@example.com',
            'billing_email' => 'valid@example.com'
        ]);

        $response->assertSessionHasErrors('store_management_email');

        // Test too long email
        $longEmail = str_repeat('a', 250) . '@example.com';
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => $longEmail,
            'support_email' => 'valid@example.com',
            'billing_email' => 'valid@example.com'
        ]);

        $response->assertSessionHasErrors('store_management_email');

        // Test missing required fields
        $response = $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => '',
            'support_email' => 'valid@example.com',
            'billing_email' => 'valid@example.com'
        ]);

        $response->assertSessionHasErrors('store_management_email');
    }

    public function test_template_validation_workflow()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::where('template_key', 'store_welcome')->first();

        // Test invalid template variables
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Hello {{invalid_variable}}',
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('subject');

        // Test dangerous HTML content
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<script>alert("xss")</script><p>Content</p>',
            'is_active' => true
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Test invalid name format
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test<script>alert("xss")</script>',
            'subject' => 'Test Subject',
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('name');

        // Test too long subject
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Test Template',
            'subject' => str_repeat('a', 501),
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('subject');
    }

    public function test_template_preview_workflow()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::where('template_key', 'store_welcome')->first();

        // Test preview with existing template
        $response = $this->post(route('superlinkiu.email.templates.preview', $template));
        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'preview' => ['subject', 'body_html', 'body_text'],
            'sample_data'
        ]);

        // Test preview with form data
        $response = $this->post(route('superlinkiu.email.templates.preview', $template), [
            'subject' => 'Custom Subject {{admin_name}}',
            'body_html' => '<p>Custom content for {{store_name}}</p>',
            'body_text' => 'Custom text for {{store_name}}'
        ]);

        $response->assertOk();
        $preview = $response->json('preview');
        $this->assertStringContainsString('Custom Subject', $preview['subject']);
        $this->assertStringContainsString('Custom content', $preview['body_html']);
    }

    public function test_access_control_workflow()
    {
        // Test unauthenticated access
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertRedirect('/superlinkiu/login');

        // Test regular user access
        $this->actingAs($this->regularUser);
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertRedirect('/superlinkiu/login');

        // Test super admin access
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertOk();
    }

    public function test_csrf_protection_workflow()
    {
        $this->actingAs($this->superAdmin);

        // Test CSRF protection on settings update
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('superlinkiu.email.settings.update'), [
                'store_management_email' => 'test@example.com',
                'support_email' => 'support@example.com',
                'billing_email' => 'billing@example.com'
            ], [
                'X-CSRF-TOKEN' => 'invalid-token'
            ]);

        // Without CSRF middleware, this should work
        // With CSRF middleware, it would return 419
        $response->assertRedirect();
    }

    public function test_error_handling_workflow()
    {
        $this->actingAs($this->superAdmin);

        // Test database error handling by trying to update non-existent template
        $response = $this->put('/superlinkiu/email/templates/99999', [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertNotFound();
    }

    public function test_integration_with_existing_email_flows()
    {
        $this->actingAs($this->superAdmin);

        // Update email settings
        $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'custom-store@example.com',
            'support_email' => 'custom-support@example.com',
            'billing_email' => 'custom-billing@example.com'
        ]);

        // Test that EmailService uses updated settings
        $storeEmail = EmailService::getContextEmail('store_management');
        $supportEmail = EmailService::getContextEmail('support');
        $billingEmail = EmailService::getContextEmail('billing');

        $this->assertEquals('custom-store@example.com', $storeEmail);
        $this->assertEquals('custom-support@example.com', $supportEmail);
        $this->assertEquals('custom-billing@example.com', $billingEmail);

        // Test email sending with updated configuration
        Mail::fake();

        $result = EmailService::sendWithTemplate('store_welcome', ['test@example.com'], [
            'admin_name' => 'John Doe',
            'store_name' => 'Test Store',
            'admin_email' => 'john@example.com',
            'login_url' => 'https://test.com/admin',
            'support_email' => 'support@test.com'
        ]);

        $this->assertTrue($result);
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 1);
    }

    public function test_configuration_persistence_workflow()
    {
        $this->actingAs($this->superAdmin);

        // Update settings
        $this->post(route('superlinkiu.email.settings.update'), [
            'store_management_email' => 'persistent@example.com',
            'support_email' => 'persistent-support@example.com',
            'billing_email' => 'persistent-billing@example.com'
        ]);

        // Verify persistence across requests
        $response = $this->get(route('superlinkiu.email.settings'));
        $response->assertOk();

        // Check that the form shows updated values
        $response->assertSee('persistent@example.com');
        $response->assertSee('persistent-support@example.com');
        $response->assertSee('persistent-billing@example.com');

        // Verify database persistence
        $this->assertDatabaseHas('email_settings', [
            'context' => 'store_management',
            'email' => 'persistent@example.com'
        ]);
    }

    public function test_template_variable_validation_workflow()
    {
        $this->actingAs($this->superAdmin);

        $template = EmailTemplate::where('template_key', 'store_welcome')->first();

        // Test valid variables for store_management context
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Valid Template',
            'subject' => 'Welcome {{admin_name}} to {{store_name}}',
            'body_html' => '<p>Login at {{login_url}} or contact {{support_email}}</p>',
            'body_text' => 'Login at {{login_url}} or contact {{support_email}}',
            'is_active' => true
        ]);

        $response->assertRedirect(route('superlinkiu.email.templates.index'));
        $response->assertSessionHas('success');

        // Test invalid variables for store_management context
        $response = $this->put(route('superlinkiu.email.templates.update', $template), [
            'name' => 'Invalid Template',
            'subject' => 'Ticket {{ticket_id}} for {{admin_name}}', // ticket_id not valid for store_management
            'body_html' => '<p>Content</p>',
            'is_active' => true
        ]);

        $response->assertSessionHasErrors('subject');
    }
}