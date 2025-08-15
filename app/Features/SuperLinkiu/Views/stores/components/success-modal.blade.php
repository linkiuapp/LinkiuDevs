{{-- ================================================================ --}}
{{-- MODAL DE ÉXITO - Tienda Creada --}}
{{-- ================================================================ --}}

@if(session('admin_credentials'))
{{-- Debug script para verificar que se ejecute --}}
<script>
console.log('🟢 SUCCESS MODAL: Modal de éxito detectado en DOM');
console.log('📊 SUCCESS MODAL: Credenciales disponibles:', @json(session('admin_credentials')));
</script>

<div x-data="{ 
        showSuccessModal: true,
        init() {
            console.log('🟢 ALPINE SUCCESS MODAL: Inicializado correctamente');
            console.log('📊 ALPINE SUCCESS MODAL: showSuccessModal =', this.showSuccessModal);
        }
     }" 
     x-show="showSuccessModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;"
     x-cloak>
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div x-show="showSuccessModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-black-500/75 backdrop-blur-sm"></div>
        </div>

        {{-- Modal --}}
        <div x-show="showSuccessModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            {{-- Header --}}
            <div class="bg-success-50 px-6 py-4 border-b border-success-100">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-success-200">
                        <x-solar-check-circle-bold class="h-6 w-6 text-white-50" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-success-300">¡Tienda Creada Exitosamente!</h3>
                        <p class="text-sm text-success-200">La tienda y el usuario administrador han sido configurados correctamente.</p>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="bg-white-50 px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Información de la Tienda --}}
                    <div class="space-y-4">
                        <h4 class="text-base font-semibold text-black-400 flex items-center gap-2">
                            <x-solar-shop-outline class="w-5 h-5 text-primary-300" />
                            Información de la Tienda
                        </h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-black-200">Nombre:</span>
                                <span class="font-medium text-black-400">{{ session('admin_credentials')['store_name'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black-200">URL:</span>
                                <span class="font-medium text-black-400">{{ session('admin_credentials')['store_slug'] }}</span>
                            </div>
                            <div class="pt-2 border-t border-white-100">
                                <p class="text-black-200 mb-2">Enlaces de acceso:</p>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <x-solar-global-outline class="w-4 h-4 text-info-300" />
                                        <a href="{{ session('admin_credentials')['frontend_url'] }}" 
                                           target="_blank" 
                                           class="text-info-300 hover:text-info-200 text-sm font-medium">
                                            Frontend de la tienda
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-solar-settings-outline class="w-4 h-4 text-primary-300" />
                                        <a href="{{ session('admin_credentials')['admin_url'] }}" 
                                           target="_blank" 
                                           class="text-primary-300 hover:text-primary-200 text-sm font-medium">
                                            Panel de administración
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Credenciales del Administrador --}}
                    <div class="space-y-4">
                        <h4 class="text-base font-semibold text-black-400 flex items-center gap-2">
                            <x-solar-user-outline class="w-5 h-5 text-secondary-300" />
                            Credenciales del Administrador
                        </h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-black-200">Nombre:</span>
                                <span class="font-medium text-black-400">{{ session('admin_credentials')['name'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black-200">Email:</span>
                                <span class="font-medium text-black-400">{{ session('admin_credentials')['email'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black-200">Contraseña:</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-black-400 bg-white-100 px-2 py-1 rounded text-xs">{{ session('admin_credentials')['password'] }}</span>
                                    <button onclick="copyToClipboard('{{ session('admin_credentials')['password'] }}')" 
                                            class="text-primary-300 hover:text-primary-200" 
                                            title="Copiar contraseña">
                                        <x-solar-copy-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Advertencia importante --}}
                        <div class="bg-warning-50 border border-warning-100 rounded-lg p-3 mt-4">
                            <div class="flex items-start gap-2">
                                <x-solar-danger-triangle-outline class="w-5 h-5 text-warning-300 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-warning-300 font-medium text-sm">¡Importante!</p>
                                    <p class="text-warning-200 text-xs mt-1">
                                        Guarda estas credenciales en un lugar seguro. Esta es la única vez que se mostrará la contraseña.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-white-100 px-6 py-4">
                <div class="flex justify-end gap-3">
                    <button onclick="copyCredentials()" 
                            class="btn-outline-primary px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                        <x-solar-copy-outline class="w-4 h-4" />
                        Copiar Credenciales
                    </button>
                    <button @click="showSuccessModal = false; console.log('🟢 SUCCESS MODAL: Modal cerrado por el usuario');" 
                            class="btn-primary px-6 py-2 rounded-lg text-sm">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif 