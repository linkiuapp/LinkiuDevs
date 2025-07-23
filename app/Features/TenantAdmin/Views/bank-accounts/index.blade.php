<x-tenant-admin-layout :store="$store">

@section('title', 'Cuentas Bancarias')

@section('content')
<div class="container-fluid">
    {{-- ================================================================ --}}
    {{-- HEADER Y ACCIONES PRINCIPALES --}}
    {{-- ================================================================ --}}
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Gestión de Cuentas Bancarias</h1>
            <p class="text-sm text-black-300">Método de pago: {{ $paymentMethod->name }}</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-black-300">
                Cuentas: <span class="font-semibold">{{ $currentCount }}/{{ $maxAccounts }}</span> 
                (Plan {{ $planName }})
            </span>
            <div class="flex gap-2">
                <a href="{{ route('tenant.admin.payment-methods.show', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" 
                    class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-arrow-left-outline class="w-5 h-5" />
                    Volver
                </a>
                @if($remainingSlots > 0)
                <a href="{{ route('tenant.admin.payment-methods.bank-accounts.create', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" 
                    class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-add-circle-outline class="w-5 h-5" />
                    Nueva Cuenta
                </a>
                @else
                <button disabled 
                    class="btn-primary opacity-50 cursor-not-allowed px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-add-circle-outline class="w-5 h-5" />
                    Límite Alcanzado
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- INFORMACIÓN DE LÍMITES DEL PLAN --}}
    {{-- ================================================================ --}}
    
    <div class="bg-white-50 rounded-lg shadow-sm mb-6">
        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">Límites del Plan</h2>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Tu plan {{ $planName }} te permite tener hasta {{ $maxAccounts }} cuentas bancarias</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-primary-200">{{ $currentCount }} / {{ $maxAccounts }}</div>
                    <p class="text-sm text-black-300">Cuentas utilizadas</p>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-white-100 rounded-full h-2.5">
                    <div class="bg-primary-200 h-2.5 rounded-full" style="width: {{ ($currentCount / $maxAccounts) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- CONTENIDO PRINCIPAL - TABLA DE CUENTAS BANCARIAS --}}
    {{-- ================================================================ --}}
    
    <div class="bg-white-50 rounded-lg shadow-sm mb-6 overflow-hidden">
        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">Listado de Cuentas Bancarias</h2>
        </div>
        
        <div class="overflow-x-auto">
            @if($bankAccounts->isEmpty())
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-50 mb-4">
                        <x-solar-card-transfer-outline class="w-8 h-8 text-primary-200" />
                    </div>
                    <h3 class="text-lg font-semibold text-black-400 mb-2">No hay cuentas bancarias configuradas</h3>
                    <p class="text-sm text-black-300 mb-4">Agrega cuentas bancarias para recibir pagos por transferencia</p>
                    @if($remainingSlots > 0)
                    <a href="{{ route('tenant.admin.bank-accounts.create', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" 
                        class="btn-primary px-4 py-2 rounded-lg inline-flex items-center gap-2">
                        <x-solar-add-circle-outline class="w-5 h-5" />
                        Agregar Cuenta Bancaria
                    </a>
                    @else
                    <div class="text-warning-300 mb-2">Has alcanzado el límite de cuentas para tu plan actual</div>
                    <a href="#" class="btn-outline-primary px-4 py-2 rounded-lg inline-flex items-center gap-2">
                        <x-solar-star-outline class="w-5 h-5" />
                        Actualiza tu Plan
                    </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white-100">
                        <thead class="bg-white-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Cuenta Bancaria
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Información
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Titular
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white-50 divide-y divide-white-100">
                            @foreach($bankAccounts as $account)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-50 flex items-center justify-center">
                                                <x-solar-card-transfer-outline class="w-5 h-5 text-primary-200" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-black-400">{{ $account->bank_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-black-400">
                                            @if($account->account_type == 'savings')
                                                Cuenta de Ahorros
                                            @elseif($account->account_type == 'checking')
                                                Cuenta Corriente
                                            @else
                                                {{ $account->account_type }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-black-400">{{ $account->getFormattedAccountNumber() }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-black-400">{{ $account->getAccountHolderWithDocument() }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($account->is_active)
                                            <span class="badge-soft-success">Activa</span>
                                        @else
                                            <span class="badge-soft-secondary">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" 
                                                    class="{{ $account->is_active ? 'text-success-200 hover:text-success-300' : 'text-secondary-200 hover:text-secondary-300' }}"
                                                    onclick="confirmToggleActive('{{ route('tenant.admin.payment-methods.bank-accounts.toggle-active', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id, 'bankAccount' => $account->id]) }}', '{{ $account->is_active ? 'desactivar' : 'activar' }}')">
                                                @if($account->is_active)
                                                    <x-solar-eye-outline class="w-5 h-5" />
                                                @else
                                                    <x-solar-eye-closed-outline class="w-5 h-5" />
                                                @endif
                                            </button>
                                            <a href="{{ route('tenant.admin.payment-methods.bank-accounts.edit', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id, 'bankAccount' => $account->id]) }}" 
                                               class="text-warning-200 hover:text-warning-300">
                                                <x-solar-pen-2-outline class="w-5 h-5" />
                                            </a>
                                            <button type="button" 
                                                    class="text-error-200 hover:text-error-300"
                                                    onclick="confirmDelete('{{ route('tenant.admin.payment-methods.bank-accounts.destroy', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id, 'bankAccount' => $account->id]) }}')">
                                                <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($remainingSlots <= 0)
                <div class="mt-6 p-4 bg-warning-50 border border-warning-100 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <x-solar-danger-triangle-outline class="w-5 h-5 text-warning-300" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-black-400">Has alcanzado el límite de cuentas bancarias</h4>
                            <p class="text-xs text-black-300 mt-1">Tu plan actual permite un máximo de {{ $maxAccounts }} cuentas bancarias. Actualiza tu plan para agregar más cuentas.</p>
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(url) {
    if (confirm('¿Estás seguro de que deseas eliminar esta cuenta bancaria? Esta acción no se puede deshacer.')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function confirmToggleActive(url, action) {
    if (confirm(`¿Estás seguro de que deseas ${action} esta cuenta bancaria?`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
</x-tenant-admin-layout>