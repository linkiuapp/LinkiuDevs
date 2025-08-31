<x-tenant-admin-layout :store="$store">
@section('title', 'Nuevo Pedido')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('tenant.admin.orders.index', $store->slug) }}" 
               class="text-black-300 hover:text-black-400">
                <x-solar-arrow-left-outline class="w-6 h-6" />
            </a>
            <h1 class="text-lg font-semibold text-black-500">Nuevo Pedido</h1>
        </div>
    </div>

    <form action="{{ route('tenant.admin.orders.store', $store->slug) }}" method="POST" enctype="multipart/form-data" x-data="orderCreate" x-init="init()" class="space-y-6">
        @csrf

        <!-- Información del Cliente -->
        <div class="bg-accent-50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-black-400 mb-4">Información del Cliente</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="lg:col-span-2">
                    <label for="customer_name" class="block text-sm font-medium text-black-400 mb-2">
                        Nombre Completo *
                    </label>
                    <input type="text" 
                           id="customer_name" 
                           name="customer_name" 
                           value="{{ old('customer_name') }}" 
                           required
                           class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
                           placeholder="Nombre completo del cliente">
                    @error('customer_name')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-black-400 mb-2">
                        Teléfono / WhatsApp *
                    </label>
                    <input type="tel" 
                           id="customer_phone" 
                           name="customer_phone" 
                           value="{{ old('customer_phone') }}" 
                           required
                           class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
                           placeholder="3001234567">
                    @error('customer_phone')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="department" class="block text-sm font-medium text-black-400 mb-2">
                        Departamento *
                    </label>
                    <select id="department" 
                            name="department" 
                            x-model="selectedDepartment"
                            class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500"
                            required>
                        <option value="">Seleccionar departamento</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                    @error('department')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-black-400 mb-2">
                        Ciudad *
                    </label>
                    <input type="text" 
                           id="city" 
                           name="city" 
                           value="{{ old('city') }}" 
                           required
                           class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
                           placeholder="Ciudad">
                    @error('city')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <label for="customer_address" class="block text-sm font-medium text-black-400 mb-2">
                        Dirección Completa *
                    </label>
                    <textarea id="customer_address" 
                              name="customer_address" 
                              required 
                              rows="3"
                              class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
                              placeholder="Dirección completa, barrio, referencias">{{ old('customer_address') }}</textarea>
                    @error('customer_address')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tipo de Entrega y Pago -->
        <div class="bg-accent-50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-black-400 mb-4">Entrega y Pago</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">Tipo de Entrega *</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border border-accent-200 rounded-lg hover:bg-accent-100 cursor-pointer">
                            <input type="radio" 
                                   name="delivery_type" 
                                   value="domicilio" 
                                   {{ old('delivery_type') === 'domicilio' ? 'checked' : '' }}
                                   x-model="deliveryType" 
                                   @change="updateShippingOptions()"
                                   class="w-4 h-4 text-primary-200 border-accent-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Domicilio</span>
                        </label>
                        <label class="flex items-center p-3 border border-accent-200 rounded-lg hover:bg-accent-100 cursor-pointer">
                            <input type="radio" 
                                   name="delivery_type" 
                                   value="pickup" 
                                   {{ old('delivery_type') === 'pickup' ? 'checked' : '' }}
                                   x-model="deliveryType" 
                                   @change="updateShippingOptions()"
                                   class="w-4 h-4 text-primary-200 border-accent-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Pickup en Tienda (Gratis)</span>
                        </label>
                    </div>
                    @error('delivery_type')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">Método de Pago *</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border border-accent-200 rounded-lg hover:bg-accent-100 cursor-pointer">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="transferencia" 
                                   {{ old('payment_method') === 'transferencia' ? 'checked' : '' }}
                                   x-model="paymentMethod"
                                   class="w-4 h-4 text-primary-200 border-accent-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Transferencia Bancaria</span>
                        </label>
                        <label class="flex items-center p-3 border border-accent-200 rounded-lg hover:bg-accent-100 cursor-pointer">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="contra_entrega" 
                                   {{ old('payment_method') === 'contra_entrega' ? 'checked' : '' }}
                                   x-model="paymentMethod"
                                   class="w-4 h-4 text-primary-200 border-accent-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Pago Contra Entrega</span>
                        </label>
                        <label class="flex items-center p-3 border border-accent-200 rounded-lg hover:bg-accent-100 cursor-pointer" x-show="deliveryType === 'pickup'">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="efectivo" 
                                   {{ old('payment_method') === 'efectivo' ? 'checked' : '' }}
                                   x-model="paymentMethod"
                                   class="w-4 h-4 text-primary-200 border-accent-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Efectivo</span>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Zona de Envío (solo para domicilio) -->
            <div x-show="deliveryType === 'domicilio'" class="mt-6">
                <label for="shipping_zone_id" class="block text-sm font-medium text-black-400 mb-2">Zona de Envío</label>
                
                <div x-show="!shippingMethods || shippingMethods.length === 0" class="p-4 bg-warning-50 border border-warning-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-solar-danger-outline class="w-5 h-5 text-warning-300 flex-shrink-0" />
                        <div>
                            <h4 class="text-sm font-medium text-warning-300">Sin Métodos de Envío</h4>
                            <div class="text-xs text-warning-300 mt-1">
                                No hay métodos de envío configurados. 
                                <a href="{{ route('tenant.admin.shipping-methods.index', $store->slug) }}" class="underline">
                                    Configurar métodos de envío
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div x-show="shippingMethods && shippingMethods.length > 0">
                    <select id="shipping_zone_id" 
                            name="shipping_zone_id" 
                            x-model="selectedShippingZone" 
                            @change="calculateShipping()"
                            class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500">
                        <option value="">Seleccionar zona</option>
                        <template x-for="method in shippingMethods" :key="method.id">
                            <optgroup :label="method.name">
                                <template x-for="zone in method.active_zones" :key="zone.id">
                                    <option :value="zone.id" x-text="`${zone.name} - $${zone.cost ? zone.cost.toLocaleString() : '0'}`"></option>
                                </template>
                            </optgroup>
                        </template>
                    </select>
                    
                    <!-- Mostrar cuando no hay zonas -->
                    <div x-show="shippingMethods.every(method => !method.active_zones || method.active_zones.length === 0)" 
                         class="mt-3 p-3 bg-warning-50 border border-warning-200 rounded-lg">
                        <div class="text-sm text-warning-300">
                            Los métodos de envío no tienen zonas activas configuradas.
                            <a href="{{ route('tenant.admin.shipping-methods.index', $store->slug) }}" class="underline">
                                Configurar zonas
                            </a>
                        </div>
                    </div>
                </div>
                
                @error('shipping_zone_id')
                    <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Comprobante de Pago (solo para transferencia) -->
            <div x-show="paymentMethod === 'transferencia'" class="mt-6">
                <label for="payment_proof" class="block text-sm font-medium text-black-400 mb-2">Comprobante de Pago (Opcional)</label>
                <input type="file" 
                       id="payment_proof" 
                       name="payment_proof" 
                       accept="image/*,application/pdf" 
                       class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors">
                <div class="text-xs text-black-300 mt-1">JPG, PNG, PDF - Máximo 5MB</div>
                @error('payment_proof')
                    <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Productos -->
        <div class="bg-accent-50 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-black-400">Productos del Pedido</h3>
                <button type="button" @click="addProduct()" class="btn-primary text-sm flex items-center gap-2">
                    <x-solar-add-circle-outline class="w-4 h-4" />
                    Agregar Producto
                </button>
            </div>
            
            <!-- Lista de productos -->
            <div class="space-y-4" x-show="orderItems.length > 0">
                <template x-for="(item, index) in orderItems" :key="index">
                    <div class="border border-accent-200 rounded-lg p-4 bg-accent-100">
                        <div class="grid grid-cols-1 lg:grid-cols-6 gap-4 items-end">
                            <!-- Producto -->
                            <div class="lg:col-span-2">
                                <label class="block text-xs font-medium text-black-400 mb-2">Producto *</label>
                                <select :name="`items[${index}][product_id]`" 
                                        x-model="item.product_id" 
                                        @change="updateProductInfo(index)" 
                                        required
                                        class="w-full px-3 py-2 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                                    <option value="">Seleccionar producto</option>
                                    <template x-for="product in products" :key="product.id">
                                        <option :value="product.id" x-text="`${product.name} - $${product.price.toLocaleString()}`"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Cantidad -->
                            <div>
                                <label class="block text-xs font-medium text-black-400 mb-2">Cantidad *</label>
                                <input type="number" 
                                       :name="`items[${index}][quantity]`" 
                                       x-model="item.quantity" 
                                       @input="calculateItemTotal(index)" 
                                       min="1" 
                                       required
                                       class="w-full px-3 py-2 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                            </div>

                            <!-- Precio Unitario -->
                            <div>
                                <label class="block text-xs font-medium text-black-400 mb-2">Precio Unit.</label>
                                <div class="px-3 py-2 bg-accent-200 rounded-lg text-sm text-black-400" 
                                     x-text="`$${item.unitPrice.toLocaleString()}`"></div>
                            </div>

                            <!-- Total Item -->
                            <div>
                                <label class="block text-xs font-medium text-black-400 mb-2">Total</label>
                                <div class="px-3 py-2 bg-primary-50 rounded-lg text-sm font-semibold text-primary-200" 
                                     x-text="`$${item.total.toLocaleString()}`"></div>
                            </div>

                            <!-- Eliminar -->
                            <div>
                                <button type="button" 
                                        @click="removeProduct(index)" 
                                        class="w-full px-3 py-2 bg-error-50 text-error-300 rounded-lg hover:bg-error-100 transition-colors">
                                    <x-solar-trash-bin-trash-outline class="w-4 h-4 mx-auto" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Estado vacío -->
            <div x-show="orderItems.length === 0" class="text-center py-8">
                <x-solar-bag-outline class="w-12 h-12 text-black-200 mx-auto mb-3" />
                <p class="text-black-300 mb-3">No hay productos agregados</p>
                <button type="button" @click="addProduct()" class="btn-primary text-sm">
                    Agregar Primer Producto
                </button>
            </div>
        </div>

        <!-- Resumen del Pedido -->
        <div class="bg-accent-50 rounded-lg p-6" x-show="orderItems.length > 0">
            <h3 class="text-sm font-medium text-black-400 mb-4">Resumen del Pedido</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-black-400">Subtotal:</span>
                    <span class="text-black-500" x-text="`$${subtotal.toLocaleString()}`"></span>
                </div>
                <div class="flex justify-between text-sm" x-show="shippingCost > 0">
                    <span class="text-black-400">Envío:</span>
                    <span class="text-black-500" x-text="`$${shippingCost.toLocaleString()}`"></span>
                </div>
                <div class="flex justify-between border-t border-accent-200 pt-3">
                    <span class="text-lg font-semibold text-black-500">Total:</span>
                    <span class="text-lg font-semibold text-primary-200" x-text="`$${total.toLocaleString()}`"></span>
                </div>
            </div>
        </div>

        <!-- Notas Adicionales -->
        <div class="bg-accent-50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-black-400 mb-4">Notas Adicionales</h3>
            <textarea name="notes" 
                      rows="3"
                      class="w-full px-4 py-3 border border-accent-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
                      placeholder="Instrucciones especiales, observaciones, etc.">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Acciones -->
        <div class="flex gap-3 justify-end">
            <a href="{{ route('tenant.admin.orders.index', $store->slug) }}" class="px-4 py-2 border border-accent-300 text-black-400 rounded-lg hover:bg-accent-100 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="btn-primary" :disabled="orderItems.length === 0">
                <x-solar-check-circle-outline class="w-5 h-5 mr-2" />
                Crear Pedido
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderCreate', () => ({
        // Datos
        deliveryType: '{{ old("delivery_type") }}',
        paymentMethod: '{{ old("payment_method") }}',
        selectedDepartment: '{{ old("department") }}',
        selectedShippingZone: '',
        shippingCost: 0,
        products: @json($products),
        shippingMethods: @json($shippingMethods),
        orderItems: [],
        subtotal: 0,
        total: 0,

        init() {
            console.log('Order Create initialized');
            console.log('Shipping Methods:', this.shippingMethods);
            console.log('Initial Delivery Type:', this.deliveryType);
            
            if (this.orderItems.length === 0) {
                this.addProduct();
            }
            
            // Debug: verificar zonas de envío
            if (this.shippingMethods && this.shippingMethods.length > 0) {
                this.shippingMethods.forEach(method => {
                    console.log(`Método ${method.name}:`, method.active_zones || method.zones);
                });
            } else {
                console.log('❌ No hay métodos de envío configurados');
            }
        },

        addProduct() {
            this.orderItems.push({
                product_id: '',
                quantity: 1,
                selectedProduct: null,
                unitPrice: 0,
                total: 0
            });
        },

        removeProduct(index) {
            this.orderItems.splice(index, 1);
            this.calculateTotals();
        },

        updateProductInfo(index) {
            const item = this.orderItems[index];
            const product = this.products.find(p => p.id == item.product_id);
            
            if (product) {
                item.selectedProduct = product;
                item.unitPrice = parseFloat(product.price);
                this.calculateItemTotal(index);
            }
        },

        calculateItemTotal(index) {
            const item = this.orderItems[index];
            if (item.selectedProduct && item.quantity > 0) {
                item.total = item.unitPrice * parseInt(item.quantity);
                this.calculateTotals();
            }
        },

        calculateTotals() {
            this.subtotal = this.orderItems.reduce((sum, item) => sum + item.total, 0);
            this.total = this.subtotal + this.shippingCost;
        },

        updateShippingOptions() {
            if (this.deliveryType === 'pickup') {
                this.shippingCost = 0;
                this.selectedShippingZone = '';
            }
            this.calculateTotals();
        },

        calculateShipping() {
            if (!this.selectedShippingZone || this.deliveryType !== 'domicilio') {
                this.shippingCost = 0;
                this.calculateTotals();
                return;
            }

            fetch('{{ route("tenant.admin.orders.get-shipping-cost", $store->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    zone_id: this.selectedShippingZone,
                    subtotal: this.subtotal
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.shippingCost = data.cost;
                    this.calculateTotals();
                }
            })
            .catch(error => {
                console.error('Error calculating shipping:', error);
            });
        }
    }));
});
</script>
@endpush
@endsection
</x-tenant-admin-layout> 