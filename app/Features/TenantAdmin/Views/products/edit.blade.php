<x-tenant-admin-layout :store="$store">
    @section('title', 'Editar Producto')

    @section('content')
    <div class="space-y-4">
        <!-- Header con contador y botón crear -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('tenant.admin.products.index', [$store->slug]) }}" 
                           class="text-black-400 hover:text-primary-300 transition-colors">
                            <x-solar-arrow-left-outline class="w-6 h-6" />
                        </a>
                        <div>
                            <h2 class="text-lg font-semibold text-black-500 mb-2">Editar Producto</h2>
                            <p class="text-sm text-black-300">{{ $product->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-success-200 text-black-300' : 'bg-error-200 text-accent-50' }}">
                            {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('tenant.admin.products.update', [$store->slug, $product->id]) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            
            <!-- Información Básica -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h3 class="text-lg font-semibold text-black-500">Información Básica</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-black-400 mb-2">
                                Nombre del Producto <span class="text-error-400">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $product->name) }}"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('name') border-error-300 @enderror"
                                   placeholder="Ej: Camiseta Básica Blanca"
                                   required>
                            @error('name')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-black-400 mb-2">
                                SKU (Código)
                            </label>
                            <input type="text" 
                                   id="sku" 
                                   name="sku" 
                                   value="{{ old('sku', $product->sku) }}"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('sku') border-error-300 @enderror"
                                   placeholder="Ej: CAM-BAS-001">
                            @error('sku')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-black-400 mb-2">
                            Descripción
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('description') border-error-300 @enderror"
                                  placeholder="Describe las características principales del producto...">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Precio -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-black-400 mb-2">
                                Precio <span class="text-error-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-black-400">$</span>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price', $product->price) }}"
                                       min="0" 
                                       step="0.01"
                                       class="w-full pl-8 pr-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('price') border-error-300 @enderror"
                                       placeholder="15000"
                                       required>
                            </div>
                            @error('price')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-black-400 mb-2">
                                Tipo de Producto <span class="text-error-400">*</span>
                            </label>
                            <select id="type" 
                                    name="type" 
                                    class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('type') border-error-300 @enderror"
                                    required>
                                <option value="">Selecciona un tipo</option>
                                <option value="simple" {{ old('type', $product->type) === 'simple' ? 'selected' : '' }}>Simple</option>
                                <option value="variable" {{ old('type', $product->type) === 'variable' ? 'selected' : '' }}>Variable</option>
                            </select>
                            @error('type')
                                <p class="text-sm text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-accent-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                        </label>
                        <span class="text-sm text-black-400">Producto activo</span>
                    </div>
                </div>
            </div>

            <!-- Imágenes Actuales -->
            @if($product->images && $product->images->count() > 0)
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h3 class="text-lg font-semibold text-black-500">Imágenes Actuales</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($product->images as $image)
                        <div class="relative group">
                            <img src="{{ $image->image_url }}" class="w-full h-24 object-cover rounded-lg border border-accent-200">
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <label class="flex items-center bg-error-400 text-accent-50 rounded-full p-1 cursor-pointer">
                                    <input type="checkbox" 
                                           name="delete_images[]" 
                                           value="{{ $image->id }}" 
                                           class="sr-only delete-image-checkbox"
                                           onchange="toggleImageForDeletion(this)">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                </label>
                            </div>
                            <div class="absolute bottom-1 left-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if($image->is_main)
                                <span class="bg-primary-400 text-accent-50 rounded-full px-2 py-1 text-xs">
                                    Principal
                                </span>
                                @else
                                <button type="button" 
                                        onclick="setMainImage({{ $image->id }})"
                                        class="bg-black-500 bg-opacity-75 text-accent-50 rounded-full px-2 py-1 text-xs hover:bg-opacity-100">
                                    Hacer principal
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-sm text-black-300 mt-4">
                        Marca las imágenes que deseas eliminar con el ícono de basura. Las imágenes marcadas se eliminarán al guardar.
                    </p>
                </div>
            </div>
            @endif

            <!-- Nuevas Imágenes -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h3 class="text-lg font-semibold text-black-500">Agregar Nuevas Imágenes</h3>
                </div>
                <div class="p-6">
                    <div class="border-2 border-dashed border-primary-300 rounded-lg p-8 text-center bg-primary-50 hover:bg-primary-100 transition-colors duration-200 cursor-pointer" id="image-upload-area">
                        <div class="flex flex-col items-center justify-center gap-4">
                            <x-solar-cloud-upload-outline class="w-12 h-12 text-primary-300" />
                            <div>
                                <p class="text-base text-primary-300 mb-2">Arrastra y suelta las imágenes aquí o</p>
                                <p class="text-primary-300 text-base font-semibold">haz clic para seleccionar</p>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" class="hidden">
                            </div>
                            <p class="text-sm text-black-400">Soporta múltiples imágenes (JPG, PNG, WEBP)</p>
                        </div>
                    </div>
                    <div id="image-previews" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                </div>
            </div>

            <!-- Categorías -->
            @if($categories->count() > 0)
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h3 class="text-lg font-semibold text-black-500">Categorías</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($categories as $category)
                        <label class="flex items-center gap-3 p-3 border border-accent-200 rounded-lg hover:bg-accent-100 cursor-pointer">
                            <input type="checkbox" 
                                   name="categories[]" 
                                   value="{{ $category->id }}"
                                   {{ $product->categories->contains($category->id) ? 'checked' : '' }}
                                   class="rounded border-accent-300 text-primary-300 focus:ring-primary-200">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-black-400">{{ $category->name }}</span>
                                @if($category->description)
                                <p class="text-xs text-black-300">{{ $category->description }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Variables (solo si hay) -->
            @if($variables->count() > 0)
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden" id="variables-section" style="display: {{ $product->type === 'variable' ? 'block' : 'none' }};">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h3 class="text-lg font-semibold text-black-500">Variables del Producto</h3>
                    <p class="text-sm text-black-300">Solo para productos variables</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($variables as $variable)
                        <label class="flex items-center gap-3 p-3 border border-accent-200 rounded-lg hover:bg-accent-100 cursor-pointer">
                            <input type="checkbox" 
                                   name="variables[]" 
                                   value="{{ $variable->id }}"
                                   {{ $product->variables && $product->variables->contains($variable->id) ? 'checked' : '' }}
                                   class="rounded border-accent-300 text-primary-300 focus:ring-primary-200">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-black-400">{{ $variable->name }}</span>
                                <p class="text-xs text-black-300">{{ $variable->type_name }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Botones de acción -->
            <div class="bg-accent-50 rounded-lg p-6">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('tenant.admin.products.show', [$store->slug, $product->id]) }}" 
                       class="btn-secondary flex items-center gap-2">
                        <x-solar-arrow-left-outline class="w-4 h-4" />
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary flex items-center gap-2">
                        <x-solar-diskette-outline class="w-4 h-4" />
                        Actualizar Producto
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Mostrar/ocultar sección de variables según el tipo
        document.getElementById('type').addEventListener('change', function() {
            const variablesSection = document.getElementById('variables-section');
            if (this.value === 'variable') {
                variablesSection.style.display = 'block';
            } else {
                variablesSection.style.display = 'none';
            }
        });

        // Drag & Drop para imágenes (mismo código que en create)
        const uploadArea = document.getElementById('image-upload-area');
        const fileInput = document.getElementById('images');
        const previewsContainer = document.getElementById('image-previews');
        let selectedFiles = [];

        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('drop', handleDrop);
        fileInput.addEventListener('change', handleFileSelect);

        function handleDragOver(e) {
            e.preventDefault();
            uploadArea.classList.add('bg-primary-100');
        }

        function handleDrop(e) {
            e.preventDefault();
            uploadArea.classList.remove('bg-primary-100');
            const files = Array.from(e.dataTransfer.files);
            handleFiles(files);
        }

        function handleFileSelect(e) {
            const files = Array.from(e.target.files);
            handleFiles(files);
        }

        function handleFiles(files) {
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    selectedFiles.push(file);
                    createImagePreview(file);
                }
            });
            updateFileInput();
        }

        function createImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group';
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border border-accent-200">
                    <button type="button" class="absolute top-1 right-1 bg-error-400 text-accent-50 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="removeImage(this, '${file.name}')">
                        <x-solar-close-circle-outline class="w-4 h-4" />
                    </button>
                    <p class="text-xs text-black-300 mt-1 truncate">${file.name}</p>
                `;
                previewsContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        }

        function removeImage(button, fileName) {
            selectedFiles = selectedFiles.filter(file => file.name !== fileName);
            button.parentElement.remove();
            updateFileInput();
        }

        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;
        }

        // Función para marcar/desmarcar imágenes para eliminar
        function toggleImageForDeletion(checkbox) {
            const imageDiv = checkbox.closest('.group');
            if (checkbox.checked) {
                imageDiv.classList.add('opacity-50');
                imageDiv.querySelector('img').style.filter = 'grayscale(100%)';
            } else {
                imageDiv.classList.remove('opacity-50');
                imageDiv.querySelector('img').style.filter = 'none';
            }
        }

        // Función para establecer imagen principal
        function setMainImage(imageId) {
            if (confirm('¿Establecer esta imagen como principal?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/{{ $store->slug }}/admin/products/{{ $product->id }}/set-main-image`;
                form.innerHTML = `
                    @csrf
                    <input type="hidden" name="image_id" value="${imageId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 