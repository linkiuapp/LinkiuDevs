/**
 * WizardStateManager Component
 * 
 * Implements client-side routing for wizard steps
 * Provides form state persistence in localStorage
 * Manages step validation status tracking
 * Creates navigation guards for incomplete steps
 * 
 * Requirements: 1.1, 5.1
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('wizardStateManager', (config = {}) => ({
        // Configuration
        wizardId: config.wizardId || 'default-wizard',
        enableRouting: config.enableRouting !== false,
        enablePersistence: config.enablePersistence !== false,
        persistenceKey: config.persistenceKey || null,
        routePrefix: config.routePrefix || '#step-',
        
        // State
        currentRoute: '',
        isInitialized: false,
        persistedData: {},
        validationStates: {},
        
        // Auto-save state
        autoSaveEnabled: config.autoSaveEnabled !== false,
        autoSaveInterval: config.autoSaveInterval || 30000, // 30 seconds
        autoSaveTimer: null,
        lastServerSave: null,
        draftId: null,
        saveStatus: 'idle', // idle, saving, saved, error
        conflictDetected: false,
        
        init() {
            console.log('🗂️ WizardStateManager: Initializing for wizard', this.wizardId);
            
            // Generate persistence key if not provided
            if (!this.persistenceKey) {
                this.persistenceKey = `wizard_${this.wizardId}_state`;
            }
            
            // Load persisted state
            if (this.enablePersistence) {
                this.loadPersistedState();
            }
            
            // Set up routing
            if (this.enableRouting) {
                this.setupRouting();
            }
            
            // Set up event listeners
            this.setupEventListeners();
            
            // Set up auto-save
            if (this.autoSaveEnabled) {
                this.setupAutoSave();
            }
            
            // Check for existing draft
            this.checkForExistingDraft();
            
            this.isInitialized = true;
        },
        
        setupRouting() {
            // Listen for hash changes
            window.addEventListener('hashchange', () => {
                this.handleRouteChange();
            });
            
            // Handle initial route
            this.handleRouteChange();
        },
        
        setupEventListeners() {
            // Listen for step changes
            this.$el.addEventListener('wizard:step-changed', (event) => {
                this.handleStepChange(event.detail);
            });
            
            // Listen for form data changes
            this.$el.addEventListener('wizard:data-changed', (event) => {
                this.handleDataChange(event.detail);
            });
            
            // Listen for validation state changes
            this.$el.addEventListener('step:validated', (event) => {
                this.handleValidationChange(event.detail);
            });
            
            // Listen for auto-save requests
            this.$el.addEventListener('wizard:auto-save', (event) => {
                this.handleAutoSave(event.detail);
            });
            
            // Listen for draft restoration
            this.$el.addEventListener('wizard:restore-draft', (event) => {
                this.handleDraftRestore(event.detail);
            });
            
            // Listen for wizard completion to cleanup drafts
            this.$el.addEventListener('wizard:completed', (event) => {
                this.handleWizardCompleted(event.detail);
            });
            
            // Listen for conflict resolution
            this.$el.addEventListener('wizard:resolve-conflict', (event) => {
                this.handleConflictResolution(event.detail);
            });
            
            // Listen for clear local data requests
            this.$el.addEventListener('wizard:clear-local-data', () => {
                this.clearPersistedState();
            });
            
            // Save state before page unload
            window.addEventListener('beforeunload', () => {
                this.saveCurrentState();
            });
        },
        
        // Routing methods
        handleRouteChange() {
            const hash = window.location.hash;
            
            if (hash.startsWith(this.routePrefix)) {
                const stepParam = hash.substring(this.routePrefix.length);
                const stepIndex = this.parseStepFromRoute(stepParam);
                
                if (stepIndex !== null && this.canNavigateToStep(stepIndex)) {
                    this.navigateToStepByRoute(stepIndex);
                }
            }
        },
        
        parseStepFromRoute(stepParam) {
            // Support both numeric index and step ID
            const stepIndex = parseInt(stepParam);
            if (!isNaN(stepIndex)) {
                return stepIndex;
            }
            
            // Try to find step by ID
            const wizard = this.getWizardComponent();
            if (wizard && wizard.steps) {
                const foundIndex = wizard.steps.findIndex(step => step.id === stepParam);
                return foundIndex >= 0 ? foundIndex : null;
            }
            
            return null;
        },
        
        updateRoute(stepIndex, stepId = null) {
            if (!this.enableRouting) return;
            
            const routeParam = stepId || stepIndex.toString();
            const newHash = `${this.routePrefix}${routeParam}`;
            
            if (window.location.hash !== newHash) {
                this.currentRoute = newHash;
                window.history.replaceState(null, null, newHash);
            }
        },
        
        navigateToStepByRoute(stepIndex) {
            const wizard = this.getWizardComponent();
            if (wizard && wizard.navigateToStep) {
                wizard.navigateToStep(stepIndex);
            }
        },
        
        canNavigateToStep(stepIndex) {
            const wizard = this.getWizardComponent();
            return wizard ? wizard.canNavigateToStep(stepIndex) : false;
        },
        
        // State persistence methods
        loadPersistedState() {
            try {
                const saved = localStorage.getItem(this.persistenceKey);
                if (saved) {
                    const state = JSON.parse(saved);
                    this.persistedData = state.formData || {};
                    this.validationStates = state.validationStates || {};
                    
                    console.log('🗂️ WizardStateManager: Loaded persisted state', state);
                    
                    // Emit loaded event
                    this.$dispatch('wizard:state-loaded', {
                        formData: this.persistedData,
                        validationStates: this.validationStates,
                        timestamp: state.timestamp
                    });
                }
            } catch (error) {
                console.error('WizardStateManager: Error loading persisted state:', error);
                this.clearPersistedState();
            }
        },
        
        saveCurrentState() {
            if (!this.enablePersistence) return;
            
            try {
                const wizard = this.getWizardComponent();
                const currentData = this.collectFormData();
                
                const state = {
                    wizardId: this.wizardId,
                    currentStep: wizard ? wizard.currentStep : 0,
                    formData: { ...this.persistedData, ...currentData },
                    validationStates: this.validationStates,
                    timestamp: new Date().toISOString(),
                    version: '1.0'
                };
                
                localStorage.setItem(this.persistenceKey, JSON.stringify(state));
                console.log('🗂️ WizardStateManager: Saved current state');
                
            } catch (error) {
                console.error('WizardStateManager: Error saving state:', error);
            }
        },
        
        clearPersistedState() {
            try {
                localStorage.removeItem(this.persistenceKey);
                this.persistedData = {};
                this.validationStates = {};
                console.log('🗂️ WizardStateManager: Cleared persisted state');
            } catch (error) {
                console.error('WizardStateManager: Error clearing state:', error);
            }
        },
        
        collectFormData() {
            const formData = {};
            
            // Collect data from all wizard steps
            const stepElements = this.$el.querySelectorAll('[x-data*="wizardStep"]');
            stepElements.forEach(stepEl => {
                const stepComponent = Alpine.$data(stepEl);
                if (stepComponent && stepComponent.stepId) {
                    formData[stepComponent.stepId] = stepComponent.getData();
                }
            });
            
            return formData;
        },
        
        // Event handlers
        handleStepChange(detail) {
            const { currentStep, stepData } = detail;
            
            // Update route
            if (stepData) {
                this.updateRoute(currentStep, stepData.id);
            }
            
            // Save state
            this.saveCurrentState();
        },
        
        handleDataChange(detail) {
            const { stepId, formData } = detail;
            
            // Update persisted data
            if (stepId) {
                this.persistedData[stepId] = formData;
            }
            
            // Debounced save
            this.debouncedSave();
        },
        
        handleValidationChange(detail) {
            const { stepIndex, stepId, isValid, errors } = detail;
            
            // Update validation state
            this.validationStates[stepId || stepIndex] = {
                isValid,
                errors,
                timestamp: new Date().toISOString()
            };
            
            // Save state
            this.saveCurrentState();
        },
        
        async handleAutoSave(detail) {
            const { stepData, callback } = detail;
            
            try {
                // Update persisted data
                if (stepData.stepId) {
                    this.persistedData[stepData.stepId] = stepData.formData;
                }
                
                // Save to localStorage
                this.saveCurrentState();
                
                // Simulate API call for server-side persistence
                await this.saveToServer(stepData);
                
                callback({ success: true });
                
            } catch (error) {
                console.error('WizardStateManager: Auto-save error:', error);
                callback({ success: false, error: error.message });
            }
        },
        
        async saveToServer(stepData) {
            try {
                const formData = this.collectFormData();
                const wizard = this.getWizardComponent();
                
                const payload = {
                    form_data: formData,
                    template: wizard?.selectedTemplate || null,
                    current_step: wizard?.currentStep || 1
                };
                
                const response = await fetch('/superlinkiu/api/stores/save-draft', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });
                
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    console.log('🗂️ WizardStateManager: Server save successful', result.data);
                    
                    // Update local state with server response
                    this.lastServerSave = result.data.saved_at;
                    this.draftId = result.data.draft_id;
                    
                    // Emit save success event
                    this.$dispatch('wizard:draft-saved', {
                        draftId: this.draftId,
                        savedAt: this.lastServerSave,
                        expiresAt: result.data.expires_at
                    });
                    
                    return result.data;
                } else {
                    throw new Error(result.message || 'Unknown server error');
                }
                
            } catch (error) {
                console.error('🗂️ WizardStateManager: Server save failed:', error);
                
                // Emit save error event
                this.$dispatch('wizard:draft-save-error', {
                    error: error.message,
                    timestamp: new Date().toISOString()
                });
                
                throw error;
            }
        },
        
        // Auto-save methods
        setupAutoSave() {
            console.log('🗂️ WizardStateManager: Setting up auto-save with interval', this.autoSaveInterval);
            
            // Start auto-save timer
            this.startAutoSaveTimer();
            
            // Listen for form changes to trigger auto-save
            this.$el.addEventListener('input', () => {
                this.scheduleAutoSave();
            });
            
            // Listen for step changes to trigger immediate save
            this.$el.addEventListener('wizard:step-changed', () => {
                this.performAutoSave();
            });
        },
        
        startAutoSaveTimer() {
            if (this.autoSaveTimer) {
                clearInterval(this.autoSaveTimer);
            }
            
            this.autoSaveTimer = setInterval(() => {
                this.performAutoSave();
            }, this.autoSaveInterval);
        },
        
        stopAutoSaveTimer() {
            if (this.autoSaveTimer) {
                clearInterval(this.autoSaveTimer);
                this.autoSaveTimer = null;
            }
        },
        
        scheduleAutoSave() {
            // Debounced auto-save on form changes
            clearTimeout(this.autoSaveDebounce);
            this.autoSaveDebounce = setTimeout(() => {
                this.performAutoSave();
            }, 2000); // 2 seconds after last change
        },
        
        async performAutoSave() {
            if (this.saveStatus === 'saving') {
                console.log('🗂️ WizardStateManager: Auto-save already in progress, skipping');
                return;
            }
            
            try {
                this.saveStatus = 'saving';
                this.updateSaveIndicator();
                
                // Check for conflicts before saving
                if (this.draftId) {
                    await this.checkForConflicts();
                }
                
                // Collect current form data
                const formData = this.collectFormData();
                
                // Only save if there's actual data
                if (Object.keys(formData).length === 0) {
                    this.saveStatus = 'idle';
                    return;
                }
                
                // Save to server
                await this.saveToServer({ formData });
                
                this.saveStatus = 'saved';
                this.updateSaveIndicator();
                
                // Reset status after a delay
                setTimeout(() => {
                    if (this.saveStatus === 'saved') {
                        this.saveStatus = 'idle';
                        this.updateSaveIndicator();
                    }
                }, 3000);
                
            } catch (error) {
                console.error('🗂️ WizardStateManager: Auto-save failed:', error);
                this.saveStatus = 'error';
                this.updateSaveIndicator();
                
                // Show error notification
                this.showSaveError(error.message);
            }
        },
        
        async checkForConflicts() {
            if (!this.draftId || !this.lastServerSave) return;
            
            try {
                const response = await fetch('/superlinkiu/api/stores/check-draft-conflict', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        draft_id: this.draftId,
                        last_known_update: this.lastServerSave
                    })
                });
                
                const result = await response.json();
                
                if (result.success && result.data.has_conflict) {
                    this.conflictDetected = true;
                    this.handleConflict(result.data);
                }
                
            } catch (error) {
                console.error('🗂️ WizardStateManager: Conflict check failed:', error);
            }
        },
        
        handleConflict(conflictData) {
            console.warn('🗂️ WizardStateManager: Draft conflict detected', conflictData);
            
            // Emit conflict event for UI to handle
            this.$dispatch('wizard:draft-conflict', {
                serverData: conflictData.draft_data,
                serverUpdatedAt: conflictData.server_updated_at,
                clientLastKnown: conflictData.client_last_known
            });
        },
        
        async checkForExistingDraft() {
            try {
                const response = await fetch('/superlinkiu/api/stores/get-draft', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    const draft = result.data;
                    
                    if (!draft.is_expired) {
                        // Emit draft found event
                        this.$dispatch('wizard:draft-found', {
                            draftId: draft.id,
                            formData: draft.form_data,
                            template: draft.template,
                            currentStep: draft.current_step,
                            updatedAt: draft.updated_at,
                            expiresAt: draft.expires_at
                        });
                        
                        this.draftId = draft.id;
                        this.lastServerSave = draft.updated_at;
                    }
                }
                
            } catch (error) {
                console.error('🗂️ WizardStateManager: Error checking for existing draft:', error);
            }
        },
        
        updateSaveIndicator() {
            // Emit save status change for UI components
            this.$dispatch('wizard:save-status-changed', {
                status: this.saveStatus,
                lastSaved: this.lastServerSave,
                hasConflict: this.conflictDetected
            });
        },
        
        showSaveError(message) {
            // Emit error event for UI to show notification
            this.$dispatch('wizard:save-error', {
                message: message,
                timestamp: new Date().toISOString()
            });
        },
        
        async deleteDraft() {
            if (!this.draftId) return;
            
            try {
                const response = await fetch(`/superlinkiu/api/stores/delete-draft/${this.draftId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.draftId = null;
                    this.lastServerSave = null;
                    this.clearPersistedState();
                    
                    console.log('🗂️ WizardStateManager: Draft deleted successfully');
                }
                
            } catch (error) {
                console.error('🗂️ WizardStateManager: Error deleting draft:', error);
            }
        },
        
        handleDraftRestore(detail) {
            console.log('🗂️ WizardStateManager: Restoring draft', detail);
            
            const { formData, template, currentStep, draftId } = detail;
            
            // Update local state
            this.persistedData = formData || {};
            this.draftId = draftId;
            
            // Update wizard state
            const wizard = this.getWizardComponent();
            if (wizard) {
                if (template) {
                    wizard.selectedTemplate = template;
                }
                if (currentStep) {
                    wizard.navigateToStep(currentStep - 1); // Convert to 0-based index
                }
            }
            
            // Emit restoration complete event
            this.$dispatch('wizard:draft-restored', {
                formData: this.persistedData,
                template,
                currentStep
            });
            
            // Save the restored state
            this.saveCurrentState();
        },
        
        async handleWizardCompleted(detail) {
            console.log('🗂️ WizardStateManager: Wizard completed, cleaning up draft');
            
            try {
                // Delete server draft
                if (this.draftId) {
                    await this.deleteDraft();
                }
                
                // Clear local state
                this.clearPersistedState();
                
                // Stop auto-save
                this.stopAutoSaveTimer();
                
                console.log('🗂️ WizardStateManager: Draft cleanup completed');
                
            } catch (error) {
                console.error('🗂️ WizardStateManager: Error during cleanup:', error);
            }
        },
        
        handleConflictResolution(detail) {
            const { action } = detail;
            
            console.log('🗂️ WizardStateManager: Resolving conflict with action:', action);
            
            switch (action) {
                case 'use-local':
                    // Keep local changes, force save to server
                    this.conflictDetected = false;
                    this.performAutoSave();
                    break;
                    
                case 'use-server':
                    // Reload from server and overwrite local
                    this.reloadFromServer();
                    break;
                    
                case 'merge':
                    // TODO: Implement merge logic if needed
                    console.warn('Merge conflict resolution not implemented yet');
                    break;
            }
        },
        
        async reloadFromServer() {
            try {
                const response = await fetch('/superlinkiu/api/stores/get-draft', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    const draft = result.data;
                    
                    // Update local state with server data
                    this.persistedData = draft.form_data || {};
                    this.lastServerSave = draft.updated_at;
                    this.conflictDetected = false;
                    
                    // Emit reload event for UI to update
                    this.$dispatch('wizard:data-reloaded', {
                        formData: this.persistedData,
                        template: draft.template,
                        currentStep: draft.current_step
                    });
                    
                    console.log('🗂️ WizardStateManager: Reloaded data from server');
                }
                
            } catch (error) {
                console.error('🗂️ WizardStateManager: Error reloading from server:', error);
            }
        },
        
        // Utility methods
        getWizardComponent() {
            // Find the wizard navigation component
            const wizardEl = this.$el.querySelector('[x-data*="wizardNavigation"]');
            return wizardEl ? Alpine.$data(wizardEl) : null;
        },
        
        debouncedSave: (() => {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.saveCurrentState();
                }, 1000);
            };
        })(),
        
        // Public API methods
        getPersistedData(stepId = null) {
            if (stepId) {
                return this.persistedData[stepId] || {};
            }
            return { ...this.persistedData };
        },
        
        setPersistedData(stepId, data) {
            this.persistedData[stepId] = data;
            this.saveCurrentState();
        },
        
        getValidationState(stepId) {
            return this.validationStates[stepId] || null;
        },
        
        hasPersistedData() {
            return Object.keys(this.persistedData).length > 0;
        },
        
        getStateInfo() {
            return {
                wizardId: this.wizardId,
                hasPersistedData: this.hasPersistedData(),
                stepCount: Object.keys(this.persistedData).length,
                validationStates: { ...this.validationStates },
                lastSaved: this.getLastSavedTime()
            };
        },
        
        getLastSavedTime() {
            try {
                const saved = localStorage.getItem(this.persistenceKey);
                if (saved) {
                    const state = JSON.parse(saved);
                    return state.timestamp;
                }
            } catch (error) {
                // Ignore errors
            }
            return null;
        },
        
        reset() {
            this.clearPersistedState();
            
            // Reset route
            if (this.enableRouting) {
                window.location.hash = '';
            }
            
            console.log('🗂️ WizardStateManager: Reset complete');
        },
        
        destroy() {
            // Clean up timers
            this.stopAutoSaveTimer();
            if (this.autoSaveDebounce) {
                clearTimeout(this.autoSaveDebounce);
            }
            
            // Perform final save
            if (this.autoSaveEnabled && this.hasPersistedData()) {
                this.performAutoSave();
            }
            
            console.log('🗂️ WizardStateManager: Component destroyed');
        }
    }));
    
    // Navigation guard store
    Alpine.store('wizardGuards', {
        guards: new Map(),
        
        addGuard(stepIndex, guardFn) {
            this.guards.set(stepIndex, guardFn);
        },
        
        removeGuard(stepIndex) {
            this.guards.delete(stepIndex);
        },
        
        async checkGuard(stepIndex, context) {
            const guard = this.guards.get(stepIndex);
            if (guard) {
                return await guard(context);
            }
            return true;
        },
        
        hasGuard(stepIndex) {
            return this.guards.has(stepIndex);
        }
    });
});