<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Shared\Models\User;
use App\Shared\Models\Plan;
use App\Shared\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class StoreConfigurationStepTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a super admin user
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@superlinkiu.com'
        ]);
        
        // Create a test plan
        $this->plan = Plan::factory()->create([
            'name' => 'Test Plan',
            'allow_custom_slug' => true,
            'max_products' => 100,
            'max_categories' => 10,
            'max_admins' => 1
        ]);
    }

    /** @test */
    public function it_can_validate_email_uniqueness()
    {
        $this->actingAs($this->superAdmin);
        
        // Create an existing user with email
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
    public function it_can_validate_slug_availability()
    {
        $this->actingAs($this->superAdmin);
        
        // Create an existing store with slug
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
        
        // Test new slug
        $response = $this->postJson('/superlinkiu/api/stores/validate-slug', [
            'slug' => 'new-store'
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'is_valid' => true,
                        'field' => 'slug'
                    ]
                ]);
    }

    /** @test */
    public function it_can_suggest_alternative_slugs()
    {
        $this->actingAs($this->superAdmin);
        
        // Create an existing store with slug
        Store::factory()->create(['slug' => 'test-store']);
        
        $response = $this->postJson('/superlinkiu/api/stores/suggest-slug', [
            'slug' => 'test-store'
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'suggestions'
                    ]
                ]);
        
        $suggestions = $response->json('data.suggestions');
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
    }

    /** @test */
    public function it_can_calculate_billing()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->postJson('/superlinkiu/api/stores/calculate-billing', [
            'plan_id' => $this->plan->id,
            'billing_period' => 'monthly'
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'plan',
                        'billing' => [
                            'base_amount',
                            'tax',
                            'total',
                            'currency',
                            'period'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_renders_store_configuration_step_component()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->get('/superlinkiu/stores/create-wizard');
        
        $response->assertStatus(200)
                ->assertViewIs('superlinkiu::stores.create-wizard')
                ->assertSee('store-configuration-step');
    }

    /** @test */
    public function slug_generation_from_name_works_correctly()
    {
        // Test slug generation logic (this would be in JavaScript, but we can test the PHP equivalent)
        $testCases = [
            'Mi Tienda Online' => 'mi-tienda-online',
            'Café & Restaurante' => 'cafe-restaurante',
            'Tienda 123' => 'tienda-123',
            'Ñoño\'s Store' => 'nono-s-store',
            'UPPERCASE STORE' => 'uppercase-store',
            '   Spaces   ' => 'spaces',
            'Multiple---Hyphens' => 'multiple-hyphens',
            'Acentós y Ñoñerías' => 'acentos-y-nonerias'
        ];
        
        foreach ($testCases as $input => $expected) {
            $result = $this->generateSlugFromName($input);
            $this->assertEquals($expected, $result, "Failed for input: {$input}");
        }
    }

    /**
     * Helper method to test slug generation (mirrors JavaScript logic)
     */
    private function generateSlugFromName($name)
    {
        $slug = strtolower(trim($name));
        
        // Replace accented characters
        $accents = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ā' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'ē' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'ī' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'ū' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        ];
        $slug = strtr($slug, $accents);
        
        // Replace spaces and special characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }
}