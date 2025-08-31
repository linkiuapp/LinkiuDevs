<x-tenant-admin-layout :store="$store">
    @section('title', 'Nueva Categoría')

    @section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('tenant.admin.categories.index', $store->slug) }}" 
                   class="text-black-300 hover:text-black-400">
                    <x-solar-arrow-left-outline class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-black-500">Nueva Categoría</h1>
            </div>
            <div class="bg-info-50 border border-info-100 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <x-solar-info-circle-outline class="w-5 h-5 text-info-200 flex-shrink-0" />
                    <p class="text-sm text-info-200">
                        Estás usando <strong>{{ $totalCategories }}</strong> de <strong>{{ $categoryLimit }}</strong> categorías disponibles en tu plan {{ $store->plan->name }}.
                        @if($parentCategories->count() > 0)
                            Crear una subcategoría también cuenta para el límite.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form action="{{ route('tenant.admin.categories.store', $store->slug) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Card principal -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="p-6 space-y-6">
                    <!-- Selector de icono -->
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-3">
                            Icono de la categoría <span class="text-error-200">*</span>
                        </label>
                        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                            @foreach($icons as $icon)
                                <label class="relative cursor-pointer">
                                    <input type="radio" 
                                           name="icon_id" 
                                           value="{{ $icon->id }}" 
                                           class="sr-only peer"
                                           {{ old('icon_id') == $icon->id ? 'checked' : '' }}
                                           required>
                                    <div class="w-full aspect-square bg-accent-100 rounded-lg p-3 border-2 border-accent-200 
                                                peer-checked:border-primary-200 peer-checked:bg-primary-50 
                                                hover:border-accent-300 transition-all">
                                        <img src="{{ $icon->image_url }}" 
                                             alt="{{ $icon->display_name }}" 
                                             class="w-full h-full object-contain">
                                    </div>
                                    <span class="absolute bottom-0 left-0 right-0 text-center text-xs text-black-300 mt-4 mb-2">
                                        {{ $icon->display_name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('icon_id')
                            <p class="mt-2 text-sm text-error-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Información básica -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-black-400 mb-2">
                                Nombre <span class="text-error-200">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name') }}"
                                   class="w-full px-3 py-2 rounded-lg bg-accent-100 border border-accent-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-transparent
                                          @error('name') border-error-200 @enderror"
                                   placeholder="Ej: Hamburguesas"
                                   required>
                            @error('name')
                                <p class="mt-2 text-sm text-error-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-black-400 mb-2">
                                Slug (URL)
                            </label>
                            <input type="text" 
                                   name="slug" 
                                   id="slug"
                                   value="{{ old('slug') }}"
                                   class="w-full px-3 py-2 rounded-lg bg-accent-100 border border-accent-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-transparent
                                          @error('slug') border-error-200 @enderror"
                                   placeholder="Se genera automáticamente">
                            <p class="mt-1 text-xs text-black-300">
                                URL: {{ config('app.url') }}/{{ $store->slug }}/categoria/<span id="slug-preview">slug</span>
                            </p>
                            @error('slug')
                                <p class="mt-2 text-sm text-error-200">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-black-400 mb-2">
                            Descripción
                        </label>
                        <textarea name="description" 
                                  id="description"
                                  rows="3"
                                  class="w-full px-3 py-2 rounded-lg bg-accent-100 border border-accent-200 
                                         focus:ring-2 focus:ring-primary-200 focus:border-transparent
                                         @error('description') border-error-200 @enderror"
                                  placeholder="Descripción opcional de la categoría">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-error-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Categoría padre -->
                    @if($parentCategories->count() > 0)
                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-black-400 mb-2">
                                Categoría padre (opcional)
                            </label>
                            <select name="parent_id" 
                                    id="parent_id"
                                    class="w-full px-3 py-2 rounded-lg bg-accent-100 border border-accent-200 
                                           focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                                <option value="">Ninguna (será categoría principal)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <p class="mt-2 text-sm text-error-200">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Configuración adicional -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Estado -->
                        <div>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       class="rounded border-accent-300 text-primary-200 focus:ring-primary-200"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-black-400">Categoría activa</span>
                            </label>
                            <p class="mt-1 text-xs text-black-300 ml-7">
                                Las categorías inactivas no se muestran en la tienda
                            </p>
                        </div>

                        <!-- Orden -->
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-black-400 mb-2">
                                Orden de visualización
                            </label>
                            <input type="number" 
                                   name="sort_order" 
                                   id="sort_order"
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   class="w-full px-3 py-2 rounded-lg bg-accent-100 border border-accent-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                            <p class="mt-1 text-xs text-black-300">
                                Menor número = aparece primero
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer con botones -->
                <div class="bg-accent-100 px-6 py-4 flex justify-between items-center">
                    <a href="{{ route('tenant.admin.categories.index', $store->slug) }}" 
                       class="btn-outline-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        Crear Categoría
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Auto-generar slug desde el nombre
        document.getElementById('name').addEventListener('input', function(e) {
            const slug = e.target.value
                .toLowerCase()
                .replace(/[áàäâ]/g, 'a')
                .replace(/[éèëê]/g, 'e')
                .replace(/[íìïî]/g, 'i')
                .replace(/[óòöô]/g, 'o')
                .replace(/[úùüû]/g, 'u')
                .replace(/ñ/g, 'n')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            
            if (!document.getElementById('slug').value || document.getElementById('slug').dataset.autoGenerated) {
                document.getElementById('slug').value = slug;
                document.getElementById('slug').dataset.autoGenerated = 'true';
                document.getElementById('slug-preview').textContent = slug || 'slug';
            }
        });

        // Actualizar preview del slug
        document.getElementById('slug').addEventListener('input', function(e) {
            document.getElementById('slug-preview').textContent = e.target.value || 'slug';
            if (e.target.value) {
                e.target.dataset.autoGenerated = 'false';
            }
        });
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 