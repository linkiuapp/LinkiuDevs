/**
 * Validation Helpers - Utility functions for common validation scenarios
 */

class ValidationHelpers {
    constructor(validationEngine) {
        this.engine = validationEngine || window.validationEngine;
        
        if (!this.engine) {
            console.error('ValidationEngine not found. Make sure it is loaded before ValidationHelpers.');
            return;
        }

        // Enhanced error tracking and display
        this.fieldLabels = {
            'owner.name': 'Nombre del propietario',
            'owner.email': 'Email del propietario',
            'owner.document_number': 'Número de documento',
            'owner.country': 'País',
            'owner.department': 'Departamento',
            'owner.city': 'Ciudad',
            'owner.password': 'Contraseña',
            'store.name': 'Nombre de la tienda',
            'store.slug': 'URL de la tienda',
            'store.email': 'Email de la tienda',
            'store.phone': 'Teléfono',
            'fiscal.document_number': 'NIT/Documento fiscal',
            'fiscal.country': 'País (fiscal)',
            'fiscal.department': 'Departamento (fiscal)',
            'fiscal.city': 'Ciudad (fiscal)',
            'fiscal.address': 'Dirección fiscal'
        };

        this.fieldHelp = {
            'owner.email': {
                title: 'Email del propietario',
                description: 'Este será el email principal para acceder al panel de administración de la tienda.'
            },
            'owner.document_number': {
                title: 'Número de documento',
                description: 'Ingresa el número de cédula, NIT o pasaporte según el tipo seleccionado.'
            },
            'owner.password': {
                title: 'Contraseña segura',
                description: 'Debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.'
            },
            'store.slug': {
                title: 'URL de la tienda',
                description: 'Esta será la dirección web de la tienda. Solo puede contener letras, números y guiones.'
            },
            'store.email': {
                title: 'Email de contacto',
                description: 'Email público que aparecerá en la tienda para que los clientes puedan contactar.'
            },
            'fiscal.document_number': {
                title: 'NIT o documento fiscal',
                description: 'Número de identificación tributaria para facturación empresarial.'
            }
        };

        this.errorSummaryPanel = null;
        this.currentErrors = new Map();
        this.helpTooltips = new Map();
        
        this.init();
    }

    init() {
        this.createErrorSummaryPanel();
        this.setupGlobalErrorTracking();
        this.enhanceValidationEngine();
    }

    /**
     * Set up validation for store creation form
     */
    setupStoreCreationValidation(options = {}) {
        const defaultOptions = {
            storeId: null, // For updates
            ...options
        };

        // Email validation
        this.engine.setupFieldValidation('admin_email', {
            required: true,
            storeId: defaultOptions.storeId
        });

        // Store email validation (if different from admin email)
        this.engine.setupFieldValidation('email', {
            required: false,
            storeId: defaultOptions.storeId
        });

        // Slug validation
        this.engine.setupFieldValidation('slug', {
            required: true,
            storeId: defaultOptions.storeId
        });

        // Set up slug auto-generation from store name
        this.setupSlugAutoGeneration();

        // Set up billing calculation
        this.setupBillingCalculation();
    }

