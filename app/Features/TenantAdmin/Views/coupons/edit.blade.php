<x-tenant-admin-layout :store="$store">

@section('title', 'Editar Cupón')

@section('content')
<div class="container-fluid" x-data="couponEditForm">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Editar Cupón</h1>
            <p class="text-sm text-black-300">{{ $coupon->name }} - {{ $coupon->code }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.admin.coupons.show', ['store' => $store->slug, 'coupon' => $coupon]) }}" 
               class="btn-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-eye-outline class="w-4 h-4" />
                Ver detalles
            </a>
            <a href="{{ route('tenant.admin.coupons.index', ['store' => $store->slug]) }}" 
               class="btn-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if($coupon->current_uses > 0)
        <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <x-solar-info-circle-outline class="w-5 h-5 text-warning-300 flex-shrink-0" />
                <div>
                    <p class="text-sm text-warning-400">
                        <span class="font-medium">Cupón en uso:</span> 
                        Este cupón ya ha sido utilizado {{ $coupon->current_uses }} vez{{ $coupon->current_uses > 1 ? 'es' : '' }}. 
                        Los cambios en el descuento no afectarán órdenes ya procesadas.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.admin.coupons.update', ['store' => $store->slug, 'coupon' => $coupon]) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Columna principal (formulario) --}}
            <div class="lg:col-span-2">
                {{-- Información básica --}}
                <div class="bg-white-50 rounded-lg border border-white-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-black-400 mb-4">Información Básica</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-black-300 mb-2">
                                Nombre del cupón <span class="text-error-300">*</span>
                            </label>
                            <input type="text" id="name" name="name" 
                                   value="{{ old('name', $coupon->name) }}" 
                                   class="form-input w-full @error('name') border-error-300 @enderror"
                                   placeholder="Ej: Descuento de Bienvenida" required>
                            @error('name')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Código -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-black-300 mb-2">
                                Código del cupón <span class="text-error-300">*</span>
                            </label>
                            <input type="text" id="code" name="code" 
                                   value="{{ old('code', $coupon->code) }}" 
                                   class="form-input w-full @error('code') border-error-300 @enderror"
                                   placeholder="Ej: BIENVENIDA20" 
                                   x-model="formData.code"
                                   @input="formData.code = $event.target.value.toUpperCase()" required>
                            @error('code')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-black-300 mb-2">
                            Descripción
                        </label>
                        <textarea id="description" name="description" rows="3" 
                                  class="form-input w-full @error('description') border-error-300 @enderror"
                                  placeholder="Describe brevemente este cupón...">{{ old('description', $coupon->description) }}</textarea>
                        @error('description')
                            <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Configuración de descuento --}}
                <div class="bg-white-50 rounded-lg border border-white-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-black-400 mb-4">Configuración de Descuento</h3>
                    
                    @if($coupon->current_uses > 0)
                        <div class="bg-info-50 border border-info-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-info-400">
                                <x-solar-info-circle-outline class="w-4 h-4 inline mr-1" />
                                Estos cambios no afectarán órdenes ya procesadas con este cupón.
                            </p>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Tipo de cupón -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-black-300 mb-2">
                                Aplicar a <span class="text-error-300">*</span>
                            </label>
                            <select id="type" name="type" 
                                    class="form-select w-full @error('type') border-error-300 @enderror"
                                    x-model="formData.type" required>
                                @foreach(\App\Features\TenantAdmin\Models\Coupon::TYPES as $key => $label)
                                    <option value="{{ $key }}" 
                                            {{ old('type', $coupon->type) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo de descuento -->
                        <div>
                            <label for="discount_type" class="block text-sm font-medium text-black-300 mb-2">
                                Tipo de descuento <span class="text-error-300">*</span>
                            </label>
                            <select id="discount_type" name="discount_type" 
                                    class="form-select w-full @error('discount_type') border-error-300 @enderror"
                                    x-model="formData.discount_type" required>
                                @foreach(\App\Features\TenantAdmin\Models\Coupon::DISCOUNT_TYPES as $key => $label)
                                    <option value="{{ $key }}" 
                                            {{ old('discount_type', $coupon->discount_type) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('discount_type')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor del descuento -->
                        <div>
                            <label for="discount_value" class="block text-sm font-medium text-black-300 mb-2">
                                <span x-text="formData.discount_type === 'percentage' ? 'Porcentaje (%)' : 'Valor fijo ($)'"></span>
                                <span class="text-error-300">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" id="discount_value" name="discount_value" 
                                       value="{{ old('discount_value', $coupon->discount_value) }}" 
                                       class="form-input w-full @error('discount_value') border-error-300 @enderror"
                                       :placeholder="formData.discount_type === 'percentage' ? 'Ej: 20' : 'Ej: 5000'"
                                       :max="formData.discount_type === 'percentage' ? '100' : ''"
                                       step="0.01" min="0.01" required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-black-300 text-sm" x-text="formData.discount_type === 'percentage' ? '%' : '$'"></span>
                                </div>
                            </div>
                            @error('discount_value')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Restricciones de descuento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4" x-show="formData.discount_type === 'percentage'">
                        <div>
                            <label for="max_discount_amount" class="block text-sm font-medium text-black-300 mb-2">
                                Descuento máximo ($)
                                <span class="text-xs text-black-200">(opcional)</span>
                            </label>
                            <input type="number" id="max_discount_amount" name="max_discount_amount" 
                                   value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" 
                                   class="form-input w-full @error('max_discount_amount') border-error-300 @enderror"
                                   placeholder="Ej: 50000" step="100" min="0">
                            @error('max_discount_amount')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="min_purchase_amount" class="block text-sm font-medium text-black-300 mb-2">
                                Compra mínima ($)
                                <span class="text-xs text-black-200">(opcional)</span>
                            </label>
                            <input type="number" id="min_purchase_amount" name="min_purchase_amount" 
                                   value="{{ old('min_purchase_amount', $coupon->min_purchase_amount) }}" 
                                   class="form-input w-full @error('min_purchase_amount') border-error-300 @enderror"
                                   placeholder="Ej: 30000" step="100" min="0">
                            @error('min_purchase_amount')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4" x-show="formData.discount_type === 'fixed'">
                        <div>
                            <label for="min_purchase_amount_fixed" class="block text-sm font-medium text-black-300 mb-2">
                                Compra mínima ($)
                                <span class="text-xs text-black-200">(opcional)</span>
                            </label>
                            <input type="number" id="min_purchase_amount_fixed" name="min_purchase_amount" 
                                   value="{{ old('min_purchase_amount', $coupon->min_purchase_amount) }}" 
                                   class="form-input w-full @error('min_purchase_amount') border-error-300 @enderror"
                                   placeholder="Ej: 30000" step="100" min="0">
                        </div>
                    </div>
                </div>

                {{-- Aplicabilidad --}}
                <div class="bg-white-50 rounded-lg border border-white-200 p-6 mb-6" x-show="formData.type !== 'global'">
                    <h3 class="text-lg font-semibold text-black-400 mb-4">
                        <span x-text="formData.type === 'categories' ? 'Categorías Aplicables' : 'Productos Aplicables'"></span>
                    </h3>
                    
                    <!-- Categorías -->
                    <div x-show="formData.type === 'categories'">
                        <p class="text-sm text-black-300 mb-3">Selecciona las categorías donde se puede aplicar este cupón:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($categories as $category)
                                <label class="flex items-center p-3 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                           class="rounded border-white-300 text-primary-300 focus:ring-primary-200"
                                           {{ 
                                               (is_array(old('categories')) && in_array($category->id, old('categories'))) ||
                                               (!old('categories') && $coupon->categories->contains($category->id))
                                               ? 'checked' : '' 
                                           }}>
                                    <span class="ml-3 text-sm text-black-400">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('categories')
                            <p class="text-error-300 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Productos -->
                    <div x-show="formData.type === 'products'">
                        <p class="text-sm text-black-300 mb-3">Selecciona los productos donde se puede aplicar este cupón:</p>
                        <div class="max-h-64 overflow-y-auto border border-white-200 rounded-lg">
                            @foreach($products as $product)
                                <label class="flex items-center p-3 border-b border-white-100 last:border-b-0 hover:bg-white-50 cursor-pointer">
                                    <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                           class="rounded border-white-300 text-primary-300 focus:ring-primary-200"
                                           {{ 
                                               (is_array(old('products')) && in_array($product->id, old('products'))) ||
                                               (!old('products') && $coupon->products->contains($product->id))
                                               ? 'checked' : '' 
                                           }}>
                                    <div class="ml-3">
                                        <span class="text-sm text-black-400">{{ $product->name }}</span>
                                        <span class="text-xs text-black-200 block">${{ number_format($product->price, 0, ',', '.') }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('products')
                            <p class="text-error-300 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Configuración avanzada --}}
                <div class="bg-white-50 rounded-lg border border-white-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-black-400 mb-4">Configuración Avanzada</h3>
                    
                    {{-- Límites de uso --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="max_uses" class="block text-sm font-medium text-black-300 mb-2">
                                Límite total de usos
                                <span class="text-xs text-black-200">(opcional)</span>
                            </label>
                            <input type="number" id="max_uses" name="max_uses" 
                                   value="{{ old('max_uses', $coupon->max_uses) }}" 
                                   class="form-input w-full @error('max_uses') border-error-300 @enderror"
                                   placeholder="Ej: 100" min="{{ $coupon->current_uses }}">
                            @if($coupon->current_uses > 0)
                                <p class="text-xs text-info-300 mt-1">
                                    Mínimo: {{ $coupon->current_uses }} (usos actuales)
                                </p>
                            @endif
                            @error('max_uses')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="uses_per_session" class="block text-sm font-medium text-black-300 mb-2">
                                Usos por cliente
                                <span class="text-xs text-black-200">(opcional)</span>
                            </label>
                            <input type="number" id="uses_per_session" name="uses_per_session" 
                                   value="{{ old('uses_per_session', $coupon->uses_per_session) }}" 
                                   class="form-input w-full @error('uses_per_session') border-error-300 @enderror"
                                   placeholder="Ej: 1" min="1">
                            @error('uses_per_session')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Fechas de validez --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-black-300 mb-2">
                                Fecha de inicio
                                <span class="text-xs text-black-200">(opcional)</span>
                            </label>
                            <input type="datetime-local" id="start_date" name="start_date" 
                                   value="{{ old('start_date', $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}" 
                                   class="form-input w-full @error('start_date') border-error-300 @enderror">
                            @error('start_date')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-black-300 mb-2">
                                Fecha de fin
                                <span class="text-xs text-black-200">(opcional)</span>
                            </label>
                            <input type="datetime-local" id="end_date" name="end_date" 
                                   value="{{ old('end_date', $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}" 
                                   class="form-input w-full @error('end_date') border-error-300 @enderror">
                            @error('end_date')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Restricciones horarias --}}
                    <div class="border-t border-white-200 pt-6" x-data="{ showTimeRestrictions: {{ ($coupon->days_of_week || $coupon->start_time || $coupon->end_time) ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-semibold text-black-400">Restricciones Horarias</h4>
                            <button type="button" @click="showTimeRestrictions = !showTimeRestrictions"
                                    class="text-primary-300 hover:text-primary-200 text-sm">
                                <span x-text="showTimeRestrictions ? 'Ocultar' : 'Configurar'"></span>
                            </button>
                        </div>

                        <div x-show="showTimeRestrictions" x-transition class="space-y-4">
                            {{-- Días de la semana --}}
                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    Días permitidos
                                    <span class="text-xs text-black-200">(dejar vacío para todos los días)</span>
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    @php
                                        $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                        $selectedDays = old('days_of_week', $coupon->days_of_week ?? []);
                                    @endphp
                                    @foreach($days as $index => $day)
                                        <label class="flex items-center p-2 border border-white-200 rounded cursor-pointer hover:bg-white-100">
                                            <input type="checkbox" name="days_of_week[]" value="{{ $index }}" 
                                                   class="rounded border-white-300 text-primary-300 focus:ring-primary-200"
                                                   {{ is_array($selectedDays) && in_array($index, $selectedDays) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-black-400">{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Horarios --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-black-300 mb-2">
                                        Hora de inicio
                                    </label>
                                    <input type="time" id="start_time" name="start_time" 
                                           value="{{ old('start_time', $coupon->start_time ? $coupon->start_time->format('H:i') : '') }}" 
                                           class="form-input w-full @error('start_time') border-error-300 @enderror">
                                    @error('start_time')
                                        <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-black-300 mb-2">
                                        Hora de fin
                                    </label>
                                    <input type="time" id="end_time" name="end_time" 
                                           value="{{ old('end_time', $coupon->end_time ? $coupon->end_time->format('H:i') : '') }}" 
                                           class="form-input w-full @error('end_time') border-error-300 @enderror">
                                    @error('end_time')
                                        <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar derecho --}}
            <div class="lg:col-span-1">
                {{-- Estadísticas actuales --}}
                @if($coupon->current_uses > 0)
                    <div class="bg-white-50 rounded-lg border border-white-200 p-6 mb-6">
                        <h3 class="text-lg font-semibold text-black-400 mb-4">Estadísticas</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-black-300">Total usos:</span>
                                <span class="font-semibold text-black-400">{{ $coupon->current_uses }}</span>
                            </div>
                            
                            @if($coupon->max_uses)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-black-300">Disponibles:</span>
                                    <span class="font-semibold text-black-400">{{ $coupon->max_uses - $coupon->current_uses }}</span>
                                </div>
                            @endif
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-black-300">Estado:</span>
                                <span class="text-xs px-2 py-1 rounded-full {{ $coupon->is_active ? 'bg-success-100 text-success-300' : 'bg-error-100 text-error-300' }}">
                                    {{ $coupon->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Configuración general --}}
                <div class="bg-white-50 rounded-lg border border-white-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-black-400 mb-4">Configuración</h3>
                    
                    <div class="space-y-4">
                        <!-- Estado -->
                        <div>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" 
                                       class="rounded border-white-300 text-primary-300 focus:ring-primary-200"
                                       {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-black-400">Activar cupón</span>
                                    <p class="text-xs text-black-200">El cupón estará disponible para uso</p>
                                </div>
                            </label>
                        </div>

                        <!-- Visibilidad pública -->
                        <div>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_public" value="1" 
                                       class="rounded border-white-300 text-primary-300 focus:ring-primary-200"
                                       {{ old('is_public', $coupon->is_public) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-black-400">Cupón público</span>
                                    <p class="text-xs text-black-200">Visible en la tienda para todos los clientes</p>
                                </div>
                            </label>
                        </div>

                        <!-- Aplicación automática -->
                        <div>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_automatic" value="1" 
                                       class="rounded border-white-300 text-primary-300 focus:ring-primary-200"
                                       {{ old('is_automatic', $coupon->is_automatic) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-black-400">Aplicación automática</span>
                                    <p class="text-xs text-black-200">Se aplica automáticamente sin código</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="flex flex-col gap-3">
                    <button type="submit" class="btn-primary w-full py-3 rounded-lg flex items-center justify-center gap-2">
                        <x-solar-diskette-outline class="w-5 h-5" />
                        Actualizar Cupón
                    </button>
                    
                    <a href="{{ route('tenant.admin.coupons.show', ['store' => $store->slug, 'coupon' => $coupon]) }}" 
                       class="btn-secondary w-full py-3 rounded-lg text-center">
                        Ver detalles
                    </a>
                    
                    <a href="{{ route('tenant.admin.coupons.index', ['store' => $store->slug]) }}" 
                       class="btn-secondary bg-white-200 text-black-400 w-full py-3 rounded-lg text-center">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function couponEditForm() {
        return {
            formData: {
                name: '{{ old('name', $coupon->name) }}',
                code: '{{ old('code', $coupon->code) }}',
                type: '{{ old('type', $coupon->type) }}',
                discount_type: '{{ old('discount_type', $coupon->discount_type) }}',
                discount_value: {{ old('discount_value', $coupon->discount_value) }}
            }
        }
    }
</script>
@endpush
@endsection
</x-tenant-admin-layout> 