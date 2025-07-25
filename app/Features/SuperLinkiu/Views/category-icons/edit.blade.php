@extends('shared::layouts.admin')
@section('title', 'Editar Icono: ' . $categoryIcon->display_name)

@section('content')
<div class="flex-1 space-y-6">
    <!-- Header Section -->
    <div class="flex items-center gap-4">
        <a href="{{ route('superlinkiu.category-icons.index') }}" 
           class="p-2 text-black-300 hover:text-black-400 hover:bg-white-100 rounded-lg transition-colors">
            <x-solar-arrow-left-outline class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-3xl font-semibold text-black-400">Editar: {{ $categoryIcon->display_name }}</h1>
            <p class="text-black-300 mt-1">Modifica la información del icono de categoría</p>
        </div>
    </div>

    <!-- Usage Information -->
    @php
        $categoriesUsingIcon = \App\Features\TenantAdmin\Models\Category::where('icon_id', $categoryIcon->id)->count();
    @endphp
    @if($categoriesUsingIcon > 0)
        <div class="bg-info-50 border border-info-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-info-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <x-solar-info-circle-outline class="w-5 h-5 text-info-400" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-info-400 mb-1">Icono en uso</h3>
                    <p class="text-sm text-info-300">
                        Este icono está siendo usado por <strong>{{ $categoriesUsingIcon }}</strong> categoría{{ $categoriesUsingIcon !== 1 ? 's' : '' }} en las tiendas.
                        Los cambios se reflejarán automáticamente en todas las categorías que lo usen.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Form -->
    <div class="max-w-5xl">
        <form action="{{ route('superlinkiu.category-icons.update', $categoryIcon->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Main Content Card -->
            <div class="bg-white-50 rounded-xl shadow-sm border border-white-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-white-100 bg-gradient-to-r from-white-50 to-white-100">
                    <h2 class="text-xl font-semibold text-black-500">Información del Icono</h2>
                </div>

                <!-- Card Content -->
                <div class="p-6 space-y-8">
                    <!-- Current Icon and New Upload Section -->
                    <div x-data="iconPreview('{{ $categoryIcon->image_url }}')" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Current Icon Display -->
                            <div class="space-y-4">
                                <label class="block text-sm font-semibold text-black-400">
                                    Icono Actual
                                </label>
                                <div class="bg-white-100 rounded-xl p-6 text-center border-2 border-white-200">
                                    <div class="w-32 h-32 mx-auto bg-white-50 rounded-xl p-4 flex items-center justify-center shadow-inner mb-4">
                                        <img src="{{ $categoryIcon->image_url }}" 
                                             class="max-w-full max-h-full object-contain" 
                                             alt="{{ $categoryIcon->display_name }}">
                                    </div>
                                    <p class="text-sm font-medium text-black-400">{{ basename($categoryIcon->image_path) }}</p>
                                    <p class="text-xs text-black-300 mt-1">Archivo actual</p>
                                </div>
                            </div>

                            <!-- New File Upload (Optional) -->
                            <div class="space-y-4">
                                <label for="icon_file" class="block text-sm font-semibold text-black-400">
                                    Reemplazar Icono <span class="text-black-300">(Opcional)</span>
                                </label>
                                
                                <div class="relative border-2 border-dashed border-white-300 rounded-xl p-8 text-center hover:border-primary-200 hover:bg-primary-50 transition-all duration-200"
                                     @dragover.prevent="isDragging = true"
                                     @dragleave.prevent="isDragging = false"
                                     @drop.prevent="handleDrop"
                                     :class="{ 'border-primary-200 bg-primary-50': isDragging }">
                                    
                                    <template x-if="!preview">
                                        <div class="space-y-4">
                                            <div class="w-16 h-16 mx-auto bg-white-200 rounded-xl flex items-center justify-center">
                                                <x-solar-gallery-add-outline class="w-8 h-8 text-black-300" />
                                            </div>
                                            <div>
                                                <label for="icon_file" class="cursor-pointer">
                                                    <span class="text-primary-400 hover:text-primary-300 font-medium">Selecciona un nuevo archivo</span>
                                                    <span class="text-black-300"> o arrastra y suelta aquí</span>
                                                    <input id="icon_file" 
                                                           name="icon_file" 
                                                           type="file" 
                                                           class="sr-only" 
                                                           accept=".svg,.png,.jpg,.jpeg"
                                                           @change="handleFileSelect">
                                                </label>
                                            </div>
                                            <p class="text-sm text-black-300">
                                                Formatos: SVG, PNG, JPG • Tamaño máximo: 2MB
                                            </p>
                                        </div>
                                    </template>
                                    
                                    <template x-if="preview">
                                        <div class="relative">
                                            <div class="w-32 h-32 mx-auto bg-white-100 rounded-xl p-4 flex items-center justify-center shadow-inner">
                                                <img :src="preview" class="max-w-full max-h-full object-contain" alt="Preview">
                                            </div>
                                            <button type="button" 
                                                    @click="clearPreview"
                                                    class="absolute -top-2 -right-2 w-8 h-8 bg-error-400 text-white-50 rounded-full hover:bg-error-300 transition-colors flex items-center justify-center">
                                                <x-solar-close-circle-outline class="w-4 h-4" />
                                            </button>
                                            <p class="mt-3 text-sm font-medium text-black-400" x-text="fileName"></p>
                                            <p class="text-xs text-success-400">✓ Nuevo archivo seleccionado</p>
                                        </div>
                                    </template>
                                </div>
                                
                                @error('icon_file')
                                    <p class="text-sm text-error-400 flex items-center gap-2">
                                        <x-solar-info-circle-outline class="w-4 h-4" />
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Preview of New Icon (only if file selected) -->
                        <div x-show="preview" x-transition class="space-y-4">
                            <label class="block text-sm font-semibold text-black-400">
                                Vista Previa del Nuevo Icono
                            </label>
                            
                            <div class="bg-white-100 rounded-xl p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <!-- Small -->
                                    <div class="text-center p-4 bg-white-50 rounded-lg">
                                        <div class="w-8 h-8 bg-white-200 rounded-lg p-1 flex items-center justify-center mx-auto mb-3">
                                            <img :src="preview" class="w-full h-full object-contain" alt="Pequeño">
                                        </div>
                                        <span class="text-sm font-medium text-black-400">Pequeño</span>
                                        <p class="text-xs text-black-300">32×32px</p>
                                    </div>
                                    
                                    <!-- Medium -->
                                    <div class="text-center p-4 bg-white-50 rounded-lg">
                                        <div class="w-12 h-12 bg-white-200 rounded-lg p-2 flex items-center justify-center mx-auto mb-3">
                                            <img :src="preview" class="w-full h-full object-contain" alt="Mediano">
                                        </div>
                                        <span class="text-sm font-medium text-black-400">Mediano</span>
                                        <p class="text-xs text-black-300">48×48px</p>
                                    </div>
                                    
                                    <!-- Large -->
                                    <div class="text-center p-4 bg-white-50 rounded-lg">
                                        <div class="w-16 h-16 bg-white-200 rounded-lg p-3 flex items-center justify-center mx-auto mb-3">
                                            <img :src="preview" class="w-full h-full object-contain" alt="Grande">
                                        </div>
                                        <span class="text-sm font-medium text-black-400">Grande</span>
                                        <p class="text-xs text-black-300">64×64px</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Display Name -->
                        <div class="space-y-2">
                            <label for="display_name" class="block text-sm font-semibold text-black-400">
                                Nombre para Mostrar <span class="text-error-400">*</span>
                            </label>
                            <input type="text" 
                                   name="display_name" 
                                   id="display_name"
                                   value="{{ old('display_name', $categoryIcon->display_name) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-primary-200 transition-colors
                                          @error('display_name') border-error-200 focus:ring-error-200 @enderror"
                                   placeholder="Ej: Hamburguesas, Pizza, Bebidas..."
                                   required>
                            @error('display_name')
                                <p class="text-sm text-error-400 flex items-center gap-2">
                                    <x-solar-info-circle-outline class="w-4 h-4" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Internal Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-semibold text-black-400">
                                Nombre Interno (Slug) <span class="text-error-400">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name', $categoryIcon->name) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-primary-200 transition-colors
                                          @error('name') border-error-200 focus:ring-error-200 @enderror"
                                   required>
                            <p class="text-xs text-black-300 flex items-center gap-1">
                                <x-solar-info-circle-outline class="w-3 h-3" />
                                Debe ser único y sin espacios
                            </p>
                            @error('name')
                                <p class="text-sm text-error-400 flex items-center gap-2">
                                    <x-solar-info-circle-outline class="w-4 h-4" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Configuration -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-black-400">Estado</label>
                            <div class="flex items-center gap-3 p-4 bg-white-100 rounded-lg">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           value="1"
                                           class="sr-only peer"
                                           {{ old('is_active', $categoryIcon->is_active) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-white-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-200 rounded-full peer 
                                                peer-checked:after:translate-x-full peer-checked:after:border-white-50 
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                                after:bg-white-50 after:rounded-full after:h-5 after:w-5 after:transition-all 
                                                peer-checked:bg-success-300"></div>
                                </label>
                                <div>
                                    <span class="text-sm font-medium text-black-400">Icono activo</span>
                                    <p class="text-xs text-black-300">Los iconos inactivos no aparecen en las tiendas</p>
                                    @if($categoriesUsingIcon > 0)
                                        <p class="text-xs text-warning-400 mt-1 flex items-center gap-1">
                                            <x-solar-danger-triangle-outline class="w-3 h-3" />
                                            Desactivar ocultará este icono de {{ $categoriesUsingIcon }} categoría{{ $categoriesUsingIcon !== 1 ? 's' : '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="space-y-2">
                            <label for="sort_order" class="block text-sm font-semibold text-black-400">
                                Orden de Visualización
                            </label>
                            <input type="number" 
                                   name="sort_order" 
                                   id="sort_order"
                                   value="{{ old('sort_order', $categoryIcon->sort_order) }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-primary-200 transition-colors
                                          @error('sort_order') border-error-200 focus:ring-error-200 @enderror"
                                   min="0">
                            <p class="text-xs text-black-300 flex items-center gap-1">
                                <x-solar-info-circle-outline class="w-3 h-3" />
                                Menor número aparece primero
                            </p>
                            @error('sort_order')
                                <p class="text-sm text-error-400 flex items-center gap-2">
                                    <x-solar-info-circle-outline class="w-4 h-4" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between p-6 bg-white-50 rounded-xl border border-white-100">
                <a href="{{ route('superlinkiu.category-icons.index') }}" 
                   class="btn-secondary flex items-center gap-2">
                    <x-solar-arrow-left-outline class="w-4 h-4" />
                    Cancelar
                </a>
                
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <x-solar-diskette-outline class="w-4 h-4" />
                    Actualizar Icono
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function iconPreview(currentImageUrl = null) {
        return {
            preview: null,
            fileName: '',
            isDragging: false,
            currentImage: currentImageUrl,
            
            handleFileSelect(event) {
                const file = event.target.files[0];
                this.processFile(file);
            },
            
            handleDrop(event) {
                this.isDragging = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    document.getElementById('icon_file').files = event.dataTransfer.files;
                    this.processFile(file);
                }
            },
            
            processFile(file) {
                if (file && file.type.startsWith('image/')) {
                    this.fileName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.preview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else if (file) {
                    alert('Por favor selecciona un archivo de imagen válido (SVG, PNG, JPG)');
                }
            },
            
            clearPreview() {
                this.preview = null;
                this.fileName = '';
                document.getElementById('icon_file').value = '';
            }
        }
    }
</script>
@endpush
@endsection 