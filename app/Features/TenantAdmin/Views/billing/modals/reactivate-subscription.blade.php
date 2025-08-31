<!-- Modal Reactivar Suscripción -->
<div x-show="showReactivateModal" 
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
        <div @click.away="showReactivateModal = false; resetModalData()"
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
                    <h3 class="text-lg font-semibold text-success-300">🔄 Reactivar Suscripción</h3>
                    <button @click="showReactivateModal = false; resetModalData()" 
                            class="text-black-300 hover:text-black-400">
                        <x-solar-close-circle-outline class="w-5 h-5" />
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-4">
                <div class="mb-4">
                    <!-- Información de reactivación -->
                    <div class="bg-success-50 border border-success-100 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mt-0.5 mr-3" />
                            <div>
                                <h4 class="text-sm font-medium text-success-300 mb-1">¡Bienvenido de vuelta!</h4>
                                <div class="text-xs text-success-200 space-y-1">
                                    <p>• Tu suscripción se reactivará inmediatamente</p>
                                    <p>• Recuperarás acceso a todas las funcionalidades premium</p>
                                    <p>• Plan actual: <strong>{{ $subscription->plan->name }}</strong></p>
                                    <p>• Próxima facturación: <strong>{{ $subscription->current_period_end->format('d/m/Y') }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estado actual -->
                    <div class="mb-4">
                        <div class="text-sm text-black-400 mb-2">Estado actual de tu suscripción:</div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-warning-300 text-black-500">
                                <x-solar-clock-circle-outline class="w-3 h-3 mr-1" />
                                {{ $subscription->status_label }}
                            </span>
                            @if($subscription->is_in_grace_period)
                                <span class="text-xs text-black-300">
                                    (Período de gracia hasta {{ $subscription->grace_period_end->format('d/m/Y') }})
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Confirmación con contraseña -->
                    <div class="mb-4">
                        <label for="reactivate_password" class="block text-sm font-medium text-black-400 mb-2">
                            Confirma tu contraseña <span class="text-error-300">*</span>
                        </label>
                        <input type="password" 
                               id="reactivate_password"
                               x-model="password"
                               required
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 text-sm text-black-500"
                               placeholder="Tu contraseña actual">
                    </div>
                    
                    <!-- Información de facturación -->
                    <div class="bg-info-50 border border-info-100 rounded-lg p-3">
                        <div class="flex items-start">
                            <x-solar-dollar-minimalistic-outline class="w-4 h-4 text-info-300 mt-0.5 mr-2" />
                            <div class="text-xs text-info-300">
                                <strong>Facturación:</strong> Tu próxima factura será el 
                                {{ $subscription->current_period_end->format('d/m/Y') }} por 
                                ${{ number_format($subscription->next_billing_amount, 0, ',', '.') }}.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="border-t border-accent-100 px-6 py-4">
                <div class="flex gap-3 justify-end">
                    <button @click="showReactivateModal = false; resetModalData()" 
                            type="button"
                            class="px-4 py-2 text-sm text-black-400 hover:text-black-500 transition-colors">
                        Cancelar
                    </button>
                    <button @click="reactivateSubscription()" 
                            :disabled="!password || isLoading"
                            :class="{ 'opacity-50 cursor-not-allowed': !password || isLoading }"
                            class="px-4 py-2 bg-success-300 text-accent-50 text-sm rounded-lg hover:bg-success-400 transition-colors">
                        <span x-show="!isLoading">Reactivar Suscripción</span>
                        <span x-show="isLoading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-accent-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Reactivando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 