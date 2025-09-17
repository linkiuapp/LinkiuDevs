<x-tenant-admin-layout :store="$store">
    @section('title', 'Configuración de Envíos')
    
    @section('content')
<div x-data="shippingManager" x-init="init()" class="space-y-4 lg:space-y-6">
    
    <!-- Header -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-3 lg:py-4 px-4 lg:px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-black-500 mb-2">🚚 Configuración de Envíos</h2>
                    <p class="text-sm text-black-300">
                        Sistema simple y flexible para configurar tus métodos de envío
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="saveAll()" :disabled="saving" class="btn-primary flex items-center gap-2">
                        <x-solar-diskette-outline class="w-5 h-5" />
                        <span x-text="saving ? 'Guardando...' : 'Guardar Todo'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Secciones principales en grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        
        <!-- 🏪 RECOGIDA EN TIENDA -->
        <div class="bg-white rounded-lg border border-accent-200 overflow-hidden">
            <div class="bg-gradient-to-r from-primary-50 to-info-50 px-4 lg:px-6 py-3 lg:py-4 border-b border-accent-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">🏪</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-black-500">Recogida en Tienda</h3>
                        <p class="text-sm text-black-300">Los clientes pueden recoger sus pedidos directamente</p>
                    </div>
                </div>
                
                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" 
                           x-model="shipping.pickup_enabled" 
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-accent-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                    <span class="ml-3 text-sm font-medium" x-text="shipping.pickup_enabled ? 'Habilitado' : 'Deshabilitado'" :class="shipping.pickup_enabled ? 'text-primary-400' : 'text-black-300'"></span>
                </label>
            </div>
        </div>
        
        <div x-show="shipping.pickup_enabled" x-transition class="p-4 lg:p-6 space-y-3 lg:space-y-4">
            <!-- Tiempo de preparación -->
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">⏱️ Tiempo de preparación</label>
                <select x-model="shipping.pickup_preparation_time" class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                    @foreach(\App\Features\TenantAdmin\Models\SimpleShipping::PREPARATION_TIMES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Instrucciones -->
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">📝 Instrucciones para el cliente</label>
                <textarea x-model="shipping.pickup_instructions" 
                          rows="3" 
                          placeholder="Ej: Puedes recoger tu pedido en nuestra tienda ubicada en..."
                          class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent resize-none"></textarea>
                <p class="text-xs text-black-300 mt-1">Máximo 500 caracteres</p>
            </div>
        </div>
        </div>

        <!-- 🚚 ENVÍO LOCAL -->
        <div class="bg-white rounded-lg border border-accent-200 overflow-hidden">
            <div class="bg-gradient-to-r from-secondary-50 to-primary-50 px-4 lg:px-6 py-3 lg:py-4 border-b border-accent-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-secondary-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">🚚</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-black-500">Envío Local</h3>
                        <p class="text-sm text-black-300">Entregas dentro de tu ciudad principal</p>
                    </div>
                </div>
                
                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" 
                           x-model="shipping.local_enabled" 
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-accent-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-secondary-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary-300"></div>
                    <span class="ml-3 text-sm font-medium" x-text="shipping.local_enabled ? 'Habilitado' : 'Deshabilitado'" :class="shipping.local_enabled ? 'text-secondary-400' : 'text-black-300'"></span>
                </label>
            </div>
        </div>
        
        <div x-show="shipping.local_enabled" x-transition class="p-4 lg:p-6 space-y-3 lg:space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Ciudad principal -->
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">📍 Tu ciudad principal</label>
                    <input type="text" 
                           x-model="shipping.local_city" 
                           placeholder="Ej: Sincelejo"
                           class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-200 focus:border-transparent">
                    <p class="text-xs text-black-300 mt-1">Los clientes de esta ciudad pagarán tarifa local</p>
                </div>
                
                <!-- Costo -->
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">💰 Costo del envío</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">$</span>
                        <input type="number" 
                               x-model="shipping.local_cost" 
                               placeholder="3000"
                               min="0"
                               step="500"
                               class="w-full pl-8 pr-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-200 focus:border-transparent">
                    </div>
                </div>
                
                <!-- Tiempo de preparación -->
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">⏱️ Tiempo de entrega</label>
                    <select x-model="shipping.local_preparation_time" class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-200 focus:border-transparent">
                        @foreach(\App\Features\TenantAdmin\Models\SimpleShipping::PREPARATION_TIMES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Envío gratis -->
            <div class="bg-success-50 rounded-lg p-4 border border-success-200">
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" 
                           x-model="localFreeShippingEnabled" 
                           class="w-4 h-4 text-success-300 bg-accent-50 border-accent-300 rounded focus:ring-success-300">
                    <label class="text-sm font-medium text-success-400">🎁 Activar envío gratis por monto</label>
                </div>
                
                <div x-show="localFreeShippingEnabled" x-transition>
                    <div>
                        <label class="block text-sm font-medium text-success-400 mb-2">Envío gratis desde:</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-success-300">$</span>
                            <input type="number" 
                                   x-model="shipping.local_free_from" 
                                   placeholder="50000"
                                   min="0"
                                   step="1000"
                                   class="w-full pl-8 pr-3 py-2 border border-success-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-success-200 focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instrucciones -->
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">📝 Instrucciones para el cliente</label>
                <textarea x-model="shipping.local_instructions" 
                          rows="3" 
                          placeholder="Ej: Entregamos en toda la ciudad de Sincelejo en horario de 8AM a 6PM"
                          class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary-200 focus:border-transparent resize-none"></textarea>
            </div>
        </div>
        </div>
        
    </div> <!-- Fin del grid -->

    <!-- 📦 ENVÍO NACIONAL -->
    <div class="bg-white rounded-lg border border-accent-200 overflow-hidden">
        <div class="bg-gradient-to-r from-info-50 to-warning-50 px-4 lg:px-6 py-3 lg:py-4 border-b border-accent-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-info-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">📦</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-black-500">Envío Nacional</h3>
                        <p class="text-sm text-black-300">Configura zonas con diferentes tarifas</p>
                        <p class="text-xs text-info-400" x-text="`${zones.length}/${maxZones} zonas configuradas`"></p>
                    </div>
                </div>
                
                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" 
                           x-model="shipping.national_enabled" 
                           class="sr-only peer">
                    <div class="relative w-11 h-6 bg-accent-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-info-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-info-300"></div>
                    <span class="ml-3 text-sm font-medium" x-text="shipping.national_enabled ? 'Habilitado' : 'Deshabilitado'" :class="shipping.national_enabled ? 'text-info-400' : 'text-black-300'"></span>
                </label>
            </div>
        </div>
        
        <div x-show="shipping.national_enabled" x-transition class="p-4 lg:p-6 space-y-4 lg:space-y-6">
            
            <!-- Envío gratis nacional -->
            <div class="bg-success-50 rounded-lg p-4 border border-success-200">
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" 
                           x-model="nationalFreeShippingEnabled" 
                           class="w-4 h-4 text-success-300 bg-accent-50 border-accent-300 rounded focus:ring-success-300">
                    <label class="text-sm font-medium text-success-400">🎁 Activar envío gratis nacional por monto</label>
                </div>
                
                <div x-show="nationalFreeShippingEnabled" x-transition>
                    <div>
                        <label class="block text-sm font-medium text-success-400 mb-2">Envío gratis desde:</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-success-300">$</span>
                            <input type="number" 
                                   x-model="shipping.national_free_from" 
                                   placeholder="100000"
                                   min="0"
                                   step="1000"
                                   class="w-full pl-8 pr-3 py-2 border border-success-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-success-200 focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instrucciones generales -->
            <div>
                <label class="block text-sm font-medium text-black-400 mb-2">📝 Instrucciones generales para envío nacional</label>
                <textarea x-model="shipping.national_instructions" 
                          rows="2" 
                          placeholder="Ej: Enviamos a nivel nacional mediante empresas de mensajería confiables"
                          class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-info-200 focus:border-transparent resize-none"></textarea>
            </div>

            <!-- Zonas de envío -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-base font-semibold text-black-500">🗺️ Zonas de Envío</h4>
                    <button @click="addZone()" 
                            :disabled="zones.length >= maxZones"
                            class="btn-secondary flex items-center gap-2 text-sm"
                            :class="zones.length >= maxZones ? 'opacity-50 cursor-not-allowed' : ''">
                        <x-solar-add-circle-outline class="w-4 h-4" />
                        Agregar Zona
                    </button>
                </div>

                <!-- Lista de zonas -->
                <div class="space-y-4" id="zones-container">
                    <template x-for="(zone, index) in zones" :key="zone.id || `new-${index}`">
                        <div class="border border-accent-200 rounded-lg overflow-hidden" :class="zone.is_editing ? 'border-primary-300 shadow-sm' : ''">
                            
                            <!-- Vista normal -->
                            <div x-show="!zone.is_editing" class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h5 class="font-semibold text-black-500" x-text="zone.name"></h5>
                                            <span class="px-2 py-1 bg-primary-100 text-primary-400 rounded text-xs font-medium" x-text="formatPrice(zone.cost)"></span>
                                            <span class="px-2 py-1 bg-info-100 text-info-400 rounded text-xs" x-text="getDeliveryTimeLabel(zone.delivery_time)"></span>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="city in zone.cities" :key="city">
                                                <span class="px-2 py-1 bg-accent-100 text-black-400 rounded-full text-xs" x-text="city"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="editZone(index)" class="text-info-300 hover:text-info-200 p-2">
                                            <x-solar-pen-outline class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteZone(index)" class="text-error-300 hover:text-error-200 p-2">
                                            <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista de edición -->
                            <div x-show="zone.is_editing" class="p-4 bg-accent-50">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Nombre -->
                                        <div>
                                            <label class="block text-sm font-medium text-black-400 mb-1">🏷️ Nombre de la zona</label>
                                            <input type="text" 
                                                   x-model="zone.name" 
                                                   placeholder="Ej: Ciudades Principales"
                                                   class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-info-200 focus:border-transparent">
                                        </div>
                                        
                                        <!-- Costo -->
                                        <div>
                                            <label class="block text-sm font-medium text-black-400 mb-1">💰 Costo del envío</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">$</span>
                                                <input type="number" 
                                                       x-model="zone.cost" 
                                                       placeholder="8000"
                                                       min="0"
                                                       step="500"
                                                       class="w-full pl-8 pr-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-info-200 focus:border-transparent">
                                            </div>
                                        </div>
                                        
                                        <!-- Tiempo -->
                                        <div>
                                            <label class="block text-sm font-medium text-black-400 mb-1">⏱️ Tiempo de entrega</label>
                                            <select x-model="zone.delivery_time" class="w-full px-3 py-2 border border-accent-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-info-200 focus:border-transparent">
                                                @foreach(\App\Features\TenantAdmin\Models\SimpleShipping::DELIVERY_TIMES as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Cities Input con Tags -->
                                    <div>
                                        <label class="block text-sm font-medium text-black-400 mb-2">📍 Ciudades incluidas</label>
                                        <div class="cities-input-container">
                                            <div class="flex flex-wrap gap-2 p-3 border border-accent-300 rounded-lg bg-white min-h-[44px]" @click="$refs.cityInput.focus()">
                                                <!-- Tags de ciudades -->
                                                <template x-for="(city, cityIndex) in zone.cities" :key="cityIndex">
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-100 text-primary-400 rounded-lg text-sm">
                                                        <span x-text="city"></span>
                                                        <button @click="removeCity(index, cityIndex)" class="text-primary-300 hover:text-primary-200">
                                                            <x-solar-close-circle-outline class="w-4 h-4" />
                                                        </button>
                                                    </span>
                                                </template>
                                                
                                                <!-- Input para agregar ciudades -->
                                                <input type="text" 
                                                       x-ref="cityInput"
                                                       x-model="zone.newCity"
                                                       @keydown.enter.prevent="addCity(index)"
                                                       @keydown.comma.prevent="addCity(index)"
                                                       placeholder="Escribe una ciudad y presiona Enter..."
                                                       class="flex-1 min-w-[200px] outline-none bg-transparent">
                                            </div>
                                            <p class="text-xs text-black-300 mt-1">Escribe el nombre de una ciudad y presiona Enter. Máximo 20 ciudades por zona.</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Botones de acción -->
                                    <div class="flex items-center gap-3">
                                        <button @click="saveZone(index)" 
                                                :disabled="!isZoneValid(zone)"
                                                class="btn-primary text-sm"
                                                :class="!isZoneValid(zone) ? 'opacity-50 cursor-not-allowed' : ''">
                                            Guardar Zona
                                        </button>
                                        <button @click="cancelEditZone(index)" class="btn-secondary text-sm">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Estado vacío -->
                <div x-show="zones.length === 0" class="text-center py-8 border-2 border-dashed border-accent-200 rounded-lg">
                    <div class="w-16 h-16 bg-accent-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <span class="text-2xl">🗺️</span>
                    </div>
                    <h4 class="text-base font-medium text-black-400 mb-2">No hay zonas configuradas</h4>
                    <p class="text-sm text-black-300 mb-4">Agrega zonas para diferentes tarifas de envío nacional</p>
                    <button @click="addZone()" class="btn-primary text-sm">
                        Crear Primera Zona
                    </button>
                </div>
            </div>

            <!-- Configuración de ciudades no listadas -->
            <div class="bg-warning-50 rounded-lg p-4 border border-warning-200">
                <div class="flex items-center gap-3 mb-3">
                    <input type="checkbox" 
                           x-model="shipping.allow_unlisted_cities" 
                           class="w-4 h-4 text-warning-300 bg-accent-50 border-accent-300 rounded focus:ring-warning-300">
                    <label class="text-sm font-medium text-warning-400">⚠️ Permitir pedidos de ciudades no listadas</label>
                </div>
                
                <div x-show="shipping.allow_unlisted_cities" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-warning-400 mb-2">Costo por defecto:</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-warning-300">$</span>
                            <input type="number" 
                                   x-model="shipping.unlisted_cities_cost" 
                                   placeholder="12000"
                                   min="0"
                                   step="500"
                                   class="w-full pl-8 pr-3 py-2 border border-warning-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-warning-200 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-warning-400 mb-2">Mensaje para el cliente:</label>
                        <input type="text" 
                               x-model="shipping.unlisted_cities_message" 
                               placeholder="Contacta para confirmar disponibilidad"
                               class="w-full px-3 py-2 border border-warning-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-warning-200 focus:border-transparent">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para mensajes de error -->
    <div x-show="showErrorModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;"
         @click.away="showErrorModal = false">
        
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        
        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="showErrorModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 border border-gray-100">
                
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <x-solar-close-circle-bold class="w-6 h-6 text-red-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Ciudad Duplicada</h3>
                    </div>
                    <button @click="showErrorModal = false" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <x-solar-close-square-outline class="w-6 h-6" />
                    </button>
                </div>
                
                <!-- Content -->
                <div class="p-4">
                    <p class="text-gray-700 leading-relaxed" x-text="errorMessage"></p>
                </div>
                
                <!-- Footer -->
                <div class="flex justify-end p-4 border-t border-gray-200">
                    <button @click="showErrorModal = false" 
                            class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('shippingManager', () => ({
        // Data principal
        shipping: @json($shipping),
        zones: @json($shipping->zones ?? []),
        maxZones: {{ $maxZones }},
        currentZoneCount: {{ $currentZoneCount }},
        
        // Estados de UI
        saving: false,
        localFreeShippingEnabled: false,
        nationalFreeShippingEnabled: false,
        
        // Modal de error
        showErrorModal: false,
        errorMessage: '',
        
        init() {
            // Inicializar toggles de envío gratis
            this.localFreeShippingEnabled = !!this.shipping.local_free_from;
            this.nationalFreeShippingEnabled = !!this.shipping.national_free_from;
            
            // Watchers para envío gratis
            this.$watch('localFreeShippingEnabled', (enabled) => {
                if (!enabled) {
                    this.shipping.local_free_from = null;
                }
            });
            
            this.$watch('nationalFreeShippingEnabled', (enabled) => {
                if (!enabled) {
                    this.shipping.national_free_from = null;
                }
            });
            
            console.log('🚚 Shipping Manager initialized');
        },
        
        // Zona management
        addZone() {
            if (this.zones.length >= this.maxZones) {
                alert(`Máximo ${this.maxZones} zonas permitidas en tu plan`);
                return;
            }
            
            this.zones.push({
                id: null,
                name: '',
                cost: 8000,
                delivery_time: '3-5dias',
                cities: [],
                newCity: '',
                is_editing: true,
                is_new: true
            });
        },
        
        editZone(index) {
            this.zones[index].is_editing = true;
            this.zones[index].newCity = '';
        },
        
        cancelEditZone(index) {
            if (this.zones[index].is_new) {
                this.zones.splice(index, 1);
            } else {
                this.zones[index].is_editing = false;
                this.zones[index].newCity = '';
            }
        },
        
        async saveZone(index) {
            const zone = this.zones[index];
            
            if (!this.isZoneValid(zone)) {
                alert('Por favor completa todos los campos requeridos');
                return;
            }
            
            try {
                const url = zone.is_new ? 
                    '/{{ $store->slug }}/admin/envios/zones' :
                    `/{{ $store->slug }}/admin/envios/zones/${zone.id}`;
                
                const method = zone.is_new ? 'POST' : 'PUT';
                
                let response;
                
                if (zone.is_new) {
                    // POST para crear nueva zona - usar JSON
                    response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: zone.name,
                            cost: zone.cost,
                            delivery_time: zone.delivery_time,
                            cities: zone.cities
                        })
                    });
                } else {
                    // PUT para actualizar - usar FormData
                    const form = new FormData();
                    form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    form.append('_method', 'PUT');
                    form.append('name', zone.name);
                    form.append('cost', zone.cost);
                    form.append('delivery_time', zone.delivery_time);
                    form.append('cities', JSON.stringify(zone.cities));
                    
                    response = await fetch(url, {
                        method: 'POST',
                        body: form,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                }
                
                const data = await response.json();
                
                if (!response.ok) {
                    // Si es un error 422 (validación), mostrar el mensaje específico
                    if (response.status === 422 && data.message) {
                        this.showError(data.message);
                    } else {
                        this.showError(`Error HTTP ${response.status}: Problema en el servidor`);
                    }
                    return;
                }
                
                if (data.success) {
                    if (zone.is_new) {
                        this.zones[index] = { ...data.zone, is_editing: false, newCity: '' };
                    } else {
                        Object.assign(this.zones[index], data.zone, { is_editing: false, newCity: '' });
                    }
                    this.showSuccess(data.message);
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Error al guardar la zona: ' + error.message);
            }
        },
        
        async deleteZone(index) {
            const zone = this.zones[index];
            
            if (zone.is_new) {
                this.zones.splice(index, 1);
                return;
            }
            
            if (!confirm(`¿Eliminar la zona "${zone.name}"?`)) {
                return;
            }
            
            try {
                const url = `/{{ $store->slug }}/admin/envios/zones/${zone.id}`;
                
                // Crear un formulario temporal para envío
                const form = new FormData();
                form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                form.append('_method', 'DELETE');
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: form,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    // Si es un error 422 (validación), mostrar el mensaje específico
                    if (response.status === 422 && data.message) {
                        this.showError(data.message);
                    } else {
                        this.showError(`Error ${response.status}: Problema en el servidor`);
                    }
                    return;
                }
                
                if (data.success) {
                    this.zones.splice(index, 1);
                    this.showSuccess(data.message);
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                this.showError('Error al eliminar la zona: ' + error.message);
            }
        },
        
        // Mostrar modal de error
        showError(message) {
            this.errorMessage = message;
            this.showErrorModal = true;
        },
        
        // Cities management
        addCity(zoneIndex) {
            const zone = this.zones[zoneIndex];
            const city = zone.newCity?.trim();
            
            if (!city) return;
            
            if (zone.cities.length >= 20) {
                alert('Máximo 20 ciudades por zona');
                return;
            }
            
            if (zone.cities.includes(city)) {
                alert('Esta ciudad ya está agregada');
                return;
            }
            
            zone.cities.push(city);
            zone.newCity = '';
        },
        
        removeCity(zoneIndex, cityIndex) {
            if (this.zones[zoneIndex] && this.zones[zoneIndex].cities) {
                this.zones[zoneIndex].cities.splice(cityIndex, 1);
            }
        },
        
        // Validation
        isZoneValid(zone) {
            return zone.name?.trim() && 
                   zone.cost >= 0 && 
                   zone.delivery_time && 
                   zone.cities.length > 0;
        },
        
        // Guardar configuración general
        async saveAll() {
            this.saving = true;
            
            try {
                const response = await fetch('{{ route("tenant.admin.simple-shipping.update", $store->slug) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.shipping)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.shipping = data.shipping;
                    this.showSuccess(data.message);
                } else {
                    this.showError(data.message);
                }
            } catch (error) {
                console.error('Error saving shipping config:', error);
                this.showError('Error al guardar la configuración');
            } finally {
                this.saving = false;
            }
        },
        
        
        // Helpers
        formatPrice(price) {
            return '$' + new Intl.NumberFormat('es-CO').format(price);
        },
        
        getDeliveryTimeLabel(time) {
            const times = {
                @foreach(\App\Features\TenantAdmin\Models\SimpleShipping::DELIVERY_TIMES as $key => $label)
                    '{{ $key }}': '{{ $label }}',
                @endforeach
            };
            return times[time] || time;
        },
        
        showSuccess(message) {
            // Simple success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-success-300 text-white p-4 rounded-lg shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }));
});
</script>
@endpush

    @endsection
</x-tenant-admin-layout>
