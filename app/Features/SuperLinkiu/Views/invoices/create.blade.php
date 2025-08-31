@extends('shared::layouts.admin')

@section('title', 'Crear Nueva Factura')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg text-black-500 mb-0">Crear Nueva Factura</h1>
            <p class="text-black-300 mt-1">Genera una nueva factura para una tienda</p>
        </div>
        <a href="{{ route('superlinkiu.invoices.index') }}" class="bg-accent-100 hover:bg-accent-200 text-black-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver a Facturas
        </a>
    </div>

    <!-- Formulario -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-lg text-black-500 mb-0">Información de la Factura</h2>
        </div>
        
        <form action="{{ route('superlinkiu.invoices.store') }}" method="POST" class="p-6" x-data="createInvoice">
            @csrf
            
            <!-- Selección de Tienda y Plan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Tienda <span class="text-error-300">*</span>
                    </label>
                    <select name="store_id" 
                            x-model="selectedStore"
                            @change="updatePlanFromStore()"
                            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('store_id') border-error-200 @enderror">
                        <option value="">Seleccionar tienda</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" 
                                    data-plan-id="{{ $store->plan_id }}"
                                    data-plan-name="{{ $store->plan->name }}"
                                    {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }} - {{ $store->plan->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Plan <span class="text-error-300">*</span>
                    </label>
                    <select name="plan_id" 
                            x-model="selectedPlan"
                            @change="updateAmountFromPlan()"
                            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('plan_id') border-error-200 @enderror">
                        <option value="">Seleccionar plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" 
                                    data-prices="{{ json_encode($plan->prices) }}"
                                    {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Período y Monto -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Período <span class="text-error-300">*</span>
                    </label>
                    <select name="period" 
                            x-model="selectedPeriod"
                            @change="updateAmountFromPlan()"
                            class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('period') border-error-200 @enderror">
                        <option value="">Seleccionar período</option>
                        <option value="monthly" {{ old('period') == 'monthly' ? 'selected' : '' }}>Mensual</option>
                        <option value="quarterly" {{ old('period') == 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                        <option value="biannual" {{ old('period') == 'biannual' ? 'selected' : '' }}>Semestral</option>
                    </select>
                    @error('period')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Monto (COP) <span class="text-error-300">*</span>
                    </label>
                    <input type="number" 
                           name="amount" 
                           x-model="amount"
                           value="{{ old('amount') }}"
                           min="0"
                           step="1000"
                           class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('amount') border-error-200 @enderror"
                           placeholder="60000">
                    @error('amount')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Fecha de Emisión <span class="text-error-300">*</span>
                    </label>
                    <input type="date" 
                           name="issue_date" 
                           value="{{ old('issue_date', now()->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('issue_date') border-error-200 @enderror">
                    @error('issue_date')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Fecha de Vencimiento <span class="text-error-300">*</span>
                    </label>
                    <input type="date" 
                           name="due_date" 
                           value="{{ old('due_date', now()->addDays(15)->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('due_date') border-error-200 @enderror">
                    @error('due_date')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notas -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-black-400 mb-2">
                    Notas
                </label>
                <textarea name="notes" 
                          rows="4"
                          class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('notes') border-error-200 @enderror"
                          placeholder="Notas adicionales sobre la factura">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <!-- Resumen -->
            <div class="bg-accent-100 rounded-lg p-4 mb-8" x-show="selectedStore && selectedPlan && selectedPeriod">
                <h3 class="text-sm font-semibold text-black-400 mb-3">Resumen de la Factura</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-black-300">Tienda:</p>
                        <p class="text-sm font-medium text-black-500" x-text="getStoreName()"></p>
                    </div>
                    <div>
                        <p class="text-sm text-black-300">Plan:</p>
                        <p class="text-sm font-medium text-black-500" x-text="getPlanName()"></p>
                    </div>
                    <div>
                        <p class="text-sm text-black-300">Período:</p>
                        <p class="text-sm font-medium text-black-500" x-text="getPeriodLabel()"></p>
                    </div>
                    <div>
                        <p class="text-sm text-black-300">Monto:</p>
                        <p class="text-lg font-bold text-primary-300" x-text="getFormattedAmount()"></p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('superlinkiu.invoices.index') }}" 
                   class="bg-accent-100 hover:bg-accent-200 text-black-400 px-6 py-2 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-6 py-2 rounded-lg transition-colors">
                    Crear Factura
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('createInvoice', () => ({
        selectedStore: '{{ old('store_id') }}',
        selectedPlan: '{{ old('plan_id') }}',
        selectedPeriod: '{{ old('period') }}',
        amount: {{ old('amount', 0) }},
        stores: @json($stores),
        plans: @json($plans),
        
        updatePlanFromStore() {
            if (this.selectedStore) {
                const storeSelect = document.querySelector('select[name="store_id"]');
                const selectedOption = storeSelect.options[storeSelect.selectedIndex];
                const planId = selectedOption.dataset.planId;
                
                this.selectedPlan = planId;
                this.updateAmountFromPlan();
            }
        },
        
        updateAmountFromPlan() {
            if (this.selectedPlan && this.selectedPeriod) {
                const plan = this.plans.find(p => p.id == this.selectedPlan);
                if (plan && plan.prices && plan.prices[this.selectedPeriod]) {
                    this.amount = plan.prices[this.selectedPeriod];
                }
            }
        },
        
        getStoreName() {
            if (this.selectedStore) {
                const store = this.stores.find(s => s.id == this.selectedStore);
                return store ? store.name : '';
            }
            return '';
        },
        
        getPlanName() {
            if (this.selectedPlan) {
                const plan = this.plans.find(p => p.id == this.selectedPlan);
                return plan ? plan.name : '';
            }
            return '';
        },
        
        getPeriodLabel() {
            const labels = {
                'monthly': 'Mensual',
                'quarterly': 'Trimestral',
                'biannual': 'Semestral'
            };
            return labels[this.selectedPeriod] || '';
        },
        
        getFormattedAmount() {
            return '$' + new Intl.NumberFormat('es-CO').format(this.amount);
        }
    }));
});
</script>
@endsection 