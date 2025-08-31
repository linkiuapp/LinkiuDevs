/**
 * Dynamic Form Generator Component
 * 
 * Generates form fields dynamically based on template configuration
 * Supports conditional field display, dependencies, and validation
 * Requirements: 3.2, 3.4
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('dynamicFormGenerator', (config = {}) => ({
        // Configuration
        templateId: config.templateId || null,
        stepId: config.stepId || null,
        fields: config.fields || [],
        formData: config.formData || {},
        validationErrors: config.validationErrors || {},
        showOptionalFields: config.showOptionalFields !== false,
        
        // State
        fieldSuccesses: {},
        passwordVisible: {},
        fieldValidationCache: {},
        
        // Computed properties
        get visibleFields() {
            return this.fields.filter(field => {
                if (!field.required && !this.showOptionalFields) {
                    return false;
                }
                return this.isFieldVisible(field);
            });
        },

        init() {
            console.log('🎯 Dynamic Form Generator: Initialized for template', this.templateId);
            
            // Initialize form data for all fields
            this.initializeFormData();
            
            // Set up watchers for dependent fields
            this.setupFieldWatchers();
            
            // Initialize password visibility state
            this.initializePasswordVisibility();
        },

        /**
         * Initialize form data with default values
         */
        initializeFormData() {
            this.fields.forEach(field => {
                if (this.formData[field.name] === undefined) {
                    this.formData[field.name] = field.defaultValue || '';
                }
            });
        },

        /**
         * Set up watchers for field dependencies
         */
        setupFieldWatchers() {
            this.fields.forEach(field => {
                if (field.dependsOn) {
                    this.$watch(`formData.${field.dependsOn.field}`, (value) => {
                        this.handleFieldDependency(field, value);
                    });
                }
            });
        },

        /**
         * Initialize password visibility state
         */
        initializePasswordVisibility() {
            this.fields.forEach(field => {
                if (field.type === 'password') {
                    this.passwordVisible[field.name] = false;
                }
            });
        },

        /**
         * Check if field is visible based on dependencies
         */
        isFieldVisible(field) {
            if (!field.dependsOn) {
                return true;
            }

            const dependency = field.dependsOn;
            const dependentValue = this.formData[dependency.field];

            switch (dependency.condition) {
                case 'equals':
                    return dependentValue === dependency.value;
                case 'not_equals':
                    return dependentValue !== dependency.value;
                case 'in':
                    return Array.isArray(dependency.value) && dependency.value.includes(dependentValue);
                case 'not_in':
                    return Array.isArray(dependency.value) && !dependency.value.includes(dependentValue);
                case 'not_empty':
                    return dependentValue && dependentValue.toString().trim() !== '';
                case 'empty':
                    return !dependentValue || dependentValue.toString().trim() === '';
                default:
                    return true;
            }
        },

        /**
         * Check if field is disabled
         */
        isFieldDisabled(field) {
            if (field.disabled) {
                return true;
            }

            // Check if field should be disabled based on dependencies
            if (field.dependsOn && field.dependsOn.action === 'disable') {
                return this.isFieldVisible(field);
            }

            return false;
        },

        /**
         * Handle field dependency changes
         */
        handleFieldDependency(field, dependentValue) {
            if (!field.dependsOn) return;

            const isVisible = this.isFieldVisible(field);
            
            // Clear field value if it becomes hidden
            if (!isVisible && this.formData[field.name]) {
                this.formData[field.name] = '';
                this.clearFieldValidation(field.name);
            }

            // Auto-fill field if it becomes visible and has auto-fill value
            if (isVisible && field.dependsOn.autoFill) {
                this.formData[field.name] = field.dependsOn.autoFill;
            }
        },

        /**
         * Handle field value change
         */
        handleFieldChange(fieldName, value) {
            this.formData[fieldName] = value;
            
            // Clear validation errors when user starts typing
            if (this.validationErrors[fieldName]) {
                delete this.validationErrors[fieldName];
            }

            // Clear success state
            if (this.fieldSuccesses[fieldName]) {
                delete this.fieldSuccesses[fieldName];
            }

            // Trigger field validation after a delay
            clearTimeout(this.fieldValidationCache[fieldName]);
            this.fieldValidationCache[fieldName] = setTimeout(() => {
                this.validateField(fieldName);
            }, 500);

            // Notify parent component
            this.$dispatch('field-changed', {
                fieldName: fieldName,
                value: value,
                formData: this.formData
            });
        },

        /**
         * Validate individual field
         */
        async validateField(fieldName) {
            const field = this.fields.find(f => f.name === fieldName);
            if (!field) return;

            const value = this.formData[fieldName];
            
            // Skip validation if field is not visible
            if (!this.isFieldVisible(field)) {
                return;
            }

            try {
                // Client-side validation
                const clientValidation = this.validateFieldClient(field, value);
                if (!clientValidation.isValid) {
                    this.validationErrors[fieldName] = clientValidation.message;
                    return;
                }

                // Server-side validation for specific fields
                if (this.needsServerValidation(field)) {
                    const serverValidation = await this.validateFieldServer(field, value);
                    if (!serverValidation.isValid) {
                        this.validationErrors[fieldName] = serverValidation.message;
                        return;
                    }
                }

                // Field is valid
                this.clearFieldValidation(fieldName);
                this.fieldSuccesses[fieldName] = 'Válido';

            } catch (error) {
                console.error('Field validation error:', error);
                this.validationErrors[fieldName] = 'Error de validación';
            }
        },

        /**
         * Client-side field validation
         */
        validateFieldClient(field, value) {
            // Required field validation
            if (field.required && (!value || value.toString().trim() === '')) {
                return {
                    isValid: false,
                    message: `${field.label} es obligatorio`
                };
            }

            // Skip further validation if field is empty and not required
            if (!value || value.toString().trim() === '') {
                return { isValid: true };
            }

            // Type-specific validation
            switch (field.type) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        return {
                            isValid: false,
                            message: 'Formato de email inválido'
                        };
                    }
                    break;

                case 'password':
                    if (value.length < 8) {
                        return {
                            isValid: false,
                            message: 'La contraseña debe tener al menos 8 caracteres'
                        };
                    }
                    break;

                case 'tel':
                    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{7,}$/;
                    if (!phoneRegex.test(value)) {
                        return {
                            isValid: false,
                            message: 'Formato de teléfono inválido'
                        };
                    }
                    break;

                case 'url':
                    try {
                        new URL(value);
                    } catch {
                        return {
                            isValid: false,
                            message: 'Formato de URL inválido'
                        };
                    }
                    break;
            }

            // Custom validation rules
            if (field.validation) {
                for (const rule of field.validation) {
                    const ruleValidation = this.validateRule(rule, value, field);
                    if (!ruleValidation.isValid) {
                        return ruleValidation;
                    }
                }
            }

            // Length validation
            if (field.maxlength && value.length > field.maxlength) {
                return {
                    isValid: false,
                    message: `Máximo ${field.maxlength} caracteres`
                };
            }

            if (field.minlength && value.length < field.minlength) {
                return {
                    isValid: false,
                    message: `Mínimo ${field.minlength} caracteres`
                };
            }

            return { isValid: true };
        },

        /**
         * Validate custom rule
         */
        validateRule(rule, value, field) {
            switch (rule.type) {
                case 'pattern':
                    const regex = new RegExp(rule.pattern);
                    if (!regex.test(value)) {
                        return {
                            isValid: false,
                            message: rule.message || `${field.label} no tiene el formato correcto`
                        };
                    }
                    break;

                case 'min':
                    if (parseFloat(value) < rule.value) {
                        return {
                            isValid: false,
                            message: rule.message || `Valor mínimo: ${rule.value}`
                        };
                    }
                    break;

                case 'max':
                    if (parseFloat(value) > rule.value) {
                        return {
                            isValid: false,
                            message: rule.message || `Valor máximo: ${rule.value}`
                        };
                    }
                    break;
            }

            return { isValid: true };
        },

        /**
         * Check if field needs server validation
         */
        needsServerValidation(field) {
            const serverValidationFields = ['admin_email', 'slug', 'email'];
            return serverValidationFields.includes(field.name);
        },

        /**
         * Server-side field validation
         */
        async validateFieldServer(field, value) {
            // This would make API calls to validate fields like email uniqueness, slug availability
            // For now, return valid to avoid API calls during development
            return { isValid: true };
        },

        /**
         * Clear field validation
         */
        clearFieldValidation(fieldName) {
            delete this.validationErrors[fieldName];
            delete this.fieldSuccesses[fieldName];
        },

        /**
         * Check if field has error
         */
        hasFieldError(fieldName) {
            return !!this.validationErrors[fieldName];
        },

        /**
         * Get field error message
         */
        getFieldError(fieldName) {
            return this.validationErrors[fieldName] || '';
        },

        /**
         * Check if field has success state
         */
        hasFieldSuccess(fieldName) {
            return !!this.fieldSuccesses[fieldName];
        },

        /**
         * Get field success message
         */
        getFieldSuccess(fieldName) {
            return this.fieldSuccesses[fieldName] || '';
        },

        /**
         * Get character count for field
         */
        getCharacterCount(fieldName) {
            const value = this.formData[fieldName] || '';
            return value.toString().length;
        },

        /**
         * Toggle password visibility
         */
        togglePasswordVisibility(fieldName) {
            this.passwordVisible[fieldName] = !this.passwordVisible[fieldName];
        },

        /**
         * Toggle optional fields visibility
         */
        toggleOptionalFields() {
            this.showOptionalFields = !this.showOptionalFields;
        },

        /**
         * Check if there are optional fields
         */
        hasOptionalFields() {
            return this.fields.some(field => !field.required);
        },

        /**
         * Check if there are dependent fields
         */
        hasDependentFields() {
            return this.fields.some(field => field.dependsOn);
        },

        /**
         * Validate all visible fields
         */
        async validateAllFields() {
            const validationPromises = this.visibleFields.map(field => 
                this.validateField(field.name)
            );

            await Promise.all(validationPromises);

            return Object.keys(this.validationErrors).length === 0;
        },

        /**
         * Get form data for submission
         */
        getFormData() {
            const data = {};
            
            this.visibleFields.forEach(field => {
                if (this.formData[field.name] !== undefined && this.formData[field.name] !== '') {
                    data[field.name] = this.formData[field.name];
                }
            });

            return data;
        },

        /**
         * Reset form
         */
        resetForm() {
            this.formData = {};
            this.validationErrors = {};
            this.fieldSuccesses = {};
            this.initializeFormData();
        }
    }));

    // Autocomplete field component
    Alpine.data('autocompleteField', (field) => ({
        searchQuery: '',
        suggestions: [],
        showSuggestions: false,
        selectedIndex: -1,
        isLoading: false,

        init() {
            // Initialize search query with current value
            this.searchQuery = this.$parent.formData[field.name] || '';
        },

        async handleAutocompleteInput(query) {
            this.searchQuery = query;
            this.$parent.handleFieldChange(field.name, query);

            if (query.length < (field.autoComplete?.minChars || 2)) {
                this.hideSuggestions();
                return;
            }

            this.isLoading = true;
            
            try {
                this.suggestions = await this.fetchSuggestions(query);
                this.showSuggestions = this.suggestions.length > 0;
                this.selectedIndex = -1;
            } catch (error) {
                console.error('Autocomplete error:', error);
                this.suggestions = [];
                this.showSuggestions = false;
            } finally {
                this.isLoading = false;
            }
        },

        async fetchSuggestions(query) {
            // Mock suggestions for development
            // In production, this would make API calls
            const mockSuggestions = {
                'owner_country': ['Colombia', 'Venezuela', 'Ecuador', 'Perú', 'Brasil'],
                'owner_department': ['Antioquia', 'Cundinamarca', 'Valle del Cauca', 'Atlántico'],
                'owner_city': ['Medellín', 'Bogotá', 'Cali', 'Barranquilla', 'Cartagena']
            };

            const suggestions = mockSuggestions[field.name] || [];
            return suggestions.filter(item => 
                item.toLowerCase().includes(query.toLowerCase())
            ).slice(0, 10);
        },

        handleAutocompleteFocus() {
            if (this.suggestions.length > 0) {
                this.showSuggestions = true;
            }
        },

        handleAutocompleteBlur() {
            // Delay hiding to allow click on suggestions
            setTimeout(() => {
                this.hideSuggestions();
            }, 200);
        },

        selectNext() {
            if (this.selectedIndex < this.suggestions.length - 1) {
                this.selectedIndex++;
            }
        },

        selectPrevious() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
            }
        },

        selectCurrent() {
            if (this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
                this.selectSuggestion(this.suggestions[this.selectedIndex]);
            }
        },

        selectSuggestion(suggestion) {
            const value = suggestion.value || suggestion;
            this.searchQuery = value;
            this.$parent.handleFieldChange(field.name, value);
            this.hideSuggestions();
        },

        hideSuggestions() {
            this.showSuggestions = false;
            this.selectedIndex = -1;
        }
    }));
});

