<x-tenant-admin-layout :store="$store">
    @section('title', 'Editar Categoría')

    @section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('tenant.admin.categories.index', $store->slug) }}" 
                   class="text-black-300 hover:text-black-400">
                    <x-solar-arrow-left-outline class="w-6 h-6" />
                </a>
                <h1 class="text-lg font-semibold text-black-500">Editar Categoría</h1>
            </div>
            <div class="bg-info-50 border border-info-100 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <x-solar-info-circle-outline class="w-5 h-5 text-info-300 flex-shrink-0" />
                    <p class="text-sm text-info-300">
                        Estás usando <strong>{{ $totalCategories }}</strong> de <strong>{{ $categoryLimit }}</strong> categorías disponibles en tu plan {{ $store->plan->name }}.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form action="{{ route('tenant.admin.categories.update', [$store->slug, $category->id]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Card principal -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
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
                                           {{ old('icon_id', $category->icon_id) == $icon->id ? 'checked' : '' }}
                                           required>
                                    <div class="w-full aspect-square bg-white-100 rounded-lg p-3 border-1 border-white-200 
                                                peer-checked:border-primary-200 peer-checked:bg-primary-50 
                                                hover:border-white-300 transition-all">
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
                            <p class="mt-2 text-sm text-error-400">{{ $message }}</p>
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
                                   value="{{ old('name', $category->name) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
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
                                   value="{{ old('slug', $category->slug) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-transparent
                                          @error('slug') border-error-200 @enderror"
                                   placeholder="Se genera automáticamente">
                            <p class="mt-1 text-xs text-black-300">
                                URL: {{ config('app.url') }}/{{ $store->slug }}/categoria/<span id="slug-preview">{{ $category->slug }}</span>
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
                                  class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                         focus:ring-2 focus:ring-primary-200 focus:border-transparent
                                         @error('description') border-error-200 @enderror"
                                  placeholder="Descripción opcional de la categoría">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-error-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Categoría padre -->
                    @if($parentCategories->count() > 0 || $category->parent_id)
                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-black-400 mb-2">
                                Categoría padre (opcional)
                            </label>
                            <select name="parent_id" 
                                    id="parent_id"
                                    class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                           focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                                <option value="">Ninguna (será categoría principal)</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
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
                                       class="rounded border-white-300 text-primary-200 focus:ring-primary-200"
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
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
                                   value="{{ old('sort_order', $category->sort_order) }}"
                                   min="0"
                                   class="w-full px-3 py-2 rounded-lg bg-white-100 border border-white-200 
                                          focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                            <p class="mt-1 text-xs text-black-300">
                                Menor número = aparece primero
                            </p>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    @if($category->children->count() > 0)
                        <div class="bg-warning-50 border border-warning-100 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <x-solar-danger-triangle-outline class="w-5 h-5 text-warning-200 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-sm font-medium text-warning-200">
                                        Esta categoría tiene {{ $category->children->count() }} subcategoría(s)
                                    </p>
                                    <p class="text-sm text-warning-300 mt-1">
                                        Si cambias esta categoría a subcategoría, sus subcategorías actuales se convertirán en categorías principales.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($category->products_count > 0)
                        <div class="bg-info-50 border border-info-100 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <x-solar-info-circle-outline class="w-5 h-5 text-info-200 flex-shrink-0" />
                                <p class="text-sm text-info-200">
                                    Esta categoría tiene <strong>{{ $category->products_count }}</strong> producto(s) asociado(s).
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer con botones -->
                <div class="bg-white-100 px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('tenant.admin.categories.index', $store->slug) }}" 
                           class="btn-outline-secondary">
                            Cancelar
                        </a>
                        @if($category->products_count == 0)
                            <button type="button" 
                                    onclick="if(confirm('¿Estás seguro de eliminar esta categoría?')) { document.getElementById('delete-form').submit(); }"
                                    class="btn-outline-error">
                                Eliminar
                            </button>
                        @endif
                    </div>
                    <button type="submit" class="btn-primary">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>

        <!-- Formulario oculto para eliminar -->
        @if($category->products_count == 0)
            <form id="delete-form" action="{{ route('tenant.admin.categories.destroy', [$store->slug, $category->id]) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>

    @push('scripts')
    <script>
        // Actualizar preview del slug
        document.getElementById('slug').addEventListener('input', function(e) {
            document.getElementById('slug-preview').textContent = e.target.value || 'slug';
        });
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 