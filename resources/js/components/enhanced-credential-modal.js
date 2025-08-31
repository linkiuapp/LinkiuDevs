/**
 * Enhanced Credential Modal Component
 * 
 * Improved credential display modal with better styling, one-click copy functionality,
 * email delivery option, and credential strength indicators
 * Requirements: 4.1, 4.2, 4.3, 4.4 - Enhanced credential management and display
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('enhancedCredentialModal', (config = {}) => ({
        // Configuration
        credentials: config.credentials || {},
        showModal: config.show || false,
        
        // State
        showPassword: false,
        emailSending: false,
        copyStatus: {
            show: false,
            success: false,
            message: ''
        },
        
        // Password strength analysis
        passwordChecks: {
            length: false,
            uppercase: false,
            lowercase: false,
            numbers: false,
            special: false
        },

        init() {
            console.log('🔐 Enhanced Credential Modal: Initialized', this.credentials);
            
            // Analyze password strength
            if (this.credentials.password) {
                this.analyzePasswordStrength(this.credentials.password);
            }
            
            // Auto-hide copy status after 3 seconds
            this.$watch('copyStatus.show', (show) => {
                if (show) {
                    setTimeout(() => {
                        this.copyStatus.show = false;
                    }, 3000);
                }
            });
        },

        /**
         * Password Analysis Methods
         */
        analyzePasswordStrength(password) {
            this.passwordChecks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                numbers: /\d/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password)
            };
        },

        getPasswordStrengthScore() {
            return Object.values(this.passwordChecks).filter(Boolean).length;
        },

        getPasswordStrengthLabel() {
            const score = this.getPasswordStrengthScore();
            const labels = ['Muy débil', 'Débil', 'Regular', 'Buena', 'Fuerte', 'Muy fuerte'];
            return labels[score] || 'Muy débil';
        },

        getPasswordStrengthColor() {
            const score = this.getPasswordStrengthScore();
            const colors = [
                'bg-red-500',    // 0
                'bg-red-400',    // 1
                'bg-yellow-500', // 2
                'bg-yellow-400', // 3
                'bg-green-500',  // 4
                'bg-green-600'   // 5
            ];
            return colors[score] || 'bg-gray-300';
        },

        getPasswordStrengthTextColor() {
            const score = this.getPasswordStrengthScore();
            const colors = [
                'text-red-700',    // 0
                'text-red-600',    // 1
                'text-yellow-700', // 2
                'text-yellow-600', // 3
                'text-green-700',  // 4
                'text-green-800'   // 5
            ];
            return colors[score] || 'text-gray-600';
        },

        /**
         * UI Methods
         */
        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },

        closeModal() {
            this.showModal = false;
            
            // Emit close event
            this.$dispatch('credential-modal-closed');
        },

        showCopyStatus(success, message) {
            this.copyStatus = {
                show: true,
                success: success,
                message: message
            };
        },

        /**
         * Copy Methods
         */
        async copyToClipboard(text, label = 'Texto') {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                    this.showCopyStatus(true, `${label} copiado al portapapeles`);
                    console.log('✅ Copied to clipboard:', label);
                } else {
                    // Fallback for older browsers
                    this.fallbackCopy(text);
                    this.showCopyStatus(true, `${label} copiado al portapapeles`);
                }
            } catch (error) {
                console.error('❌ Copy failed:', error);
                this.showCopyStatus(false, 'Error al copiar al portapapeles');
            }
        },

        fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
            } catch (error) {
                console.error('Fallback copy failed:', error);
                throw error;
            } finally {
                document.body.removeChild(textArea);
            }
        },

        async copyAllCredentials() {
            const credentialText = this.formatCredentialsForCopy();
            await this.copyToClipboard(credentialText, 'Todas las credenciales');
        },

        formatCredentialsForCopy() {
            return `
CREDENCIALES DE ACCESO - ${this.credentials.store_name}
=====================================================

INFORMACIÓN DE LA TIENDA:
- Nombre: ${this.credentials.store_name}
- URL: ${this.credentials.store_slug}
- Frontend: ${this.credentials.frontend_url}
- Admin Panel: ${this.credentials.admin_url}

CREDENCIALES DEL ADMINISTRADOR:
- Nombre: ${this.credentials.name}
- Email: ${this.credentials.email}
- Contraseña: ${this.credentials.password}

IMPORTANTE:
- Cambia la contraseña después del primer acceso
- Guarda estas credenciales en un lugar seguro
- No compartas por medios inseguros

Generado el: ${new Date().toLocaleString()}
            `.trim();
        },

        /**
         * Email Methods
         */
        async sendCredentialsByEmail() {
            try {
                this.emailSending = true;
                
                const response = await fetch('/superlinkiu/api/stores/send-credentials-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        email: this.credentials.email,
                        credentials: this.credentials
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.showCopyStatus(true, 'Credenciales enviadas por email exitosamente');
                } else {
                    this.showCopyStatus(false, result.message || 'Error al enviar email');
                }

            } catch (error) {
                console.error('Email send error:', error);
                this.showCopyStatus(false, 'Error al enviar email');
            } finally {
                this.emailSending = false;
            }
        },

        /**
         * Download Methods
         */
        downloadCredentials() {
            const credentialText = this.formatCredentialsForCopy();
            const blob = new Blob([credentialText], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `credenciales-${this.credentials.store_slug}-${new Date().toISOString().split('T')[0]}.txt`;
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            window.URL.revokeObjectURL(url);
            
            this.showCopyStatus(true, 'Credenciales descargadas exitosamente');
        },

        /**
         * Utility Methods
         */
        formatDate(date) {
            return new Date(date).toLocaleString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        /**
         * Keyboard Shortcuts
         */
        handleKeydown(event) {
            // Ctrl/Cmd + C: Copy all credentials
            if ((event.ctrlKey || event.metaKey) && event.key === 'c' && !event.target.matches('input, textarea')) {
                event.preventDefault();
                this.copyAllCredentials();
            }
            
            // Escape: Close modal
            if (event.key === 'Escape') {
                this.closeModal();
            }
            
            // Ctrl/Cmd + S: Download credentials
            if ((event.ctrlKey || event.metaKey) && event.key === 's') {
                event.preventDefault();
                this.downloadCredentials();
            }
        }
    }));
});

// Global keyboard event listener for shortcuts
document.addEventListener('keydown', (event) => {
    // Only handle if modal is open
    const modal = document.querySelector('[x-data*="enhancedCredentialModal"]');
    if (modal && modal.__x && modal.__x.$data.showModal) {
        modal.__x.$data.handleKeydown(event);
    }
});

console.log('🔐 Enhanced Credential Modal Component: Loaded');