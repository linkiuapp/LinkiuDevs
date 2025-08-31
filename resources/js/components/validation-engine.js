/**
 * ValidationEngine - Real-time validation system with debouncing and caching
 */
class ValidationEngine {
    constructor(options = {}) {
        this.options = {
            debounceDelay: 500,
            cacheTimeout: 300000, // 5 minutes
            baseUrl: '/superlinkiu/api/stores',
            showLoadingToasts: false, // Disable for validation requests
            ...options
        };
        
        this.cache = new Map();
        this.debounceTimers = new Map();
        this.validationCallbacks = new Map();
        this.errorDisplays = new Map();
        this.loadingManager = null;
        
        this.init();
    }

    init() {
        // Initialize CSRF token for requests
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Get manager instances
        this.loadingManager = window.loadingManager;
        this.animationManager = window.animationManager;
        
        // Set up global error handler
        this.setupGlobalErrorHandler();
    }

    /**
     * Validate a field with debouncing
     */
    validateField(fieldName, value, options = {}) {
        return new Promise((resolve, reject) => {
            // Clear existing timer for this field
            if (this.debounceTimers.has(fieldName)) {
                clearTimeout(this.debounceTimers.get(fieldName));
            }

            // Set up debounced validation
            const timer = setTimeout(async () => {
                try {
                    const result = await this.performValidation(fieldName, value, options);
                    resolve(result);
                } catch (error) {
                    reject(error);
                }
            }, this.options.debounceDelay);

            this.debounceTimers.set(fieldName, timer);
        });
    }

    /**
     * Perform the actual validation request
     */
    async performValidation(fieldName, value, options = {}) {
        // Check cache first
        const cacheKey = `${fieldName}:${value}:${JSON.stringify(options)}`;
        const cached = this.getCachedResult(cacheKey);
        
        if (cached) {
            return cached;
        }

        let endpoint, payload;

        switch (fieldName) {
            case 'email':
            case 'admin_email':
                endpoint = '/validate-email';
                payload = { 
                    email: value,
                    store_id: options.storeId 
                };
                break;

            case 'slug':
                endpoint = '/validate-slug';
                payload = { 
                    slug: value,
                    store_id: options.storeId 
                };
                break;

            default:
                throw new Error(`Validation not implemented for field: ${fieldName}`);
        }

        try {
            const response = await this.makeRequest(endpoint, payload);
            
            if (response.success) {
                // Cache the result
                this.setCachedResult(cacheKey, response.data);
                return response.data;
            } else {
                throw new Error(response.message || 'Validation failed');
            }
        } catch (error) {
            console.error('Validation error:', error);
            throw error;
        }
    }

    /**
     * Get suggestions for validation errors
     */
    async getSuggestionsFor(fieldName, value, options = {}) {
        try {
            let suggestions = [];

            switch (fieldName) {
                case 'slug':
                    const slugResponse = await this.makeRequest('/suggest-slug', { slug: value });
                    if (slugResponse.success) {
                        suggestions = slugResponse.data.suggestions || [];
                    }
                    break;

                case 'email':
                case 'admin_email':
                    const emailResponse = await this.makeRequest('/suggest-email-domain', { email: value });
                    if (emailResponse.success) {
                        suggestions = emailResponse.data.suggestions || [];
                    }
                    break;

                default:
                    // Try to get validation-specific suggestions
                    const validationResponse = await this.makeRequest('/validation-suggestions', {
                        field: fieldName,
                        value: value,
                        error_type: options.errorType || 'general'
                    });
                    if (validationResponse.success) {
                        suggestions = validationResponse.data.suggestions
                            .filter(s => s.type === 'replacement')
                            .map(s => s.value);
                    }
                    break;
            }
            
            return suggestions;
        } catch (error) {
            console.error('Error getting suggestions:', error);
            return [];
        }
    }

