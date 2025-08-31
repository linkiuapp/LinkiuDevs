/**
 * LoadingManager - Comprehensive loading states and progress feedback system
 */

class LoadingManager {
    constructor() {
        this.activeLoadings = new Map();
        this.toastContainer = null;
        this.loadingOverlay = null;
        this.progressBars = new Map();
        
        this.init();
    }

    init() {
        this.createToastContainer();
        this.setupGlobalLoadingHandlers();
    }

    /**
     * Create toast notification container
     */
    createToastContainer() {
        if (this.toastContainer) return;

        this.toastContainer = document.createElement('div');
        this.toastContainer.className = 'toast-container';
        document.body.appendChild(this.toastContainer);
    }

    /**
     * Show global loading overlay
     */
    showGlobalLoading(message = 'Cargando...', showProgress = false) {
        if (this.loadingOverlay) return;

        this.loadingOverlay = document.createElement('div');
        this.loadingOverlay.className = 'loading-overlay';
        
        this.loadingOverlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner">
                    <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div class="loading-text">${message}</div>
                ${showProgress ? `
                    <div class="loading-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 0%"></div>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;

        document.body.appendChild(this.loadingOverlay);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    /**
     * Update global loading progress
     */
    updateGlobalProgress(percentage, message = null) {
        if (!this.loadingOverlay) return;

        const progressFill = this.loadingOverlay.querySelector('.progress-fill');
        const loadingText = this.loadingOverlay.querySelector('.loading-text');
        
        if (progressFill) {
            progressFill.style.width = `${Math.min(100, Math.max(0, percentage))}%`;
        }
        
        if (message && loadingText) {
            loadingText.textContent = message;
        }
    }

    /**
     * Hide global loading overlay
     */
    hideGlobalLoading() {
        if (this.loadingOverlay) {
            this.loadingOverlay.style.animation = 'fadeOut 0.3s ease-in forwards';
            setTimeout(() => {
                if (this.loadingOverlay && this.loadingOverlay.parentNode) {
                    this.loadingOverlay.parentNode.removeChild(this.loadingOverlay);
                    this.loadingOverlay = null;
                }
                // Restore body scroll
                document.body.style.overflow = '';
            }, 300);
        }
    }

    /**
     * Show form loading state
     */
    showFormLoading(formElement, message = 'Procesando...') {
        if (!formElement) return;

        const loadingId = this.generateLoadingId();
        
        // Add loading class
        formElement.classList.add('form-loading');
        
        // Create loading spinner
        const spinner = document.createElement('div');
        spinner.className = 'form-loading-spinner';
        spinner.innerHTML = `
            <div class="text-center">
                <svg class="animate-spin h-6 w-6 text-blue-500 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="text-sm text-gray-600">${message}</div>
            </div>
        `;
        
        formElement.appendChild(spinner);
        
        // Disable form inputs
        const inputs = formElement.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            input.disabled = true;
        });
        
        this.activeLoadings.set(loadingId, {
            type: 'form',
            element: formElement,
            spinner: spinner,
            inputs: inputs
        });
        
        return loadingId;
    }

    /**
     * Hide form loading state
     */
    hideFormLoading(loadingId) {
        const loading = this.activeLoadings.get(loadingId);
        if (!loading || loading.type !== 'form') return;

        const { element, spinner, inputs } = loading;
        
        // Remove loading class
        element.classList.remove('form-loading');
        
        // Remove spinner
        if (spinner && spinner.parentNode) {
            spinner.parentNode.removeChild(spinner);
        }
        
        // Re-enable form inputs
        inputs.forEach(input => {
            input.disabled = false;
        });
        
        this.activeLoadings.delete(loadingId);
    }

    /**
     * Show button loading state
     */
    showButtonLoading(button, loadingText = null) {
        if (!button) return;

        const loadingId = this.generateLoadingId();
        const originalText = button.textContent;
        
        // Add loading class
        button.classList.add('btn-loading');
        button.disabled = true;
        
        // Store original content
        const textSpan = document.createElement('span');
        textSpan.className = 'btn-text';
        textSpan.textContent = loadingText || originalText;
        
        const spinner = document.createElement('span');
        spinner.className = 'btn-spinner';
        spinner.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;
        
        // Replace button content
        button.innerHTML = '';
        button.appendChild(textSpan);
        button.appendChild(spinner);
        
        this.activeLoadings.set(loadingId, {
            type: 'button',
            element: button,
            originalText: originalText
        });
        
        return loadingId;
    }

    /**
     * Hide button loading state
     */
    hideButtonLoading(loadingId) {
        const loading = this.activeLoadings.get(loadingId);
        if (!loading || loading.type !== 'button') return;

        const { element, originalText } = loading;
        
        // Remove loading class
        element.classList.remove('btn-loading');
        element.disabled = false;
        
        // Restore original text
        element.textContent = originalText;
        
        this.activeLoadings.delete(loadingId);
    }

    /**
     * Show skeleton loader for templates
     */
    showTemplateSkeletons(container, count = 3) {
        if (!container) return;

        const skeletonContainer = document.createElement('div');
        skeletonContainer.className = 'template-loading-skeleton';
        
        for (let i = 0; i < count; i++) {
            const skeleton = document.createElement('div');
            skeleton.className = 'template-skeleton';
            skeletonContainer.appendChild(skeleton);
        }
        
        container.innerHTML = '';
        container.appendChild(skeletonContainer);
        
        return skeletonContainer;
    }

    /**
     * Show skeleton loader for form fields
     */
    showFormSkeletons(container, fieldCount = 5) {
        if (!container) return;

        const skeletonHTML = Array.from({ length: fieldCount }, () => `
            <div class="mb-4">
                <div class="skeleton-text w-1/4 mb-2"></div>
                <div class="skeleton-form-field"></div>
            </div>
        `).join('');
        
        container.innerHTML = skeletonHTML;
    }

    /**
     * Create progress bar
     */
    createProgressBar(container, options = {}) {
        const {
            id = this.generateLoadingId(),
            label = 'Progreso',
            showPercentage = true,
            animated = true
        } = options;

        const progressContainer = document.createElement('div');
        progressContainer.className = 'mb-4';
        progressContainer.innerHTML = `
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">${label}</span>
                ${showPercentage ? '<span class="text-sm text-gray-500 progress-percentage">0%</span>' : ''}
            </div>
            <div class="progress-bar ${animated ? '' : 'no-animation'}">
                <div class="progress-fill" style="width: 0%"></div>
            </div>
        `;

        if (container) {
            container.appendChild(progressContainer);
        }

        this.progressBars.set(id, {
            container: progressContainer,
            fill: progressContainer.querySelector('.progress-fill'),
            percentage: progressContainer.querySelector('.progress-percentage')
        });

        return id;
    }

    /**
     * Update progress bar
     */
    updateProgress(progressId, percentage, status = 'progress') {
        const progress = this.progressBars.get(progressId);
        if (!progress) return;

        const { fill, percentage: percentageElement, container } = progress;
        
        // Update width
        fill.style.width = `${Math.min(100, Math.max(0, percentage))}%`;
        
        // Update percentage text
        if (percentageElement) {
            percentageElement.textContent = `${Math.round(percentage)}%`;
        }
        
        // Update status classes
        const progressBar = container.querySelector('.progress-bar');
        progressBar.classList.remove('success', 'error');
        if (status !== 'progress') {
            progressBar.classList.add(status);
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info', duration = 5000, options = {}) {
        const {
            title = null,
            closable = true,
            persistent = false
        } = options;

        const toastId = this.generateLoadingId();
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.dataset.toastId = toastId;

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-title">
                    <span class="toast-icon">${icons[type] || icons.info}</span>
                    ${title || this.getDefaultTitle(type)}
                </div>
                ${closable ? `
                    <button type="button" class="toast-close" aria-label="Cerrar notificación">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                ` : ''}
            </div>
            <div class="toast-message">${message}</div>
        `;

        // Add close functionality
        if (closable) {
            const closeButton = toast.querySelector('.toast-close');
            closeButton.addEventListener('click', () => {
                this.hideToast(toastId);
            });
        }

        this.toastContainer.appendChild(toast);

        // Auto-hide after duration (unless persistent)
        if (!persistent && duration > 0) {
            setTimeout(() => {
                this.hideToast(toastId);
            }, duration);
        }

        return toastId;
    }

    /**
     * Hide toast notification
     */
    hideToast(toastId) {
        const toast = this.toastContainer.querySelector(`[data-toast-id="${toastId}"]`);
        if (toast) {
            toast.classList.add('hiding');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
    }

    /**
     * Show inline loading dots
     */
    showInlineLoading(element, text = 'Cargando') {
        if (!element) return;

        const loadingId = this.generateLoadingId();
        const originalContent = element.innerHTML;
        
        element.innerHTML = `
            <span>${text}</span>
            <span class="inline-loading ml-2">
                <span class="loading-dot"></span>
                <span class="loading-dot"></span>
                <span class="loading-dot"></span>
            </span>
        `;

        this.activeLoadings.set(loadingId, {
            type: 'inline',
            element: element,
            originalContent: originalContent
        });

        return loadingId;
    }

    /**
     * Hide inline loading
     */
    hideInlineLoading(loadingId) {
        const loading = this.activeLoadings.get(loadingId);
        if (!loading || loading.type !== 'inline') return;

        const { element, originalContent } = loading;
        element.innerHTML = originalContent;
        
        this.activeLoadings.delete(loadingId);
    }

    /**
     * Setup global loading handlers for AJAX requests
     */
    setupGlobalLoadingHandlers() {
        // Intercept fetch requests
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            const [url, options = {}] = args;
            
            // Show loading for validation requests
            if (url.includes('/validate-') || url.includes('/suggest-')) {
                // These are handled by ValidationEngine
                return originalFetch.apply(window, args);
            }
            
            // Show loading for other requests
            const loadingId = this.showToast('Procesando solicitud...', 'info', 0, { persistent: true });
            
            try {
                const response = await originalFetch.apply(window, args);
                this.hideToast(loadingId);
                
                if (!response.ok) {
                    this.showToast('Error en la solicitud', 'error');
                }
                
                return response;
            } catch (error) {
                this.hideToast(loadingId);
                this.showToast('Error de conexión', 'error');
                throw error;
            }
        };
    }

    /**
     * Utility methods
     */
    generateLoadingId() {
        return `loading_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    getDefaultTitle(type) {
        const titles = {
            success: 'Éxito',
            error: 'Error',
            warning: 'Advertencia',
            info: 'Información'
        };
        return titles[type] || 'Notificación';
    }

    /**
     * Clean up all active loadings
     */
    destroy() {
        // Hide global loading
        this.hideGlobalLoading();
        
        // Clean up active loadings
        this.activeLoadings.forEach((loading, id) => {
            switch (loading.type) {
                case 'form':
                    this.hideFormLoading(id);
                    break;
                case 'button':
                    this.hideButtonLoading(id);
                    break;
                case 'inline':
                    this.hideInlineLoading(id);
                    break;
            }
        });
        
        // Remove toast container
        if (this.toastContainer && this.toastContainer.parentNode) {
            this.toastContainer.parentNode.removeChild(this.toastContainer);
        }
        
        // Clear progress bars
        this.progressBars.clear();
        this.activeLoadings.clear();
    }
}

// Export for use in other modules
window.LoadingManager = LoadingManager;

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    if (!window.loadingManager) {
        window.loadingManager = new LoadingManager();
    }
});