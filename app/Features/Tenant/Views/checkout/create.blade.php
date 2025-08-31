@extends('frontend.layouts.app')

@push('meta')
    <meta name="description" content="Finaliza tu compra en {{ $store->name }} - Proceso de checkout seguro y rápido">
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="px-4 py-4 sm:py-6">
    <!-- Checkout Container -->
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-black-500 mb-2">Finalizar Compra</h1>
            <p class="text-black-300">Completa tu información para procesar tu pedido</p>
        </div>

        <!-- Main Checkout Layout - Single Column -->
        <div class="max-w-2xl mx-auto">
            <!-- Checkout Form -->
            <div class="bg-accent-50 rounded-xl p-6 border border-accent-200">
                <div class="space-y-6">
            
            <!-- CARD 1: DATOS PERSONALES -->
            <div id="card-step1" class="bg-accent-50 rounded-xl p-6 border border-accent-200 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-primary-300 text-accent-50 rounded-full flex items-center justify-center text-sm font-bold mr-3">1</div>
                    <h3 class="text-lg font-semibold text-black-500">Datos Personales</h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-black-500 mb-2">Nombre Completo *</label>
                        <input 
                            type="text" 
                            id="customer_name" 
                            name="customer_name" 
                            class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors"
                            placeholder="Tu nombre completo"
                        >
                        <div id="name_error" class="hidden mt-1 text-sm text-error-300"></div>
                    </div>
                    
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-black-500 mb-2">Número de Celular *</label>
                        <input 
                            type="tel" 
                            id="customer_phone" 
                            name="customer_phone" 
                            class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors"
                            placeholder="3001234567"
                        >
                        <div id="phone_error" class="hidden mt-1 text-sm text-error-300"></div>
                    </div>
                </div>
                
                <button 
                    type="button" 
                    id="btn-continue-step1" 
                    class="w-full bg-primary-300 hover:bg-primary-200 text-accent-50 py-2 px-4 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                    disabled
                >
                    Continuar al Paso 2
                </button>
            </div>

            <!-- CARD 2: MÉTODO DE ENVÍO -->
            <div id="card-step2" class="bg-accent-50 rounded-xl p-6 border border-accent-200 shadow-sm hidden">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-primary-300 text-accent-50 rounded-full flex items-center justify-center text-sm font-bold mr-3">2</div>
                    <h3 class="text-lg font-semibold text-black-500">Método de Envío</h3>
                </div>
                
                <div class="space-y-3 mb-6">
                    <!-- Los métodos de envío se cargan dinámicamente aquí -->
                    <div class="text-center py-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-300 mx-auto"></div>
                        <p class="text-sm text-black-300 mt-2">Cargando métodos de envío...</p>
                    </div>
                </div>
                
                <!-- Address Fields (only for domicilio) -->
                <div id="address-fields" class="hidden space-y-4 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="department" class="block text-sm font-medium text-black-500 mb-2">Departamento *</label>
                            <select 
                                id="department" 
                                name="department" 
                                class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors"
                            >
                                <option value="">Selecciona tu departamento</option>
                                @foreach(['Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 'Caldas', 'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare', 'Huila', 'La Guajira', 'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 'Risaralda', 'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 'Valle del Cauca', 'Vaupés', 'Vichada'] as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="city" class="block text-sm font-medium text-black-500 mb-2">Ciudad *</label>
                            <input 
                                type="text" 
                                id="city" 
                                name="city" 
                                class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors"
                                placeholder="Escribe tu ciudad"
                            >
                        </div>
                    </div>
                    
                    <div>
                        <label for="customer_address" class="block text-sm font-medium text-black-500 mb-2">Dirección Completa *</label>
                        <textarea 
                            id="customer_address" 
                            name="customer_address" 
                            rows="2"
                            class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors resize-none"
                            placeholder="Calle, carrera, número, apartamento, referencias..."
                        ></textarea>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button 
                        type="button" 
                        id="btn-back-step2" 
                        class="px-4 py-2 bg-accent-100 hover:bg-accent-200 text-black-400 rounded-lg font-medium transition-colors border border-accent-200 text-sm"
                    >
                        Volver
                    </button>
                    <button 
                        type="button" 
                        id="btn-continue-step2" 
                        class="flex-1 bg-primary-300 hover:bg-primary-200 text-accent-50 py-2 px-4 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                        disabled
                    >
                        Continuar al Paso 3
                    </button>
                </div>
            </div>

            <!-- CARD 3: MÉTODO DE PAGO -->
            <div id="card-step3" class="bg-accent-50 rounded-xl p-6 border border-accent-200 shadow-sm hidden">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-primary-300 text-accent-50 rounded-full flex items-center justify-center text-sm font-bold mr-3">3</div>
                    <h3 class="text-lg font-semibold text-black-500">Método de Pago</h3>
                </div>
                
                <div class="space-y-4 mb-6">
                    <!-- Los métodos de pago se cargan dinámicamente aquí -->
                    <div class="text-center py-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-300 mx-auto"></div>
                        <p class="text-sm text-black-300 mt-2">Cargando métodos de pago...</p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button 
                        type="button" 
                        id="btn-back-step3" 
                        class="px-4 py-2 bg-accent-100 hover:bg-accent-200 text-black-400 rounded-lg font-medium transition-colors border border-accent-200 text-sm"
                    >
                        Volver
                    </button>
                    <button 
                        type="button" 
                        id="btn-submit-order" 
                        class="flex-1 bg-success-300 hover:bg-success-200 text-accent-50 py-2 px-4 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                        disabled
                    >
                        Finalizar Pedido
                    </button>
                </div>
            </div>
            
            <!-- Resumen integrado al final -->
            <div class="border-t border-accent-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-black-500 mb-4">Resumen del Pedido</h3>
                
                <!-- Products List -->
                <div id="order-products" class="space-y-3 mb-4">
                    <div class="text-center py-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-300 mx-auto"></div>
                        <p class="text-sm text-black-300 mt-2">Cargando productos...</p>
                    </div>
                </div>
                
                <!-- Coupon Section -->
                <div class="border-t border-accent-200 pt-4 mb-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">🎟️</span>
                        <h4 class="text-sm font-medium text-black-500">Cupón de Descuento</h4>
                    </div>
                    
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="coupon_code" 
                            name="coupon_code" 
                            class="flex-1 px-3 py-2 border border-accent-200 rounded-lg focus:ring-1 focus:ring-primary-300 focus:border-transparent transition-colors text-sm"
                            placeholder="Código de cupón"
                            maxlength="50"
                        >
                        <button 
                            type="button" 
                            id="apply-coupon-btn" 
                            class="px-4 py-2 bg-primary-300 hover:bg-primary-200 text-accent-50 rounded-lg text-sm font-medium transition-colors"
                        >
                            Aplicar
                        </button>
                    </div>
                    
                    <div id="coupon_error" class="hidden mt-1 text-xs text-error-300"></div>
                </div>
                
                <!-- Totals -->
                <div class="border-t border-accent-200 pt-4 space-y-3">
                    <!-- Subtotal productos -->
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-400">Subtotal productos:</span>
                        <span class="text-sm font-medium text-black-500" id="summary-subtotal">$0</span>
                    </div>
                    
                    <!-- Envío -->
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-400">Costo de envío:</span>
                        <span class="text-sm font-medium text-black-500" id="summary-shipping">Calculando...</span>
                    </div>
                    
                    <!-- Descuento cupón (oculto por defecto) -->
                    <div id="coupon-discount-row" class="hidden flex justify-between items-center">
                        <span class="text-sm text-success-400">Descuento cupón:</span>
                        <span class="text-sm font-medium text-success-400" id="summary-discount">-$0</span>
                    </div>
                    
                    <!-- Línea separadora -->
                    <div class="border-t border-accent-200 pt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-black-500">Total a pagar:</span>
                            <span class="text-lg font-bold text-primary-300" id="summary-total">$0</span>
                        </div>
                    </div>
                    
                    <!-- Desglose productos -->
                    <div id="product-breakdown" class="hidden border-t border-accent-200 pt-3">
                        <div class="text-xs text-black-300 mb-2">
                            <span class="font-medium">Cantidad total de productos:</span>
                            <span id="total-quantity">0</span>
                        </div>
                        <div class="text-xs text-black-300">
                            <span class="font-medium">Productos únicos:</span>
                            <span id="unique-products">0</span>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
console.log('🟢 CHECKOUT SIMPLE - INICIANDO');

// Variables globales MUY simples
let currentStep = 1;

// Variables globales para métodos
let shippingMethods = [];
let paymentMethods = [];

// DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM LISTO');
    
    initCheckout();
    loadOrderSummary();
    loadShippingMethods();
    loadPaymentMethods();
});

