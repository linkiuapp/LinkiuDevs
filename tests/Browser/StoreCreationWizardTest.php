<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Shared\Models\User;
use App\Shared\Models\Plan;
use App\Shared\Models\Store;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Store Creation Wizard End-to-End Tests
 * 
 * Browser tests for complete store creation workflow including
 * user interactions, form validation, and error scenarios
 * Requirements: All requirements validation
 */
class StoreCreationWizardTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $superAdmin;
    protected $basicPlan;
    protected $enterprisePlan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@superlinkiu.com',
            'password' => bcrypt('password')
        ]);
        
        $this->basicPlan = Plan::factory()->create([
            'name' => 'Basic Plan',
            'allow_custom_slug' => false,
            'price' => 50000
        ]);
        
        $this->enterprisePlan = Plan::factory()->create([
            'name' => 'Enterprise Plan',
            'allow_custom_slug' => true,
            'price' => 200000
        ]);
    }

    /** @test */
    public function user_can_complete_basic_store_creation_workflow()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->assertSee('Crear Nueva Tienda')
                    ->assertSee('Selecciona una Plantilla');

            // Step 1: Template Selection
            $browser->click('[data-template="basic"]')
                    ->assertSee('Tienda Básica')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Step 2: Owner Information
            $browser->assertSee('Información del Propietario')
                    ->type('[name="owner_name"]', 'John Doe')
                    ->type('[name="admin_email"]', 'john@example.com')
                    ->type('[name="admin_password"]', 'password123')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->type('[name="owner_department"]', 'Cundinamarca')
                    ->type('[name="owner_city"]', 'Bogotá');

            // Wait for email validation
            $browser->waitUntilMissing('.validation-loading', 10)
                    ->assertDontSee('Este email ya está registrado');

            $browser->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5);

            // Step 3: Store Configuration
            $browser->assertSee('Configuración de Tienda')
                    ->type('[name="name"]', 'My Test Store')
                    ->select('[name="plan_id"]', $this->basicPlan->id);

            // Slug should be auto-generated for basic plan
            $browser->assertInputValue('[name="slug"]', 'my-test-store')
                    ->assertAttribute('[name="slug"]', 'readonly', 'readonly');

            $browser->click('[data-action="next-step"]')
                    ->waitFor('[data-step="review"]', 5);

            // Step 4: Review and Confirmation
            $browser->assertSee('Revisar y Confirmar')
                    ->assertSee('John Doe')
                    ->assertSee('john@example.com')
                    ->assertSee('My Test Store')
                    ->assertSee('Basic Plan');

            // Submit form
            $browser->click('[data-action="create-store"]')
                    ->waitFor('.success-modal', 10)
                    ->assertSee('¡Tienda Creada Exitosamente!')
                    ->assertSee('john@example.com')
                    ->assertSee('password123');

            // Verify store was created
            $this->assertDatabaseHas('stores', [
                'name' => 'My Test Store',
                'plan_id' => $this->basicPlan->id
            ]);

            $this->assertDatabaseHas('users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'store_admin'
            ]);
        });
    }

    /** @test */
    public function user_can_complete_enterprise_store_creation_workflow()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Step 1: Template Selection
            $browser->click('[data-template="enterprise"]')
                    ->assertSee('Tienda Empresarial')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Step 2: Owner Information
            $browser->type('[name="owner_name"]', 'Jane Smith')
                    ->type('[name="admin_email"]', 'jane@enterprise.com')
                    ->select('[name="owner_document_type"]', 'cedula')
                    ->type('[name="owner_document_number"]', '12345678')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->type('[name="owner_department"]', 'Antioquia')
                    ->type('[name="owner_city"]', 'Medellín')
                    ->type('[name="admin_password"]', 'enterprise123');

            $browser->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5);

            // Step 3: Store Configuration
            $browser->type('[name="name"]', 'Enterprise Store')
                    ->type('[name="slug"]', 'enterprise-store')
                    ->select('[name="plan_id"]', $this->enterprisePlan->id)
                    ->type('[name="email"]', 'contact@enterprise.com')
                    ->type('[name="phone"]', '+57 300 123 4567')
                    ->type('[name="description"]', 'A professional enterprise store');

            // Wait for slug validation
            $browser->waitUntilMissing('.validation-loading', 10)
                    ->assertSee('✓'); // Validation success indicator

            $browser->click('[data-action="next-step"]')
                    ->waitFor('[data-step="fiscal-info"]', 5);

            // Step 4: Fiscal Information
            $browser->assertSee('Información Fiscal')
                    ->select('[name="fiscal_document_type"]', 'nit')
                    ->type('[name="fiscal_document_number"]', '900123456-7')
                    ->type('[name="fiscal_address"]', 'Calle 123 #45-67, Medellín')
                    ->select('[name="tax_regime"]', 'comun');

            $browser->click('[data-action="next-step"]')
                    ->waitFor('[data-step="seo-config"]', 5);

            // Step 5: SEO Configuration (Optional)
            $browser->assertSee('Configuración SEO')
                    ->type('[name="meta_title"]', 'Enterprise Store - Best Products')
                    ->type('[name="meta_description"]', 'The best enterprise store with quality products')
                    ->type('[name="meta_keywords"]', 'enterprise, store, quality');

            $browser->click('[data-action="next-step"]')
                    ->waitFor('[data-step="review"]', 5);

            // Step 6: Review and Confirmation
            $browser->assertSee('Revisar y Confirmar')
                    ->assertSee('Jane Smith')
                    ->assertSee('Enterprise Store')
                    ->assertSee('enterprise-store')
                    ->assertSee('900123456-7');

            // Submit form
            $browser->click('[data-action="create-store"]')
                    ->waitFor('.success-modal', 10)
                    ->assertSee('¡Tienda Creada Exitosamente!');

            // Verify comprehensive store creation
            $this->assertDatabaseHas('stores', [
                'name' => 'Enterprise Store',
                'slug' => 'enterprise-store',
                'email' => 'contact@enterprise.com',
                'document_type' => 'nit',
                'document_number' => '900123456-7',
                'meta_title' => 'Enterprise Store - Best Products'
            ]);
        });
    }

    /** @test */
    public function wizard_shows_real_time_validation_errors()
    {
        $this->browse(function (Browser $browser) {
            // Create existing user to test email validation
            User::factory()->create(['email' => 'existing@example.com']);
            
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Test email validation
            $browser->type('[name="admin_email"]', 'existing@example.com')
                    ->waitFor('.validation-error', 10)
                    ->assertSee('Este email ya está registrado');

            // Test email correction
            $browser->clear('[name="admin_email"]')
                    ->type('[name="admin_email"]', 'new@example.com')
                    ->waitUntilMissing('.validation-error', 10)
                    ->assertSee('✓');

            // Move to store configuration
            $browser->type('[name="owner_name"]', 'Test User')
                    ->type('[name="admin_password"]', 'password123')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->type('[name="owner_department"]', 'Cundinamarca')
                    ->type('[name="owner_city"]', 'Bogotá')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5);

            // Test slug validation with existing store
            Store::factory()->create(['slug' => 'existing-store']);
            
            $browser->type('[name="name"]', 'Existing Store')
                    ->waitFor('.slug-suggestions', 10)
                    ->assertSee('existing-store-1'); // Should show suggestions

            // Click on suggestion
            $browser->click('[data-suggestion="existing-store-1"]')
                    ->assertInputValue('[name="slug"]', 'existing-store-1');
        });
    }

    /** @test */
    public function wizard_handles_navigation_between_steps()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="complete"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Fill owner information
            $browser->type('[name="owner_name"]', 'Navigation Test')
                    ->type('[name="admin_email"]', 'nav@example.com')
                    ->type('[name="admin_password"]', 'password123')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5);

            // Fill store configuration
            $browser->type('[name="name"]', 'Navigation Store')
                    ->select('[name="plan_id"]', $this->basicPlan->id)
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="seo-config"]', 5);

            // Navigate back to previous step
            $browser->click('[data-step-nav="store-config"]')
                    ->waitFor('[data-step="store-config"]', 5)
                    ->assertInputValue('[name="name"]', 'Navigation Store');

            // Navigate back to first step
            $browser->click('[data-step-nav="owner-info"]')
                    ->waitFor('[data-step="owner-info"]', 5)
                    ->assertInputValue('[name="owner_name"]', 'Navigation Test');

            // Navigate forward again
            $browser->click('[data-step-nav="store-config"]')
                    ->waitFor('[data-step="store-config"]', 5)
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="seo-config"]', 5);

            // Verify progress indicator
            $browser->assertSeeIn('.progress-bar', '50%'); // Approximate progress
        });
    }

    /** @test */
    public function wizard_auto_saves_draft_data()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Fill some data
            $browser->type('[name="owner_name"]', 'Auto Save Test')
                    ->type('[name="admin_email"]', 'autosave@example.com');

            // Wait for auto-save (should happen after 30 seconds or on field blur)
            $browser->click('body') // Trigger blur
                    ->waitFor('.save-indicator', 10)
                    ->assertSee('Guardado automáticamente');

            // Refresh page to simulate session interruption
            $browser->refresh()
                    ->waitFor('.draft-recovery-modal', 10)
                    ->assertSee('Borrador Encontrado')
                    ->assertSee('Auto Save Test')
                    ->click('[data-action="restore-draft"]')
                    ->waitFor('[data-step="owner-info"]', 5)
                    ->assertInputValue('[name="owner_name"]', 'Auto Save Test')
                    ->assertInputValue('[name="admin_email"]', 'autosave@example.com');
        });
    }

    /** @test */
    public function wizard_handles_network_errors_gracefully()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Simulate network error by blocking API calls
            $browser->script('
                window.originalFetch = window.fetch;
                window.fetch = function() {
                    return Promise.reject(new Error("Network error"));
                };
            ');

            // Try to validate email (should show error)
            $browser->type('[name="admin_email"]', 'network@example.com')
                    ->waitFor('.network-error', 10)
                    ->assertSee('Error de conexión');

            // Restore network
            $browser->script('window.fetch = window.originalFetch;');

            // Should retry and succeed
            $browser->click('[data-action="retry-validation"]')
                    ->waitUntilMissing('.network-error', 10)
                    ->assertSee('✓');
        });
    }

    /** @test */
    public function wizard_prevents_navigation_with_invalid_data()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Try to proceed without filling required fields
            $browser->click('[data-action="next-step"]')
                    ->assertSee('Por favor completa todos los campos requeridos')
                    ->assertPresent('[data-step="owner-info"]'); // Should stay on same step

            // Fill partial data
            $browser->type('[name="owner_name"]', 'Partial User')
                    ->click('[data-action="next-step"]')
                    ->assertSee('Email es requerido')
                    ->assertPresent('[data-step="owner-info"]');

            // Fill all required data
            $browser->type('[name="admin_email"]', 'complete@example.com')
                    ->type('[name="admin_password"]', 'password123')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->type('[name="owner_department"]', 'Cundinamarca')
                    ->type('[name="owner_city"]', 'Bogotá')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5); // Should proceed
        });
    }

    /** @test */
    public function wizard_shows_billing_calculation_in_real_time()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="complete"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Fill owner info and proceed
            $browser->type('[name="owner_name"]', 'Billing Test')
                    ->type('[name="admin_email"]', 'billing@example.com')
                    ->type('[name="admin_password"]', 'password123')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5);

            // Select plan and see billing calculation
            $browser->select('[name="plan_id"]', $this->enterprisePlan->id)
                    ->waitFor('.billing-summary', 5)
                    ->assertSee('$200,000'); // Base price

            // Change billing period
            $browser->select('[name="billing_period"]', 'quarterly')
                    ->waitFor('.billing-update', 5)
                    ->assertSee('$540,000'); // Quarterly price (approximate)

            // Apply discount code
            $browser->type('[name="discount_code"]', 'WELCOME10')
                    ->click('[data-action="apply-discount"]')
                    ->waitFor('.discount-applied', 5)
                    ->assertSee('10% descuento')
                    ->assertSee('$486,000'); // After discount and tax
        });
    }

    /** @test */
    public function wizard_supports_keyboard_navigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Use Tab to navigate to template selection
            $browser->keys('body', ['{tab}', '{tab}', '{enter}']) // Navigate to and select basic template
                    ->waitFor('[data-step="owner-info"]', 5);

            // Use Tab to navigate through form fields
            $browser->keys('body', ['{tab}']) // Focus first field
                    ->type('[name="owner_name"]', 'Keyboard User')
                    ->keys('[name="owner_name"]', ['{tab}']) // Move to next field
                    ->type('[name="admin_email"]', 'keyboard@example.com')
                    ->keys('[name="admin_email"]', ['{tab}'])
                    ->type('[name="admin_password"]', 'password123');

            // Use Enter to proceed to next step
            $browser->keys('[data-action="next-step"]', ['{enter}'])
                    ->waitFor('[data-step="store-config"]', 5);

            // Verify accessibility attributes
            $browser->assertAttribute('[data-action="next-step"]', 'aria-label')
                    ->assertAttribute('.progress-bar', 'role', 'progressbar')
                    ->assertAttribute('.progress-bar', 'aria-valuenow');
        });
    }

    /** @test */
    public function wizard_displays_helpful_tooltips_and_guidance()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="enterprise"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Test tooltip on document type field
            $browser->mouseover('[data-tooltip="document-type"]')
                    ->waitFor('.tooltip', 2)
                    ->assertSee('Selecciona el tipo de documento');

            // Test help text for password field
            $browser->click('[name="admin_password"]')
                    ->assertSee('Mínimo 8 caracteres');

            // Move to fiscal information step
            $browser->type('[name="owner_name"]', 'Tooltip Test')
                    ->type('[name="admin_email"]', 'tooltip@example.com')
                    ->select('[name="owner_document_type"]', 'cedula')
                    ->type('[name="owner_document_number"]', '12345678')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->type('[name="owner_department"]', 'Cundinamarca')
                    ->type('[name="owner_city"]', 'Bogotá')
                    ->type('[name="admin_password"]', 'password123')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5)
                    ->type('[name="name"]', 'Tooltip Store')
                    ->select('[name="plan_id"]', $this->enterprisePlan->id)
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="fiscal-info"]', 5);

            // Test fiscal information tooltips
            $browser->mouseover('[data-tooltip="nit-format"]')
                    ->waitFor('.tooltip', 2)
                    ->assertSee('Formato: 900123456-7');

            // Test inline help for tax regime
            $browser->click('[data-help="tax-regime"]')
                    ->waitFor('.help-panel', 2)
                    ->assertSee('Régimen Común')
                    ->assertSee('Régimen Simplificado');
        });
    }

    /** @test */
    public function wizard_handles_browser_back_button_correctly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Fill data and proceed
            $browser->type('[name="owner_name"]', 'Back Button Test')
                    ->type('[name="admin_email"]', 'back@example.com')
                    ->type('[name="admin_password"]', 'password123')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->type('[name="owner_department"]', 'Cundinamarca')
                    ->type('[name="owner_city"]', 'Bogotá')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5);

            // Use browser back button
            $browser->back()
                    ->waitFor('[data-step="owner-info"]', 5)
                    ->assertInputValue('[name="owner_name"]', 'Back Button Test'); // Data should be preserved

            // Use browser forward button
            $browser->forward()
                    ->waitFor('[data-step="store-config"]', 5);

            // Verify URL reflects current step
            $browser->assertPathIs('/superlinkiu/stores/create-wizard')
                    ->assertFragmentIs('#step-store-config');
        });
    }
}