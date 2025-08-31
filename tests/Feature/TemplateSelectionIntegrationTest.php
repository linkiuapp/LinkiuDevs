<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Shared\Models\User;
use App\Shared\Models\Plan;
use App\Features\SuperLinkiu\Services\StoreTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * Template Selection Integration Tests
 * 
 * Tests for template selection and dynamic form generation integration
 * Requirements: 3.1, 3.2, 3.4 - Template system and form generation
 */
class TemplateSelectionIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $templateService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@superlinkiu.com'
        ]);
        
        $this->templateService = app(StoreTemplateService::class);
    }

    /** @test */
    public function it_can_get_all_available_templates()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'icon',
                            'capabilities',
                            'fieldCount',
                            'estimatedTime',
                            'features'
                        ]
                    ]
                ]);
        
        $templates = $response->json('data');
        $this->assertCount(3, $templates); // basic, complete, enterprise
        
        // Verify each template has required fields
        foreach ($templates as $template) {
            $this->assertArrayHasKey('id', $template);
            $this->assertArrayHasKey('name', $template);
            $this->assertArrayHasKey('description', $template);
            $this->assertArrayHasKey('capabilities', $template);
            $this->assertIsArray($template['capabilities']);
        }
    }

    /** @test */
    public function it_can_get_basic_template_configuration()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/basic/config');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => 'basic',
                        'name' => 'Tienda Básica'
                    ]
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'steps' => [
                            '*' => [
                                'id',
                                'title',
                                'order',
                                'isOptional',
                                'fields' => [
                                    '*' => [
                                        'name',
                                        'label',
                                        'type',
                                        'required'
                                    ]
                                ]
                            ]
                        ],
                        'capabilities',
                        'defaultValues',
                        'validationRules',
                        'fieldMapping'
                    ]
                ]);
        
        $config = $response->json('data');
        
        // Verify basic template has correct capabilities
        $this->assertContains('basicInfo', $config['capabilities']);
        $this->assertNotContains('fiscalInfo', $config['capabilities']);
        
        // Verify steps are ordered correctly
        $steps = $config['steps'];
        $this->assertCount(2, $steps); // owner-info, store-config
        
        $previousOrder = 0;
        foreach ($steps as $step) {
            $this->assertGreaterThan($previousOrder, $step['order']);
            $previousOrder = $step['order'];
        }
        
        // Verify default values
        $defaults = $config['defaultValues'];
        $this->assertEquals('Colombia', $defaults['owner_country']);
        $this->assertEquals('active', $defaults['status']);
        $this->assertEquals('basic', $defaults['plan_type']);
    }

    /** @test */
    public function it_can_get_complete_template_configuration()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/complete/config');
        
        $response->assertStatus(200);
        
        $config = $response->json('data');
        
        // Verify complete template capabilities
        $this->assertContains('basicInfo', $config['capabilities']);
        $this->assertContains('fiscalInfo', $config['capabilities']);
        $this->assertContains('seoConfig', $config['capabilities']);
        
        // Should have more steps than basic
        $this->assertGreaterThan(2, count($config['steps']));
        
        // Should have SEO step
        $stepIds = array_column($config['steps'], 'id');
        $this->assertContains('seo-config', $stepIds);
        
        // Verify field mapping includes additional fields
        $fieldMapping = $config['fieldMapping'];
        $this->assertArrayHasKey('owner_document_type', $fieldMapping);
        $this->assertArrayHasKey('email', $fieldMapping);
        $this->assertArrayHasKey('meta_title', $fieldMapping);
    }

    /** @test */
    public function it_can_get_enterprise_template_configuration()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/enterprise/config');
        
        $response->assertStatus(200);
        
        $config = $response->json('data');
        
        // Verify enterprise template has all capabilities
        $expectedCapabilities = [
            'basicInfo',
            'fiscalInfo', 
            'seoConfig',
            'advancedConfig',
            'customDomain',
            'analytics'
        ];
        
        foreach ($expectedCapabilities as $capability) {
            $this->assertContains($capability, $config['capabilities']);
        }
        
        // Should have fiscal-info step
        $stepIds = array_column($config['steps'], 'id');
        $this->assertContains('fiscal-info', $stepIds);
        
        // Verify fiscal fields in mapping
        $fieldMapping = $config['fieldMapping'];
        $this->assertArrayHasKey('fiscal_document_type', $fieldMapping);
        $this->assertArrayHasKey('fiscal_document_number', $fieldMapping);
        $this->assertArrayHasKey('fiscal_address', $fieldMapping);
        $this->assertArrayHasKey('tax_regime', $fieldMapping);
        
        // Verify fiscal fields are required
        $this->assertTrue($fieldMapping['fiscal_document_type']['required']);
        $this->assertTrue($fieldMapping['fiscal_address']['required']);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_template()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/nonexistent/config');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => "Template 'nonexistent' not found"
                ]);
    }

    /** @test */
    public function it_can_get_templates_filtered_by_capability()
    {
        $this->actingAs($this->superAdmin);
        
        // Test basicInfo capability (should return all templates)
        $response = $this->getJson('/superlinkiu/api/templates/capability/basicInfo');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
        
        $basicInfoTemplates = $response->json('data');
        $this->assertCount(3, $basicInfoTemplates);
        
        // Test fiscalInfo capability (should exclude basic)
        $response = $this->getJson('/superlinkiu/api/templates/capability/fiscalInfo');
        
        $response->assertStatus(200);
        
        $fiscalTemplates = $response->json('data');
        $this->assertCount(2, $fiscalTemplates); // complete, enterprise
        
        $templateIds = array_column($fiscalTemplates, 'id');
        $this->assertNotContains('basic', $templateIds);
        $this->assertContains('complete', $templateIds);
        $this->assertContains('enterprise', $templateIds);
        
        // Test advancedConfig capability (should only return enterprise)
        $response = $this->getJson('/superlinkiu/api/templates/capability/advancedConfig');
        
        $response->assertStatus(200);
        
        $advancedTemplates = $response->json('data');
        $this->assertCount(1, $advancedTemplates);
        $this->assertEquals('enterprise', $advancedTemplates[0]['id']);
    }

    /** @test */
    public function it_can_get_template_validation_rules_with_correct_structure()
    {
        $this->actingAs($this->superAdmin);
        
        // Test basic template rules
        $response = $this->getJson('/superlinkiu/api/templates/basic/validation-rules');
        
        $response->assertStatus(200);
        
        $basicRules = $response->json('data');
        
        // Should have base rules only
        $expectedBaseRules = [
            'owner_name',
            'admin_email', 
            'admin_password',
            'name',
            'slug',
            'plan_id'
        ];
        
        foreach ($expectedBaseRules as $field) {
            $this->assertArrayHasKey($field, $basicRules);
        }
        
        // Should not have extended rules
        $this->assertArrayNotHasKey('owner_document_type', $basicRules);
        $this->assertArrayNotHasKey('fiscal_document_type', $basicRules);
        
        // Test enterprise template rules
        $response = $this->getJson('/superlinkiu/api/templates/enterprise/validation-rules');
        
        $response->assertStatus(200);
        
        $enterpriseRules = $response->json('data');
        
        // Should have base rules plus enterprise rules
        foreach ($expectedBaseRules as $field) {
            $this->assertArrayHasKey($field, $enterpriseRules);
        }
        
        // Should have enterprise-specific rules
        $expectedEnterpriseRules = [
            'owner_document_type',
            'owner_document_number',
            'fiscal_document_type',
            'fiscal_document_number',
            'fiscal_address',
            'tax_regime'
        ];
        
        foreach ($expectedEnterpriseRules as $field) {
            $this->assertArrayHasKey($field, $enterpriseRules);
        }
    }

    /** @test */
    public function it_can_get_template_field_mapping_with_step_information()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/complete/field-mapping');
        
        $response->assertStatus(200);
        
        $mapping = $response->json('data');
        
        // Verify field mapping structure
        foreach ($mapping as $fieldName => $fieldConfig) {
            $this->assertArrayHasKey('step', $fieldConfig);
            $this->assertArrayHasKey('label', $fieldConfig);
            $this->assertArrayHasKey('type', $fieldConfig);
            $this->assertArrayHasKey('required', $fieldConfig);
            
            // Verify step assignment
            $this->assertNotEmpty($fieldConfig['step']);
            
            // Verify field type is valid
            $validTypes = ['text', 'email', 'password', 'select', 'textarea', 'number', 'tel', 'url'];
            $this->assertContains($fieldConfig['type'], $validTypes);
        }
        
        // Verify specific field assignments
        $this->assertEquals('owner-info', $mapping['owner_name']['step']);
        $this->assertEquals('store-config', $mapping['name']['step']);
        $this->assertEquals('seo-config', $mapping['meta_title']['step']);
        
        // Verify field types
        $this->assertEquals('email', $mapping['admin_email']['type']);
        $this->assertEquals('password', $mapping['admin_password']['type']);
        $this->assertEquals('select', $mapping['owner_document_type']['type']);
        $this->assertEquals('textarea', $mapping['meta_description']['type']);
    }

    /** @test */
    public function it_generates_correct_form_structure_for_each_template()
    {
        $this->actingAs($this->superAdmin);
        
        $templates = ['basic', 'complete', 'enterprise'];
        
        foreach ($templates as $templateId) {
            $response = $this->getJson("/superlinkiu/api/templates/{$templateId}/config");
            
            $response->assertStatus(200);
            
            $config = $response->json('data');
            $steps = $config['steps'];
            
            // Verify steps are properly ordered
            $previousOrder = 0;
            foreach ($steps as $step) {
                $this->assertGreaterThan($previousOrder, $step['order']);
                $previousOrder = $step['order'];
                
                // Verify each step has fields
                $this->assertArrayHasKey('fields', $step);
                $this->assertNotEmpty($step['fields']);
                
                // Verify field structure
                foreach ($step['fields'] as $field) {
                    $this->assertArrayHasKey('name', $field);
                    $this->assertArrayHasKey('label', $field);
                    $this->assertArrayHasKey('type', $field);
                    $this->assertArrayHasKey('required', $field);
                    
                    // Name should not be empty
                    $this->assertNotEmpty($field['name']);
                    $this->assertNotEmpty($field['label']);
                }
            }
        }
    }

    /** @test */
    public function it_handles_template_service_errors_gracefully()
    {
        $this->actingAs($this->superAdmin);
        
        // Mock template service to throw exception
        $mockService = $this->createMock(StoreTemplateService::class);
        $mockService->method('getTemplateConfig')
                   ->willThrowException(new \Exception('Service error'));
        
        $this->app->instance(StoreTemplateService::class, $mockService);
        
        $response = $this->getJson('/superlinkiu/api/templates/basic/config');
        
        $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'Error al obtener la configuración de la plantilla'
                ]);
    }

    /** @test */
    public function it_validates_template_step_dependencies()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/enterprise/config');
        
        $response->assertStatus(200);
        
        $config = $response->json('data');
        $steps = $config['steps'];
        
        // Find fiscal-info step
        $fiscalStep = null;
        foreach ($steps as $step) {
            if ($step['id'] === 'fiscal-info') {
                $fiscalStep = $step;
                break;
            }
        }
        
        $this->assertNotNull($fiscalStep);
        $this->assertFalse($fiscalStep['isOptional']); // Should be required for enterprise
        
        // Find seo-config step
        $seoStep = null;
        foreach ($steps as $step) {
            if ($step['id'] === 'seo-config') {
                $seoStep = $step;
                break;
            }
        }
        
        $this->assertNotNull($seoStep);
        $this->assertTrue($seoStep['isOptional']); // Should be optional
    }

    /** @test */
    public function it_provides_correct_field_options_for_select_fields()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/templates/enterprise/field-mapping');
        
        $response->assertStatus(200);
        
        $mapping = $response->json('data');
        
        // Check document type field has options
        $documentTypeField = $mapping['owner_document_type'];
        $this->assertEquals('select', $documentTypeField['type']);
        $this->assertArrayHasKey('options', $documentTypeField);
        $this->assertNotNull($documentTypeField['options']);
        
        // Check fiscal document type field
        $fiscalDocumentTypeField = $mapping['fiscal_document_type'];
        $this->assertEquals('select', $fiscalDocumentTypeField['type']);
        $this->assertArrayHasKey('options', $fiscalDocumentTypeField);
        
        // Check tax regime field
        $taxRegimeField = $mapping['tax_regime'];
        $this->assertEquals('select', $taxRegimeField['type']);
        $this->assertArrayHasKey('options', $taxRegimeField);
    }

    /** @test */
    public function it_can_determine_template_capabilities_correctly()
    {
        $templates = $this->templateService->getAllTemplates();
        
        // Test basic template
        $basicTemplate = $templates->firstWhere('id', 'basic');
        $this->assertTrue($this->templateService->hasCapability('basic', 'basicInfo'));
        $this->assertFalse($this->templateService->hasCapability('basic', 'fiscalInfo'));
        $this->assertFalse($this->templateService->hasCapability('basic', 'advancedConfig'));
        
        // Test complete template
        $this->assertTrue($this->templateService->hasCapability('complete', 'basicInfo'));
        $this->assertTrue($this->templateService->hasCapability('complete', 'fiscalInfo'));
        $this->assertTrue($this->templateService->hasCapability('complete', 'seoConfig'));
        $this->assertFalse($this->templateService->hasCapability('complete', 'advancedConfig'));
        
        // Test enterprise template
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'basicInfo'));
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'fiscalInfo'));
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'seoConfig'));
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'advancedConfig'));
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'customDomain'));
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'analytics'));
    }

    /** @test */
    public function it_returns_recommended_template()
    {
        $recommended = $this->templateService->getRecommendedTemplate();
        
        $this->assertNotNull($recommended);
        $this->assertEquals('basic', $recommended['id']);
        $this->assertTrue($recommended['isRecommended']);
    }
}