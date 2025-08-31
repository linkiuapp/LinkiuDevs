/**
 * WizardNavigation Component
 * 
 * Reusable wizard navigation component with Alpine.js
 * Implements step validation and navigation logic
 * Includes progress bar with completion indicators
 * Features step transition animations
 * 
 * Requirements: 1.1, 1.2
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('wizardNavigation', (config = {}) => ({
        // Configuration
        steps: config.steps || [],
        currentStep: config.initialStep || 0,
        allowBackNavigation: config.allowBackNavigation !== false,
        showProgressBar: config.showProgressBar !== false,
        showBreadcrumbs: config.showBreadcrumbs !== false,
        
        // State management
        completedSteps: new Set(),
        validationErrors: {},
        isValidating: false,
        isNavigating: false,
        loadingManager: null,
        activeLoadings: new Map(),
        
        // Animation settings
        animationDuration: 300,
        
        init() {
            console.log('🧙‍♂️ WizardNavigation: Initialized with', this.steps.length, 'steps');
            
            // Get manager instances
            this.loadingManager = window.loadingManager;
            this.animationManager = window.animationManager;
            
            // Validate configuration
            if (!this.steps.length) {
                console.error('WizardNavigation: No steps provided');
                return;
            }
            
            // Set up event listeners
            this.setupEventListeners();
            
            // Initialize first step
            this.validateCurrentStep();
        },
        
        setupEventListeners() {
            // Listen for step validation events
            this.$el.addEventListener('step:validated', (event) => {
                this.handleStepValidation(event.detail);
            });
            
            // Listen for step completion events
            this.$el.addEventListener('step:completed', (event) => {
                this.markStepCompleted(event.detail.stepIndex);
            });
            
            // Listen for navigation requests
            this.$el.addEventListener('wizard:navigate', (event) => {
                this.navigateToStep(event.detail.stepIndex);
            });
        },
        
        // Navigation methods
        canNavigateToStep(stepIndex) {
            if (stepIndex < 0 || stepIndex >= this.steps.length) {
                return false;
            }
            
            // Can always navigate backwards if allowed
            if (stepIndex < this.currentStep && this.allowBackNavigation) {
                return true;
            }
            
            // Can navigate to current step
            if (stepIndex === this.currentStep) {
                return true;
            }
            
            // Can navigate forward only if previous steps are completed
            if (stepIndex > this.currentStep) {
                for (let i = 0; i < stepIndex; i++) {
                    if (!this.isStepCompleted(i) && !this.steps[i].isOptional) {
                        return false;
                    }
                }
                return true;
            }
            
            return false;
        },
        
        async navigateToStep(stepIndex) {
            if (this.isNavigating || !this.canNavigateToStep(stepIndex)) {
                return false;
            }
            
            console.log('🧙‍♂️ WizardNavigation: Navigating to step', stepIndex);
            
            this.isNavigating = true;
            
            // Show navigation loading
            let navigationLoadingId = null;
            if (this.loadingManager) {
                navigationLoadingId = this.loadingManager.showGlobalLoading(
                    `Navegando al paso ${stepIndex + 1}...`,
                    false
                );
            }
            
            try {
                // Validate current step before leaving (if moving forward)
                if (stepIndex > this.currentStep) {
                    if (navigationLoadingId) {
                        this.loadingManager.updateGlobalProgress(25, 'Validando paso actual...');
                    }
                    
                    const isValid = await this.validateCurrentStep();
                    if (!isValid) {
                        if (navigationLoadingId) {
                            this.loadingManager.hideGlobalLoading();
                        }
                        
                        // Show validation error toast
                        if (this.loadingManager) {
                            this.loadingManager.showToast(
                                'Por favor corrige los errores antes de continuar',
                                'error'
                            );
                        }
                        
                        this.isNavigating = false;
                        return false;
                    }
                }
                
                if (navigationLoadingId) {
                    this.loadingManager.updateGlobalProgress(50, 'Preparando siguiente paso...');
                }
                
                // Animate transition
                await this.animateStepTransition(this.currentStep, stepIndex);
                
                if (navigationLoadingId) {
                    this.loadingManager.updateGlobalProgress(75, 'Cargando contenido...');
                }
                
                // Update current step
                const previousStep = this.currentStep;
                this.currentStep = stepIndex;
                
                // Emit navigation event
                this.$dispatch('wizard:step-changed', {
                    previousStep,
                    currentStep: this.currentStep,
                    stepData: this.getCurrentStepData()
                });
                
                if (navigationLoadingId) {
                    this.loadingManager.updateGlobalProgress(100, 'Completado');
                }
                
                // Small delay to show completion
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Validate new step
                this.validateCurrentStep();
                
                // Show success toast
                if (this.loadingManager) {
                    this.loadingManager.showToast(
                        `Paso ${stepIndex + 1}: ${this.steps[stepIndex]?.title || 'Sin título'}`,
                        'success',
                        3000
                    );
                }
                
                return true;
                
            } catch (error) {
                console.error('WizardNavigation: Navigation error:', error);
                
                if (this.loadingManager) {
                    this.loadingManager.showToast(
                        'Error al navegar entre pasos',
                        'error'
                    );
                }
                
                return false;
            } finally {
                if (navigationLoadingId) {
                    this.loadingManager.hideGlobalLoading();
                }
                this.isNavigating = false;
            }
        },
        
        async nextStep() {
            return await this.navigateToStep(this.currentStep + 1);
        },
        
        async previousStep() {
            return await this.navigateToStep(this.currentStep - 1);
        },
        
        // Validation methods
        async validateCurrentStep() {
            const step = this.getCurrentStep();
            if (!step) return false;
            
            this.isValidating = true;
            this.clearStepErrors(this.currentStep);
            
            // Show validation loading
            let validationLoadingId = null;
            if (this.loadingManager) {
                const stepElement = this.$el.querySelector(`[data-step="${this.currentStep}"]`);
                if (stepElement) {
                    validationLoadingId = this.loadingManager.showFormLoading(
                        stepElement,
                        'Validando campos...'
                    );
                }
            }
            
            try {
                console.log('🧙‍♂️ WizardNavigation: Validating step', this.currentStep);
                
                // Emit validation request
                const validationResult = await new Promise((resolve) => {
                    this.$dispatch('wizard:validate-step', {
                        stepIndex: this.currentStep,
                        stepId: step.id,
                        callback: resolve
                    });
                    
                    // Timeout fallback
                    setTimeout(() => resolve({ isValid: true }), 10000);
                });
                
                if (validationResult.isValid) {
                    this.markStepCompleted(this.currentStep);
                    
                    // Show success feedback
                    if (this.loadingManager) {
                        this.loadingManager.showToast(
                            `Paso ${this.currentStep + 1} validado correctamente`,
                            'success',
                            2000
                        );
                    }
                } else {
                    this.setStepErrors(this.currentStep, validationResult.errors || {});
                    
                    // Show error feedback
                    if (this.loadingManager) {
                        const errorCount = Object.keys(validationResult.errors || {}).length;
                        this.loadingManager.showToast(
                            `Se encontraron ${errorCount} errores en este paso`,
                            'error'
                        );
                    }
                }
                
                return validationResult.isValid;
                
            } catch (error) {
                console.error('WizardNavigation: Validation error:', error);
                
                if (this.loadingManager) {
                    this.loadingManager.showToast(
                        'Error al validar el paso',
                        'error'
                    );
                }
                
                return false;
            } finally {
                if (validationLoadingId) {
                    this.loadingManager.hideFormLoading(validationLoadingId);
                }
                this.isValidating = false;
            }
        },
        
        handleStepValidation(validationData) {
            const { stepIndex, isValid, errors } = validationData;
            
            if (isValid) {
                this.markStepCompleted(stepIndex);
                this.clearStepErrors(stepIndex);
            } else {
                this.setStepErrors(stepIndex, errors || {});
            }
        },
        
        // Step completion methods
        markStepCompleted(stepIndex) {
            this.completedSteps.add(stepIndex);
            console.log('🧙‍♂️ WizardNavigation: Step', stepIndex, 'marked as completed');
            
            // Animate step completion
            if (this.animationManager) {
                const stepElement = this.$el.querySelector(`[data-step-nav="${stepIndex}"]`);
                if (stepElement) {
                    this.animationManager.animateStepCompletion(stepElement, stepIndex);
                }
            }
        },
        
        isStepCompleted(stepIndex) {
            return this.completedSteps.has(stepIndex);
        },
        
        // Error handling methods
        setStepErrors(stepIndex, errors) {
            this.validationErrors[stepIndex] = errors;
        },
        
        clearStepErrors(stepIndex) {
            delete this.validationErrors[stepIndex];
        },
        
        getStepErrors(stepIndex) {
            return this.validationErrors[stepIndex] || {};
        },
        
        hasStepErrors(stepIndex) {
            const errors = this.getStepErrors(stepIndex);
            return Object.keys(errors).length > 0;
        },
        
        // Animation methods
        async animateStepTransition(fromStep, toStep) {
            const direction = toStep > fromStep ? 'forward' : 'backward';
            
            // Use animation manager if available
            if (this.animationManager) {
                this.animationManager.animateWizardTransition(fromStep, toStep, direction);
                await new Promise(resolve => setTimeout(resolve, this.animationDuration));
                return;
            }
            
            // Fallback animation
            const currentStepEl = this.$el.querySelector(`[data-step="${fromStep}"]`);
            const nextStepEl = this.$el.querySelector(`[data-step="${toStep}"]`);
            
            if (currentStepEl && nextStepEl) {
                // Hide current step
                currentStepEl.classList.add('wizard-step-exit', `wizard-step-exit-${direction}`);
                
                // Show next step
                nextStepEl.classList.remove('hidden');
                nextStepEl.classList.add('wizard-step-enter', `wizard-step-enter-${direction}`);
                
                // Wait for animation
                await new Promise(resolve => setTimeout(resolve, this.animationDuration));
                
                // Clean up classes
                currentStepEl.classList.add('hidden');
                currentStepEl.classList.remove('wizard-step-exit', `wizard-step-exit-${direction}`);
                nextStepEl.classList.remove('wizard-step-enter', `wizard-step-enter-${direction}`);
            }
        },
        
        // Utility methods
        getCurrentStep() {
            return this.steps[this.currentStep] || null;
        },
        
        getCurrentStepData() {
            const step = this.getCurrentStep();
            return step ? {
                index: this.currentStep,
                id: step.id,
                title: step.title,
                description: step.description,
                isOptional: step.isOptional || false,
                isCompleted: this.isStepCompleted(this.currentStep),
                hasErrors: this.hasStepErrors(this.currentStep)
            } : null;
        },
        
        getProgressPercentage() {
            if (this.steps.length === 0) return 0;
            return Math.round((this.completedSteps.size / this.steps.length) * 100);
        },
        
        getStepStatus(stepIndex) {
            if (stepIndex === this.currentStep) {
                return this.hasStepErrors(stepIndex) ? 'error' : 'current';
            }
            
            if (this.isStepCompleted(stepIndex)) {
                return 'completed';
            }
            
            if (this.canNavigateToStep(stepIndex)) {
                return 'available';
            }
            
            return 'disabled';
        },
        
        // Public API methods
        reset() {
            this.currentStep = 0;
            this.completedSteps.clear();
            this.validationErrors = {};
            console.log('🧙‍♂️ WizardNavigation: Reset to initial state');
        },
        
        jumpToStep(stepIndex) {
            return this.navigateToStep(stepIndex);
        },
        
        isFirstStep() {
            return this.currentStep === 0;
        },
        
        isLastStep() {
            return this.currentStep === this.steps.length - 1;
        },
        
        canGoNext() {
            return !this.isLastStep() && this.canNavigateToStep(this.currentStep + 1);
        },
        
        canGoPrevious() {
            return !this.isFirstStep() && this.allowBackNavigation;
        }
    }));
});