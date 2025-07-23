<x-tenant-admin-layout :store="$store">
    @section('title', 'Editar Zona de Envío')

    @section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('tenant.admin.shipping-methods.index', $store->slug) }}" 
                   class="text-black-300 hover:text-black-400">
                    <x-solar-arrow-left-outline class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-black-500">Editar Zona de Envío</h1>
            </div>
        </div>

                 <form method="POST" action="{{ route('tenant.admin.shipping-methods.zones.update', [$store->slug, $methodModel->id, $zone->id]) }}" 
               x-data="zoneForm">
            @csrf
            @method('PUT')

            {{-- Información de la Zona --}}
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-500">Información de la Zona</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">
                            Nombre de la zona <span class="text-error-300">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $zone->name) }}"
                               placeholder="Ej: Ciudad Principal"
                               class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('name') border-error-300 @enderror"
                               required>
                        @error('name')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">
                            Descripción
                        </label>
                        <textarea name="description" 
                                  rows="2"
                                  placeholder="Ej: Bogotá y alrededores"
                                  class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('description') border-error-300 @enderror">{{ old('description', $zone->description) }}</textarea>
                        @error('description')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Costo de Envío --}}
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">
                                Costo de envío <span class="text-error-300">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">$</span>
                                <input type="number" 
                                       name="cost" 
                                       value="{{ old('cost', $zone->cost) }}"
                                       min="0"
                                       step="1000"
                                       class="w-full pl-8 pr-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('cost') border-error-300 @enderror"
                                       required>
                            </div>
                            @error('cost')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Envío Gratis Desde --}}
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">
                                Envío gratis desde
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-black-300">$</span>
                                <input type="number" 
                                       name="free_shipping_from" 
                                       value="{{ old('free_shipping_from', $zone->free_shipping_from) }}"
                                       min="0"
                                       step="1000"
                                       placeholder="Opcional"
                                       class="w-full pl-8 pr-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('free_shipping_from') border-error-300 @enderror">
                            </div>
                            <p class="text-xs text-black-300 mt-1">Deja vacío si no aplica envío gratis</p>
                            @error('free_shipping_from')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    {{-- Tiempo Estimado --}}
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">
                            Tiempo estimado de entrega <span class="text-error-300">*</span>
                        </label>
                                                 <select name="estimated_time" 
                                 class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('estimated_time') border-error-300 @enderror"
                                 required>
                             <option value="">Selecciona un tiempo</option>
                             @foreach(\App\Features\TenantAdmin\Models\ShippingZone::ESTIMATED_TIMES as $key => $label)
                                 <option value="{{ $key }}" {{ old('estimated_time', $zone->estimated_time) == $key ? 'selected' : '' }}>
                                     {{ $label }}
                                 </option>
                             @endforeach
                         </select>
                        @error('estimated_time')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    {{-- Estado --}}
                    <div>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $zone->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-primary-300 border-white-300 rounded focus:ring-primary-200">
                            <span class="text-sm text-black-400">
                                Zona activa
                            </span>
                        </label>
                        <p class="text-xs text-black-300 mt-1 ml-7">
                            Las zonas inactivas no aparecerán en el checkout
                        </p>
                    </div>
                </div>
            </div>

            {{-- Horarios de Entrega --}}
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-500">Horarios de Entrega</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    {{-- Días de entrega --}}
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-3">
                            Días de entrega
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @php
                                $days = [
                                    'L' => 'Lunes',
                                    'M' => 'Martes', 
                                    'X' => 'Miércoles',
                                    'J' => 'Jueves',
                                    'V' => 'Viernes',
                                    'S' => 'Sábado',
                                    'D' => 'Domingo'
                                ];
                                $selectedDays = old('delivery_days', $zone->delivery_days ?? ['L', 'M', 'X', 'J', 'V']);
                            @endphp
                            @foreach($days as $key => $day)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" 
                                           name="delivery_days[]" 
                                           value="{{ $key }}"
                                           {{ in_array($key, $selectedDays) ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary-300 border-white-300 rounded focus:ring-primary-200">
                                    <span class="text-sm text-black-400">{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('delivery_days')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Hora de inicio --}}
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">
                                Hora de inicio
                            </label>
                            <input type="time" 
                                   name="start_time" 
                                   value="{{ old('start_time', $zone->start_time ? $zone->start_time->format('H:i') : '09:00') }}"
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('start_time') border-error-300 @enderror">
                            @error('start_time')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Hora de fin --}}
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">
                                Hora de fin
                            </label>
                            <input type="time" 
                                   name="end_time" 
                                   value="{{ old('end_time', $zone->end_time ? $zone->end_time->format('H:i') : '18:00') }}"
                                   class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('end_time') border-error-300 @enderror">
                            @error('end_time')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    {{-- Instrucciones adicionales --}}
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">
                            Instrucciones adicionales
                        </label>
                        <textarea name="instructions" 
                                  rows="2"
                                  placeholder="Ej: Entrega en horario laboral, llamar antes de entregar"
                                  class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">{{ old('instructions', $zone->instructions) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex justify-between">
                <button type="button" 
                        onclick="if(confirm('¿Estás seguro de que deseas eliminar esta zona?')) { document.getElementById('delete-form').submit(); }"
                        class="btn-error">
                    <x-solar-trash-bin-trash-outline class="w-5 h-5 mr-2" />
                    Eliminar Zona
                </button>
                
                <div class="flex gap-3">
                    <a href="{{ route('tenant.admin.shipping-methods.index', $store->slug) }}" 
                       class="btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <x-solar-diskette-outline class="w-5 h-5 mr-2" />
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>

        {{-- Formulario oculto para eliminar --}}
        <form id="delete-form" action="{{ route('tenant.admin.shipping-methods.zones.destroy', [$store->slug, $methodModel->id, $zone->id]) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
    @endsection

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('zoneForm', () => ({
                // Aquí podemos agregar lógica adicional si es necesaria
            }));
        });
    </script>
    @endpush
</x-tenant-admin-layout> 