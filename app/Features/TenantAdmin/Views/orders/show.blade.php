<x-tenant-admin-layout :store="$store">
@section('title', 'Pedido #' . $order->order_number)

@section('content')
<div class="max-w-6xl mx-auto" x-data="orderDetail" x-init="init()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('tenant.admin.orders.index', $store->slug) }}" 
               class="text-black-300 hover:text-black-400">
                <x-solar-arrow-left-outline class="w-6 h-6" />
            </a>
            <h1 class="text-lg font-semibold text-black-500">Pedido #{{ $order->order_number }}</h1>
            <span class="text-xs {{ $order->status_color }} px-2 py-1 rounded">
                {{ $order->status_label }}
            </span>
        </div>
        <p class="text-sm text-black-300">
            Creado el {{ $order->created_at->format('d/m/Y \a \l\a\s H:i') }} 
            ({{ $order->created_at->diffForHumans() }})
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Columna Principal -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- Información del Cliente -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Información del Cliente</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-black-400 mb-1">Nombre</label>
                        <div class="text-sm text-black-500">{{ $order->customer_name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-black-400 mb-1">Teléfono</label>
                        <div class="text-sm text-black-500">
                            <a href="https://wa.me/57{{ preg_replace('/\D/', '', $order->customer_phone) }}" 
                               target="_blank" class="text-success-300 hover:text-success-400 flex items-center gap-1">
                                {{ $order->customer_phone }}
                                <x-solar-chat-round-dots-outline class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs text-black-400 mb-1">Dirección</label>
                        <div class="text-sm text-black-500">{{ $order->customer_address }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-black-400 mb-1">Departamento</label>
                        <div class="text-sm text-black-500">{{ $order->department }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-black-400 mb-1">Ciudad</label>
                        <div class="text-sm text-black-500">{{ $order->city }}</div>
                    </div>
                </div>
            </div>

            <!-- Productos del Pedido -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Productos ({{ $order->items->count() }})</h3>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center p-4 bg-white-100 rounded-lg">
                            @if($item->product && $item->product->mainImage)
                                <img src="{{ $item->product->mainImage->image_url }}" 
                                     alt="{{ $item->product_name }}" 
                                     class="w-12 h-12 rounded-lg object-cover mr-4">
                            @else
                                <div class="w-12 h-12 bg-white-200 rounded-lg flex items-center justify-center mr-4">
                                    <x-solar-gallery-outline class="w-6 h-6 text-black-300" />
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-black-500">{{ $item->product_name }}</div>
                                @if($item->variant_details)
                                    <div class="text-xs text-black-300">{{ $item->formatted_variants }}</div>
                                @endif
                                @if($item->product)
                                    <a href="{{ route('tenant.admin.products.show', [$store->slug, $item->product->id]) }}" 
                                       class="text-xs text-primary-200 hover:text-primary-300">
                                        Ver producto
                                    </a>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-black-500">Cant: {{ $item->quantity }}</div>
                                <div class="text-sm text-black-400">${{ number_format($item->unit_price, 0, ',', '.') }} c/u</div>
                                <div class="text-sm font-semibold text-black-500">${{ number_format($item->item_total, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Historial de Estados -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Historial de Estados</h3>
                <div class="space-y-4">
                    @foreach($order->statusHistory as $history)
                        <div class="flex">
                            <div class="flex-shrink-0 w-8 h-8 {{ $history->status_color }} rounded-full flex items-center justify-center mr-3">
                                <x-solar-check-circle-outline class="w-4 h-4 text-white-50" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-black-500">{{ $history->status_change }}</div>
                                @if($history->notes)
                                    <div class="text-xs text-black-400 mt-1">{{ $history->notes }}</div>
                                @endif
                                <div class="text-xs text-black-300">
                                    Por: {{ $history->changed_by }} • {{ $history->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Resumen del Pedido -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Resumen</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-black-400">Subtotal:</span>
                        <span class="text-black-500">${{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($order->shipping_cost > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-black-400">Envío:</span>
                            <span class="text-black-500">${{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($order->coupon_discount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-black-400">Descuento:</span>
                            <span class="text-success-300">-${{ number_format($order->coupon_discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-white-200 pt-3">
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold text-black-500">Total:</span>
                            <span class="text-lg font-semibold text-primary-200">${{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Entrega -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Entrega</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-black-400 mb-1">Tipo</label>
                        <div class="flex items-center text-sm">
                            @if($order->delivery_type === 'domicilio')
                                <x-solar-delivery-outline class="w-4 h-4 text-primary-200 mr-2" />
                                <span class="text-black-500">Domicilio</span>
                            @else
                                <x-solar-shop-outline class="w-4 h-4 text-secondary-200 mr-2" />
                                <span class="text-black-500">Pickup en Tienda</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($order->delivery_type === 'domicilio')
                        <div>
                            <label class="block text-xs text-black-400 mb-1">Costo de Envío</label>
                            <div class="text-sm text-black-500">
                                @if($order->shipping_cost > 0)
                                    ${{ number_format($order->shipping_cost, 0, ',', '.') }}
                                @else
                                    <span class="text-success-300">Gratis</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información de Pago -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Pago</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-black-400 mb-1">Método</label>
                        <div class="flex items-center text-sm">
                            @if($order->payment_method === 'transferencia' || $order->payment_method === 'bank_transfer')
                                <x-solar-card-outline class="w-4 h-4 text-info-200 mr-2" />
                                <span class="text-black-500">Transferencia Bancaria</span>
                            @elseif($order->payment_method === 'contra_entrega')
                                <x-solar-wallet-money-outline class="w-4 h-4 text-warning-300 mr-2" />
                                <span class="text-black-500">Pago Contra Entrega</span>
                            @elseif($order->payment_method === 'efectivo' || $order->payment_method === 'cash')
                                <x-solar-dollar-outline class="w-4 h-4 text-success-300 mr-2" />
                                <span class="text-black-500">Efectivo</span>
                            @else
                                <x-solar-card-2-outline class="w-4 h-4 text-primary-300 mr-2" />
                                <span class="text-black-500">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                            @endif
                        </div>
                    </div>

                    @if($order->payment_proof_path)
                        <div>
                            <label class="block text-xs text-black-400 mb-1">Comprobante</label>
                            <div class="flex items-center">
                                <x-solar-document-outline class="w-4 h-4 text-success-300 mr-2" />
                                <a href="{{ route('tenant.admin.orders.download-payment-proof', [$store->slug, $order->id]) }}" 
                                   class="text-sm text-primary-200 hover:text-primary-300">
                                    Descargar comprobante
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cambiar Estado -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Cambiar Estado</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-black-400 mb-2">Nuevo Estado</label>
                        <select x-model="newStatus" class="w-full px-3 py-2 border border-white-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Seleccionar estado</option>
                            @foreach(['pending' => 'Pendiente', 'confirmed' => 'Confirmado', 'preparing' => 'Preparando', 'shipped' => 'Enviado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado'] as $status => $label)
                                @if($status !== $order->status)
                                    <option value="{{ $status }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-black-400 mb-2">Notas (Opcional)</label>
                        <textarea x-model="statusNotes" rows="3" 
                                  class="w-full px-3 py-2 border border-white-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200"
                                  placeholder="Observaciones sobre el cambio de estado..."></textarea>
                    </div>
                    <button type="button" @click="showStatusModal = true" :disabled="!newStatus" 
                            class="w-full btn-primary text-sm">
                        <x-solar-refresh-outline class="w-4 h-4 mr-2" />
                        Actualizar Estado
                    </button>
                </div>
            </div>

            <!-- Notas del Pedido -->
            @if($order->notes)
                <div class="bg-white-50 rounded-lg p-6">
                    <h3 class="text-sm font-medium text-black-400 mb-4">Notas</h3>
                    <div class="text-sm text-black-500">{{ $order->notes }}</div>
                </div>
            @endif

            <!-- Acciones Rápidas -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-400 mb-4">Acciones</h3>
                <div class="space-y-3">
                    @if($order->canBeEdited())
                        <a href="{{ route('tenant.admin.orders.edit', [$store->slug, $order->id]) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-primary-200 text-primary-200 rounded-lg hover:bg-primary-50 transition-colors text-sm">
                            <x-solar-pen-new-square-outline class="w-4 h-4 mr-2" />
                            Editar Pedido
                        </a>
                    @endif
                    
                    <form action="{{ route('tenant.admin.orders.duplicate', [$store->slug, $order->id]) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" onclick="return confirm('¿Duplicar este pedido?')"
                                class="w-full flex items-center justify-center px-4 py-2 border border-secondary-200 text-secondary-200 rounded-lg hover:bg-secondary-50 transition-colors text-sm">
                            <x-solar-copy-outline class="w-4 h-4 mr-2" />
                            Duplicar Pedido
                        </button>
                    </form>
                    
                    <button onclick="printOrder()" 
                            class="w-full flex items-center justify-center px-4 py-2 border border-info-200 text-info-200 rounded-lg hover:bg-info-50 transition-colors text-sm">
                        <x-solar-printer-outline class="w-4 h-4 mr-2" />
                        Imprimir Pedido
                    </button>
                    
                    <a href="https://wa.me/57{{ preg_replace('/\D/', '', $order->customer_phone) }}?text=Hola {{ $order->customer_name }}, te contactamos sobre tu pedido #{{ $order->order_number }}" 
                       target="_blank" 
                       class="w-full flex items-center justify-center px-4 py-2 bg-success-200 text-white-50 rounded-lg hover:bg-success-300 transition-colors text-sm">
                        <x-solar-chat-round-dots-outline class="w-4 h-4 mr-2" />
                        Contactar por WhatsApp
                    </a>
                    
                    @if($order->status === 'pending')
                        <button @click="showDeleteModal = true" 
                                class="w-full flex items-center justify-center px-4 py-2 bg-error-200 text-white-50 rounded-lg hover:bg-error-300 transition-colors text-sm">
                            <x-solar-trash-bin-trash-outline class="w-4 h-4 mr-2" />
                            Eliminar Pedido
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación de cambio de estado --}}
    <div x-show="showStatusModal" 
         x-transition.opacity
         x-cloak
         @click.away="showStatusModal = false"
         @keydown.escape.window="showStatusModal = false"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black-400 opacity-75" @click="showStatusModal = false"></div>
            <div class="relative bg-white-50 rounded-lg max-w-md w-full p-6" @click.stop>
                <!-- Botón X para cerrar -->
                <button @click="showStatusModal = false" 
                        class="absolute top-4 right-4 text-black-300 hover:text-black-500">
                    <x-solar-close-circle-outline class="w-5 h-5" />
                </button>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-solar-refresh-outline class="w-6 h-6 text-primary-300" />
                    </div>
                    <h3 class="text-lg font-semibold text-black-500 mb-2">Confirmar cambio de estado</h3>
                    <p class="text-black-300 mb-2">
                        ¿Cambiar el estado del pedido <strong>#{{ $order->order_number }}</strong> a:
                    </p>
                    <p class="text-sm text-primary-300 font-medium mb-6" x-text="getStatusLabel(newStatus)"></p>
                    <div class="flex gap-3 justify-center">
                        <button @click="showStatusModal = false" 
                                class="btn-secondary">
                            Cancelar
                        </button>
                        <button @click="confirmStatusUpdate()" 
                                class="btn-primary">
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación de eliminación --}}
    <div x-show="showDeleteModal" 
         x-transition.opacity
         x-cloak
         @click.away="showDeleteModal = false"
         @keydown.escape.window="showDeleteModal = false"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black-400 opacity-75" @click="showDeleteModal = false"></div>
            <div class="relative bg-white-50 rounded-lg max-w-md w-full p-6" @click.stop>
                <!-- Botón X para cerrar -->
                <button @click="showDeleteModal = false" 
                        class="absolute top-4 right-4 text-black-300 hover:text-black-500">
                    <x-solar-close-circle-outline class="w-5 h-5" />
                </button>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-error-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-solar-danger-triangle-outline class="w-6 h-6 text-error-200" />
                    </div>
                    <h3 class="text-lg font-semibold text-black-500 mb-2">Confirmar eliminación</h3>
                    <p class="text-black-300 mb-6">
                        ¿Estás seguro de que deseas eliminar el pedido <strong>#{{ $order->order_number }}</strong>? 
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="flex gap-3 justify-center">
                        <button @click="showDeleteModal = false" 
                                class="btn-secondary">
                            Cancelar
                        </button>
                        <button @click="confirmDelete()" 
                                class="btn-primary bg-error-200 hover:bg-error-100">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderDetail', () => ({
        newStatus: '',
        statusNotes: '',
        showStatusModal: false,
        showDeleteModal: false,

        init() {
            // Forzar cierre de modales al inicio
            this.showDeleteModal = false;
            this.showStatusModal = false;
        },

        getStatusLabel(status) {
            const labels = {
                'pending': 'Pendiente',
                'confirmed': 'Confirmado', 
                'preparing': 'Preparando',
                'shipped': 'Enviado',
                'delivered': 'Entregado',
                'cancelled': 'Cancelado'
            };
            return labels[status] || status;
        },

        confirmStatusUpdate() {
            if (!this.newStatus) return;

            fetch(`{{ route('tenant.admin.orders.update-status', [$store->slug, $order->id]) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: this.newStatus,
                    notes: this.statusNotes
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
        },

        confirmDelete() {
            fetch(`{{ route('tenant.admin.orders.destroy', [$store->slug, $order->id]) }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `{{ route('tenant.admin.orders.index', $store->slug) }}`;
                } else {
                    alert(data.message || 'Error al eliminar pedido');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }
    }));
});

function printOrder() {
    window.print();
}
</script>
@endpush
@endsection
</x-tenant-admin-layout>