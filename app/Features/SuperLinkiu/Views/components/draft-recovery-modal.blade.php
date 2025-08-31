{{-- Draft Recovery Modal Component --}}
<div 
    x-data="draftRecoveryModal({{ json_encode($config ?? []) }})"
    x-show="isVisible"
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
        <div class="inline-block align-bottom bg-accent rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            {{-- Header --}}
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Borrador encontrado
                    </h3>
                    
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Encontramos un borrador guardado de una sesión anterior. 
                            <span x-text="formattedDraftAge" class="font-medium"></span>
                        </p>
                        
                        <template x-if="formattedExpirationTime">
                            <p class="text-xs text-orange-600 mt-1" x-text="formattedExpirationTime"></p>
                        </template>
                    </div>
                </div>
            </div>
            
            {{-- Progress Indicator --}}
            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progreso del formulario</span>
                    <span class="text-sm text-gray-500" x-text="progressPercentage + '%'"></span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="`width: ${progressPercentage}%`"
                    ></div>
                </div>
                
                <div class="mt-2 text-xs text-gray-500">
                    <span x-text="completedStepsCount"></span> de <span x-text="totalStepsCount"></span> pasos completados
                </div>
            </div>
            
            {{-- Draft Preview --}}
            <template x-if="showPreview && draftData">
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-900">Vista previa del borrador</h4>
                        <button 
                            @click="togglePreviewMode()"
                            class="text-xs text-blue-600 hover:text-blue-800"
                        >
                            <span x-text="previewMode === 'summary' ? 'Ver detalles' : previewMode === 'detailed' ? 'Ver JSON' : 'Ver resumen'"></span>
                        </button>
                    </div>
                    
                    {{-- Summary View --}}
                    <template x-if="previewMode === 'summary'">
                        <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                            <template x-if="draftSummary.template">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Plantilla:</span>
                                    <span class="font-medium capitalize" x-text="draftSummary.template"></span>
                                </div>
                            </template>
                            
                            <template x-if="draftSummary.owner?.name">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Propietario:</span>
                                    <span class="font-medium" x-text="draftSummary.owner.name"></span>
                                </div>
                            </template>
                            
                            <template x-if="draftSummary.owner?.email">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium" x-text="draftSummary.owner.email"></span>
                                </div>
                            </template>
                            
                            <template x-if="draftSummary.store?.name">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tienda:</span>
                                    <span class="font-medium" x-text="draftSummary.store.name"></span>
                                </div>
                            </template>
                            
                            <template x-if="draftSummary.store?.slug">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">URL:</span>
                                    <span class="font-medium font-mono text-xs" x-text="draftSummary.store.slug"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    {{-- Detailed View --}}
                    <template x-if="previewMode === 'detailed'">
                        <div class="bg-gray-50 rounded-lg p-3 max-h-40 overflow-y-auto">
                            <template x-for="(section, sectionName) in draftData.formData" :key="sectionName">
                                <div class="mb-3">
                                    <h5 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1" x-text="sectionName"></h5>
                                    <template x-for="(value, key) in section" :key="key">
                                        <div class="flex justify-between text-xs py-1">
                                            <span class="text-gray-600" x-text="key"></span>
                                            <span class="font-medium text-right ml-2" x-text="value || '-'"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    {{-- JSON View --}}
                    <template x-if="previewMode === 'json'">
                        <div class="bg-gray-900 rounded-lg p-3 max-h-40 overflow-y-auto">
                            <pre class="text-xs text-green-400 font-mono" x-text="JSON.stringify(draftData.formData, null, 2)"></pre>
                        </div>
                    </template>
                </div>
            </template>
            
            {{-- Actions --}}
            <div class="mt-6 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-reverse sm:space-x-3">
                {{-- Restore Button --}}
                <button 
                    @click="restoreDraft()"
                    :disabled="isLoading"
                    type="button" 
                    class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-accent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm"
                >
                    <template x-if="isLoading">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-accent" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    Restaurar borrador
                </button>
                
                {{-- Start Fresh Button --}}
                <button 
                    @click="startFresh()"
                    :disabled="isLoading"
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-accent text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto sm:text-sm"
                >
                    Empezar de nuevo
                </button>
                
                {{-- Discard Button --}}
                <button 
                    @click="discardDraft()"
                    :disabled="isLoading"
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-accent text-base font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto sm:text-sm"
                >
                    Descartar borrador
                </button>
            </div>
            
            {{-- Help Text --}}
            <div class="mt-4 text-xs text-gray-500 bg-blue-50 rounded-lg p-3">
                <div class="flex items-start space-x-2">
                    <svg class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-medium text-blue-800">¿Qué significan estas opciones?</p>
                        <ul class="mt-1 space-y-1 text-blue-700">
                            <li><strong>Restaurar:</strong> Continúa donde lo dejaste con todos los datos guardados</li>
                            <li><strong>Empezar de nuevo:</strong> Inicia un formulario limpio (mantiene el borrador)</li>
                            <li><strong>Descartar:</strong> Elimina permanentemente el borrador guardado</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>