// Inicializar todo
function initCheckout() {
    console.log('⚡ Inicializando...');
    
    // Mostrar solo paso 1
    showStep(1);
    
    // Event listeners básicos
    setupEvents();
}

// Configurar eventos básicos
function setupEvents() {
    console.log('🔧 Configurando eventos...');
    
    // Paso 1 - validación nombre/teléfono
    const nameInput = document.getElementById('customer_name');
    const phoneInput = document.getElementById('customer_phone');
    
    nameInput.addEventListener('input', checkStep1);
    phoneInput.addEventListener('input', checkStep1);
    
    // Botones navegación
    document.getElementById('btn-continue-step1').addEventListener('click', () => {
        console.log('🔄 Click continuar paso 1');
        showStep(2);
    });
    
    document.getElementById('btn-back-step2').addEventListener('click', () => {
        console.log('🔄 Click volver paso 2');
        showStep(1);
    });
    
    document.getElementById('btn-continue-step2').addEventListener('click', () => {
        console.log('🔄 Click continuar paso 2');
        showStep(3);
    });
    
    document.getElementById('btn-back-step3').addEventListener('click', () => {
        console.log('🔄 Click volver paso 3');
        showStep(2);
    });
    
    // Address fields validation (los delivery type listeners se configuran en renderShippingMethods)
    document.getElementById('department').addEventListener('change', enableStep2Button);
    document.getElementById('city').addEventListener('input', enableStep2Button);
    document.getElementById('customer_address').addEventListener('input', enableStep2Button);
    
    // Payment method listeners se configuran en renderPaymentMethods()
    // Cash amount listeners se configuran en renderPaymentMethods()
    
    // Submit order button
    document.getElementById('btn-submit-order').addEventListener('click', submitOrder);
}

