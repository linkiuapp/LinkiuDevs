@extends('shared::layouts.admin')

@section('title', 'Gestión de Plantillas de Email')

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
                <span class="text-black-400 font-medium">Plantillas de Email</span>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
        <div class="flex-1">
            <h1 class="text-lg font-bold text-black-400">Gestión de Plantillas de Email</h1>
            <p class="text-sm text-black-300 mt-1">Personaliza el contenido de las notificaciones automáticas</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <a 
                href="{{ route('superlinkiu.email.settings') }}" 
                class="bg-accent-200 hover:bg-accent-300 text-black-400 px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors"
                title="Volver a la configuración de emails"
            >
                Volver a Configuración
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

    <!-- Templates by Context -->
    @foreach($contexts as $contextKey => $contextName)
        <div class="bg-accent-50 rounded-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                @if($contextKey === 'store_management')
                    <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                        <x-solar-shop-outline class="w-5 h-5 text-primary-300" />
                    </div>
                @elseif($contextKey === 'support')
                    <div class="w-10 h-10 bg-info-100 rounded-lg flex items-center justify-center">
                        <x-solar-chat-round-dots-outline class="w-5 h-5 text-info-300" />
                    </div>
                @else
                    <div class="w-10 h-10 bg-success-100 rounded-lg flex items-center justify-center">
                        <x-solar-dollar-outline class="w-5 h-5 text-success-300" />
                    </div>
                @endif
                <h2 class="text-lg font-semibold text-black-500">{{ $contextName }}</h2>
            </div>

            @if(isset($templates[$contextKey]) && $templates[$contextKey]->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($templates[$contextKey] as $template)
                        <div class="bg-accent-50 border border-accent-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-black-500 mb-1">{{ $template->name }}</h3>
                                    <p class="text-xs text-black-300 font-mono">{{ $template->template_key }}</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($template->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-success-100 text-success-700">
                                            <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                            Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-warning-100 text-warning-700">
                                            <x-solar-pause-circle-outline class="w-3 h-3 mr-1" />
                                            Inactiva
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <p class="text-sm text-black-400 font-medium mb-1">Asunto:</p>
                                <p class="text-sm text-black-300 truncate">{{ $template->subject }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-black-400 font-medium mb-1">Vista previa:</p>
                                <div class="text-xs text-black-300 bg-accent-100 p-2 rounded max-h-16 overflow-hidden">
                                    {{ Str::limit(strip_tags($template->body_html ?: $template->body_text), 100) }}
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('superlinkiu.email.templates.edit', $template) }}" 
                                   class="flex-1 bg-primary-200 hover:bg-primary-300 text-accent-50 px-3 py-2 rounded text-sm text-center transition-colors">
                                    <x-solar-pen-outline class="w-4 h-4 inline mr-1" />
                                    Editar
                                </a>
                                <button onclick="previewTemplate({{ $template->id }})" 
                                        class="flex-1 bg-info-200 hover:bg-info-300 text-accent-50 px-3 py-2 rounded text-sm transition-colors">
                                    <x-solar-eye-outline class="w-4 h-4 inline mr-1" />
                                    Vista Previa
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-solar-document-text-outline class="w-12 h-12 text-black-200 mx-auto mb-3" />
                    <p class="text-black-300">No hay plantillas configuradas para este contexto</p>
                    <p class="text-sm text-black-200">Las plantillas se crean automáticamente cuando se necesitan</p>
                </div>
            @endif
        </div>
    @endforeach

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="bg-accent-50 border border-accent-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <x-solar-document-text-outline class="w-8 h-8 text-primary-300" />
                <div>
                    <p class="text-2xl font-bold text-black-500">{{ $templates->flatten()->count() }}</p>
                    <p class="text-sm text-black-300">Total de Plantillas</p>
                </div>
            </div>
        </div>
        
        <div class="bg-accent-50 border border-accent-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <x-solar-check-circle-outline class="w-8 h-8 text-success-300" />
                <div>
                    <p class="text-2xl font-bold text-black-500">{{ $templates->flatten()->where('is_active', true)->count() }}</p>
                    <p class="text-sm text-black-300">Plantillas Activas</p>
                </div>
            </div>
        </div>
        
        <div class="bg-accent-50 border border-accent-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <x-solar-layers-outline class="w-8 h-8 text-info-300" />
                <div>
                    <p class="text-2xl font-bold text-black-500">{{ count($contexts) }}</p>
                    <p class="text-sm text-black-300">Contextos Configurados</p>
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
                        <p class="text-black-300 mt-2">Cargando vista previa...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewTemplate(templateId) {
    document.getElementById('previewModal').classList.remove('hidden');
    
    fetch(`/superlinkiu/email/templates/${templateId}/preview`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
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
                    <p class="text-error-300">Error al cargar la vista previa</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('previewContent').innerHTML = `
            <div class="text-center py-8">
                <x-solar-close-circle-outline class="w-12 h-12 text-error-300 mx-auto mb-3" />
                <p class="text-error-300">Error al cargar la vista previa</p>
            </div>
        `;
    });
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('previewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>
@endsection