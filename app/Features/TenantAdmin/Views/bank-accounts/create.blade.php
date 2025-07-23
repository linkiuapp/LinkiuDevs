<x-tenant-admin-layout :store="$store">

@section('title', 'Nueva Cuenta Bancaria')

@section('content')
<div class="container-fluid">
    {{-- ================================================================ --}}
    {{-- HEADER Y ACCIONES PRINCIPALES --}}
    {{-- ================================================================ --}}
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Nueva Cuenta Bancaria</h1>
            <p class="text-sm text-black-300">Método de pago: {{ $paymentMethod->name }}</p>
        </div>
        <a href="{{ route('tenant.admin.payment-methods.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" 
            class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver
        </a>
    </div>

    {{-- Formulario --}}
    <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">Información de la Cuenta</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('tenant.admin.bank-accounts.store', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" method="POST">
                @csrf
                
                {{-- Sección: Información Bancaria --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-card-transfer-outline class="w-5 h-5" />
                        Información Bancaria
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Banco --}}
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Banco <span class="text-error-300">*</span>
                            </label>
                            <input type="text" name="bank_name" id="bank_name" 
                                   class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('bank_name') border-error-200 @enderror"
                                   value="{{ old('bank_name') }}" required>
                            @error('bank_name')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Tipo de Cuenta --}}
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Tipo de Cuenta <span class="text-error-300">*</span>
                            </label>
                            <select name="account_type" id="account_type" 
                                    class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('account_type') border-error-200 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($accountTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('account_type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('account_type')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Número de Cuenta --}}
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Número de Cuenta <span class="text-error-300">*</span>
                            </label>
                            <input type="text" name="account_number" id="account_number" 
                                   class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('account_number') border-error-200 @enderror"
                                   value="{{ old('account_number') }}" required
                                   pattern="[0-9]{10,20}" title="El número de cuenta debe contener solo dígitos y tener entre 10 y 20 caracteres"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <p class="text-xs text-black-300 mt-1">Solo números, entre 10 y 20 dígitos</p>
                            @error('account_number')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Titular de la Cuenta --}}
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Titular de la Cuenta <span class="text-error-300">*</span>
                            </label>
                            <input type="text" name="account_holder" id="account_holder" 
                                   class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('account_holder') border-error-200 @enderror"
                                   value="{{ old('account_holder') }}" required>
                            @error('account_holder')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Número de Documento --}}
                        <div>
                            <label class="block text-sm font-semibold text-black-300 mb-2">
                                Número de Documento
                            </label>
                            <input type="text" name="document_number" id="document_number" 
                                   class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('document_number') border-error-200 @enderror"
                                   value="{{ old('document_number') }}">
                            <p class="text-xs text-black-300 mt-1">Opcional</p>
                            @error('document_number')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Estado --}}
                        <div class="flex items-start gap-3">
                            {{-- Hidden field to ensure a value is always sent --}}
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   class="mt-1 h-4 w-4 rounded border-white-200 text-primary-200 focus:ring-primary-50"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <div>
                                <label for="is_active" class="block text-sm font-semibold text-black-300">
                                    Cuenta Activa
                                </label>
                                <p class="text-xs text-black-300">La cuenta estará disponible para recibir pagos</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <a href="{{ route('tenant.admin.bank-accounts.index', ['store' => $store->slug, 'paymentMethod' => $paymentMethod->id]) }}" 
                       class="btn-outline-secondary px-4 py-2 rounded-lg mr-2">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary px-4 py-2 rounded-lg">
                        Guardar Cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isActiveCheckbox = document.getElementById('is_active');
    const statusLabel = isActiveCheckbox.nextElementSibling.querySelector('label');
    const statusDescription = isActiveCheckbox.nextElementSibling.querySelector('p');
    
    // Function to update visual feedback based on checkbox state
    function updateCheckboxVisuals() {
        if (isActiveCheckbox.checked) {
            statusLabel.classList.remove('text-black-200');
            statusLabel.classList.add('text-black-400');
            statusDescription.textContent = 'La cuenta estará disponible para recibir pagos';
            statusDescription.classList.remove('text-black-200');
            statusDescription.classList.add('text-black-300');
        } else {
            statusLabel.classList.remove('text-black-400');
            statusLabel.classList.add('text-black-200');
            statusDescription.textContent = 'La cuenta no estará disponible para recibir pagos';
            statusDescription.classList.remove('text-black-300');
            statusDescription.classList.add('text-black-200');
        }
    }
    
    // Initialize visual state
    updateCheckboxVisuals();
    
    // Add event listener for checkbox changes
    isActiveCheckbox.addEventListener('change', updateCheckboxVisuals);
    
    // Add keyboard support for better accessibility
    isActiveCheckbox.addEventListener('keydown', function(e) {
        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            this.checked = !this.checked;
            updateCheckboxVisuals();
        }
    });
});
</script>
@endpush

</x-tenant-admin-layout>