<!-- Modal Cambiar Plan -->
<div x-show="showChangePlanModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div @click.away="showChangePlanModal = false; resetModalData()"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="bg-accent-50 rounded-lg shadow-xl max-w-md w-full">
            
            <!-- Header -->
            <div class="border-b border-accent-100 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-black-500">Cambiar Plan</h3>
                    <button @click="showChangePlanModal = false; resetModalData()" 
                            class="text-black-300 hover:text-black-400">
                        <x-solar-close-circle-outline class="w-5 h-5" />
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-4">
                <div class="mb-4">
                    <div class="bg-info-50 border border-info-100 rounded-lg p-3 mb-4">
                        <h4 class="text-sm font-medium text-info-300 mb-1">Cambio de Plan</h4>
                        <p class="text-xs text-info-200">
                            Vas a cambiar al plan <strong x-text="selectedPlanName"></strong>. 
                            El cambio será efectivo inmediatamente.
                        </p>
                    </div>
                    
                    <!-- Razón del cambio -->
                    <div class="mb-4">
                        <label for="plan_change_reason" class="block text-sm font-medium text-black-400 mb-2">
                            Razón del cambio (opcional)
                        </label>
                        <textarea id="plan_change_reason" 
                                  x-model="reason"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 text-sm text-black-500 placeholder-black-200"
                                  placeholder="Describe por qué cambias de plan..."></textarea>
                    </div>
                    
                    <!-- Confirmación con contraseña -->
                    <div class="mb-4">
                        <label for="plan_change_password" class="block text-sm font-medium text-black-400 mb-2">
                            Confirma tu contraseña <span class="text-error-300">*</span>
                        </label>
                        <input type="password" 
                               id="plan_change_password"
                               x-model="password"
                               required
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 text-sm text-black-500"
                               placeholder="Tu contraseña actual">
                    </div>
                    
                    <!-- Warning -->
                    <div class="bg-warning-50 border border-warning-100 rounded-lg p-3">
                        <div class="flex items-start">
                            <x-solar-info-circle-outline class="w-4 h-4 text-warning-300 mt-0.5 mr-2" />
                            <div class="text-xs text-warning-300">
                                <strong>Importante:</strong> El cambio de plan es inmediato y afectará tu próxima facturación.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="border-t border-accent-100 px-6 py-4">
                <div class="flex gap-3 justify-end">
                    <button @click="showChangePlanModal = false; resetModalData()" 
                            type="button"
                            class="px-4 py-2 text-sm text-black-400 hover:text-black-500 transition-colors">
                        Cancelar
                    </button>
                    <button @click="changePlan()" 
                            :disabled="!password || isLoading"
                            :class="{ 'opacity-50 cursor-not-allowed': !password || isLoading }"
                            class="px-4 py-2 bg-primary-300 text-accent-50 text-sm rounded-lg hover:bg-primary-400 transition-colors">
                        <span x-show="!isLoading">Cambiar Plan</span>
                        <span x-show="isLoading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-accent-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Cambiando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 