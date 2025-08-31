{{-- Save Status Indicator Component --}}
<div 
    x-data="saveStatusIndicator({{ json_encode($config ?? []) }})"
    x-show="isVisible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    :class="statusClass"
    class="fixed bottom-4 right-4 z-50 max-w-sm"
    style="display: none;"
>
    {{-- Main Status Card --}}
    <div class="bg-accent rounded-lg shadow-lg border border-gray-200 p-4">
        <div class="flex items-center space-x-3">
            {{-- Status Icon --}}
            <div class="flex-shrink-0">
                <template x-if="status === 'saving'">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-500 border-t-transparent"></div>
                </template>
                
                <template x-if="status === 'saved'">
                    <div class="rounded-full h-5 w-5 bg-green-500 flex items-center justify-center">
                        <svg class="h-3 w-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </template>
                
                <template x-if="status === 'error'">
                    <div class="rounded-full h-5 w-5 bg-red-500 flex items-center justify-center">
                        <svg class="h-3 w-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </template>
            </div>
            
            {{-- Status Text --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900" x-text="statusText"></p>
                
                {{-- Timestamp --}}
                <template x-if="showTimestamp && lastSaved && status === 'saved'">
                    <p class="text-xs text-gray-500" x-text="'Guardado ' + formattedTimestamp"></p>
                </template>
                
                {{-- Error Message --}}
                <template x-if="status === 'error' && errorMessage">
                    <p class="text-xs text-red-600 mt-1" x-text="errorMessage"></p>
                </template>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex-shrink-0 flex space-x-2">
                {{-- Retry Button (for errors) --}}
                <template x-if="status === 'error'">
                    <button 
                        @click="retry()"
                        class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200 transition-colors"
                    >
                        Reintentar
                    </button>
                </template>
                
                {{-- Dismiss Button --}}
                <button 
                    @click="dismiss()"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Conflict Warning --}}
        <template x-if="hasConflict && showConflictWarning">
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                <div class="flex items-start space-x-2">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-yellow-800">Conflicto detectado</p>
                        <p class="text-xs text-yellow-700 mt-1">
                            Los datos han sido modificados desde otra sesión. 
                            <button @click="$dispatch('show-conflict-modal')" class="underline hover:no-underline">
                                Resolver conflicto
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

{{-- Conflict Resolution Modal --}}
<div 
    x-data="{ 
        showModal: false,
        conflictData: null,
        init() {
            this.$el.addEventListener('show-conflict-modal', (event) => {
                this.conflictData = event.detail;
                this.showModal = true;
            });
        }
    }"
    x-show="showModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Background overlay --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        {{-- Modal panel --}}
        <div class="inline-block align-bottom bg-accent rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Conflicto de datos detectado
                    </h3>
                    
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Los datos del formulario han sido modificados desde otra sesión o dispositivo. 
                            ¿Cómo deseas proceder?
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-reverse sm:space-x-3">
                <button 
                    @click="$dispatch('wizard:resolve-conflict', { action: 'use-local' }); showModal = false"
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-accent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                >
                    Usar mis cambios
                </button>
                
                <button 
                    @click="$dispatch('wizard:resolve-conflict', { action: 'use-server' }); showModal = false"
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-accent text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm"
                >
                    Usar cambios del servidor
                </button>
                
                <button 
                    @click="showModal = false"
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-accent text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.save-status-indicator {
    @apply transition-all duration-200;
}

.save-status-indicator--saving {
    @apply border-blue-200;
}

.save-status-indicator--saved {
    @apply border-green-200;
}

.save-status-indicator--error {
    @apply border-red-200;
}
</style>