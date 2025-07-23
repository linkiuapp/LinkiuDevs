<!-- Modal Cancelar Suscripción -->
<div x-show="showCancelModal" 
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
        <div @click.away="showCancelModal = false; resetModalData()"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="bg-white-50 rounded-lg shadow-xl max-w-md w-full">
            
            <!-- Header -->
            <div class="border-b border-white-100 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-error-300">⚠️ Cancelar Suscripción</h3>
                    <button @click="showCancelModal = false; resetModalData()" 
                            class="text-black-300 hover:text-black-400">
                        <x-solar-close-circle-outline class="w-5 h-5" />
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-4">
                <div class="mb-4">
                    <!-- Warning principal -->
                    <div class="bg-error-50 border border-error-100 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <x-solar-danger-outline class="w-5 h-5 text-error-300 mt-0.5 mr-3" />
                            <div>
                                <h4 class="text-sm font-medium text-error-300 mb-1">¿Estás seguro?</h4>
                                <div class="text-xs text-error-200 space-y-1">
                                    <p>• Tu tienda se mantendrá activa hasta el <strong>{{ $subscription->current_period_end->format('d/m/Y') }}</strong></p>
                                    <p>• Después pasará al plan Explorer automáticamente</p>
                                    <p>• Perderás acceso a las funcionalidades premium</p>
                                    <p>• Podrás reactivar en cualquier momento</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Razón de cancelación -->
                    <div class="mb-4">
                        <label for="cancellation_reason" class="block text-sm font-medium text-black-400 mb-2">
                            Razón de cancelación <span class="text-error-300">*</span>
                        </label>
                        <select id="cancellation_reason" 
                                x-model="reason"
                                required
                                class="w-full px-3 py-2 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 text-sm text-black-500 mb-2">
                            <option value="">Selecciona una razón</option>
                            <option value="Muy caro">Muy caro</option>
                            <option value="No uso todas las funcionalidades">No uso todas las funcionalidades</option>
                            <option value="Encontré una mejor alternativa">Encontré una mejor alternativa</option>
                            <option value="Problemas técnicos">Problemas técnicos</option>
                            <option value="Cerrando mi negocio">Cerrando mi negocio</option>
                            <option value="Cambio temporal">Cambio temporal</option>
                            <option value="Otro">Otro</option>
                        </select>
                        
                        <!-- Campo adicional si selecciona "Otro" -->
                        <div x-show="reason === 'Otro'" 
                             x-transition
                             class="mt-2">
                            <textarea x-model="customReason"
                                      placeholder="Describe la razón..."
                                      rows="2"
                                      class="w-full px-3 py-2 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 text-xs text-black-500"></textarea>
                        </div>
                    </div>
                    
                    <!-- Confirmación con contraseña -->
                    <div class="mb-4">
                        <label for="cancel_password" class="block text-sm font-medium text-black-400 mb-2">
                            Confirma tu contraseña <span class="text-error-300">*</span>
                        </label>
                        <input type="password" 
                               id="cancel_password"
                               x-model="password"
                               required
                               class="w-full px-3 py-2 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 text-sm text-black-500"
                               placeholder="Tu contraseña actual">
                    </div>
                    
                    <!-- Información de período de gracia -->
                    <div class="bg-info-50 border border-info-100 rounded-lg p-3">
                        <div class="flex items-start">
                            <x-solar-calendar-outline class="w-4 h-4 text-info-300 mt-0.5 mr-2" />
                            <div class="text-xs text-info-300">
                                <strong>Período de gracia:</strong> Tendrás {{ $subscription->days_until_expiration }} días 
                                para cambiar de opinión y reactivar tu suscripción.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="border-t border-white-100 px-6 py-4">
                <div class="flex gap-3 justify-end">
                    <button @click="showCancelModal = false; resetModalData()" 
                            type="button"
                            class="px-4 py-2 text-sm text-black-400 hover:text-black-500 transition-colors">
                        No, mantener suscripción
                    </button>
                    <button @click="cancelSubscription()" 
                            :disabled="!reason || !password || isLoading"
                            :class="{ 'opacity-50 cursor-not-allowed': !reason || !password || isLoading }"
                            class="px-4 py-2 bg-error-300 text-white-50 text-sm rounded-lg hover:bg-error-400 transition-colors">
                        <span x-show="!isLoading">Sí, cancelar suscripción</span>
                        <span x-show="isLoading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Cancelando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 