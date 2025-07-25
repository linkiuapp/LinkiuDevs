<x-tenant-admin-layout :store="$store">
    @section('title', 'Nuevo Cupón')

    @section('content')
    <div class="max-w-4xl mx-auto" x-data="couponForm">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('tenant.admin.coupons.index', $store->slug) }}" 
                   class="text-black-300 hover:text-black-400">
                    <x-solar-arrow-left-outline class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-black-500">Nuevo Cupón</h1>
            </div>
            <div class="bg-info-50 border border-info-100 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <x-solar-info-circle-outline class="w-5 h-5 text-info-200 flex-shrink-0" />
                    <p class="text-sm text-info-200">
                        Estás usando <strong>{{ $currentCount }}</strong> de <strong>{{ $maxCoupons }}</strong> cupones disponibles en tu plan {{ $store->plan->name }}.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form action="{{ route('tenant.admin.coupons.store', $store->slug) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Columna principal (formulario) --}}
                <div class="lg:col-span-2">
                    {{-- Información básica --}}
                    <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                            <h3 class="text-lg font-semibold text-black-500">Información Básica</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nombre -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-black-400 mb-2">
                                        Nombre del cupón <span class="text-error-200">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                                           class="form-input w-full @error('name') border-error-200 @enderror"
                                           placeholder="Ej: Descuento de Bienvenida" required>
                                    @error('name')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Código -->
                                <div>
                                    <label for="code" class="block text-sm font-medium text-black-400 mb-2">
                                        Código del cupón
                                        <span class="text-xs text-black-200">(opcional, se genera automáticamente)</span>
                                    </label>
                                    <input type="text" id="code" name="code" value="{{ old('code') }}" 
                                           class="form-input w-full @error('code') border-error-200 @enderror"
                                           placeholder="Ej: BIENVENIDA20" 
                                           x-model="formData.code"
                                           @input="formData.code = $event.target.value.toUpperCase()">
                                    @error('code')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-black-400 mb-2">
                                    Descripción
                                </label>
                                <textarea id="description" name="description" rows="3" 
                                          class="form-input w-full @error('description') border-error-200 @enderror"
                                          placeholder="Describe brevemente este cupón...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Configuración de descuento --}}
                    <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                            <h3 class="text-lg font-semibold text-black-500">Configuración de Descuento</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Tipo de cupón -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-black-400 mb-2">
                                        Aplicar a <span class="text-error-200">*</span>
                                    </label>
                                    <select id="type" name="type" 
                                            class="form-select w-full @error('type') border-error-200 @enderror"
                                            x-model="formData.type" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach(\App\Features\TenantAdmin\Models\Coupon::TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Tipo de descuento -->
                                <div>
                                    <label for="discount_type" class="block text-sm font-medium text-black-400 mb-2">
                                        Tipo de descuento <span class="text-error-200">*</span>
                                    </label>
                                    <select id="discount_type" name="discount_type" 
                                            class="form-select w-full @error('discount_type') border-error-200 @enderror"
                                            x-model="formData.discount_type" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach(\App\Features\TenantAdmin\Models\Coupon::DISCOUNT_TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ old('discount_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('discount_type')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Valor del descuento -->
                                <div>
                                    <label for="discount_value" class="block text-sm font-medium text-black-400 mb-2">
                                        <span x-text="formData.discount_type === 'percentage' ? 'Porcentaje (%)' : 'Valor fijo ($)'"></span>
                                        <span class="text-error-200">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="discount_value" name="discount_value" 
                                               value="{{ old('discount_value') }}" 
                                               class="form-input w-full @error('discount_value') border-error-200 @enderror"
                                               :placeholder="formData.discount_type === 'percentage' ? 'Ej: 20' : 'Ej: 5000'"
                                               :max="formData.discount_type === 'percentage' ? '100' : ''"
                                               step="0.01" min="0.01" required>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-black-300 text-sm" x-text="formData.discount_type === 'percentage' ? '%' : '$'"></span>
                                        </div>
                                    </div>
                                    @error('discount_value')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Restricciones monetarias -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div x-show="formData.discount_type === 'percentage'">
                                    <label for="max_discount_amount" class="block text-sm font-medium text-black-400 mb-2">
                                        Descuento máximo ($)
                                        <span class="text-xs text-black-200">(opcional)</span>
                                    </label>
                                    <input type="number" id="max_discount_amount" name="max_discount_amount" 
                                           value="{{ old('max_discount_amount') }}" 
                                           class="form-input w-full @error('max_discount_amount') border-error-200 @enderror"
                                           placeholder="Ej: 50000" step="100" min="0">
                                    @error('max_discount_amount')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="min_purchase_amount" class="block text-sm font-medium text-black-400 mb-2">
                                        Compra mínima ($)
                                        <span class="text-xs text-black-200">(opcional)</span>
                                    </label>
                                    <input type="number" id="min_purchase_amount" name="min_purchase_amount" 
                                           value="{{ old('min_purchase_amount') }}" 
                                           class="form-input w-full @error('min_purchase_amount') border-error-200 @enderror"
                                           placeholder="Ej: 30000" step="100" min="0">
                                    @error('min_purchase_amount')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Aplicabilidad --}}
                    <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6" x-show="formData.type !== 'global'">
                        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                            <h3 class="text-lg font-semibold text-black-500">
                                <span x-text="formData.type === 'categories' ? 'Categorías Aplicables' : 'Productos Aplicables'"></span>
                            </h3>
                        </div>
                        <div class="p-6">
                            <!-- Categorías -->
                            <div x-show="formData.type === 'categories'">
                                <p class="text-sm text-black-300 mb-3">Selecciona las categorías donde se puede aplicar este cupón:</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($categories as $category)
                                        <label class="flex items-center p-3 border border-white-200 rounded-lg hover:bg-white-100 cursor-pointer">
                                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                                   class="rounded border-white-300 text-primary-200 focus:ring-primary-100"
                                                   {{ is_array(old('categories')) && in_array($category->id, old('categories')) ? 'checked' : '' }}>
                                            <span class="ml-3 text-sm text-black-400">{{ $category->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('categories')
                                    <p class="text-error-200 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Productos -->
                            <div x-show="formData.type === 'products'">
                                <p class="text-sm text-black-300 mb-3">Selecciona los productos donde se puede aplicar este cupón:</p>
                                <div class="max-h-64 overflow-y-auto border border-white-200 rounded-lg">
                                    @foreach($products as $product)
                                        <label class="flex items-center p-3 border-b border-white-100 last:border-b-0 hover:bg-white-50 cursor-pointer">
                                            <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                                   class="rounded border-white-300 text-primary-200 focus:ring-primary-100"
                                                   {{ is_array(old('products')) && in_array($product->id, old('products')) ? 'checked' : '' }}>
                                            <div class="ml-3">
                                                <span class="text-sm text-black-400">{{ $product->name }}</span>
                                                <span class="text-xs text-black-200 block">${{ number_format($product->price, 0, ',', '.') }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('products')
                                    <p class="text-error-200 text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Configuración avanzada --}}
                    <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                            <h3 class="text-lg font-semibold text-black-500">Configuración Avanzada</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- Límites de uso --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="max_uses" class="block text-sm font-medium text-black-400 mb-2">
                                        Límite total de usos
                                        <span class="text-xs text-black-200">(opcional)</span>
                                    </label>
                                    <input type="number" id="max_uses" name="max_uses" value="{{ old('max_uses') }}" 
                                           class="form-input w-full @error('max_uses') border-error-200 @enderror"
                                           placeholder="Ej: 100" min="1">
                                    @error('max_uses')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="uses_per_session" class="block text-sm font-medium text-black-400 mb-2">
                                        Usos por cliente
                                        <span class="text-xs text-black-200">(opcional)</span>
                                    </label>
                                    <input type="number" id="uses_per_session" name="uses_per_session" value="{{ old('uses_per_session') }}" 
                                           class="form-input w-full @error('uses_per_session') border-error-200 @enderror"
                                           placeholder="Ej: 1" min="1">
                                    @error('uses_per_session')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Fechas de validez --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-black-400 mb-2">
                                        Fecha de inicio
                                        <span class="text-xs text-black-200">(opcional)</span>
                                    </label>
                                    <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date') }}" 
                                           class="form-input w-full @error('start_date') border-error-200 @enderror">
                                    @error('start_date')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-black-400 mb-2">
                                        Fecha de fin
                                        <span class="text-xs text-black-200">(opcional)</span>
                                    </label>
                                    <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date') }}" 
                                           class="form-input w-full @error('end_date') border-error-200 @enderror">
                                    @error('end_date')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Restricciones horarias --}}
                            <div class="border-t border-white-100 pt-6" x-data="{ showTimeRestrictions: false }">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-base font-semibold text-black-400">Restricciones Horarias</h4>
                                    <button type="button" @click="showTimeRestrictions = !showTimeRestrictions"
                                            class="text-primary-200 hover:text-primary-100 text-sm">
                                        <span x-text="showTimeRestrictions ? 'Ocultar' : 'Configurar'"></span>
                                    </button>
                                </div>

                                <div x-show="showTimeRestrictions" x-transition class="space-y-4">
                                    {{-- Días de la semana --}}
                                    <div>
                                        <label class="block text-sm font-medium text-black-400 mb-2">
                                            Días permitidos
                                            <span class="text-xs text-black-200">(dejar vacío para todos los días)</span>
                                        </label>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                            @php
                                                $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                            @endphp
                                            @foreach($days as $index => $day)
                                                <label class="flex items-center p-2 border border-white-200 rounded cursor-pointer hover:bg-white-100">
                                                    <input type="checkbox" name="days_of_week[]" value="{{ $index }}" 
                                                           class="rounded border-white-300 text-primary-200 focus:ring-primary-100"
                                                           {{ is_array(old('days_of_week')) && in_array($index, old('days_of_week')) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-black-400">{{ $day }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Horarios --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_time" class="block text-sm font-medium text-black-400 mb-2">
                                                Hora de inicio
                                            </label>
                                            <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" 
                                                   class="form-input w-full @error('start_time') border-error-200 @enderror">
                                            @error('start_time')
                                                <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="end_time" class="block text-sm font-medium text-black-400 mb-2">
                                                Hora de fin
                                            </label>
                                            <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" 
                                                   class="form-input w-full @error('end_time') border-error-200 @enderror">
                                            @error('end_time')
                                                <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar derecho --}}
                <div class="lg:col-span-1">
                    {{-- Configuración general --}}
                    <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                            <h3 class="text-lg font-semibold text-black-500">Configuración</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Estado inicial -->
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="rounded border-white-300 text-primary-200 focus:ring-primary-100"
                                           {{ old('is_active', false) ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-black-400">Activar cupón</span>
                                        <p class="text-xs text-black-200">El cupón estará disponible inmediatamente</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Visibilidad pública -->
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_public" value="1" class="rounded border-white-300 text-primary-200 focus:ring-primary-100"
                                           {{ old('is_public', false) ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-black-400">Cupón público</span>
                                        <p class="text-xs text-black-200">Visible en la tienda para todos los clientes</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Aplicación automática -->
                            <div>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_automatic" value="1" class="rounded border-white-300 text-primary-200 focus:ring-primary-100"
                                           {{ old('is_automatic', false) ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-black-400">Aplicación automática</span>
                                        <p class="text-xs text-black-200">Se aplica automáticamente sin código</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Vista previa --}}
                    <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                            <h3 class="text-lg font-semibold text-black-500">Vista Previa</h3>
                        </div>
                        <div class="p-6">
                            <div class="border border-white-200 rounded-lg p-4 bg-white-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-black-400" x-text="formData.name || 'Nombre del cupón'"></span>
                                    <span class="text-xs bg-primary-50 text-primary-200 px-2 py-1 rounded-full">
                                        Global
                                    </span>
                                </div>
                                
                                <div class="text-sm text-black-300 mb-2">
                                    <span class="font-mono bg-white-200 px-2 py-1 rounded text-xs" x-text="formData.code || 'CÓDIGO-AUTO'"></span>
                                </div>
                                
                                <div class="text-lg font-bold text-secondary-200">
                                    <span x-show="formData.discount_type === 'percentage'" x-text="(formData.discount_value || 0) + '%'"></span>
                                    <span x-show="formData.discount_type === 'fixed'">$<span x-text="parseInt(formData.discount_value || 0).toLocaleString()"></span></span>
                                    <span x-show="!formData.discount_type">Descuento</span>
                                    <span class="text-sm text-black-300 font-normal">de descuento</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                            <x-solar-diskette-outline class="w-5 h-5" />
                            Crear Cupón
                        </button>
                        
                        <a href="{{ route('tenant.admin.coupons.index', $store->slug) }}" 
                           class="btn-secondary w-full text-center">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function couponForm() {
            return {
                formData: {
                    name: '',
                    code: '',
                    type: '{{ old('type', 'global') }}',
                    discount_type: '{{ old('discount_type', 'percentage') }}',
                    discount_value: {{ old('discount_value', 0) }}
                }
            }
        }
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 