    /**
     * Calculate billing information
     */
    async calculateBilling(planId, billingPeriod, discountCode = null) {
        try {
            const response = await this.makeRequest('/calculate-billing', {
                plan_id: planId,
                billing_period: billingPeriod,
                discount_code: discountCode
            });
            
            if (response.success) {
                return response.data;
            } else {
                throw new Error(response.message || 'Billing calculation failed');
            }
        } catch (error) {
            console.error('Billing calculation error:', error);
            throw error;
        }
    }

    /**
     * Display validation error for a field with enhanced styling
     */
    displayError(fieldName, message, suggestions = [], severity = 'error') {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // Remove existing error display
        this.clearError(fieldName);

        // Add enhanced error class to field with animation
        field.classList.add('field-invalid');
        field.classList.remove('field-valid', 'field-validating');
        field.setAttribute('aria-invalid', 'true');
        field.setAttribute('aria-describedby', `${fieldName}-error`);
        
        // Animate validation error
        if (this.animationManager) {
            this.animationManager.animateValidationError(field);
        }

        // Create enhanced error message element
        const errorElement = document.createElement('div');
        errorElement.className = `validation-error ${severity === 'severe' ? 'severe' : ''}`;
        errorElement.id = `${fieldName}-error`;
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
                </div>
            </div>
        `;

        // Add suggestions if provided
        if (suggestions.length > 0) {
            const suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'suggestions-container';
            
            suggestionsContainer.innerHTML = `
                <div class="suggestions-label">Sugerencias:</div>
                <div class="suggestions-buttons">
                    ${suggestions.map(suggestion => `
                        <button type="button" class="suggestion-button" data-suggestion="${suggestion}">
                            ${suggestion}
                        </button>
                    `).join('')}
                </div>
            `;

            // Add click handlers for suggestions with animation
            suggestionsContainer.querySelectorAll('.suggestion-button').forEach(button => {
                button.addEventListener('click', () => {
                    const suggestion = button.dataset.suggestion;
                    
                    // Animate suggestion selection
                    if (this.animationManager) {
                        this.animationManager.animateSuggestionSelect(button, field);
                    }
                    
                    field.value = suggestion;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    this.clearError(fieldName);
                });
            });

            errorElement.appendChild(suggestionsContainer);
        }

        // Insert error element after the field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
        
        // Add error icon to field
        this.addFieldIcon(field, 'error');
        
        // Store reference for cleanup
        this.errorDisplays.set(fieldName, errorElement);
    }

    /**
     * Display validation success for a field with enhanced styling
     */
    displaySuccess(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // Remove error styling and add success styling
        field.classList.remove('field-invalid', 'field-validating');
        field.classList.add('field-valid');
        field.setAttribute('aria-invalid', 'false');
        field.removeAttribute('aria-describedby');

        // Clear any existing error messages
        this.clearError(fieldName);
        
        // Add success icon
        this.addFieldIcon(field, 'success');
    }

    /**
     * Clear validation error for a field with enhanced cleanup
     */
    clearError(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.remove('field-valid', 'field-invalid', 'field-validating', 'field-warning');
            field.removeAttribute('aria-invalid');
            field.removeAttribute('aria-describedby');
        }

        // Remove error message element with fade out animation
        const errorElement = this.errorDisplays.get(fieldName);
        if (errorElement && errorElement.parentNode) {
            errorElement.style.animation = 'fadeOut 0.3s ease-in forwards';
            setTimeout(() => {
                if (errorElement.parentNode) {
                    errorElement.parentNode.removeChild(errorElement);
                }
            }, 300);
            this.errorDisplays.delete(fieldName);
        }

        // Remove field icons
        this.removeFieldIcon(field);
    }

    /**
     * Set up real-time validation for a field
     */
    setupFieldValidation(fieldName, options = {}) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) {
            console.warn(`Field not found: ${fieldName}`);
            return;
        }

        // Store validation options
        this.validationCallbacks.set(fieldName, options);

        // Set up event listeners
        field.addEventListener('input', async (event) => {
            const value = event.target.value.trim();
            
            // Clear previous validation state
            this.clearError(fieldName);
            
            // Skip validation for empty values unless required
            if (!value && !options.required) {
                return;
            }

            // Show enhanced loading state
            this.showLoadingState(fieldName);

            try {
                const result = await this.validateField(fieldName, value, options);
                
                this.hideLoadingState(fieldName);
                
                if (result.is_valid) {
                    this.displaySuccess(fieldName);
                    
                    // Show success toast for important validations
                    if (this.loadingManager && (fieldName === 'slug' || fieldName === 'email')) {
                        this.loadingManager.showToast(
                            `${this.getFieldLabel(fieldName)} disponible`,
                            'success',
                            2000
                        );
                    }
                } else {
                    // Get suggestions if available
                    let suggestions = [];
                    if (fieldName === 'slug' && !result.is_valid) {
                        // Show loading for suggestions
                        const suggestionLoadingId = this.loadingManager?.showInlineLoading(
                            field.parentNode.querySelector('.validation-error') || field.parentNode,
                            'Buscando alternativas'
                        );
                        
                        suggestions = await this.getSuggestionsFor(fieldName, value, options);
                        
                        if (suggestionLoadingId) {
                            this.loadingManager.hideInlineLoading(suggestionLoadingId);
                        }
                    }
                    
                    this.displayError(fieldName, result.message, suggestions);
                }
            } catch (error) {
                this.hideLoadingState(fieldName);
                this.displayError(fieldName, 'Error de validación. Inténtalo de nuevo.');
                
                // Show error toast
                if (this.loadingManager) {
                    this.loadingManager.showToast(
                        'Error al validar el campo. Verifica tu conexión.',
                        'error'
                    );
                }
            }
        });

        // Clear validation on focus
        field.addEventListener('focus', () => {
            if (field.classList.contains('border-red-500')) {
                this.clearError(fieldName);
            }
        });
    }

    /**
     * Show enhanced loading state for a field
     */
    showLoadingState(fieldName, withBackdrop = false) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // Add loading class and field state
        field.classList.add('validation-loading', 'field-validating');
        field.classList.remove('field-valid', 'field-invalid');
        
        // Remove existing icons
        this.removeFieldIcon(field);
        
        // Add spinner
        let spinner = field.parentNode.querySelector('.validation-spinner');
        if (!spinner) {
            spinner = document.createElement('div');
            spinner.className = `validation-spinner ${withBackdrop ? 'with-backdrop' : ''}`;
            spinner.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            `;
            
            // Make sure parent has relative positioning
            if (getComputedStyle(field.parentNode).position === 'static') {
                field.parentNode.style.position = 'relative';
            }
            
            field.parentNode.appendChild(spinner);
        }
    }

    /**
     * Hide loading state for a field
     */
    hideLoadingState(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        field.classList.remove('validation-loading', 'field-validating');
        
        const spinner = field.parentNode.querySelector('.validation-spinner');
        if (spinner) {
            spinner.style.animation = 'fadeOut 0.2s ease-in forwards';
            setTimeout(() => {
                if (spinner.parentNode) {
                    spinner.remove();
                }
            }, 200);
        }
    }

    /**
     * Add validation icon to field
     */
    addFieldIcon(field, type) {
        // Remove existing icons first
        this.removeFieldIcon(field);

        let icon;
        if (type === 'success') {
            icon = document.createElement('div');
            icon.className = 'validation-success';
            icon.innerHTML = `
                <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
        } else if (type === 'error') {
            icon = document.createElement('div');
            icon.className = 'validation-error-icon';
            icon.innerHTML = `
                <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            `;
        }

        if (icon) {
            // Make sure parent has relative positioning
            if (getComputedStyle(field.parentNode).position === 'static') {
                field.parentNode.style.position = 'relative';
            }
            field.parentNode.appendChild(icon);
        }
    }

    /**
     * Remove validation icons from field
     */
    removeFieldIcon(field) {
        const icons = field.parentNode.querySelectorAll('.validation-success, .validation-error-icon');
        icons.forEach(icon => {
            if (icon.parentNode) {
                icon.remove();
            }
        });
    }

    /**
     * Make HTTP request to validation endpoint
     */
    async makeRequest(endpoint, payload) {
        const url = `${this.options.baseUrl}${endpoint}`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Cache management
     */
    getCachedResult(key) {
        const cached = this.cache.get(key);
        if (cached && Date.now() - cached.timestamp < this.options.cacheTimeout) {
            return cached.data;
        }
        
        // Remove expired cache entry
        if (cached) {
            this.cache.delete(key);
        }
        
        return null;
    }

    setCachedResult(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }

    /**
     * Clear all cached results
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Set up global error handler
     */
    setupGlobalErrorHandler() {
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.message && event.reason.message.includes('validation')) {
                console.error('Unhandled validation error:', event.reason);
                // Optionally show a global error message
            }
        });
    }

    /**
     * Validate multiple fields at once with progress feedback
     */
    async validateStep(stepData, options = {}) {
        const results = {};
        const promises = [];
        const fieldNames = Object.keys(stepData).filter(name => this.validationCallbacks.has(name));
        
        // Show progress if there are multiple fields
        let progressId = null;
        if (fieldNames.length > 1 && this.loadingManager) {
            const progressContainer = document.createElement('div');
            progressContainer.className = 'validation-progress mb-4';
            
            // Find a good place to show progress
            const firstField = document.querySelector(`[name="${fieldNames[0]}"]`);
            if (firstField) {
                const stepContainer = firstField.closest('.wizard-step') || firstField.closest('form');
                if (stepContainer) {
                    stepContainer.insertBefore(progressContainer, stepContainer.firstChild);
                }
            }
            
            progressId = this.loadingManager.createProgressBar(progressContainer, {
                label: 'Validando campos...',
                showPercentage: true
            });
        }

        let completedCount = 0;
        
        for (const [fieldName, value] of Object.entries(stepData)) {
            if (this.validationCallbacks.has(fieldName)) {
                const fieldOptions = this.validationCallbacks.get(fieldName);
                promises.push(
                    this.performValidation(fieldName, value, { ...fieldOptions, ...options })
                        .then(result => {
                            completedCount++;
                            if (progressId) {
                                const percentage = (completedCount / fieldNames.length) * 100;
                                this.loadingManager.updateProgress(progressId, percentage);
                            }
                            return { fieldName, result };
                        })
                        .catch(error => {
                            completedCount++;
                            if (progressId) {
                                const percentage = (completedCount / fieldNames.length) * 100;
                                this.loadingManager.updateProgress(progressId, percentage, 'error');
                            }
                            return { fieldName, error };
                        })
                );
            }
        }

        const validationResults = await Promise.all(promises);
        
        validationResults.forEach(({ fieldName, result, error }) => {
            if (error) {
                results[fieldName] = { is_valid: false, message: 'Error de validación' };
            } else {
                results[fieldName] = result;
            }
        });

        // Clean up progress bar
        if (progressId) {
            setTimeout(() => {
                const progressContainer = document.querySelector('.validation-progress');
                if (progressContainer && progressContainer.parentNode) {
                    progressContainer.parentNode.removeChild(progressContainer);
                }
            }, 2000);
        }

        return results;
    }

    /**
     * Get field label for display
     */
    getFieldLabel(fieldName) {
        const labels = {
            'email': 'Email',
            'admin_email': 'Email del administrador',
            'slug': 'URL de la tienda',
            'name': 'Nombre',
            'store_name': 'Nombre de la tienda'
        };
        return labels[fieldName] || fieldName;
    }

    /**
     * Clean up resources
     */
    destroy() {
        // Clear all timers
        this.debounceTimers.forEach(timer => clearTimeout(timer));
        this.debounceTimers.clear();
        
        // Clear cache
        this.clearCache();
        
        // Clear error displays
        this.errorDisplays.forEach((element, fieldName) => {
            this.clearError(fieldName);
        });
        
        // Clear callbacks
        this.validationCallbacks.clear();
    }
}

// Export for use in other modules
window.ValidationEngine = ValidationEngine;

// Auto-initialize if DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Make ValidationEngine available globally
    if (!window.validationEngine) {
        window.validationEngine = new ValidationEngine();
    }
});