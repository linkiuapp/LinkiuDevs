<x-tenant-admin-layout :store="$store">
    @section('title', 'Ver Producto')

    @section('content')
    <div class="space-y-4">
        <!-- Header -->
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('tenant.admin.products.index', $store->slug) }}" 
                           class="text-black-400 hover:text-primary-300 transition-colors">
                            <x-solar-arrow-left-outline class="w-6 h-6" />
                        </a>
                        <div>
                            <h2 class="text-lg font-semibold text-black-500 mb-2">{{ $product->name }}</h2>
                            <div class="flex items-center gap-3">
                                @if($product->sku)
                                <span class="text-sm text-black-400 font-mono">{{ $product->sku }}</span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-success-200 text-black-300' : 'bg-error-200 text-white-50' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->type === 'simple' ? 'bg-info-200 text-black-300' : 'bg-warning-200 text-black-500' }}">
                                    {{ $product->type === 'simple' ? 'Simple' : 'Variable' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('tenant.admin.products.edit', [$store->slug, $product->id]) }}" 
                           class="btn-primary flex items-center gap-2">
                            <x-solar-pen-outline class="w-4 h-4" />
                            Editar
                        </a>
                        <button onclick="duplicateProduct({{ $product->id }})" 
                                class="btn-secondary flex items-center gap-2">
                            <x-solar-copy-outline class="w-4 h-4" />
                            Duplicar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Columna principal -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Información básica -->
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h3 class="text-lg font-semibold text-black-500">Información del Producto</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-1">Nombre</label>
                                <p class="text-sm text-black-500">{{ $product->name }}</p>
                            </div>
                            @if($product->sku)
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-1">SKU</label>
                                <p class="text-sm text-black-500 font-mono">{{ $product->sku }}</p>
                            </div>
                            @endif
                        </div>
                        
                        @if($product->description)
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Descripción</label>
                            <p class="text-sm text-black-500">{{ $product->description }}</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-1">Precio</label>
                                <p class="text-lg font-semibold text-primary-300">${{ number_format($product->price, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-400 mb-1">Tipo</label>
                                <p class="text-sm text-black-500">{{ $product->type === 'simple' ? 'Producto Simple' : 'Producto Variable' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imágenes -->
                @if($product->images && $product->images->count() > 0)
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h3 class="text-lg font-semibold text-black-500">Imágenes</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($product->images as $image)
                            <div class="relative group">
                                <img src="{{ $image->image_url }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-32 object-cover rounded-lg border border-white-200 cursor-pointer hover:opacity-90 transition-opacity"
                                     onclick="openImageModal('{{ $image->image_url }}')">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                    <x-solar-eye-outline class="w-6 h-6 text-white-50 opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Categorías -->
                @if($product->categories && $product->categories->count() > 0)
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h3 class="text-lg font-semibold text-black-500">Categorías</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->categories as $category)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-50 text-primary-300">
                                {{ $category->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Acciones rápidas -->
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h3 class="text-lg font-semibold text-black-500">Acciones</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('tenant.admin.products.edit', [$store->slug, $product->id]) }}" 
                           class="w-full btn-primary flex items-center justify-center gap-2">
                            <x-solar-pen-outline class="w-4 h-4" />
                            Editar Producto
                        </a>
                        
                        <button onclick="duplicateProduct({{ $product->id }})" 
                                class="w-full btn-secondary flex items-center justify-center gap-2">
                            <x-solar-copy-outline class="w-4 h-4" />
                            Duplicar
                        </button>

                        <form method="POST" action="{{ route('tenant.admin.products.toggle-status', [$store->slug, $product->id]) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full btn-{{ $product->is_active ? 'warning' : 'success' }} flex items-center justify-center gap-2">
                                @if($product->is_active)
                                    <x-solar-pause-circle-outline class="w-4 h-4" />
                                    Desactivar
                                @else
                                    <x-solar-play-circle-outline class="w-4 h-4" />
                                    Activar
                                @endif
                            </button>
                        </form>

                        <form method="POST" action="{{ route('tenant.admin.products.destroy', [$store->slug, $product->id]) }}" 
                              onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto?')" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full btn-error flex items-center justify-center gap-2">
                                <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h3 class="text-lg font-semibold text-black-500">Información</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-400">Creado</span>
                            <span class="text-sm text-black-500">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-400">Actualizado</span>
                            <span class="text-sm text-black-500">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-400">Estado</span>
                            <span class="text-sm {{ $product->is_active ? 'text-success-400' : 'text-error-400' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver imágenes -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white-50 rounded-lg max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b border-white-100">
                <h3 class="text-lg font-semibold text-black-500">Vista de Imagen</h3>
                <button onclick="closeImageModal()" class="text-black-400 hover:text-black-500">
                    <x-solar-close-circle-outline class="w-6 h-6" />
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" src="" alt="" class="max-w-full max-h-[70vh] object-contain mx-auto">
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function duplicateProduct(productId) {
            if (confirm('¿Quieres duplicar este producto?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/{{ $store->slug }}/admin/products/${productId}/duplicate`;
                form.innerHTML = `
                    @csrf
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openImageModal(imageUrl) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 