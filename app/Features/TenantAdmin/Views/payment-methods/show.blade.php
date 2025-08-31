<x-tenant-admin-layout :store="$store">

@section('title', 'Detalles de Método de Pago')

@section('content')
<div class="container-fluid" x-data="paymentMethodDetail">
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
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center">
                @if($paymentMethod->isCash())
                    <x-solar-wallet-money-outline class="w-8 h-8 text-primary-300" />
                @elseif($paymentMethod->isBankTransfer())
                    <x-solar-card-transfer-outline class="w-8 h-8 text-primary-300" />
                @elseif($paymentMethod->isCardTerminal())
                    <x-solar-card-outline class="w-8 h-8 text-primary-300" />
                @endif
            </div>
            <div>
                <h1 class="text-lg font-bold text-black-400">
                    {{ $paymentMethod->name }}
                    @if($isDefault)
                        <span class="badge-soft-primary ml-2">Predeterminado</span>
                    @endif
                </h1>
                <div class="flex items-center gap-2">
                    <p class="text-sm text-black-300">Método de pago</p>
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('tenant.admin.payment-methods.edit', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-pen-2-outline class="w-5 h-5" />
                Editar Método
            </a>
            <a href="{{ route('tenant.admin.payment-methods.index', ['store' => $store->slug]) }}" 
                class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-5 h-5" />
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Información básica --}}
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Información Básica</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Nombre</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $paymentMethod->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Tipo</dt>
                            <dd class="mt-1 text-sm text-black-400">
                                @if($paymentMethod->isCash())
                                    Efectivo
                                @elseif($paymentMethod->isBankTransfer())
                                    Transferencia Bancaria
                                @elseif($paymentMethod->isCardTerminal())
                                    Datáfono
                                @else
                                    {{ $paymentMethod->type }}
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Estado</dt>
                            <dd class="mt-1 text-sm text-black-400 pt-2">
                                @if($paymentMethod->is_active)
                                    <span class="badge-soft-success">Activo</span>
                                @else
                                    <span class="badge-soft-secondary">Inactivo</span>
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Orden de visualización</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $paymentMethod->sort_order }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Disponible para pickup</dt>
                            <dd class="mt-1 text-sm text-black-400">
                                @if($paymentMethod->available_for_pickup)
                                    <span class="text-success-300">Sí</span>
                                @else
                                    <span class="text-error-300">No</span>
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Disponible para delivery</dt>
                            <dd class="mt-1 text-sm text-black-400">
                                @if($paymentMethod->available_for_delivery)
                                    <span class="text-success-300">Sí</span>
                                @else
                                    <span class="text-error-300">No</span>
                                @endif
                            </dd>
                        </div>
                        
                        <div class="md:col-span-2">
                            <dt class="text-sm font-semibold text-black-300">Instrucciones</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $paymentMethod->instructions ?: 'Sin instrucciones' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Cuentas bancarias (solo para transferencia) --}}
            @if($paymentMethod->isBankTransfer() && $paymentMethod->bankAccounts->isNotEmpty())
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Cuentas Bancarias</h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-accent-100">
                            <thead class="bg-accent-100">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Banco
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Número
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Titular
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-accent-50 divide-y divide-accent-100">
                                @foreach($paymentMethod->bankAccounts as $account)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-black-400">{{ $account->bank_name }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-black-400">{{ $account->account_type }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-black-400">{{ $account->account_number }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-black-400">{{ $account->account_holder }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($account->is_active)
                                                <span class="badge-soft-success">Activa</span>
                                            @else
                                                <span class="badge-soft-secondary">Inactiva</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Columna lateral --}}
        <div class="space-y-6">
            {{-- Acciones rápidas --}}
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Acciones Rápidas</h2>
                </div>
                <div class="p-6 space-y-3">
                    @if(!$isDefault)
                        <button class="w-full btn-outline-primary px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-star-outline class="w-5 h-5" />
                            Establecer como Predeterminado
                        </button>
                    @endif
                    
                    <button class="w-full btn-outline-{{ $paymentMethod->is_active ? 'warning' : 'success' }} px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                        @if($paymentMethod->is_active)
                            <x-solar-eye-closed-outline class="w-5 h-5" />
                            Desactivar Método
                        @else
                            <x-solar-eye-outline class="w-5 h-5" />
                            Activar Método
                        @endif
                    </button>
                    
                    @if($paymentMethod->isBankTransfer())
                        <a href="#" class="w-full btn-outline-primary px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-add-circle-outline class="w-5 h-5" />
                            Gestionar Cuentas Bancarias
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('paymentMethodDetail', () => ({
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        
        init() {
            // Inicialización
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