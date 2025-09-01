@extends('shared::layouts.admin')

@section('title', 'Sistema de Emails')

@section('content')
<div class="container-fluid" x-data="emailDashboard()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Sistema de Emails</h1>
            <p class="text-sm text-black-300">Gestión centralizada de notificaciones por email</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showTestModal = true" 
                    class="btn-outline-info px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-verified-check-outline class="w-4 h-4" />
                Probar SMTP
            </button>
            <a href="{{ route('superlinkiu.email.templates') }}" 
               class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-document-text-outline class="w-4 h-4" />
                Gestionar Plantillas
            </a>
        </div>
    </div>

    <!-- Estado de la configuración -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Estado SMTP -->
        <div class="bg-accent-50 rounded-lg p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg {{ $validation['valid'] ? 'bg-success-100' : 'bg-error-100' }}">
                    @if($validation['valid'])
                        <x-solar-check-circle-outline class="w-6 h-6 text-success-300" />
                    @else
                        <x-solar-close-circle-outline class="w-6 h-6 text-error-300" />
                    @endif
                </div>
                <div>
                    <h3 class="font-semibold text-black-400">Configuración SMTP</h3>
                    <p class="text-sm text-black-300">
                        {{ $validation['valid'] ? 'Configurado correctamente' : 'Necesita configuración' }}
                    </p>
                </div>
            </div>
            @if(!$validation['valid'])
                <div class="mt-4 space-y-1">
                    @foreach($validation['issues'] as $issue)
                        <p class="text-xs text-error-300">• {{ $issue }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Estadísticas -->
        <div class="bg-accent-50 rounded-lg p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-primary-100 rounded-lg">
                    <x-solar-chart-outline class="w-6 h-6 text-primary-300" />
                </div>
                <div>
                    <h3 class="font-semibold text-black-400">Plantillas Activas</h3>
                    <p class="text-sm text-black-300">{{ $stats['templates_count'] }} plantillas disponibles</p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-xs text-black-300">
                    Última prueba: {{ $stats['last_test'] }}
                </p>
            </div>
        </div>

        <!-- Contextos -->
        <div class="bg-accent-50 rounded-lg p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-warning-100 rounded-lg">
                    <x-solar-folder-outline class="w-6 h-6 text-warning-300" />
                </div>
                <div>
                    <h3 class="font-semibold text-black-400">Contextos</h3>
                    <p class="text-sm text-black-300">{{ count($contexts) }} tipos de notificaciones</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de configuración actual -->
    @if($validation['valid'])
        <div class="bg-accent-50 rounded-lg p-6 mb-6">
            <h2 class="text-base font-semibold text-black-400 mb-4">Configuración Actual</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-medium text-black-300">Servidor SMTP</label>
                    <p class="text-sm font-mono text-black-400">{{ $validation['config']['host'] }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-black-300">Puerto</label>
                    <p class="text-sm font-mono text-black-400">{{ $validation['config']['port'] }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-black-300">Usuario</label>
                    <p class="text-sm font-mono text-black-400">{{ $validation['config']['username'] }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-black-300">Encriptación</label>
                    <p class="text-sm font-mono text-black-400">{{ $validation['config']['encryption'] }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Contextos y sus emails -->
    <div class="bg-accent-50 rounded-lg p-6 mb-6">
        <h2 class="text-base font-semibold text-black-400 mb-4">Contextos de Email</h2>
        <div class="space-y-4">
            @foreach($contexts as $key => $context)
                <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-accent-200">
                    <div class="flex-1">
                        <h3 class="font-medium text-black-400">{{ $context['name'] }}</h3>
                        <p class="text-sm text-black-300">{{ $context['description'] }}</p>
                        <p class="text-xs font-mono text-primary-300 mt-1">{{ $context['email'] }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-300">
                            {{ isset($templates[$key]) ? count($templates[$key]) : 0 }} plantillas
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Plantillas por contexto -->
    @if($templates->isNotEmpty())
        <div class="bg-accent-50 rounded-lg p-6">
            <h2 class="text-base font-semibold text-black-400 mb-4">Plantillas Disponibles</h2>
            
            @foreach($templates as $context => $contextTemplates)
                <div class="mb-6 last:mb-0">
                    <h3 class="text-sm font-semibold text-black-400 mb-3">
                        {{ $contexts[$context]['name'] ?? $context }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($contextTemplates as $template)
                            <div class="bg-white rounded-lg border border-accent-200 p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-medium text-black-400">{{ $template->name }}</h4>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $template->is_active ? 'bg-success-100 text-success-300' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $template->is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                                <p class="text-xs text-black-300 mb-3">{{ $template->key }}</p>
                                <div class="flex gap-2">
                                    <a href="{{ route('superlinkiu.email.template-edit', $template) }}" 
                                       class="text-xs text-primary-300 hover:text-primary-400">
                                        Editar
                                    </a>
                                    <button @click="previewTemplate({{ $template->id }})"
                                            class="text-xs text-info-300 hover:text-info-400">
                                        Vista previa
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal de prueba de SMTP -->
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

            <div class="inline-block align-bottom bg-accent-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-accent-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-black-400 mb-4">Probar Configuración SMTP</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black-300 mb-2">Email de destino</label>
                        <input type="email" 
                               x-model="testEmail"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                               placeholder="tu-email@ejemplo.com">
                        <p class="text-xs text-black-300 mt-1">Se enviará un email de prueba a esta dirección.</p>
                    </div>
                </div>
                <div class="bg-accent-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="sendTestEmail()" 
                            :disabled="testing || !testEmail"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-primary-300 text-base font-medium text-accent-50 hover:bg-primary-400 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!testing">Enviar Prueba</span>
                        <span x-show="testing">Enviando...</span>
                    </button>
                    <button @click="showTestModal = false" 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-accent-200 shadow-sm px-4 py-2 bg-accent-50 text-base font-medium text-black-400 hover:bg-accent-100 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function emailDashboard() {
    return {
        showTestModal: false,
        testEmail: '',
        testing: false,

        sendTestEmail() {
            if (!this.testEmail) return;
            
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
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification('error', data.message);
                }
            })
            .catch(error => {
                this.testing = false;
                this.showTestModal = false;
                this.showNotification('error', 'Error al enviar email de prueba');
                console.error('Error:', error);
            });
        },

        previewTemplate(templateId) {
            // Aquí podrías implementar la vista previa
            this.showNotification('info', 'Vista previa en desarrollo');
        },

        showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
                type === 'success' ? 'bg-success-300 text-accent-50' : 
                type === 'error' ? 'bg-error-300 text-accent-50' : 
                'bg-info-300 text-accent-50'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    }
}
</script>
@endpush
@endsection
