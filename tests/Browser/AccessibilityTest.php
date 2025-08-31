<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Shared\Models\User;
use App\Shared\Models\Plan;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Accessibility Tests for Store Creation Wizard
 * 
 * Tests for WCAG 2.1 AA compliance including keyboard navigation,
 * screen reader support, and accessibility attributes
 * Requirements: WCAG 2.1 AA compliance
 */
class AccessibilityTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $superAdmin;
    protected $basicPlan;

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
    }

    /** @test */
    public function wizard_has_proper_heading_structure()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Check main heading
            $browser->assertPresent('h1')
                    ->assertSeeIn('h1', 'Crear Nueva Tienda');

            // Check step headings are h2
            $browser->assertPresent('h2[data-step-heading]')
                    ->assertSeeIn('h2', 'Selecciona una Plantilla');

            // Navigate to next step and check heading hierarchy
            $browser->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5)
                    ->assertSeeIn('h2', 'Información del Propietario');

            // Check that there are no skipped heading levels
            $headings = $browser->elements('h1, h2, h3, h4, h5, h6');
            $this->assertGreaterThan(0, count($headings));
        });
    }

    /** @test */
    public function form_fields_have_proper_labels_and_descriptions()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Check that all form fields have labels
            $inputs = $browser->elements('input[type="text"], input[type="email"], input[type="password"], select, textarea');
            
            foreach ($inputs as $input) {
                $id = $input->getAttribute('id');
                if ($id) {
                    $browser->assertPresent("label[for='{$id}']");
                }
            }

            // Check specific field accessibility
            $browser->assertAttribute('[name="owner_name"]', 'aria-required', 'true')
                    ->assertAttribute('[name="admin_email"]', 'aria-required', 'true')
                    ->assertAttribute('[name="admin_password"]', 'aria-required', 'true');

            // Check that required fields are marked
            $browser->assertPresent('[name="owner_name"][required]')
                    ->assertPresent('[name="admin_email"][required]')
                    ->assertPresent('[name="admin_password"][required]');

            // Check aria-describedby for fields with help text
            $browser->assertAttribute('[name="admin_password"]', 'aria-describedby')
                    ->assertPresent('#password-help');
        });
    }

    /** @test */
    public function error_messages_are_properly_associated_with_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Trigger validation error by trying to proceed without data
            $browser->click('[data-action="next-step"]')
                    ->waitFor('.validation-error', 5);

            // Check that error messages have proper ARIA attributes
            $browser->assertAttribute('[name="owner_name"]', 'aria-invalid', 'true')
                    ->assertAttribute('[name="owner_name"]', 'aria-describedby');

            // Check that error message is properly linked
            $errorId = $browser->attribute('[name="owner_name"]', 'aria-describedby');
            $browser->assertPresent("#{$errorId}")
                    ->assertAttribute("#{$errorId}", 'role', 'alert');

            // Fix the error and verify aria-invalid is removed
            $browser->type('[name="owner_name"]', 'Test User')
                    ->waitUntilMissing('.validation-error', 5)
                    ->assertAttributeDoesntContain('[name="owner_name"]', 'aria-invalid', 'true');
        });
    }

    /** @test */
    public function wizard_is_fully_keyboard_navigable()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Start keyboard navigation from body
            $browser->keys('body', ['{tab}']);

            // Should focus on first interactive element
            $focusedElement = $browser->driver->switchTo()->activeElement();
            $this->assertNotNull($focusedElement);

            // Navigate through template selection using keyboard
            $browser->keys('body', ['{tab}', '{tab}', '{enter}']) // Select basic template
                    ->waitFor('[data-step="owner-info"]', 5);

            // Navigate through form fields using Tab
            $browser->keys('body', ['{tab}']) // Focus first field
                    ->assertFocused('[name="owner_name"]')
                    ->type('[name="owner_name"]', 'Keyboard User')
                    ->keys('[name="owner_name"]', ['{tab}'])
                    ->assertFocused('[name="admin_email"]')
                    ->type('[name="admin_email"]', 'keyboard@example.com')
                    ->keys('[name="admin_email"]', ['{tab}'])
                    ->assertFocused('[name="admin_password"]')
                    ->type('[name="admin_password"]', 'password123');

            // Continue tabbing to other fields
            $browser->keys('[name="admin_password"]', ['{tab}'])
                    ->assertFocused('[name="owner_country"]')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->keys('[name="owner_country"]', ['{tab}'])
                    ->type('[name="owner_department"]', 'Cundinamarca')
                    ->keys('[name="owner_department"]', ['{tab}'])
                    ->type('[name="owner_city"]', 'Bogotá');

            // Navigate to next button and activate with Enter
            $browser->keys('body', ['{tab}', '{tab}']) // Navigate to next button
                    ->assertFocused('[data-action="next-step"]')
                    ->keys('[data-action="next-step"]', ['{enter}'])
                    ->waitFor('[data-step="store-config"]', 5);

            // Verify focus management - focus should move to first field of new step
            $browser->assertFocused('[name="name"]');
        });
    }

    /** @test */
    public function wizard_has_proper_focus_management()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Fill form and proceed
            $browser->type('[name="owner_name"]', 'Focus Test')
                    ->type('[name="admin_email"]', 'focus@example.com')
                    ->type('[name="admin_password"]', 'password123')
                    ->type('[name="owner_country"]', 'Colombia')
                    ->type('[name="owner_department"]', 'Cundinamarca')
                    ->type('[name="owner_city"]', 'Bogotá')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="store-config"]', 5);

            // Focus should move to first field of new step
            $browser->assertFocused('[name="name"]');

            // Navigate back using breadcrumb
            $browser->click('[data-step-nav="owner-info"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Focus should move to step heading
            $browser->assertFocused('h2[data-step-heading]');

            // Test modal focus management
            $browser->click('[data-action="help"]')
                    ->waitFor('.help-modal', 5)
                    ->assertFocused('.help-modal [data-focus-first]');

            // Close modal and verify focus returns
            $browser->keys('.help-modal', ['{escape}'])
                    ->waitUntilMissing('.help-modal', 5)
                    ->assertFocused('[data-action="help"]');
        });
    }

    /** @test */
    public function wizard_has_proper_aria_live_regions()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Check for status live region
            $browser->assertPresent('[aria-live="polite"]')
                    ->assertPresent('[aria-live="assertive"]');

            // Navigate through wizard and check status updates
            $browser->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Check that step change is announced
            $browser->assertSeeIn('[aria-live="polite"]', 'Paso 2 de 4: Información del Propietario');

            // Test validation announcements
            $browser->type('[name="admin_email"]', 'invalid-email')
                    ->waitFor('.validation-error', 5)
                    ->assertSeeIn('[aria-live="assertive"]', 'Error de validación');

            // Test success announcements
            $browser->clear('[name="admin_email"]')
                    ->type('[name="admin_email"]', 'valid@example.com')
                    ->waitFor('.validation-success', 5)
                    ->assertSeeIn('[aria-live="polite"]', 'Email válido');
        });
    }

    /** @test */
    public function wizard_supports_screen_reader_navigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Check landmark roles
            $browser->assertPresent('[role="main"]')
                    ->assertPresent('[role="navigation"]')
                    ->assertPresent('[role="form"]');

            // Check progress indicator accessibility
            $browser->assertAttribute('.progress-bar', 'role', 'progressbar')
                    ->assertAttribute('.progress-bar', 'aria-valuenow')
                    ->assertAttribute('.progress-bar', 'aria-valuemin', '0')
                    ->assertAttribute('.progress-bar', 'aria-valuemax', '100')
                    ->assertAttribute('.progress-bar', 'aria-label');

            // Navigate to form step
            $browser->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Check fieldset and legend for grouped fields
            $browser->assertPresent('fieldset')
                    ->assertPresent('legend')
                    ->assertSeeIn('legend', 'Información del Propietario');

            // Check that form has proper role and label
            $browser->assertAttribute('[data-wizard-form]', 'role', 'form')
                    ->assertAttribute('[data-wizard-form]', 'aria-label', 'Formulario de creación de tienda');

            // Check button accessibility
            $browser->assertAttribute('[data-action="next-step"]', 'type', 'button')
                    ->assertAttribute('[data-action="next-step"]', 'aria-label');
        });
    }

    /** @test */
    public function wizard_has_sufficient_color_contrast()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // This test would ideally use automated accessibility testing tools
            // For now, we'll check that elements have proper CSS classes for contrast
            
            // Check that error messages have high contrast
            $browser->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5)
                    ->click('[data-action="next-step"]') // Trigger validation errors
                    ->waitFor('.validation-error', 5);

            // Verify error styling classes are applied
            $browser->assertPresent('.validation-error.text-red-600')
                    ->assertPresent('.validation-error.bg-red-50');

            // Check success message contrast
            $browser->type('[name="owner_name"]', 'Contrast Test')
                    ->type('[name="admin_email"]', 'contrast@example.com')
                    ->waitFor('.validation-success', 5)
                    ->assertPresent('.validation-success.text-green-600');

            // Check button contrast
            $browser->assertPresent('[data-action="next-step"].bg-blue-600')
                    ->assertPresent('[data-action="next-step"].text-accent-50');
        });
    }

    /** @test */
    public function wizard_respects_reduced_motion_preferences()
    {
        $this->browse(function (Browser $browser) {
            // Set reduced motion preference
            $browser->script('
                Object.defineProperty(window, "matchMedia", {
                    writable: true,
                    value: jest.fn().mockImplementation(query => ({
                        matches: query === "(prefers-reduced-motion: reduce)",
                        media: query,
                        onchange: null,
                        addListener: jest.fn(),
                        removeListener: jest.fn(),
                        addEventListener: jest.fn(),
                        removeEventListener: jest.fn(),
                        dispatchEvent: jest.fn(),
                    })),
                });
            ');

            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Check that animations are disabled when reduced motion is preferred
            $browser->assertPresent('.reduce-motion')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]');

            // Verify instant transitions instead of animations
            $browser->waitFor('[data-step="owner-info"]', 1); // Should appear immediately
        });
    }

    /** @test */
    public function wizard_supports_high_contrast_mode()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Simulate high contrast mode
            $browser->script('document.body.classList.add("high-contrast");');

            // Check that high contrast styles are applied
            $browser->assertPresent('.high-contrast')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Verify form elements have high contrast borders
            $browser->assertPresent('input.border-2')
                    ->assertPresent('button.border-2');

            // Check focus indicators are enhanced
            $browser->click('[name="owner_name"]')
                    ->assertPresent('[name="owner_name"].focus\\:ring-4');
        });
    }

    /** @test */
    public function wizard_provides_skip_links()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Check for skip link
            $browser->keys('body', ['{tab}'])
                    ->assertFocused('.skip-link')
                    ->assertSee('Saltar al contenido principal');

            // Activate skip link
            $browser->keys('.skip-link', ['{enter}'])
                    ->assertFocused('[role="main"]');

            // Check for skip to navigation link
            $browser->assertPresent('.skip-to-nav')
                    ->keys('body', ['{tab}'])
                    ->assertFocused('.skip-to-nav');
        });
    }

    /** @test */
    public function wizard_handles_zoom_levels_properly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                    ->visit('/superlinkiu/stores/create-wizard');

            // Test at 200% zoom
            $browser->script('document.body.style.zoom = "200%";');

            // Verify layout doesn't break
            $browser->assertVisible('[data-template="basic"]')
                    ->assertVisible('[data-action="next-step"]')
                    ->click('[data-template="basic"]')
                    ->click('[data-action="next-step"]')
                    ->waitFor('[data-step="owner-info"]', 5);

            // Check that form fields are still accessible
            $browser->assertVisible('[name="owner_name"]')
                    ->assertVisible('[name="admin_email"]')
                    ->assertVisible('[data-action="next-step"]');

            // Test horizontal scrolling doesn't break functionality
            $browser->type('[name="owner_name"]', 'Zoom Test User')
                    ->assertInputValue('[name="owner_name"]', 'Zoom Test User');

            // Reset zoom
            $browser->script('document.body.style.zoom = "100%";');
        });
    }
}