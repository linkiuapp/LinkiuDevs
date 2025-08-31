<x-tenant-admin-layout :store="$store">
@section('title', 'Pedidos')

@section('content')
<div x-data="ordersManager" x-init="init()" class="space-y-4">
    
    <!-- Header con estadísticas -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-black-500 mb-2">Gestión de Pedidos</h2>
                    <p class="text-sm text-black-300">
                        Administra todos los pedidos de tu tienda
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="exportOrders()" class="text-black-300 hover:text-black-400 p-2 rounded-lg hover:bg-accent-100" title="Exportar">
                        <x-solar-download-outline class="w-5 h-5" />
                    </button>
                    <a href="{{ route('tenant.admin.orders.create', $store->slug) }}" 
                       class="btn-primary flex items-center gap-2">
                        <x-solar-add-circle-outline class="w-5 h-5" />
                        Nuevo Pedido
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas Grid -->
        <div class="px-6 py-3 border-b border-accent-100 bg-accent-50">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                <div class="text-center p-3 bg-accent-100 rounded-lg">
                    <div class="text-lg font-semibold text-black-500">{{ $stats['total'] }}</div>
                    <div class="text-xs text-black-300">Total</div>
                </div>
                <div class="text-center p-3 bg-warning-50 rounded-lg">
                    <div class="text-lg font-semibold text-warning-300">{{ $stats['pending'] }}</div>
                    <div class="text-xs text-warning-300">Pendientes</div>
                </div>
                <div class="text-center p-3 bg-info-50 rounded-lg">
                    <div class="text-lg font-semibold text-info-200">{{ $stats['confirmed'] }}</div>
                    <div class="text-xs text-info-200">Confirmados</div>
                </div>
                <div class="text-center p-3 bg-secondary-50 rounded-lg">
                    <div class="text-lg font-semibold text-secondary-200">{{ $stats['preparing'] }}</div>
                    <div class="text-xs text-secondary-200">Preparando</div>
                </div>
                <div class="text-center p-3 bg-primary-50 rounded-lg">
                    <div class="text-lg font-semibold text-primary-200">{{ $stats['shipped'] }}</div>
                    <div class="text-xs text-primary-200">Enviados</div>
                </div>
                <div class="text-center p-3 bg-success-50 rounded-lg">
                    <div class="text-lg font-semibold text-success-300">{{ $stats['delivered'] }}</div>
                    <div class="text-xs text-success-300">Entregados</div>
                </div>
                <div class="text-center p-3 bg-error-50 rounded-lg">
                    <div class="text-lg font-semibold text-error-300">{{ $stats['cancelled'] }}</div>
                    <div class="text-xs text-error-300">Cancelados</div>
                </div>
                <div class="text-center p-3 bg-success-100 rounded-lg">
                    <div class="text-sm font-semibold text-success-300">${{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
                    <div class="text-xs text-success-300">Ingresos</div>
                </div>
            </div>
        </div>

        <!-- Barra de herramientas y filtros -->
        <div class="px-6 py-3 border-b border-accent-100 bg-accent-50">
            <form method="GET" action="{{ route('tenant.admin.orders.index', $store->slug) }}">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-4">
                        <!-- Filtros rápidos -->
                        <select name="status" class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Todos los estados</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                            <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparando</option>
                            <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Enviado</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregado</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>

                        <select name="payment_method" class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Todos los métodos</option>
                            <option value="transferencia" {{ request('payment_method') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="contra_entrega" {{ request('payment_method') === 'contra_entrega' ? 'selected' : '' }}>Contra Entrega</option>
                            <option value="efectivo" {{ request('payment_method') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                        </select>

                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Buscar por número, cliente..." 
                               class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">

                        <button type="submit" class="px-3 py-1.5 bg-primary-200 text-accent-50 rounded-lg text-sm hover:bg-primary-300 transition-colors">
                            <x-solar-magnifer-outline class="w-4 h-4" />
                        </button>

                        <a href="{{ route('tenant.admin.orders.index', $store->slug) }}" class="px-3 py-1.5 bg-accent-100 text-black-300 rounded-lg text-sm hover:bg-accent-200 transition-colors">
                            <x-solar-restart-outline class="w-4 h-4" />
                        </a>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-black-300">{{ $orders->total() }} pedidos</span>
                    </div>
                </div>

                <!-- Filtros avanzados expandibles -->
                <div x-data="{ showAdvanced: false }">
                    <button type="button" @click="showAdvanced = !showAdvanced" 
                            class="text-sm text-primary-200 hover:text-primary-300 flex items-center gap-1">
                        <span x-text="showAdvanced ? 'Ocultar filtros avanzados' : 'Mostrar filtros avanzados'"></span>
                        <x-solar-alt-arrow-down-outline class="w-4 h-4 transition-transform" x-bind:class="{ 'rotate-180': showAdvanced }" />
                    </button>
                    
                    <div x-show="showAdvanced" x-transition class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="block text-xs text-black-400 mb-1">Fecha Desde</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="w-full px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                        </div>
                        <div>
                            <label class="block text-xs text-black-400 mb-1">Fecha Hasta</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="w-full px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                        </div>
                        <div>
                            <label class="block text-xs text-black-400 mb-1">Monto Desde</label>
                            <input type="number" name="amount_from" value="{{ request('amount_from') }}" 
                                   placeholder="0" min="0" step="1000"
                                   class="w-full px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                        </div>
                        <div>
                            <label class="block text-xs text-black-400 mb-1">Monto Hasta</label>
                            <input type="number" name="amount_to" value="{{ request('amount_to') }}" 
                                   placeholder="Sin límite" min="0" step="1000"
                                   class="w-full px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-accent-100">
                    <tr class="text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                        <th class="px-6 py-3">Pedido</th>
                        <th class="px-6 py-3">Cliente</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Pago</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-accent-50 divide-y divide-accent-100">
                    @forelse($orders as $order)
                        <tr class="text-black-400 hover:bg-accent-100">
                            <td class="px-6 py-4">
                                <div class="flex items-center text-sm">
                                    <div class="w-10 h-10 mr-3 flex items-center justify-center bg-primary-50 rounded-lg">
                                        <x-solar-clipboard-list-outline class="w-5 h-5 text-primary-200" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-black-500">{{ $order->order_number }}</p>
                                        <p class="text-xs text-black-200">{{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div>
                                    <p class="font-semibold text-black-500">{{ $order->customer_name }}</p>
                                    <p class="text-xs text-black-200">{{ $order->customer_phone }}</p>
                                    <p class="text-xs text-black-200">{{ $order->delivery_type_label }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="text-xs {{ $order->status_color }} px-2 py-1 rounded">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div>
                                    <p class="text-black-500">{{ $order->payment_method_label }}</p>
                                    @if($order->payment_proof_path)
                                        <p class="text-xs text-success-300 flex items-center">
                                            <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                            Con comprobante
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div>
                                    <p class="font-semibold text-black-500">${{ number_format($order->total, 0, ',', '.') }}</p>
                                    @if($order->shipping_cost > 0)
                                        <p class="text-xs text-black-200">+ ${{ number_format($order->shipping_cost, 0, ',', '.') }} envío</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div>
                                    <p class="text-black-500">{{ $order->created_at->format('d/m/Y') }}</p>
                                    <p class="text-xs text-black-200">{{ $order->created_at->format('H:i') }}</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-4">
                                    <!-- Ver -->
                                    <a href="{{ route('tenant.admin.orders.show', [$store->slug, $order->id]) }}" 
                                       class="text-info-200 hover:text-info-300" title="Ver">
                                        <x-solar-eye-outline class="w-5 h-5" />
                                    </a>

                                    <!-- Editar (solo estados tempranos) -->
                                    @if($order->canBeEdited())
                                        <a href="{{ route('tenant.admin.orders.edit', [$store->slug, $order->id]) }}" 
                                           class="text-primary-200 hover:text-primary-300" title="Editar">
                                            <x-solar-pen-new-square-outline class="w-5 h-5" />
                                        </a>
                                    @endif

                                    <!-- Cambiar Estado -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" 
                                                class="text-secondary-200 hover:text-secondary-300" 
                                                title="Cambiar estado">
                                            <x-solar-refresh-outline class="w-5 h-5" />
                                        </button>
                                        <div x-show="open" @click.away="open = false" 
                                             class="absolute right-0 mt-2 w-48 bg-accent-50 rounded-lg shadow-lg border border-accent-200 z-10">
                                            <div class="py-2">
                                                @foreach(['pending' => 'Pendiente', 'confirmed' => 'Confirmado', 'preparing' => 'Preparando', 'shipped' => 'Enviado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado'] as $status => $label)
                                                    @if($status !== $order->status)
                                                        <button onclick="updateOrderStatus({{ $order->id }}, '{{ $status }}')" 
                                                                class="block w-full text-left px-4 py-2 text-sm text-black-400 hover:bg-accent-100">
                                                            {{ $label }}
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Duplicar -->
                                    <button onclick="duplicateOrder({{ $order->id }})" 
                                            class="text-warning-300 hover:text-warning-400" 
                                            title="Duplicar">
                                        <x-solar-copy-outline class="w-5 h-5" />
                                    </button>

                                    <!-- Eliminar (solo pendientes) -->
                                    @if($order->status === 'pending')
                                        <button onclick="deleteOrder({{ $order->id }}, '{{ $order->order_number }}')" 
                                                class="text-error-200 hover:text-error-300" 
                                                title="Eliminar">
                                            <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-solar-clipboard-list-outline class="w-12 h-12 text-black-200 mb-3" />
                                    <p class="text-black-300">No hay pedidos</p>
                                    @if(request()->hasAny(['status', 'payment_method', 'delivery_type', 'search', 'date_from', 'date_to', 'amount_from', 'amount_to']))
                                        <p class="text-sm text-black-200 mb-3">No se encontraron pedidos con los filtros aplicados</p>
                                        <a href="{{ route('tenant.admin.orders.index', $store->slug) }}" class="btn-primary text-sm">
                                            Limpiar filtros
                                        </a>
                                    @else
                                        <a href="{{ route('tenant.admin.orders.create', $store->slug) }}" class="btn-primary mt-3 text-sm">
                                            Crear primer pedido
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-accent-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity" 
                 @click="closeDeleteModal()">
                <div class="absolute inset-0 bg-black-500/75 backdrop-blur-sm"></div>
            </div>

            <!-- Modal -->
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-accent-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="bg-accent-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-50 sm:mx-0 sm:h-10 sm:w-10">
                            <x-solar-trash-bin-trash-bold class="h-6 w-6 text-error-300" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-black-400">
                                Eliminar Pedido
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-black-300">
                                    ¿Estás seguro de que deseas eliminar el pedido <span class="font-semibold" x-text="deleteOrderNumber"></span>? 
                                    Esta acción no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-accent-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            @click="confirmDelete()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-error-200 text-base font-medium text-accent-50 hover:bg-error-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-200 sm:ml-3 sm:w-auto sm:text-sm">
                        Eliminar
                    </button>
                    <button type="button" 
                            @click="closeDeleteModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-accent-300 shadow-sm px-4 py-2 bg-accent-50 text-base font-medium text-black-400 hover:bg-accent-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ordersManager', () => ({
        showDeleteModal: false,
        deleteOrderId: null,
        deleteOrderNumber: '',

        init() {
            console.log('Orders Manager initialized');
        },

        deleteOrder(orderId, orderNumber) {
            this.deleteOrderId = orderId;
            this.deleteOrderNumber = orderNumber;
            this.showDeleteModal = true;
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.deleteOrderId = null;
            this.deleteOrderNumber = '';
        },

        confirmDelete() {
            if (this.deleteOrderId) {
                fetch(`{{ route('tenant.admin.orders.destroy', [$store->slug, ':id']) }}`.replace(':id', this.deleteOrderId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closeDeleteModal();
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar pedido');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
            }
        }
    }));
});

function updateOrderStatus(orderId, newStatus) {
    if (!confirm('¿Cambiar el estado de este pedido?')) return;

    const notes = prompt('Notas adicionales (opcional):');
    
    fetch(`{{ route('tenant.admin.orders.update-status', [$store->slug, ':id']) }}`.replace(':id', orderId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: newStatus,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error al cambiar estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

function duplicateOrder(orderId) {
    if (!confirm('¿Duplicar este pedido?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('tenant.admin.orders.duplicate', [$store->slug, ':id']) }}`.replace(':id', orderId);
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);
    
    document.body.appendChild(form);
    form.submit();
}

function exportOrders() {
    alert('Funcionalidad de exportación próximamente');
}

// Función global para deleteOrder desde botones
window.deleteOrder = function(orderId, orderNumber) {
    Alpine.store('ordersManager') || Alpine.$data(document.querySelector('[x-data="ordersManager"]')).deleteOrder(orderId, orderNumber);
};
</script>
@endpush
@endsection
</x-tenant-admin-layout> 