    /**
     * Set up automatic slug generation from store name
     */
    setupSlugAutoGeneration() {
        const nameField = document.querySelector('[name="name"]');
        const slugField = document.querySelector('[name="slug"]');
        
        if (!nameField || !slugField) return;

        let userModifiedSlug = false;

        // Track if user manually modified slug
        slugField.addEventListener('input', () => {
            userModifiedSlug = true;
        });

        // Auto-generate slug from name
        nameField.addEventListener('input', (event) => {
            if (!userModifiedSlug && event.target.value.trim()) {
                const generatedSlug = this.generateSlugFromName(event.target.value);
                slugField.value = generatedSlug;
                
                // Trigger validation for the generated slug
                slugField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        // Reset flag when name is cleared
        nameField.addEventListener('input', (event) => {
            if (!event.target.value.trim()) {
                userModifiedSlug = false;
            }
        });
    }

    /**
     * Generate slug from store name
     */
    generateSlugFromName(name) {
        return name
            .toLowerCase()
            .trim()
            // Replace accented characters
            .replace(/[áàäâā]/g, 'a')
            .replace(/[éèëêē]/g, 'e')
            .replace(/[íìïîī]/g, 'i')
            .replace(/[óòöôō]/g, 'o')
            .replace(/[úùüûū]/g, 'u')
            .replace(/[ñ]/g, 'n')
            .replace(/[ç]/g, 'c')
            // Replace spaces and special characters with hyphens
            .replace(/[^a-z0-9]+/g, '-')
            // Remove multiple consecutive hyphens
            .replace(/-+/g, '-')
            // Remove leading and trailing hyphens
            .replace(/^-+|-+$/g, '');
    }

    /**
     * Set up billing calculation based on plan and period selection
     */
    setupBillingCalculation() {
        const planField = document.querySelector('[name="plan_id"]');
        const periodField = document.querySelector('[name="billing_period"]');
        const discountField = document.querySelector('[name="discount_code"]');
        
        if (!planField || !periodField) return;

        const calculateAndDisplay = async () => {
            const planId = planField.value;
            const period = periodField.value;
            const discountCode = discountField?.value || null;

            if (!planId || !period) return;

            try {
                const billing = await this.engine.calculateBilling(planId, period, discountCode);
                this.displayBillingPreview(billing);
            } catch (error) {
                console.error('Error calculating billing:', error);
                this.clearBillingPreview();
            }
        };

        // Set up event listeners
        planField.addEventListener('change', calculateAndDisplay);
        periodField.addEventListener('change', calculateAndDisplay);
        
        if (discountField) {
            discountField.addEventListener('input', this.debounce(calculateAndDisplay, 1000));
        }

        // Initial calculation if values are present
        if (planField.value && periodField.value) {
            calculateAndDisplay();
        }
    }

    /**
     * Display billing preview
     */
    displayBillingPreview(billing) {
        let previewContainer = document.querySelector('.billing-preview');
        
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.className = 'billing-preview mt-4 p-4 bg-gray-50 rounded-lg border';
            
            // Find a good place to insert the preview
            const billingSection = document.querySelector('[name="billing_period"]')?.closest('.form-group') || 
                                 document.querySelector('[name="plan_id"]')?.closest('.form-group');
            
            if (billingSection) {
                billingSection.parentNode.insertBefore(previewContainer, billingSection.nextSibling);
            }
        }

        const { plan, billing: billingData } = billing;
        
        previewContainer.innerHTML = `
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Resumen de Facturación</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Plan:</span>
                    <span class="font-medium">${plan.name}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Período:</span>
                    <span class="font-medium">${this.formatBillingPeriod(billingData.period)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium">$${this.formatCurrency(billingData.base_amount)}</span>
                </div>
                ${billingData.discount_amount > 0 ? `
                <div class="flex justify-between text-green-600">
                    <span>Descuento (${billingData.discount_percentage}%):</span>
                    <span>-$${this.formatCurrency(billingData.discount_amount)}</span>
                </div>
                ` : ''}
                <div class="flex justify-between">
                    <span class="text-gray-600">IVA (19%):</span>
                    <span class="font-medium">$${this.formatCurrency(billingData.tax)}</span>
                </div>
                <div class="flex justify-between border-t pt-2 font-semibold">
                    <span>Total:</span>
                    <span>$${this.formatCurrency(billingData.total)} ${billingData.currency}</span>
                </div>
                <div class="text-xs text-gray-500 mt-2">
                    Próxima facturación: ${this.formatDate(billingData.next_billing_date)}
                </div>
            </div>
        `;
    }

    /**
     * Clear billing preview
     */
    clearBillingPreview() {
        const previewContainer = document.querySelector('.billing-preview');
        if (previewContainer) {
            previewContainer.remove();
        }
    }

    /**
     * Set up cross-field validation dependencies
     */
    setupCrossFieldValidation(dependencies) {
        dependencies.forEach(({ sourceField, targetFields, condition }) => {
            const source = document.querySelector(`[name="${sourceField}"]`);
            if (!source) return;

            source.addEventListener('change', () => {
                const sourceValue = source.value;
                
                targetFields.forEach(targetField => {
                    const target = document.querySelector(`[name="${targetField}"]`);
                    if (!target) return;

                    if (condition(sourceValue)) {
                        // Re-validate target field
                        if (target.value) {
                            target.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                });
            });
        });
    }

    /**
     * Validate entire form step
     */
    async validateFormStep(stepElement) {
        const formData = new FormData(stepElement.closest('form'));
        const stepData = {};
        
        // Extract data for fields in this step
        const stepFields = stepElement.querySelectorAll('input, select, textarea');
        stepFields.forEach(field => {
            if (field.name) {
                stepData[field.name] = field.value;
            }
        });

        try {
            const results = await this.engine.validateStep(stepData);
            
            let isValid = true;
            for (const [fieldName, result] of Object.entries(results)) {
                if (!result.is_valid) {
                    isValid = false;
                    this.engine.displayError(fieldName, result.message);
                } else {
                    this.engine.displaySuccess(fieldName);
                }
            }
            
            return isValid;
        } catch (error) {
            console.error('Step validation error:', error);
            return false;
        }
    }

    /**
     * Utility functions
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('es-CO').format(amount);
    }

    formatBillingPeriod(period) {
        const periods = {
            'monthly': 'Mensual',
            'quarterly': 'Trimestral',
            'biannual': 'Semestral'
        };
        return periods[period] || period;
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('es-CO');
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Create error summary panel
     */
    createErrorSummaryPanel() {
        if (this.errorSummaryPanel) return;

        this.errorSummaryPanel = document.createElement('div');
        this.errorSummaryPanel.className = 'error-summary-panel hidden';
        this.errorSummaryPanel.innerHTML = `
            <div class="panel-header">
                <div class="panel-title">Errores de validación</div>
                <button type="button" class="close-button" aria-label="Cerrar panel de errores">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="error-list"></div>
        `;

        // Add close functionality
        this.errorSummaryPanel.querySelector('.close-button').addEventListener('click', () => {
            this.hideErrorSummary();
        });

        document.body.appendChild(this.errorSummaryPanel);
    }

    /**
     * Setup global error tracking
     */
    setupGlobalErrorTracking() {
        // Override the engine's displayError method to track errors
        if (this.engine && this.engine.displayError) {
            const originalDisplayError = this.engine.displayError.bind(this.engine);
            
            this.engine.displayError = (fieldName, message, suggestions = []) => {
                // Call original method
                originalDisplayError(fieldName, message, suggestions);
                
                // Track error for summary
                this.trackError(fieldName, message);
                this.updateErrorSummary();
            };

            const originalClearError = this.engine.clearError.bind(this.engine);
            
            this.engine.clearError = (fieldName) => {
                // Call original method
                originalClearError(fieldName);
                
                // Remove from tracking
                this.clearTrackedError(fieldName);
                this.updateErrorSummary();
            };
        }
    }

    /**
     * Enhance validation engine with better error display
     */
    enhanceValidationEngine() {
        if (!this.engine) return;

        // Override displayError to add enhanced styling
        const originalDisplayError = this.engine.displayError.bind(this.engine);
        
        this.engine.displayError = (fieldName, message, suggestions = [], severity = 'error') => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (!field) return;

            // Remove existing error display
            this.engine.clearError(fieldName);

            // Add enhanced error class to field
            field.classList.add('field-invalid');
            field.classList.remove('field-valid', 'field-validating');
            field.setAttribute('aria-invalid', 'true');

            // Create enhanced error message element
            const errorElement = document.createElement('div');
            errorElement.className = `validation-error ${severity === 'severe' ? 'severe' : ''}`;
            errorElement.setAttribute('role', 'alert');
            errorElement.setAttribute('aria-live', 'polite');
            
            errorElement.innerHTML = `
                <div class="flex items-start">
                    <div class="error-icon">
                        <svg class="w-4 h-4 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="error-message">${message}</div>
                        ${this.getFieldHelp(fieldName) ? `<div class="error-details">💡 ${this.getFieldHelp(fieldName)}</div>` : ''}
                    </div>
                </div>
                ${suggestions.length > 0 ? this.createSuggestionsHTML(fieldName, suggestions) : ''}
            `;

            // Insert error element after the field
            field.parentNode.insertBefore(errorElement, field.nextSibling);
            
            // Store reference for cleanup
            this.engine.errorDisplays.set(fieldName, errorElement);

            // Track error for summary
            this.trackError(fieldName, message);
            this.updateErrorSummary();
        };
    }

    /**
     * Create suggestions HTML
     */
    createSuggestionsHTML(fieldName, suggestions) {
        return `
            <div class="suggestions-container">
                <div class="suggestions-label">Sugerencias:</div>
                <div class="suggestions-buttons">
                    ${suggestions.map(suggestion => `
                        <button type="button" class="suggestion-button" data-field="${fieldName}" data-suggestion="${suggestion}">
                            ${suggestion}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
    }

    /**
     * Track error for summary panel
     */
    trackError(fieldName, message) {
        this.currentErrors.set(fieldName, {
            field: fieldName,
            label: this.fieldLabels[fieldName] || fieldName,
            message: message,
            timestamp: Date.now()
        });
    }

    /**
     * Clear tracked error
     */
    clearTrackedError(fieldName) {
        this.currentErrors.delete(fieldName);
    }

    /**
     * Update error summary panel
     */
    updateErrorSummary() {
        if (!this.errorSummaryPanel) return;

        const errorList = this.errorSummaryPanel.querySelector('.error-list');
        
        if (this.currentErrors.size === 0) {
            this.hideErrorSummary();
            return;
        }

        // Build error list HTML
        const errorsHTML = Array.from(this.currentErrors.values())
            .sort((a, b) => a.timestamp - b.timestamp)
            .map(error => `
                <div class="error-item">
                    <div class="field-name">${error.label}:</div>
                    <div class="error-text">${error.message}</div>
                    <button type="button" class="goto-field" data-field="${error.field}">
                        Ir al campo
                    </button>
                </div>
            `).join('');

        errorList.innerHTML = errorsHTML;

        // Add click handlers for "go to field" buttons
        errorList.querySelectorAll('.goto-field').forEach(button => {
            button.addEventListener('click', (e) => {
                const fieldName = e.target.dataset.field;
                this.scrollToField(fieldName);
            });
        });

        this.showErrorSummary();
    }

    /**
     * Show error summary panel
     */
    showErrorSummary() {
        if (this.errorSummaryPanel) {
            this.errorSummaryPanel.classList.remove('hidden');
        }
    }

    /**
     * Hide error summary panel
     */
    hideErrorSummary() {
        if (this.errorSummaryPanel) {
            this.errorSummaryPanel.classList.add('hidden');
        }
    }

    /**
     * Scroll to field with error
     */
    scrollToField(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            field.focus();
            
            // Add temporary highlight
            field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.5)';
            setTimeout(() => {
                field.style.boxShadow = '';
            }, 2000);
        }
    }

    /**
     * Add contextual help tooltip to field
     */
    addHelpTooltip(fieldName, helpConfig = null) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        const help = helpConfig || this.fieldHelp[fieldName];
        if (!help) return;

        // Find or create label
        let label = field.closest('.form-group')?.querySelector('label') || 
                   document.querySelector(`label[for="${field.id}"]`);
        
        if (!label) return;

        // Check if tooltip already exists
        if (this.helpTooltips.has(fieldName)) return;

        // Create help tooltip
        const helpTooltip = document.createElement('div');
        helpTooltip.className = 'help-tooltip ml-1';
        helpTooltip.innerHTML = `
            <button type="button" class="tooltip-trigger" aria-label="Ayuda para ${help.title}">
                ?
            </button>
            <div class="tooltip-content">
                <div class="tooltip-title">${help.title}</div>
                <div class="tooltip-description">${help.description}</div>
            </div>
        `;

        // Make label container flex if not already
        if (!label.classList.contains('field-label-with-help')) {
            label.classList.add('field-label-with-help');
        }

        label.appendChild(helpTooltip);
        this.helpTooltips.set(fieldName, helpTooltip);
    }

