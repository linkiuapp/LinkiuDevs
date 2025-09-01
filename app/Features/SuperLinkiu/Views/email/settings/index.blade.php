@extends('shared::layouts.admin')

@section('title', 'Configuración de Emails')

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
                <span class="text-black-400 font-medium">Configuración de Email</span>
            </li>
            <li class="flex items-center">
                <span class="mx-2 text-black-200">/</span>
                <span class="text-black-400 font-medium">Direcciones de Email</span>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
        <div class="flex-1">
            <h1 class="text-lg font-bold text-black-400">Configuración de Emails</h1>
            <p class="text-sm text-black-300 mt-1">Gestiona las direcciones de correo para diferentes contextos</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <button 
                onclick="validateConfiguration()" 
                class="bg-info-200 hover:bg-info-300 text-accent-50 px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors"
                title="Validar la configuración actual de emails"
            >
                <span class="hidden sm:inline">Validar Configuración</span>
                <span class="sm:hidden">Validar</span>
            </button>
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

    <!-- Email Configuration Form -->
    <div class="bg-accent-50 rounded-lg p-6">
        <form action="{{ route('superlinkiu.email.settings.update') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                @foreach($contexts as $contextKey => $contextInfo)
                    <div class="bg-accent-50 border border-accent-200 rounded-lg p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            <div class="flex-shrink-0">
                                @if($contextKey === 'store_management')
                                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                        <x-solar-shop-outline class="w-6 h-6 text-primary-300" />
                                    </div>
                                @elseif($contextKey === 'support')
                                    <div class="w-12 h-12 bg-info-100 rounded-lg flex items-center justify-center">
                                        <x-solar-chat-round-dots-outline class="w-6 h-6 text-info-300" />
                                    </div>
                                @else
                                    <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                                        <x-solar-dollar-outline class="w-6 h-6 text-success-300" />
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-black-500 mb-2">{{ $contextInfo['name'] }}</h3>
                                <p class="text-sm text-black-300 mb-4">{{ $contextInfo['description'] }}</p>
                                
                                <div class="space-y-2">
                                    <label for="{{ $contextKey }}_email" class="block text-sm font-medium text-black-400">
                                        Dirección de Email
                                    </label>
                                    <input 
                                        type="email" 
                                        id="{{ $contextKey }}_email"
                                        name="{{ $contextKey }}_email" 
                                        value="{{ old($contextKey . '_email', $settings->get($contextKey)?->email ?? $contextInfo['default_email']) }}"
                                        class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error($contextKey . '_email') border-error-300 @enderror"
                                        placeholder="{{ $contextInfo['default_email'] }}"
                                        required
                                    >
                                    @error($contextKey . '_email')
                                        <p class="text-sm text-error-300">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                @if($settings->get($contextKey))
                                    <div class="mt-3 flex items-center gap-2 text-xs text-black-300">
                                        <x-solar-check-circle-outline class="w-4 h-4 text-success-300" />
                                        Configurado desde: {{ $settings->get($contextKey)->created_at->format('d/m/Y H:i') }}
                                    </div>
                                @else
                                    <div class="mt-3 flex items-center gap-2 text-xs text-warning-300">
                                        <x-solar-info-circle-outline class="w-4 h-4" />
                                        Usando valor por defecto
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mt-8 pt-6 border-t border-accent-200">
                <div class="text-sm text-black-300 order-2 sm:order-1">
                    <x-solar-info-circle-outline class="w-4 h-4 inline mr-1" />
                    Los cambios se aplicarán inmediatamente a todos los envíos de email
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 order-1 sm:order-2">
                    <button type="button" onclick="resetToDefaults()" class="bg-accent-200 hover:bg-accent-300 text-black-400 px-4 py-2 rounded-lg transition-colors">
                        <span class="hidden sm:inline">Restaurar Valores por Defecto</span>
                        <span class="sm:hidden">Restaurar</span>
                    </button>
                    <button type="submit" class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-6 py-2 rounded-lg transition-colors">
                        Guardar Configuración
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('superlinkiu.email.templates.index') }}" class="bg-accent-50 border border-accent-200 rounded-lg p-4 hover:bg-accent-100 transition-colors">
            <div class="flex items-center gap-3">
                <x-solar-document-text-outline class="w-8 h-8 text-primary-300" />
                <div>
                    <h4 class="font-semibold text-black-500">Gestionar Plantillas</h4>
                    <p class="text-sm text-black-300">Personalizar contenido de emails</p>
                </div>
            </div>
        </a>
        

        
        <button onclick="testEmailSending()" class="bg-accent-50 border border-accent-200 rounded-lg p-4 hover:bg-accent-100 transition-colors text-left">
            <div class="flex items-center gap-3">
                <x-solar-paper-bin-outline class="w-8 h-8 text-success-300" />
                <div>
                    <h4 class="font-semibold text-black-500">Probar Envío</h4>
                    <p class="text-sm text-black-300">Enviar email de prueba</p>
                </div>
            </div>
        </button>
    </div>
</div>

<script>
function validateConfiguration() {
    fetch('{{ route("superlinkiu.email.validate") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message + '\n\nProblemas encontrados:\n' + data.issues.join('\n'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al validar la configuración');
    });
}

function resetToDefaults() {
    if (confirm('¿Estás seguro de restaurar los valores por defecto? Se perderán los cambios no guardados.')) {
        document.getElementById('store_management_email').value = 'no-responder@linkiu.email';
        document.getElementById('support_email').value = 'soporte@linkiu.email';
        document.getElementById('billing_email').value = 'contabilidad@linkiu.email';
    }
}

function testEmailSending() {
    const email = prompt('Ingresa tu email para recibir un mensaje de prueba:');
    if (email && email.includes('@')) {
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Enviando...';
        button.disabled = true;

        console.log('🚀 Iniciando envío de email de prueba:', email);

        // SOLUCIÓN MEJORADA: Más timeout y mejor debugging
        const controller = new AbortController();
        const timeoutId = setTimeout(() => {
            console.log('⏰ Timeout alcanzado (60 segundos)');
            controller.abort();
        }, 60000); // 60 segundos

        fetch('{{ route("superlinkiu.email.simple-test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: email
            }),
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);
            console.log('📡 Response status:', response.status);
            console.log('📡 Response headers:', response.headers);
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('❌ Response error body:', text);
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Response data:', data);
            if (data && data.success) {
                alert('✅ Email enviado correctamente: ' + (data.message || 'Sin mensaje'));
            } else {
                const errorMsg = data ? data.message : 'Respuesta inválida del servidor';
                console.error('❌ API returned error:', errorMsg);
                alert('❌ Error: ' + errorMsg);
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.error('💥 Fetch error:', error);
            
            let errorMessage = 'Error desconocido';
            if (error.name === 'AbortError') {
                errorMessage = 'Timeout: El envío está tardando más de 60 segundos';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            alert('❌ Error al enviar email: ' + errorMessage);
        })
        .finally(() => {
            // Restore button state
            console.log('🔄 Restaurando estado del botón');
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}
</script>
@endsection