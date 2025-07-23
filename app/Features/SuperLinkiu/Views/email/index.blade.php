@extends('shared::layouts.admin')

@section('title', 'Configuración de Email')

@section('content')
<div class="container-fluid" x-data="emailConfiguration()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Configuración de Email</h1>
            <p class="text-sm text-black-300">Configurar SMTP y plantillas para notificaciones de tickets</p>
        </div>
        <div class="flex items-center gap-3">
            @if($config->exists && $config->isComplete())
                <button @click="testConnection()" 
                        class="btn-outline-info px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-verified-check-outline class="w-4 h-4" />
                    Probar Conexión
                </button>
            @endif
            <a href="{{ route('superlinkiu.tickets.index') }}" 
               class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver a Tickets
            </a>
        </div>
    </div>

    <!-- Estado de la configuración -->
    @if($config->exists)
        <div class="mb-6">
            <div class="flex items-center gap-4 p-4 rounded-lg {{ $config->is_active ? 'bg-success-100' : 'bg-warning-100' }}">
                <div class="flex-shrink-0">
                    @if($config->is_active)
                        <x-solar-check-circle-outline class="w-6 h-6 text-success-300" />
                    @else
                        <x-solar-clock-circle-outline class="w-6 h-6 text-warning-300" />
                    @endif
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-black-400">
                        Estado: {{ $config->is_active ? 'Activo' : 'Inactivo' }}
                    </h3>
                    <p class="text-sm text-black-300">
                        @if($config->is_active)
                            La configuración está activa y funcionando.
                        @else
                            La configuración está desactivada. Los emails no se enviarán.
                        @endif
                        @if($config->last_test_at)
                            <br>Última prueba: {{ $config->last_test_at->format('d/m/Y H:i') }} - {{ $config->last_test_result }}
                        @endif
                    </p>
                </div>
                <button @click="toggleActive()" 
                        class="btn-outline-{{ $config->is_active ? 'warning' : 'success' }} px-4 py-2 rounded-lg">
                    {{ $config->is_active ? 'Desactivar' : 'Activar' }}
                </button>
            </div>
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-white-100">
            <nav class="flex">
                <button @click="activeTab = 'smtp'" 
                        :class="activeTab === 'smtp' ? 'border-primary-300 text-primary-300' : 'border-transparent text-black-300 hover:text-black-400'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center gap-2">
                    <x-solar-settings-outline class="w-4 h-4" />
                    Configuración SMTP
                </button>
                <button @click="activeTab = 'events'" 
                        :class="activeTab === 'events' ? 'border-primary-300 text-primary-300' : 'border-transparent text-black-300 hover:text-black-400'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center gap-2">
                    <x-solar-bell-outline class="w-4 h-4" />
                    Eventos de Email
                </button>
                <button @click="activeTab = 'templates'" 
                        :class="activeTab === 'templates' ? 'border-primary-300 text-primary-300' : 'border-transparent text-black-300 hover:text-black-400'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200 flex items-center gap-2">
                    <x-solar-document-text-outline class="w-4 h-4" />
                    Plantillas
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Tab SMTP -->
            <div x-show="activeTab === 'smtp'" x-transition>
                <form method="POST" action="{{ route('superlinkiu.email.update-smtp') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Servidor SMTP -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Servidor SMTP *</label>
                            <input type="text" 
                                   name="smtp_host" 
                                   value="{{ old('smtp_host', $config->smtp_host) }}"
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                   placeholder="smtp.gmail.com"
                                   required>
                            @error('smtp_host')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Puerto -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Puerto *</label>
                            <input type="number" 
                                   name="smtp_port" 
                                   value="{{ old('smtp_port', $config->smtp_port ?: 587) }}"
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                   min="1" max="65535"
                                   required>
                            @error('smtp_port')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Usuario -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Usuario SMTP *</label>
                            <input type="text" 
                                   name="smtp_username" 
                                   value="{{ old('smtp_username', $config->smtp_username) }}"
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                   placeholder="usuario@gmail.com"
                                   required>
                            @error('smtp_username')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Contraseña SMTP {{ $config->exists ? '(dejar vacío para mantener actual)' : '*' }}
                            </label>
                            <input type="password" 
                                   name="smtp_password" 
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                   placeholder="{{ $config->exists ? '••••••••' : 'Contraseña' }}"
                                   {{ !$config->exists ? 'required' : '' }}>
                            @error('smtp_password')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Encriptación -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Encriptación</label>
                            <select name="smtp_encryption" 
                                    class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                                <option value="tls" {{ old('smtp_encryption', $config->smtp_encryption) === 'tls' ? 'selected' : '' }}>TLS (recomendado)</option>
                                <option value="ssl" {{ old('smtp_encryption', $config->smtp_encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ old('smtp_encryption', $config->smtp_encryption) === 'none' ? 'selected' : '' }}>Ninguna</option>
                            </select>
                            @error('smtp_encryption')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email remitente -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Email remitente *</label>
                            <input type="email" 
                                   name="from_email" 
                                   value="{{ old('from_email', $config->from_email) }}"
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                   placeholder="noreply@linkiu.bio"
                                   required>
                            @error('from_email')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nombre remitente -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Nombre remitente *</label>
                            <input type="text" 
                                   name="from_name" 
                                   value="{{ old('from_name', $config->from_name ?: 'Linkiu.bio Support') }}"
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                   placeholder="Linkiu.bio Support"
                                   required>
                            @error('from_name')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                            Guardar Configuración SMTP
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab Eventos -->
            <div x-show="activeTab === 'events'" x-transition>
                <form method="POST" action="{{ route('superlinkiu.email.update-events') }}">
                    @csrf
                    <div class="space-y-4">
                        <p class="text-sm text-black-300">Selecciona qué eventos deben generar notificaciones por email:</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                                <input type="checkbox" 
                                       name="send_on_ticket_created" 
                                       value="1"
                                       {{ old('send_on_ticket_created', $config->send_on_ticket_created) ? 'checked' : '' }}
                                       class="mr-3 h-4 w-4 text-primary-300 focus:ring-primary-200 border-white-200 rounded">
                                <div>
                                    <div class="font-medium text-black-400">Nuevo ticket creado</div>
                                    <div class="text-sm text-black-300">Notificar cuando se crea un nuevo ticket</div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                                <input type="checkbox" 
                                       name="send_on_ticket_response" 
                                       value="1"
                                       {{ old('send_on_ticket_response', $config->send_on_ticket_response) ? 'checked' : '' }}
                                       class="mr-3 h-4 w-4 text-primary-300 focus:ring-primary-200 border-white-200 rounded">
                                <div>
                                    <div class="font-medium text-black-400">Nueva respuesta</div>
                                    <div class="text-sm text-black-300">Notificar cuando se agrega una respuesta</div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                                <input type="checkbox" 
                                       name="send_on_status_change" 
                                       value="1"
                                       {{ old('send_on_status_change', $config->send_on_status_change) ? 'checked' : '' }}
                                       class="mr-3 h-4 w-4 text-primary-300 focus:ring-primary-200 border-white-200 rounded">
                                <div>
                                    <div class="font-medium text-black-400">Cambio de estado</div>
                                    <div class="text-sm text-black-300">Notificar cuando cambia el estado del ticket</div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                                <input type="checkbox" 
                                       name="send_on_ticket_assigned" 
                                       value="1"
                                       {{ old('send_on_ticket_assigned', $config->send_on_ticket_assigned) ? 'checked' : '' }}
                                       class="mr-3 h-4 w-4 text-primary-300 focus:ring-primary-200 border-white-200 rounded">
                                <div>
                                    <div class="font-medium text-black-400">Ticket asignado</div>
                                    <div class="text-sm text-black-300">Notificar cuando se asigna un ticket a un admin</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                            Guardar Eventos
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab Plantillas -->
            <div x-show="activeTab === 'templates'" x-transition>
                <form method="POST" action="{{ route('superlinkiu.email.update-templates') }}">
                    @csrf
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-black-300">
                                Variables disponibles: <code>@{{ticket_number}}</code>, <code>@{{title}}</code>, <code>@{{url}}</code>, <code>@{{old_status}}</code>, <code>@{{new_status}}</code>, <code>@{{response_preview}}</code>
                            </p>
                            <button type="button" 
                                    @click="restoreDefaultTemplates()"
                                    class="btn-outline-secondary px-4 py-2 rounded-lg text-sm">
                                Restaurar por defecto
                            </button>
                        </div>

                        <!-- Plantilla nuevo ticket -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Plantilla: Nuevo ticket creado</label>
                            <textarea name="ticket_created_template" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                      placeholder="{{ $defaultTemplates['ticket_created'] }}">{{ old('ticket_created_template', $config->ticket_created_template ?: $defaultTemplates['ticket_created']) }}</textarea>
                        </div>

                        <!-- Plantilla nueva respuesta -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Plantilla: Nueva respuesta</label>
                            <textarea name="ticket_response_template" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                      placeholder="{{ $defaultTemplates['ticket_response'] }}">{{ old('ticket_response_template', $config->ticket_response_template ?: $defaultTemplates['ticket_response']) }}</textarea>
                        </div>

                        <!-- Plantilla cambio estado -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Plantilla: Cambio de estado</label>
                            <textarea name="ticket_status_changed_template" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                      placeholder="{{ $defaultTemplates['ticket_status_changed'] }}">{{ old('ticket_status_changed_template', $config->ticket_status_changed_template ?: $defaultTemplates['ticket_status_changed']) }}</textarea>
                        </div>

                        <!-- Plantilla asignación -->
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">Plantilla: Ticket asignado</label>
                            <textarea name="ticket_assigned_template" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                      placeholder="{{ $defaultTemplates['ticket_assigned'] }}">{{ old('ticket_assigned_template', $config->ticket_assigned_template ?: $defaultTemplates['ticket_assigned']) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                            Guardar Plantillas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para probar conexión -->
    <div x-show="showTestModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black-400 bg-opacity-75 transition-opacity" @click="showTestModal = false"></div>

            <div class="inline-block align-bottom bg-white-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-black-400 mb-4">Probar Conexión SMTP</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black-300 mb-2">Email de prueba (opcional)</label>
                        <input type="email" 
                               x-model="testEmail"
                               class="w-full px-3 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                               placeholder="{{ $config->from_email }}">
                        <p class="text-xs text-black-300 mt-1">Si no especificas un email, se usará el email remitente configurado.</p>
                    </div>
                </div>
                <div class="bg-white-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="sendTestEmail()" 
                            :disabled="testing"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary-300 text-base font-medium text-white-50 hover:bg-primary-400 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!testing">Enviar Email de Prueba</span>
                        <span x-show="testing">Enviando...</span>
                    </button>
                    <button @click="showTestModal = false" 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-white-200 shadow-sm px-4 py-2 bg-white-50 text-base font-medium text-black-400 hover:bg-white-100 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function emailConfiguration() {
    return {
        activeTab: 'smtp',
        showTestModal: false,
        testEmail: '',
        testing: false,

        testConnection() {
            this.showTestModal = true;
        },

        sendTestEmail() {
            this.testing = true;
            
            fetch('{{ route("superlinkiu.email.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    test_email: this.testEmail
                })
            })
            .then(response => response.json())
            .then(data => {
                this.testing = false;
                this.showTestModal = false;
                
                if (data.success) {
                    this.showNotification('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showNotification('error', data.message);
                }
            })
            .catch(error => {
                this.testing = false;
                this.showTestModal = false;
                this.showNotification('error', 'Error al probar la conexión');
                console.error('Error:', error);
            });
        },

        toggleActive() {
            fetch('{{ route("superlinkiu.email.toggle-active") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotification('error', data.message);
                }
            })
            .catch(error => {
                this.showNotification('error', 'Error al cambiar el estado');
                console.error('Error:', error);
            });
        },

        restoreDefaultTemplates() {
            if (!confirm('¿Estás seguro de restaurar las plantillas por defecto? Se perderán los cambios actuales.')) {
                return;
            }

            fetch('{{ route("superlinkiu.email.restore-templates") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotification('error', 'Error al restaurar plantillas');
                }
            })
            .catch(error => {
                this.showNotification('error', 'Error al restaurar plantillas');
                console.error('Error:', error);
            });
        },

        showNotification(type, message) {
            // Crear notificación temporal
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
                type === 'success' ? 'bg-success-300 text-white-50' : 'bg-error-300 text-white-50'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }
}
</script>
@endpush
@endsection 