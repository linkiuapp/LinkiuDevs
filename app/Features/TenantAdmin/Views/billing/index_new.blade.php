<x-tenant-admin-layout :store="$store">
    @section('title', 'Plan y Facturación')
    @section('subtitle', 'Gestiona tu suscripción, plan y facturación')
    
    @section('content')
    <div x-data="billingManager" class="space-y-6">
        <!-- Navegación de Tabs -->
        <div class="bg-accent-50 rounded-lg overflow-hidden">
            <div class="border-b border-accent-100">
                <nav class="flex space-x-8 px-6 py-4" aria-label="Tabs">
                    <button @click="activeTab = 'plan'" 
                            :class="activeTab === 'plan' ? 'border-primary-200 text-primary-300 bg-primary-50' : 'border-transparent text-black-300 hover:text-black-400 hover:border-accent-200'"
                            class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200">
                        <x-solar-crown-outline class="w-4 h-4 inline mr-2" />
                        Mi Plan Actual
                    </button>
                    <button @click="activeTab = 'usage'" 
                            :class="activeTab === 'usage' ? 'border-primary-200 text-primary-300 bg-primary-50' : 'border-transparent text-black-300 hover:text-black-400 hover:border-accent-200'"
                            class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200">
                        <x-solar-chart-2-outline class="w-4 h-4 inline mr-2" />
                        Uso y Límites
                    </button>
                    <button @click="activeTab = 'invoices'" 
                            :class="activeTab === 'invoices' ? 'border-primary-200 text-primary-300 bg-primary-50' : 'border-transparent text-black-300 hover:text-black-400 hover:border-accent-200'"
                            class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200">
                        <x-solar-document-text-outline class="w-4 h-4 inline mr-2" />
                        Historial de Facturación
                    </button>
                    <button @click="activeTab = 'change'" 
                            :class="activeTab === 'change' ? 'border-primary-200 text-primary-300 bg-primary-50' : 'border-transparent text-black-300 hover:text-black-400 hover:border-accent-200'"
                            class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200">
                        <x-solar-refresh-outline class="w-4 h-4 inline mr-2" />
                        Solicitar Cambio
                    </button>
                </nav>
            </div>

            <!-- Contenido de Tabs -->
            <div class="p-6">
                <!-- =============================================== -->
                <!-- TAB 1: MI PLAN ACTUAL -->
                <!-- =============================================== -->
                <div x-show="activeTab === 'plan'" x-transition class="space-y-6">
                    <!-- Hero del Plan -->
                    <div class="bg-gradient-to-r from-primary-50 to-secondary-50 rounded-lg p-6">
                        <div class="flex items-center gap-6">
                            <!-- Imagen del Plan -->
                            <div class="flex-shrink-0">
                                <img src="{{ $subscription->plan->image_url ?? asset('assets/images/img_plan_default.png') }}" 
                                     alt="Plan {{ $subscription->plan->name }}"
                                     class="w-20 h-20 rounded-lg object-cover border-2 border-primary-100">
                            </div>
                            
                            <!-- Info del Plan -->
                            <div class="flex-grow">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xl font-bold text-black-500">
                                        Plan {{ $subscription->plan->name }}
                                    </h3>
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
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-black-300">Precio:</span>
                                        <p class="font-semibold text-black-500">
                                            {{ $subscription->plan->getFormattedPriceForPeriod($subscription->billing_cycle) }}/{{ $subscription->billing_cycle_label }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-black-300">Próxima renovación:</span>
                                        <p class="font-semibold text-black-500">
                                            {{ $nextInvoice['date']->format('d \\d\\e F, Y') }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-black-300">Ciclo de facturación:</span>
                                        <p class="font-semibold text-black-500">{{ ucfirst($subscription->billing_cycle_label) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Características del Plan -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-black-500 mb-4">Características Incluidas</h4>
                        @if($subscription->plan->features_list)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($subscription->plan->features_list as $feature)
                                <div class="flex items-center gap-2">
                                    <x-solar-check-circle-outline class="w-4 h-4 text-success-300 flex-shrink-0" />
                                    <span class="text-black-400">{{ $feature }}</span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-black-300">No hay características específicas configuradas para este plan.</p>
                        @endif
                    </div>

                    <!-- Resumen Rápido de Uso -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-black-500 mb-4">Resumen de Uso</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-3 bg-accent-50 rounded-lg border border-accent-100">
                                <div class="text-2xl font-bold text-primary-300">{{ $planUsage['products']['current'] ?? 0 }}</div>
                                <div class="text-xs text-black-300">de {{ $planUsage['products']['limit'] ?? 0 }} productos</div>
                            </div>
                            <div class="text-center p-3 bg-accent-50 rounded-lg border border-accent-100">
                                <div class="text-2xl font-bold text-primary-300">{{ $planUsage['categories']['current'] ?? 0 }}</div>
                                <div class="text-xs text-black-300">de {{ $planUsage['categories']['limit'] ?? 0 }} categorías</div>
                            </div>
                            <div class="text-center p-3 bg-accent-50 rounded-lg border border-accent-100">
                                <div class="text-2xl font-bold text-primary-300">{{ $planUsage['sliders']['current'] ?? 0 }}</div>
                                <div class="text-xs text-black-300">de {{ $planUsage['sliders']['limit'] ?? 0 }} sliders</div>
                            </div>
                            <div class="text-center p-3 bg-accent-50 rounded-lg border border-accent-100">
                                <div class="text-2xl font-bold text-primary-300">{{ $planUsage['locations']['current'] ?? 0 }}</div>
                                <div class="text-xs text-black-300">de {{ $planUsage['locations']['limit'] ?? 0 }} sedes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =============================================== -->
                <!-- TAB 2: USO Y LÍMITES -->
                <!-- =============================================== -->
                <div x-show="activeTab === 'usage'" x-transition class="space-y-6">
                    <!-- Header del Tab -->
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-semibold text-black-500">Uso y Límites del Plan</h4>
                        <div class="text-sm text-black-300">
                            Uso general: <span class="font-semibold text-primary-300">{{ $planUsage['overall_percentage'] ?? 0 }}%</span>
                        </div>
                    </div>

                    <!-- Productos y Catálogo -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-black-500 mb-4 flex items-center gap-2">
                            <x-solar-box-outline class="w-5 h-5 text-primary-300" />
                            📦 Productos y Catálogo
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Productos -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Productos totales</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['products']['current'] ?? 0 }}/{{ $planUsage['products']['limit'] ?? 0 }}
                                        ({{ $planUsage['products']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['products']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['products']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-primary-300 @endif" 
                                         style="width: {{ $planUsage['products']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Categorías -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Categorías</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['categories']['current'] ?? 0 }}/{{ $planUsage['categories']['limit'] ?? 0 }}
                                        ({{ $planUsage['categories']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['categories']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['categories']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-success-300 @endif" 
                                         style="width: {{ $planUsage['categories']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Variables -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Variables (tallas, colores)</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['variables']['current'] ?? 0 }}/{{ $planUsage['variables']['limit'] ?? 0 }}
                                        ({{ $planUsage['variables']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['variables']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['variables']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-info-300 @endif" 
                                         style="width: {{ $planUsage['variables']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Imágenes por producto -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Imágenes por producto</span>
                                    <span class="text-sm font-medium text-black-400">
                                        Límite: {{ $planUsage['product_images']['limit'] ?? 0 }} img/producto
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full bg-success-300" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Diseño y Marketing -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-black-500 mb-4 flex items-center gap-2">
                            <x-solar-palette-outline class="w-5 h-5 text-secondary-300" />
                            🎨 Diseño y Marketing
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Sliders -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Sliders en homepage</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['sliders']['current'] ?? 0 }}/{{ $planUsage['sliders']['limit'] ?? 0 }}
                                        ({{ $planUsage['sliders']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['sliders']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['sliders']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-secondary-300 @endif" 
                                         style="width: {{ $planUsage['sliders']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Cupones -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Cupones activos</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['coupons']['current'] ?? 0 }}/{{ $planUsage['coupons']['limit'] ?? 0 }}
                                        ({{ $planUsage['coupons']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['coupons']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['coupons']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-warning-300 @endif" 
                                         style="width: {{ $planUsage['coupons']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Envíos y Logística -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-black-500 mb-4 flex items-center gap-2">
                            <x-solar-delivery-outline class="w-5 h-5 text-info-300" />
                            🚚 Envíos y Logística
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Sedes -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Sedes físicas</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['locations']['current'] ?? 0 }}/{{ $planUsage['locations']['limit'] ?? 0 }}
                                        ({{ $planUsage['locations']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['locations']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['locations']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-info-300 @endif" 
                                         style="width: {{ $planUsage['locations']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Zonas de envío -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Zonas de envío</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['delivery_zones']['current'] ?? 0 }}/{{ $planUsage['delivery_zones']['limit'] ?? 0 }}
                                        ({{ $planUsage['delivery_zones']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['delivery_zones']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['delivery_zones']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-info-300 @endif" 
                                         style="width: {{ $planUsage['delivery_zones']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagos -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-black-500 mb-4 flex items-center gap-2">
                            <x-solar-card-outline class="w-5 h-5 text-success-300" />
                            💰 Pagos
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Métodos de pago -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Métodos de pago activos</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['payment_methods']['current'] ?? 0 }}/{{ $planUsage['payment_methods']['limit'] ?? 0 }}
                                        ({{ $planUsage['payment_methods']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['payment_methods']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['payment_methods']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-success-300 @endif" 
                                         style="width: {{ $planUsage['payment_methods']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Cuentas bancarias -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Cuentas bancarias</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['bank_accounts']['current'] ?? 0 }}/{{ $planUsage['bank_accounts']['limit'] ?? 0 }}
                                        ({{ $planUsage['bank_accounts']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['bank_accounts']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['bank_accounts']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-success-300 @endif" 
                                         style="width: {{ $planUsage['bank_accounts']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Administración y Soporte -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-black-500 mb-4 flex items-center gap-2">
                            <x-solar-users-group-two-rounded-outline class="w-5 h-5 text-warning-300" />
                            👥 Administración y Soporte
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Administradores -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Administradores</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['admins']['current'] ?? 0 }}/{{ $planUsage['admins']['limit'] ?? 0 }}
                                        ({{ $planUsage['admins']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['admins']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['admins']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-warning-300 @endif" 
                                         style="width: {{ $planUsage['admins']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Tickets de soporte -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Tickets este mes</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['tickets']['current'] ?? 0 }}/{{ $planUsage['tickets']['limit'] ?? 0 }}
                                        ({{ $planUsage['tickets']['percentage'] ?? 0 }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-300 
                                        @if(($planUsage['tickets']['percentage'] ?? 0) >= 90) bg-error-300
                                        @elseif(($planUsage['tickets']['percentage'] ?? 0) >= 70) bg-warning-300
                                        @else bg-warning-300 @endif" 
                                         style="width: {{ $planUsage['tickets']['percentage'] ?? 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Historial de pedidos -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-500">Historial disponible</span>
                                    <span class="text-sm font-medium text-black-400">
                                        {{ $planUsage['order_history']['current'] ?? 0 }} meses
                                    </span>
                                </div>
                                <div class="w-full bg-accent-200 rounded-full h-3">
                                    <div class="h-3 rounded-full bg-primary-300" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =============================================== -->
                <!-- TAB 3: HISTORIAL DE FACTURACIÓN -->
                <!-- =============================================== -->
                <div x-show="activeTab === 'invoices'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-semibold text-black-500">Historial de Facturación</h4>
                        <div class="text-sm text-black-300">
                            Últimas facturas generadas
                        </div>
                    </div>

                    <!-- Tabla de Facturas -->
                    <div class="bg-accent-50 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-accent-200">
                                <thead class="bg-accent-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                            Factura
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                            Plan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                            Período
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                            Monto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-accent-50 divide-y divide-accent-200">
                                    @forelse($invoices ?? [] as $invoice)
                                    <tr class="hover:bg-accent-100 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black-500">
                                            #{{ $invoice->invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black-400">
                                            {{ $invoice->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black-400">
                                            {{ $invoice->plan_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black-400">
                                            {{ $invoice->billing_period }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black-500">
                                            ${{ number_format($invoice->amount, 0, ',', '.') }} COP
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($invoice->status === 'paid') bg-success-100 text-success-300
                                                @elseif($invoice->status === 'pending') bg-warning-100 text-warning-300
                                                @else bg-error-100 text-error-300 @endif">
                                                {{ ucfirst($invoice->status_label) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black-400">
                                            <button class="text-primary-300 hover:text-primary-400 mr-3">
                                                <x-solar-download-minimalistic-outline class="w-4 h-4" />
                                            </button>
                                            <button class="text-info-300 hover:text-info-400">
                                                <x-solar-eye-outline class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-black-300">
                                            <div class="flex flex-col items-center">
                                                <x-solar-document-text-outline class="w-8 h-8 text-black-200 mb-2" />
                                                <p>No hay facturas disponibles</p>
                                                <p class="text-xs mt-1">Las facturas aparecerán aquí cuando se generen</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Información de Facturación -->
                    <div class="bg-accent-50 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-black-500 mb-4">Información de Facturación</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h6 class="text-sm font-medium text-black-400 mb-2">Datos de la Empresa</h6>
                                <div class="space-y-1 text-sm text-black-400">
                                    <p><strong>Razón Social:</strong> {{ $store->business_name ?? $store->name }}</p>
                                    <p><strong>NIT/CC:</strong> {{ $store->tax_id ?? 'No configurado' }}</p>
                                    <p><strong>Dirección:</strong> {{ $store->address ?? 'No configurada' }}</p>
                                    <p><strong>Email:</strong> {{ $store->email }}</p>
                                </div>
                            </div>
                            <div>
                                <h6 class="text-sm font-medium text-black-400 mb-2">Método de Pago</h6>
                                <div class="space-y-1 text-sm text-black-400">
                                    <p><strong>Tipo:</strong> {{ $subscription->payment_method ?? 'Transferencia Bancaria' }}</p>
                                    <p><strong>Frecuencia:</strong> {{ ucfirst($subscription->billing_cycle_label) }}</p>
                                    <p><strong>Próximo cobro:</strong> {{ $nextInvoice['date']->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =============================================== -->
                <!-- TAB 4: SOLICITAR CAMBIO -->
                <!-- =============================================== -->
                <div x-show="activeTab === 'change'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-semibold text-black-500">Solicitar Cambio de Plan</h4>
                        <div class="text-sm text-black-300">
                            Upgrade o downgrade disponible
                        </div>
                    </div>

                    <!-- Planes Disponibles -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($availablePlans ?? [] as $plan)
                        <div class="bg-accent-50 rounded-lg p-6 border-2 
                            @if($plan->id === $subscription->plan_id) border-primary-200 bg-primary-50
                            @else border-accent-200 hover:border-primary-200 @endif 
                            transition-all duration-200 cursor-pointer"
                             @if($plan->id !== $subscription->plan_id) @click="selectPlanForChange({{ $plan->id }}, '{{ $plan->name }}')" @endif>
                            
                            <!-- Imagen del Plan -->
                            <div class="text-center mb-4">
                                <img src="{{ $plan->image_url ?? asset('assets/images/img_plan_default.png') }}" 
                                     alt="Plan {{ $plan->name }}"
                                     class="w-16 h-16 mx-auto rounded-lg object-cover">
                            </div>
                            
                            <!-- Info del Plan -->
                            <div class="text-center">
                                <h5 class="text-lg font-semibold text-black-500 mb-2">
                                    Plan {{ $plan->name }}
                                    @if($plan->id === $subscription->plan_id)
                                        <span class="text-xs bg-primary-200 text-accent-50 px-2 py-1 rounded-full ml-2">Actual</span>
                                    @endif
                                </h5>
                                <div class="text-2xl font-bold text-primary-300 mb-2">
                                    ${{ number_format($plan->price, 0, ',', '.') }}
                                </div>
                                <div class="text-sm text-black-300 mb-4">
                                    por {{ $plan->duration_in_days }} días
                                </div>
                                
                                <!-- Características destacadas -->
                                <div class="text-xs text-black-400 space-y-1">
                                    <p>{{ $plan->max_products }} productos</p>
                                    <p>{{ $plan->max_categories }} categorías</p>
                                    <p>{{ $plan->max_locations }} {{ Str::plural('sede', $plan->max_locations) }}</p>
                                </div>
                                
                                @if($plan->id !== $subscription->plan_id)
                                    <button @click="selectPlanForChange({{ $plan->id }}, '{{ $plan->name }}')"
                                            class="mt-4 w-full bg-primary-200 hover:bg-primary-300 text-accent-50 px-4 py-2 rounded-lg text-sm transition-colors">
                                        @if($plan->price > $subscription->plan->price)
                                            Upgrade a {{ $plan->name }}
                                        @else
                                            Downgrade a {{ $plan->name }}
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Solicitudes Pendientes -->
                    @if(isset($pendingRequests) && $pendingRequests->count() > 0)
                    <div class="bg-warning-50 border border-warning-200 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-warning-400 mb-4">
                            <x-solar-clock-circle-outline class="w-5 h-5 inline mr-2" />
                            Solicitudes Pendientes
                        </h5>
                        <div class="space-y-4">
                            @foreach($pendingRequests as $request)
                            <div class="bg-accent-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-black-500">
                                            {{ $request->type === 'upgrade' ? 'Upgrade' : 'Downgrade' }} a Plan {{ $request->requestedPlan->name }}
                                        </p>
                                        <p class="text-sm text-black-300">
                                            Solicitado el {{ $request->requested_at->format('d/m/Y H:i') }}
                                        </p>
                                        @if($request->reason)
                                        <p class="text-sm text-black-400 mt-2">
                                            <strong>Motivo:</strong> {{ $request->reason }}
                                        </p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-400">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Información Important -->
                    <div class="bg-info-50 border border-info-200 rounded-lg p-6">
                        <h5 class="text-base font-semibold text-info-400 mb-3">
                            <x-solar-info-circle-outline class="w-5 h-5 inline mr-2" />
                            Información Importante
                        </h5>
                        <div class="text-sm text-info-300 space-y-2">
                            <p>• <strong>Upgrade:</strong> Los cambios se aplican inmediatamente y se cobra la diferencia prorrateada.</p>
                            <p>• <strong>Downgrade:</strong> Los cambios se aplican al final del período actual para evitar pérdida de funcionalidades.</p>
                            <p>• <strong>Procesamiento:</strong> Las solicitudes son revisadas por nuestro equipo dentro de 24-48 horas.</p>
                            <p>• <strong>Políticas:</strong> Consulta nuestros términos y condiciones para más detalles sobre cambios de plan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para solicitar cambio de plan -->
    <div x-show="showChangePlanModal" 
         x-cloak 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-accent-50 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-black-500 mb-4">
                Solicitar Cambio de Plan
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-1">
                        Plan seleccionado
                    </label>
                    <input type="text" x-model="selectedPlanName" readonly 
                           class="w-full px-3 py-2 border border-accent-200 rounded-lg bg-accent-100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-1">
                        Motivo del cambio (opcional)
                    </label>
                    <textarea x-model="changeReason" rows="3"
                              class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200"
                              placeholder="Explica por qué quieres cambiar de plan..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-1">
                        Confirma tu contraseña
                    </label>
                    <input type="password" x-model="password" 
                           class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button @click="showChangePlanModal = false; resetModalData()" 
                        class="px-4 py-2 bg-accent-100 hover:bg-accent-200 text-black-400 rounded-lg transition-colors">
                    Cancelar
                </button>
                <button @click="submitPlanChangeRequest()" 
                        :disabled="!selectedPlanId || !password || isLoading"
                        class="px-4 py-2 bg-primary-200 hover:bg-primary-300 text-accent-50 rounded-lg transition-colors disabled:opacity-50">
                    <span x-show="!isLoading">Enviar Solicitud</span>
                    <span x-show="isLoading">Enviando...</span>
                </button>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
    function billingManager() {
        return {
            activeTab: 'plan',
            
            // Modal states
            showChangePlanModal: false,
            
            // Form data
            selectedPlanId: null,
            selectedPlanName: '',
            changeReason: '',
            password: '',
            isLoading: false,
            
            // Select plan for change
            selectPlanForChange(planId, planName) {
                this.selectedPlanId = planId;
                this.selectedPlanName = planName;
                this.showChangePlanModal = true;
            },
            
            // Submit plan change request
            async submitPlanChangeRequest() {
                if (!this.selectedPlanId || !this.password) return;
                
                this.isLoading = true;
                
                try {
                    const response = await fetch('{{ route("tenant.admin.billing.request-plan-change", $store->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            plan_id: this.selectedPlanId,
                            reason: this.changeReason,
                            password: this.password
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.showChangePlanModal = false;
                        this.resetModalData();
                        // Refresh the page to show the new request
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showToast(data.message, 'error');
                    }
                } catch (error) {
                    this.showToast('Error al enviar la solicitud', 'error');
                } finally {
                    this.isLoading = false;
                }
            },
            
            // Reset modal data
            resetModalData() {
                this.password = '';
                this.changeReason = '';
                this.selectedPlanId = null;
                this.selectedPlanName = '';
                this.isLoading = false;
            },
            
            // Show toast notification
            showToast(message, type = 'info') {
                // Simple alert for now - can be enhanced later
                alert(`${type.toUpperCase()}: ${message}`);
            }
        };
    }
    </script>
    @endpush
</x-tenant-admin-layout>
