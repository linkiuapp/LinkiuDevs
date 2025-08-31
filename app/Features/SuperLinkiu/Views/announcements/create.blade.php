@extends('shared::layouts.admin')

@section('title', 'Crear Anuncio')

@section('content')
<div class="container-fluid" x-data="createAnnouncement()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Crear Nuevo Anuncio</h1>
            <p class="text-sm text-black-300">Comunica actualizaciones importantes a las tiendas</p>
        </div>
        <a href="{{ route('superlinkiu.announcements.index') }}" 
           class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-4 h-4" />
            Volver
        </a>
    </div>

    <form action="{{ route('superlinkiu.announcements.store') }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Contenido Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Información Básica -->
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Información Básica</h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Título del Anuncio <span class="text-error-300">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Ej: Mantenimiento Programado del Sistema"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('title') border-error-200 @enderror"
                                   required>
                            @error('title')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    Tipo <span class="text-error-300">*</span>
                                </label>
                                <select name="type" 
                                        x-model="formData.type"
                                        class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('type') border-error-200 @enderror"
                                        required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="critical" {{ old('type') === 'critical' ? 'selected' : '' }}>🚨 Crítico</option>
                                    <option value="important" {{ old('type') === 'important' ? 'selected' : '' }}>⭐ Importante</option>
                                    <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>ℹ️ Información</option>
                                </select>
                                @error('type')
                                    <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    Prioridad <span class="text-error-300">*</span>
                                </label>
                                <select name="priority" 
                                        class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('priority') border-error-200 @enderror"
                                        required>
                                    <option value="10" {{ old('priority', 5) == 10 ? 'selected' : '' }}>10 - Muy Alta</option>
                                    <option value="9" {{ old('priority', 5) == 9 ? 'selected' : '' }}>9 - Muy Alta</option>
                                    <option value="8" {{ old('priority', 5) == 8 ? 'selected' : '' }}>8 - Muy Alta</option>
                                    <option value="7" {{ old('priority', 5) == 7 ? 'selected' : '' }}>7 - Alta</option>
                                    <option value="6" {{ old('priority', 5) == 6 ? 'selected' : '' }}>6 - Alta</option>
                                    <option value="5" {{ old('priority', 5) == 5 ? 'selected' : '' }}>5 - Media</option>
                                    <option value="4" {{ old('priority', 5) == 4 ? 'selected' : '' }}>4 - Media</option>
                                    <option value="3" {{ old('priority', 5) == 3 ? 'selected' : '' }}>3 - Baja</option>
                                    <option value="2" {{ old('priority', 5) == 2 ? 'selected' : '' }}>2 - Baja</option>
                                    <option value="1" {{ old('priority', 5) == 1 ? 'selected' : '' }}>1 - Baja</option>
                                </select>
                                @error('priority')
                                    <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Contenido del Anuncio <span class="text-error-300">*</span>
                            </label>
                            <textarea name="content" 
                                      rows="8"
                                      placeholder="Describe detalladamente el contenido del anuncio..."
                                      class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('content') border-error-200 @enderror"
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Banner Configuration -->
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Configuración de Banner</h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" 
                                   name="show_as_banner" 
                                   value="1"
                                   x-model="formData.showAsBanner"
                                   {{ old('show_as_banner') ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <label class="text-sm font-medium text-black-400">
                                Mostrar como banner deslizable en dashboard
                            </label>
                        </div>

                        <div x-show="formData.showAsBanner" x-transition class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    Imagen del Banner (516x200px)
                                </label>
                                <input type="file" 
                                       name="banner_image" 
                                       accept="image/*"
                                       @change="handleImagePreview"
                                       class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('banner_image') border-error-200 @enderror">
                                @error('banner_image')
                                    <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-black-200 mt-1">
                                    Formatos: JPG, PNG, WebP. Tamaño exacto: 628x200 píxeles. Máximo 2MB.
                                </p>
                                
                                <div x-show="imagePreview" class="mt-3">
                                    <img :src="imagePreview" 
                                         alt="Preview" 
                                         class="border border-accent-200 rounded" 
                                         style="width: 320px; height: 100px; object-fit: cover;">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    Enlace del Banner (Opcional)
                                </label>
                                <input type="url" 
                                       name="banner_link" 
                                       value="{{ old('banner_link') }}"
                                       placeholder="https://..."
                                       class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('banner_link') border-error-200 @enderror">
                                @error('banner_link')
                                    <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-black-200 mt-1">
                                    URL a la que dirigir cuando hagan clic en el banner
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración Lateral -->
            <div class="space-y-6">
                <!-- Segmentación -->
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Segmentación</h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-3">Planes Target</label>
                            <div class="space-y-2">
                                @foreach(['explorer' => 'Explorer', 'master' => 'Master', 'legend' => 'Legend'] as $plan => $label)
                                    <label class="flex items-center gap-3 p-2 hover:bg-accent-100 rounded cursor-pointer">
                                        <input type="checkbox" 
                                               name="target_plans[]" 
                                               value="{{ $plan }}"
                                               {{ in_array($plan, old('target_plans', [])) ? 'checked' : '' }}
                                               class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                                        <span class="text-sm text-black-400">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-black-200 mt-2">
                                Si no seleccionas ninguno, se mostrará a todos los planes
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Programación</h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Fecha de Publicación
                            </label>
                            <input type="datetime-local" 
                                   name="published_at" 
                                   value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('published_at') border-error-200 @enderror">
                            @error('published_at')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-black-200 mt-1">
                                Déjalo vacío para publicar inmediatamente
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Fecha de Expiración
                            </label>
                            <input type="datetime-local" 
                                   name="expires_at" 
                                   value="{{ old('expires_at') }}"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('expires_at') border-error-200 @enderror">
                            @error('expires_at')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-black-200 mt-1">
                                Déjalo vacío para que sea permanente
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Comportamiento -->
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Comportamiento</h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <span class="text-sm text-black-400">Activar inmediatamente</span>
                        </label>

                        <label class="flex items-center gap-3" x-show="formData.type === 'critical'">
                            <input type="checkbox" 
                                   name="show_popup" 
                                   value="1"
                                   {{ old('show_popup') ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <span class="text-sm text-black-400">Mostrar popup automático</span>
                        </label>

                        <label class="flex items-center gap-3" x-show="formData.type === 'critical'">
                            <input type="checkbox" 
                                   name="send_email" 
                                   value="1"
                                   {{ old('send_email') ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <span class="text-sm text-black-400">Enviar email</span>
                        </label>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Auto-marcar como leído (días)
                            </label>
                            <input type="number" 
                                   name="auto_mark_read_after" 
                                   value="{{ old('auto_mark_read_after') }}"
                                   min="1" 
                                   max="365"
                                   placeholder="Opcional"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('auto_mark_read_after') border-error-200 @enderror">
                            @error('auto_mark_read_after')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-black-200 mt-1">
                                Días después de los cuales se marcará automáticamente como leído
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="bg-accent-50 rounded-lg p-6">
                    <div class="space-y-3">
                        <button type="submit" 
                                class="w-full btn-primary px-4 py-3 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-diskette-outline class="w-5 h-5" />
                            Crear Anuncio
                        </button>
                        
                        <a href="{{ route('superlinkiu.announcements.index') }}" 
                           class="w-full btn-outline-secondary px-4 py-3 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-close-circle-outline class="w-5 h-5" />
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function createAnnouncement() {
    return {
        formData: {
            type: '{{ old('type', 'info') }}',
            showAsBanner: {{ old('show_as_banner') ? 'true' : 'false' }}
        },
        imagePreview: null,
        
        handleImagePreview(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.imagePreview = null;
            }
        }
    }
}
</script>
@endpush
@endsection 