// Mostrar paso específico
function showStep(step) {
    console.log('📍 Mostrando paso:', step);
    
    // Ocultar todos
    document.getElementById('card-step1').classList.add('hidden');
    document.getElementById('card-step2').classList.add('hidden');
    document.getElementById('card-step3').classList.add('hidden');
    
    // Mostrar actual
    document.getElementById(`card-step${step}`).classList.remove('hidden');
    
    currentStep = step;
}

// Validar paso 1 (nombre + teléfono)
function checkStep1() {
    const name = document.getElementById('customer_name').value.trim();
    const phone = document.getElementById('customer_phone').value.trim();
    
    console.log('📝 Checkeando:', { name, phone });
    
    const nameOk = name.length >= 2;
    const phoneOk = /^3[0-9]{9}$/.test(phone);
    const valid = nameOk && phoneOk;
    
    console.log('✅ Validación:', { nameOk, phoneOk, valid });
    
    const btn = document.getElementById('btn-continue-step1');
    btn.disabled = !valid;
    
    if (valid) {
        console.log('🎉 Paso 1 VÁLIDO - botón habilitado');
    } else {
        console.log('❌ Paso 1 INVÁLIDO - botón deshabilitado');
    }
}

// Habilitar botón paso 2
function enableStep2Button() {
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
    const btn = document.getElementById('btn-continue-step2');
    
    if (!deliveryType) {
        btn.disabled = true;
        console.log('❌ Paso 2 deshabilitado - sin delivery type');
        return;
    }
    
    if (deliveryType.value === 'pickup') {
        btn.disabled = false;
        console.log('✅ Paso 2 habilitado - pickup');
        return;
    }
    
    // Para domicilio, validar campos de dirección
    const department = document.getElementById('department').value.trim();
    const city = document.getElementById('city').value.trim();
    const address = document.getElementById('customer_address').value.trim();
    
    const isValid = department && city.length >= 2 && address.length >= 10;
    
    btn.disabled = !isValid;
    
    if (isValid) {
        console.log('✅ Paso 2 habilitado - domicilio válido');
        calculateShippingCost(department, city);
    } else {
        console.log('❌ Paso 2 deshabilitado - domicilio incompleto');
    }
}

// Manejar cambio de método de pago
function handlePaymentMethodChange(paymentMethod, methodId) {
    // Ocultar todos los campos de métodos
    document.querySelectorAll('[id^="cash-fields-"], [id^="transfer-fields-"]').forEach(field => {
        field.classList.add('hidden');
    });
    
    // Mostrar campos relevantes
    if (paymentMethod === 'efectivo') {
        const cashFields = document.getElementById(`cash-fields-${methodId}`);
        if (cashFields) {
            cashFields.classList.remove('hidden');
        }
    } else if (paymentMethod === 'transferencia') {
        const transferFields = document.getElementById(`transfer-fields-${methodId}`);
        if (transferFields) {
            transferFields.classList.remove('hidden');
        }
    }
    
    console.log('💳 Método de pago cambiado:', { paymentMethod, methodId });
}

