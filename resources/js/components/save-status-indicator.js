/**
 * SaveStatusIndicator Component
 * 
 * Displays auto-save status and provides user feedback
 * Shows save progress, success, and error states
 * Handles draft conflict notifications
 * 
 * Requirements: 5.2, 5.3
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('saveStatusIndicator', (config = {}) => ({
        // Configuration
        showTimestamp: config.showTimestamp !== false,
        showConflictWarning: config.showConflictWarning !== false,
        autoHide: config.autoHide !== false,
        autoHideDelay: config.autoHideDelay || 3000,
        
        // State
        status: 'idle', // idle, saving, saved, error
        lastSaved: null,
        hasConflict: false,
        errorMessage: null,
        isVisible: false,
        autoHideTimer: null,
        
        init() {
            console.log('💾 SaveStatusIndicator: Initializing');
            
            // Listen for save status changes
            this.$el.addEventListener('wizard:save-status-changed', (event) => {
                this.handleStatusChange(event.detail);
            });
            
            // Listen for save errors
            this.$el.addEventListener('wizard:save-error', (event) => {
                this.handleSaveError(event.detail);
            });
            
            // Listen for draft conflicts
            this.$el.addEventListener('wizard:draft-conflict', (event) => {
                this.handleConflict(event.detail);
            });
            
            // Listen for draft saved events
            this.$el.addEventListener('wizard:draft-saved', (event) => {
                this.handleDraftSaved(event.detail);
            });
        },
        
        handleStatusChange(detail) {
            const { status, lastSaved, hasConflict } = detail;
            
            this.status = status;
            this.lastSaved = lastSaved;
            this.hasConflict = hasConflict || false;
            
            // Show indicator when saving or if there's an error
            if (status === 'saving' || status === 'error') {
                this.show();
            } else if (status === 'saved' && this.autoHide) {
                this.show();
                this.scheduleAutoHide();
            }
            
            console.log('💾 SaveStatusIndicator: Status changed to', status);
        },
        
        handleSaveError(detail) {
            this.status = 'error';
            this.errorMessage = detail.message;
            this.show();
            
            // Don't auto-hide errors
            this.clearAutoHide();
        },
        
        handleConflict(detail) {
            this.hasConflict = true;
            this.show();
            
            // Show conflict resolution modal or notification
            this.showConflictNotification(detail);
        },
        
        handleDraftSaved(detail) {
            this.lastSaved = detail.savedAt;
            
            if (this.status !== 'saving') {
                this.status = 'saved';
                this.show();
                
                if (this.autoHide) {
                    this.scheduleAutoHide();
                }
            }
        },
        
        show() {
            this.isVisible = true;
            this.clearAutoHide();
        },
        
        hide() {
            this.isVisible = false;
            this.clearAutoHide();
        },
        
        scheduleAutoHide() {
            this.clearAutoHide();
            this.autoHideTimer = setTimeout(() => {
                this.hide();
            }, this.autoHideDelay);
        },
        
        clearAutoHide() {
            if (this.autoHideTimer) {
                clearTimeout(this.autoHideTimer);
                this.autoHideTimer = null;
            }
        },
        
        showConflictNotification(conflictData) {
            // Emit event for parent components to handle
            this.$dispatch('show-conflict-modal', {
                serverData: conflictData.serverData,
                serverUpdatedAt: conflictData.serverUpdatedAt,
                clientLastKnown: conflictData.clientLastKnown
            });
        },
        
        // Computed properties
        get statusText() {
            switch (this.status) {
                case 'saving':
                    return 'Guardando...';
                case 'saved':
                    return 'Guardado';
                case 'error':
                    return 'Error al guardar';
                default:
                    return '';
            }
        },
        
        get statusIcon() {
            switch (this.status) {
                case 'saving':
                    return 'loading';
                case 'saved':
                    return 'check';
                case 'error':
                    return 'exclamation-triangle';
                default:
                    return '';
            }
        },
        
        get statusClass() {
            const baseClass = 'save-status-indicator';
            
            switch (this.status) {
                case 'saving':
                    return `${baseClass} ${baseClass}--saving`;
                case 'saved':
                    return `${baseClass} ${baseClass}--saved`;
                case 'error':
                    return `${baseClass} ${baseClass}--error`;
                default:
                    return baseClass;
            }
        },
        
        get formattedTimestamp() {
            if (!this.lastSaved) return '';
            
            try {
                const date = new Date(this.lastSaved);
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                
                if (diffMins < 1) {
                    return 'hace un momento';
                } else if (diffMins < 60) {
                    return `hace ${diffMins} min`;
                } else {
                    return date.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            } catch (error) {
                return '';
            }
        },
        
        // Actions
        retry() {
            if (this.status === 'error') {
                this.$dispatch('wizard:retry-save');
                this.status = 'saving';
                this.errorMessage = null;
            }
        },
        
        dismiss() {
            this.hide();
            
            if (this.status === 'error') {
                this.status = 'idle';
                this.errorMessage = null;
            }
        },
        
        resolveConflict(action) {
            // action can be 'use-server', 'use-local', 'merge'
            this.$dispatch('wizard:resolve-conflict', { action });
            this.hasConflict = false;
        }
    }));
});