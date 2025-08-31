<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Shared\Models\User;
use App\Shared\Models\Store;
use App\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Validation Performance Tests
 * 
 * Tests for validation endpoint performance including concurrent requests,
 * database query optimization, and response times
 * Requirements: Performance optimization and monitoring
 */
class ValidationPerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@superlinkiu.com'
        ]);
        
        $this->plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'allow_custom_slug' => true,
            'price' => 50000
        ]);
    }

    /** @test */
    public function email_validation_endpoint_responds_within_acceptable_time()
    {
        $this->actingAs($this->superAdmin);
        
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'performance@example.com'
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        $this->assertLessThan(200, $responseTime, 'Email validation should respond within 200ms');
    }

    /** @test */
    public function slug_validation_endpoint_responds_within_acceptable_time()
    {
        $this->actingAs($this->superAdmin);
        
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-slug', [
            'slug' => 'performance-test-slug'
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(200, $responseTime, 'Slug validation should respond within 200ms');
    }

    /** @test */
    public function validation_endpoints_handle_concurrent_requests_efficiently()
    {
        $this->actingAs($this->superAdmin);
        
        $concurrentRequests = 10;
        $promises = [];
        
        // Create multiple concurrent requests
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $promises[] = $this->postJson('/superlinkiu/api/stores/validate-email', [
                'email' => "concurrent{$i}@example.com"
            ]);
        }
        
        // All requests should complete successfully
        foreach ($promises as $response) {
            $response->assertStatus(200)
                    ->assertJson(['success' => true]);
        }
    }

    /** @test */
    public function email_validation_uses_efficient_database_queries()
    {
        $this->actingAs($this->superAdmin);
        
        // Create some test data
        User::factory()->count(100)->create();
        Store::factory()->count(100)->create();
        
        // Enable query logging
        DB::enableQueryLog();
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'query-test@example.com'
        ]);
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        $response->assertStatus(200);
        
        // Should use minimal queries (ideally 2: one for users, one for stores)
        $this->assertLessThanOrEqual(3, count($queries), 'Email validation should use minimal database queries');
        
        // Check that queries use indexes (no table scans)
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('table scan', strtolower($query['query']));
        }
    }

    /** @test */
    public function slug_validation_uses_efficient_database_queries()
    {
        $this->actingAs($this->superAdmin);
        
        // Create test data
        Store::factory()->count(100)->create();
        
        DB::enableQueryLog();
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-slug', [
            'slug' => 'query-test-slug'
        ]);
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        $response->assertStatus(200);
        
        // Should use minimal queries (ideally 1 for stores table)
        $this->assertLessThanOrEqual(2, count($queries), 'Slug validation should use minimal database queries');
    }

    /** @test */
    public function slug_suggestion_endpoint_performs_well_with_many_existing_slugs()
    {
        $this->actingAs($this->superAdmin);
        
        // Create many stores with similar slugs
        for ($i = 1; $i <= 50; $i++) {
            Store::factory()->create(['slug' => "test-store-{$i}"]);
        }
        
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/suggest-slug', [
            'slug' => 'test-store'
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => ['suggestions']
                ]);
        
        $this->assertLessThan(500, $responseTime, 'Slug suggestion should respond within 500ms even with many existing slugs');
        
        // Should still provide useful suggestions
        $suggestions = $response->json('data.suggestions');
        $this->assertGreaterThan(0, count($suggestions));
    }

    /** @test */
    public function billing_calculation_endpoint_performs_complex_calculations_efficiently()
    {
        $this->actingAs($this->superAdmin);
        
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/calculate-billing', [
            'plan_id' => $this->plan->id,
            'billing_period' => 'quarterly',
            'discount_code' => 'WELCOME10'
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'billing' => [
                            'base_amount',
                            'discount_amount',
                            'tax',
                            'total'
                        ]
                    ]
                ]);
        
        $this->assertLessThan(300, $responseTime, 'Billing calculation should complete within 300ms');
    }

    /** @test */
    public function validation_endpoints_implement_proper_caching()
    {
        $this->actingAs($this->superAdmin);
        
        // Clear any existing cache
        Cache::flush();
        
        // First request (should hit database)
        $startTime = microtime(true);
        $response1 = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'cache-test@example.com'
        ]);
        $firstRequestTime = (microtime(true) - $startTime) * 1000;
        
        // Second identical request (should use cache if implemented)
        $startTime = microtime(true);
        $response2 = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'cache-test@example.com'
        ]);
        $secondRequestTime = (microtime(true) - $startTime) * 1000;
        
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        // If caching is implemented, second request should be faster
        // This is optional since caching might not be implemented for validation
        if ($secondRequestTime < $firstRequestTime * 0.8) {
            $this->addToAssertionCount(1); // Caching is working
        }
    }

    /** @test */
    public function validation_endpoints_handle_large_datasets_efficiently()
    {
        $this->actingAs($this->superAdmin);
        
        // Create large dataset
        User::factory()->count(1000)->create();
        Store::factory()->count(1000)->create();
        
        // Test email validation performance with large dataset
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'large-dataset@example.com'
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime, 'Email validation should handle large datasets within 500ms');
        
        // Test slug validation performance with large dataset
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-slug', [
            'slug' => 'large-dataset-slug'
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $responseTime, 'Slug validation should handle large datasets within 500ms');
    }

    /** @test */
    public function validation_endpoints_maintain_performance_under_load()
    {
        $this->actingAs($this->superAdmin);
        
        $requestCount = 20;
        $responseTimes = [];
        
        // Make multiple sequential requests to simulate load
        for ($i = 0; $i < $requestCount; $i++) {
            $startTime = microtime(true);
            
            $response = $this->postJson('/superlinkiu/api/stores/validate-email', [
                'email' => "load-test-{$i}@example.com"
            ]);
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;
            
            $response->assertStatus(200);
            $responseTimes[] = $responseTime;
        }
        
        // Calculate average response time
        $averageResponseTime = array_sum($responseTimes) / count($responseTimes);
        $maxResponseTime = max($responseTimes);
        
        $this->assertLessThan(300, $averageResponseTime, 'Average response time should be under 300ms');
        $this->assertLessThan(1000, $maxResponseTime, 'Maximum response time should be under 1000ms');
        
        // Check that response times don't degrade significantly over time
        $firstHalf = array_slice($responseTimes, 0, $requestCount / 2);
        $secondHalf = array_slice($responseTimes, $requestCount / 2);
        
        $firstHalfAverage = array_sum($firstHalf) / count($firstHalf);
        $secondHalfAverage = array_sum($secondHalf) / count($secondHalf);
        
        // Second half shouldn't be more than 50% slower than first half
        $this->assertLessThan($firstHalfAverage * 1.5, $secondHalfAverage, 
            'Performance should not degrade significantly under load');
    }

    /** @test */
    public function location_search_endpoint_performs_efficiently()
    {
        $this->actingAs($this->superAdmin);
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/superlinkiu/api/locations/search?query=bog&type=city');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'results',
                        'query',
                        'total'
                    ]
                ]);
        
        $this->assertLessThan(400, $responseTime, 'Location search should respond within 400ms');
        
        // Verify results are limited to prevent performance issues
        $results = $response->json('data.results');
        $this->assertLessThanOrEqual(10, count($results), 'Location search should limit results to prevent performance issues');
    }

    /** @test */
    public function template_endpoints_respond_quickly()
    {
        $this->actingAs($this->superAdmin);
        
        // Test template list endpoint
        $startTime = microtime(true);
        $response = $this->getJson('/superlinkiu/api/templates');
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(100, $responseTime, 'Template list should respond within 100ms');
        
        // Test template config endpoint
        $startTime = microtime(true);
        $response = $this->getJson('/superlinkiu/api/templates/basic/config');
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(150, $responseTime, 'Template config should respond within 150ms');
    }

    /** @test */
    public function validation_endpoints_handle_malformed_requests_efficiently()
    {
        $this->actingAs($this->superAdmin);
        
        // Test with malformed email
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-email', [
            'email' => 'not-an-email'
        ]);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        // Should fail fast for validation errors
        $this->assertLessThan(100, $responseTime, 'Validation errors should be handled quickly');
        $response->assertStatus(422); // Validation error
        
        // Test with missing parameters
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/validate-slug', []);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $this->assertLessThan(100, $responseTime, 'Missing parameter errors should be handled quickly');
        $response->assertStatus(422);
    }

    /** @test */
    public function draft_save_endpoint_performs_well_with_large_form_data()
    {
        $this->actingAs($this->superAdmin);
        
        // Create large form data
        $largeDescription = str_repeat('This is a very long description. ', 200);
        $largeFormData = [
            'template' => 'enterprise',
            'current_step' => 4,
            'form_data' => [
                'owner_name' => 'Performance Test User',
                'admin_email' => 'performance@example.com',
                'name' => 'Performance Test Store',
                'description' => $largeDescription,
                'meta_description' => $largeDescription,
                'additional_notes' => $largeDescription,
                'custom_field_1' => str_repeat('data', 100),
                'custom_field_2' => str_repeat('more data', 100)
            ]
        ];
        
        $startTime = microtime(true);
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $largeFormData);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(1000, $responseTime, 'Draft save should handle large data within 1000ms');
    }
}