@extends('frontend.layouts.app')

@push('scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-6">
    <!-- Header dinámico según método de envío -->
    <div class="text-center">
        <div class="w-20 h-20 bg-gradient-to-r from-success-300 to-primary-300 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
            @if(($order->delivery_type ?? '') === 'domicilio')
                <span class="text-3xl">🚚</span>
            @else
                <span class="text-3xl">🏪</span>
            @endif
        </div>
        
        @if(($order->delivery_type ?? '') === 'domicilio')
            <h1 class="text-2xl font-bold text-black-500 mb-2">¡Tu pedido viene en camino!</h1>
            <p class="text-black-300">Lo enviaremos a tu domicilio lo antes posible</p>
        @else
            <h1 class="text-2xl font-bold text-black-500 mb-2">¡Tu pedido está listo!</h1>
            <p class="text-black-300">Podrás recogerlo en nuestra tienda pronto</p>
        @endif
    </div>

    <!-- Código de pedido destacado -->
    <div class="bg-gradient-to-r from-primary-50 to-info-50 rounded-xl p-6 border border-primary-200 shadow-sm">
        <div class="text-center">
            <h2 class="text-sm font-medium text-primary-400 mb-2">CÓDIGO DE PEDIDO</h2>
            <div class="bg-white-50 rounded-lg p-4 border-2 border-dashed border-primary-300">
                <p class="text-3xl font-bold text-primary-300 tracking-wider" id="order-code">{{ $order->order_number ?? 'N/A' }}</p>
                <button onclick="copyOrderCode()" class="mt-2 text-xs bg-primary-200 hover:bg-primary-300 text-primary-400 px-3 py-1 rounded-full transition-colors">
                    📋 Copiar código
                </button>
            </div>
        </div>
    </div>

    <!-- Estado del pedido en tiempo real -->
    <div class="bg-white-50 rounded-xl p-6 border border-white-200 shadow-sm">
        <h3 class="text-lg font-semibold text-black-500 mb-4 flex items-center">
            <span class="mr-2">📊</span>
            Estado del Pedido
        </h3>
        <div id="order-status-tracker" class="space-y-4">
            <!-- Los estados se cargan dinámicamente -->
            <div class="text-center py-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-300 mx-auto"></div>
                <p class="text-sm text-black-300 mt-2">Cargando estado...</p>
            </div>
        </div>
    </div>

    <!-- Detalles del pedido -->
    <div class="bg-white-50 rounded-xl p-6 border border-white-200 shadow-sm">
        <h3 class="text-lg font-semibold text-black-500 mb-4 flex items-center">
            <span class="mr-2">📋</span>
            Detalles del Pedido
        </h3>
        
        <div class="space-y-4">
            <!-- Información del cliente -->
            <div class="bg-white-100 rounded-lg p-4">
                <h4 class="font-medium text-black-500 mb-3 text-sm">👤 Información del Cliente</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-black-400">Nombre:</span>
                        <span class="font-medium text-black-500">{{ $order->customer_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-black-400">Teléfono:</span>
                        <span class="font-medium text-black-500">{{ $order->customer_phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Información de entrega -->
            <div class="bg-white-100 rounded-lg p-4">
                <h4 class="font-medium text-black-500 mb-3 text-sm">
                    @if(($order->delivery_type ?? '') === 'domicilio')
                        🚚 Información de Envío
                    @else
                        🏪 Información de Recogida
                    @endif
                </h4>
                <div class="space-y-2 text-sm">
                    @if(($order->delivery_type ?? '') === 'domicilio')
                        <div class="flex justify-between">
                            <span class="text-black-400">Dirección:</span>
                            <span class="font-medium text-black-500 text-right">{{ $order->customer_address ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black-400">Ciudad:</span>
                            <span class="font-medium text-black-500">{{ $order->city ?? 'N/A' }}</span>
                        </div>
                    @else
                        <div class="flex justify-between">
                            <span class="text-black-400">Dirección tienda:</span>
                            <span class="font-medium text-black-500 text-right">{{ $store->address ?? 'Ver en Google Maps' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black-400">Horario:</span>
                            <span class="font-medium text-black-500">{{ $store->schedule ?? 'Lun-Vie 9am-6pm' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resumen de pago -->
            <div class="bg-white-100 rounded-lg p-4">
                <h4 class="font-medium text-black-500 mb-3 text-sm">💳 Resumen de Pago</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-black-400">Método:</span>
                        <span class="font-medium text-black-500">
                            @if(($order->payment_method ?? '') === 'efectivo' || ($order->payment_method ?? '') === 'cash')
                                💵 Efectivo
                            @elseif(($order->payment_method ?? '') === 'transferencia' || ($order->payment_method ?? '') === 'bank_transfer')
                                🏦 Transferencia
                            @elseif(($order->payment_method ?? '') === 'contra_entrega')
                                🚚 Contra Entrega
                            @elseif(($order->payment_method ?? '') === 'card_terminal')
                                💳 Terminal de Pago
                            @else
                                💳 {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}
                            @endif
                        </span>
                    </div>
                    @if((($order->payment_method ?? '') === 'efectivo' || ($order->payment_method ?? '') === 'cash') && isset($order->cash_amount))
                        <div class="flex justify-between">
                            <span class="text-black-400">Pagas con:</span>
                            <span class="font-medium text-black-500">${{ number_format($order->cash_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black-400">Tu cambio:</span>
                            <span class="font-medium text-success-300">${{ number_format($order->cash_amount - $order->total, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-white-200 pt-2">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-black-500">Total a pagar:</span>
                            <span class="text-lg font-bold text-primary-300">${{ number_format($order->total ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones de compartir -->
    <div class="bg-white-50 rounded-xl p-6 border border-white-200 shadow-sm">
        <h3 class="text-lg font-semibold text-black-500 mb-4 flex items-center">
            <span class="mr-2">📱</span>
            Compartir
        </h3>
        
        <div class="grid grid-cols-1 gap-3">
            <!-- Compartir con el negocio -->
            <button onclick="shareWithBusiness()" class="flex items-center justify-center w-full bg-success-300 hover:bg-success-200 text-white-50 py-3 px-4 rounded-lg font-medium transition-colors">
                <span class="mr-2">📞</span>
                Contactar al negocio
            </button>
            
            <!-- Compartir con un amigo -->
            <button onclick="shareWithFriend()" class="flex items-center justify-center w-full bg-info-300 hover:bg-info-200 text-white-50 py-3 px-4 rounded-lg font-medium transition-colors">
                <span class="mr-2">👥</span>
                Compartir con un amigo
            </button>
        </div>
    </div>

    <!-- Acciones principales -->
    <div class="grid grid-cols-1 gap-3">
        <button onclick="refreshOrderStatus()" class="flex items-center justify-center w-full bg-primary-300 hover:bg-primary-200 text-white-50 py-3 px-4 rounded-lg font-semibold transition-colors">
            <span class="mr-2">🔄</span>
            Actualizar estado
        </button>
        
        <a href="{{ route('tenant.home', $store->slug) }}" 
           class="flex items-center justify-center w-full bg-white-200 hover:bg-white-300 text-black-500 py-3 px-4 rounded-lg font-medium transition-colors">
            <span class="mr-2">🛍️</span>
            Continuar comprando
        </a>
    </div>
</div>

@push('scripts')
<script>
const ORDER_ID = {{ $order->id ?? 'null' }};
const STORE_SLUG = '{{ $store->slug ?? '' }}';
const STORE_PHONE = '{{ $store->phone ?? '' }}';
const CUSTOMER_PHONE = '{{ $order->customer_phone ?? '' }}';

// Cargar estado del pedido al iniciar
document.addEventListener('DOMContentLoaded', function() {
    loadOrderStatus();
    
    // Actualizar cada 30 segundos
    setInterval(loadOrderStatus, 30000);
});

// Cargar estado del pedido
async function loadOrderStatus() {
    if (!ORDER_ID) return;
    
    try {
        const response = await fetch(`/${STORE_SLUG}/checkout/api/order-status?id=${ORDER_ID}`);
        const data = await response.json();
        
        if (data.success) {
            renderOrderStatus(data.order);
        }
    } catch (error) {
        console.error('Error cargando estado:', error);
    }
}

// Mapear estados de inglés (BD) a español (frontend)
function mapOrderStatus(dbStatus) {
    const statusMap = {
        'pending': 'pendiente',
        'confirmed': 'confirmado', 
        'preparing': 'en_preparacion',
        'ready': 'listo',
        'shipped': 'en_camino',
        'delivered': 'entregado'
    };
    return statusMap[dbStatus] || dbStatus;
}

// Renderizar estado del pedido
function renderOrderStatus(order) {
    const container = document.getElementById('order-status-tracker');
    
    // Mapear el estado de la BD al español
    const currentStatus = mapOrderStatus(order.status);
    
    console.log('🔄 Estado actualizado:', {
        original: order.status,
        mapped: currentStatus,
        order: order
    });
    
    const statusSteps = [
        { key: 'pendiente', label: 'Pedido Recibido', icon: '📝', description: 'Tu pedido ha sido registrado' },
        { key: 'confirmado', label: 'Confirmado', icon: '✅', description: 'Hemos confirmado tu pedido' },
        { key: 'en_preparacion', label: 'En Preparación', icon: '👨‍🍳', description: 'Estamos preparando tu pedido' },
        { key: 'listo', label: 'Listo', icon: '🎉', description: 'Tu pedido está listo' },
        { key: 'en_camino', label: 'En Camino', icon: '🚚', description: 'En route a tu dirección' },
        { key: 'entregado', label: 'Entregado', icon: '✨', description: '¡Disfruta tu pedido!' }
    ];
    
    // Encontrar el estado actual para mostrar el indicador principal
    const currentStep = statusSteps.find(step => step.key === currentStatus);
    
    let html = `
        <!-- Indicador principal del estado actual -->
        <div class="bg-gradient-to-r from-primary-50 to-primary-100 rounded-lg p-4 mb-4 border border-primary-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-300 rounded-full flex items-center justify-center text-white-50 text-lg mr-3">
                        ${currentStep ? currentStep.icon : '📋'}
                    </div>
                    <div>
                        <h4 class="font-semibold text-primary-500">Estado Actual</h4>
                        <p class="text-primary-400 text-sm">${currentStep ? currentStep.label : currentStatus}</p>
                    </div>
                </div>
                <div class="text-xs text-primary-300">
                    Actualizado: ${new Date(order.updated_at).toLocaleString('es-ES')}
                </div>
            </div>
        </div>
        
        <!-- Tracker de progreso -->
        <div class="space-y-3">
    `;
    
    statusSteps.forEach((step, index) => {
        const isActive = currentStatus === step.key;
        const isCompleted = getStatusOrder(currentStatus) > getStatusOrder(step.key);
        
        html += `
            <div class="flex items-center ${isActive ? 'bg-primary-50 border border-primary-200 rounded-lg p-3' : 'p-3'}">
                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 ${
                    isCompleted ? 'bg-success-300 text-white-50' :
                    isActive ? 'bg-primary-300 text-white-50' :
                    'bg-white-200 text-black-400'
                }">
                    ${isCompleted ? '✅' : step.icon}
                </div>
                <div class="flex-1">
                    <h4 class="font-medium text-sm ${isActive ? 'text-primary-400' : isCompleted ? 'text-success-400' : 'text-black-400'}">${step.label}</h4>
                    <p class="text-xs ${isActive ? 'text-primary-300' : 'text-black-300'}">${step.description}</p>
                </div>
                ${isActive ? '<div class="animate-pulse w-2 h-2 bg-primary-300 rounded-full"></div>' : ''}
            </div>
        `;
    });
    
    html += '</div></div>'; // Cerrar tanto el space-y-3 como el contenedor principal
    container.innerHTML = html;
}

// Obtener orden numérico del estado
function getStatusOrder(status) {
    const order = {
        'pendiente': 1,
        'confirmado': 2,
        'en_preparacion': 3,
        'listo': 4,
        'en_camino': 5,
        'entregado': 6
    };
    return order[status] || 0;
}

// Copiar código de pedido
function copyOrderCode() {
    const code = document.getElementById('order-code').textContent;
    
    navigator.clipboard.writeText(code).then(() => {
        alert('📋 Código copiado: ' + code);
    }).catch(() => {
        alert('Código del pedido: ' + code);
    });
}

// Compartir con el negocio
function shareWithBusiness() {
    const message = `¡Hola! Mi pedido ${document.getElementById('order-code').textContent} está confirmado. ¿Podrían darme más información?`;
    const whatsappUrl = `https://wa.me/${STORE_PHONE}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// Compartir con un amigo
function shareWithFriend() {
    const orderCode = document.getElementById('order-code').textContent;
    const message = `¡Acabo de hacer un pedido en {{ $store->name ?? 'esta tienda' }}! 🛍️\n\nCódigo: ${orderCode}\n\nRevisa sus productos: ${window.location.origin}/{{ $store->slug ?? '' }}`;
    
    if (CUSTOMER_PHONE) {
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    } else {
        // Copiar al portapapeles si no hay teléfono
        navigator.clipboard.writeText(message).then(() => {
            alert('📋 Mensaje copiado. Compártelo donde quieras!');
        }).catch(() => {
            alert('Mensaje para compartir:\n\n' + message);
        });
    }
}

// Actualizar estado manualmente
function refreshOrderStatus() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<span class="mr-2">⏳</span>Actualizando...';
    button.disabled = true;
    
    loadOrderStatus().then(() => {
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 1000);
    });
}
</script>
@endpush
@endsection
