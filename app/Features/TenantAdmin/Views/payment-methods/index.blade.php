<x-tenant-admin-layout :store="$store">

@section('title', 'Métodos de Pago')

@push('styles')
<style>
    /* Estilos para drag & drop */
    .drag-handle {
        cursor: grab;
        transition: color 0.2s ease;
    }
    .drag-handle:hover {
        color: #4F46E5; /* primary color */
    }
    .dragging-active .drag-handle {
        cursor: grabbing;
    }
    .sortable-drag {
        opacity: 0.8;
        background-color: #F9FAFB !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .sortable-chosen {
        background-color: #F3F4F6;
    }
    .sortable-ghost {
        background-color: #EFF6FF !important;
        border: 1px dashed #4F46E5;
    }
    tr.sortable-chosen td {
        background-color: #F3F4F6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid" x-data="paymentMethodManagement">
    {{-- Sistema de Notificaciones --}}
    <div x-show="showNotification" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg"
         :class="{
            'bg-success-50 text-success-300 border border-success-100': notificationType === 'success',
            'bg-error-50 text-error-300 border border-error-100': notificationType === 'error',
            'bg-warning-50 text-warning-300 border border-warning-100': notificationType === 'warning'
         }">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <template x-if="notificationType === 'success'">
                    <x-solar-check-circle-outline class="w-5 h-5" />
                </template>
                <template x-if="notificationType === 'error'">
                    <x-solar-close-circle-outline class="w-5 h-5" />
                </template>
                <template x-if="notificationType === 'warning'">
                    <x-solar-danger-triangle-outline class="w-5 h-5" />
                </template>
            </div>
            <div x-text="notificationMessage"></div>
            <button @click="showNotification = false" class="ml-auto">
                <x-solar-close-circle-outline class="w-4 h-4" />
            </button>
        </div>
    </div>
    
    {{-- Loading Overlay --}}
    <div x-show="isLoading" 
         class="fixed inset-0 bg-black-400 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white-50 p-4 rounded-lg shadow-lg flex items-center gap-3">
            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-200"></div>
            <span class="text-black-400">Actualizando orden...</span>
        </div>
    </div>

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-black-400">Métodos de Pago</h1>
            <p class="text-sm text-black-300">Configura los métodos de pago disponibles para tus clientes</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.admin.payment-methods.create', ['store' => $store->slug]) }}" 
                class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-add-circle-outline class="w-5 h-5" />
                Nuevo Método
            </a>
        </div>
    </div>

    {{-- Información de métodos de pago --}}
    <div class="bg-white-50 rounded-lg p-6 mb-6 border border-white-100">
        <div class="flex items-start gap-4">
            <div class="rounded-full bg-primary-50 p-3 flex-shrink-0">
                <x-solar-info-circle-outline class="w-6 h-6 text-primary-200" />
            </div>
            <div>
                <h3 class="text-lg font-semibold text-black-400 mb-1">Gestión de Métodos de Pago</h3>
                <p class="text-sm text-black-300 mb-2">
                    Configura los métodos de pago que tus clientes podrán utilizar al realizar sus compras. 
                    Puedes activar o desactivar métodos, cambiar su orden de visualización y configurar opciones específicas para cada uno.
                </p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <div class="bg-white-100 px-3 py-1 rounded-lg flex items-center gap-2">
                        <x-solar-sort-outline class="w-4 h-4 text-black-300" />
                        <span class="text-xs text-black-300">Arrastra para reordenar</span>
                    </div>
                    <div class="bg-white-100 px-3 py-1 rounded-lg flex items-center gap-2">
                        <x-solar-eye-outline class="w-4 h-4 text-primary-200" />
                        <span class="text-xs text-black-300">Ver detalles</span>
                    </div>
                    <div class="bg-white-100 px-3 py-1 rounded-lg flex items-center gap-2">
                        <x-solar-pen-2-outline class="w-4 h-4 text-warning-200" />
                        <span class="text-xs text-black-300">Editar método</span>
                    </div>
                    <div class="bg-white-100 px-3 py-1 rounded-lg flex items-center gap-2">
                        <x-solar-server-path-outline class="w-4 h-4 text-success-200" />
                        <span class="text-xs text-black-300">Activar/Desactivar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de métodos de pago --}}
    <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6 shadow-sm">
        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">Métodos de Pago Disponibles</h2>
        </div>
        <div class="p-6">
            @if($paymentMethods->isEmpty())
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-50 mb-4">
                        <x-solar-card-outline class="w-8 h-8 text-primary-200" />
                    </div>
                    <h3 class="text-lg font-semibold text-black-400 mb-2">No hay métodos de pago configurados</h3>
                    <p class="text-sm text-black-300 mb-4">Configura los métodos de pago para tus clientes</p>
                    <a href="{{ route('tenant.admin.payment-methods.create', ['store' => $store->slug]) }}" class="btn-primary px-4 py-2 rounded-lg inline-flex items-center gap-2">
                        <x-solar-add-circle-outline class="w-5 h-5" />
                        Configurar Métodos de Pago
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white-100">
                        <thead class="bg-white-100">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Orden
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Método
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Disponibilidad
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Predeterminado
                                </th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white-50 divide-y divide-white-100">
                            @foreach($paymentMethods as $method)
                                <tr data-method-id="{{ $method->id }}">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="drag-handle cursor-move mr-2 text-black-300">
                                                <x-solar-sort-outline class="w-5 h-5" />
                                            </span>
                                            <span class="text-sm text-black-400">{{ $method->sort_order }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-50 flex items-center justify-center">
                                                @if($method->isCash())
                                                    <x-solar-wallet-money-outline class="w-5 h-5 text-primary-200" />
                                                @elseif($method->isBankTransfer())
                                                    <x-solar-card-transfer-outline class="w-5 h-5 text-primary-200" />
                                                @elseif($method->isCardTerminal())
                                                    <x-solar-card-outline class="w-5 h-5 text-primary-200" />
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-black-400">{{ $method->name }}</div>
                                                <div class="text-xs text-black-300">
                                                    @if($method->isCash())
                                                        Efectivo
                                                    @elseif($method->isBankTransfer())
                                                        Transferencia Bancaria
                                                    @elseif($method->isCardTerminal())
                                                        Datáfono
                                                    @endif
                                                </div>
                                                @if($method->instructions)
                                                    <div class="text-xs text-black-200 mt-1 max-w-xs truncate">
                                                        {{ Str::limit($method->instructions, 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <button @click="toggleActive({{ $method->id }}, {{ $method->is_active ? 'true' : 'false' }})" 
                                                class="flex items-center gap-2 focus:outline-none">
                                            @if($method->is_active)
                                                <span class="badge-soft-success">Activo</span>
                                            @else
                                                <span class="badge-soft-secondary">Inactivo</span>
                                            @endif
                                            <x-solar-toggle-{{ $method->is_active ? 'on' : 'off' }}-outline class="w-5 h-5 {{ $method->is_active ? 'text-success-200' : 'text-black-200' }}" />
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-black-400">
                                            @if($method->available_for_pickup && $method->available_for_delivery)
                                                <span class="badge-soft-info">Pickup y Delivery</span>
                                            @elseif($method->available_for_pickup)
                                                <span class="badge-soft-info">Solo Pickup</span>
                                            @elseif($method->available_for_delivery)
                                                <span class="badge-soft-info">Solo Delivery</span>
                                            @else
                                                <span class="badge-soft-secondary">No disponible</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <button @click="setDefaultMethod({{ $method->id }})" class="focus:outline-none">
                                            @if($defaultMethod && $defaultMethod->id === $method->id)
                                                <span class="badge-soft-primary flex items-center gap-1">
                                                    <x-solar-star-bold class="w-4 h-4" />
                                                    Predeterminado
                                                </span>
                                            @else
                                                <span class="text-sm text-black-300 hover:text-primary-200 flex items-center gap-1">
                                                    <x-solar-star-outline class="w-4 h-4" />
                                                    Establecer
                                                </span>
                                            @endif
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('tenant.admin.payment-methods.show', ['store' => $store->slug, 'paymentMethod' => $method->id]) }}" 
                                               class="text-primary-200 hover:text-primary-300" title="Ver detalles">
                                                <x-solar-eye-outline class="w-5 h-5" />
                                            </a>
                                            <a href="{{ route('tenant.admin.payment-methods.edit', ['store' => $store->slug, 'paymentMethod' => $method->id]) }}" 
                                               class="text-warning-200 hover:text-warning-300" title="Editar método">
                                                <x-solar-pen-2-outline class="w-5 h-5" />
                                            </a>
                                            @if($method->isBankTransfer())
                                                <a href="{{ route('tenant.admin.payment-methods.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $method->id]) }}" 
                                                class="text-info-200 hover:text-info-300" title="Gestionar cuentas bancarias">
                                                    <x-solar-card-transfer-outline class="w-5 h-5" />
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Instrucciones para el usuario --}}
    <div class="bg-white-50 rounded-lg p-6 border border-white-100">
        <h3 class="text-lg font-semibold text-black-400 mb-3">¿Cómo configurar tus métodos de pago?</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col">
                <div class="rounded-full bg-primary-50 p-3 w-12 h-12 flex items-center justify-center mb-3">
                    <span class="text-primary-300 font-bold">1</span>
                </div>
                <h4 class="text-md font-semibold text-black-400 mb-1">Crear métodos de pago</h4>
                <p class="text-sm text-black-300">
                    Agrega los métodos de pago que deseas ofrecer a tus clientes. Puedes configurar efectivo, transferencia bancaria y datáfono.
                </p>
            </div>
            
            <div class="flex flex-col">
                <div class="rounded-full bg-primary-50 p-3 w-12 h-12 flex items-center justify-center mb-3">
                    <span class="text-primary-300 font-bold">2</span>
                </div>
                <h4 class="text-md font-semibold text-black-400 mb-1">Configurar opciones</h4>
                <p class="text-sm text-black-300">
                    Personaliza cada método con instrucciones específicas y configura su disponibilidad para pickup o delivery.
                </p>
            </div>
            
            <div class="flex flex-col">
                <div class="rounded-full bg-primary-50 p-3 w-12 h-12 flex items-center justify-center mb-3">
                    <span class="text-primary-300 font-bold">3</span>
                </div>
                <h4 class="text-md font-semibold text-black-400 mb-1">Ordenar y activar</h4>
                <p class="text-sm text-black-300">
                    Arrastra los métodos para cambiar su orden de visualización y activa o desactiva según tus necesidades.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('paymentMethodManagement', () => ({
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        isLoading: false,
        sortable: null,
        
        init() {
            this.$nextTick(() => {
                this.initSortable();
            });
        },
        
        initSortable() {
            const tbody = this.$el.querySelector('tbody');
            if (!tbody) return;
            
            this.sortable = new Sortable(tbody, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-primary-50',
                dragClass: 'sortable-drag',
                chosenClass: 'sortable-chosen',
                onStart: (evt) => {
                    // Add a class to the body to indicate dragging is in progress
                    document.body.classList.add('dragging-active');
                },
                onEnd: (evt) => {
                    // Remove the class when dragging ends
                    document.body.classList.remove('dragging-active');
                    this.updateOrder();
                }
            });
        },
        
        updateOrder() {
            const rows = this.$el.querySelectorAll('tbody tr');
            if (!rows.length) return;
            
            this.isLoading = true;
            
            // Get all method IDs in the current order
            const methodIds = Array.from(rows).map(row => {
                return parseInt(row.dataset.methodId);
            });
            
            // Send the order to the server
            fetch('{{ route("tenant.admin.payment-methods.update-order", ["store" => $store->slug]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ methods: methodIds })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                this.isLoading = false;
                if (data.success) {
                    this.showNotificationMessage(data.message, 'success');
                    
                    // Update the displayed order numbers
                    this.updateDisplayedOrderNumbers(methodIds);
                } else {
                    this.showNotificationMessage(data.message, 'error');
                }
            })
            .catch(error => {
                this.isLoading = false;
                this.showNotificationMessage('Error al actualizar el orden: ' + error, 'error');
            });
        },
        
        updateDisplayedOrderNumbers(methodIds) {
            // Update the displayed order numbers in the UI
            const rows = this.$el.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                const orderSpan = row.querySelector('td:first-child .text-sm');
                if (orderSpan) {
                    orderSpan.textContent = index + 1;
                }
            });
        },
        
        toggleActive(id, isActive) {
            this.isLoading = true;
            
            fetch(`{{ route("tenant.admin.payment-methods.toggle-active", ["store" => $store->slug, "paymentMethod" => ":id"]) }}`.replace(':id', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                this.isLoading = false;
                if (data.success) {
                    this.showNotificationMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotificationMessage(data.message, 'error');
                }
            })
            .catch(error => {
                this.isLoading = false;
                this.showNotificationMessage('Error al cambiar el estado: ' + error, 'error');
            });
        },
        
        setDefaultMethod(id) {
            this.isLoading = true;
            
            // Esta es una simulación, necesitarías implementar esta ruta en el controlador
            fetch(`{{ route("tenant.admin.payment-methods.edit", ["store" => $store->slug, "paymentMethod" => ":id"]) }}`.replace(':id', id), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                this.isLoading = false;
                if (response.ok) {
                    window.location.href = `{{ route("tenant.admin.payment-methods.edit", ["store" => $store->slug, "paymentMethod" => ":id"]) }}`.replace(':id', id);
                } else {
                    this.showNotificationMessage('Error al acceder a la edición del método', 'error');
                }
            })
            .catch(error => {
                this.isLoading = false;
                this.showNotificationMessage('Error: ' + error, 'error');
            });
        },
        
        showNotificationMessage(message, type = 'success') {
            this.notificationMessage = message;
            this.notificationType = type;
            this.showNotification = true;
            
            setTimeout(() => {
                this.showNotification = false;
            }, 5000);
        }
    }));
});
</script>
@endpush
@endsection
</x-tenant-admin-layout>