// Manejar cambio de monto en efectivo
function handleCashAmountChange(e) {
    const cashAmount = parseFloat(e.target.value) || 0;
    const changeDisplay = document.getElementById('change-display');
    const changeAmount = document.getElementById('change-amount');
    const currentTotal = getCurrentSubtotal() + getCurrentShipping() - getCurrentDiscount();
    
    if (cashAmount >= currentTotal && cashAmount > 0) {
        const change = cashAmount - currentTotal;
        changeAmount.textContent = `$${formatPrice(change)}`;
        changeDisplay.classList.remove('hidden');
    } else {
        changeDisplay.classList.add('hidden');
    }
    
    enableStep3Button();
    console.log('💰 Cambio calculado para:', { cashAmount, currentTotal });
}

// Habilitar botón paso 3
function enableStep3Button() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    const btn = document.getElementById('btn-submit-order');
    
    if (!paymentMethod) {
        btn.disabled = true;
        console.log('❌ Finalizar pedido deshabilitado - sin método');
        return;
    }
    
    if (paymentMethod.value === 'efectivo') {
        const cashAmount = parseFloat(document.getElementById('cash_amount').value) || 0;
        const currentTotal = getCurrentSubtotal() + getCurrentShipping() - getCurrentDiscount();
        const isValid = cashAmount >= currentTotal && cashAmount > 0;
        
        btn.disabled = !isValid;
        
        if (isValid) {
            console.log('✅ Finalizar pedido habilitado - efectivo válido');
        } else {
            console.log('❌ Finalizar pedido deshabilitado - efectivo insuficiente');
        }
    } else {
        // Transferencia siempre es válida
        btn.disabled = false;
        console.log('✅ Finalizar pedido habilitado - transferencia');
    }
    
    // Actualizar texto del botón con el total
    updateSubmitButtonText();
}

// Actualizar texto del botón de finalizar con el total
function updateSubmitButtonText() {
    const btn = document.getElementById('btn-submit-order');
    const currentTotal = getCurrentSubtotal() + getCurrentShipping() - getCurrentDiscount();
    
    if (currentTotal > 0) {
        btn.innerHTML = `Valor a pagar $${formatPrice(currentTotal)}`;
    } else {
        btn.innerHTML = 'Finalizar Pedido';
    }
}

// Copiar al portapapeles
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        // Cambiar temporalmente el texto del botón
        const button = element.nextElementSibling;
        const originalText = button.textContent;
        button.textContent = '¡Copiado!';
        button.classList.add('bg-success-200', 'text-success-400');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-success-200', 'text-success-400');
        }, 2000);
        
        console.log('📋 Copiado al portapapeles:', text);
    }).catch(err => {
        console.error('Error copiando:', err);
        alert('No se pudo copiar. Copia manualmente: ' + text);
    });
}

// Cargar resumen de orden
async function loadOrderSummary() {
    console.log('📋 Cargando resumen de orden...');
    
    try {
        const response = await fetch('{{ route("tenant.cart.get", $store->slug) }}');
        const data = await response.json();
        
        if (data.success && data.items.length > 0) {
            displayOrderProducts(data.items);
            updateOrderTotals(data.total, 0, 0); // subtotal, shipping, discount
        } else {
            // Redirigir si no hay productos
            window.location.href = '{{ route("tenant.cart.index", $store->slug) }}';
        }
    } catch (error) {
        console.error('Error cargando carrito:', error);
        document.getElementById('order-products').innerHTML = `
            <div class="text-center py-4">
                <p class="text-sm text-error-300">Error al cargar productos</p>
            </div>
        `;
    }
}

