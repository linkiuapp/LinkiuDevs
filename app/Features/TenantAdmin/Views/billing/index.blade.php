<x-tenant-admin-layout :store="$store">
    @section('title', 'Plan y Facturación')
    @section('subtitle', 'Gestiona tu suscripción, plan y facturación')
    
    @section('content')
    <div x-data="billingManager" class="space-y-8">
        
        <!-- ================================================================= -->
        <!-- SECCIÓN 1: MI PLAN ACTUAL -->
        <!-- ================================================================= -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg text-black-500 mb-0 font-semibold">Mi Plan Actual</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Info del Plan -->
                <div class="bg-gradient-to-r from-primary-50 to-secondary-50 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-black-500">
                                Plan {{ $subscription->plan->name }} - 
                                {{ $subscription->plan->getFormattedPriceForPeriod($subscription->billing_cycle) }}/{{ $subscription->billing_cycle_label }}
                            </h3>
                            <p class="text-sm text-black-300 mt-1">
                                Renovación: {{ $nextInvoice['date']->format('d \\d\\e F, Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                @if($subscription->is_active) bg-success-300 text-accent-50
                                @elseif($subscription->is_cancelled) bg-warning-300 text-black-500
                                @else bg-error-300 text-accent-50 @endif">
                                @if($subscription->is_active) 
                                    <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                    {{ $subscription->status_label }}
                                @elseif($subscription->is_cancelled)
                                    <x-solar-clock-circle-outline class="w-3 h-3 mr-1" />
                                    {{ $subscription->status_label }}
                                @else
                                    <x-solar-close-circle-outline class="w-3 h-3 mr-1" />
                                    {{ $subscription->status_label }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Uso Actual vs Límites -->
                <div>
                    <h4 class="text-sm font-medium text-black-400 mb-4">Uso Actual vs Límites:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Productos -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-black-500">Productos</span>
                                <span class="text-black-300">{{ $usage['products'] }}/{{ $limits['products'] }} ({{ $percentages['products'] }}%)</span>
                            </div>
                            <div class="w-full bg-accent-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300 
                                    @if($percentages['products'] >= 90) bg-error-300
                                    @elseif($percentages['products'] >= 70) bg-warning-300
                                    @else bg-primary-300 @endif" 
                                     style="width: {{ $percentages['products'] }}%"></div>
                            </div>
                        </div>

                        <!-- Categorías -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-black-500">Categorías</span>
                                <span class="text-black-300">{{ $usage['categories'] }}/{{ $limits['categories'] }} ({{ $percentages['categories'] }}%)</span>
                            </div>
                            <div class="w-full bg-accent-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300 
                                    @if($percentages['categories'] >= 90) bg-error-300
                                    @elseif($percentages['categories'] >= 70) bg-warning-300
                                    @else bg-success-300 @endif" 
                                     style="width: {{ $percentages['categories'] }}%"></div>
                            </div>
                        </div>

                        <!-- Variables -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-black-500">Variables</span>
                                <span class="text-black-300">{{ $usage['variables'] }}/{{ $limits['variables'] }} ({{ $percentages['variables'] }}%)</span>
                            </div>
                            <div class="w-full bg-accent-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300 
                                    @if($percentages['variables'] >= 90) bg-error-300
                                    @elseif($percentages['variables'] >= 70) bg-warning-300
                                    @else bg-info-300 @endif" 
                                     style="width: {{ $percentages['variables'] }}%"></div>
                            </div>
                        </div>

                        <!-- Sliders -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-black-500">Sliders</span>
                                <span class="text-black-300">{{ $usage['sliders'] }}/{{ $limits['sliders'] }} ({{ $percentages['sliders'] }}%)</span>
                            </div>
                            <div class="w-full bg-accent-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300 
                                    @if($percentages['sliders'] >= 90) bg-error-300
                                    @elseif($percentages['sliders'] >= 70) bg-warning-300
                                    @else bg-secondary-300 @endif" 
                                     style="width: {{ $percentages['sliders'] }}%"></div>
                            </div>
                        </div>

                        <!-- Sedes -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-black-500">Sedes</span>
                                <span class="text-black-300">{{ $usage['locations'] }}/{{ $limits['locations'] }} ({{ $percentages['locations'] }}%)</span>
                            </div>
                            <div class="w-full bg-accent-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300 
                                    @if($percentages['locations'] >= 90) bg-error-300
                                    @elseif($percentages['locations'] >= 70) bg-warning-300
                                    @else bg-primary-300 @endif" 
                                     style="width: {{ $percentages['locations'] }}%"></div>
                            </div>
                        </div>

                        <!-- Cuentas Bancarias -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-black-500">Cuentas Bancarias</span>
                                <span class="text-black-300">{{ $usage['bank_accounts'] }}/{{ $limits['bank_accounts'] }} ({{ $percentages['bank_accounts'] }}%)</span>
                            </div>
                            <div class="w-full bg-accent-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300 
                                    @if($percentages['bank_accounts'] >= 90) bg-error-300
                                    @elseif($percentages['bank_accounts'] >= 70) bg-warning-300
                                    @else bg-success-300 @endif" 
                                     style="width: {{ $percentages['bank_accounts'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Características del Plan -->
                <div>
                    <h4 class="text-sm font-medium text-black-400 mb-3">Características del Plan:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @if($subscription->plan->allow_custom_slug)
                            <div class="flex items-center text-sm text-success-400">
                                <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                                Slug personalizado
                            </div>
                        @endif
                        
                        <div class="flex items-center text-sm text-success-400">
                            <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                            Soporte {{ $subscription->plan->support_level ?? 'básico' }}
                        </div>
                        
                        @if($subscription->plan->support_response_time)
                            <div class="flex items-center text-sm text-success-400">
                                <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                                Respuesta en {{ $subscription->plan->support_response_time }}
                            </div>
                        @endif
                        
                        <div class="flex items-center text-sm text-success-400">
                            <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                            Hasta {{ $subscription->plan->max_admins }} administradores
                        </div>
                    </div>
                </div>

                <!-- Acciones del Plan -->
                <div class="flex flex-wrap gap-3 pt-4 border-t border-accent-100">
                    <button @click="showChangePlanModal = true" 
                            class="btn-primary">
                        <x-solar-arrow-up-outline class="w-4 h-4 mr-2" />
                        Cambiar Plan
                    </button>
                    
                    <a href="{{ route('superlinkiu.plans.index') }}" 
                       target="_blank"
                       class="btn-secondary">
                        <x-solar-eye-outline class="w-4 h-4 mr-2" />
                        Ver Todos los Planes
                    </a>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- SECCIÓN 2: FACTURACIÓN -->
        <!-- ================================================================= -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg text-black-500 mb-0 font-semibold">Facturación</h2>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Próxima Facturación -->
                <div class="bg-info-50 border border-info-100 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-info-300 mb-3">Próxima Facturación:</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-black-400">Fecha:</span>
                            <span class="text-black-500">{{ $nextInvoice['date']->format('d \\d\\e F, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black-400">Monto:</span>
                            <span class="text-black-500 font-semibold">${{ number_format($nextInvoice['amount'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black-400">Plan:</span>
                            <span class="text-black-500">{{ $nextInvoice['plan'] }} ({{ $nextInvoice['cycle'] }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-black-400">Estado:</span>
                            <span class="text-info-300">Programada</span>
                        </div>
                    </div>
                </div>

                <!-- Historial de Facturas -->
                <div>
                    <h4 class="text-sm font-medium text-black-400 mb-4">Historial de Facturas:</h4>
                    
                    @if($recentInvoices->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentInvoices as $invoice)
                                <div class="flex items-center justify-between p-4 border border-accent-100 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-1">
                                            <h5 class="text-sm font-semibold text-black-500">#{{ $invoice->invoice_number }}</h5>
                                            <span class="text-xs px-2 py-1 rounded-full 
                                                @if($invoice->status === 'paid') bg-success-300 text-accent-50
                                                @elseif($invoice->status === 'pending') bg-warning-300 text-black-500
                                                @else bg-error-300 text-accent-50 @endif">
                                                @if($invoice->status === 'paid') Pagada
                                                @elseif($invoice->status === 'pending') Pendiente
                                                @elseif($invoice->status === 'overdue') Vencida
                                                @else Cancelada @endif
                                            </span>
                                        </div>
                                        <div class="text-xs text-black-300">
                                            {{ $invoice->issue_date->format('d/m/Y') }} • Plan {{ $invoice->plan->name }} • ${{ number_format($invoice->amount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    
                                    @if($invoice->status === 'paid')
                                        <a href="{{ route('tenant.admin.billing.download-invoice', [$store->slug, $invoice->id]) }}" 
                                           class="text-primary-200 hover:text-primary-300 p-2 rounded-lg hover:bg-accent-100 transition-colors">
                                            <x-solar-download-outline class="w-4 h-4" />
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-black-300">
                            <x-solar-document-outline class="w-12 h-12 mx-auto mb-3 text-black-200" />
                            <p>No hay facturas disponibles</p>
                        </div>
                    @endif
                </div>

                <!-- Ciclo de Facturación -->
                <div>
                    <h4 class="text-sm font-medium text-black-400 mb-3">Ciclo de Facturación:</h4>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="billing_cycle" 
                                   value="monthly" 
                                   @if($subscription->billing_cycle === 'monthly') checked @endif
                                   class="text-primary-200 focus:ring-primary-200">
                            <span class="ml-2 text-sm text-black-500">
                                Mensual ({{ $subscription->plan->getFormattedPriceForPeriod('monthly') }})
                            </span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="billing_cycle" 
                                   value="quarterly" 
                                   @if($subscription->billing_cycle === 'quarterly') checked @endif
                                   class="text-primary-200 focus:ring-primary-200">
                            <span class="ml-2 text-sm text-black-500">
                                Trimestral ({{ $subscription->plan->getFormattedPriceForPeriod('quarterly') }})
                                @if($subscription->plan->getDiscountForPeriod('quarterly') > 0)
                                    <span class="text-success-400 text-xs">- Ahorra {{ $subscription->plan->getDiscountForPeriod('quarterly') }}%</span>
                                @endif
                            </span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="billing_cycle" 
                                   value="biannual" 
                                   @if($subscription->billing_cycle === 'biannual') checked @endif
                                   class="text-primary-200 focus:ring-primary-200">
                            <span class="ml-2 text-sm text-black-500">
                                Semestral ({{ $subscription->plan->getFormattedPriceForPeriod('biannual') }})
                                @if($subscription->plan->getDiscountForPeriod('biannual') > 0)
                                    <span class="text-success-400 text-xs">- Ahorra {{ $subscription->plan->getDiscountForPeriod('biannual') }}%</span>
                                @endif
                            </span>
                        </label>
                    </div>
                    
                    <div class="mt-4">
                        <button @click="showChangeCycleModal = true" 
                                class="btn-secondary">
                            <x-solar-refresh-outline class="w-4 h-4 mr-2" />
                            Cambiar Ciclo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- SECCIÓN 3: GESTIÓN DE SUSCRIPCIÓN -->
        <!-- ================================================================= -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg text-black-500 mb-0 font-semibold">Gestión de Suscripción</h2>
            </div>
            
            <div class="p-6 space-y-6">
                
                <!-- Estado de la Suscripción -->
                <div class="bg-gradient-to-r from-primary-50 to-accent-50 border border-primary-100 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-primary-300 mb-1">Estado de la Suscripción</h4>
                            <p class="text-sm text-black-400">
                                @if($subscription->is_active)
                                    Tu suscripción está activa y se renovará automáticamente
                                @elseif($subscription->is_cancelled)
                                    Suscripción cancelada - Acceso hasta {{ $subscription->current_period_end->format('d/m/Y') }}
                                @else
                                    Tu suscripción necesita atención
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            @if($subscription->days_until_expiration > 0)
                                <div class="text-xs text-black-300">
                                    {{ $subscription->days_until_expiration }} días restantes
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Acciones de Plan -->
                @if($subscription->is_active)
                    <div>
                        <h4 class="text-sm font-medium text-black-400 mb-3">Cambiar Plan:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach($availablePlans as $plan)
                                @if($plan->id !== $subscription->plan_id)
                                    <div class="p-4 border border-accent-200 rounded-lg hover:border-primary-200 transition-colors">
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="text-sm font-semibold text-black-500">{{ $plan->name }}</h5>
                                            @if($plan->price > $subscription->plan->price)
                                                <span class="text-xs bg-success-100 text-success-400 px-2 py-1 rounded">Upgrade</span>
                                            @elseif($plan->price < $subscription->plan->price)
                                                <span class="text-xs bg-warning-100 text-warning-400 px-2 py-1 rounded">Downgrade</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-black-300 mb-3">
                                            {{ $plan->getFormattedPriceForPeriod($subscription->billing_cycle) }}/{{ $subscription->billing_cycle_label }}
                                        </div>
                                        <button @click="selectPlan({{ $plan->id }}, '{{ $plan->name }}')" 
                                                class="w-full text-xs px-3 py-2 border border-primary-200 text-primary-200 rounded hover:bg-primary-50 transition-colors">
                                            @if($plan->price > $subscription->plan->price) Actualizar
                                            @elseif($plan->price < $subscription->plan->price) Cambiar
                                            @else Cambiar @endif
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Historial de Cambios -->
                @if($recentChanges->count() > 0)
                    <div>
                        <h4 class="text-sm font-medium text-black-400 mb-3">Historial de Cambios:</h4>
                        <div class="space-y-2">
                            @foreach($recentChanges->take(5) as $change)
                                <div class="flex items-center justify-between py-2 px-3 bg-accent-100 rounded">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full 
                                            @if($change->is_upgrade) bg-success-300
                                            @elseif($change->is_downgrade) bg-warning-300
                                            @else bg-info-300 @endif"></div>
                                        <span class="text-sm text-black-500">{{ $change->change_description }}</span>
                                    </div>
                                    <span class="text-xs text-black-300">{{ $change->changed_at->format('d/m/Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Acciones de Suscripción -->
                <div class="pt-4 border-t border-accent-100">
                    @if($subscription->is_active)
                        <div class="bg-error-50 border border-error-100 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-error-300 mb-2">⚠️ Cancelar Suscripción</h4>
                            <p class="text-xs text-error-200 mb-3">
                                Tu tienda se mantendrá activa hasta el {{ $subscription->current_period_end->format('d/m/Y') }}.
                                Después pasará al plan Explorer automáticamente. Podrás reactivar en cualquier momento.
                            </p>
                            <button @click="showCancelModal = true" 
                                    class="text-xs px-4 py-2 bg-error-300 text-accent-50 rounded hover:bg-error-400 transition-colors">
                                Cancelar Suscripción
                            </button>
                        </div>
                    @elseif($subscription->can_reactivate)
                        <div class="bg-success-50 border border-success-100 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-success-300 mb-2">🔄 Reactivar Suscripción</h4>
                            <p class="text-xs text-success-200 mb-3">
                                Puedes reactivar tu suscripción y continuar con todas las funcionalidades.
                            </p>
                            <button @click="showReactivateModal = true" 
                                    class="text-xs px-4 py-2 bg-success-300 text-accent-50 rounded hover:bg-success-400 transition-colors">
                                Reactivar Suscripción
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODALES -->
        <!-- ================================================================= -->

        <!-- Modal Cambiar Plan -->
        @include('tenant-admin::billing.modals.change-plan')

        <!-- Modal Cambiar Ciclo -->
        @include('tenant-admin::billing.modals.change-cycle')

        <!-- Modal Cancelar Suscripción -->
        @include('tenant-admin::billing.modals.cancel-subscription')

        <!-- Modal Reactivar Suscripción -->
        @include('tenant-admin::billing.modals.reactivate-subscription')

    </div>
    @endsection

    @push('scripts')
    <script>
    function billingManager() {
        return {
            // Modal states
            showChangePlanModal: false,
            showChangeCycleModal: false,
            showCancelModal: false,
            showReactivateModal: false,
            
            // Form data
            selectedPlanId: null,
            selectedPlanName: '',
            password: '',
            reason: '',
            isLoading: false,
            
            // Select plan for change
            selectPlan(planId, planName) {
                this.selectedPlanId = planId;
                this.selectedPlanName = planName;
                this.showChangePlanModal = true;
            },
            
            // Change plan
            async changePlan() {
                if (!this.selectedPlanId || !this.password) return;
                
                this.isLoading = true;
                
                try {
                    const response = await fetch('{{ route("tenant.admin.billing.change-plan", $store->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            plan_id: this.selectedPlanId,
                            reason: this.reason,
                            password: this.password
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showToast(data.message, 'error');
                    }
                } catch (error) {
                    this.showToast('Error al cambiar el plan', 'error');
                } finally {
                    this.isLoading = false;
                }
            },
            
            // Change billing cycle
            async changeBillingCycle() {
                const selectedCycle = document.querySelector('input[name="billing_cycle"]:checked')?.value;
                if (!selectedCycle || !this.password) return;
                
                this.isLoading = true;
                
                try {
                    const response = await fetch('{{ route("tenant.admin.billing.change-billing-cycle", $store->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            billing_cycle: selectedCycle,
                            password: this.password
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showToast(data.message, 'error');
                    }
                } catch (error) {
                    this.showToast('Error al cambiar el ciclo de facturación', 'error');
                } finally {
                    this.isLoading = false;
                }
            },
            
            // Cancel subscription
            async cancelSubscription() {
                if (!this.reason || !this.password) return;
                
                this.isLoading = true;
                
                try {
                    const response = await fetch('{{ route("tenant.admin.billing.cancel-subscription", $store->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            reason: this.reason,
                            password: this.password
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        this.showToast(data.message, 'error');
                    }
                } catch (error) {
                    this.showToast('Error al cancelar la suscripción', 'error');
                } finally {
                    this.isLoading = false;
                }
            },
            
            // Reactivate subscription
            async reactivateSubscription() {
                if (!this.password) return;
                
                this.isLoading = true;
                
                try {
                    const response = await fetch('{{ route("tenant.admin.billing.reactivate-subscription", $store->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            password: this.password
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showToast(data.message, 'error');
                    }
                } catch (error) {
                    this.showToast('Error al reactivar la suscripción', 'error');
                } finally {
                    this.isLoading = false;
                }
            },
            
            // Reset modal data
            resetModalData() {
                this.password = '';
                this.reason = '';
                this.selectedPlanId = null;
                this.selectedPlanName = '';
                this.isLoading = false;
            },
            
            // Show toast notification
            showToast(message, type = 'info') {
                // Simple alert for now - can be enhanced later
                alert(`${type.toUpperCase()}: ${message}`);
            }
        }));
    });
</script>
    @endpush
</x-tenant-admin-layout> 