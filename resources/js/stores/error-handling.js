/**
 * 🛡️ SISTEMA CENTRALIZADO DE MANEJO DE ERRORES - GESTIÓN DE TIENDAS
 * Manejo unificado de errores y notificaciones
 */

class StoreErrorHandler {
    constructor() {
        this.notificationQueue = [];
        this.isProcessing = false;
    }

    init() {
        console.log('🛡️ ERROR HANDLER: Inicializando sistema');
        this.setupGlobalErrorHandling();
        this.setupFormErrorEnhancements();
        this.setupAjaxErrorHandling();
    }

    /**
     * 🌐 MANEJO GLOBAL DE ERRORES
     */
    setupGlobalErrorHandling() {
        // Interceptar errores de JavaScript
        window.addEventListener('error', (event) => {
            console.error('🔴 JS ERROR:', event.error);
            this.showNotification('Se produjo un error inesperado', 'error');
        });

        // Interceptar promesas rechazadas
        window.addEventListener('unhandledrejection', (event) => {
            console.error('🔴 UNHANDLED PROMISE:', event.reason);
            this.showNotification('Error de conexión', 'error');
        });
    }

    /**
     * 📝 MEJORAS EN FORMULARIOS
     */
    setupFormErrorEnhancements() {
        const forms = document.querySelectorAll('form[action*="stores"]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                this.handleFormSubmit(e, form);
            });

            // Mejorar visualización de errores de validación Laravel
            this.enhanceValidationErrors(form);
        });
    }

    handleFormSubmit(event, form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton?.textContent;
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin w-5 h-5 mr-2 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Procesando...
            `;
        }

        // Restaurar botón después de un tiempo en caso de error
        setTimeout(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }, 10000);
    }

    enhanceValidationErrors(form) {
        const errorElements = form.querySelectorAll('.text-error-300');
        
        errorElements.forEach(errorEl => {
            const input = errorEl.parentNode.querySelector('input, select, textarea');
            if (input) {
                input.classList.add('border-error-200');
                
                // Limpiar error cuando el usuario empiece a escribir
                input.addEventListener('input', () => {
                    input.classList.remove('border-error-200');
                    errorEl.style.opacity = '0.5';
                });

                input.addEventListener('focus', () => {
                    errorEl.style.opacity = '0.3';
                });

                input.addEventListener('blur', () => {
                    if (input.value.trim()) {
                        errorEl.style.opacity = '0.5';
                    } else {
                        errorEl.style.opacity = '1';
                    }
                });
            }
        });
    }

    /**
     * 📡 MANEJO DE ERRORES AJAX
     */
    setupAjaxErrorHandling() {
        // Interceptar fetch requests
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            try {
                const response = await originalFetch(...args);
                
                if (!response.ok) {
                    this.handleAjaxError(response);
                }
                
                return response;
            } catch (error) {
                this.handleNetworkError(error);
                throw error;
            }
        };
    }

    handleAjaxError(response) {
        if (response.status === 422) {
            this.showNotification('Por favor verifica los datos del formulario', 'warning');
        } else if (response.status === 500) {
            this.showNotification('Error interno del servidor', 'error');
        } else if (response.status === 403) {
            this.showNotification('No tienes permisos para esta acción', 'error');
        } else {
            this.showNotification(`Error ${response.status}: ${response.statusText}`, 'error');
        }
    }

    handleNetworkError(error) {
        console.error('🔴 NETWORK ERROR:', error);
        this.showNotification('Error de conexión. Verifica tu internet.', 'error');
    }

    /**
     * 🔔 SISTEMA DE NOTIFICACIONES
     */
    showNotification(message, type = 'info', duration = 5000) {
        // Intentar usar Alpine.js store si está disponible
        if (window.Alpine && Alpine.store && Alpine.store('notifications')) {
            Alpine.store('notifications').show(message, type, duration);
            return;
        }

        // Fallback: crear notificación manual
        this.createManualNotification(message, type, duration);
    }

    createManualNotification(message, type, duration) {
        const notificationId = 'notification-' + Date.now();
        const notification = document.createElement('div');
        
        const colors = {
            success: 'bg-success-200 text-accent-50',
            error: 'bg-error-200 text-accent-50', 
            warning: 'bg-warning-200 text-black-400',
            info: 'bg-info-200 text-accent-50'
        };

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        notification.id = notificationId;
        notification.className = `fixed top-4 right-4 z-50 max-w-sm ${colors[type]} rounded-lg shadow-lg p-4 flex items-center gap-3 transition-all duration-300 transform translate-x-full`;
        notification.innerHTML = `
            <span class="text-lg">${icons[type]}</span>
            <span class="text-sm font-medium flex-1">${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 text-lg opacity-70 hover:opacity-100">
                ×
            </button>
        `;

        document.body.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Auto-remover
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    /**
     * 🧹 LIMPIEZA Y UTILIDADES
     */
    clearAllNotifications() {
        document.querySelectorAll('[id^="notification-"]').forEach(el => el.remove());
    }

    logError(context, error, data = {}) {
        console.group(`🔴 ERROR - ${context}`);
        console.error('Message:', error.message || error);
        console.error('Stack:', error.stack);
        console.error('Data:', data);
        console.groupEnd();

        // En producción, enviar a servicio de logging
        if (window.location.hostname !== 'localhost') {
            this.sendErrorToServer(context, error, data);
        }
    }

    async sendErrorToServer(context, error, data) {
        try {
            await fetch('/superlinkiu/api/errors/log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({
                    context: context,
                    message: error.message || error.toString(),
                    stack: error.stack,
                    url: window.location.href,
                    user_agent: navigator.userAgent,
                    data: data
                })
            });
        } catch (logError) {
            console.error('Failed to log error to server:', logError);
        }
    }
}

// 🚀 AUTO-INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    const errorHandler = new StoreErrorHandler();
    errorHandler.init();
    
    // Hacer disponible globalmente
    window.storeErrorHandler = errorHandler;
});

// 📤 EXPORTAR
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StoreErrorHandler;
}
