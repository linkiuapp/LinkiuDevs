<x-tenant-admin-layout :store="$store">
    @section('title', 'Crear Slider')

    @section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('tenant.admin.sliders.index', $store->slug) }}" 
                   class="text-black-300 hover:text-black-400">
                    <x-solar-arrow-left-outline class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-black-500">Crear Slider</h1>
            </div>
        </div>

        <!-- Formulario -->
        <form action="{{ route('tenant.admin.sliders.store', $store->slug) }}" method="POST" enctype="multipart/form-data" x-data="sliderForm">
            @csrf
            
            <!-- Información Básica -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                <div class="p-6 space-y-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-black-500 mb-1">Información Básica</h3>
                        <p class="text-sm text-black-300">Configura los datos principales del slider</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">Nombre *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-transparent"
                                   placeholder="Promoción Navidad 2024">
                            @error('name')
                                <p class="text-error-300 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">Transición</label>
                            <select name="transition_duration" 
                                    class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                           focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                                <option value="3" {{ old('transition_duration') == '3' ? 'selected' : '' }}>3 segundos</option>
                                <option value="5" {{ old('transition_duration', '5') == '5' ? 'selected' : '' }}>5 segundos</option>
                                <option value="7" {{ old('transition_duration') == '7' ? 'selected' : '' }}>7 segundos</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">Descripción</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                         focus:ring-2 focus:ring-primary-200 focus:border-transparent"
                                  placeholder="Descuentos especiales en toda la tienda">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-white-300 text-primary-300 focus:ring-primary-200">
                            <span class="ml-2 text-sm text-black-400">Slider activo</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Imagen -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-black-500 mb-1">Imagen</h3>
                        <p class="text-sm text-black-300">Sube la imagen del slider (170x100px)</p>
                    </div>
                    
                    <div class="border-2 border-dashed border-white-200 rounded-lg p-6 text-center" x-data="{ imagePreview: null }">
                        <input type="file" name="image" accept="image/*" required
                               class="hidden" id="image-upload"
                               @change="
                                   const file = $event.target.files[0];
                                   if (file) {
                                       const reader = new FileReader();
                                       reader.onload = (e) => imagePreview = e.target.result;
                                       reader.readAsDataURL(file);
                                   }
                               ">
                        <div x-show="!imagePreview">
                            <label for="image-upload" class="cursor-pointer">
                                <x-solar-gallery-outline class="w-12 h-12 text-black-300 mx-auto mb-4" />
                                <p class="text-sm text-black-400 mb-2">Haz clic para subir una imagen</p>
                                <p class="text-xs text-black-300">Exactamente 170x100px, máximo 2MB</p>
                            </label>
                        </div>
                        <div x-show="imagePreview" class="space-y-4">
                            <img :src="imagePreview" alt="Preview" class="max-w-full h-auto mx-auto rounded-lg" style="max-height: 200px;">
                            <div class="flex justify-center">
                                <label for="image-upload" class="btn-outline-primary cursor-pointer">
                                    Cambiar imagen
                                </label>
                            </div>
                        </div>
                    </div>
                    @error('image')
                        <p class="text-error-300 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Enlace -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                <div class="p-6 space-y-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-black-500 mb-1">Enlace</h3>
                        <p class="text-sm text-black-300">Configura hacia dónde dirigirá el slider</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">Tipo de enlace</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="url_type" value="none" {{ old('url_type', 'none') == 'none' ? 'checked' : '' }}
                                       class="border-white-300 text-primary-300 focus:ring-primary-200">
                                <span class="ml-2 text-sm text-black-400">Sin enlace</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="url_type" value="internal" {{ old('url_type') == 'internal' ? 'checked' : '' }}
                                       class="border-white-300 text-primary-300 focus:ring-primary-200">
                                <span class="ml-2 text-sm text-black-400">Enlace interno</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="url_type" value="external" {{ old('url_type') == 'external' ? 'checked' : '' }}
                                       class="border-white-300 text-primary-300 focus:ring-primary-200">
                                <span class="ml-2 text-sm text-black-400">Enlace externo</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">URL</label>
                        <input type="url" name="url" value="{{ old('url') }}"
                               class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                      focus:ring-2 focus:ring-primary-200 focus:border-transparent"
                               placeholder="https://ejemplo.com o /categoria/ropa">
                        <p class="text-xs text-black-300 mt-1">
                            Interna: /categoria/ropa, /producto/camiseta<br>
                            Externa: https://instagram.com/mitienda
                        </p>
                        @error('url')
                            <p class="text-error-300 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Programación -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
                <div class="p-6 space-y-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-black-500 mb-1">Programación</h3>
                        <p class="text-sm text-black-300">Configura cuándo se mostrará el slider (opcional)</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_scheduled" value="1" {{ old('is_scheduled') ? 'checked' : '' }}
                                   x-model="isScheduled"
                                   class="rounded border-white-300 text-primary-300 focus:ring-primary-200">
                            <span class="ml-2 text-sm text-black-400">Programar slider</span>
                        </label>
                    </div>

                    <div x-show="isScheduled" class="space-y-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_permanent" value="1" {{ old('is_permanent') ? 'checked' : '' }}
                                       x-model="isPermanent"
                                       class="rounded border-white-300 text-primary-300 focus:ring-primary-200">
                                <span class="ml-2 text-sm text-black-400">Slider permanente (sin fecha fin)</span>
                            </label>
                        </div>

                        <div x-show="!isPermanent" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Fecha inicio</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}"
                                       class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                              focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Fecha fin</label>
                                <input type="date" name="end_date" value="{{ old('end_date') }}"
                                       class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                              focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Hora inicio</label>
                                <input type="time" name="start_time" value="{{ old('start_time') }}"
                                       class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                              focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-2">Hora fin</label>
                                <input type="time" name="end_time" value="{{ old('end_time') }}"
                                       class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                              focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">Días de la semana</label>
                            <div class="grid grid-cols-4 md:grid-cols-7 gap-2">
                                @foreach(['monday' => 'L', 'tuesday' => 'M', 'wednesday' => 'X', 'thursday' => 'J', 'friday' => 'V', 'saturday' => 'S', 'sunday' => 'D'] as $day => $label)
                                    <label class="flex items-center justify-center p-2 border border-white-200 rounded-lg cursor-pointer hover:bg-white-100">
                                        <input type="checkbox" name="scheduled_days[{{ $day }}]" value="1" {{ old("scheduled_days.{$day}") ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <span class="text-sm font-medium text-black-400 peer-checked:text-primary-300">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="bg-white-100 px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <a href="{{ route('tenant.admin.sliders.index', $store->slug) }}" 
                       class="btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
                <button type="submit" class="btn-primary">
                    Crear Slider
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sliderForm', () => ({
                isScheduled: {{ old('is_scheduled') ? 'true' : 'false' }},
                isPermanent: {{ old('is_permanent') ? 'true' : 'false' }}
            }));
        });
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 