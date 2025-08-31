{{--
    WizardStateManager Blade Component
    
    Provides client-side routing and state persistence for wizard
    Manages form data persistence and navigation guards
    
    Props:
    - wizardId: Unique identifier for the wizard
    - enableRouting: Enable URL routing (default: true)
    - enablePersistence: Enable localStorage persistence (default: true)
    - persistenceKey: Custom persistence key (optional)
    - routePrefix: URL hash prefix (default: '#step-')
    
    Requirements: 1.1, 5.1
--}}

@props([
    'wizardId' => 'store-creation-wizard',
    'enableRouting' => true,
    'enablePersistence' => true,
    'persistenceKey' => null,
    'routePrefix' => '#step-',
    'class' => ''
])

<div 
    x-data="wizardStateManager({
        wizardId: '{{ $wizardId }}',
        enableRouting: {{ $enableRouting ? 'true' : 'false' }},
        enablePersistence: {{ $enablePersistence ? 'true' : 'false' }},
        persistenceKey: {{ $persistenceKey ? "'" . $persistenceKey . "'" : 'null' }},
        routePrefix: '{{ $routePrefix }}',
        autoSaveEnabled: true,
        autoSaveInterval: 30000
    })"
    class="wizard-state-manager {{ $class }}"
    x-cloak
>
    {{-- State Recovery Modal --}}
    <div 
        x-show="hasPersistedData() && !isInitialized" 
        x-transition
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        style="display: none;"
    >
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-accent">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <x-solar-refresh-outline class="h-6 w-6 text-blue-600" />
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">
                    Datos Guardados Encontrados
                </h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Se encontraron datos guardados de una sesión anterior. 
                        ¿Deseas continuar donde lo dejaste o empezar de nuevo?
                    </p>
                    <div class="mt-4 text-xs text-gray-400">
                        <span>Última actualización: </span>
                        <span x-text="getLastSavedTime() ? new Date(getLastSavedTime()).toLocaleString() : 'Desconocido'"></span>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button
                        @click="$dispatch('wizard:restore-state'); isInitialized = true"
                        class="px-4 py-2 bg-blue-500 text-accent text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 mb-2"
                    >
                        Continuar Donde Lo Dejé
                    </button>
                    <button
                        @click="reset(); isInitialized = true"
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
                    >
                        Empezar de Nuevo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- State Information Panel (for debugging) --}}
    @if(config('app.debug'))
    <div class="wizard-debug-panel bg-gray-100 p-4 rounded-lg mb-4 text-xs">
        <details>
            <summary class="cursor-pointer font-medium">Estado del Wizard (Debug)</summary>
            <div class="mt-2 space-y-2">
                <div><strong>Wizard ID:</strong> <span x-text="wizardId"></span></div>
                <div><strong>Routing:</strong> <span x-text="enableRouting ? 'Habilitado' : 'Deshabilitado'"></span></div>
                <div><strong>Persistence:</strong> <span x-text="enablePersistence ? 'Habilitado' : 'Deshabilitado'"></span></div>
                <div><strong>Ruta Actual:</strong> <span x-text="currentRoute || 'Ninguna'"></span></div>
                <div><strong>Datos Persistidos:</strong> <span x-text="Object.keys(persistedData).length + ' pasos'"></span></div>
                <div><strong>Estados de Validación:</strong> <span x-text="Object.keys(validationStates).length + ' pasos'"></span></div>
            </div>
        </details>
    </div>
    @endif

    {{-- Main Content --}}
    {{ $slot }}

    {{-- State Actions (Hidden by default) --}}
    <div class="wizard-state-actions hidden">
        <button 
            type="button" 
            @click="saveCurrentState()"
            class="btn btn-sm btn-outline"
        >
            Guardar Estado
        </button>
        <button 
            type="button" 
            @click="reset()"
            class="btn btn-sm btn-outline btn-danger"
        >
            Limpiar Estado
        </button>
    </div>
</div>

@once
@push('scripts')
<script src="{{ asset('js/components/wizard-state-manager.js') }}"></script>
@endpush
@endonce