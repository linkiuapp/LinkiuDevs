<x-tenant-admin-layout :store="$store">

@section('title', 'Editar Método de Pago')

@section('content')
<div class="container-fluid" x-data="paymentMethodForm">
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

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Editar Método de Pago</h1>
        <a href="{{ route('tenant.admin.payment-methods.show', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver
        </a>
    </div>

    <form action="{{ route('tenant.admin.payment-methods.update', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" method="POST" @submit.prevent="validateForm($event)">
        @csrf
        @method('PUT')
        
        {{-- Card principal con toda la información --}}
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Información del Método de Pago</h2>
            </div>
            
            <div class="p-6">
                {{-- Sección: Información Básica --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-info-circle-outline class="w-5 h-5" />
                        Información Básica
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Tipo de Método
                            </label>
                            <div class="w-full px-4 py-2 border border-accent-200 rounded-lg bg-accent-100 text-black-300">
                                @if($paymentMethod->isCash())
                                    Efectivo
                                @elseif($paymentMethod->isBankTransfer())
                                    Transferencia Bancaria
                                @elseif($paymentMethod->isCardTerminal())
                                    Datáfono
                                @else
                                    {{ $paymentMethod->type }}
                                @endif
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                El tipo de método no se puede cambiar después de creado.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Nombre <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                value="{{ old('name', $paymentMethod->name) }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('name') border-error-200 @enderror"
                                required>
                            @error('name')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Instrucciones para el cliente
                            </label>
                            <textarea
                                name="instructions"
                                rows="3"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('instructions') border-error-200 @enderror"
                                placeholder="Instrucciones para el cliente sobre cómo usar este método de pago...">{{ old('instructions', $paymentMethod->instructions) }}</textarea>
                            @error('instructions')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Estado
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="is_active"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-accent-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Método activo</span>
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                Los métodos inactivos no se mostrarán en el checkout.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Método predeterminado
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="is_default"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('is_default', $isDefault) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-accent-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Establecer como predeterminado</span>
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                Este método se seleccionará automáticamente en el checkout.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Sección: Disponibilidad --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-check-square-outline class="w-5 h-5" />
                        Disponibilidad
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Disponible para pickup
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="available_for_pickup"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('available_for_pickup', $paymentMethod->available_for_pickup) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-accent-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Disponible para recogida en tienda</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Disponible para delivery
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="available_for_delivery"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('available_for_delivery', $paymentMethod->available_for_delivery) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-accent-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Disponible para entrega a domicilio</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección: Opciones específicas para Efectivo --}}
                @if($paymentMethod->isCash())
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-wallet-money-outline class="w-5 h-5" />
                        Opciones para Efectivo
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Aceptar cambio
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="cash_change_available"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('cash_change_available', $cashChangeAvailable) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-accent-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Permitir que el cliente solicite cambio</span>
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                Si está activado, el cliente podrá especificar si necesita cambio y el monto.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                
                {{-- Sección: Opciones específicas para Transferencia Bancaria --}}
                @if($paymentMethod->isBankTransfer())
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-card-transfer-outline class="w-5 h-5" />
                        Opciones para Transferencia Bancaria
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Comprobante de pago
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="require_proof"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('require_proof', $paymentMethod->require_proof ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-accent-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Requerir comprobante de pago</span>
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                Si está activado, el cliente deberá subir un comprobante de la transferencia.
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('tenant.admin.payment-methods.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" 
                           class="btn-outline-primary px-4 py-2 rounded-lg inline-flex items-center gap-2">
                            <x-solar-card-transfer-outline class="w-5 h-5" />
                            Gestionar Cuentas Bancarias
                        </a>
                    </div>
                </div>
                @endif
                
                {{-- Sección: Opciones específicas para Datáfono --}}
                @if($paymentMethod->isCardTerminal())
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-card-outline class="w-5 h-5" />
                        Opciones para Datáfono
                    </h3>
                    
                    <div class="bg-warning-50 border border-warning-100 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <x-solar-danger-triangle-outline class="w-5 h-5 text-warning-300 mt-0.5" />
                            <div>
                                <p class="text-sm text-warning-300">
                                    Recuerda que el pago con datáfono generalmente solo está disponible para recogida en tienda.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Tarjetas aceptadas
                            </label>
                            <input type="text"
                                name="accepted_cards"
                                value="{{ old('accepted_cards', $paymentMethod->accepted_cards ?? 'Visa, Mastercard') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                placeholder="Ej: Visa, Mastercard, American Express">
                            <p class="text-xs text-black-200 mt-1">
                                Especifica qué tarjetas aceptas (opcional).
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Footer con botones --}}
            <div class="border-t border-accent-100 bg-accent-50 px-6 py-4">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('tenant.admin.payment-methods.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}"
                        class="btn-outline-secondary px-6 py-2 rounded-lg">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                        <x-solar-diskette-outline class="w-5 h-5" />
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('paymentMethodForm', () => ({
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        methodType: '{{ $paymentMethod->type }}',
        
        init() {
            // Inicialización
        },
        
        validateForm(event) {
            let valid = true;
            const form = event.target;
            
            // Validación básica
            if (!form.name.value) {
                valid = false;
                this.showNotificationMessage('Por favor, ingrese un nombre para el método de pago', 'error');
            }
            
            // Si es válido, enviar el formulario
            if (valid) {
                form.submit();
            }
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