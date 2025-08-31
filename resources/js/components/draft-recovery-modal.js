/**
 * DraftRecoveryModal Component
 * 
 * Handles draft detection and recovery on wizard initialization
 * Provides options to restore, discard, or merge draft data
 * Manages draft cleanup on successful store creation
 * 
 * Requirements: 5.3, 5.4
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('draftRecoveryModal', (config = {}) => ({
        // Configuration
        autoShow: config.autoShow !== false,
        showPreview: config.showPreview !== false,
        
        // State
        isVisible: false,
        draftData: null,
        isLoading: false,
        previewMode: 'summary', // summary, detailed, json
        
        init() {
            console.log('📋 DraftRecoveryModal: Initializing');
            
            // Listen for draft found events
            this.$el.addEventListener('wizard:draft-found', (event) => {
                this.handleDraftFound(event.detail);
            });
            
            // Listen for wizard completion to cleanup drafts
            this.$el.addEventListener('wizard:completed', (event) => {
                this.handleWizardCompleted(event.detail);
            });
        },
        
        handleDraftFound(draftDetail) {
            console.log('📋 DraftRecoveryModal: Draft found', draftDetail);
            
            this.draftData = draftDetail;
            
            if (this.autoShow) {
                this.show();
            }
        },
        
        handleWizardCompleted(detail) {
            // Clean up draft when wizard is successfully completed
            if (this.draftData) {
                this.deleteDraft();
            }
        },
        
        show() {
            this.isVisible = true;
        },
        
        hide() {
            this.isVisible = false;
        },
        
        async restoreDraft() {
            if (!this.draftData) return;
            
            try {
                this.isLoading = true;
                
                // Emit restore event with draft data
                this.$dispatch('wizard:restore-draft', {
                    formData: this.draftData.formData,
                    template: this.draftData.template,
                    currentStep: this.draftData.currentStep,
                    draftId: this.draftData.draftId
                });
                
                // Hide modal
                this.hide();
                
                // Show success notification
                this.showNotification('Borrador restaurado exitosamente', 'success');
                
            } catch (error) {
                console.error('📋 DraftRecoveryModal: Error restoring draft:', error);
                this.showNotification('Error al restaurar el borrador', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        async discardDraft() {
            if (!this.draftData) return;
            
            try {
                this.isLoading = true;
                
                // Delete draft from server
                await this.deleteDraft();
                
                // Clear local data
                this.clearLocalData();
                
                // Hide modal
                this.hide();
                
                // Show notification
                this.showNotification('Borrador descartado', 'info');
                
            } catch (error) {
                console.error('📋 DraftRecoveryModal: Error discarding draft:', error);
                this.showNotification('Error al descartar el borrador', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        async deleteDraft() {
            if (!this.draftData?.draftId) return;
            
            const response = await fetch(`/superlinkiu/api/stores/delete-draft/${this.draftData.draftId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Unknown error');
            }
        },
        
        clearLocalData() {
            // Emit event to clear local storage
            this.$dispatch('wizard:clear-local-data');
            
            // Reset draft data
            this.draftData = null;
        },
        
        startFresh() {
            // Just hide modal and let user start fresh
            this.hide();
            
            // Optionally clear local data but keep server draft
            this.clearLocalData();
            
            this.showNotification('Iniciando formulario nuevo', 'info');
        },
        
        showNotification(message, type = 'info') {
            this.$dispatch('show-notification', {
                message,
                type,
                duration: 3000
            });
        },
        
        // Preview methods
        togglePreviewMode() {
            const modes = ['summary', 'detailed', 'json'];
            const currentIndex = modes.indexOf(this.previewMode);
            this.previewMode = modes[(currentIndex + 1) % modes.length];
        },
        
        // Computed properties
        get formattedDraftAge() {
            if (!this.draftData?.updatedAt) return '';
            
            try {
                const draftDate = new Date(this.draftData.updatedAt);
                const now = new Date();
                const diffMs = now - draftDate;
                const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                const diffDays = Math.floor(diffHours / 24);
                
                if (diffDays > 0) {
                    return `hace ${diffDays} día${diffDays > 1 ? 's' : ''}`;
                } else if (diffHours > 0) {
                    return `hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
                } else {
                    const diffMins = Math.floor(diffMs / (1000 * 60));
                    return `hace ${diffMins} minuto${diffMins > 1 ? 's' : ''}`;
                }
            } catch (error) {
                return '';
            }
        },
        
        get formattedExpirationTime() {
            if (!this.draftData?.expiresAt) return '';
            
            try {
                const expirationDate = new Date(this.draftData.expiresAt);
                const now = new Date();
                const diffMs = expirationDate - now;
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                
                if (diffDays > 0) {
                    return `Expira en ${diffDays} día${diffDays > 1 ? 's' : ''}`;
                } else {
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    if (diffHours > 0) {
                        return `Expira en ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
                    } else {
                        return 'Expira pronto';
                    }
                }
            } catch (error) {
                return '';
            }
        },
        
        get draftSummary() {
            if (!this.draftData?.formData) return {};
            
            const formData = this.draftData.formData;
            const summary = {};
            
            // Extract key information from form data
            if (formData.owner) {
                summary.owner = {
                    name: formData.owner.name || '',
                    email: formData.owner.email || ''
                };
            }
            
            if (formData.store) {
                summary.store = {
                    name: formData.store.name || '',
                    slug: formData.store.slug || '',
                    plan: formData.store.planId || ''
                };
            }
            
            if (formData.template) {
                summary.template = formData.template;
            }
            
            return summary;
        },
        
        get completedStepsCount() {
            if (!this.draftData?.formData) return 0;
            
            const formData = this.draftData.formData;
            let count = 0;
            
            if (formData.owner && Object.keys(formData.owner).length > 0) count++;
            if (formData.store && Object.keys(formData.store).length > 0) count++;
            if (formData.fiscal && Object.keys(formData.fiscal).length > 0) count++;
            if (formData.billing && Object.keys(formData.billing).length > 0) count++;
            if (formData.seo && Object.keys(formData.seo).length > 0) count++;
            
            return count;
        },
        
        get totalStepsCount() {
            // This should match the total number of steps in the wizard
            return 6;
        },
        
        get progressPercentage() {
            return Math.round((this.completedStepsCount / this.totalStepsCount) * 100);
        }
    }));
});