    /**
     * Get field help text
     */
    getFieldHelp(fieldName) {
        const help = this.fieldHelp[fieldName];
        return help ? help.description : null;
    }

    /**
     * Setup contextual help for all fields
     */
    setupContextualHelp() {
        Object.keys(this.fieldHelp).forEach(fieldName => {
            this.addHelpTooltip(fieldName);
        });
    }

    /**
     * Enhanced field highlighting with animations
     */
    highlightField(fieldName, type = 'error') {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // Remove existing classes
        field.classList.remove('field-valid', 'field-invalid', 'field-validating', 'field-warning');
        
        // Add appropriate class
        field.classList.add(`field-${type}`);

        // Add icon
        this.addFieldIcon(field, type);
    }

    /**
     * Add validation icon to field
     */
    addFieldIcon(field, type) {
        // Remove existing icons
        const existingIcon = field.parentNode.querySelector('.validation-success, .validation-error-icon, .validation-spinner');
        if (existingIcon) {
            existingIcon.remove();
        }

        if (type === 'valid') {
            const icon = document.createElement('div');
            icon.className = 'validation-success';
            icon.innerHTML = `
                <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
            field.parentNode.appendChild(icon);
        } else if (type === 'invalid') {
            const icon = document.createElement('div');
            icon.className = 'validation-error-icon';
            icon.innerHTML = `
                <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            `;
            field.parentNode.appendChild(icon);
        }
    }

    /**
     * Clean up all validation helpers
     */
    destroy() {
        this.clearBillingPreview();
        
        // Remove error summary panel
        if (this.errorSummaryPanel && this.errorSummaryPanel.parentNode) {
            this.errorSummaryPanel.parentNode.removeChild(this.errorSummaryPanel);
        }

        // Remove help tooltips
        this.helpTooltips.forEach((tooltip, fieldName) => {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        });
        this.helpTooltips.clear();

        // Clear error tracking
        this.currentErrors.clear();
        
        if (this.engine) {
            this.engine.destroy();
        }
    }
}

// Export for use in other modules
window.ValidationHelpers = ValidationHelpers;

// Auto-initialize if ValidationEngine is available
document.addEventListener('DOMContentLoaded', () => {
    if (window.validationEngine && !window.validationHelpers) {
        window.validationHelpers = new ValidationHelpers(window.validationEngine);
    }
});