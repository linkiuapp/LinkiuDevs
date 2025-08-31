/**
 * Dynamic Wizard Step Component
 * 
 * Enhanced wizard step that integrates with dynamic form generation
 * Automatically generates form fields based on template configuration
 * Requirements: 3.2, 3.4
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('dynamicWizardStep', (config = {}) => ({
        // Configuration
        stepId: config.stepId || '',
        stepIndex: config.stepIndex || 0,
        templateId: config.templateId || null,
        isOptional: config.isOptional || false,
        validationRules: config.validationRules || [],
        initialData: config.initialData || {},
        autoSaveInterval: config.autoSaveInterval || 30000,
        
        // State
        fields: [],
        formData: {},
        validationErrors: {},
        templateConfig: null,
        isLoadingFields: false,
        isValidating: false,
        isCompleted: false,
        showOptionalFields: true,
        selectedTemplate: null,
        templateSpecificContent: false,
        
        // Auto-save state
        autoSaveStatus: {
            isVisible: false,
            status: 'idle', // 'idle', 'saving', 'saved', 'error'
            message: ''
        },
        autoSaveTimer: null,

        init() {
            console.log('🎯 Dynamic Wizard Step: Initialized', this.stepId);
            
            // Initialize form data
            this.formData = { ...this.initialData };
            
            // Load template configuration if templateId is provided
            if (this.templateId) {
                this.loadTemplateConfig();
            }
            
            // Set up auto-save
            this.setupAutoSave();
            
            // Listen for template changes
            this.$watch('$parent.selectedTemplate', (templateId) => {
                if (templateId && templateId !== this.templateId) {
                    this.templateId = templateId;
                    this.loadTemplateConfig();
                }
            });

            // Listen for parent wizard events
            document.addEventListener('wizard:validate-step', (event) => {
                if (event.detail.stepId === this.stepId || event.detail.stepIndex === this.stepIndex) {
                    this.validateStep();
                }
            });

            document.addEventListener('wizard:reset-step', (event) => {
                if (event.detail.stepId === this.stepId || event.detail.stepIndex === this.stepIndex) {
                    this.resetStep();
                }
            });
        },

        /**
         * Load template configuration and generate fields
         */
        async loadTemplateConfig() {
            if (!this.templateId) return;

            this.isLoadingFields = true;
            
            try {
                // Get template configuration from API
                const response = await fetch(`/superlinkiu/api/templates/${this.templateId}/config`);
                const result = await response.json();
                
                if (result.success) {
                    this.templateConfig = result.data;
                    this.generateFieldsForStep();
                } else {
                    console.error('Failed to load template config:', result.message);
                    this.fields = [];
                }
            } catch (error) {
                console.error('Error loading template config:', error);
                this.fields = [];
            } finally {
                this.isLoadingFields = false;
            }
        },

        /**
         * Generate fields for current step based on template
         */
        generateFieldsForStep() {
            if (!this.templateConfig || !this.templateConfig.steps) {
                this.fields = [];
                return;
            }

            const step = this.templateConfig.steps.find(s => s.id === this.stepId);
            if (!step) {
                this.fields = [];
                return;
            }

            this.fields = step.fields.map(field => ({
                ...field,
                validation: field.validation || [],
                placeholder: field.placeholder || this.generatePlaceholder(field),
                helpText: field.helpText || null
            }));

            // Initialize form data for new fields
            this.initializeFieldData();

            // Check if this step has template-specific content
            this.templateSpecificContent = this.hasTemplateSpecificContent();

            console.log('🎯 Generated fields for step', this.stepId, ':', this.fields.length);
        },

        /**
         * Initialize form data for fields
         */
        initializeFieldData() {
            this.fields.forEach(field => {
                if (this.formData[field.name] === undefined) {
                    // Use template defaults if available
                    const defaultValue = this.templateConfig?.defaultValues?.[field.name] || field.defaultValue || '';
                    this.formData[field.name] = defaultValue;
                }
            });
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
         * Check if step has template-specific content
         */
        hasTemplateSpecificContent() {
            const stepsWithSpecificContent = ['template-selection', 'review'];
            return stepsWithSpecificContent.includes(this.stepId);
        },

        /**
         * Handle field value change
         */
        handleFieldChange(event) {
            const { fieldName, value } = event;
            
            this.formData[fieldName] = value;
            
            // Clear validation error for this field
            if (this.validationErrors[fieldName]) {
                delete this.validationErrors[fieldName];
            }

            // Trigger auto-save
            this.scheduleAutoSave();

            // Notify parent wizard
            this.$dispatch('step-field-changed', {
                stepId: this.stepId,
                stepIndex: this.stepIndex,
                fieldName: fieldName,
                value: value,
                formData: this.formData
            });

            // Special handling for template selection
            if (fieldName === 'template') {
                this.selectedTemplate = value;
                this.$parent.selectedTemplate = value;
            }
        },

        /**
         * Validate current step
         */
        async validateStep() {
            this.isValidating = true;
            this.validationErrors = {};

            try {
                // Validate required fields
                const requiredFields = this.fields.filter(field => field.required);
                
                for (const field of requiredFields) {
                    const value = this.formData[field.name];
                    
                    if (!value || (typeof value === 'string' && value.trim() === '')) {
                        this.validationErrors[field.name] = `${field.label} es obligatorio`;
                    }
                }

                // Validate field formats and rules
                for (const field of this.fields) {
                    const value = this.formData[field.name];
                    
                    if (value && value.toString().trim() !== '') {
                        const fieldValidation = await this.validateField(field, value);
                        if (!fieldValidation.isValid) {
                            this.validationErrors[field.name] = fieldValidation.message;
                        }
                    }
                }

                // Template-specific validation
                await this.validateTemplateSpecific();

                this.isCompleted = Object.keys(this.validationErrors).length === 0;

                // Notify parent wizard
                this.$dispatch('step-validated', {
                    stepId: this.stepId,
                    stepIndex: this.stepIndex,
                    isValid: this.isCompleted,
                    errors: this.validationErrors,
                    formData: this.formData
                });

                return this.isCompleted;

            } catch (error) {
                console.error('Step validation error:', error);
                this.validationErrors['general'] = 'Error de validación';
                return false;
            } finally {
                this.isValidating = false;
            }
        },

        /**
         * Validate individual field
         */
        async validateField(field, value) {
            // Basic type validation
            switch (field.type) {
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        return { isValid: false, message: 'Formato de email inválido' };
                    }
                    break;

                case 'password':
                    if (value.length < 8) {
                        return { isValid: false, message: 'La contraseña debe tener al menos 8 caracteres' };
                    }
                    break;

                case 'tel':
                    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{7,}$/;
                    if (!phoneRegex.test(value)) {
                        return { isValid: false, message: 'Formato de teléfono inválido' };
                    }
                    break;

                case 'url':
                    try {
                        new URL(value);
                    } catch {
                        return { isValid: false, message: 'Formato de URL inválido' };
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
         * Template-specific validation
         */
        async validateTemplateSpecific() {
            // Validate template selection step
            if (this.stepId === 'template-selection') {
                if (!this.formData.template) {
                    this.validationErrors['template'] = 'Debes seleccionar una plantilla';
                }
                if (!this.formData.plan_id) {
                    this.validationErrors['plan_id'] = 'Debes seleccionar un plan';
                }
            }

            // Add more template-specific validations as needed
        },

        /**
         * Setup auto-save functionality
         */
        setupAutoSave() {
            // Watch for form data changes
            this.$watch('formData', () => {
                this.scheduleAutoSave();
            }, { deep: true });
        },

        /**
         * Schedule auto-save
         */
        scheduleAutoSave() {
            // Clear existing timer
            if (this.autoSaveTimer) {
                clearTimeout(this.autoSaveTimer);
            }

            // Schedule new auto-save
            this.autoSaveTimer = setTimeout(() => {
                this.performAutoSave();
            }, this.autoSaveInterval);
        },

        /**
         * Perform auto-save
         */
        async performAutoSave() {
            if (Object.keys(this.formData).length === 0) return;

            this.autoSaveStatus = {
                isVisible: true,
                status: 'saving',
                message: 'Guardando borrador...'
            };

            try {
                // Here you would make an API call to save the draft
                // For now, we'll just simulate it
                await new Promise(resolve => setTimeout(resolve, 1000));

                this.autoSaveStatus = {
                    isVisible: true,
                    status: 'saved',
                    message: 'Borrador guardado automáticamente'
                };

                // Hide status after 3 seconds
                setTimeout(() => {
                    this.autoSaveStatus.isVisible = false;
                }, 3000);

            } catch (error) {
                console.error('Auto-save error:', error);
                
                this.autoSaveStatus = {
                    isVisible: true,
                    status: 'error',
                    message: 'Error al guardar borrador'
                };

                // Hide status after 5 seconds
                setTimeout(() => {
                    this.autoSaveStatus.isVisible = false;
                }, 5000);
            }
        },

        /**
         * Reset step
         */
        resetStep() {
            this.formData = { ...this.initialData };
            this.validationErrors = {};
            this.isCompleted = false;
            this.initializeFieldData();
        },

        /**
         * Check if step has errors
         */
        hasErrors() {
            return Object.keys(this.validationErrors).length > 0;
        },

        /**
         * Get completed fields count
         */
        getCompletedFieldsCount() {
            return this.fields.filter(field => {
                const value = this.formData[field.name];
                return value && value.toString().trim() !== '';
            }).length;
        },

        /**
         * Get required fields count
         */
        getRequiredFieldsCount() {
            return this.fields.filter(field => field.required).length;
        },

        /**
         * Get form data for submission
         */
        getFormData() {
            const data = {};
            
            this.fields.forEach(field => {
                const value = this.formData[field.name];
                if (value !== undefined && value !== '') {
                    data[field.name] = value;
                }
            });

            return data;
        },

        /**
         * Check if step is ready for navigation
         */
        canNavigateNext() {
            return this.isCompleted && !this.hasErrors();
        },

        /**
         * Get step completion percentage
         */
        getCompletionPercentage() {
            const requiredFields = this.getRequiredFieldsCount();
            const completedFields = this.getCompletedFieldsCount();
            
            if (requiredFields === 0) return 100;
            
            return Math.round((completedFields / requiredFields) * 100);
        }
    }));
});

console.log('🎯 Dynamic Wizard Step Component: Loaded');