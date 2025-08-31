<x-tenant-admin-layout :store="$store">
    @section('title', 'Cupones')

    @section('content')
    <div x-data="couponManagement" class="space-y-4">
        <!-- Header con contador y botón crear -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-black-500 mb-2">Cupones</h2>
                        <p class="text-sm text-black-300">
                            Usando {{ $currentCount }} de {{ $maxCoupons }} cupones disponibles en tu plan {{ $store->plan->name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($remainingSlots > 0)
                            <a href="{{ route('tenant.admin.coupons.create', ['store' => $store->slug]) }}" 
                               class="btn-primary flex items-center gap-2">
                                <x-solar-add-circle-outline class="w-5 h-5" />
                                Nuevo Cupón
                            </a>
                        @else
                            <button class="btn-secondary opacity-50 cursor-not-allowed flex items-center gap-2" disabled>
                                <x-solar-add-circle-outline class="w-5 h-5" />
                                Límite Alcanzado
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Barra de herramientas -->
            <div class="px-6 py-3 border-b border-accent-100 bg-accent-50">
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <!-- Búsqueda -->
                    <div class="flex-1 min-w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Buscar por nombre o código..."
                               class="w-full px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                    </div>

                    <!-- Filtros -->
                    <select name="status" class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirados</option>
                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Próximos</option>
                    </select>

                    <select name="type" class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none">
                        <option value="">Todos los tipos</option>
                        @foreach(\App\Features\TenantAdmin\Models\Coupon::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <select name="discount_type" class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none">
                        <option value="">Todos los descuentos</option>
                        @foreach(\App\Features\TenantAdmin\Models\Coupon::DISCOUNT_TYPES as $key => $label)
                            <option value="{{ $key }}" {{ request('discount_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <!-- Botones -->
                    <button type="submit" class="btn-secondary px-4 py-1.5 text-sm">Filtrar</button>
                    <a href="{{ route('tenant.admin.coupons.index', ['store' => $store->slug]) }}" 
                       class="btn-secondary px-4 py-1.5 text-sm">Limpiar</a>
                </form>
            </div>

            <!-- Contenido principal -->
            <div class="p-6">
                @if($coupons->count() > 0)
                    <!-- Tabla de cupones -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b border-accent-200">
                                <tr>
                                    <th class="px-0 py-3 text-left text-xs font-medium text-black-300 uppercase tracking-wider">Cupón</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-black-300 uppercase tracking-wider">Tipo & Descuento</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-black-300 uppercase tracking-wider">Validez</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-black-300 uppercase tracking-wider">Uso</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-black-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-black-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-accent-100">
                                @foreach($coupons as $coupon)
                                    <tr class="hover:bg-accent-100 transition-colors">
                                        {{-- Información del cupón --}}
                                        <td class="px-0 py-4">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-medium text-black-500">{{ $coupon->name }}</span>
                                                    @if($coupon->is_public)
                                                        <x-solar-eye-outline class="w-4 h-4 text-info-200" title="Público" />
                                                    @endif
                                                    @if($coupon->is_automatic)
                                                        <x-solar-magic-stick-outline class="w-4 h-4 text-secondary-200" title="Automático" />
                                                    @endif
                                                </div>
                                                <div class="text-sm text-black-300">
                                                    <span class="font-mono bg-accent-200 px-2 py-1 rounded text-xs">{{ $coupon->code }}</span>
                                                </div>
                                                @if($coupon->description)
                                                    <p class="text-xs text-black-200 mt-1">{{ Str::limit($coupon->description, 50) }}</p>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Tipo y descuento --}}
                                        <td class="px-4 py-4">
                                            <div>
                                                <span class="inline-block bg-primary-50 text-primary-200 text-xs px-2 py-1 rounded-full font-medium mb-1">
                                                    {{ \App\Features\TenantAdmin\Models\Coupon::TYPES[$coupon->type] }}
                                                </span>
                                                <div class="text-sm">
                                                    @if($coupon->discount_type === 'percentage')
                                                        <span class="font-bold text-secondary-200">{{ $coupon->discount_value }}%</span>
                                                    @else
                                                        <span class="font-bold text-secondary-200">${{ number_format($coupon->discount_value, 0, ',', '.') }}</span>
                                                    @endif
                                                    <span class="text-black-300">descuento</span>
                                                </div>
                                                @if($coupon->min_purchase_amount)
                                                    <div class="text-xs text-black-200">
                                                        Mín: ${{ number_format($coupon->min_purchase_amount, 0, ',', '.') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Validez --}}
                                        <td class="px-4 py-4">
                                            <div class="text-sm">
                                                @if($coupon->start_date || $coupon->end_date)
                                                    @if($coupon->start_date)
                                                        <div class="text-black-300">
                                                            Desde: {{ $coupon->start_date->format('d/m/Y') }}
                                                        </div>
                                                    @endif
                                                    @if($coupon->end_date)
                                                        <div class="text-black-300">
                                                            Hasta: {{ $coupon->end_date->format('d/m/Y') }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-black-200">Sin límite</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Uso --}}
                                        <td class="px-4 py-4">
                                            <div class="text-sm">
                                                <div class="font-medium text-black-400">
                                                    {{ $coupon->current_uses }}{{ $coupon->max_uses ? '/' . $coupon->max_uses : '' }}
                                                </div>
                                                @if($coupon->max_uses)
                                                    @php
                                                        $percentage = round(($coupon->current_uses / $coupon->max_uses) * 100);
                                                        $colorClass = $percentage >= 80 ? 'bg-error-100' : ($percentage >= 60 ? 'bg-warning-100' : 'bg-success-100');
                                                    @endphp
                                                    <div class="w-full bg-accent-200 rounded-full h-2 mt-1">
                                                        <div class="{{ $colorClass }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-black-200">{{ $percentage }}% usado</span>
                                                @else
                                                    <span class="text-xs text-black-200">Ilimitado</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Estado --}}
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $coupon->status_info['bg'] }} {{ $coupon->status_info['color'] }} border {{ $coupon->status_info['border'] }}">
                                                {{ $coupon->status_info['text'] }}
                                            </span>
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="px-4 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- Ver -->
                                                <a href="{{ route('tenant.admin.coupons.show', ['store' => $store->slug, 'coupon' => $coupon]) }}" 
                                                   class="text-info-200 hover:text-info-100 transition-colors" title="Ver detalles">
                                                    <x-solar-eye-outline class="w-4 h-4" />
                                                </a>

                                                <!-- Editar -->
                                                <a href="{{ route('tenant.admin.coupons.edit', ['store' => $store->slug, 'coupon' => $coupon]) }}" 
                                                   class="text-secondary-200 hover:text-secondary-100 transition-colors" title="Editar">
                                                    <x-solar-pen-outline class="w-4 h-4" />
                                                </a>

                                                <!-- Toggle Estado -->
                                                <form method="POST" action="{{ route('tenant.admin.coupons.toggle-status', ['store' => $store->slug, 'coupon' => $coupon]) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="{{ $coupon->is_active ? 'text-warning-200 hover:text-warning-100' : 'text-success-200 hover:text-success-100' }} transition-colors" 
                                                            title="{{ $coupon->is_active ? 'Desactivar' : 'Activar' }}">
                                                        @if($coupon->is_active)
                                                            <x-solar-pause-outline class="w-4 h-4" />
                                                        @else
                                                            <x-solar-play-outline class="w-4 h-4" />
                                                        @endif
                                                    </button>
                                                </form>

                                                <!-- Duplicar -->
                                                @if($remainingSlots > 0)
                                                    <form method="POST" action="{{ route('tenant.admin.coupons.duplicate', ['store' => $store->slug, 'coupon' => $coupon]) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="text-primary-200 hover:text-primary-100 transition-colors" 
                                                                title="Duplicar">
                                                            <x-solar-copy-outline class="w-4 h-4" />
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Eliminar -->
                                                @if($coupon->current_uses === 0)
                                                    <button @click="confirmDelete({{ $coupon->id }}, '{{ $coupon->name }}')"
                                                            class="text-error-200 hover:text-error-100 transition-colors" 
                                                            title="Eliminar">
                                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    @if($coupons->hasPages())
                        <div class="mt-6 pt-4 border-t border-accent-200">
                            {{ $coupons->links() }}
                        </div>
                    @endif
                @else
                    {{-- Estado vacío --}}
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-secondary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-solar-ticket-outline class="w-8 h-8 text-secondary-200" />
                        </div>
                        <h3 class="text-lg font-semibold text-black-500 mb-2">No hay cupones</h3>
                        @if(request()->hasAny(['search', 'status', 'type', 'discount_type']))
                            <p class="text-black-300 text-sm mb-4">No se encontraron cupones con los filtros aplicados</p>
                            <a href="{{ route('tenant.admin.coupons.index', ['store' => $store->slug]) }}" 
                               class="btn-secondary">
                                Limpiar filtros
                            </a>
                        @else
                            <p class="text-black-300 text-sm mb-4">Crea tu primer cupón para ofrecer descuentos a tus clientes</p>
                            @if($remainingSlots > 0)
                                <a href="{{ route('tenant.admin.coupons.create', ['store' => $store->slug]) }}" 
                                   class="btn-primary">
                                    Crear primer cupón
                                </a>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal de confirmación de eliminación --}}
        <div x-show="showDeleteModal" 
             x-transition.opacity
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black-400 opacity-75"></div>
                <div class="relative bg-accent-50 rounded-lg max-w-md w-full p-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-error-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-solar-danger-triangle-outline class="w-6 h-6 text-error-200" />
                        </div>
                        <h3 class="text-lg font-semibold text-black-500 mb-2">Confirmar eliminación</h3>
                        <p class="text-black-300 mb-6">
                            ¿Estás seguro de que deseas eliminar el cupón "<span x-text="couponToDelete.name"></span>"? 
                            Esta acción no se puede deshacer.
                        </p>
                        <div class="flex gap-3 justify-center">
                            <button @click="showDeleteModal = false" 
                                    class="btn-secondary">
                                Cancelar
                            </button>
                            <form :action="deleteUrl" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-primary bg-error-200 hover:bg-error-100">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function couponManagement() {
            return {
                showDeleteModal: false,
                couponToDelete: {},
                deleteUrl: '',
                
                confirmDelete(couponId, couponName) {
                    this.couponToDelete = { id: couponId, name: couponName };
                    this.deleteUrl = `{{ route('tenant.admin.coupons.index', ['store' => $store->slug]) }}/${couponId}`;
                    this.showDeleteModal = true;
                }
            }
        }
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 