<x-tenant-admin-layout :store="$store">
@section('title', 'Editar Pedido #' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('tenant.admin.orders.show', [$store->slug, $order->id]) }}" 
               class="text-black-300 hover:text-black-400">
                <x-solar-arrow-left-outline class="w-6 h-6" />
            </a>
            <h1 class="text-lg font-semibold text-black-500">Editar Pedido #{{ $order->order_number }}</h1>
            <span class="text-xs {{ $order->status_color }} px-2 py-1 rounded">
                {{ $order->status_label }}
            </span>
        </div>
    </div>

    @if(!$order->canBeEdited())
        <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <x-solar-danger-outline class="w-5 h-5 text-warning-300 flex-shrink-0" />
                <div>
                    <h3 class="text-sm font-medium text-warning-300">Edición Limitada</h3>
                    <div class="text-xs text-warning-300 mt-1">
                        Este pedido está en estado "{{ $order->status_label }}" y solo se pueden editar algunos campos básicos.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('tenant.admin.orders.update', [$store->slug, $order->id]) }}" method="POST" enctype="multipart/form-data" x-data="orderEdit" x-init="init()" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Información del Cliente -->
        <div class="bg-white-50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-black-400 mb-4">Información del Cliente</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="lg:col-span-2">
                    <label for="customer_name" class="block text-sm font-medium text-black-400 mb-2">
                        Nombre Completo *
                    </label>
                    <input type="text" 
                           id="customer_name" 
                           name="customer_name" 
                           value="{{ old('customer_name', $order->customer_name) }}" 
                           required
                           class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
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
                           value="{{ old('customer_phone', $order->customer_phone) }}" 
                           required
                           class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
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
                            class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500"
                            required>
                        <option value="">Seleccionar departamento</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ old('department', $order->department) === $department ? 'selected' : '' }}>
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
                           value="{{ old('city', $order->city) }}" 
                           required
                           class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
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
                              class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
                              placeholder="Dirección completa, barrio, referencias">{{ old('customer_address', $order->customer_address) }}</textarea>
                    @error('customer_address')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tipo de Entrega y Pago -->
        <div class="bg-white-50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-black-400 mb-4">Entrega y Pago</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">Tipo de Entrega *</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                            <input type="radio" 
                                   name="delivery_type" 
                                   value="domicilio" 
                                   {{ old('delivery_type', $order->delivery_type) === 'domicilio' ? 'checked' : '' }}
                                   x-model="deliveryType"
                                   class="w-4 h-4 text-primary-200 border-white-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Domicilio</span>
                        </label>
                        <label class="flex items-center p-3 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                            <input type="radio" 
                                   name="delivery_type" 
                                   value="pickup" 
                                   {{ old('delivery_type', $order->delivery_type) === 'pickup' ? 'checked' : '' }}
                                   x-model="deliveryType"
                                   class="w-4 h-4 text-primary-200 border-white-300 focus:ring-primary-200">
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
                        <label class="flex items-center p-3 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="transferencia" 
                                   {{ old('payment_method', $order->payment_method) === 'transferencia' ? 'checked' : '' }}
                                   x-model="paymentMethod"
                                   class="w-4 h-4 text-primary-200 border-white-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Transferencia Bancaria</span>
                        </label>
                        <label class="flex items-center p-3 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="contra_entrega" 
                                   {{ old('payment_method', $order->payment_method) === 'contra_entrega' ? 'checked' : '' }}
                                   x-model="paymentMethod"
                                   class="w-4 h-4 text-primary-200 border-white-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Pago Contra Entrega</span>
                        </label>
                        <label class="flex items-center p-3 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer" x-show="deliveryType === 'pickup'">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="efectivo" 
                                   {{ old('payment_method', $order->payment_method) === 'efectivo' ? 'checked' : '' }}
                                   x-model="paymentMethod"
                                   class="w-4 h-4 text-primary-200 border-white-300 focus:ring-primary-200">
                            <span class="ml-3 text-sm text-black-500">Efectivo</span>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Comprobante de Pago (solo para transferencia) -->
            <div x-show="paymentMethod === 'transferencia'" class="mt-6">
                @if($order->payment_proof_path)
                    <div class="mb-4 p-4 bg-success-50 border border-success-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <x-solar-document-outline class="w-5 h-5 text-success-300 mr-2" />
                                <span class="text-sm text-success-300">Comprobante actual subido</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('tenant.admin.orders.download-payment-proof', [$store->slug, $order->id]) }}" 
                                   class="text-xs text-primary-200 hover:text-primary-300">
                                    Descargar
                                </a>
                                <label class="flex items-center">
                                    <input type="checkbox" name="remove_payment_proof" value="1" 
                                           class="w-4 h-4 text-error-400 border-white-300 rounded focus:ring-error-200">
                                    <span class="ml-2 text-xs text-error-400">Eliminar</span>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <label for="payment_proof" class="block text-sm font-medium text-black-400 mb-2">
                    {{ $order->payment_proof_path ? 'Reemplazar Comprobante' : 'Comprobante de Pago' }} (Opcional)
                </label>
                <input type="file" 
                       id="payment_proof" 
                       name="payment_proof" 
                       accept="image/*,application/pdf" 
                       class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors">
                <div class="text-xs text-black-300 mt-1">JPG, PNG, PDF - Máximo 5MB</div>
                @error('payment_proof')
                    <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Productos del Pedido (Solo lectura si no es editable) -->
        <div class="bg-white-50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-black-400 mb-4">Productos del Pedido</h3>
            @if($order->canBeEdited())
                <div class="bg-info-50 border border-info-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <x-solar-info-circle-outline class="w-5 h-5 text-info-200 flex-shrink-0" />
                        <div>
                            <h4 class="text-sm font-medium text-info-200">Edición de Productos</h4>
                            <div class="text-xs text-info-200 mt-1">
                                Para modificar los productos, elimina este pedido y crea uno nuevo con los productos correctos.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-black-500">Cant: {{ $item->quantity }}</div>
                            <div class="text-sm text-black-400">${{ number_format($item->unit_price, 0, ',', '.') }} c/u</div>
                            <div class="text-sm font-semibold text-black-500">${{ number_format($item->item_total, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Resumen de Totales -->
            <div class="mt-6 pt-6 border-t border-white-200">
                <div class="flex justify-end">
                    <div class="w-full max-w-sm space-y-3">
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
            </div>
        </div>

        <!-- Notas Adicionales -->
        <div class="bg-white-50 rounded-lg p-6">
            <h3 class="text-sm font-medium text-black-400 mb-4">Notas Adicionales</h3>
            <textarea name="notes" 
                      rows="3"
                      class="w-full px-4 py-3 border border-white-200 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-300 transition-colors text-black-500 placeholder-black-200"
                      placeholder="Instrucciones especiales, observaciones, etc.">{{ old('notes', $order->notes) }}</textarea>
            @error('notes')
                <p class="text-error-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Acciones -->
        <div class="flex gap-3 justify-end">
            <a href="{{ route('tenant.admin.orders.show', [$store->slug, $order->id]) }}" class="px-4 py-2 border border-white-300 text-black-400 rounded-lg hover:bg-white-100 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                <x-solar-check-circle-outline class="w-5 h-5 mr-2" />
                Actualizar Pedido
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderEdit', () => ({
        // Datos iniciales del pedido
        deliveryType: '{{ old("delivery_type", $order->delivery_type) }}',
        paymentMethod: '{{ old("payment_method", $order->payment_method) }}',
        selectedDepartment: '{{ old("department", $order->department) }}',

        init() {
            console.log('Order Edit initialized');
        }
    }));
});
</script>
@endpush
@endsection
</x-tenant-admin-layout> 