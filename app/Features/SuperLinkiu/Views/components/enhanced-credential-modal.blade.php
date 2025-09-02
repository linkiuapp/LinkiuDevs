{{--
    Enhanced Credential Management Modal
    
    Improved credential display modal with better styling, one-click copy functionality,
    email delivery option, and credential strength indicators
    
    Props:
    - credentials: Array with store and admin credentials
    - show: Boolean to control modal visibility
    - class: Additional CSS classes
    
    Requirements: 4.1, 4.2, 4.3, 4.4 - Enhanced credential management and display
--}}

@props([
    'credentials' => [],
    'show' => false,
    'class' => ''
])

<div 
    x-data="enhancedCredentialModal({
        credentials: {{ json_encode($credentials) }},
        show: {{ $show ? 'true' : 'false' }}
    })"
    x-show="showModal" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto {{ $class }}"
    style="display: none;"
    x-cloak
>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div 
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity"
            @click="closeModal()"
        >
            <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"></div>
        </div>

        {{-- Modal --}}
        <div 
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
        >
            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-white/20 backdrop-blur-sm">
                            <x-solar-check-circle-bold class="h-7 w-7 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">¡Tienda Creada Exitosamente!</h3>
                            <p class="text-green-100 mt-1">Credenciales de acceso generadas</p>
                        </div>
                    </div>
                    <button 
                        @click="closeModal()"
                        class="text-white/80 hover:text-white transition-colors"
                    >
                        <x-solar-close-circle-outline class="w-6 h-6" />
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="bg-gray-50 px-6 py-6">
                {{-- Store Information --}}
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <x-solar-shop-outline class="w-6 h-6 text-blue-600" />
                        <h4 class="text-lg font-semibold text-gray-900">Información de la Tienda</h4>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 font-medium">Nombre:</span>
                                    <span class="font-semibold text-gray-900" x-text="credentials.store_name"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 font-medium">URL:</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm bg-blue-50 px-2 py-1 rounded border border-blue-200" x-text="credentials.store_slug"></span>
                                        <button 
                                            @click="copyToClipboard(credentials.store_slug, 'URL de la tienda')"
                                            class="text-blue-600 hover:text-blue-800 transition-colors"
                                            title="Copiar URL"
                                        >
                                            <x-solar-copy-outline class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <x-solar-global-outline class="w-4 h-4 text-blue-600" />
                                    <a 
                                        :href="credentials.frontend_url" 
                                        target="_blank" 
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-1"
                                    >
                                        Ver tienda
                                        <x-solar-link-outline class="w-3 h-3" />
                                    </a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-solar-settings-outline class="w-4 h-4 text-blue-600" />
                                    <a 
                                        :href="credentials.admin_url" 
                                        target="_blank" 
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-1"
                                    >
                                        Panel de administración
                                        <x-solar-link-outline class="w-3 h-3" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Admin Credentials --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <x-solar-user-outline class="w-6 h-6 text-purple-600" />
                            <h4 class="text-lg font-semibold text-gray-900">Credenciales del Administrador</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Seguridad:</span>
                            <div class="flex items-center gap-1">
                                <div 
                                    class="h-2 w-8 rounded-full"
                                    :class="getPasswordStrengthColor()"
                                ></div>
                                <span 
                                    class="text-xs font-medium"
                                    :class="getPasswordStrengthTextColor()"
                                    x-text="getPasswordStrengthLabel()"
                                ></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="space-y-4">
                            {{-- Name --}}
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Nombre:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900" x-text="credentials.name"></span>
                                    <button 
                                        @click="copyToClipboard(credentials.name, 'Nombre del administrador')"
                                        class="text-purple-600 hover:text-purple-800 transition-colors"
                                        title="Copiar nombre"
                                    >
                                        <x-solar-copy-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Email:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm bg-green-50 px-2 py-1 rounded border border-green-200" x-text="credentials.email"></span>
                                    <button 
                                        @click="copyToClipboard(credentials.email, 'Email del administrador')"
                                        class="text-purple-600 hover:text-purple-800 transition-colors"
                                        title="Copiar email"
                                    >
                                        <x-solar-copy-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Contraseña:</span>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-2">
                                        <span 
                                            class="font-mono text-sm bg-yellow-50 px-3 py-2 rounded border border-yellow-200 select-all"
                                            :class="{ 'blur-sm': !showPassword }"
                                            x-text="credentials.password"
                                        ></span>
                                        <button 
                                            @click="togglePasswordVisibility()"
                                            class="text-gray-500 hover:text-gray-700 transition-colors"
                                            :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                        >
                                            <x-solar-eye-outline x-show="!showPassword" class="w-4 h-4" />
                                            <x-solar-eye-closed-outline x-show="showPassword" class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <button 
                                        @click="copyToClipboard(credentials.password, 'Contraseña del administrador')"
                                        class="text-purple-600 hover:text-purple-800 transition-colors"
                                        title="Copiar contraseña"
                                    >
                                        <x-solar-copy-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            {{-- Password Strength Indicators --}}
                            <div class="bg-amber-50 rounded-lg p-3 border border-amber-200">
                                <div class="text-xs text-gray-600 mb-2">Características de seguridad:</div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="flex items-center gap-1">
                                        <div 
                                            class="w-3 h-3 rounded-full"
                                            :class="passwordChecks.length ? 'bg-green-500' : 'bg-gray-300'"
                                        ></div>
                                        <span :class="passwordChecks.length ? 'text-green-700' : 'text-gray-500'">
                                            8+ caracteres
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <div 
                                            class="w-3 h-3 rounded-full"
                                            :class="passwordChecks.uppercase ? 'bg-green-500' : 'bg-gray-300'"
                                        ></div>
                                        <span :class="passwordChecks.uppercase ? 'text-green-700' : 'text-gray-500'">
                                            Mayúsculas
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <div 
                                            class="w-3 h-3 rounded-full"
                                            :class="passwordChecks.lowercase ? 'bg-green-500' : 'bg-gray-300'"
                                        ></div>
                                        <span :class="passwordChecks.lowercase ? 'text-green-700' : 'text-gray-500'">
                                            Minúsculas
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <div 
                                            class="w-3 h-3 rounded-full"
                                            :class="passwordChecks.numbers ? 'bg-green-500' : 'bg-gray-300'"
                                        ></div>
                                        <span :class="passwordChecks.numbers ? 'text-green-700' : 'text-gray-500'">
                                            Números
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <div 
                                            class="w-3 h-3 rounded-full"
                                            :class="passwordChecks.special ? 'bg-green-500' : 'bg-gray-300'"
                                        ></div>
                                        <span :class="passwordChecks.special ? 'text-green-700' : 'text-gray-500'">
                                            Especiales
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Security Warning --}}
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <x-solar-shield-warning-outline class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" />
                        <div>
                            <h5 class="font-semibold text-amber-800 mb-1">¡Información Importante!</h5>
                            <p class="text-amber-700 text-sm mb-2">
                                Esta es la única vez que se mostrarán estas credenciales. Asegúrate de guardarlas en un lugar seguro.
                            </p>
                            <ul class="text-amber-700 text-xs space-y-1">
                                <li>• Cambia la contraseña después del primer acceso</li>
                                <li>• No compartas estas credenciales por medios inseguros</li>
                                <li>• Usa un gestor de contraseñas para almacenarlas</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Copy Status --}}
                <div 
                    x-show="copyStatus.show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform translate-y-2"
                    class="mb-4"
                >
                    <div 
                        class="flex items-center gap-2 p-3 rounded-lg"
                        :class="copyStatus.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                        <x-solar-check-circle-outline x-show="copyStatus.success" class="w-5 h-5" />
                        <x-solar-close-circle-outline x-show="!copyStatus.success" class="w-5 h-5" />
                        <span x-text="copyStatus.message"></span>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 px-6 py-4">
                <div class="flex flex-col sm:flex-row justify-between gap-3">
                    <div class="flex gap-2">
                        <button 
                            @click="copyAllCredentials()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                        >
                            <x-solar-copy-outline class="w-4 h-4" />
                            Copiar Todo
                        </button>
                        <button 
                            @click="sendCredentialsByEmail()"
                            :disabled="emailSending"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                        >
                            <x-solar-letter-outline x-show="!emailSending" class="w-4 h-4" />
                            <div x-show="emailSending" class="w-4 h-4 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
                            <span x-text="emailSending ? 'Enviando...' : 'Enviar por Email'"></span>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            @click="downloadCredentials()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium"
                        >
                            <x-solar-download-outline class="w-4 h-4" />
                            Descargar
                        </button>
                        <button 
                            @click="closeModal()"
                            class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"
                        >
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
/**
 * Enhanced Credential Modal Component
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

        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },

        closeModal() {
            this.showModal = false;
            this.$dispatch('credential-modal-closed');
        },

        showCopyStatus(success, message) {
            this.copyStatus = {
                show: true,
                success: success,
                message: message
            };
        },

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
        }
    }));
});

console.log('🔐 Enhanced Credential Modal Component: Loaded inline');
</script>
@endpush