// Mostrar productos en el resumen
function displayOrderProducts(items) {
    console.log('🛒 Datos del carrito recibidos:', items);
    
    const container = document.getElementById('order-products');
    
    let html = '';
    let totalQuantity = 0;
    
    items.forEach((item, index) => {
        console.log(`📦 Producto ${index + 1}:`, {
            name: item.product?.name,
            rawPrice: item.price,
            rawQuantity: item.quantity,
            variant: item.variant_details
        });
        
        // Asegurar que los precios sean números válidos
        const basePrice = parseFloat(item.price) || 0;
        const variantModifier = parseFloat(item.variant_details?.precio_modificador) || 0;
        const quantity = parseInt(item.quantity) || 0;
        
        console.log(`💰 Producto ${index + 1} calculado:`, {
            basePrice,
            variantModifier,
            quantity,
            willAdd: quantity
        });
        
        const unitPrice = basePrice + variantModifier;
        const totalPrice = unitPrice * quantity;
        totalQuantity += quantity;
        
        html += `
            <div class="flex items-center gap-3 py-2 border-b border-accent-200 last:border-b-0">
                <img src="${item.product.image_url || '{{ asset("assets/images/placeholder-product.svg") }}'}" 
                     alt="${item.product.name}" 
                     class="w-12 h-12 object-cover rounded-lg">
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-black-500 truncate">${item.product.name}</h4>
                    ${item.variant_display ? `<p class="text-xs text-black-300">${item.variant_display}</p>` : ''}
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-black-400">Cantidad: ${item.quantity}</span>
                        <span class="text-sm font-semibold text-black-500">$${formatPrice(totalPrice)}</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Actualizar desglose de productos
    console.log('📊 TOTAL FINAL calculado:', {
        totalQuantity: totalQuantity,
        uniqueProducts: items.length,
        typeof_totalQuantity: typeof totalQuantity
    });
    
    document.getElementById('total-quantity').textContent = totalQuantity;
    document.getElementById('unique-products').textContent = items.length;
    document.getElementById('product-breakdown').classList.remove('hidden');
    
    console.log('✅ Productos cargados y mostrados en DOM');
}

// Actualizar totales
function updateOrderTotals(subtotal, shipping, discount) {
    document.getElementById('summary-subtotal').textContent = `$${formatPrice(subtotal)}`;
    document.getElementById('summary-shipping').textContent = shipping > 0 ? `$${formatPrice(shipping)}` : 'GRATIS';
    
    // Mostrar/ocultar descuento de cupón
    const discountRow = document.getElementById('coupon-discount-row');
    if (discount > 0) {
        discountRow.classList.remove('hidden');
        document.getElementById('summary-discount').textContent = `-$${formatPrice(discount)}`;
    } else {
        discountRow.classList.add('hidden');
    }
    
    const total = subtotal + shipping - discount;
    document.getElementById('summary-total').textContent = `$${formatPrice(total)}`;
    
    // Actualizar validación del paso 3 si está en efectivo
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (paymentMethod && paymentMethod.value === 'efectivo') {
        handleCashAmountChange({ target: document.getElementById('cash_amount') });
    }
    
    // Actualizar texto del botón
    updateSubmitButtonText();
    
    console.log('💰 Totales actualizados:', { subtotal, shipping, discount, total });
}

// Calcular costo de envío usando API real
async function calculateShippingCost(department, city) {
    console.log('💰 Calculando envío REAL para:', { department, city });
    
    const shippingDisplay = document.getElementById('shipping-cost-display');
    shippingDisplay.classList.remove('hidden');
    shippingDisplay.textContent = 'Calculando...';
    
    try {
        const subtotal = getCurrentSubtotal();
        
        const response = await fetch('{{ route("tenant.checkout.shipping-cost", $store->slug) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                department: department,
                city: city,
                subtotal: subtotal
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const costText = data.cost > 0 ? `${data.formatted_cost}` : 'GRATIS';
            shippingDisplay.textContent = costText;
            
            // Mostrar información adicional si hay envío gratis
            if (data.has_free_shipping && data.free_shipping_message) {
                const additionalInfo = document.createElement('p');
                additionalInfo.className = 'text-xs text-success-400 mt-1';
                additionalInfo.textContent = data.free_shipping_message;
                shippingDisplay.appendChild(additionalInfo);
            }
            
            // Guardar información de la zona para el formulario
            window.selectedShippingZone = {
                zone_id: data.zone_id,
                zone_name: data.zone_name,
                cost: data.cost,
                estimated_time: data.estimated_time
            };
            
            updateOrderTotals(subtotal, data.cost, getCurrentDiscount());
            
            console.log('✅ Costo de envío calculado:', {
                zone: data.zone_name,
                cost: data.cost,
                formatted: data.formatted_cost,
                free_shipping: data.has_free_shipping
            });
            
        } else {
            shippingDisplay.textContent = 'No disponible';
            console.error('Error en respuesta:', data.message);
        }
        
    } catch (error) {
        console.error('Error calculando envío:', error);
        shippingDisplay.textContent = 'Error al calcular';
    }
}

// Obtener subtotal actual (helper)
function getCurrentSubtotal() {
    const subtotalText = document.getElementById('summary-subtotal').textContent;
    return parseInt(subtotalText.replace(/\D/g, '')) || 0;
}

// Obtener descuento actual (helper)
function getCurrentDiscount() {
    const discountElement = document.getElementById('summary-discount');
    if (!discountElement || discountElement.closest('.hidden')) return 0;
    const discountText = discountElement.textContent;
    return parseInt(discountText.replace(/\D/g, '')) || 0;
}

// Obtener envío actual (helper)
function getCurrentShipping() {
    const shippingText = document.getElementById('summary-shipping').textContent;
    if (shippingText.includes('GRATIS')) return 0;
    return parseInt(shippingText.replace(/\D/g, '')) || 0;
}

// Cargar métodos de envío disponibles
async function loadShippingMethods() {
    console.log('🚚 Cargando métodos de envío...');
    
    try {
        const response = await fetch('{{ route("tenant.checkout.shipping-methods", $store->slug) }}');
        const data = await response.json();
        
        if (data.success) {
            shippingMethods = data.methods;
            renderShippingMethods();
            console.log('✅ Métodos de envío cargados:', shippingMethods.length);
        } else {
            console.error('Error cargando métodos:', data.message);
        }
    } catch (error) {
        console.error('Error en loadShippingMethods:', error);
    }
}

// Renderizar métodos de envío en el HTML
function renderShippingMethods() {
    const container = document.querySelector('#card-step2 .space-y-3');
    
    let html = '';
    
    shippingMethods.forEach(method => {
        if (method.type === 'pickup') {
            html += `
                <div class="border border-accent-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="radio" 
                            name="delivery_type" 
                            value="pickup"
                            data-method-id="${method.id}"
                            class="mr-3 text-primary-300 focus:ring-primary-300 w-4 h-4"
                        >
                        <div class="flex items-center justify-center w-12 h-12 bg-primary-50 rounded-lg mr-3">
                            <span class="text-2xl">${method.icon}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-black-500">${method.name}</h4>
                                    <p class="text-sm text-black-300">${method.instructions || 'Recoge tu pedido en nuestra tienda física'}</p>
                                    ${method.pickup_address ? `<p class="text-xs text-black-400 mt-1">📍 ${method.pickup_address}</p>` : ''}
                                </div>
                                <span class="bg-success-100 text-success-400 text-xs px-3 py-1 rounded-full font-medium">GRATIS</span>
                            </div>
                        </div>
                    </label>
                </div>
            `;
        } else if (method.type === 'domicilio') {
            const zoneInfo = method.zones?.length > 0 ? 
                `Desde ${method.zones[0].formatted_cost} - ${method.zones.map(z => z.name).join(', ')}` : 
                'Zonas disponibles';
                
            html += `
                <div class="border border-accent-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="radio" 
                            name="delivery_type" 
                            value="domicilio"
                            data-method-id="${method.id}"
                            class="mr-3 text-primary-300 focus:ring-primary-300 w-4 h-4"
                        >
                        <div class="flex items-center justify-center w-12 h-12 bg-primary-50 rounded-lg mr-3">
                            <span class="text-2xl">${method.icon}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-black-500">${method.name}</h4>
                                    <p class="text-sm text-black-300">${method.instructions || 'Entregamos en tu dirección'}</p>
                                    <p class="text-xs text-black-400 mt-1">${zoneInfo}</p>
                                </div>
                                <div id="shipping-cost-display" class="hidden text-sm font-medium text-primary-300"></div>
                            </div>
                        </div>
                    </label>
                </div>
            `;
        }
    });
    
    container.innerHTML = html;
    
    // Re-configurar event listeners
    document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            console.log('📦 Delivery type:', e.target.value);
            if (e.target.value === 'domicilio') {
                document.getElementById('address-fields').classList.remove('hidden');
            } else {
                document.getElementById('address-fields').classList.add('hidden');
            }
            enableStep2Button();
        });
    });
}

// Cargar métodos de pago disponibles
async function loadPaymentMethods() {
    console.log('💳 Cargando métodos de pago...');
    
    const url = '{{ route("tenant.checkout.payment-methods", $store->slug) }}';
    console.log('🌐 URL de métodos de pago:', url);
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            paymentMethods = data.methods;
            renderPaymentMethods();
            console.log('✅ Métodos de pago cargados:', paymentMethods.length);
        } else {
            console.error('Error cargando métodos:', data.message);
        }
    } catch (error) {
        console.error('Error en loadPaymentMethods:', error);
    }
}

// Renderizar métodos de pago en el HTML
function renderPaymentMethods() {
    const container = document.querySelector('#card-step3 .space-y-4');
    
    let html = '';
    
    paymentMethods.forEach(method => {
        if (method.type === 'cash') {
            html += `
                <div class="border border-accent-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="radio" 
                            name="payment_method" 
                            value="efectivo"
                            data-method-id="${method.id}"
                            class="mr-3 text-primary-300 focus:ring-primary-300 w-4 h-4"
                        >
                        <div class="flex items-center justify-center w-12 h-12 bg-primary-50 rounded-lg mr-3">
                            <span class="text-2xl">${method.icon}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-black-500">${method.name}</h4>
                            <p class="text-sm text-black-300">${method.instructions || 'Paga en efectivo al recibir tu pedido'}</p>
                        </div>
                    </label>
                    
                    <!-- Efectivo Fields -->
                    <div id="cash-fields-${method.id}" class="hidden mt-4 pl-16">
                        <div class="bg-accent-100 border border-accent-200 rounded-lg p-4">
                            <label for="cash_amount" class="block text-sm font-medium text-black-500 mb-2">
                                ¿Con cuánto vas a pagar? *
                            </label>
                            <input 
                                type="number" 
                                id="cash_amount" 
                                name="cash_amount" 
                                class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:ring-1 focus:ring-primary-300 focus:border-transparent transition-colors text-sm"
                                placeholder="Ejemplo: 50000"
                                min="0"
                            >
                            <div id="change-display" class="hidden mt-3 p-3 bg-info-50 border border-info-200 rounded-lg">
                                <p class="text-sm text-info-400">
                                    <span class="font-medium">Tu cambio será:</span> 
                                    <span id="change-amount" class="font-semibold"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else if (method.type === 'bank_transfer') {
            const accounts = method.bank_accounts || [];
            
            html += `
                <div class="border border-accent-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="radio" 
                            name="payment_method" 
                            value="transferencia"
                            data-method-id="${method.id}"
                            class="mr-3 text-primary-300 focus:ring-primary-300 w-4 h-4"
                        >
                        <div class="flex items-center justify-center w-12 h-12 bg-primary-50 rounded-lg mr-3">
                            <span class="text-2xl">${method.icon}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-black-500">${method.name}</h4>
                            <p class="text-sm text-black-300">${method.instructions || 'Transfiere a nuestras cuentas bancarias'}</p>
                        </div>
                    </label>
                    
                    <!-- Transferencia Fields -->
                    <div id="transfer-fields-${method.id}" class="hidden mt-4 pl-16 space-y-4">
                        ${accounts.map(account => `
                            <div class="bg-gradient-to-r from-info-50 to-primary-50 border border-info-200 rounded-xl p-5 shadow-sm">
                                <h4 class="font-bold text-info-400 mb-4 flex items-center text-sm">
                                    <span class="text-lg mr-2">🏦</span>
                                    ${account.bank_name}
                                </h4>
                                <div class="space-y-3">
                                    <!-- Tipo de Cuenta -->
                                    <div class="bg-accent-50 rounded-lg p-3 border border-accent-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-black-400 font-medium">Tipo de Cuenta:</span>
                                            <span class="text-sm font-bold text-black-500 bg-primary-100 px-2 py-1 rounded-full">${account.account_type || 'Cuenta Corriente'}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Número de Cuenta -->
                                    <div class="bg-accent-50 rounded-lg p-3 border border-accent-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-black-400 font-medium">Número de Cuenta:</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-mono font-bold text-primary-300 bg-primary-100 px-3 py-1 rounded-lg tracking-wider" id="account-${account.id}">${account.account_number}</span>
                                                <button type="button" onclick="copyToClipboard('account-${account.id}')" class="text-xs bg-primary-200 hover:bg-primary-300 text-primary-400 px-3 py-1 rounded-lg font-semibold transition-colors">
                                                    📋 Copiar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Titular -->
                                    <div class="bg-accent-50 rounded-lg p-3 border border-accent-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-black-400 font-medium">Titular:</span>
                                            <span class="text-sm font-bold text-black-500">${account.account_holder}</span>
                                        </div>
                                    </div>
                                    
                                    ${account.document_number ? `
                                        <!-- Documento -->
                                        <div class="bg-accent-50 rounded-lg p-3 border border-accent-200">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-black-400 font-medium">Documento:</span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-mono font-bold text-info-400 bg-info-100 px-3 py-1 rounded-lg" id="document-${account.id}">${account.document_number}</span>
                                                    <button type="button" onclick="copyToClipboard('document-${account.id}')" class="text-xs bg-info-200 hover:bg-info-300 text-info-400 px-3 py-1 rounded-lg font-semibold transition-colors">
                                                        📋 Copiar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('')}
                        
                        <div class="bg-accent-100 border border-accent-200 rounded-lg p-4">
                            <label for="payment_proof" class="block text-sm font-medium text-black-500 mb-2">
                                Comprobante de Pago ${method.require_proof ? '*' : '(Opcional)'}
                            </label>
                            <input 
                                type="file" 
                                id="payment_proof" 
                                name="payment_proof" 
                                accept=".jpg,.jpeg,.png,.pdf"
                                ${method.require_proof ? 'required' : ''}
                                class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:ring-1 focus:ring-primary-300 focus:border-transparent transition-colors text-sm"
                            >
                            <p class="text-xs text-black-300 mt-1">
                                ${method.require_proof ? 
                                    'Sube tu comprobante de transferencia (JPG, PNG o PDF) - OBLIGATORIO' : 
                                    'Sube tu comprobante de transferencia (JPG, PNG o PDF) - OPCIONAL'
                                }
                            </p>
                        </div>
                    </div>
                </div>
            `;
        }
    });
    
    container.innerHTML = html;
    
    // Re-configurar event listeners
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            console.log('💳 Payment method:', e.target.value);
            handlePaymentMethodChange(e.target.value, e.target.dataset.methodId);
            enableStep3Button();
        });
    });
    
    // Re-configurar cash amount listener
    const cashAmountInput = document.getElementById('cash_amount');
    if (cashAmountInput) {
        cashAmountInput.addEventListener('input', handleCashAmountChange);
    }
}

