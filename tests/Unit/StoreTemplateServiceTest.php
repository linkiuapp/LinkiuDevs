<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Features\SuperLinkiu\Services\StoreTemplateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Store Template Service Unit Tests
 * 
 * Tests for template management and configuration functionality
 * Requirements: 3.2, 3.4 - Template system and dynamic form generation
 */
class StoreTemplateServiceTest extends TestCase
{
    protected StoreTemplateService $templateService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Cache facade
        Cache::shouldReceive('remember')->andReturnUsing(function ($key, $ttl, $callback) {
            return $callback();
        });
        Cache::shouldReceive('forget')->andReturn(true);
        Cache::shouldReceive('put')->andReturn(true);
        
        $this->templateService = new StoreTemplateService();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function get_all_templates_returns_collection()
    {
        $templates = $this->templateService->getAllTemplates();
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $templates);
        $this->assertGreaterThan(0, $templates->count());
        
        // Check that default templates exist
        $this->assertTrue($templates->has('basic'));
        $this->assertTrue($templates->has('complete'));
        $this->assertTrue($templates->has('enterprise'));
    }

    /** @test */
    public function get_template_returns_correct_template()
    {
        $basicTemplate = $this->templateService->getTemplate('basic');
        
        $this->assertNotNull($basicTemplate);
        $this->assertEquals('basic', $basicTemplate['id']);
        $this->assertEquals('Tienda Básica', $basicTemplate['name']);
        $this->assertArrayHasKey('steps', $basicTemplate);
        $this->assertArrayHasKey('capabilities', $basicTemplate);
    }

    /** @test */
    public function get_template_returns_null_for_nonexistent_template()
    {
        $template = $this->templateService->getTemplate('nonexistent');
        
        $this->assertNull($template);
    }

    /** @test */
    public function get_template_config_returns_complete_configuration()
    {
        $config = $this->templateService->getTemplateConfig('basic');
        
        $this->assertArrayHasKey('id', $config);
        $this->assertArrayHasKey('name', $config);
        $this->assertArrayHasKey('steps', $config);
        $this->assertArrayHasKey('capabilities', $config);
        $this->assertArrayHasKey('defaultValues', $config);
        $this->assertArrayHasKey('validationRules', $config);
        $this->assertArrayHasKey('fieldMapping', $config);
        
        $this->assertEquals('basic', $config['id']);
        $this->assertEquals('Tienda Básica', $config['name']);
    }

    /** @test */
    public function get_template_config_throws_exception_for_invalid_template()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Template 'invalid' not found");
        
        $this->templateService->getTemplateConfig('invalid');
    }

    /** @test */
    public function get_template_defaults_returns_correct_values()
    {
        $basicDefaults = $this->templateService->getTemplateDefaults('basic');
        
        $this->assertArrayHasKey('owner_country', $basicDefaults);
        $this->assertEquals('Colombia', $basicDefaults['owner_country']);
        $this->assertEquals('active', $basicDefaults['status']);
        $this->assertEquals('basic', $basicDefaults['plan_type']);
    }

    /** @test */
    public function get_template_defaults_returns_empty_for_unknown_template()
    {
        $defaults = $this->templateService->getTemplateDefaults('unknown');
        
        $this->assertEmpty($defaults);
    }

    /** @test */
    public function get_template_validation_rules_includes_base_rules()
    {
        $rules = $this->templateService->getTemplateValidationRules('basic');
        
        // Check base rules
        $this->assertArrayHasKey('owner_name', $rules);
        $this->assertArrayHasKey('admin_email', $rules);
        $this->assertArrayHasKey('admin_password', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('slug', $rules);
        $this->assertArrayHasKey('plan_id', $rules);
        
        // Basic template should not have additional rules
        $this->assertArrayNotHasKey('owner_document_type', $rules);
    }

    /** @test */
    public function get_template_validation_rules_includes_template_specific_rules()
    {
        $enterpriseRules = $this->templateService->getTemplateValidationRules('enterprise');
        
        // Should have base rules
        $this->assertArrayHasKey('owner_name', $enterpriseRules);
        
        // Should have enterprise-specific rules
        $this->assertArrayHasKey('owner_document_type', $enterpriseRules);
        $this->assertArrayHasKey('fiscal_document_type', $enterpriseRules);
        $this->assertArrayHasKey('fiscal_address', $enterpriseRules);
        $this->assertArrayHasKey('tax_regime', $enterpriseRules);
    }

    /** @test */
    public function get_template_field_mapping_returns_correct_structure()
    {
        $mapping = $this->templateService->getTemplateFieldMapping('complete');
        
        $this->assertIsArray($mapping);
        $this->assertNotEmpty($mapping);
        
        // Check that owner_name field exists and has correct structure
        $this->assertArrayHasKey('owner_name', $mapping);
        $ownerNameField = $mapping['owner_name'];
        
        $this->assertArrayHasKey('step', $ownerNameField);
        $this->assertArrayHasKey('label', $ownerNameField);
        $this->assertArrayHasKey('type', $ownerNameField);
        $this->assertArrayHasKey('required', $ownerNameField);
        
        $this->assertEquals('owner-info', $ownerNameField['step']);
        $this->assertEquals('Nombre', $ownerNameField['label']);
        $this->assertEquals('text', $ownerNameField['type']);
        $this->assertTrue($ownerNameField['required']);
    }

    /** @test */
    public function has_capability_returns_correct_boolean()
    {
        $this->assertTrue($this->templateService->hasCapability('basic', 'basicInfo'));
        $this->assertFalse($this->templateService->hasCapability('basic', 'fiscalInfo'));
        
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'fiscalInfo'));
        $this->assertTrue($this->templateService->hasCapability('enterprise', 'advancedConfig'));
    }

    /** @test */
    public function has_capability_returns_false_for_nonexistent_template()
    {
        $this->assertFalse($this->templateService->hasCapability('nonexistent', 'basicInfo'));
    }

    /** @test */
    public function get_templates_by_capability_filters_correctly()
    {
        $basicTemplates = $this->templateService->getTemplatesByCapability('basicInfo');
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $basicTemplates);
        $this->assertGreaterThan(0, $basicTemplates->count());
        
        // All returned templates should have basicInfo capability
        foreach ($basicTemplates as $template) {
            $this->assertContains('basicInfo', $template['capabilities']);
        }
    }

    /** @test */
    public function get_templates_by_capability_returns_empty_for_nonexistent_capability()
    {
        $templates = $this->templateService->getTemplatesByCapability('nonexistentCapability');
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $templates);
        $this->assertEquals(0, $templates->count());
    }

    /** @test */
    public function get_recommended_template_returns_basic_template()
    {
        $recommended = $this->templateService->getRecommendedTemplate();
        
        $this->assertNotNull($recommended);
        $this->assertEquals('basic', $recommended['id']);
        $this->assertTrue($recommended['isRecommended']);
    }

    /** @test */
    public function validate_template_accepts_valid_template()
    {
        $validTemplate = [
            'id' => 'test-template',
            'name' => 'Test Template',
            'description' => 'A test template for validation',
            'icon' => 'test-icon',
            'capabilities' => ['basicInfo'],
            'steps' => [
                [
                    'id' => 'test-step',
                    'title' => 'Test Step',
                    'order' => 1,
                    'isOptional' => false,
                    'fields' => [
                        [
                            'name' => 'test_field',
                            'label' => 'Test Field',
                            'type' => 'text',
                            'required' => true
                        ]
                    ]
                ]
            ],
            'fieldCount' => 1,
            'estimatedTime' => '5 min'
        ];
        
        $result = $this->templateService->validateTemplate($validTemplate);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function validate_template_throws_exception_for_invalid_template()
    {
        $invalidTemplate = [
            'id' => '', // Invalid: empty ID
            'name' => 'Test Template',
            'steps' => [] // Invalid: no steps
        ];
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid template configuration');
        
        $this->templateService->validateTemplate($invalidTemplate);
    }

    /** @test */
    public function validate_template_validates_step_structure()
    {
        $templateWithInvalidStep = [
            'id' => 'test-template',
            'name' => 'Test Template',
            'description' => 'A test template',
            'icon' => 'test-icon',
            'capabilities' => ['basicInfo'],
            'steps' => [
                [
                    'id' => '', // Invalid: empty step ID
                    'title' => 'Test Step',
                    'fields' => []
                ]
            ],
            'fieldCount' => 1,
            'estimatedTime' => '5 min'
        ];
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid step configuration');
        
        $this->templateService->validateTemplate($templateWithInvalidStep);
    }

    /** @test */
    public function validate_template_validates_field_structure()
    {
        $templateWithInvalidField = [
            'id' => 'test-template',
            'name' => 'Test Template',
            'description' => 'A test template',
            'icon' => 'test-icon',
            'capabilities' => ['basicInfo'],
            'steps' => [
                [
                    'id' => 'test-step',
                    'title' => 'Test Step',
                    'order' => 1,
                    'fields' => [
                        [
                            'name' => '', // Invalid: empty field name
                            'label' => 'Test Field'
                        ]
                    ]
                ]
            ],
            'fieldCount' => 1,
            'estimatedTime' => '5 min'
        ];
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field configuration');
        
        $this->templateService->validateTemplate($templateWithInvalidField);
    }

    /** @test */
    public function add_template_validates_and_stores_template()
    {
        $newTemplate = [
            'id' => 'custom-template',
            'name' => 'Custom Template',
            'description' => 'A custom template',
            'icon' => 'custom-icon',
            'capabilities' => ['basicInfo'],
            'steps' => [
                [
                    'id' => 'custom-step',
                    'title' => 'Custom Step',
                    'order' => 1,
                    'isOptional' => false,
                    'fields' => [
                        [
                            'name' => 'custom_field',
                            'label' => 'Custom Field',
                            'type' => 'text',
                            'required' => true
                        ]
                    ]
                ]
            ],
            'fieldCount' => 1,
            'estimatedTime' => '3 min'
        ];
        
        $this->templateService->addTemplate($newTemplate);
        
        $retrievedTemplate = $this->templateService->getTemplate('custom-template');
        $this->assertNotNull($retrievedTemplate);
        $this->assertEquals('Custom Template', $retrievedTemplate['name']);
    }

    /** @test */
    public function remove_template_removes_existing_template()
    {
        // First add a template
        $testTemplate = [
            'id' => 'removable-template',
            'name' => 'Removable Template',
            'description' => 'A template to be removed',
            'icon' => 'remove-icon',
            'capabilities' => ['basicInfo'],
            'steps' => [
                [
                    'id' => 'remove-step',
                    'title' => 'Remove Step',
                    'order' => 1,
                    'isOptional' => false,
                    'fields' => [
                        [
                            'name' => 'remove_field',
                            'label' => 'Remove Field',
                            'type' => 'text',
                            'required' => true
                        ]
                    ]
                ]
            ],
            'fieldCount' => 1,
            'estimatedTime' => '2 min'
        ];
        
        $this->templateService->addTemplate($testTemplate);
        
        // Verify it exists
        $this->assertNotNull($this->templateService->getTemplate('removable-template'));
        
        // Remove it
        $result = $this->templateService->removeTemplate('removable-template');
        $this->assertTrue($result);
        
        // Verify it's gone
        $this->assertNull($this->templateService->getTemplate('removable-template'));
    }

    /** @test */
    public function remove_template_returns_false_for_nonexistent_template()
    {
        $result = $this->templateService->removeTemplate('nonexistent-template');
        
        $this->assertFalse($result);
    }

    /** @test */
    public function clear_cache_reloads_templates()
    {
        // This test verifies that clearCache method exists and can be called
        // In a real scenario, this would test cache invalidation
        $this->templateService->clearCache();
        
        // Verify templates are still accessible after cache clear
        $templates = $this->templateService->getAllTemplates();
        $this->assertGreaterThan(0, $templates->count());
    }

    /** @test */
    public function template_steps_are_processed_correctly()
    {
        $config = $this->templateService->getTemplateConfig('complete');
        $steps = $config['steps'];
        
        $this->assertIsArray($steps);
        $this->assertNotEmpty($steps);
        
        // Steps should be sorted by order
        $previousOrder = 0;
        foreach ($steps as $step) {
            $this->assertGreaterThan($previousOrder, $step['order']);
            $previousOrder = $step['order'];
            
            // Each step should have required fields
            $this->assertArrayHasKey('id', $step);
            $this->assertArrayHasKey('title', $step);
            $this->assertArrayHasKey('order', $step);
            $this->assertArrayHasKey('isOptional', $step);
            $this->assertArrayHasKey('fields', $step);
            $this->assertArrayHasKey('dependencies', $step);
        }
    }

    /** @test */
    public function template_fields_are_processed_correctly()
    {
        $mapping = $this->templateService->getTemplateFieldMapping('enterprise');
        
        foreach ($mapping as $fieldName => $fieldConfig) {
            $this->assertArrayHasKey('step', $fieldConfig);
            $this->assertArrayHasKey('label', $fieldConfig);
            $this->assertArrayHasKey('type', $fieldConfig);
            $this->assertArrayHasKey('required', $fieldConfig);
            $this->assertArrayHasKey('validation', $fieldConfig);
            $this->assertArrayHasKey('placeholder', $fieldConfig);
            $this->assertArrayHasKey('options', $fieldConfig);
            $this->assertArrayHasKey('dependsOn', $fieldConfig);
            
            // Type should be valid
            $validTypes = ['text', 'email', 'password', 'select', 'textarea', 'number', 'tel', 'url'];
            $this->assertContains($fieldConfig['type'], $validTypes);
        }
    }
}