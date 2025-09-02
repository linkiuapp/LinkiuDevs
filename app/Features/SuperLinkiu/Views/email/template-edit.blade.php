@extends('shared::layouts.admin')

@section('title', 'Editar Plantilla: ' . $template->name)

@section('content')
<div class="container-fluid" x-data="templateEditor()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Editar Plantilla</h1>
            <p class="text-sm text-black-300">{{ $template->name }} - {{ $contexts[$template->context]['name'] }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showPreviewModal = true" 
                    class="btn-outline-info px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-eye-outline class="w-4 h-4" />
                Vista Previa
            </button>
            <button @click="showTestModal = true" 
                    class="btn-outline-success px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-verified-check-outline class="w-4 h-4" />
                Probar
            </button>
            <a href="{{ route('superlinkiu.email.templates') }}" 
               class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
        </div>
    </div>

    <!-- Información de la plantilla -->
    <div class="bg-accent-50 rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-xs font-medium text-black-300">Clave</label>
                <p class="text-sm font-mono text-black-400">{{ $template->key }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-black-300">Contexto</label>
                <p class="text-sm text-black-400">{{ $contexts[$template->context]['name'] }}</p>
                <p class="text-xs font-mono text-primary-300">{{ $contexts[$template->context]['email'] }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-black-300">Estado</label>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $template->is_active ? 'bg-success-100 text-success-300' : 'bg-gray-100 text-gray-400' }}">
                    {{ $template->is_active ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Variables disponibles -->
    <div class="bg-accent-50 rounded-lg p-6 mb-6">
        <h2 class="text-base font-semibold text-black-400 mb-4">Variables Disponibles</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($availableVariables as $variable)
                <button @click="insertVariable('{{ $variable }}')"
                        class="text-left p-3 bg-white border border-accent-200 rounded-lg hover:border-primary-200 transition-colors">
                    <code class="text-sm font-mono text-primary-300">&#123;&#123; {{ $variable }} &#125;&#125;</code>
                </button>
            @endforeach
            <!-- Variables comunes -->
            @foreach(['app_name', 'app_url', 'current_year', 'support_email'] as $common)
                <button @click="insertVariable('{{ $common }}')"
                        class="text-left p-3 bg-primary-50 border border-primary-200 rounded-lg hover:border-primary-300 transition-colors">
                    <code class="text-sm font-mono text-primary-400">&#123;&#123; {{ $common }} &#125;&#125;</code>
                </button>
            @endforeach
        </div>
        <p class="text-xs text-black-300 mt-3">
            Haz clic en una variable para insertarla en el cursor. Las variables en azul claro son variables comunes disponibles en todas las plantillas.
        </p>
    </div>

    <!-- Formulario de edición -->
    <form method="POST" action="{{ route('superlinkiu.email.template-update', $template) }}">
        @csrf
        @method('PUT')
        
        <div class="bg-accent-50 rounded-lg p-6 space-y-6">
            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Nombre de la plantilla</label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name', $template->name) }}"
                       class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                       required>
                @error('name')
                    <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Asunto -->
            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Asunto del email</label>
                <input type="text" 
                       name="subject" 
                       value="{{ old('subject', $template->subject) }}"
                       x-ref="subjectInput"
                       class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                       required>
                @error('subject')
                    <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cuerpo HTML -->
            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Contenido HTML</label>
                <textarea name="body_html" 
                          rows="15"
                          x-ref="bodyHtmlInput"
                          class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none font-mono text-sm"
                          required>{{ old('body_html', $template->body_html) }}</textarea>
                @error('body_html')
                    <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-black-300 mt-2">
                    Puedes usar HTML básico: &lt;h1&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt;, etc.
                </p>
            </div>

            <!-- Cuerpo de texto plano (opcional) -->
            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">
                    Contenido de texto plano 
                    <span class="text-xs text-black-300">(opcional, se generará automáticamente si se deja vacío)</span>
                </label>
                <textarea name="body_text" 
                          rows="10"
                          x-ref="bodyTextInput"
                          class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none font-mono text-sm"
                          placeholder="Versión de texto plano del email...">{{ old('body_text', $template->body_text) }}</textarea>
                @error('body_text')
                    <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estado activo -->
            <div class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                <label class="ml-2 block text-sm text-black-400">
                    Plantilla activa (disponible para usar)
                </label>
            </div>

            <!-- Advertencias de validación -->
            @if(session('validation_warnings'))
                <div class="p-4 bg-warning-100 border border-warning-200 rounded-lg">
                    <h4 class="font-medium text-warning-400 mb-2">Advertencias de validación:</h4>
                    <ul class="text-sm text-warning-400 space-y-1">
                        @foreach(session('validation_warnings') as $warning)
                            <li>• {{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Botones de acción -->
            <div class="flex justify-between items-center pt-6 border-t border-accent-200">
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                        Guardar Cambios
                    </button>
                    <button type="button" 
                            @click="showPreviewModal = true"
                            class="btn-outline-info px-4 py-2 rounded-lg">
                        Vista Previa
                    </button>
                </div>
                <a href="{{ route('superlinkiu.email.templates') }}" 
                   class="text-sm text-black-300 hover:text-black-400">
                    Cancelar
                </a>
            </div>
        </div>
    </form>

    <!-- Modal de vista previa -->
    <div x-show="showPreviewModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black-400 bg-opacity-75 transition-opacity" @click="showPreviewModal = false"></div>

            <div class="inline-block align-bottom bg-accent-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-accent-50 px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg font-medium text-black-400 mb-4">Vista Previa - {{ $template->name }}</h3>
                    
                    <div class="bg-white rounded-lg border border-accent-200 p-4 mb-4">
                        <div class="text-sm text-black-300 mb-2">
                            <strong>Para:</strong> ejemplo@correo.com<br>
                            <strong>De:</strong> {{ $contexts[$template->context]['email'] }}<br>
                            <strong>Asunto:</strong> <span x-text="previewData.subject"></span>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg border border-accent-200 max-h-96 overflow-y-auto">
                        <div x-html="previewData.body_html" class="p-4"></div>
                    </div>
                </div>
                <div class="bg-accent-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="showPreviewModal = false" 
                            type="button" 
                            class="w-full inline-flex justify-center rounded-lg border border-accent-200 shadow-sm px-4 py-2 bg-accent-50 text-base font-medium text-black-400 hover:bg-accent-100 focus:outline-none sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de prueba -->
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
                    <h3 class="text-lg font-medium text-black-400 mb-4">Probar Plantilla</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-black-300 mb-2">Email de destino</label>
                        <input type="email" 
                               x-model="testEmail"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                               placeholder="tu-email@ejemplo.com">
                        <p class="text-xs text-black-300 mt-1">Se enviará usando los cambios actuales con datos de ejemplo.</p>
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
</div>

@push('scripts')
<script>
function templateEditor() {
    return {
        showPreviewModal: false,
        showTestModal: false,
        testEmail: '',
        testing: false,
        previewData: {
            subject: '',
            body_html: ''
        },

        insertVariable(variable) {
            const activeElement = document.activeElement;
            const variableText = '{{' + variable + '}}';
            
            if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
                const start = activeElement.selectionStart;
                const end = activeElement.selectionEnd;
                const value = activeElement.value;
                
                activeElement.value = value.substring(0, start) + variableText + value.substring(end);
                activeElement.selectionStart = activeElement.selectionEnd = start + variableText.length;
                activeElement.focus();
            } else {
                // Si no hay elemento activo, insertar en el cuerpo HTML
                const textarea = this.$refs.bodyHtmlInput;
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;
                
                textarea.value = value.substring(0, start) + variableText + value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + variableText.length;
                textarea.focus();
            }
        },

        async loadPreview() {
            try {
                const response = await fetch(`{{ route('superlinkiu.email.template-preview', $template) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.previewData = data.preview;
                    this.showPreviewModal = true;
                } else {
                    this.showNotification('error', 'Error cargando vista previa');
                }
            } catch (error) {
                this.showNotification('error', 'Error cargando vista previa');
                console.error('Error:', error);
            }
        },

        testTemplate() {
            if (!this.testEmail) return;
            
            this.testing = true;
            
            fetch(`{{ route('superlinkiu.email.template-test', $template) }}`, {
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
        },

        init() {
            // Cargar vista previa automáticamente
            this.loadPreview();
        }
    }
}
</script>
@endpush
@endsection