/**
 * Dynamic Form Generator Utilities
 */
window.DynamicFormUtils = {
    /**
     * Create field configuration from template
     */
    createFieldsFromTemplate(template, stepId) {
        const step = template.steps?.find(s => s.id === stepId);
        if (!step) return [];

        return step.fields.map(field => ({
            ...field,
            validation: field.validation || [],
            placeholder: field.placeholder || this.generatePlaceholder(field),
            helpText: field.helpText || null
        }));
    },

    /**
     * Generate placeholder text for field
     */
    generatePlaceholder(field) {
        const placeholders = {
            'text': `Ingresa ${field.label.toLowerCase()}`,
            'email': 'ejemplo@correo.com',
            'password': 'Mínimo 8 caracteres',
            'tel': '+57 300 123 4567',
            'url': 'https://ejemplo.com',
            'number': '0'
        };

        return placeholders[field.type] || '';
    },

    /**
     * Validate field configuration
     */
    validateFieldConfig(field) {
        const required = ['name', 'label', 'type'];
        const missing = required.filter(prop => !field[prop]);
        
        if (missing.length > 0) {
            throw new Error(`Field missing required properties: ${missing.join(', ')}`);
        }

        return true;
    }
};

console.log('🎯 Dynamic Form Generator Component: Loaded');