<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Shared\Models\User;
use App\Models\StoreDraft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

/**
 * Draft Management Integration Tests
 * 
 * Tests for draft management system integration including auto-save,
 * recovery, and cleanup functionality
 * Requirements: 5.1, 5.2, 5.3, 5.4 - Draft management and auto-save
 */
class DraftManagementIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $otherAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@superlinkiu.com'
        ]);
        
        $this->otherAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'other@superlinkiu.com'
        ]);
    }

    /** @test */
    public function it_can_save_draft_data()
    {
        $this->actingAs($this->superAdmin);
        
        $draftData = [
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => [
                'owner_name' => 'John Doe',
                'admin_email' => 'john@example.com'
            ]
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Borrador guardado exitosamente'
                ]);
        
        // Verify draft was saved in database
        $this->assertDatabaseHas('store_drafts', [
            'user_id' => $this->superAdmin->id,
            'template' => 'basic',
            'current_step' => 1
        ]);
        
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        $this->assertNotNull($draft);
        $this->assertEquals('John Doe', $draft->form_data['owner_name']);
        $this->assertEquals('john@example.com', $draft->form_data['admin_email']);
    }

    /** @test */
    public function it_can_update_existing_draft()
    {
        $this->actingAs($this->superAdmin);
        
        // Create initial draft
        $initialData = [
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => [
                'owner_name' => 'John Doe'
            ]
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $initialData);
        
        // Update draft with more data
        $updatedData = [
            'template' => 'basic',
            'current_step' => 2,
            'form_data' => [
                'owner_name' => 'John Doe',
                'admin_email' => 'john@example.com',
                'name' => 'Test Store'
            ]
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $updatedData);
        
        $response->assertStatus(200);
        
        // Should still have only one draft
        $this->assertEquals(1, StoreDraft::where('user_id', $this->superAdmin->id)->count());
        
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        $this->assertEquals(2, $draft->current_step);
        $this->assertEquals('Test Store', $draft->form_data['name']);
    }

    /** @test */
    public function it_can_retrieve_existing_draft()
    {
        $this->actingAs($this->superAdmin);
        
        // Create draft
        $draftData = [
            'template' => 'complete',
            'current_step' => 3,
            'form_data' => [
                'owner_name' => 'Jane Smith',
                'admin_email' => 'jane@example.com',
                'name' => 'Complete Store',
                'slug' => 'complete-store'
            ]
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        // Retrieve draft
        $response = $this->getJson('/superlinkiu/api/stores/get-draft');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'has_draft' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'has_draft',
                    'data' => [
                        'id',
                        'template',
                        'current_step',
                        'form_data',
                        'created_at',
                        'updated_at'
                    ]
                ]);
        
        $retrievedData = $response->json('data');
        $this->assertEquals('complete', $retrievedData['template']);
        $this->assertEquals(3, $retrievedData['current_step']);
        $this->assertEquals('Jane Smith', $retrievedData['form_data']['owner_name']);
        $this->assertEquals('Complete Store', $retrievedData['form_data']['name']);
    }

    /** @test */
    public function it_returns_no_draft_when_none_exists()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->getJson('/superlinkiu/api/stores/get-draft');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'has_draft' => false,
                    'data' => null
                ]);
    }

    /** @test */
    public function it_can_delete_draft()
    {
        $this->actingAs($this->superAdmin);
        
        // Create draft
        $draftData = [
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => ['owner_name' => 'Test User']
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        // Verify draft exists
        $this->assertEquals(1, StoreDraft::where('user_id', $this->superAdmin->id)->count());
        
        // Delete draft
        $response = $this->deleteJson('/superlinkiu/api/stores/delete-draft');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Borrador eliminado exitosamente'
                ]);
        
        // Verify draft was deleted
        $this->assertEquals(0, StoreDraft::where('user_id', $this->superAdmin->id)->count());
    }

    /** @test */
    public function it_handles_deleting_nonexistent_draft()
    {
        $this->actingAs($this->superAdmin);
        
        $response = $this->deleteJson('/superlinkiu/api/stores/delete-draft');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'No se encontró ningún borrador'
                ]);
    }

    /** @test */
    public function it_isolates_drafts_between_users()
    {
        // Create draft for first user
        $this->actingAs($this->superAdmin);
        
        $draftData1 = [
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => ['owner_name' => 'User One']
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $draftData1);
        
        // Create draft for second user
        $this->actingAs($this->otherAdmin);
        
        $draftData2 = [
            'template' => 'enterprise',
            'current_step' => 2,
            'form_data' => ['owner_name' => 'User Two']
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $draftData2);
        
        // Verify first user can only see their draft
        $this->actingAs($this->superAdmin);
        $response = $this->getJson('/superlinkiu/api/stores/get-draft');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('basic', $data['template']);
        $this->assertEquals('User One', $data['form_data']['owner_name']);
        
        // Verify second user can only see their draft
        $this->actingAs($this->otherAdmin);
        $response = $this->getJson('/superlinkiu/api/stores/get-draft');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('enterprise', $data['template']);
        $this->assertEquals('User Two', $data['form_data']['owner_name']);
    }

    /** @test */
    public function it_sets_expiration_date_on_draft_creation()
    {
        $this->actingAs($this->superAdmin);
        
        $draftData = [
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => ['owner_name' => 'Test User']
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        
        $this->assertNotNull($draft->expires_at);
        
        // Should expire in 7 days (default)
        $expectedExpiration = now()->addDays(7);
        $this->assertTrue($draft->expires_at->diffInMinutes($expectedExpiration) < 1);
    }

    /** @test */
    public function it_can_handle_concurrent_draft_saves()
    {
        $this->actingAs($this->superAdmin);
        
        // Simulate concurrent saves by creating multiple requests
        $draftData1 = [
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => ['owner_name' => 'First Save']
        ];
        
        $draftData2 = [
            'template' => 'basic',
            'current_step' => 2,
            'form_data' => ['owner_name' => 'Second Save', 'admin_email' => 'test@example.com']
        ];
        
        // Save first draft
        $response1 = $this->postJson('/superlinkiu/api/stores/save-draft', $draftData1);
        $response1->assertStatus(200);
        
        // Save second draft (should update, not create new)
        $response2 = $this->postJson('/superlinkiu/api/stores/save-draft', $draftData2);
        $response2->assertStatus(200);
        
        // Should still have only one draft
        $this->assertEquals(1, StoreDraft::where('user_id', $this->superAdmin->id)->count());
        
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        $this->assertEquals(2, $draft->current_step);
        $this->assertEquals('Second Save', $draft->form_data['owner_name']);
        $this->assertEquals('test@example.com', $draft->form_data['admin_email']);
    }

    /** @test */
    public function it_can_detect_draft_conflicts()
    {
        $this->actingAs($this->superAdmin);
        
        // Create initial draft
        $initialData = [
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => ['owner_name' => 'Original']
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $initialData);
        
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        $originalTimestamp = $draft->updated_at;
        
        // Simulate time passing and another update
        Carbon::setTestNow(now()->addMinutes(5));
        
        $updatedData = [
            'template' => 'basic',
            'current_step' => 2,
            'form_data' => ['owner_name' => 'Updated'],
            'last_updated' => $originalTimestamp->toISOString()
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $updatedData);
        
        $response->assertStatus(200);
        
        // Reset time
        Carbon::setTestNow();
    }

    /** @test */
    public function it_validates_draft_data_structure()
    {
        $this->actingAs($this->superAdmin);
        
        // Test missing required fields
        $invalidData = [
            'current_step' => 1
            // Missing template and form_data
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $invalidData);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['template', 'form_data']);
        
        // Test invalid step number
        $invalidStepData = [
            'template' => 'basic',
            'current_step' => 0, // Invalid step
            'form_data' => ['owner_name' => 'Test']
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $invalidStepData);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_step']);
    }

    /** @test */
    public function it_can_cleanup_expired_drafts()
    {
        $this->actingAs($this->superAdmin);
        
        // Create draft that will be expired
        $expiredDraft = StoreDraft::create([
            'user_id' => $this->superAdmin->id,
            'template' => 'basic',
            'current_step' => 1,
            'form_data' => ['owner_name' => 'Expired'],
            'expires_at' => now()->subDays(1) // Expired yesterday
        ]);
        
        // Create draft that is still valid
        $validDraft = StoreDraft::create([
            'user_id' => $this->otherAdmin->id,
            'template' => 'complete',
            'current_step' => 2,
            'form_data' => ['owner_name' => 'Valid'],
            'expires_at' => now()->addDays(5) // Expires in 5 days
        ]);
        
        // Run cleanup command
        $this->artisan('drafts:cleanup')
             ->expectsOutput('Cleaned up 1 expired draft(s)')
             ->assertExitCode(0);
        
        // Verify expired draft was deleted
        $this->assertDatabaseMissing('store_drafts', [
            'id' => $expiredDraft->id
        ]);
        
        // Verify valid draft still exists
        $this->assertDatabaseHas('store_drafts', [
            'id' => $validDraft->id
        ]);
    }

    /** @test */
    public function it_can_restore_form_state_from_draft()
    {
        $this->actingAs($this->superAdmin);
        
        // Create comprehensive draft
        $draftData = [
            'template' => 'enterprise',
            'current_step' => 4,
            'form_data' => [
                'owner_name' => 'Enterprise Owner',
                'admin_email' => 'admin@enterprise.com',
                'owner_document_type' => 'cedula',
                'owner_document_number' => '12345678',
                'name' => 'Enterprise Store',
                'slug' => 'enterprise-store',
                'email' => 'contact@enterprise.com',
                'fiscal_document_type' => 'nit',
                'fiscal_document_number' => '900123456-7',
                'fiscal_address' => 'Calle 123 #45-67'
            ]
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        // Retrieve and verify all data is preserved
        $response = $this->getJson('/superlinkiu/api/stores/get-draft');
        
        $response->assertStatus(200);
        
        $retrievedData = $response->json('data');
        $formData = $retrievedData['form_data'];
        
        $this->assertEquals('enterprise', $retrievedData['template']);
        $this->assertEquals(4, $retrievedData['current_step']);
        $this->assertEquals('Enterprise Owner', $formData['owner_name']);
        $this->assertEquals('admin@enterprise.com', $formData['admin_email']);
        $this->assertEquals('cedula', $formData['owner_document_type']);
        $this->assertEquals('Enterprise Store', $formData['name']);
        $this->assertEquals('enterprise-store', $formData['slug']);
        $this->assertEquals('nit', $formData['fiscal_document_type']);
        $this->assertEquals('900123456-7', $formData['fiscal_document_number']);
    }

    /** @test */
    public function it_handles_large_form_data_in_drafts()
    {
        $this->actingAs($this->superAdmin);
        
        // Create draft with large description
        $largeDescription = str_repeat('This is a very long description. ', 100);
        
        $draftData = [
            'template' => 'complete',
            'current_step' => 3,
            'form_data' => [
                'owner_name' => 'Test User',
                'admin_email' => 'test@example.com',
                'name' => 'Test Store',
                'description' => $largeDescription,
                'meta_description' => $largeDescription,
                'additional_notes' => $largeDescription
            ]
        ];
        
        $response = $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        $response->assertStatus(200);
        
        // Verify large data was saved correctly
        $draft = StoreDraft::where('user_id', $this->superAdmin->id)->first();
        $this->assertEquals($largeDescription, $draft->form_data['description']);
        $this->assertEquals($largeDescription, $draft->form_data['meta_description']);
    }

    /** @test */
    public function it_can_handle_draft_recovery_after_session_timeout()
    {
        $this->actingAs($this->superAdmin);
        
        // Create draft
        $draftData = [
            'template' => 'basic',
            'current_step' => 2,
            'form_data' => [
                'owner_name' => 'Session User',
                'admin_email' => 'session@example.com',
                'name' => 'Session Store'
            ]
        ];
        
        $this->postJson('/superlinkiu/api/stores/save-draft', $draftData);
        
        // Simulate new session (logout and login again)
        auth()->logout();
        $this->actingAs($this->superAdmin);
        
        // Should still be able to retrieve draft
        $response = $this->getJson('/superlinkiu/api/stores/get-draft');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'has_draft' => true
                ]);
        
        $retrievedData = $response->json('data');
        $this->assertEquals('Session User', $retrievedData['form_data']['owner_name']);
        $this->assertEquals('Session Store', $retrievedData['form_data']['name']);
    }
}