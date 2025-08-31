@extends('shared::layouts.admin')

@section('title', 'Editar Plantilla: ' . $template->name)

@push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumbs -->
    <nav class="flex items-center text-sm text-black-300 mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li class="flex items-center">
                <a href="{{ route('superlinkiu.dashboard') }}" 
                   class="hover:text-primary-300 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200 rounded px-1">
                    Dashboard
                </a>
            </li>
            <li class="flex items-center">
                <span class="mx-2 text-black-200">/</span>
                <a href="{{ route('superlinkiu.tickets.index') }}" 
                   class="hover:text-primary-300 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200 rounded px-1">
                    Tickets
                </a>
            </li>
            <li class="flex items-center">
                <span class="mx-2 text-black-200">/</span>
                <a href="{{ route('superlinkiu.email.settings') }}" 
                   class="hover:text-primary-300 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200 rounded px-1">
                    Configuración de Email
                </a>
            </li>
            <li class="flex items-center">
                <span class="mx-2 text-black-200">/</span>
                <a href="{{ route('superlinkiu.email.templates.index') }}" 
                   class="hover:text-primary-300 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200 rounded px-1">
                    Plantillas de Email
                </a>
            </li>
            <li class="flex items-center">
                <span class="mx-2 text-black-200">/</span>
                <span class="text-black-400 font-medium">Editar Plantilla</span>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
        <div class="flex-1">
            <h1 class="text-lg font-bold text-black-400">Editar Plantilla: {{ $template->name }}</h1>
            <p class="text-sm text-black-300 mt-1">Contexto: {{ ucfirst(str_replace('_', ' ', $template->context)) }} • Clave: {{ $template->template_key }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <button 
                onclick="previewTemplate()" 
                class="bg-info-200 hover:bg-info-300 text-accent-50 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                title="Ver vista previa de la plantilla"
            >
                Vista Previa
            </button>
            <button 
                onclick="sendTestEmail()" 
                class="bg-success-200 hover:bg-success-300 text-accent-50 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                title="Enviar email de prueba con esta plantilla"
            >
                Enviar Prueba
            </button>
            <a 
                href="{{ route('superlinkiu.email.templates.index') }}" 
                class="bg-accent-200 hover:bg-accent-300 text-black-400 px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors"
                title="Volver a la lista de plantillas"
            >
                Volver
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-success-100 border border-success-200 text-success-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-error-100 border border-error-200 text-error-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Template Form -->
        <div class="lg:col-span-2">
            <div class="bg-accent-50 rounded-lg p-6">
                <form action="{{ route('superlinkiu.email.templates.update', $template) }}" method="POST" id="templateForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Template Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-black-400 mb-2">
                            Nombre de la Plantilla
                        </label>
                        <input 
                            type="text" 
                            id="name"
                            name="name" 
                            value="{{ old('name', $template->name) }}"
                            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('name') border-error-300 @enderror"
                            required
                        >
                        @error('name')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subject -->
                    <div class="mb-6">
                        <label for="subject" class="block text-sm font-medium text-black-400 mb-2">
                            Asunto del Email
                        </label>
                        <input 
                            type="text" 
                            id="subject"
                            name="subject" 
                            value="{{ old('subject', $template->subject) }}"
                            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('subject') border-error-300 @enderror"
                            placeholder="Ej: Bienvenido a {{store_name}}"
                            required
                        >
                        @error('subject')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-black-300 mt-1">Puedes usar variables como {{store_name}} en el asunto</p>
                    </div>

                    <!-- HTML Content -->
                    <div class="mb-6">
                        <label for="body_html" class="block text-sm font-medium text-black-400 mb-2">
                            Contenido HTML
                        </label>
                        <textarea 
                            id="body_html"
                            name="body_html" 
                            rows="12"
                            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 font-mono text-sm @error('body_html') border-error-300 @enderror"
                            placeholder="<p>Hola {{admin_name}},</p><p>Tu tienda {{store_name}} ha sido creada exitosamente.</p>"
                        >{{ old('body_html', $template->body_html) }}</textarea>
                        @error('body_html')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-black-300 mt-1">HTML básico permitido: p, br, strong, em, ul, li, ol, a, div, span</p>
                    </div>

                    <!-- Text Content -->
                    <div class="mb-6">
                        <label for="body_text" class="block text-sm font-medium text-black-400 mb-2">
                            Contenido de Texto (Opcional)
                        </label>
                        <textarea 
                            id="body_text"
                            name="body_text" 
                            rows="8"
                            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 font-mono text-sm @error('body_text') border-error-300 @enderror"
                            placeholder="Hola {{admin_name}}, Tu tienda {{store_name}} ha sido creada exitosamente."
                        >{{ old('body_text', $template->body_text) }}</textarea>
                        @error('body_text')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-black-300 mt-1">Versión de texto plano para clientes que no soportan HTML</p>
                    </div>

                    <!-- Active Status -->
                    <div class="mb-6">
                        <label class="flex items-center gap-3">
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1"
                                {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200"
                            >
                            <span class="text-sm font-medium text-black-400">Plantilla activa</span>
                        </label>
                        <p class="text-xs text-black-300 mt-1 ml-7">Solo las plantillas activas se utilizarán para enviar emails</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-6 border-t border-accent-200">
                        <div class="text-sm text-black-300">
                            <x-solar-info-circle-outline class="w-4 h-4 inline mr-1" />
                            Los cambios se aplicarán inmediatamente
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="button" onclick="resetForm()" class="bg-accent-200 hover:bg-accent-300 text-black-400 px-4 py-2 rounded-lg transition-colors">
                                Restaurar
                            </button>
                            <button type="submit" class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-6 py-2 rounded-lg transition-colors">
                                Guardar Plantilla
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Variable Helper Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-accent-50 rounded-lg p-6 sticky top-6">
                <h3 class="text-lg font-semibold text-black-500 mb-4 flex items-center gap-2">
                    <x-solar-code-outline class="w-5 h-5" />
                    Variables Disponibles
                </h3>
                
                <div class="space-y-4">
                    @foreach($availableVariables as $variable => $description)
                        <div class="bg-accent-100 p-3 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <code class="text-sm font-mono text-primary-400 bg-accent-50 px-2 py-1 rounded">{{ $variable }}</code>
                                <button onclick="insertVariable('{{ $variable }}')" class="text-black-300 hover:text-black-500 transition-colors">
                                    <x-solar-copy-outline class="w-4 h-4" />
                                </button>
                            </div>
                            <p class="text-xs text-black-300">{{ $description }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 p-4 bg-info-100 rounded-lg">
                    <h4 class="font-semibold text-info-700 mb-2 flex items-center gap-2">
                        <x-solar-lightbulb-outline class="w-4 h-4" />
                        Consejos
                    </h4>
                    <ul class="text-xs text-info-600 space-y-1">
                        <li>• Haz clic en una variable para copiarla</li>
                        <li>• Usa la vista previa para probar cambios</li>
                        <li>• Las variables se reemplazan automáticamente</li>
                        <li>• El HTML se sanitiza por seguridad</li>
                    </ul>
                </div>

                <!-- Template Info -->
                <div class="mt-6 p-4 bg-accent-100 rounded-lg">
                    <h4 class="font-semibold text-black-500 mb-2">Información</h4>
                    <div class="text-xs text-black-300 space-y-1">
                        <p><strong>Creada:</strong> {{ $template->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Actualizada:</strong> {{ $template->updated_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Estado:</strong> 
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $template->is_active ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700' }}">
                                {{ $template->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-accent-50 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-accent-200">
                <h3 class="text-lg font-semibold text-black-500">Vista Previa de Plantilla</h3>
                <button onclick="closePreview()" class="text-black-300 hover:text-black-500">
                    <x-solar-close-circle-outline class="w-6 h-6" />
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <div id="previewContent">
                    <div class="text-center py-8">
                        <div class="animate-spin w-8 h-8 border-2 border-primary-200 border-t-transparent rounded-full mx-auto"></div>
                        <p class="text-black-300 mt-2">Generando vista previa...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function insertVariable(variable) {
    // Get the currently focused textarea
    const activeElement = document.activeElement;
    let targetTextarea = null;
    
    if (activeElement && (activeElement.id === 'subject' || activeElement.id === 'body_html' || activeElement.id === 'body_text')) {
        targetTextarea = activeElement;
    } else {
        // Default to body_html if no textarea is focused
        targetTextarea = document.getElementById('body_html');
    }
    
    if (targetTextarea) {
        const start = targetTextarea.selectionStart;
        const end = targetTextarea.selectionEnd;
        const text = targetTextarea.value;
        
        targetTextarea.value = text.substring(0, start) + variable + text.substring(end);
        targetTextarea.focus();
        targetTextarea.setSelectionRange(start + variable.length, start + variable.length);
    }
    
    // Show feedback
    const button = event.target.closest('button');
    const originalIcon = button.innerHTML;
    button.innerHTML = '<x-solar-check-circle-outline class="w-4 h-4" />';
    button.classList.add('text-success-300');
    
    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('text-success-300');
    }, 1000);
}

function previewTemplate() {
    const formData = new FormData(document.getElementById('templateForm'));
    
    document.getElementById('previewModal').classList.remove('hidden');
    document.getElementById('previewContent').innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin w-8 h-8 border-2 border-primary-200 border-t-transparent rounded-full mx-auto"></div>
            <p class="text-black-300 mt-2">Generando vista previa...</p>
        </div>
    `;
    
    fetch(`{{ route('superlinkiu.email.templates.preview', $template) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('previewContent').innerHTML = `
                <div class="space-y-4">
                    <div class="bg-accent-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-black-500 mb-2">Asunto:</h4>
                        <p class="text-black-400">${data.preview.subject}</p>
                    </div>
                    <div class="bg-accent-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-black-500 mb-2">Contenido HTML:</h4>
                        <div class="bg-accent-50 p-4 rounded border max-h-64 overflow-y-auto">
                            ${data.preview.body_html || '<em class="text-black-300">Sin contenido HTML</em>'}
                        </div>
                    </div>
                    ${data.preview.body_text ? `
                    <div class="bg-accent-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-black-500 mb-2">Contenido de Texto:</h4>
                        <div class="bg-accent-50 p-4 rounded border max-h-32 overflow-y-auto">
                            <pre class="whitespace-pre-wrap text-sm">${data.preview.body_text}</pre>
                        </div>
                    </div>
                    ` : ''}
                    <div class="bg-info-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-black-500 mb-2">Datos de Ejemplo Utilizados:</h4>
                        <pre class="text-xs text-black-400 bg-accent-50 p-3 rounded overflow-x-auto">${JSON.stringify(data.sample_data, null, 2)}</pre>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('previewContent').innerHTML = `
                <div class="text-center py-8">
                    <x-solar-close-circle-outline class="w-12 h-12 text-error-300 mx-auto mb-3" />
                    <p class="text-error-300">Error al generar la vista previa</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('previewContent').innerHTML = `
            <div class="text-center py-8">
                <x-solar-close-circle-outline class="w-12 h-12 text-error-300 mx-auto mb-3" />
                <p class="text-error-300">Error al generar la vista previa</p>
            </div>
        `;
    });
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

function resetForm() {
    if (confirm('¿Estás seguro de restaurar el formulario? Se perderán los cambios no guardados.')) {
        document.getElementById('templateForm').reset();
        // Restore original values
        document.getElementById('name').value = '{{ $template->name }}';
        document.getElementById('subject').value = '{{ $template->subject }}';
        document.getElementById('body_html').value = `{{ $template->body_html }}`;
        document.getElementById('body_text').value = `{{ $template->body_text }}`;
        document.querySelector('input[name="is_active"]').checked = {{ $template->is_active ? 'true' : 'false' }};
    }
}

// Close modal when clicking outside
document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});

// Auto-save draft functionality (optional)
let autoSaveTimeout;
document.getElementById('templateForm').addEventListener('input', function() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        // Could implement auto-save to localStorage here
        console.log('Auto-save triggered');
    }, 2000);
});

function sendTestEmail() {
    const email = prompt('Ingresa tu email para recibir un mensaje de prueba con esta plantilla:');
    if (email && email.includes('@')) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Enviando...';
        button.disabled = true;
        
        fetch('{{ route("superlinkiu.email.send-test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                template_id: {{ $template->id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al enviar el email de prueba');
        })
        .finally(() => {
            // Restore button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}
</script>
@endsection