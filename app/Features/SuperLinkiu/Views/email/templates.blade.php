@extends('shared::layouts.admin')

@section('title', 'Gestión de Plantillas de Email')

@section('content')
<div class="container-fluid" x-data="templateManager()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Gestión de Plantillas</h1>
            <p class="text-sm text-black-300">Editar y gestionar plantillas de email por contexto</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showRestoreModal = true" 
                    class="btn-outline-warning px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-refresh-outline class="w-4 h-4" />
                Restaurar Predeterminadas
            </button>
            <a href="{{ route('superlinkiu.email.index') }}" 
               class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Filtros por contexto -->
    <div class="bg-accent-50 rounded-lg p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-black-300">Filtrar por contexto:</span>
            <button @click="selectedContext = 'all'" 
                    :class="selectedContext === 'all' ? 'btn-primary' : 'btn-outline-secondary'"
                    class="px-3 py-1 rounded-lg text-sm">
                Todos
            </button>
            @foreach($contexts as $key => $context)
                <button @click="selectedContext = '{{ $key }}'" 
                        :class="selectedContext === '{{ $key }}' ? 'btn-primary' : 'btn-outline-secondary'"
                        class="px-3 py-1 rounded-lg text-sm">
                    {{ $context['name'] }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Lista de plantillas -->
    <div class="space-y-6">
        @foreach($contexts as $contextKey => $context)
            <div class="bg-accent-50 rounded-lg p-6" 
                 x-show="selectedContext === 'all' || selectedContext === '{{ $contextKey }}'"
                 x-transition>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-black-400">{{ $context['name'] }}</h2>
                        <p class="text-sm text-black-300">{{ $context['description'] }}</p>
                        <p class="text-xs font-mono text-primary-300 mt-1">{{ $context['email'] }}</p>
                    </div>
                </div>

                @php
                    $contextTemplates = $templates->where('context', $contextKey);
                @endphp

                @if($contextTemplates->isEmpty())
                    <div class="text-center py-8">
                        <x-solar-document-outline class="w-12 h-12 text-black-200 mx-auto mb-3" />
                        <p class="text-black-300">No hay plantillas para este contexto</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($contextTemplates as $template)
                            <div class="bg-white rounded-lg border border-accent-200 p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-black-400">{{ $template->name }}</h3>
                                        <p class="text-xs text-black-300 mt-1">{{ $template->key }}</p>
                                    </div>
                                    <button @click="toggleTemplate({{ $template->id }})"
                                            class="flex-shrink-0 ml-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $template->is_active ? 'bg-success-100 text-success-300' : 'bg-gray-100 text-gray-400' }}">
                                            {{ $template->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </button>
                                </div>

                                <div class="text-xs text-black-300 mb-3">
                                    <strong>Asunto:</strong> {{ Str::limit($template->subject, 50) }}
                                </div>

                                @if($template->variables)
                                    <div class="mb-3">
                                        <p class="text-xs font-medium text-black-300 mb-1">Variables:</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($template->variables as $variable)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-primary-100 text-primary-300">
                                                    {{{{$variable}}}}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="flex justify-between items-center pt-3 border-t border-accent-100">
                                    <div class="flex gap-2">
                                        <a href="{{ route('superlinkiu.email.template-edit', $template) }}" 
                                           class="text-xs text-primary-300 hover:text-primary-400 font-medium">
                                            Editar
                                        </a>
                                        <button @click="previewTemplate({{ $template->id }})"
                                                class="text-xs text-info-300 hover:text-info-400 font-medium">
                                            Vista previa
                                        </button>
                                    </div>
                                    <button @click="showTestTemplateModal({{ $template->id }}, '{{ $template->name }}')"
                                            class="text-xs text-success-300 hover:text-success-400 font-medium">
                                        Probar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Modal para probar plantilla -->
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
                    <h3 class="text-lg font-medium text-black-400 mb-4">
                        Probar Plantilla: <span x-text="testTemplateName"></span>
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black-300 mb-2">Email de destino</label>
                        <input type="email" 
                               x-model="testEmail"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                               placeholder="tu-email@ejemplo.com">
                        <p class="text-xs text-black-300 mt-1">Se enviará usando datos de ejemplo.</p>
                    </div>
                </div>
                <div class="bg-accent-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="testTemplate()" 
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

    <!-- Modal para restaurar plantillas -->
    <div x-show="showRestoreModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black-400 bg-opacity-75 transition-opacity" @click="showRestoreModal = false"></div>

            <div class="inline-block align-bottom bg-accent-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-accent-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <x-solar-danger-triangle-outline class="w-6 h-6 text-warning-300" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-black-400">Restaurar Plantillas Predeterminadas</h3>
                            <p class="text-sm text-black-300 mt-2">
                                Esto creará/actualizará las plantillas con sus valores por defecto. 
                                Los cambios personalizados se mantendrán si las plantillas ya existen.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-accent-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form method="POST" action="{{ route('superlinkiu.email.restore-defaults') }}" class="sm:ml-3">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-warning-300 text-base font-medium text-accent-50 hover:bg-warning-400 focus:outline-none sm:w-auto sm:text-sm">
                            Restaurar
                        </button>
                    </form>
                    <button @click="showRestoreModal = false" 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-accent-200 shadow-sm px-4 py-2 bg-accent-50 text-base font-medium text-black-400 hover:bg-accent-100 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function templateManager() {
    return {
        selectedContext: 'all',
        showTestModal: false,
        showRestoreModal: false,
        testEmail: '',
        testing: false,
        testTemplateId: null,
        testTemplateName: '',

        showTestTemplateModal(templateId, templateName) {
            this.testTemplateId = templateId;
            this.testTemplateName = templateName;
            this.showTestModal = true;
        },

        testTemplate() {
            if (!this.testEmail || !this.testTemplateId) return;
            
            this.testing = true;
            
            fetch(`/superlinkiu/email/template/${this.testTemplateId}/test`, {
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

        toggleTemplate(templateId) {
            fetch(`/superlinkiu/email/template/${templateId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('success', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('error', 'Error al cambiar estado de plantilla');
                }
            })
            .catch(error => {
                this.showNotification('error', 'Error al cambiar estado de plantilla');
                console.error('Error:', error);
            });
        },

        previewTemplate(templateId) {
            // Implementar vista previa aquí
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
