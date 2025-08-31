{{--
    Post-Creation Success Flow Component
    
    Comprehensive success modal with next steps, direct links to store admin panel,
    welcome email automation, and store setup checklist generation
    
    Props:
    - store: Store model instance
    - credentials: Admin credentials array
    - show: Boolean to control modal visibility
    - class: Additional CSS classes
    
    Requirements: 4.5, 4.6 - Post-creation success flow
--}}

@props([
    'store' => null,
    'credentials' => [],
    'show' => false,
    'class' => ''
])

<div 
    x-data="postCreationSuccessFlow({
        store: {{ json_encode($store) }},
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
            class="inline-block align-bottom bg-accent rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full"
        >
            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 px-6 py-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-accent/20 backdrop-blur-sm">
                            <x-solar-check-circle-bold class="h-10 w-10 text-accent" />
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-accent">🎉 ¡Felicitaciones!</h3>
                            <p class="text-green-100 mt-1 text-lg">Tu tienda ha sido creada exitosamente</p>
                            <p class="text-green-200 text-sm mt-1" x-text="`Tienda: ${store.name}`"></p>
                        </div>
                    </div>
                    <button 
                        @click="closeModal()"
                        class="text-accent/80 hover:text-accent transition-colors"
                    >
                        <x-solar-close-circle-outline class="w-6 h-6" />
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="bg-accent">
                {{-- Progress Steps --}}
                <div class="px-6 py-6 bg-gray-50 border-b">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Progreso de Configuración</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="flex items-center gap-3 p-3 bg-green-100 rounded-lg">
                            <div class="flex-shrink-0 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <x-solar-check-circle-outline class="w-5 h-5 text-accent" />
                            </div>
                            <div>
                                <div class="font-medium text-green-800">Tienda Creada</div>
                                <div class="text-xs text-green-600">Completado</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 p-3 bg-blue-100 rounded-lg">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-accent font-bold text-sm">2</span>
                            </div>
                            <div>
                                <div class="font-medium text-blue-800">Configuración Inicial</div>
                                <div class="text-xs text-blue-600">Siguiente paso</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 p-3 bg-gray-100 rounded-lg">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center">
                                <span class="text-accent font-bold text-sm">3</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-600">Productos</div>
                                <div class="text-xs text-gray-500">Pendiente</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 p-3 bg-gray-100 rounded-lg">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center">
                                <span class="text-accent font-bold text-sm">4</span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-600">Lanzamiento</div>
                                <div class="text-xs text-gray-500">Pendiente</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Left Column: Quick Actions --}}
                        <div class="space-y-6">
                            {{-- Credentials Section --}}
                            <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                                <div class="flex items-center gap-3 mb-4">
                                    <x-solar-key-outline class="w-6 h-6 text-purple-600" />
                                    <h5 class="text-lg font-semibold text-purple-900">Credenciales de Acceso</h5>
                                </div>
                                
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between items-center">
                                        <span class="text-purple-700 font-medium">Email:</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono bg-accent px-2 py-1 rounded border" x-text="credentials.email"></span>
                                            <button 
                                                @click="copyToClipboard(credentials.email, 'Email')"
                                                class="text-purple-600 hover:text-purple-800"
                                                title="Copiar email"
                                            >
                                                <x-solar-copy-outline class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-purple-700 font-medium">Contraseña:</span>
                                        <div class="flex items-center gap-2">
                                            <span 
                                                class="font-mono bg-accent px-2 py-1 rounded border select-all"
                                                :class="{ 'blur-sm': !showPassword }"
                                                x-text="credentials.password"
                                            ></span>
                                            <button 
                                                @click="togglePasswordVisibility()"
                                                class="text-purple-600 hover:text-purple-800"
                                            >
                                                <x-solar-eye-outline x-show="!showPassword" class="w-4 h-4" />
                                                <x-solar-eye-closed-outline x-show="showPassword" class="w-4 h-4" />
                                            </button>
                                            <button 
                                                @click="copyToClipboard(credentials.password, 'Contraseña')"
                                                class="text-purple-600 hover:text-purple-800"
                                                title="Copiar contraseña"
                                            >
                                                <x-solar-copy-outline class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Access Links --}}
                            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                                <div class="flex items-center gap-3 mb-4">
                                    <x-solar-link-outline class="w-6 h-6 text-blue-600" />
                                    <h5 class="text-lg font-semibold text-blue-900">Acceso Rápido</h5>
                                </div>
                                
                                <div class="space-y-3">
                                    <a 
                                        :href="credentials.admin_url" 
                                        target="_blank"
                                        class="flex items-center gap-3 p-3 bg-accent rounded-lg border border-blue-200 hover:border-blue-400 hover:bg-blue-50 transition-colors group"
                                    >
                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200">
                                            <x-solar-settings-outline class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-blue-900">Panel de Administración</div>
                                            <div class="text-xs text-blue-600">Configura tu tienda</div>
                                        </div>
                                        <x-solar-link-outline class="w-4 h-4 text-blue-400" />
                                    </a>
                                    
                                    <a 
                                        :href="credentials.frontend_url" 
                                        target="_blank"
                                        class="flex items-center gap-3 p-3 bg-accent rounded-lg border border-blue-200 hover:border-blue-400 hover:bg-blue-50 transition-colors group"
                                    >
                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200">
                                            <x-solar-global-outline class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-blue-900">Ver Tienda</div>
                                            <div class="text-xs text-blue-600">Vista del cliente</div>
                                        </div>
                                        <x-solar-link-outline class="w-4 h-4 text-blue-400" />
                                    </a>
                                </div>
                            </div>

                            {{-- Welcome Email Status --}}
                            <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                                <div class="flex items-center gap-3 mb-4">
                                    <x-solar-letter-outline class="w-6 h-6 text-green-600" />
                                    <h5 class="text-lg font-semibold text-green-900">Email de Bienvenida</h5>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <div 
                                        x-show="!welcomeEmailSent"
                                        class="flex items-center gap-2 text-yellow-700"
                                    >
                                        <div class="w-4 h-4 border-2 border-yellow-600 border-t-transparent rounded-full animate-spin"></div>
                                        <span class="text-sm">Enviando email de bienvenida...</span>
                                    </div>
                                    
                                    <div 
                                        x-show="welcomeEmailSent"
                                        class="flex items-center gap-2 text-green-700"
                                    >
                                        <x-solar-check-circle-outline class="w-5 h-5" />
                                        <span class="text-sm">Email de bienvenida enviado</span>
                                    </div>
                                </div>
                                
                                <button 
                                    @click="resendWelcomeEmail()"
                                    :disabled="emailSending"
                                    class="mt-3 text-sm text-green-600 hover:text-green-800 disabled:opacity-50"
                                >
                                    Reenviar email
                                </button>
                            </div>
                        </div>

                        {{-- Right Column: Setup Checklist --}}
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <x-solar-checklist-outline class="w-6 h-6 text-gray-600" />
                                        <h5 class="text-lg font-semibold text-gray-900">Lista de Configuración</h5>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span x-text="getCompletedTasksCount()"></span>/<span x-text="setupTasks.length"></span> completadas
                                    </div>
                                </div>
                                
                                <div class="space-y-3">
                                    <template x-for="(task, index) in setupTasks" :key="index">
                                        <div 
                                            class="flex items-start gap-3 p-3 rounded-lg border"
                                            :class="task.completed ? 'bg-green-50 border-green-200' : 'bg-accent border-gray-200'"
                                        >
                                            <button 
                                                @click="toggleTask(index)"
                                                class="flex-shrink-0 mt-0.5"
                                            >
                                                <div 
                                                    class="w-5 h-5 rounded border-2 flex items-center justify-center"
                                                    :class="task.completed ? 'bg-green-500 border-green-500' : 'border-gray-300 hover:border-gray-400'"
                                                >
                                                    <x-solar-check-circle-outline 
                                                        x-show="task.completed" 
                                                        class="w-3 h-3 text-accent" 
                                                    />
                                                </div>
                                            </button>
                                            <div class="flex-1">
                                                <div 
                                                    class="font-medium"
                                                    :class="task.completed ? 'text-green-800 line-through' : 'text-gray-900'"
                                                    x-text="task.title"
                                                ></div>
                                                <div 
                                                    class="text-sm mt-1"
                                                    :class="task.completed ? 'text-green-600' : 'text-gray-600'"
                                                    x-text="task.description"
                                                ></div>
                                                <a 
                                                    x-show="task.link && !task.completed"
                                                    :href="task.link"
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 mt-2"
                                                >
                                                    <span>Ir a configuración</span>
                                                    <x-solar-link-outline class="w-3 h-3" />
                                                </a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                {{-- Progress Bar --}}
                                <div class="mt-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                                        <span>Progreso de configuración</span>
                                        <span x-text="`${getProgressPercentage()}%`"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div 
                                            class="bg-green-500 h-2 rounded-full transition-all duration-500"
                                            :style="`width: ${getProgressPercentage()}%`"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Next Steps --}}
                            <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                                <div class="flex items-center gap-3 mb-4">
                                    <x-solar-rocket-outline class="w-6 h-6 text-yellow-600" />
                                    <h5 class="text-lg font-semibold text-yellow-900">Próximos Pasos Recomendados</h5>
                                </div>
                                
                                <ol class="space-y-2 text-sm text-yellow-800">
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-5 h-5 bg-yellow-200 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                        <span>Accede al panel de administración y cambia la contraseña</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-5 h-5 bg-yellow-200 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                        <span>Configura la información básica de tu tienda</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-5 h-5 bg-yellow-200 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                        <span>Añade tus primeros productos o servicios</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-5 h-5 bg-yellow-200 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                        <span>Personaliza el diseño según tu marca</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <span class="flex-shrink-0 w-5 h-5 bg-yellow-200 rounded-full flex items-center justify-center text-xs font-bold">5</span>
                                        <span>Configura métodos de pago y envío</span>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <button 
                            @click="downloadSetupGuide()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-accent rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                        >
                            <x-solar-download-outline class="w-4 h-4" />
                            Descargar Guía
                        </button>
                        <button 
                            @click="sendWelcomeEmail()"
                            :disabled="emailSending"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-accent rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors text-sm font-medium"
                        >
                            <x-solar-letter-outline x-show="!emailSending" class="w-4 h-4" />
                            <div x-show="emailSending" class="w-4 h-4 border-2 border-accent border-t-transparent rounded-full animate-spin"></div>
                            <span x-text="emailSending ? 'Enviando...' : 'Enviar Guía por Email'"></span>
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <button 
                            @click="goToAdminPanel()"
                            class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-accent rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"
                        >
                            <x-solar-settings-outline class="w-4 h-4" />
                            Ir al Panel de Administración
                        </button>
                        <button 
                            @click="closeModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-accent rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/components/post-creation-success-flow.js') }}"></script>
@endpush