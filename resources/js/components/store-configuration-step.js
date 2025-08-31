/**
 * Store Configuration Step Component
 * 
 * Enhanced step for store configuration with slug auto-generation,
 * availability checking, plan-based feature toggles, and contact information
 * 
 * Requirements: 1.4, 1.5, 2.2, 2.3
 */

function storeConfigurationStep(config = {}) {
    return {
        // Component state
        formData: config.formData || {
            name: '',
            slug: '',
            email: '',
            phone: '',
            description: '',
            status: 'active'
        },
        template: config.template || null,
        plan: config.plan || null,
        errors: config.errors || {},
        
        // Validation state
        validationEngine: null,
        validationHelpers: null,
        validationErrors: [],
        fieldValidationStatus: {},
        slugSuggestions: [],
        
        // UI state
        isValidating: {},
        userModifiedSlug: false,
        
        // Debounce timers
        debounceTimers: {},

        /**
         * Initialize component
         */
        init() {
            this.setupValidation();
            this.setupSlugGeneration();
            this.setupFieldWatchers();
            this.validateInitialData();
        },

        /**
         * Setup validation engine
         */
        setupValidation() {
            if (window.validationEngine) {
                this.validationEngine = window.validationEngine;
            }
            
            if (window.validationHelpers) {
                this.validationHelpers = window.validationHelpers;
            }
        },

        /**
         * Setup automatic slug generation
         */
        setupSlugGeneration() {
            // Watch for name changes to auto-generate slug
            this.$watch('formData.name', (newName) => {
                if (!this.userModifiedSlug && newName && this.canEditSlug()) {
                    this.formData.slug = this.generateSlugFromName(newName);
                    this.validateField('slug', this.formData.slug);
                }
            });
        },

        /**
         * Setup field watchers for validation
         */
        setupFieldWatchers() {
            // Watch slug changes
            this.$watch('formData.slug', (newSlug) => {
                if (newSlug !== this.generateSlugFromName(this.formData.name)) {
                    this.userModifiedSlug = true;
                }
                this.debounceValidation('slug', newSlug);
            });

            // Watch email changes
            this.$watch('formData.email', (newEmail) => {
                if (newEmail) {
                    this.debounceValidation('email', newEmail);
                }
            });
        },

        /**
         * Validate initial data
         */
        validateInitialData() {
            this.$nextTick(() => {
                if (this.formData.slug) {
                    this.validateField('slug', this.formData.slug);
                }
                if (this.formData.email) {
                    this.validateField('email', this.formData.email);
                }
            });
        },

        /**
         * Handle store name input change
         */
        onStoreNameChange(value) {
            this.formData.name = value;
            this.clearFieldError('name');
            
            // Validate name
            if (value.trim()) {
                this.validateField('name', value);
            }
        },

        /**
         * Handle slug input change
         */
        onSlugChange(value) {
            this.formData.slug = value;
            this.clearFieldError('slug');
            this.slugSuggestions = [];
            
            if (value !== this.generateSlugFromName(this.formData.name)) {
                this.userModifiedSlug = true;
            }
        },

        /**
         * Generate slug from store name
         */
        generateSlugFromName(name = '') {
            if (!name) return '';
            
            return name
                .toLowerCase()
                .trim()
                // Replace accented characters
                .replace(/[áàäâāã]/g, 'a')
                .replace(/[éèëêē]/g, 'e')
                .replace(/[íìïîī]/g, 'i')
                .replace(/[óòöôōõ]/g, 'o')
                .replace(/[úùüûū]/g, 'u')
                .replace(/[ñ]/g, 'n')
                .replace(/[ç]/g, 'c')
                // Replace spaces and special characters with hyphens
                .replace(/[^a-z0-9]+/g, '-')
                // Remove multiple consecutive hyphens
                .replace(/-+/g, '-')
                // Remove leading and trailing hyphens
                .replace(/^-+|-+$/g, '');
        },

        /**
         * Generate slug from current name
         */
        generateSlugFromName() {
            if (this.formData.name && this.canEditSlug()) {
                this.formData.slug = this.generateSlugFromName(this.formData.name);
                this.userModifiedSlug = false;
                this.validateField('slug', this.formData.slug);
            }
        },

        /**
         * Check if slug can be edited based on plan
         */
        canEditSlug() {
            return !this.plan || this.plan.allow_custom_slug !== false;
        },

        /**
         * Debounced validation
         */
        debounceValidation(fieldName, value) {
            if (this.debounceTimers[fieldName]) {
                clearTimeout(this.debounceTimers[fieldName]);
            }

            this.debounceTimers[fieldName] = setTimeout(() => {
                this.validateField(fieldName, value);
            }, 500);
        },

        /**
         * Validate individual field
         */
        async validateField(fieldName, value) {
            if (!this.validationEngine) return;

            this.setValidating(fieldName, true);
            this.clearFieldError(fieldName);

            try {
                const result = await this.validationEngine.performValidation(fieldName, value, {
                    storeId: this.formData.id || null
                });

                this.setValidating(fieldName, false);

                if (result.is_valid) {
                    this.setFieldValid(fieldName, true);
                    this.clearFieldError(fieldName);
                } else {
                    this.setFieldValid(fieldName, false);
                    this.setFieldError(fieldName, result.message);
                    
                    // Get suggestions for slug
                    if (fieldName === 'slug') {
                        this.getSuggestionsForSlug(value);
                    }
                }
            } catch (error) {
                this.setValidating(fieldName, false);
                this.setFieldValid(fieldName, false);
                this.setFieldError(fieldName, 'Error de validación. Inténtalo de nuevo.');
            }
        },

        /**
         * Get suggestions for slug
         */
        async getSuggestionsForSlug(slug) {
            if (!this.validationEngine) return;

            try {
                const suggestions = await this.validationEngine.getSuggestionsFor('slug', slug);
                this.slugSuggestions = suggestions.slice(0, 5); // Limit to 5 suggestions
            } catch (error) {
                console.error('Error getting slug suggestions:', error);
                this.slugSuggestions = [];
            }
        },

        /**
         * Select a slug suggestion
         */
        selectSlugSuggestion(suggestion) {
            this.formData.slug = suggestion;
            this.slugSuggestions = [];
            this.userModifiedSlug = true;
            this.validateField('slug', suggestion);
        },

        /**
         * Validate phone number format
         */
        validatePhone(phone) {
            if (!phone) return;
            
            // Basic phone validation for Colombian numbers
            const phoneRegex = /^(\+57\s?)?[0-9\s\-\(\)]{7,15}$/;
            
            if (!phoneRegex.test(phone)) {
                this.setFieldError('phone', 'Formato de teléfono inválido');
            } else {
                this.clearFieldError('phone');
            }
        },

        /**
         * Validate description length
         */
        validateDescription(description) {
            if (description && description.length > 1000) {
                this.setFieldError('description', 'La descripción no puede exceder 1000 caracteres');
            } else {
                this.clearFieldError('description');
            }
        },

        /**
         * Check if field should be shown based on template/plan
         */
        shouldShowField(fieldName) {
            if (!this.template) return true;

            const fieldVisibility = {
                'email': true,
                'phone': this.template.id !== 'basic',
                'description': this.template.id !== 'basic',
                'status': true
            };

            return fieldVisibility[fieldName] !== false;
        },

        /**
         * Check if field is required based on template/plan
         */
        isFieldRequired(fieldName) {
            if (!this.template) return false;

            const requiredFields = {
                'email': this.template.id === 'enterprise',
                'phone': this.template.id === 'enterprise',
                'description': false,
                'status': true
            };

            return requiredFields[fieldName] === true;
        },

        /**
         * Get store name label based on template
         */
        getStoreNameLabel() {
            if (this.template?.id === 'enterprise') {
                return 'Nombre de la Empresa';
            }
            return 'Nombre de la Tienda';
        },

        /**
         * Get store name placeholder based on template
         */
        getStoreNamePlaceholder() {
            if (this.template?.id === 'enterprise') {
                return 'Ej: Empresa ABC S.A.S.';
            }
            return 'Ej: Mi Tienda Online';
        },

        /**
         * Get slug placeholder
         */
        getSlugPlaceholder() {
            if (this.template?.id === 'enterprise') {
                return 'empresa-abc';
            }
            return 'mi-tienda-online';
        },

        /**
         * Get store URL preview
         */
        getStoreUrl() {
            const baseUrl = window.location.origin;
            const slug = this.formData.slug || 'mi-tienda';
            return `${baseUrl}/${slug}`;
        },

        /**
         * Get plan feature value for display
         */
        getPlanFeatureValue(feature) {
            if (!this.plan || !this.plan[feature]) return 'N/A';
            
            const value = this.plan[feature];
            
            if (feature.startsWith('max_') && value === -1) {
                return 'Ilimitado';
            }
            
            if (feature === 'support_level') {
                const levels = {
                    'basic': 'Básico',
                    'standard': 'Estándar',
                    'premium': 'Premium'
                };
                return levels[value] || value;
            }
            
            return value;
        },

        /**
         * Validation state management
         */
        setValidating(fieldName, isValidating) {
            this.isValidating[fieldName] = isValidating;
        },

        setFieldValid(fieldName, isValid) {
            this.fieldValidationStatus[fieldName] = isValid;
        },

        setFieldError(fieldName, message) {
            this.errors[fieldName] = message;
            this.updateValidationErrors();
        },

        clearFieldError(fieldName) {
            delete this.errors[fieldName];
            this.updateValidationErrors();
        },

        hasFieldError(fieldName) {
            return !!this.errors[fieldName];
        },

        getFieldError(fieldName) {
            return this.errors[fieldName] || '';
        },

        isValidating(fieldName) {
            return !!this.isValidating[fieldName];
        },

        isFieldValid(fieldName) {
            return this.fieldValidationStatus[fieldName] === true;
        },

        getFieldHint(fieldName) {
            const hints = {
                'name': this.template?.id === 'enterprise' ? 'Nombre legal de la empresa' : 'Nombre que aparecerá en tu tienda',
                'slug': this.canEditSlug() ? 'URL única para tu tienda (solo letras, números y guiones)' : null
            };
            
            return hints[fieldName] || '';
        },

        getSlugSuggestions() {
            return this.slugSuggestions || [];
        },

        /**
         * Update validation errors array
         */
        updateValidationErrors() {
            this.validationErrors = Object.entries(this.errors).map(([field, message]) => ({
                field,
                label: this.getFieldLabel(field),
                message
            }));
        },

        /**
         * Get field label for error display
         */
        getFieldLabel(fieldName) {
            const labels = {
                'name': 'Nombre de la tienda',
                'slug': 'URL de la tienda',
                'email': 'Email de contacto',
                'phone': 'Teléfono',
                'description': 'Descripción',
                'status': 'Estado'
            };
            
            return labels[fieldName] || fieldName;
        },

        /**
         * Check if step is valid
         */
        isStepValid() {
            const requiredFields = ['name', 'slug'];
            
            // Add conditionally required fields
            if (this.isFieldRequired('email')) {
                requiredFields.push('email');
            }
            if (this.isFieldRequired('phone')) {
                requiredFields.push('phone');
            }

            // Check if all required fields are filled and valid
            for (const field of requiredFields) {
                if (!this.formData[field] || this.hasFieldError(field)) {
                    return false;
                }
            }

            return true;
        },

        /**
         * Get form data for submission
         */
        getFormData() {
            return {
                ...this.formData,
                template_id: this.template?.id || null,
                plan_id: this.plan?.id || null
            };
        },

        /**
         * Clean up component
         */
        destroy() {
            // Clear debounce timers
            Object.values(this.debounceTimers).forEach(timer => {
                clearTimeout(timer);
            });
            this.debounceTimers = {};
        }
    };
}