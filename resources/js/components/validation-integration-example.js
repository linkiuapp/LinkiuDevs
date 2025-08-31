/**
 * Integration Example - How to use the validation system in store creation forms
 */

// Example of how to integrate all validation components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize validation engine
    const validationEngine = new ValidationEngine({
        debounceDelay: 500,
        cacheTimeout: 300000,
        baseUrl: '/superlinkiu/api/stores'
    });

    // Initialize suggestion system
    const suggestionSystem = new SuggestionSystem({
        debounceDelay: 300,
        maxSuggestions: 5,
        baseUrl: '/superlinkiu/api/stores'
    });

    // Initialize validation helpers
    const validationHelpers = new ValidationHelpers(validationEngine);

    // Set up store creation validation
    validationHelpers.setupStoreCreationValidation();

    // Set up suggestion system for store creation
    suggestionSystem.setupStoreCreationSuggestions();

    // Example: Custom validation setup for specific fields
    validationEngine.setupFieldValidation('admin_email', {
        required: true,
        storeId: null // Set to store ID for updates
    });

    validationEngine.setupFieldValidation('slug', {
        required: true,
        storeId: null // Set to store ID for updates
    });

    // Example: Cross-field validation
    validationHelpers.setupCrossFieldValidation([
        {
            sourceField: 'plan_id',
            targetFields: ['slug'],
            condition: (planId) => {
                // Re-validate slug when plan changes (affects slug permissions)
                return planId !== '';
            }
        }
    ]);

    // Example: Form step validation
    const validateStepButton = document.querySelector('[data-validate-step]');
    if (validateStepButton) {
        validateStepButton.addEventListener('click', async function() {
            const stepElement = this.closest('.wizard-step');
            const isValid = await validationHelpers.validateFormStep(stepElement);
            
            if (isValid) {
                console.log('Step is valid, can proceed');
                // Enable next button or proceed to next step
            } else {
                console.log('Step has validation errors');
                // Show errors and prevent navigation
            }
        });
    }

    // Example: Manual validation trigger
    const manualValidateButton = document.querySelector('[data-manual-validate]');
    if (manualValidateButton) {
        manualValidateButton.addEventListener('click', async function() {
            const fieldName = this.dataset.field;
            const field = document.querySelector(`[name="${fieldName}"]`);
            
            if (field && field.value) {
                try {
                    const result = await validationEngine.validateField(fieldName, field.value);
                    
                    if (result.is_valid) {
                        validationEngine.displaySuccess(fieldName);
                    } else {
                        const suggestions = await validationEngine.getSuggestionsFor(fieldName, field.value);
                        validationEngine.displayError(fieldName, result.message, suggestions);
                    }
                } catch (error) {
                    console.error('Manual validation error:', error);
                }
            }
        });
    }

    // Example: Billing calculation trigger
    const calculateBillingButton = document.querySelector('[data-calculate-billing]');
    if (calculateBillingButton) {
        calculateBillingButton.addEventListener('click', async function() {
            const planId = document.querySelector('[name="plan_id"]')?.value;
            const period = document.querySelector('[name="billing_period"]')?.value;
            const discountCode = document.querySelector('[name="discount_code"]')?.value;
            
            if (planId && period) {
                try {
                    const billing = await validationEngine.calculateBilling(planId, period, discountCode);
                    validationHelpers.displayBillingPreview(billing);
                } catch (error) {
                    console.error('Billing calculation error:', error);
                }
            }
        });
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        validationEngine.destroy();
        suggestionSystem.destroy();
        validationHelpers.destroy();
    });
});

// Example usage in Blade templates:
/*
<!-- Include the validation components -->
<script src="{{ asset('js/components/validation-engine.js') }}"></script>
<script src="{{ asset('js/components/validation-helpers.js') }}"></script>
<script src="{{ asset('js/components/suggestion-system.js') }}"></script>
<script src="{{ asset('js/components/validation-integration-example.js') }}"></script>

<!-- Or use the Blade component -->
<x-superlinkiu::validation-engine :fields="['admin_email', 'slug', 'name']">
    <form>
        <div class="form-field">
            <label for="admin_email">Email del Administrador</label>
            <input type="email" name="admin_email" id="admin_email" required>
        </div>
        
        <div class="form-field">
            <label for="slug">URL de la Tienda</label>
            <input type="text" name="slug" id="slug" required>
        </div>
        
        <button type="button" data-validate-step>Validar Paso</button>
        <button type="button" data-manual-validate data-field="admin_email">Validar Email</button>
        <button type="button" data-calculate-billing>Calcular Facturación</button>
    </form>
</x-superlinkiu::validation-engine>
*/