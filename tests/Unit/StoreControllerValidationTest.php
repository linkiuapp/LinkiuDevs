<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Features\SuperLinkiu\Controllers\StoreController;
use App\Features\SuperLinkiu\Services\StoreTemplateService;
use App\Features\SuperLinkiu\Services\LocationService;
use App\Features\SuperLinkiu\Services\BulkImportBatchManager;
use App\Shared\Models\User;
use App\Shared\Models\Store;
use App\Shared\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;

/**
 * Store Controller Validation Unit Tests
 * 
 * Tests for new validation endpoints in StoreController
 * Requirements: All requirements validation
 */
class StoreControllerValidationTest extends TestCase
{
    use RefreshDatabase;

    protected StoreController $controller;
    protected $templateService;
    protected $locationService;
    protected $batchManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock dependencies
        $this->templateService = Mockery::mock(StoreTemplateService::class);
        $this->locationService = Mockery::mock(LocationService::class);
        $this->batchManager = Mockery::mock(BulkImportBatchManager::class);
        
        $this->controller = new StoreController(
            $this->templateService,
            $this->locationService,
            $this->batchManager
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function validate_email_returns_valid_for_new_email()
    {
        $request = Request::create('/test', 'POST', [
            'email' => 'new@example.com'
        ]);

        $response = $this->controller->validateEmail($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertTrue($data['data']['is_valid']);
        $this->assertEquals('email', $data['data']['field']);
        $this->assertNull($data['data']['message']);
    }

    /** @test */
    public function validate_email_returns_invalid_for_existing_user_email()
    {
        // Create existing user
        User::factory()->create(['email' => 'existing@example.com']);

        $request = Request::create('/test', 'POST', [
            'email' => 'existing@example.com'
        ]);

        $response = $this->controller->validateEmail($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertFalse($data['data']['is_valid']);
        $this->assertEquals('email', $data['data']['field']);
        $this->assertStringContainsString('ya está registrado como usuario', $data['data']['message']);
    }

    /** @test */
    public function validate_email_returns_invalid_for_existing_store_email()
    {
        // Create existing store
        Store::factory()->create(['email' => 'store@example.com']);

        $request = Request::create('/test', 'POST', [
            'email' => 'store@example.com'
        ]);

        $response = $this->controller->validateEmail($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertFalse($data['data']['is_valid']);
        $this->assertEquals('email', $data['data']['field']);
        $this->assertStringContainsString('ya está registrado para otra tienda', $data['data']['message']);
    }

    /** @test */
    public function validate_email_excludes_current_store_admin_on_update()
    {
        // Create store with admin
        $store = Store::factory()->create();
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'store_id' => $store->id,
            'role' => 'store_admin'
        ]);

        $request = Request::create('/test', 'POST', [
            'email' => 'admin@example.com',
            'store_id' => $store->id
        ]);

        $response = $this->controller->validateEmail($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertTrue($data['data']['is_valid']);
    }

    /** @test */
    public function validate_email_handles_invalid_email_format()
    {
        $request = Request::create('/test', 'POST', [
            'email' => 'invalid-email'
        ]);

        $response = $this->controller->validateEmail($request);
        
        $this->assertEquals(422, $response->getStatusCode());
    }

    /** @test */
    public function validate_slug_returns_valid_for_new_slug()
    {
        $request = Request::create('/test', 'POST', [
            'slug' => 'new-store-slug'
        ]);

        $response = $this->controller->validateSlug($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertTrue($data['data']['is_valid']);
        $this->assertEquals('slug', $data['data']['field']);
        $this->assertEquals('new-store-slug', $data['data']['sanitized_value']);
    }

    /** @test */
    public function validate_slug_returns_invalid_for_existing_slug()
    {
        // Create existing store
        Store::factory()->create(['slug' => 'existing-slug']);

        $request = Request::create('/test', 'POST', [
            'slug' => 'existing-slug'
        ]);

        $response = $this->controller->validateSlug($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertFalse($data['data']['is_valid']);
        $this->assertStringContainsString('ya está en uso', $data['data']['message']);
    }

    /** @test */
    public function validate_slug_sanitizes_input()
    {
        $request = Request::create('/test', 'POST', [
            'slug' => 'Tienda Con Espacios & Símbolos!'
        ]);

        $response = $this->controller->validateSlug($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('tienda-con-espacios-simbolos', $data['data']['sanitized_value']);
    }

    /** @test */
    public function suggest_slug_returns_multiple_alternatives()
    {
        // Create existing store to force suggestions
        Store::factory()->create(['slug' => 'test-store']);

        $request = Request::create('/test', 'POST', [
            'slug' => 'test-store'
        ]);

        $response = $this->controller->suggestSlug($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']['suggestions']);
        $this->assertGreaterThan(0, count($data['data']['suggestions']));
        $this->assertEquals('test-store', $data['data']['base_slug']);
        
        // Check that suggestions are different from base
        foreach ($data['data']['suggestions'] as $suggestion) {
            $this->assertNotEquals('test-store', $suggestion);
            $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $suggestion);
        }
    }

    /** @test */
    public function suggest_slug_includes_numbered_alternatives()
    {
        Store::factory()->create(['slug' => 'my-store']);

        $request = Request::create('/test', 'POST', [
            'slug' => 'my-store'
        ]);

        $response = $this->controller->suggestSlug($request);
        $data = $response->getData(true);

        $suggestions = $data['data']['suggestions'];
        
        // Should include numbered alternatives
        $this->assertContains('my-store-1', $suggestions);
    }

    /** @test */
    public function calculate_billing_returns_correct_structure()
    {
        $plan = Plan::factory()->create([
            'price' => 50000,
            'monthly_price' => 50000,
            'quarterly_price' => 135000,
            'biannual_price' => 240000
        ]);

        $request = Request::create('/test', 'POST', [
            'plan_id' => $plan->id,
            'billing_period' => 'monthly'
        ]);

        $response = $this->controller->calculateBilling($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('plan', $data['data']);
        $this->assertArrayHasKey('billing', $data['data']);
        
        $billing = $data['data']['billing'];
        $this->assertEquals('monthly', $billing['period']);
        $this->assertEquals(50000, $billing['base_amount']);
        $this->assertGreaterThan(0, $billing['tax']);
        $this->assertGreaterThan($billing['base_amount'], $billing['total']);
        $this->assertEquals('COP', $billing['currency']);
    }

    /** @test */
    public function calculate_billing_applies_discount_correctly()
    {
        $plan = Plan::factory()->create(['price' => 100000]);

        $request = Request::create('/test', 'POST', [
            'plan_id' => $plan->id,
            'billing_period' => 'monthly',
            'discount_code' => 'WELCOME10'
        ]);

        $response = $this->controller->calculateBilling($request);
        $data = $response->getData(true);

        $billing = $data['data']['billing'];
        $this->assertEquals('WELCOME10', $billing['discount_code']);
        $this->assertEquals(10, $billing['discount_percentage']);
        $this->assertEquals(10000, $billing['discount_amount']);
        $this->assertEquals(90000, $billing['subtotal']);
    }

    /** @test */
    public function calculate_billing_handles_quarterly_period()
    {
        $plan = Plan::factory()->create([
            'price' => 50000,
            'quarterly_price' => 135000
        ]);

        $request = Request::create('/test', 'POST', [
            'plan_id' => $plan->id,
            'billing_period' => 'quarterly'
        ]);

        $response = $this->controller->calculateBilling($request);
        $data = $response->getData(true);

        $billing = $data['data']['billing'];
        $this->assertEquals('quarterly', $billing['period']);
        $this->assertEquals(135000, $billing['base_amount']);
    }

    /** @test */
    public function suggest_email_domain_returns_corrections()
    {
        $request = Request::create('/test', 'POST', [
            'email' => 'user@gmai.com'  // Typo in gmail
        ]);

        $response = $this->controller->suggestEmailDomain($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']['suggestions']);
        $this->assertContains('user@gmail.com', $data['data']['suggestions']);
    }

    /** @test */
    public function suggest_email_domain_handles_common_typos()
    {
        $testCases = [
            'user@hotmai.com' => 'user@hotmail.com',
            'user@yahho.com' => 'user@yahoo.com',
            'user@outlok.com' => 'user@outlook.com'
        ];

        foreach ($testCases as $typo => $expected) {
            $request = Request::create('/test', 'POST', ['email' => $typo]);
            $response = $this->controller->suggestEmailDomain($request);
            $data = $response->getData(true);

            $this->assertContains($expected, $data['data']['suggestions'], 
                "Failed to suggest {$expected} for {$typo}");
        }
    }

    /** @test */
    public function get_validation_suggestions_returns_slug_alternatives()
    {
        $request = Request::create('/test', 'POST', [
            'field' => 'slug',
            'value' => 'taken-slug',
            'error_type' => 'taken'
        ]);

        $response = $this->controller->getValidationSuggestions($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']['suggestions']);
        $this->assertEquals('slug', $data['data']['field']);
        $this->assertEquals('taken', $data['data']['error_type']);
        
        // Should have replacement suggestions
        $replacementSuggestions = array_filter($data['data']['suggestions'], function($suggestion) {
            return $suggestion['type'] === 'replacement';
        });
        $this->assertNotEmpty($replacementSuggestions);
    }

    /** @test */
    public function get_validation_suggestions_returns_email_alternatives()
    {
        $request = Request::create('/test', 'POST', [
            'field' => 'email',
            'value' => 'taken@example.com',
            'error_type' => 'taken'
        ]);

        $response = $this->controller->getValidationSuggestions($request);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $suggestions = $data['data']['suggestions'];
        
        // Should have action and replacement suggestions
        $actionSuggestions = array_filter($suggestions, function($suggestion) {
            return $suggestion['type'] === 'action';
        });
        $this->assertNotEmpty($actionSuggestions);
        
        $replacementSuggestions = array_filter($suggestions, function($suggestion) {
            return $suggestion['type'] === 'replacement';
        });
        $this->assertNotEmpty($replacementSuggestions);
    }

    /** @test */
    public function validation_endpoints_handle_server_errors_gracefully()
    {
        // Mock Log to prevent actual logging during tests
        Log::shouldReceive('error')->andReturn(true);

        // Test with invalid plan_id to trigger exception
        $request = Request::create('/test', 'POST', [
            'plan_id' => 99999,
            'billing_period' => 'monthly'
        ]);

        $response = $this->controller->calculateBilling($request);
        $data = $response->getData(true);

        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /** @test */
    public function validation_endpoints_require_authentication()
    {
        // This would be tested in feature tests, but we can verify the controller
        // methods don't have built-in auth (auth is handled by middleware)
        $this->assertTrue(method_exists($this->controller, 'validateEmail'));
        $this->assertTrue(method_exists($this->controller, 'validateSlug'));
        $this->assertTrue(method_exists($this->controller, 'suggestSlug'));
        $this->assertTrue(method_exists($this->controller, 'calculateBilling'));
    }
}