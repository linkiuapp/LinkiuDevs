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
            <div class="bg-white-50 rounded-xl p-6 border border-white-200">
                    <!-- Checkout Accordion Form -->
                    <form id="checkout-form" class="space-y-4">
                        @csrf
                        
                        <!-- Step 1: Personal Data -->
                        <div id="step-1" class="checkout-step active">
                            <div class="flex items-center mb-3">
                                <div class="w-5 h-5 bg-primary-300 text-white-50 rounded-full flex items-center justify-center text-xs font-medium mr-2">
                                    1
                                </div>
                                <h3 class="text-sm font-medium text-black-500">Datos Personales</h3>
                            </div>
                            <div class="pl-7 space-y-3 checkout-step-content">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Nombre Completo -->
                                    <div>
                                        <label for="customer_name" class="block text-xs font-medium text-black-500 mb-1">
                                            Nombre Completo *
                                        </label>
                                        <input 
                                            type="text" 
                                            id="customer_name" 
                                            name="customer_name" 
                                            class="w-full px-3 py-2 border border-white-200 rounded-md focus:ring-1 focus:ring-primary-300 focus:border-transparent transition-colors text-sm"
                                            placeholder="Tu nombre completo"
                                            required
                                        >
                                        <div id="customer_name_error" class="hidden mt-1 text-xs text-error-300"></div>
                                    </div>

                                    <!-- Número de Celular -->
                                    <div>
                                        <label for="customer_phone" class="block text-xs font-medium text-black-500 mb-1">
                                            Celular *
                                        </label>
                                        <input 
                                            type="tel" 
                                            id="customer_phone" 
                                            name="customer_phone" 
                                            class="w-full px-3 py-2 border border-white-200 rounded-md focus:ring-1 focus:ring-primary-300 focus:border-transparent transition-colors text-sm"
                                            placeholder="3001234567"
                                            required
                                        >
                                        <div id="customer_phone_error" class="hidden mt-1 text-xs text-error-300"></div>
                                    </div>
                                </div>

                                <!-- Continue Button -->
                                <div class="pt-2">
                                    <button 
                                        type="button" 
                                        id="continue-to-shipping" 
                                        class="w-full bg-primary-300 hover:bg-primary-200 text-white-50 py-2 px-4 rounded-md font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                        disabled
                                    >
                                        Continuar al Envío
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Shipping Method -->
                        <div id="step-2" class="checkout-step">
                            <div class="flex items-center mb-3">
                                <div class="w-5 h-5 bg-white-200 text-black-300 rounded-full flex items-center justify-center text-xs font-medium mr-2">
                                    2
                                </div>
                                <h3 class="text-sm font-medium text-black-300">Método de Envío</h3>
                            </div>
                            <div class="pl-7 space-y-3 checkout-step-content hidden">
                                <!-- Shipping Method Options -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Domicilio Option -->
                                    <label class="border border-white-200 rounded-lg p-3 hover:border-primary-300 transition-colors cursor-pointer">
                                        <input 
                                            type="radio" 
                                            name="delivery_type" 
                                            value="domicilio" 
                                            id="delivery_domicilio"
                                            class="sr-only"
                                        >
                                        <div class="flex items-center">
                                            <span class="text-lg mr-2">🚚</span>
                                            <div>
                                                <div class="font-medium text-black-500 text-sm">Envío a Domicilio</div>
                                                <div class="text-xs text-black-300">Recibe en tu dirección</div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Pickup Option -->
                                    <label class="border border-white-200 rounded-lg p-3 hover:border-primary-300 transition-colors cursor-pointer">
                                        <input 
                                            type="radio" 
                                            name="delivery_type" 
                                            value="pickup" 
                                            id="delivery_pickup"
                                            class="sr-only"
                                        >
                                        <div class="flex items-center">
                                            <span class="text-lg mr-2">🏪</span>
                                            <div>
                                                <div class="font-medium text-black-500 text-sm flex items-center">
                                                    Recoger en Tienda
                                                    <span class="ml-1 bg-success-100 text-success-300 text-xs px-1 py-0.5 rounded">GRATIS</span>
                                                </div>
                                                <div class="text-xs text-black-300">Recoge en la tienda</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Domicilio Fields (Hidden by default) -->
                                <div id="domicilio-fields" class="hidden space-y-4 mt-6 p-4 bg-white-100 rounded-lg">
                                    <h4 class="font-semibold text-black-500 mb-3">Información de Entrega</h4>
                                    
                                    <!-- Departamento -->
                                    <div>
                                        <label for="department" class="block text-sm font-medium text-black-500 mb-2">
                                            Departamento *
                                        </label>
                                        <select 
                                            id="department" 
                                            name="department" 
                                            class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors"
                                        >
                                            <option value="">Selecciona tu departamento</option>
                                            @foreach(['Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 'Caldas', 'Caquetá', 'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare', 'Huila', 'La Guajira', 'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo', 'Quindío', 'Risaralda', 'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima', 'Valle del Cauca', 'Vaupés', 'Vichada'] as $dept)
                                                <option value="{{ $dept }}">{{ $dept }}</option>
                                            @endforeach
                                        </select>
                                        <div id="department_error" class="hidden mt-1 text-sm text-error-300"></div>
                                    </div>

                                    <!-- Ciudad -->
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-black-500 mb-2">
                                            Ciudad *
                                        </label>
                                        <select 
                                            id="city" 
                                            name="city" 
                                            class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors"
                                            disabled
                                        >
                                            <option value="">Primero selecciona el departamento</option>
                                        </select>
                                        <div id="city_error" class="hidden mt-1 text-sm text-error-300"></div>
                                    </div>

                                    <!-- Dirección -->
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-black-500 mb-2">
                                            Dirección Completa *
                                        </label>
                                        <textarea 
                                            id="address" 
                                            name="address" 
                                            rows="3"
                                            class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors resize-none"
                                            placeholder="Ej: Calle 123 #45-67, Barrio Centro, Referencias adicionales..."
                                        ></textarea>
                                        <div id="address_error" class="hidden mt-1 text-sm text-error-300"></div>
                                    </div>

                                    <!-- Shipping Cost Display -->
                                    <div id="shipping-cost-display" class="hidden p-3 bg-primary-50 border border-primary-200 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <span class="text-black-500 font-medium">Costo de envío:</span>
                                            <span id="shipping-cost-amount" class="text-primary-300 font-bold">Calculando...</span>
                                        </div>
                                        <div id="estimated-time" class="text-sm text-black-400 mt-1"></div>
                                    </div>
                                </div>

                                <!-- Pickup Info (Hidden by default) -->
                                <div id="pickup-info" class="hidden mt-6 p-4 bg-success-50 border border-success-200 rounded-lg">
                                    <h4 class="font-semibold text-success-300 mb-3">📍 Información de Recogida</h4>
                                    <div class="space-y-2 text-sm">
                                        <p class="text-black-500"><strong>Dirección:</strong> <span id="pickup-address">Cargando...</span></p>
                                        <p class="text-black-500"><strong>Horario:</strong> <span id="pickup-schedule">Lunes a Viernes 8:00 AM - 6:00 PM</span></p>
                                        <p class="text-success-300 font-medium">✅ Envío GRATIS - Ahorra en costos de entrega</p>
                                    </div>
                                </div>

                                <!-- Continue Button -->
                                <div class="pt-2">
                                    <button 
                                        type="button" 
                                        id="continue-to-payment" 
                                        class="w-full bg-primary-300 hover:bg-primary-200 text-white-50 py-2 px-4 rounded-md font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                        disabled
                                    >
                                        Continuar al Pago
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Payment Method -->
                        <div id="step-3" class="checkout-step">
                            <div class="flex items-center mb-3">
                                <div class="w-5 h-5 bg-white-200 text-black-300 rounded-full flex items-center justify-center text-xs font-medium mr-2">
                                    3
                                </div>
                                <h3 class="text-sm font-medium text-black-300">Método de Pago</h3>
                            </div>
                            <div class="pl-7 space-y-3 checkout-step-content hidden">
                                <!-- Payment Method Options -->
                                <div class="space-y-4">
                                    <!-- Efectivo Option -->
                                    <div class="border border-white-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                                        <label class="flex items-start cursor-pointer">
                                            <input 
                                                type="radio" 
                                                name="payment_method" 
                                                value="efectivo" 
                                                id="payment_efectivo"
                                                class="mt-1 mr-3 text-primary-300 focus:ring-primary-300"
                                            >
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="text-2xl mr-2">💵</span>
                                                    <span class="font-semibold text-black-500">Pago en Efectivo</span>
                                                </div>
                                                <p class="text-sm text-black-300">Paga en efectivo al recibir tu pedido</p>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Transferencia Option -->
                                    <div class="border border-white-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                                        <label class="flex items-start cursor-pointer">
                                            <input 
                                                type="radio" 
                                                name="payment_method" 
                                                value="transferencia" 
                                                id="payment_transferencia"
                                                class="mt-1 mr-3 text-primary-300 focus:ring-primary-300"
                                            >
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="text-2xl mr-2">🏦</span>
                                                    <span class="font-semibold text-black-500">Transferencia Bancaria</span>
                                                </div>
                                                <p class="text-sm text-black-300">Transfiere a nuestra cuenta bancaria</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Efectivo Fields (Hidden by default) -->
                                <div id="efectivo-fields" class="hidden space-y-4 mt-6 p-4 bg-white-100 rounded-lg">
                                    <h4 class="font-semibold text-black-500 mb-3">💵 Pago en Efectivo</h4>
                                    
                                    <div>
                                        <label for="cash_amount" class="block text-sm font-medium text-black-500 mb-2">
                                            ¿Con cuánto vas a pagar? *
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-3 text-black-400">$</span>
                                            <input 
                                                type="number" 
                                                id="cash_amount" 
                                                name="cash_amount" 
                                                class="w-full pl-8 pr-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors"
                                                placeholder="0"
                                                min="0"
                                            >
                                        </div>
                                        <div id="cash_amount_error" class="hidden mt-1 text-sm text-error-300"></div>
                                        
                                        <!-- Change calculation -->
                                        <div id="change-display" class="hidden mt-3 p-3 bg-success-50 border border-success-200 rounded-lg">
                                            <div class="flex justify-between items-center">
                                                <span class="text-black-500 font-medium">Tu cambio será:</span>
                                                <span id="change-amount" class="text-success-300 font-bold">$0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transferencia Fields (Hidden by default) -->
                                <div id="transferencia-fields" class="hidden space-y-4 mt-6 p-4 bg-white-100 rounded-lg">
                                    <h4 class="font-semibold text-black-500 mb-3">🏦 Información Bancaria</h4>
                                    
                                    <!-- Bank Info -->
                                    <div class="space-y-3">
                                        <div class="bg-white-50 border border-white-200 rounded-lg p-4">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium text-black-500">Banco:</span>
                                                <span class="text-black-400">Bancolombia</span>
                                            </div>
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="font-medium text-black-500">Tipo de cuenta:</span>
                                                <span class="text-black-400">Ahorros</span>
                                            </div>
                                            <div class="flex justify-between items-center mb-3">
                                                <span class="font-medium text-black-500">Número de cuenta:</span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-black-400 font-mono">1234567890</span>
                                                    <button 
                                                        type="button" 
                                                        id="copy-account" 
                                                        class="bg-primary-300 hover:bg-primary-200 text-white-50 px-3 py-1 rounded text-xs font-medium transition-colors"
                                                    >
                                                        Copiar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-black-500">Titular:</span>
                                                <span class="text-black-400">{{ $store->name ?? 'Tienda' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Proof Upload -->
                                    <div>
                                        <label for="payment_proof" class="block text-sm font-medium text-black-500 mb-2">
                                            Comprobante de Pago (Opcional)
                                        </label>
                                        <div class="border-2 border-dashed border-white-200 rounded-lg p-6 text-center hover:border-primary-300 transition-colors">
                                            <input 
                                                type="file" 
                                                id="payment_proof" 
                                                name="payment_proof" 
                                                accept=".jpg,.jpeg,.png,.pdf"
                                                class="hidden"
                                            >
                                            <label for="payment_proof" class="cursor-pointer">
                                                <div class="text-4xl mb-2">📎</div>
                                                <p class="text-black-500 font-medium mb-1">Subir comprobante</p>
                                                <p class="text-sm text-black-300">JPG, PNG o PDF (máx. 5MB)</p>
                                            </label>
                                        </div>
                                        <div id="payment_proof_preview" class="hidden mt-3"></div>
                                        <div id="payment_proof_error" class="hidden mt-1 text-sm text-error-300"></div>
                                    </div>
                                </div>

                                <!-- Order Notes -->
                                <div class="mt-6">
                                    <label for="notes" class="block text-sm font-medium text-black-500 mb-2">
                                        Notas del Pedido (Opcional)
                                    </label>
                                    <textarea 
                                        id="notes" 
                                        name="notes" 
                                        rows="3"
                                        class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-300 focus:border-transparent transition-colors resize-none"
                                        placeholder="Instrucciones especiales, referencias adicionales, etc..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-6 border-t border-white-200">
                            <button type="submit" id="submit-order" class="w-full bg-success-300 hover:bg-success-200 text-white-50 py-4 px-6 rounded-xl font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-base" disabled>
                                <span id="submit-button-text">Finalizar Pedido - Total: $<span id="submit-button-total">0</span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary (Right Column - 1/3 width on desktop, top on mobile) -->
            <div class="lg:col-span-1 order-1 lg:order-2">
                <div class="bg-white-50 rounded-xl p-4 sm:p-6 border border-white-200 lg:sticky lg:top-4">
                    <!-- Order Summary Component -->
                    <div id="order-summary">
                        <h3 class="text-lg font-semibold text-black-500 mb-4">Resumen del Pedido</h3>
                        
                        <!-- Products List -->
                        <div id="summary-products" class="space-y-3 mb-4">
                            @foreach($products as $item)
                                <div class="flex items-center gap-3 p-3 bg-white-100 rounded-lg">
                                    <div class="w-12 h-12 bg-white-200 rounded-lg overflow-hidden flex-shrink-0">
                                        @if($item['product']->main_image_url)
                                            <img src="{{ $item['product']->main_image_url }}" alt="{{ $item['product']->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-black-300">
                                                📦
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-black-500 text-sm truncate">{{ $item['product']->name }}</h4>
                                        @if($item['variant_display'])
                                            <p class="text-xs text-black-300">{{ $item['variant_display'] }}</p>
                                        @endif
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-xs text-black-400">Cantidad: {{ $item['quantity'] }}</span>
                                            <span class="font-semibold text-black-500 text-sm">${{ number_format($item['item_total'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Coupon Section -->
                        <div class="border-t border-white-200 pt-4 mb-4">
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    id="coupon-code" 
                                    placeholder="Código de cupón" 
                                    class="flex-1 px-3 py-2 border border-white-200 rounded-md focus:ring-1 focus:ring-primary-300 focus:border-transparent text-sm"
                                >
                                <button 
                                    type="button" 
                                    id="apply-coupon" 
                                    class="bg-primary-300 hover:bg-primary-200 text-white-50 px-4 py-2 rounded-md font-medium transition-colors text-sm"
                                >
                                    Aplicar
                                </button>
                            </div>
                            <div id="coupon-message" class="hidden mt-2 text-sm"></div>
                            <div id="applied-coupon" class="hidden mt-3 p-2 bg-success-50 border border-success-200 rounded-md">
                                <div class="flex justify-between items-center">
                                    <span class="text-success-300 font-medium text-sm">Cupón aplicado: <span id="coupon-name"></span></span>
                                    <button type="button" id="remove-coupon" class="text-success-300 hover:text-success-200 text-sm">Quitar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Totals Section -->
                        <div class="border-t border-white-200 pt-4 space-y-2">
                            <!-- Subtotal -->
                            <div class="flex justify-between items-center">
                                <span class="text-black-400">Subtotal:</span>
                                <span id="summary-subtotal" class="font-semibold text-black-500">${{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <!-- Shipping Cost -->
                            <div class="flex justify-between items-center">
                                <span class="text-black-400">Envío:</span>
                                <span id="summary-shipping" class="font-semibold text-black-500">Por calcular</span>
                            </div>

                            <!-- Discounts -->
                            <div id="summary-discounts" class="hidden">
                                <div class="flex justify-between items-center text-success-300">
                                    <span>Descuento:</span>
                                    <span id="summary-discount-amount">-$0</span>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t border-white-200 pt-2 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-black-500">Total:</span>
                                    <span id="summary-total" class="text-lg font-bold text-primary-300">${{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Free Shipping Message -->
                        <div id="free-shipping-message" class="hidden mt-4 p-3 bg-success-50 border border-success-200 rounded-lg">
                            <p class="text-success-300 text-sm font-medium text-center">
                                🎉 ¡Envío GRATIS!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Checkout Accordion Styles */
    .checkout-step {
        transition: all 0.3s ease;
    }
    
    .checkout-step.active .checkout-step-content {
        display: block;
    }
    
    .checkout-step.completed .step-number {
        background-color: #10b981;
        color: white;
    }
    
    .checkout-step.completed h3 {
        color: #374151;
    }
    
    .checkout-step:not(.active) .checkout-step-content {
        display: none;
    }
    
    /* Smooth transitions for step content */
    .checkout-step .checkout-step-content {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Order summary animations */
    #order-summary .animate-update {
        animation: highlight 0.5s ease-in-out;
    }
    
    @keyframes highlight {
        0% { background-color: transparent; }
        50% { background-color: #fef3c7; }
        100% { background-color: transparent; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
document.addEventListener('DOMContentLoaded', function() {
    // Checkout form management
    const checkoutForm = {
        currentStep: 1,
        formData: {},
        
        // Initialize checkout
        init() {
            this.loadSavedData();
            this.bindEvents();
            this.validateCurrentStep();
        },
        
        // Load saved data from localStorage
        loadSavedData() {
            const savedData = localStorage.getItem('linkiu_checkout_data');
            if (savedData) {
                try {
                    this.formData = JSON.parse(savedData);
                    this.populateFields();
                } catch (e) {
                    console.log('Error loading saved data:', e);
                }
            }
        },
        
        // Populate fields with saved data
        populateFields() {
            // Personal data
            if (this.formData.customer_name) {
                document.getElementById('customer_name').value = this.formData.customer_name;
            }
            if (this.formData.customer_phone) {
                document.getElementById('customer_phone').value = this.formData.customer_phone;
            }
            
            // Shipping data
            if (this.formData.delivery_type) {
                const radio = document.getElementById(`delivery_${this.formData.delivery_type}`);
                if (radio) {
                    radio.checked = true;
                    this.handleDeliveryTypeChange(this.formData.delivery_type);
                }
            }
            if (this.formData.department) {
                document.getElementById('department').value = this.formData.department;
                this.handleDepartmentChange(this.formData.department);
            }
            if (this.formData.city) {
                setTimeout(() => {
                    document.getElementById('city').value = this.formData.city;
                    this.handleCityChange(this.formData.city);
                }, 100);
            }
            if (this.formData.address) {
                document.getElementById('address').value = this.formData.address;
            }
            
            // Payment data
            if (this.formData.payment_method) {
                const document.
        
        // Save data to localStora= true;
   ge
        saveData() {
            localStorage.setItem('linkiu_checkout_data', JSON.stringify(this.formData));
        },
        s.f
        // Bind events
        bindEvents() {
            // Personal data validation
            document.getElementById('customer_name').addEventListener('input', (e) => {
            }
        },this.validateName(e.target.value);
                this.formData.customer_name = e.target.value;
                this.saveData();
            });
            
            document.getElementById('customer_phone').addEventListener('input', (e) => {
                this.validatePhone(e.target.value);
                this.formData.customer_phone = e.target.value;
                this.saveData();
            });
            
            // Continue buttons
            document.getElementById('continue-to-shipping').addEventListener('click', () => {
                this.nextStep();
            });
            
            // Shipping method events
            document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.handleDeliveryTypeChange(e.target.value);
                });
            });
            
            // Address fields events
            document.getElementById('department').addEventListener('change', (e) => {
                this.handleDepartmentChange(e.target.value);
            });
            
            document.getElementById('city').addEventListener('change', (e) => {
                this.calculateShippingCost();
            });
            
            document.getElementById('address').addEventListener('input', (e) => {
                this.formData.address = e.target.value;
                this.saveData();
            });
            
            document.getElementById('continue-to-payment').addEventListener('click', () => {
                this.nextStep();
            });
            
            // Payment method events
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.handlePaymentMethodChange(e.target.value);
                });
            });
            
            // Cash amount events
            document.getElementById('cash_amount').addEventListener('input', (e) => {
                this.handleCashAmountChange(e.target.value);
            });
            
            // Copy account button
            document.getElementById('copy-account').addEventListener('click', () => {
                this.copyAccountNumber();
            });
            
            // Shipping method selection
            document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.handleDeliveryTypeChange(e.target.value);
                    this.formData.delivery_type = e.target.value;
                    this.saveData();
                });
            });
            
            // Department selection
            document.getElementById('department').addEventListener('change', (e) => {
                this.handleDepartmentChange(e.target.value);
                this.formData.department = e.target.value;
                this.saveData();
            });
            
            // City selection
            document.getElementById('city').addEventListener('change', (e) => {
                this.handleCityChange(e.target.value);
                this.formData.city = e.target.value;
                this.saveData();
            });
            
            // Address input
            document.getElementById('address').addEventListener('input', (e) => {
                this.formData.address = e.target.value;
                this.saveData();
                this.validateCurrentStep();
            });
            
            // Payment method selection
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.handlePaymentMethodChange(e.target.value);
                    this.formData.payment_method = e.target.value;
                    this.saveData();
                });
            });
            
            // Cash amount input
            document.getElementById('cash_amount').addEventListener('input', (e) => {
                this.handleCashAmountChange(e.target.value);
                this.formData.cash_amount = e.target.value;
                this.saveData();
            });
            
            // Payment proof upload
            document.getElementById('payment_proof').addEventListener('change', (e) => {
                this.handlePaymentProofUpload(e.target.files[0]);
            });
            
            // Copy account number
            document.getElementById('copy-account').addEventListener('click', () => {
                this.copyAccountNumber();
            });
            
            // Notes input
            document.getElementById('notes').addEventListener('input', (e) => {
                this.formData.notes = e.target.value;
                this.saveData();
            });
            
            // Form submission
            document.getElementById('checkout-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitOrder();
            });
        },
        
        // Validate name (only letters and spaces)
        validateName(name) {
            const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
            const errorElement = document.getElementById('customer_name_error');
            const inputElement = document.getElementById('customer_name');
            
            if (!name.trim()) {
                this.showError(errorElement, inputElement, 'El nombre es obligatorio');
                return false;
            } else if (name.trim().length < 2) {
                this.showError(errorElement, inputElement, 'El nombre debe tener al menos 2 caracteres');
                return false;
            } else if (!nameRegex.test(name)) {
                this.showError(errorElement, inputElement, 'El nombre solo puede contener letras y espacios');
                return false;
            } else {
                this.hideError(errorElement, inputElement);
                return true;
            }
        },
        
        // Validate Colombian phone number
        validatePhone(phone) {
            const phoneRegex = /^3[0-9]{9}$/;
            const errorElement = document.getElementById('customer_phone_error');
            const inputElement = document.getElementById('customer_phone');
            
            if (!phone.trim()) {
                this.showError(errorElement, inputElement, 'El celular es obligatorio');
                return false;
            } else if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
                this.showError(errorElement, inputElement, 'Ingresa un número de celular válido (Ej: 3001234567)');
                return false;
            } else {
                this.hideError(errorElement, inputElement);
                return true;
            }
        },
        
        // Show error message
        showError(errorElement, inputElement, message) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            inputElement.classList.add('border-error-300', 'focus:ring-error-300');
            inputElement.classList.remove('border-white-200', 'focus:ring-primary-300');
        },
        
        // Hide error message
        hideError(errorElement, inputElement) {
            errorElement.classList.add('hidden');
            inputElement.classList.remove('border-error-300', 'focus:ring-error-300');
            inputElement.classList.add('border-white-200', 'focus:ring-primary-300');
        },
        
        // Handle delivery type change
        handleDeliveryTypeChange(type) {
            const domicilioFields = document.getElementById('domicilio-fields');
            const pickupInfo = document.getElementById('pickup-info');
            
            if (type === 'domicilio') {
                domicilioFields.classList.remove('hidden');
                pickupInfo.classList.add('hidden');
                this.loadPickupInfo();
            } else if (type === 'pickup') {
                domicilioFields.classList.add('hidden');
                pickupInfo.classList.remove('hidden');
                this.loadPickupInfo();
            }
            
            this.validateCurrentStep();
        },
        
        // Handle department change
        handleDepartmentChange(department) {
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Selecciona tu ciudad</option>';
            citySelect.disabled = false;
            
            // Simplified city list for demo - in production this would come from API
            const cities = {
                'Antioquia': ['Medellín', 'Bello', 'Itagüí', 'Envigado', 'Sabaneta'],
                'Atlántico': ['Barranquilla', 'Soledad', 'Malambo', 'Puerto Colombia'],
                'Bolívar': ['Cartagena', 'Magangué', 'Turbaco', 'Arjona'],
                'Cundinamarca': ['Bogotá', 'Soacha', 'Chía', 'Zipaquirá', 'Facatativá'],
                'Valle del Cauca': ['Cali', 'Palmira', 'Buenaventura', 'Tuluá', 'Cartago'],
                'Santander': ['Bucaramanga', 'Floridablanca', 'Girón', 'Piedecuesta']
            };
            
            if (cities[department]) {
                cities[department].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
            
            // Reset city and address
            this.formData.city = '';
            this.formData.address = '';
            document.getElementById('address').value = '';
            
            this.validateCurrentStep();
        },
        
        // Handle city change
        handleCityChange(city) {
            if (city && this.formData.department) {
                this.calculateShippingCost(this.formData.department, city);
            }
            this.validateCurrentStep();
        },
        
        // Calculate shipping cost
        async calculateShippingCost(department, city) {
            const costDisplay = document.getElementById('shipping-cost-display');
            const costAmount = document.getElementById('shipping-cost-amount');
            const estimatedTime = document.getElementById('estimated-time');
            
            costDisplay.classList.remove('hidden');
            costAmount.textContent = 'Calculando...';
            
            try {
                // Simulate API call - in production this would be a real API
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Mock shipping calculation
                const mockCost = this.getMockShippingCost(department, city);
                costAmount.textContent = mockCost.cost === 0 ? 'GRATIS' : `$${this.formatPrice(mockCost.cost)}`;
                estimatedTime.textContent = `Tiempo estimado: ${mockCost.estimatedTime}`;
                
                if (mockCost.cost === 0) {
                    costDisplay.className = 'p-3 bg-success-50 border border-success-200 rounded-lg';
                    costAmount.className = 'text-success-300 font-bold';
                } else {
                    costDisplay.className = 'p-3 bg-primary-50 border border-primary-200 rounded-lg';
                    costAmount.className = 'text-primary-300 font-bold';
                }
                
                // Update order summary
                this.updateOrderSummary();
                
            } catch (error) {
                costAmount.textContent = 'Error al calcular';
                estimatedTime.textContent = 'Intenta nuevamente';
            }
        },
        
        // Mock shipping cost calculation
        getMockShippingCost(department, city) {
            // Mock data - in production this would come from API
            const shippingRates = {
                'Antioquia': { cost: 8000, estimatedTime: '2-4 horas' },
                'Atlántico': { cost: 12000, estimatedTime: '4-6 horas' },
                'Bolívar': { cost: 15000, estimatedTime: '1-2 días' },
                'Cundinamarca': { cost: 0, estimatedTime: '1-3 horas' }, // Free shipping
                'Valle del Cauca': { cost: 10000, estimatedTime: '3-5 horas' },
                'Santander': { cost: 9000, estimatedTime: '4-8 horas' }
            };
            
            return shippingRates[department] || { cost: 15000, estimatedTime: '1-2 días' };
        },
        
        // Load pickup information
        loadPickupInfo() {
            // Mock pickup info - in production this would come from API
            document.getElementById('pickup-address').textContent = 'Calle 123 #45-67, Centro, {{ $store->name ?? "Tienda" }}';
        },
        
        // Update order summary
        updateOrderSummary() {
            const shippingElement = document.getElementById('summary-shipping');
            const totalElement = document.getElementById('summary-total');
            const freeShippingMessage = document.getElementById('free-shipping-message');
            
            const shippingCost = this.getCurrentShippingCost();
            const total = this.calculateTotal();
            
            // Update shipping cost
            if (shippingCost === 0) {
                shippingElement.textContent = 'GRATIS';
                shippingElement.className = 'font-semibold text-success-300';
                freeShippingMessage.classList.remove('hidden');
            } else {
                shippingElement.textContent = `$${this.formatPrice(shippingCost)}`;
                shippingElement.className = 'font-semibold text-black-500';
                freeShippingMessage.classList.add('hidden');
            }
            
            // Update total with animation
            totalElement.classList.add('animate-update');
            totalElement.textContent = `$${this.formatPrice(total)}`;
            
            setTimeout(() => {
                totalElement.classList.remove('animate-update');
            }, 500);
        },
        
        // Submit order
        async submitOrder() {
            const submitButton = document.getElementById('submit-order');
            const originalText = submitButton.textContent;
            
            // Disable button and show loading
            submitButton.disabled = true;
            submitButton.textContent = 'Procesando pedido...';
            
            try {
                // Prepare form data
                const formData = new FormData();
                
                // Add all form fields
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                formData.append('customer_name', this.formData.customer_name || '');
                formData.append('customer_phone', this.formData.customer_phone || '');
                formData.append('delivery_type', this.formData.delivery_type || '');
                
                if (this.formData.delivery_type === 'domicilio') {
                    formData.append('department', this.formData.department || '');
                    formData.append('city', this.formData.city || '');
                    formData.append('customer_address', this.formData.address || '');
                }
                
                formData.append('payment_method', this.formData.payment_method || '');
                
                if (this.formData.payment_method === 'efectivo') {
                    formData.append('cash_amount', this.formData.cash_amount || '');
                }
                
                // Add payment proof if uploaded
                const paymentProofFile = document.getElementById('payment_proof').files[0];
                if (paymentProofFile) {
                    formData.append('payment_proof', paymentProofFile);
                }
                
                formData.append('notes', this.formData.notes || '');
                formData.append('terms_accepted', '1');
                
                // Submit to server
                const response = await fetch('{{ route("tenant.checkout.store", $store->slug) }}', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    // Clear saved data
                    localStorage.removeItem('linkiu_checkout_data');
                    
                    // Redirect to success page
                    window.location.href = response.url;
                } else {
                    const errorData = await response.json();
                    this.showSubmissionError(errorData.message || 'Error al procesar el pedido');
                }
                
            } catch (error) {
                console.error('Error submitting order:', error);
                this.showSubmissionError('Error de conexión. Intenta nuevamente.');
            } finally {
                // Re-enable button
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        },
        
        // Show submission error
        showSubmissionError(message) {
            // Create error notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-error-300 text-white-50 px-6 py-4 rounded-lg shadow-lg z-50 max-w-sm';
            notification.innerHTML = `
                <div class="flex items-start gap-3">
                    <span class="text-xl">⚠️</span>
                    <div class="flex-1">
                        <p class="font-semibold mb-1">Error en el pedido</p>
                        <p class="text-sm">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white-50 hover:text-white-200 ml-2">✕</button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
        
        // Format price
        formatPrice(price) {
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(price);
        },
        
        // Validate current step
        validateCurrentStep() {
            if (this.currentStep === 1) {
                const nameValue = document.getElementById('customer_name').value;
                const phoneValue = document.getElementById('customer_phone').value;
                const nameValid = this.validateName(nameValue);
                const phoneValid = this.validatePhone(phoneValue);
                
                const continueButton = document.getElementById('continue-to-shipping');
                if (continueButton) {
                    continueButton.disabled = !(nameValid && phoneValid);
                }
            } else if (this.currentStep === 2) {
                const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
                let isValid = false;
                
                if (deliveryType) {
                    if (deliveryType.value === 'pickup') {
                        isValid = true;
                    } else if (deliveryType.value === 'domicilio') {
                        const department = document.getElementById('department').value;
                        const city = document.getElementById('city').value;
                        const address = document.getElementById('address').value.trim();
                        
                        isValid = department && city && address.length >= 10;
                    }
                }
                
                const continueButton = document.getElementById('continue-to-payment');
                continueButton.disabled = !isValid;
            } else if (this.currentStep === 3) {
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                let isValid = false;
                
                if (paymentMethod) {
                    if (paymentMethod.value === 'transferencia') {
                        isValid = true; // Transferencia is always valid
                    } else if (paymentMethod.value === 'efectivo') {
                        const cashAmount = parseFloat(document.getElementById('cash_amount').value) || 0;
                        const total = orderSummary.total;
                        isValid = cashAmount >= total;
                    }
                }
                
                const submitButton = document.getElementById('submit-order');
                submitButton.disabled = !isValid;
            }
        },
        
        // Handle payment method change
        handlePaymentMethodChange(method) {
            const efectivoFields = document.getElementById('efectivo-fields');
            const transferenciaFields = document.getElementById('transferencia-fields');
            
            if (method === 'efectivo') {
                efectivoFields.classList.remove('hidden');
                transferenciaFields.classList.add('hidden');
            } else if (method === 'transferencia') {
                efectivoFields.classList.add('hidden');
                transferenciaFields.classList.remove('hidden');
            }
            
            this.validateCurrentStep();
        },
        
        // Handle cash amount change
        handleCashAmountChange(amount) {
            const cashAmount = parseFloat(amount) || 0;
            const total = this.calculateTotal();
            const changeDisplay = document.getElementById('change-display');
            const changeAmount = document.getElementById('change-amount');
            const errorElement = document.getElementById('cash_amount_error');
            const inputElement = document.getElementById('cash_amount');
            
            if (cashAmount < total) {
                this.showError(errorElement, inputElement, `El monto debe ser mayor o igual a $${this.formatPrice(total)}`);
                changeDisplay.classList.add('hidden');
            } else {
                this.hideError(errorElement, inputElement);
                const change = cashAmount - total;
                changeAmount.textContent = `$${this.formatPrice(change)}`;
                changeDisplay.classList.remove('hidden');
            }
            
            this.validateCurrentStep();
        },
        
        // Handle payment proof upload
        handlePaymentProofUpload(file) {
            const preview = document.getElementById('payment_proof_preview');
            const errorElement = document.getElementById('payment_proof_error');
            
            if (!file) {
                preview.classList.add('hidden');
                return;
            }
            
            // Validate file
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!allowedTypes.includes(file.type)) {
                errorElement.textContent = 'Solo se permiten archivos JPG, PNG o PDF';
                errorElement.classList.remove('hidden');
                return;
            }
            
            if (file.size > maxSize) {
                errorElement.textContent = 'El archivo no puede ser mayor a 5MB';
                errorElement.classList.remove('hidden');
                return;
            }
            
            // Hide error and show preview
            errorElement.classList.add('hidden');
            preview.innerHTML = `
                <div class="flex items-center gap-3 p-3 bg-success-50 border border-success-200 rounded-lg">
                    <span class="text-2xl">📎</span>
                    <div class="flex-1">
                        <p class="font-medium text-success-300">${file.name}</p>
                        <p class="text-sm text-black-400">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.classList.add('hidden'); document.getElementById('payment_proof').value = '';" class="text-error-300 hover:text-error-200">
                        ✕
                    </button>
                </div>
            `;
            preview.classList.remove('hidden');
        },
        
        // Copy account number
        copyAccountNumber() {
            const accountNumber = '1234567890';
            navigator.clipboard.writeText(accountNumber).then(() => {
                const button = document.getElementById('copy-account');
                const originalText = button.textContent;
                button.textContent = '¡Copiado!';
                button.classList.add('bg-success-300');
                button.classList.remove('bg-primary-300');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('bg-success-300');
                    button.classList.add('bg-primary-300');
                }, 2000);
            });
        },
        
        // Calculate total
        calculateTotal() {
            const subtotal = {{ $subtotal }};
            const shippingCost = this.getCurrentShippingCost();
            const discount = this.getCurrentDiscount();
            
            return subtotal + shippingCost - discount;
        },
        
        // Get current shipping cost
        getCurrentShippingCost() {
            const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
            if (!deliveryType) return 0;
            
            if (deliveryType.value === 'pickup') return 0;
            
            const department = this.formData.department;
            const city = this.formData.city;
            
            if (department && city) {
                return this.getMockShippingCost(department, city).cost;
            }
            
            return 0;
        },
        
        // Get current discount
        getCurrentDiscount() {
            // This would check for applicable coupons
            return 0;
        },
        
        // Move to next step
        nextStep() {
            if (this.currentStep < 3) {
                // Hide current step content
                const currentContent = document.querySelector(`#step-${this.currentStep} .checkout-step-content`);
                currentContent.classList.add('hidden');
                
                // Mark current step as completed
                const currentStepElement = document.getElementById(`step-${this.currentStep}`);
                currentStepElement.classList.add('completed');
                currentStepElement.classList.remove('active');
                
                // Activate next step
                this.currentStep++;
                const nextStepElement = document.getElementById(`step-${this.currentStep}`);
                nextStepElement.classList.add('active');
                
                // Show next step content
                const nextContent = document.querySelector(`#step-${this.currentStep} .checkout-step-content`);
                nextContent.classList.remove('hidden');
                
                // Update step indicators
                this.updateStepIndicators();
            }
        },
        
        // Handle delivery type change
        handleDeliveryTypeChange(deliveryType) {
            this.formData.delivery_type = deliveryType;
            this.saveData();
            
            const domicilioFields = document.getElementById('domicilio-fields');
            const pickupInfo = document.getElementById('pickup-info');
            
            if (deliveryType === 'domicilio') {
                domicilioFields.classList.remove('hidden');
                pickupInfo.classList.add('hidden');
                orderSummary.shippingCost = 0; // Reset until calculated
            } else if (deliveryType === 'pickup') {
                domicilioFields.classList.add('hidden');
                pickupInfo.classList.remove('hidden');
                orderSummary.shippingCost = 0; // Pickup is free
                orderSummary.updateTotals();
            }
            
            this.validateCurrentStep();
        },
        
        // Handle department change
        handleDepartmentChange(department) {
            this.formData.department = department;
            this.saveData();
            
            const citySelect = document.getElementById('city');
            citySelect.disabled = false;
            citySelect.innerHTML = '<option value="">Selecciona tu ciudad</option>';
            
            // Add some common cities for the selected department
            const cities = this.getCitiesForDepartment(department);
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        },
        
        // Get cities for department (simplified)
        getCitiesForDepartment(department) {
            const citiesByDepartment = {
                'Antioquia': ['Medellín', 'Bello', 'Itagüí', 'Envigado', 'Rionegro'],
                'Atlántico': ['Barranquilla', 'Soledad', 'Malambo', 'Sabanalarga'],
                'Bolívar': ['Cartagena', 'Magangué', 'Turbaco', 'Arjona'],
                'Cundinamarca': ['Bogotá', 'Soacha', 'Chía', 'Zipaquirá', 'Facatativá'],
                'Valle del Cauca': ['Cali', 'Palmira', 'Buenaventura', 'Tuluá', 'Cartago'],
                'Santander': ['Bucaramanga', 'Floridablanca', 'Girón', 'Piedecuesta'],
                'Sucre': ['Sincelejo', 'Corozal', 'Sampués', 'San Marcos']
            };
            
            return citiesByDepartment[department] || [department + ' (Ciudad principal)'];
        },
        
        // Handle payment method change
        handlePaymentMethodChange(paymentMethod) {
            this.formData.payment_method = paymentMethod;
            this.saveData();
            
            const efectivoFields = document.getElementById('efectivo-fields');
            const transferenciaFields = document.getElementById('transferencia-fields');
            
            if (paymentMethod === 'efectivo') {
                efectivoFields.classList.remove('hidden');
                transferenciaFields.classList.add('hidden');
            } else if (paymentMethod === 'transferencia') {
                efectivoFields.classList.add('hidden');
                transferenciaFields.classList.remove('hidden');
            }
            
            this.validateCurrentStep();
        },
        
        // Handle cash amount change
        handleCashAmountChange(amount) {
            this.formData.cash_amount = parseFloat(amount) || 0;
            this.saveData();
            
            const total = orderSummary.total;
            const changeDisplay = document.getElementById('change-display');
            const changeAmount = document.getElementById('change-amount');
            
            if (amount && parseFloat(amount) >= total) {
                const change = parseFloat(amount) - total;
                changeDisplay.classList.remove('hidden');
                changeAmount.textContent = `$${orderSummary.formatPrice(change)}`;
            } else {
                changeDisplay.classList.add('hidden');
            }
            
            this.validateCurrentStep();
        },
        
        // Copy account number
        copyAccountNumber() {
            const accountNumber = '1234567890'; // This should come from store settings
            navigator.clipboard.writeText(accountNumber).then(() => {
                const button = document.getElementById('copy-account');
                const originalText = button.textContent;
                button.textContent = '¡Copiado!';
                button.classList.add('bg-success-300');
                button.classList.remove('bg-primary-300');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('bg-success-300');
                    button.classList.add('bg-primary-300');
                }, 2000);
            });
        },
        
        // Calculate shipping cost
        async calculateShippingCost() {
            const department = document.getElementById('department').value;
            const city = document.getElementById('city').value;
            
            if (!department || !city) return;
            
            try {
                const response = await fetch('{{ route("tenant.checkout.shipping-cost", $store->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        department: department,
                        city: city,
                        subtotal: orderSummary.subtotal
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    orderSummary.shippingCost = data.cost;
                    orderSummary.updateTotals();
                    
                    // Show shipping cost display
                    const costDisplay = document.getElementById('shipping-cost-display');
                    const costAmount = document.getElementById('shipping-cost-amount');
                    const estimatedTime = document.getElementById('estimated-time');
                    
                    costDisplay.classList.remove('hidden');
                    costAmount.textContent = data.cost > 0 ? `$${orderSummary.formatPrice(data.cost)}` : 'GRATIS';
                    estimatedTime.textContent = data.estimated_time || 'Tiempo estimado no disponible';
                    
                    if (data.free_shipping_message) {
                        document.getElementById('free-shipping-message').classList.remove('hidden');
                    }
                } else {
                    console.error('Error calculating shipping:', data.message);
                }
            } catch (error) {
                console.error('Error calculating shipping:', error);
            }
        },
        
        // Update step indicators
        updateStepIndicators() {
            for (let i = 1; i <= 3; i++) {
                const stepElement = document.getElementById(`step-${i}`);
                const numberElement = stepElement.querySelector('.w-8');
                const titleElement = stepElement.querySelector('h3');
                
                if (i < this.currentStep) {
                    // Completed step
                    numberElement.className = 'w-8 h-8 bg-success-300 text-white-50 rounded-full flex items-center justify-center text-sm font-semibold mr-3';
                    titleElement.className = 'text-lg font-semibold text-black-500';
                } else if (i === this.currentStep) {
                    // Active step
                    numberElement.className = 'w-8 h-8 bg-primary-300 text-white-50 rounded-full flex items-center justify-center text-sm font-semibold mr-3';
                    titleElement.className = 'text-lg font-semibold text-black-500';
                } else {
                    // Inactive step
                    numberElement.className = 'w-8 h-8 bg-white-200 text-black-300 rounded-full flex items-center justify-center text-sm font-semibold mr-3';
                    titleElement.className = 'text-lg font-semibold text-black-300';
                }
            }
        }
    };
    
    // Order Summary Management
    const orderSummary = {
        products: [],
        subtotal: 0,
        shippingCost: 0,
        discounts: 0,
        to);: 0,
        
        // Initialize order summary
        init() {
            this.loadCartProducts();
        },
        
        // Load products from cart
        async loadCartProducts() {
            try {
                const response = await fetch('{{ route("tenant.cart.get", $store->slug) }}');
                const data = await response.json();
                
                if (data.success) {
                    this.products = data.items;
                    this.subtotal = data.total;
                    this.updateDisplay();
                } else {
                    this.showEmptyCart();
                }
            } catch (error) {
                console.error('Error loading cart:', error);
                this.showEmptyCart();
            }
        },
        
        // Update summary display
        updateDisplay() {
            this.displayProducts();
            this.updateTotals();
        },
        
        // Display products in summary
        displayProducts() {getElementById('summary-products');
            
            if (this.products.length === 0) {
                container.innerHTML = '<div class="text-center py-4"><p class="text-black-300 text-sm">No hay productos en el carrito</p></div>';
                return;
            }
            
            let html = '';
            this.products.forEach(item => {
                const unitPrice = item.product_pricitem.variant_details?.precio_modificador || 0);
                html += `
                    <div class="flex items-center gap-3 py-2">
                        <div class="w-12 h-12 bg-white-100 rounded-lg overflow-hidden flex-shrink-0">
                            ${item.product.main_image_url ? 
                                `<img src="${item.product.main_image_url}" alt="${item.product.name}" class="w-full h-full object-cover">` :
                                `<div class="w-full h-full bg-white-200 flex items-center justify-center text-black-300 text-xs">Sin imagen</div>`
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-black-500 truncate">${item.product.name}</h4>
                            ${item.variant_display ? `<p class="text-xs text-black-300">${item.variant_display}</p>` : ''}
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-black-400">Cantidad: ${item.quantity}</span>
                                <span class="text-sm font-semibold text-black-500">$${this.formatPrice(unitPrice * item.quantity)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        },
        
        // Update totals
        updateTotals() {
            document.getElementById('summary-subtotal').textContent = `$${this.formatPrice(this.subtotal)}`;
            document.getElementById('summary-shipping').textContent = this.shippingCost > 0 ? `$${this.formatPrice(this.shippingCost)}` : 'Calculando...';
            
            this.total = this.subtotal + this.shippingCost - this.discounts;
            document.getElementById('summary-total').textContent = `$${this.formatPrice(this.total)}`;
            
            // Update submit button total
            document.getElementById('submit-button-total').textContent = this.formatPrice(this.total);
            
            // Show/hide free shipping message
            if (this.shippingCost === 0 && checkoutForm.formData.delivery_type === 'domicilio') {
                document.getElementById('free-shipping-message').classList.remove('hidden');
            } else {
                document.getElementById('free-shipping-message').classList.add('hidden');
            }
        },
        
        // Format price
        formatPrice(price) {
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            ice);
        },
        
        // Show empty cart message
        showEmptyCart() {
            document.getElementById('summary-products').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-black-300 text-sm mb-4">Tu carrito está vacío</p>
                    <a href="{{ route('tenant.home', $store->slug) }}" class="text-primary-300 text-sm font-medium hover:underline">
                        Continuar comprando
                    </a>
                </div>
            `;
        }
    };
    
    // Coupon Management
    const couponManager = {
        appliedCoupon: null,
        
        init() {
            this.bindEvents();
        },
        
        bindEvents() {
            document.getElementById('apply-coupon').addEventListener('click', () => {
                this.applyCoupon();
            });
            
            document.getElementById('coupon-code').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.applyCoupon();
                }
            });
            
            document.getElementById('remove-coupon').addEventListener('click', () => {
                this.removeCoupon();
            });
        },
        
        async applyCoupon() {
            const couponCode = document.getElementById('coupon-code').value.trim();
            const messageElement = document.getElementById('coupon-message');
            const applyButton = document.getElementById('apply-coupon');
            
            if (!couponCode) {
                this.showMessage('Ingresa un código de cupón', 'error');
                return;
            }
            
            // Disable button and show loading
            applyButton.disabled = true;
            applyButton.textContent = 'Aplicando...';
            
            try {
                const response = await fetch('{{ route("tenant.checkout.apply-coupon", $store->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        coupon_code: couponCode,
                        subtotal: orderSummary.subtotal
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.appliedCoupon = data.coupon;
                    orderSummary.discounts = data.discount_amount;
                    orderSummary.updateTotals();
                    
                    this.showAppliedCoupon(data.coupon.name || couponCode);
                    this.showMessage(`¡Cupón aplicado! Descuento: ${orderSummary.formatPrice(data.discount_amount)}`, 'success');
                    
                    // Clear input
                    document.getElementById('coupon-code').value = '';
                } else {
                    this.showMessage(data.message || 'Cupón no válido', 'error');
                }
            } catch (error) {
                console.error('Error applying coupon:', error);
                this.showMessage('Error al aplicar el cupón. Intenta nuevamente.', 'error');
            } finally {
                // Re-enable button
                applyButton.disabled = false;
                applyButton.textContent = 'Aplicar';
            }
        },
        
        removeCoupon() {
            this.appliedCoupon = null;
            orderSummary.discounts = 0;
            orderSummary.updateTotals();
            
            document.getElementById('applied-coupon').classList.add('hidden');
            document.getElementById('coupon-message').classList.add('hidden');
            document.getElementById('coupon-code').value = '';
        },
        
        showAppliedCoupon(couponName) {
            document.getElementById('coupon-name').textContent = couponName;
            document.getElementById('applied-coupon').classList.remove('hidden');
        },
        
        showMessage(message, type) {
            const messageElement = document.getElementById('coupon-message');
            messageElement.textContent = message;
            messageElement.className = `mt-2 text-sm ${type === 'success' ? 'text-success-300' : 'text-error-300'}`;
            messageElement.classList.remove('hidden');
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                messageElement.classList.add('hidden');
            }, 3000);
        }
    };

    // Debug: verificar que los elementos existen
    console.log('=== CHECKOUT DEBUG ===');
    console.log('customer_name:', document.getElementById('customer_name'));
    console.log('customer_phone:', document.getElementById('customer_phone'));
    console.log('continue-to-shipping:', document.getElementById('continue-to-shipping'));
    
    // Función de validación simple
    function checkForm() {
        const name = document.getElementById('customer_name')?.value?.trim() || '';
        const phone = document.getElementById('customer_phone')?.value?.trim() || '';
        const button = document.getElementById('continue-to-shipping');
        
        console.log('Checking form:', { name, phone });
        
        // Validaciones básicas
        const nameOk = name.length >= 2;
        const phoneOk = phone.length >= 10;
        
        console.log('Validation:', { nameOk, phoneOk });
        
        if (button) {
            button.disabled = !(nameOk && phoneOk);
            console.log('Button disabled:', button.disabled);
        }
    }
    
    // Event listeners simples
    document.getElementById('customer_name')?.addEventListener('input', checkForm);
    document.getElementById('customer_phone')?.addEventListener('input', checkForm);
    
    // Inicializar
    checkForm();
});
</script>
@endpush
@push('s
cripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== CHECKOUT DEBUG ===');
    console.log('customer_name:', document.getElementById('customer_name'));
    console.log('customer_phone:', document.getElementById('customer_phone'));
    console.log('continue-to-shipping:', document.getElementById('continue-to-shipping'));
    
    // Función de validación simple
    function checkForm() {
        const name = document.getElementById('customer_name')?.value?.trim() || '';
        const phone = document.getElementById('customer_phone')?.value?.trim() || '';
        const button = document.getElementById('continue-to-shipping');
        
        console.log('Checking form:', { name, phone });
        
        // Validaciones básicas
        const nameOk = name.length >= 2;
        const phoneOk = phone.length >= 10;
        
        console.log('Validation:', { nameOk, phoneOk });
        
        if (button) {
            button.disabled = !(nameOk && phoneOk);
            console.log('Button disabled:', button.disabled);
        }
    }
    
    // Event listeners simples
    document.getElementById('customer_name')?.addEventListener('input', checkForm);
    document.getElementById('customer_phone')?.addEventListener('input', checkForm);
    
    // Inicializar
    checkForm();
});
</script>
@endpush