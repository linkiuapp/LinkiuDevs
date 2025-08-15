<x-tenant-admin-layout :store="$store">
    @section('title', 'Métodos de Envío')

    @section('content')
    <div x-data="shippingManagement" class="space-y-6">
        {{-- Header con información del plan --}}
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-black-500 mb-2">Configurar Métodos de Envío</h2>
                        <p class="text-sm text-black-300">
                            Zonas de envío: <span class="font-semibold">{{ $zonesCount }}/{{ $maxZones }}</span> 
                            (Plan {{ $store->plan->name }})
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="saveOrder()" 
                                class="btn-secondary flex items-center gap-2"
                                :disabled="!orderChanged">
                            <x-solar-diskette-outline class="w-5 h-5" />
                            Guardar Orden
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Métodos de Envío Disponibles --}}
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                <h3 class="text-lg font-semibold text-black-500">Métodos de Envío Disponibles</h3>
            </div>
            
            <div class="p-6 space-y-4">
                {{-- Lista de métodos con drag & drop --}}
                <div x-ref="sortableContainer" class="space-y-3">
                    @foreach($methods as $method)
                    <div class="shipping-method-item bg-white-100 rounded-lg p-4 cursor-move" 
                         data-method-id="{{ $method->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                {{-- Drag handle --}}
                                <div class="drag-handle text-black-300 hover:text-black-400">
                                    <x-solar-hamburger-menu-outline class="w-5 h-5" />
                                </div>
                                
                                {{-- Ícono del método --}}
                                <div class="w-10 h-10 bg-{{ $method->type === 'domicilio' ? 'primary' : 'secondary' }}-100 rounded-lg flex items-center justify-center">
                                    @if($method->type === 'domicilio')
                                        <x-solar-delivery-outline class="w-5 h-5 text-primary-300" />
                                    @else
                                        <x-solar-shop-outline class="w-5 h-5 text-secondary-300" />
                                    @endif
                                </div>
                                
                                {{-- Información del método --}}
                                <div>
                                    <h4 class="font-semibold text-black-500">{{ $method->name }}</h4>
                                    <p class="text-sm text-black-300">
                                        @if($method->type === 'domicilio')
                                            {{ $method->zones->count() }} zona(s) configurada(s)
                                            @if($method->zones->where('free_shipping_from', '>', 0)->count() > 0)
                                                • Envío gratis disponible
                                            @endif
                                        @else
                                            Gratis • {{ $method->preparation_time ?? '1 hora' }} de preparación
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                {{-- Toggle activo/inactivo --}}
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                        class="sr-only peer method-toggle"
                                        {{ $method->is_active ? 'checked' : '' }}
                                        data-method-id="{{ $method->id }}"
                                        @change="toggleMethod({{ $method->id }}, $event.target.checked)">
                                    <div class="w-11 h-6 bg-white-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                
                                {{-- Botón configurar --}}
                                @if($method->type === 'domicilio')
                                    <a href="{{ route('tenant.admin.shipping-methods.zones.create', [$store->slug, $method->id]) }}" 
                                       class="btn-outline-primary text-sm px-3 py-1.5">
                                        <x-solar-settings-outline class="w-4 h-4 mr-1" />
                                        Configurar Zonas
                                    </a>
                                @else
                                    <button @click="editPickup({{ $method->id }})" 
                                            class="btn-outline-primary text-sm px-3 py-1.5">
                                        <x-solar-settings-outline class="w-4 h-4 mr-1" />
                                        Configurar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                {{-- Advertencia mínimo 1 método activo --}}
                <div class="bg-warning-50 border border-warning-100 rounded-lg p-4 mt-4">
                    <div class="flex items-center gap-3">
                        <x-solar-danger-triangle-outline class="w-5 h-5 text-warning-300 flex-shrink-0" />
                        <p class="text-sm text-warning-300">
                            Debes tener al menos 1 método de envío activo para que tus clientes puedan realizar pedidos.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Zonas de Envío a Domicilio --}}
        @if($domicilioMethod)
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-black-500">Zonas de Envío a Domicilio</h3>
                    @if($zonesCount < $maxZones)
                        <a href="{{ route('tenant.admin.shipping-methods.zones.create', [$store->slug, $domicilioMethod->id]) }}" 
                           class="btn-primary flex items-center gap-2">
                            <x-solar-add-circle-outline class="w-5 h-5" />
                            Crear Zona
                        </a>
                    @else
                        <span class="text-sm text-error-300 font-medium">
                            Límite de zonas alcanzado
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if($domicilioMethod->zones->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-white-100">
                                    <th class="text-left py-3 px-4 font-medium text-black-400">Zona</th>
                                    <th class="text-left py-3 px-4 font-medium text-black-400">Costo</th>
                                    <th class="text-left py-3 px-4 font-medium text-black-400">Tiempo</th>
                                    <th class="text-left py-3 px-4 font-medium text-black-400">Estado</th>
                                    <th class="text-right py-3 px-4 font-medium text-black-400">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($domicilioMethod->zones as $zone)
                                <tr class="border-b border-white-100 hover:bg-white-50">
                                    <td class="py-3 px-4">
                                        <div>
                                            <p class="font-medium text-black-500">{{ $zone->name }}</p>
                                            @if($zone->description)
                                                <p class="text-sm text-black-300">{{ $zone->description }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="font-medium">${{ number_format($zone->cost, 0, ',', '.') }}</p>
                                        @if($zone->free_shipping_from)
                                            <p class="text-xs text-success-300">
                                                Gratis desde ${{ number_format($zone->free_shipping_from, 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="text-sm">{{ $zone->getEstimatedTimeLabel() }}</p>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($zone->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-300">
                                                Activa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white-200 text-black-300">
                                                Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('tenant.admin.shipping-methods.zones.edit', [$store->slug, $domicilioMethod->id, $zone->id]) }}" 
                                               class="text-primary-300 hover:text-primary-400">
                                                <x-solar-pen-outline class="w-4 h-4" />
                                            </a>
                                            <button @click="deleteZone({{ $zone->id }})" 
                                                    class="text-error-300 hover:text-error-400">
                                                <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-50 mb-4">
                            <x-solar-map-point-outline class="w-8 h-8 text-primary-200" />
                        </div>
                        <h3 class="text-lg font-semibold text-black-400 mb-2">No hay zonas configuradas</h3>
                        <p class="text-sm text-black-300 mb-4">Crea zonas de envío para que tus clientes puedan recibir sus pedidos</p>
                        @if($zonesCount < $maxZones)
                            <a href="{{ route('tenant.admin.shipping-methods.zones.create', [$store->slug, $domicilioMethod->id]) }}" 
                               class="btn-primary inline-flex items-center gap-2">
                                <x-solar-add-circle-outline class="w-5 h-5" />
                                Crear Primera Zona
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Modal de configuración de Pickup --}}
        <div x-show="showPickupModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-black-500 opacity-75"></div>
                </div>

                <div class="inline-block align-bottom bg-white-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="updatePickup()">
                        <div class="bg-white-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-semibold text-black-500 mb-4">Configurar Recoger en Tienda</h3>
                            
                            <div class="space-y-4">
                                {{-- Tiempo de preparación --}}
                                <div>
                                    <label class="block text-sm font-medium text-black-400 mb-1">
                                        Tiempo de preparación
                                    </label>
                                    <select x-model="pickupData.preparation_time" 
                                            class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                                        <option value="30min">30 minutos</option>
                                        <option value="1h">1 hora</option>
                                        <option value="2h">2 horas</option>
                                        <option value="4h">4 horas</option>
                                    </select>
                                </div>
                                
                                {{-- Notificación WhatsApp --}}
                                <div>
                                    <label class="flex items-center gap-3">
                                        <input type="checkbox" 
                                               x-model="pickupData.notification_enabled"
                                               class="w-4 h-4 text-primary-300 border-white-300 rounded focus:ring-primary-200">
                                        <span class="text-sm text-black-400">
                                            Notificar por WhatsApp cuando esté listo
                                        </span>
                                    </label>
                                    <p class="text-xs text-black-300 mt-1 ml-7">
                                        (Función disponible próximamente)
                                    </p>
                                </div>
                                
                                {{-- Instrucciones --}}
                                <div>
                                    <label class="block text-sm font-medium text-black-400 mb-1">
                                        Instrucciones para el cliente
                                    </label>
                                    <textarea x-model="pickupData.instructions" 
                                              rows="3"
                                              placeholder="Ej: Recoger en nuestra tienda principal en horario de atención"
                                              class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="btn-primary">
                                Guardar Cambios
                            </button>
                            <button type="button" @click="showPickupModal = false" class="btn-secondary">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('shippingManagement', () => ({
            orderChanged: false,
            showPickupModal: false,
            editingMethodId: null,
            pickupData: {
                instructions: '',
                preparation_time: '1h',
                notification_enabled: false
            },
            
            init() {
                this.initSortable();
                // ✅ Eliminado initToggleListeners() - evita conflicto con Alpine.js @change
            },
            
            initSortable() {
                const container = this.$refs.sortableContainer;
                if (container) {
                    Sortable.create(container, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onEnd: () => {
                            this.orderChanged = true;
                        }
                    });
                }
            },
            
            // ✅ ELIMINADO initToggleListeners() - Causaba conflicto con Alpine.js @change
            // Solo usamos el @change="toggleMethod(...)" del HTML
            
            toggleMethod(methodId, isActive) {
                fetch(`/{{ $store->slug }}/admin/shipping-methods/toggle-active/${methodId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // ✅ Mostrar mensaje sin recargar página
                        this.showToast(data.message || 'Estado actualizado correctamente', 'success');
                    } else {
                        // Revertir el toggle si hay error
                        const toggle = document.querySelector(`[data-method-id="${methodId}"]`);
                        if (toggle) {
                            toggle.checked = !isActive;
                        }
                        this.showToast(data.message || 'Error al actualizar estado', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revertir el toggle
                    const toggle = document.querySelector(`[data-method-id="${methodId}"]`);
                    if (toggle) {
                        toggle.checked = !isActive;
                    }
                    this.showToast('Error de conexión', 'error');
                });
            },
            
            saveOrder() {
                const items = Array.from(this.$refs.sortableContainer.children);
                const order = items.map((item, index) => ({
                    id: parseInt(item.dataset.methodId),
                    sort_order: index + 1
                }));
                
                fetch(`{{ route('tenant.admin.shipping-methods.update-order', $store->slug) }}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ methods: order })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.orderChanged = false;
                        this.showToast('Orden guardado correctamente', 'success');
                    } else {
                        this.showToast(data.message || 'Error al guardar orden', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showToast('Error de conexión', 'error');
                });
            },
            
            editPickup(methodId) {
                this.editingMethodId = methodId;
                // Aquí cargarías los datos del método si es necesario
                this.showPickupModal = true;
            },
            
            updatePickup() {
                if (!this.editingMethodId) return;
                
                fetch(`{{ route('tenant.admin.shipping-methods.update-pickup', [$store->slug, ':methodId']) }}`.replace(':methodId', this.editingMethodId), {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.pickupData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showPickupModal = false;
                        this.showToast('Configuración actualizada', 'success');
                        // ✅ Eliminado reload automático - ya no necesario
                    } else {
                        this.showToast(data.message || 'Error al actualizar', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showToast('Error de conexión', 'error');
                });
            },
            
            showToast(message, type = 'info') {
                // Implementación básica de toast (se puede mejorar)
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white-50 ${
                    type === 'success' ? 'bg-success-300' : 
                    type === 'error' ? 'bg-error-300' : 'bg-info-200'
                }`;
                toast.textContent = message;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        }));
    });
    </script>
    @endpush
</x-tenant-admin-layout> 