// Enviar pedido
async function submitOrder() {
    console.log('🚀 Enviando pedido...');
    
    const btn = document.getElementById('btn-submit-order');
    const originalText = btn.innerHTML;
    
    // Deshabilitar botón y mostrar loading
    btn.disabled = true;
    btn.innerHTML = '⏳ Procesando...';
    
    try {
        // Recopilar todos los datos del formulario
        const formData = new FormData();
        
        // Datos personales
        formData.append('customer_name', document.getElementById('customer_name').value);
        formData.append('customer_phone', document.getElementById('customer_phone').value);
        
        // Datos de envío
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
        if (deliveryType) {
            formData.append('delivery_type', deliveryType.value);
            
            if (deliveryType.value === 'domicilio') {
                formData.append('department', document.getElementById('department').value);
                formData.append('city', document.getElementById('city').value);
                formData.append('customer_address', document.getElementById('customer_address').value);
                
                // Agregar shipping_zone_id si está disponible
                if (window.selectedShippingZone && window.selectedShippingZone.zone_id) {
                    formData.append('shipping_zone_id', window.selectedShippingZone.zone_id);
                }
            }
        }
        
        // Datos de pago
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (paymentMethod) {
            formData.append('payment_method', paymentMethod.value);
            formData.append('payment_method_id', paymentMethod.dataset.methodId);
            
            // Si es efectivo, agregar monto
            if (paymentMethod.value === 'efectivo') {
                const cashAmount = document.getElementById('cash_amount').value;
                if (cashAmount) {
                    formData.append('cash_amount', cashAmount);
                }
            }
            
            // Si hay comprobante de pago
            const paymentProof = document.getElementById('payment_proof');
            if (paymentProof && paymentProof.files.length > 0) {
                formData.append('payment_proof', paymentProof.files[0]);
            }
        }
        
        // Cupón si existe
        const couponCode = document.getElementById('coupon_code');
        if (couponCode && couponCode.value) {
            formData.append('coupon_code', couponCode.value);
        }
        
        console.log('📝 Datos a enviar recopilados');
        
        // Debug: Mostrar datos que se van a enviar
        console.log('🚀 FormData contents:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}:`, value);
        }
        
        // Enviar al servidor
        const checkoutUrl = '{{ route("tenant.checkout.store", $store->slug) }}';
        console.log('🌐 URL de checkout:', checkoutUrl);
        
        const response = await fetch(checkoutUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('📡 Response status:', response.status);
        console.log('📡 Response headers:', response.headers);
        
        const responseText = await response.text();
        console.log('📡 Raw response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('📡 Parsed JSON:', data);
        } catch (e) {
            console.error('❌ Error parsing JSON:', e);
            console.error('❌ Response was not JSON:', responseText);
            alert('Error: El servidor no devolvió una respuesta JSON válida');
            return;
        }
        
        if (data.success) {
            console.log('✅ Pedido creado exitosamente:', data);
            // Redirigir a página de éxito con el ID del pedido
            window.location.href = `{{ route("tenant.checkout.success", $store->slug) }}?order=${data.order.id}`;
        } else {
            console.error('❌ Error del servidor:', data);
            
            // Manejar errores de validación específicos
            if (data.errors) {
                console.error('❌ Errores de validación:', data.errors);
                let errorMessage = 'Errores de validación:\n';
                for (const field in data.errors) {
                    errorMessage += `• ${field}: ${data.errors[field].join(', ')}\n`;
                }
                alert(errorMessage);
            } else {
                alert('Error al procesar el pedido: ' + (data.message || 'Error desconocido'));
            }
        }
        
    } catch (error) {
        console.error('❌ Error enviando pedido:', error);
        alert('Error de conexión. Por favor intenta nuevamente.');
    } finally {
        // Restaurar botón
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Formatear precio
function formatPrice(price) {
    // Validar que el precio sea un número válido
    const numPrice = parseFloat(price);
    if (isNaN(numPrice) || !isFinite(numPrice)) {
        console.warn('⚠️ Precio inválido:', price);
        return '0';
    }
    
    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numPrice);
}
</script>
@endpush
