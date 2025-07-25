@extends('shared::layouts.admin')
@section('title', 'Nuevo Icono de Categoría')

@section('content')
<div class="flex-1 space-y-6">
    <!-- Header Section -->
    <div class="flex items-center gap-4">
        <a href="{{ route('superlinkiu.category-icons.index') }}" 
           class="p-2 text-black-300 hover:text-black-400 hover:bg-white-100 rounded-lg transition-colors">
            <x-solar-arrow-left-outline class="w-5 h-5" />
        </a>
        <div>
            <h1 class="text-3xl font-semibold text-black-400">Nuevo Icono de Categoría</h1>
            <p class="text-black-300 mt-1">Agrega un nuevo icono que estará disponible para las tiendas</p>
        </div>
    </div>

    <!-- Main Form -->
    <div class="max-w-5xl">
        <form action="{{ route('superlinkiu.category-icons.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Main Content Card -->
            <div class="bg-white-50 rounded-xl shadow-sm border border-white-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-white-100 bg-gradient-to-r from-white-50 to-white-100">
                    <h2 class="text-xl font-semibold text-black-500">Información del Icono</h2>
                </div>

                <!-- Card Content -->
                <div class="p-6 space-y-8">
                                                        <!-- File Upload Section -->
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- File Upload Area -->
                            <div class="space-y-4">
                                <label for="icon_file" class="block text-sm font-semibold text-black-400">
                                    Archivo del Icono <span class="text-error-400">*</span>
                                </label>
                                
                                <div class="relative border-2 border-dashed border-white-300 rounded-xl p-8 text-center hover:border-primary-200 hover:bg-primary-50 transition-all duration-200">
                                    
                                    <!-- Default Upload State -->
                                    <div id="upload-area" class="space-y-4">
                                        <div class="w-16 h-16 mx-auto bg-white-200 rounded-xl flex items-center justify-center">
                                            <x-solar-gallery-add-outline class="w-8 h-8 text-black-300" />
                                        </div>
                                        <div>
                                            <label for="icon_file" class="cursor-pointer">
                                                <span class="text-primary-400 hover:text-primary-300 font-medium">Selecciona un archivo</span>
                                                <span class="text-black-300"> o arrastra y suelta aquí</span>
                                            </label>
                                        </div>
                                        <p class="text-sm text-black-300">
                                            Formatos: SVG, PNG, JPG • Tamaño máximo: 2MB
                                        </p>
                                    </div>

                                    <!-- Preview State (hidden initially) -->
                                    <div id="preview-area" class="space-y-4 hidden">
                                        <div class="relative">
                                            <div class="w-32 h-32 mx-auto bg-white-100 rounded-xl p-4 flex items-center justify-center shadow-inner">
                                                <img id="preview-image" class="max-w-full max-h-full object-contain" alt="Preview">
                                            </div>
                                            <button type="button" 
                                                    onclick="clearPreview()"
                                                    class="absolute -top-2 -right-2 w-8 h-8 bg-error-400 text-white-50 rounded-full hover:bg-error-300 transition-colors flex items-center justify-center">
                                                <x-solar-close-circle-outline class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <p id="file-name" class="text-sm font-medium text-black-400"></p>
                                        <p class="text-xs text-success-400">✓ Archivo seleccionado</p>
                                    </div>

                                    <!-- Hidden File Input -->
                                    <input id="icon_file" 
                                           name="icon_file" 
                                           type="file" 
                                           class="sr-only" 
                                           accept=".svg,.png,.jpg,.jpeg"
                                           onchange="handleFileSelect(this)"
                                           required>
                                </div>
                                
                                @error('icon_file')
                                    <p class="text-sm text-error-400 flex items-center gap-2">
                                        <x-solar-info-circle-outline class="w-4 h-4" />
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Preview Sizes (only shown when file selected) -->
                            <div id="preview-sizes" class="space-y-4 hidden">
                                <label class="block text-sm font-semibold text-black-400">
                                    Vista Previa en Diferentes Tamaños
                                </label>
                                
                                <div class="bg-white-100 rounded-xl p-6 space-y-6">
                                    <div class="grid grid-cols-1 gap-4">
                                        <!-- Small -->
                                        <div class="flex items-center gap-4 p-3 bg-white-50 rounded-lg">
                                            <div class="w-8 h-8 bg-white-200 rounded-lg p-1 flex items-center justify-center">
                                                <img class="preview-small w-full h-full object-contain" alt="Pequeño">
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-black-400">Tamaño pequeño</span>
                                                <p class="text-xs text-black-300">32×32px • Para listas y menús</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Medium -->
                                        <div class="flex items-center gap-4 p-3 bg-white-50 rounded-lg">
                                            <div class="w-12 h-12 bg-white-200 rounded-lg p-2 flex items-center justify-center">
                                                <img class="preview-medium w-full h-full object-contain" alt="Mediano">
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-black-400">Tamaño mediano</span>
                                                <p class="text-xs text-black-300">48×48px • Para selector de categorías</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Large -->
                                        <div class="flex items-center gap-4 p-3 bg-white-50 rounded-lg">
                                            <div class="w-16 h-16 bg-white-200 rounded-lg p-3 flex items-center justify-center">
                                                <img class="preview-large w-full h-full object-contain" alt="Grande">
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-black-400">Tamaño grande</span>
                                                <p class="text-xs text-black-300">64×64px • Para vista de detalles</p>
                                            </div>
                                        </div>
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
                                   value="{{ old('display_name') }}"
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
                                Nombre Interno (Slug)
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name') }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-primary-200 transition-colors
                                          @error('name') border-error-200 focus:ring-error-200 @enderror"
                                   placeholder="Se genera automáticamente">
                            <p class="text-xs text-black-300 flex items-center gap-1">
                                <x-solar-info-circle-outline class="w-3 h-3" />
                                Déjalo vacío para generar automáticamente desde el nombre
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
                            <label class="block text-sm font-semibold text-black-400">Estado Inicial</label>
                            <div class="flex items-center gap-3 p-4 bg-white-100 rounded-lg">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           value="1"
                                           class="sr-only peer"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-white-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-200 rounded-full peer 
                                                peer-checked:after:translate-x-full peer-checked:after:border-white-50 
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                                after:bg-white-50 after:rounded-full after:h-5 after:w-5 after:transition-all 
                                                peer-checked:bg-success-300"></div>
                                </label>
                                <div>
                                    <span class="text-sm font-medium text-black-400">Icono activo</span>
                                    <p class="text-xs text-black-300">Los iconos inactivos no aparecen en las tiendas</p>
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
                                   value="{{ old('sort_order') }}"
                                   class="w-full px-4 py-3 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-primary-200 transition-colors
                                          @error('sort_order') border-error-200 focus:ring-error-200 @enderror"
                                   placeholder="Se asigna automáticamente"
                                   min="0">
                            <p class="text-xs text-black-300 flex items-center gap-1">
                                <x-solar-info-circle-outline class="w-3 h-3" />
                                Menor número aparece primero • Se asigna automáticamente si se deja vacío
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
                    Crear Icono
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Simple vanilla JavaScript for file preview (no Alpine.js)
    function handleFileSelect(input) {
        const file = input.files[0];
        
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Show preview area, hide upload area
                document.getElementById('upload-area').classList.add('hidden');
                document.getElementById('preview-area').classList.remove('hidden');
                document.getElementById('preview-sizes').classList.remove('hidden');
                
                // Set preview images
                const previewSrc = e.target.result;
                document.getElementById('preview-image').src = previewSrc;
                document.querySelector('.preview-small').src = previewSrc;
                document.querySelector('.preview-medium').src = previewSrc;
                document.querySelector('.preview-large').src = previewSrc;
                
                // Set filename
                document.getElementById('file-name').textContent = file.name;
            };
            reader.readAsDataURL(file);
        } else if (file) {
            alert('Por favor selecciona un archivo de imagen válido (SVG, PNG, JPG)');
            clearPreview();
        }
    }
    
    function clearPreview() {
        // Reset file input
        document.getElementById('icon_file').value = '';
        
        // Show upload area, hide preview
        document.getElementById('upload-area').classList.remove('hidden');
        document.getElementById('preview-area').classList.add('hidden');
        document.getElementById('preview-sizes').classList.add('hidden');
        
        // Clear preview images
        document.getElementById('preview-image').src = '';
        document.querySelector('.preview-small').src = '';
        document.querySelector('.preview-medium').src = '';
        document.querySelector('.preview-large').src = '';
        
        // Clear filename
        document.getElementById('file-name').textContent = '';
    }
    
    // Optional: Handle click on upload area to trigger file input
    document.getElementById('upload-area').addEventListener('click', function() {
        document.getElementById('icon_file').click();
    });
</script>
@endpush
@endsection 