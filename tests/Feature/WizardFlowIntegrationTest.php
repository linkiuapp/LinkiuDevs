<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Shared\Models\User;
use App\Shared\Models\Plan;
use App\Shared\Models\Store;
use App\Models\StoreDraft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;

/**
 * Wizard Flow Integration Tests
 * 
 * Tests for complete wizard flow integration including template selection,
 * form generation, validation, and draft management
 * Requirements: All requirements validation
 */
class WizardFlowIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $basicPlan;
    protected $enterprisePlan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@superlinkiu.com'
        ]);
        
        // Create test plans
        $this->basicPlan = Plan::factory()->create([
            'name' => 'Basic Plan',
            'allow_custom_slug' => false,
            'price' => 50000,
            'monthly_price' => 50000
        ]);
        
        $this->enterprisePlan = Plan::factory()->create([
            'name' => 'Enterprise Plan',
            'allow_custom_slug' => true,
            'price' => 200000,
            'monthly_price' => 200000
        ]);
    }

    /** @test */
    public function it_can_load_wizard_with_templates_and_plans()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->get('/superlinkiu/stores/create-wizard');
        
        $response->assertStatus(200)
                ->assertViewIs('superlinkiu::stores.create-wizard')
                ->assertViewHas('plans')
                ->assertViewHas('templates');
        
        // Check that templates are passed to view
        $templates = $response->viewData('templates');
        $this->assertNotEmpty($templates);
        $this->assertArrayHasKey('basic', $templates->keyBy('id')->toArray());
        $this->assertArrayHasKey('complete', $templates->keyBy('id')->toArray());
        $this->assertArrayHasKey('enterprise', $templates->keyBy('id')->toArray());
    }

    /** @test */
    public function it_can_get_template_configurations()
    {
        $this->actingAs($this->superAdmin);
        
        // Test basic template
        $response = $this->getJson('/superlinkiu/api/templates/basic/config');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'steps',
                        'capabilities',
                        'defaultValues',
                        'validationRules',
                        'fieldMapping'
                    ]
                ]);
        
        $config = $response->json('data');
        $this->assertEquals('basic', $config['id']);
        $this->assertEquals('Tienda Básica', $config['name']);
        $this->assertIsArray($config['steps']);
        $this->assertContains('basicInfo', $config['capabilities']);
    }

    /** @test */
    public function it_can_get_template_validation_rules()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/enterprise/validation-rules');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data'
                ]);
        
        $rules = $response->json('data');
        
        // Should have base rules
        $this->assertArrayHasKey('owner_name', $rules);
        $this->assertArrayHasKey('admin_email', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('slug', $rules);
        
        // Should have enterprise-specific rules
        $this->assertArrayHasKey('fiscal_document_type', $rules);
        $this->assertArrayHasKey('fiscal_address', $rules);
    }

    /** @test */
    public function it_can_get_template_field_mapping()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/complete/field-mapping');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
        
        $mapping = $response->json('data');
        
        $this->assertArrayHasKey('owner_name', $mapping);
        $this->assertArrayHasKey('admin_email', $mapping);
        $this->assertArrayHasKey('name', $mapping);
        
        // Check field structure
        $ownerNameField = $mapping['owner_name'];
        $this->assertArrayHasKey('step', $ownerNameField);
        $this->assertArrayHasKey('label', $ownerNameField);
        $this->assertArrayHasKey('type', $ownerNameField);
        $this->assertArrayHasKey('required', $ownerNameField);
    }

    /** @test */
    public function it_can_validate_email_in_real_time()
    {
        $this->actingAs($this->superAdmin);
        
        // Create existing user
        User::factory()->create(['email' => 'existing@example.com']);
        
        // Test existing email
        $response = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'existing@example.com'
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'is_valid' => false,
                        'field' => 'email'
                    ]
                ]);
        
        // Test new email
        $response = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'new@example.com'
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'is_valid' => true,
                        'field' => 'email'
                    ]
                ]);
    }

    /** @test */
    public function it_can_validate_slug_availability_and_get_suggestions()
    {
        $this->actingAs($this->superAdmin);
        
        // Create existing store
        Store::factory()->create(['slug' => 'existing-store']);
        
        // Test existing slug
        $response = $this->postJson('/superlinkiu/api/stores/validate-slug', [
            'slug' => 'existing-store'
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'is_valid' => false,
                        'field' => 'slug'
                    ]
                ]);
        
        // Get suggestions for taken slug
        $response = $this->postJson('/superlinkiu/api/stores/suggest-slug', [
            'slug' => 'existing-store'
        ]);
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'suggestions',
                        'base_slug'
                    ]
                ]);
        
        $suggestions = $response->json('data.suggestions');
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        
        // Suggestions should be different from original
        foreach ($suggestions as $suggestion) {
            $this->assertNotEquals('existing-store', $suggestion);
        }
    }

    /** @test */
    public function it_can_calculate_billing_for_different_plans_and_periods()
    {
        $this->actingAs($this->superAdmin);
        
        // Test monthly billing
        $response = $this->postJson('/superlinkiu/api/stores/calculate-billing', [
            'plan_id' => $this->basicPlan->id,
            'billing_period' => 'monthly'
        ]);
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'plan' => ['id', 'name'],
                        'billing' => [
                            'period',
                            'base_amount',
                            'tax',
                            'total',
                            'currency',
                            'next_billing_date'
                        ]
                    ]
                ]);
        
        $billing = $response->json('data.billing');
        $this->assertEquals('monthly', $billing['period']);
        $this->assertEquals(50000, $billing['base_amount']);
        $this->assertEquals('COP', $billing['currency']);
        
        // Test with discount
        $response = $this->postJson('/superlinkiu/api/stores/calculate-billing', [
            'plan_id' => $this->basicPlan->id,
            'billing_period' => 'monthly',
            'discount_code' => 'WELCOME10'
        ]);
        
        $response->assertStatus(200);
        $billing = $response->json('data.billing');
        $this->assertEquals('WELCOME10', $billing['discount_code']);
        $this->assertEquals(10, $billing['discount_percentage']);
        $this->assertGreaterThan(0, $billing['discount_amount']);
    }

    /** @test */
    public function it_can_save_and_recover_draft_data()
    {
        $this->actingAs($this->superAdmin);
        
        $draftData = [
            'template' => 'basic',
            'current_step' => 2,
            'form_data' => [
                'owner_name' => 'John Doe',
                'admin_email' => 'john@example.com',
                'name' => 'Test Store Draft'
            ]
        ];
        
        // Save draft
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
        
        // Verify draft was saved
        $this->assertDatabaseHas('store_drafts', [
            'user_id' => $this->superAdmin->id,
            'template' => 'basic',
            'current_step' => 2
        ]);
        
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        $this->assertNotNull($draft);
        
        $formData = $draft->form_data;
        $this->assertEquals('John Doe', $formData['owner_name']);
        $this->assertEquals('john@example.com', $formData['admin_email']);
        $this->assertEquals('Test Store Draft', $formData['name']);
    }

    /** @test */
    public function it_can_complete_full_wizard_flow_with_basic_template()
    {
        $this->actingAs($this->superAdmin);
        
        $storeData = [
            // Template selection (step 1)
            'template' => 'basic',
            
            // Owner information (step 2)
            'owner_name' => 'John Doe',
            'admin_email' => 'john@example.com',
            'admin_password' => 'password123',
            'owner_country' => 'Colombia',
            'owner_department' => 'Cundinamarca',
            'owner_city' => 'Bogotá',
            
            // Store configuration (step 3)
            'name' => 'Test Store Basic',
            'plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'billing_period' => 'monthly',
            'initial_payment_status' => 'pending'
        ];
        
        // Submit complete form
        $response = $this->post('/superlinkiu/stores', $storeData);
        
        $response->assertRedirect('/superlinkiu/stores')
                ->assertSessionHas('success')
                ->assertSessionHas('admin_credentials');
        
        // Verify store was created
        $this->assertDatabaseHas('stores', [
            'name' => 'Test Store Basic',
            'plan_id' => $this->basicPlan->id,
            'status' => 'active'
        ]);
        
        // Verify admin user was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'store_admin'
        ]);
        
        // Verify admin is linked to store
        $store = Store::where('name', 'Test Store Basic')->first();
        $admin = User::where('email', 'john@example.com')->first();
        $this->assertEquals($store->id, $admin->store_id);
    }

    /** @test */
    public function it_can_complete_full_wizard_flow_with_enterprise_template()
    {
        $this->actingAs($this->superAdmin);
        
        $storeData = [
            // Template selection
            'template' => 'enterprise',
            
            // Owner information
            'owner_name' => 'Jane Smith',
            'admin_email' => 'jane@enterprise.com',
            'owner_document_type' => 'cedula',
            'owner_document_number' => '12345678',
            'owner_country' => 'Colombia',
            'owner_department' => 'Antioquia',
            'owner_city' => 'Medellín',
            'admin_password' => 'enterprise123',
            
            // Store configuration
            'name' => 'Enterprise Store',
            'slug' => 'enterprise-store',
            'plan_id' => $this->enterprisePlan->id,
            'email' => 'contact@enterprise.com',
            'phone' => '+57 300 123 4567',
            'description' => 'Enterprise level store',
            
            // Fiscal information
            'document_type' => 'nit',
            'document_number' => '900123456-7',
            'country' => 'Colombia',
            'department' => 'Antioquia',
            'city' => 'Medellín',
            'address' => 'Calle 123 #45-67',
            
            // SEO configuration
            'meta_title' => 'Enterprise Store - Best Products',
            'meta_description' => 'The best enterprise store with quality products',
            'meta_keywords' => 'enterprise, store, quality, products',
            
            // Billing
            'status' => 'active',
            'billing_period' => 'quarterly',
            'initial_payment_status' => 'paid'
        ];
        
        $response = $this->post('/superlinkiu/stores', $storeData);
        
        $response->assertRedirect('/superlinkiu/stores')
                ->assertSessionHas('success');
        
        // Verify store with all data
        $this->assertDatabaseHas('stores', [
            'name' => 'Enterprise Store',
            'slug' => 'enterprise-store',
            'email' => 'contact@enterprise.com',
            'phone' => '+57 300 123 4567',
            'document_type' => 'nit',
            'document_number' => '900123456-7',
            'meta_title' => 'Enterprise Store - Best Products'
        ]);
    }

    /** @test */
    public function it_handles_validation_errors_during_wizard_flow()
    {
        $this->actingAs($this->superAdmin);
        
        // Create existing user to cause email conflict
        User::factory()->create(['email' => 'existing@example.com']);
        
        $invalidData = [
            'owner_name' => '', // Required field missing
            'admin_email' => 'existing@example.com', // Duplicate email
            'name' => '', // Required field missing
            'slug' => 'invalid slug!', // Invalid format
            'plan_id' => 99999 // Non-existent plan
        ];
        
        $response = $this->post('/superlinkiu/stores', $invalidData);
        
        $response->assertSessionHasErrors([
            'owner_name',
            'admin_email',
            'name',
            'slug',
            'plan_id'
        ]);
        
        // Verify no store was created
        $this->assertDatabaseMissing('stores', [
            'name' => ''
        ]);
    }

    /** @test */
    public function it_can_handle_concurrent_slug_validation()
    {
        $this->actingAs($this->superAdmin);
        
        // Simulate concurrent requests for same slug
        $slug = 'concurrent-test-store';
        
        // First request should pass
        $response1 = $this->postJson('/superlinkiu/api/stores/validate-slug', [
            'slug' => $slug
        ]);
        
        $response1->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => ['is_valid' => true]
                 ]);
        
        // Create store with that slug
        Store::factory()->create(['slug' => $slug]);
        
        // Second request should fail
        $response2 = $this->postJson('/superlinkiu/api/stores/validate-slug', [
            'slug' => $slug
        ]);
        
        $response2->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => ['is_valid' => false]
                 ]);
    }

    /** @test */
    public function it_can_get_templates_by_capability()
    {
        $this->actingAs($this->superAdmin);
        
        // Test basic capability
        $response = $this->getJson('/superlinkiu/api/templates/capability/basicInfo');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
        
        $templates = $response->json('data');
        $this->assertNotEmpty($templates);
        
        // All returned templates should have basicInfo capability
        foreach ($templates as $template) {
            $this->assertContains('basicInfo', $template['capabilities']);
        }
        
        // Test fiscal capability
        $response = $this->getJson('/superlinkiu/api/templates/capability/fiscalInfo');
        
        $response->assertStatus(200);
        $fiscalTemplates = $response->json('data');
        
        // Should not include basic template
        $templateIds = array_column($fiscalTemplates, 'id');
        $this->assertNotContains('basic', $templateIds);
        $this->assertContains('enterprise', $templateIds);
    }

    /** @test */
    public function it_can_search_locations_with_autocomplete()
    {
        $this->actingAs($this->superAdmin);
        
        // Test city search
        $response = $this->getJson('/superlinkiu/api/locations/search?query=bog&type=city');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'results',
                        'query',
                        'total'
                    ]
                ]);
        
        $results = $response->json('data.results');
        $this->assertIsArray($results);
        
        // Should find Bogotá
        $bogotaFound = false;
        foreach ($results as $result) {
            if (stripos($result['name'], 'bogot') !== false) {
                $bogotaFound = true;
                $this->assertEquals('city', $result['type']);
                break;
            }
        }
        $this->assertTrue($bogotaFound, 'Bogotá should be found in city search');
    }

    /** @test */
    public function it_can_get_validation_suggestions_for_errors()
    {
        $this->actingAs($this->superAdmin);
        
        // Test slug suggestions
        $response = $this->postJson('/superlinkiu/api/stores/validation-suggestions', [
            'field' => 'slug',
            'value' => 'taken-slug',
            'error_type' => 'taken'
        ]);
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'suggestions',
                        'field',
                        'error_type'
                    ]
                ]);
        
        $suggestions = $response->json('data.suggestions');
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        
        // Should have replacement type suggestions
        $hasReplacement = false;
        foreach ($suggestions as $suggestion) {
            if ($suggestion['type'] === 'replacement') {
                $hasReplacement = true;
                $this->assertArrayHasKey('value', $suggestion);
                $this->assertArrayHasKey('description', $suggestion);
                break;
            }
        }
        $this->assertTrue($hasReplacement);
    }

    /** @test */
    public function it_maintains_wizard_state_across_requests()
    {
        $this->actingAs($this->superAdmin);
        
        // Start wizard and save initial data
        $initialData = [
            'template' => 'complete',
            'current_step' => 1,
            'form_data' => [
                'owner_name' => 'Progressive User',
                'admin_email' => 'progressive@example.com'
            ]
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $initialData);
        $response->assertStatus(200);
        
        // Update with more data
        $updatedData = [
            'template' => 'complete',
            'current_step' => 2,
            'form_data' => [
                'owner_name' => 'Progressive User',
                'admin_email' => 'progressive@example.com',
                'name' => 'Progressive Store',
                'slug' => 'progressive-store'
            ]
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $updatedData);
        $response->assertStatus(200);
        
        // Verify final state
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        $this->assertEquals(2, $draft->current_step);
        $this->assertEquals('Progressive Store', $draft->form_data['name']);
        $this->assertEquals('progressive-store', $draft->form_data['slug']);
    }
}