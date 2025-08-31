/**
 * WizardStep Base Component
 * 
 * Abstract step component for consistent behavior across wizard steps
 * Includes step validation interface and error handling
 * Implements auto-save functionality with 30-second intervals
 * Provides step completion tracking system
 * 
 * Requirements: 1.2, 5.2
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('wizardStep', (config = {}) => ({
        // Configuration
        stepId: config.stepId || '',
        stepIndex: config.stepIndex || 0,
        isOptional: config.isOptional || false,
        validationRules: config.validationRules || {},
        autoSaveInterval: config.autoSaveInterval || 30000, // 30 seconds
        
        // State management
        formData: config.initialData || {},
        originalData: {},
        isDirty: false,
        isValid: false,
        isValidating: false,
        validationErrors: {},
        isCompleted: false,
        
        // Auto-save state
        autoSaveTimer: null,
        lastSaveTime: null,
        isSaving: false,
        saveStatus: 'idle', // idle, saving, saved, error
        
        init() {
            console.log('🪄 WizardStep: Initialized step', this.stepId, 'at index', this.stepIndex);
            
            // Store original data for dirty checking
            this.originalData = JSON.parse(JSON.stringify(this.formData));
            
            // Set up auto-save
            this.setupAutoSave();
            
            // Set up form change listeners
            this.setupFormListeners();
            
            // Initial validation
            this.validateStep();
            
            // Listen for wizard events
            this.setupWizardListeners();
        },
        
        destroy() {
            this.clearAutoSaveTimer();
        },
        
        setupAutoSave() {
            if (this.autoSaveInterval > 0) {
                this.autoSaveTimer = setInterval(() => {
                    if (this.isDirty && !this.isSaving) {
                        this.autoSave();
                    }
                }, this.autoSaveInterval);
                
                console.log('🪄 WizardStep: Auto-save enabled with', this.autoSaveInterval / 1000, 'second interval');
            }
        },
        
        clearAutoSaveTimer() {
            if (this.autoSaveTimer) {
                clearInterval(this.autoSaveTimer);
                this.autoSaveTimer = null;
            }
        },
        
        setupFormListeners() {
            // Listen for form input changes
            this.$el.addEventListener('input', (event) => {
                if (event.target.matches('input, select, textarea')) {
                    this.handleFieldChange(event.target);
                }
            });
            
            // Listen for form changes
            this.$el.addEventListener('change', (event) => {
                if (event.target.matches('input, select, textarea')) {
                    this.handleFieldChange(event.target);
                }
            });
        },
        
        setupWizardListeners() {
            // Listen for validation requests from wizard
            this.$el.addEventListener('wizard:validate-step', (event) => {
                if (event.detail.stepIndex === this.stepIndex) {
                    this.validateStep().then(result => {
                        event.detail.callback(result);
                    });
                }
            });
            
            // Listen for step activation
            this.$el.addEventListener('wizard:step-changed', (event) => {
                if (event.detail.currentStep === this.stepIndex) {
                    this.onStepActivated();
                } else if (event.detail.previousStep === this.stepIndex) {
                    this.onStepDeactivated();
                }
            });
        },
        
        handleFieldChange(field) {
            const name = field.name;
            const value = this.getFieldValue(field);
            
            // Update form data
            this.setFieldValue(name, value);
            
            // Mark as dirty
            this.markDirty();
            
            // Validate field
            this.validateField(name, value);
            
            // Clear field error if value is valid
            if (this.validationErrors[name] && this.isFieldValid(name, value)) {
                delete this.validationErrors[name];
            }
        },
        
        getFieldValue(field) {
            switch (field.type) {
                case 'checkbox':
                    return field.checked;
                case 'radio':
                    return field.checked ? field.value : null;
                case 'number':
                    return field.value ? parseFloat(field.value) : null;
                default:
                    return field.value;
            }
        },
        
        setFieldValue(name, value) {
            // Support nested object notation (e.g., 'owner.name')
            const keys = name.split('.');
            let obj = this.formData;
            
            for (let i = 0; i < keys.length - 1; i++) {
                if (!obj[keys[i]]) {
                    obj[keys[i]] = {};
                }
                obj = obj[keys[i]];
            }
            
            obj[keys[keys.length - 1]] = value;
        },
        
        getFieldValue(name) {
            // Support nested object notation
            const keys = name.split('.');
            let obj = this.formData;
            
            for (const key of keys) {
                if (obj && typeof obj === 'object' && key in obj) {
                    obj = obj[key];
                } else {
                    return null;
                }
            }
            
            return obj;
        },
        
        markDirty() {
            this.isDirty = true;
            this.updateSaveStatus();
        },
        
        markClean() {
            this.isDirty = false;
            this.originalData = JSON.parse(JSON.stringify(this.formData));
            this.updateSaveStatus();
        },
        
        updateSaveStatus() {
            // Update global auto-save store if available
            if (Alpine.store('autosave')) {
                Alpine.store('autosave').updateStatus(this.saveStatus, this.getSaveMessage());
            }
        },
        
        getSaveMessage() {
            switch (this.saveStatus) {
                case 'saving':
                    return 'Guardando...';
                case 'saved':
                    return 'Guardado automáticamente';
                case 'error':
                    return 'Error al guardar';
                default:
                    return '';
            }
        },
        
        // Validation methods
        async validateStep() {
            this.isValidating = true;
            this.validationErrors = {};
            
            try {
                console.log('🪄 WizardStep: Validating step', this.stepId);
                
                // Validate all fields
                const fieldValidations = Object.keys(this.validationRules).map(async (fieldName) => {
                    const value = this.getFieldValue(fieldName);
                    return this.validateField(fieldName, value);
                });
                
                await Promise.all(fieldValidations);
                
                // Custom validation hook
                if (typeof this.customValidation === 'function') {
                    const customResult = await this.customValidation(this.formData);
                    if (customResult && customResult.errors) {
                        Object.assign(this.validationErrors, customResult.errors);
                    }
                }
                
                this.isValid = Object.keys(this.validationErrors).length === 0;
                
                // Update completion status
                if (this.isValid) {
                    this.markCompleted();
                }
                
                // Emit validation result
                this.$dispatch('step:validated', {
                    stepIndex: this.stepIndex,
                    stepId: this.stepId,
                    isValid: this.isValid,
                    errors: this.validationErrors
                });
                
                return {
                    isValid: this.isValid,
                    errors: this.validationErrors
                };
                
            } catch (error) {
                console.error('WizardStep: Validation error:', error);
                this.isValid = false;
                return {
                    isValid: false,
                    errors: { general: 'Error de validación interno' }
                };
            } finally {
                this.isValidating = false;
            }
        },
        
        async validateField(fieldName, value) {
            const rules = this.validationRules[fieldName];
            if (!rules) return true;
            
            try {
                // Built-in validation rules
                for (const rule of rules) {
                    const result = await this.applyValidationRule(rule, value, fieldName);
                    if (!result.isValid) {
                        this.validationErrors[fieldName] = result.message;
                        return false;
                    }
                }
                
                // Remove error if field is now valid
                delete this.validationErrors[fieldName];
                return true;
                
            } catch (error) {
                console.error('WizardStep: Field validation error:', error);
                this.validationErrors[fieldName] = 'Error de validación';
                return false;
            }
        },
        
        async applyValidationRule(rule, value, fieldName) {
            if (typeof rule === 'string') {
                return this.applyBuiltInRule(rule, value, fieldName);
            }
            
            if (typeof rule === 'function') {
                return await rule(value, fieldName, this.formData);
            }
            
            if (typeof rule === 'object' && rule.type) {
                return this.applyBuiltInRule(rule.type, value, fieldName, rule.params);
            }
            
            return { isValid: true };
        },
        
        applyBuiltInRule(ruleType, value, fieldName, params = {}) {
            switch (ruleType) {
                case 'required':
                    return {
                        isValid: value !== null && value !== undefined && value !== '',
                        message: `${fieldName} es requerido`
                    };
                    
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return {
                        isValid: !value || emailRegex.test(value),
                        message: `${fieldName} debe ser un email válido`
                    };
                    
                case 'min':
                    return {
                        isValid: !value || value.length >= params.length,
                        message: `${fieldName} debe tener al menos ${params.length} caracteres`
                    };
                    
                case 'max':
                    return {
                        isValid: !value || value.length <= params.length,
                        message: `${fieldName} no puede tener más de ${params.length} caracteres`
                    };
                    
                case 'pattern':
                    const regex = new RegExp(params.pattern);
                    return {
                        isValid: !value || regex.test(value),
                        message: params.message || `${fieldName} tiene un formato inválido`
                    };
                    
                default:
                    return { isValid: true };
            }
        },
        
        isFieldValid(fieldName, value) {
            const rules = this.validationRules[fieldName];
            if (!rules) return true;
            
            return rules.every(rule => {
                const result = this.applyValidationRule(rule, value, fieldName);
                return result.isValid;
            });
        },
        
        // Auto-save methods
        async autoSave() {
            if (this.isSaving || !this.isDirty) return;
            
            console.log('🪄 WizardStep: Auto-saving step', this.stepId);
            
            this.isSaving = true;
            this.saveStatus = 'saving';
            this.updateSaveStatus();
            
            try {
                const saveData = {
                    stepId: this.stepId,
                    stepIndex: this.stepIndex,
                    formData: this.formData,
                    timestamp: new Date().toISOString()
                };
                
                // Emit auto-save event
                const saveResult = await new Promise((resolve) => {
                    this.$dispatch('wizard:auto-save', {
                        stepData: saveData,
                        callback: resolve
                    });
                    
                    // Timeout fallback
                    setTimeout(() => resolve({ success: true }), 10000);
                });
                
                if (saveResult.success) {
                    this.lastSaveTime = new Date();
                    this.saveStatus = 'saved';
                    this.markClean();
                    
                    // Hide save indicator after 3 seconds
                    setTimeout(() => {
                        if (this.saveStatus === 'saved') {
                            this.saveStatus = 'idle';
                            this.updateSaveStatus();
                        }
                    }, 3000);
                } else {
                    throw new Error(saveResult.error || 'Save failed');
                }
                
            } catch (error) {
                console.error('WizardStep: Auto-save error:', error);
                this.saveStatus = 'error';
                
                // Hide error indicator after 5 seconds
                setTimeout(() => {
                    if (this.saveStatus === 'error') {
                        this.saveStatus = 'idle';
                        this.updateSaveStatus();
                    }
                }, 5000);
            } finally {
                this.isSaving = false;
                this.updateSaveStatus();
            }
        },
        
        // Step lifecycle methods
        onStepActivated() {
            console.log('🪄 WizardStep: Step activated', this.stepId);
            
            // Focus first input if available
            const firstInput = this.$el.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        },
        
        onStepDeactivated() {
            console.log('🪄 WizardStep: Step deactivated', this.stepId);
            
            // Auto-save on step change
            if (this.isDirty) {
                this.autoSave();
            }
        },
        
        markCompleted() {
            if (!this.isCompleted) {
                this.isCompleted = true;
                this.$dispatch('step:completed', {
                    stepIndex: this.stepIndex,
                    stepId: this.stepId,
                    formData: this.formData
                });
                console.log('🪄 WizardStep: Step completed', this.stepId);
            }
        },
        
        // Public API methods
        reset() {
            this.formData = JSON.parse(JSON.stringify(this.originalData));
            this.validationErrors = {};
            this.isValid = false;
            this.isCompleted = false;
            this.isDirty = false;
            this.saveStatus = 'idle';
            this.updateSaveStatus();
        },
        
        getData() {
            return JSON.parse(JSON.stringify(this.formData));
        },
        
        setData(data) {
            this.formData = { ...this.formData, ...data };
            this.markDirty();
        },
        
        getErrors() {
            return { ...this.validationErrors };
        },
        
        clearErrors() {
            this.validationErrors = {};
        },
        
        hasErrors() {
            return Object.keys(this.validationErrors).length > 0;
        },
        
        forceSave() {
            return this.autoSave();
        }
    }));
    
    // Global auto-save store
    Alpine.store('autosave', {
        isVisible: false,
        status: 'idle',
        message: '',
        
        updateStatus(status, message) {
            this.status = status;
            this.message = message;
            this.isVisible = status !== 'idle';
        }
    });
});