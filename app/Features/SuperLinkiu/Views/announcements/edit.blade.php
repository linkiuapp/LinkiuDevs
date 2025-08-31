@extends('shared::layouts.admin')

@section('title', 'Editar Anuncio')

@section('content')
<div class="container-fluid" x-data="editAnnouncement()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Editar Anuncio</h1>
            <p class="text-sm text-black-300">{{ $announcement->title }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superlinkiu.announcements.show', $announcement) }}" 
               class="btn-outline-info px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-eye-outline class="w-4 h-4" />
                Ver Detalle
            </a>
            <a href="{{ route('superlinkiu.announcements.index') }}" 
               class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
        </div>
    </div>

    <form action="{{ route('superlinkiu.announcements.update', $announcement) }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                                   value="{{ old('title', $announcement->title) }}"
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
                                    <option value="critical" {{ old('type', $announcement->type) === 'critical' ? 'selected' : '' }}>🚨 Crítico</option>
                                    <option value="important" {{ old('type', $announcement->type) === 'important' ? 'selected' : '' }}>⭐ Importante</option>
                                    <option value="info" {{ old('type', $announcement->type) === 'info' ? 'selected' : '' }}>ℹ️ Información</option>
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
                                    <option value="10" {{ old('priority', $announcement->priority) == 10 ? 'selected' : '' }}>10 - Muy Alta</option>
                                    <option value="9" {{ old('priority', $announcement->priority) == 9 ? 'selected' : '' }}>9 - Muy Alta</option>
                                    <option value="8" {{ old('priority', $announcement->priority) == 8 ? 'selected' : '' }}>8 - Muy Alta</option>
                                    <option value="7" {{ old('priority', $announcement->priority) == 7 ? 'selected' : '' }}>7 - Alta</option>
                                    <option value="6" {{ old('priority', $announcement->priority) == 6 ? 'selected' : '' }}>6 - Alta</option>
                                    <option value="5" {{ old('priority', $announcement->priority) == 5 ? 'selected' : '' }}>5 - Media</option>
                                    <option value="4" {{ old('priority', $announcement->priority) == 4 ? 'selected' : '' }}>4 - Media</option>
                                    <option value="3" {{ old('priority', $announcement->priority) == 3 ? 'selected' : '' }}>3 - Baja</option>
                                    <option value="2" {{ old('priority', $announcement->priority) == 2 ? 'selected' : '' }}>2 - Baja</option>
                                    <option value="1" {{ old('priority', $announcement->priority) == 1 ? 'selected' : '' }}>1 - Baja</option>
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
                                      required>{{ old('content', $announcement->content) }}</textarea>
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
                                   {{ old('show_as_banner', $announcement->show_as_banner) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <label class="text-sm font-medium text-black-400">
                                Mostrar como banner deslizable en dashboard
                            </label>
                        </div>

                        <div x-show="formData.showAsBanner" x-transition class="space-y-4">
                            <!-- Banner Actual -->
                            @if($announcement->banner_image)
                                <div class="p-4 bg-info-50 rounded-lg border border-info-100">
                                    <h4 class="text-sm font-medium text-info-300 mb-2">Banner Actual</h4>
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $announcement->banner_image_url }}" 
                                             alt="Banner actual" 
                                             class="border border-accent-200 rounded"
                                             style="width: 160px; height: 50px; object-fit: cover;">
                                        <div class="flex-1">
                                            <p class="text-sm text-black-400">{{ $announcement->banner_image }}</p>
                                            <label class="flex items-center gap-2 mt-2">
                                                <input type="checkbox" 
                                                       name="remove_banner" 
                                                       value="1"
                                                       x-model="formData.removeBanner"
                                                       class="h-4 w-4 text-error-300 focus:ring-error-200 border-accent-200 rounded">
                                                <span class="text-sm text-error-300">Eliminar banner actual</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div x-show="!formData.removeBanner">
                                <label class="block text-sm font-medium text-black-300 mb-2">
                                    {{ $announcement->banner_image ? 'Reemplazar Imagen del Banner' : 'Imagen del Banner' }} (1570x300px)
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
                                       value="{{ old('banner_link', $announcement->banner_link) }}"
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
                <!-- Estadísticas -->
                <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Estadísticas</h2>
                    </div>
                    
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-300">Creado:</span>
                            <span class="text-sm text-black-400">{{ $announcement->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-300">Modificado:</span>
                            <span class="text-sm text-black-400">{{ $announcement->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-300">Lecturas:</span>
                            <span class="text-sm text-black-400">{{ $announcement->reads->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-300">Estado:</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $announcement->is_active ? 'success' : 'black' }}-100 text-{{ $announcement->is_active ? 'success' : 'black' }}-300">
                                {{ $announcement->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                </div>

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
                                               {{ in_array($plan, old('target_plans', $announcement->target_plans ?? [])) ? 'checked' : '' }}
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
                                   value="{{ old('published_at', $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('published_at') border-error-200 @enderror">
                            @error('published_at')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Fecha de Expiración
                            </label>
                            <input type="datetime-local" 
                                   name="expires_at" 
                                   value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('expires_at') border-error-200 @enderror">
                            @error('expires_at')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
                                   {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <span class="text-sm text-black-400">Activo</span>
                        </label>

                        <label class="flex items-center gap-3" x-show="formData.type === 'critical'">
                            <input type="checkbox" 
                                   name="show_popup" 
                                   value="1"
                                   {{ old('show_popup', $announcement->show_popup) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <span class="text-sm text-black-400">Mostrar popup automático</span>
                        </label>

                        <label class="flex items-center gap-3" x-show="formData.type === 'critical'">
                            <input type="checkbox" 
                                   name="send_email" 
                                   value="1"
                                   {{ old('send_email', $announcement->send_email) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary-300 focus:ring-primary-200 border-accent-200 rounded">
                            <span class="text-sm text-black-400">Enviar email</span>
                        </label>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Auto-marcar como leído (días)
                            </label>
                            <input type="number" 
                                   name="auto_mark_read_after" 
                                   value="{{ old('auto_mark_read_after', $announcement->auto_mark_read_after) }}"
                                   min="1" 
                                   max="365"
                                   placeholder="Opcional"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('auto_mark_read_after') border-error-200 @enderror">
                            @error('auto_mark_read_after')
                                <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="bg-accent-50 rounded-lg p-6">
                    <div class="space-y-3">
                        <button type="submit" 
                                class="w-full btn-primary px-4 py-3 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-diskette-outline class="w-5 h-5" />
                            Actualizar Anuncio
                        </button>
                        
                        <a href="{{ route('superlinkiu.announcements.show', $announcement) }}" 
                           class="w-full btn-outline-info px-4 py-3 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-eye-outline class="w-5 h-5" />
                            Ver Detalle
                        </a>

                        <form method="POST" 
                              action="{{ route('superlinkiu.announcements.duplicate', $announcement) }}" 
                              class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full btn-outline-warning px-4 py-3 rounded-lg flex items-center justify-center gap-2">
                                <x-solar-copy-outline class="w-5 h-5" />
                                Duplicar
                            </button>
                        </form>
                        
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
function editAnnouncement() {
    return {
        formData: {
            type: '{{ old('type', $announcement->type) }}',
            showAsBanner: {{ old('show_as_banner', $announcement->show_as_banner) ? 'true' : 'false' }},
            removeBanner: false
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