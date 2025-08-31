<x-tenant-admin-layout :store="$store">
    @section('title', 'Productos')

    @section('content')
    <div x-data="productManagement" class="space-y-4">
        <!-- Header con contador y botón crear -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-black-500 mb-2">Productos</h2>
                        <p class="text-sm text-black-300">
                            Usando {{ $currentCount }} de {{ $maxProducts }} productos disponibles en tu plan {{ $store->plan->name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($currentCount < $maxProducts)
                            <a href="{{ route('tenant.admin.products.create', $store->slug) }}" 
                               class="btn-primary flex items-center gap-2">
                                <x-solar-add-circle-outline class="w-5 h-5" />
                                Nuevo Producto
                            </a>
                        @else
                            <button class="btn-secondary opacity-50 cursor-not-allowed flex items-center gap-2" disabled>
                                <x-solar-add-circle-outline class="w-5 h-5" />
                                Límite Alcanzado
                            </button>
                        @endif
                    </div>
                </div>
            </div>


            <!-- Barra de herramientas -->
            <div class="px-6 py-3 border-b border-accent-100 bg-accent-50">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <!-- Filtros rápidos -->
                        <select x-model="filterStatus" @change="applyFilters()" 
                                class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Todos</option>
                            <option value="active">Activos</option>
                            <option value="inactive">Inactivos</option>
                        </select>
                        
                        <select x-model="filterType" @change="applyFilters()" 
                                class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Todos los tipos</option>
                            <option value="simple">Productos simples</option>
                            <option value="variable">Productos variables</option>
                        </select>

                        @if($categories->count() > 0)
                        <select x-model="filterCategory" @change="applyFilters()" 
                                class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <!-- Buscador -->
                        <div class="relative">
                            <x-solar-magnifer-outline class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-black-300" />
                            <input type="text" 
                                   x-model="searchTerm"
                                   @input="applyFilters()"
                                   placeholder="Buscar productos..."
                                   class="pl-10 pr-4 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                        </div>
                        
                        <!-- Acciones masivas -->
                        <div x-show="selectedProducts.length > 0" class="flex items-center gap-2">
                            <button @click="bulkActivate()" 
                                    class="px-3 py-1.5 bg-success-400 text-accent-50 rounded-lg text-sm hover:bg-success-300 flex items-center gap-1">
                                <x-solar-check-circle-outline class="w-4 h-4" />
                                Activar
                            </button>
                            <button @click="bulkDeactivate()" 
                                    class="px-3 py-1.5 bg-warning-300 text-black-500 rounded-lg text-sm hover:bg-warning-200 flex items-center gap-1">
                                <x-solar-close-circle-outline class="w-4 h-4" />
                                Desactivar
                            </button>
                            <button @click="bulkDelete()" 
                                    class="px-3 py-1.5 bg-error-400 text-accent-50 rounded-lg text-sm hover:bg-error-300 flex items-center gap-1">
                                <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-accent-100 border-b border-accent-200">
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" 
                                       @change="toggleAll($event.target.checked)"
                                       class="rounded border-accent-300">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                Producto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                Precio
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                Categorías
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                Activar/Desactivar
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-accent-50 divide-y divide-accent-200">
                        @forelse($products as $product)
                            <tr class="text-black-400 hover:bg-accent-100" data-id="{{ $product->id }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" 
                                           class="product-checkbox rounded border-accent-300" 
                                           value="{{ $product->id }}"
                                           @change="toggleProduct({{ $product->id }}, $event.target.checked)">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-sm">
                                        <div class="w-12 h-12 mr-3 flex items-center justify-center bg-accent-100 rounded-lg overflow-hidden">
                                            @if($product->images && $product->images->count() > 0)
                                                <img src="{{ $product->images->first()->image_url }}" 
                                                     alt="{{ $product->name }}"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <x-solar-gallery-minimalistic-outline class="w-6 h-6 text-black-300" />
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-semibold">{{ $product->name }}</p>
                                            <p class="text-xs text-black-300">
                                                SKU: {{ $product->sku ?: 'Sin SKU' }}
                                            </p>
                                            <p class="text-xs text-black-200">
                                                {{ $product->type === 'variable' ? 'Variable' : 'Simple' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="font-semibold text-primary-300">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($product->categories && $product->categories->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($product->categories->take(2) as $category)
                                                <span class="text-xs bg-primary-50 text-primary-300 px-2 py-1 rounded">
                                                    {{ $category->name }}
                                                </span>
                                            @endforeach
                                            @if($product->categories->count() > 2)
                                                <span class="text-xs bg-black-100 text-black-300 px-2 py-1 rounded">
                                                    +{{ $product->categories->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-black-200">Sin categorías</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($product->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-200 text-black-300">
                                            <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-error-200 text-accent-50">
                                            <x-solar-close-circle-outline class="w-3 h-3 mr-1" />
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               class="sr-only peer product-toggle"
                                               {{ $product->is_active ? 'checked' : '' }}
                                               data-product-id="{{ $product->id }}"
                                               data-url="{{ route('tenant.admin.products.toggle-status', [$store->slug, $product->id]) }}">
                                        <div class="w-11 h-6 bg-accent-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-accent-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-accent-50 after:border-accent-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-300"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('tenant.admin.products.show', [$store->slug, $product->id]) }}" 
                                           class="text-info-200 hover:text-info-300" title="Ver">
                                            <x-solar-eye-outline class="w-4 h-4" />
                                        </a>
                                        <button @click="duplicateProduct({{ $product->id }}, '{{ $product->name }}')" 
                                                class="text-secondary-200 hover:text-secondary-300" title="Duplicar">
                                            <x-solar-copy-outline class="w-4 h-4" />
                                        </button>
                                        <a href="{{ route('tenant.admin.products.edit', [$store->slug, $product->id]) }}" 
                                           class="text-warning-200 hover:text-warning-300" title="Editar">
                                            <x-solar-pen-outline class="w-4 h-4" />
                                        </a>
                                        <button @click="deleteProduct({{ $product->id }}, '{{ $product->name }}')" 
                                                class="text-error-200 hover:text-error-300" title="Eliminar">
                                            <x-solar-trash-bin-minimalistic-outline class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <x-solar-box-outline class="w-12 h-12 text-black-200 mb-4" />
                                        <h3 class="text-lg font-medium text-black-300 mb-2">No hay productos</h3>
                                        <p class="text-sm text-black-200 mb-4">Comienza agregando tu primer producto</p>
                                        @if($currentCount < $maxProducts)
                                            <a href="{{ route('tenant.admin.products.create', $store->slug) }}" 
                                               class="btn-primary flex items-center gap-2">
                                                <x-solar-add-circle-outline class="w-5 h-5" />
                                                Crear Producto
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
                <div class="px-6 py-4 border-t border-accent-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- Modal de confirmación de eliminación -->
        <div x-show="showDeleteModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto modal-overlay">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showDeleteModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity" 
                     @click="closeDeleteModal()">
                    <div class="absolute inset-0 bg-black-500/75 backdrop-blur-sm"></div>
                </div>

                <!-- Modal -->
                <div x-show="showDeleteModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-accent-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-accent-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-50 sm:mx-0 sm:h-10 sm:w-10">
                                <x-solar-trash-bin-trash-bold class="h-6 w-6 text-error-300" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-black-400">
                                    Eliminar Producto
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-black-300">
                                        ¿Estás seguro de que deseas eliminar el producto <span class="font-semibold" x-text="deleteProductName"></span>? 
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-accent-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                @click="confirmDelete()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-error-200 text-base font-medium text-accent-50 hover:bg-error-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-200 sm:ml-3 sm:w-auto sm:text-sm">
                            Eliminar
                        </button>
                        <button type="button" 
                                @click="closeDeleteModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-accent-300 shadow-sm px-4 py-2 bg-accent-50 text-base font-medium text-black-400 hover:bg-accent-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal de duplicar producto -->
        <div x-show="showDuplicateModal" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto modal-overlay">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="showDuplicateModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity" 
                    @click="closeDuplicateModal()">
                    <div class="absolute inset-0 bg-black-500/75 backdrop-blur-sm"></div>
                </div>

                <!-- Modal -->
                <div x-show="showDuplicateModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-accent-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-accent-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary-50 sm:mx-0 sm:h-10 sm:w-10">
                                <x-solar-copy-outline class="h-6 w-6 text-secondary-300" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-black-400">
                                    Duplicar Producto
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-black-300 mb-4">
                                        Ingresa el nombre para la copia de <span class="font-semibold" x-text="duplicateProductName"></span>
                                    </p>
                                    <input type="text" 
                                        x-model="newProductName"
                                        class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200"
                                        placeholder="Nombre del nuevo producto">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-accent-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                @click="confirmDuplicate()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-secondary-300 text-base font-medium text-accent-50 hover:bg-secondary-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-300 sm:ml-3 sm:w-auto sm:text-sm">
                            Duplicar
                        </button>
                        <button type="button" 
                                @click="closeDuplicateModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-accent-300 shadow-sm px-4 py-2 bg-accent-50 text-base font-medium text-black-400 hover:bg-accent-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-300 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productManagement', () => ({
                showDeleteModal: false,
                deleteProductId: null,
                deleteProductName: '',
                showDuplicateModal: false,
                duplicateProductId: null,
                duplicateProductName: '',
                newProductName: '',
                selectedProducts: [],
                filterStatus: '',
                filterType: '',
                filterCategory: '',
                searchTerm: '',

                init() {
                    // Inicializar Sortable.js para drag & drop si está disponible
                    if (typeof Sortable !== 'undefined') {
                        new Sortable(document.getElementById('sortableProducts'), {
                            handle: '.drag-handle',
                            animation: 150,
                            onEnd: () => {
                                this.updateOrder();
                            }
                        });
                    }
                },

                deleteProduct(id, name) {
                    this.deleteProductId = id;
                    this.deleteProductName = name;
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteProductId = null;
                    this.deleteProductName = '';
                },

                async confirmDelete() {
                    if (!this.deleteProductId) return;

                    try {
                        const response = await fetch(`/{{ $store->slug }}/admin/products/${this.deleteProductId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // ✅ Reload con delay para evitar modal automático
                            setTimeout(() => window.location.reload(), 1500);
                            alert('Producto eliminado correctamente');
                        } else {
                            alert(data.error || 'Error al eliminar el producto');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al eliminar el producto');
                    }

                    this.closeDeleteModal();
                },

                duplicateProduct(id, name) {
                    this.duplicateProductId = id;
                    this.duplicateProductName = name;
                    this.newProductName = name + ' (Copia)';
                    this.showDuplicateModal = true;
                },

                closeDuplicateModal() {
                    this.showDuplicateModal = false;
                    this.duplicateProductId = null;
                    this.duplicateProductName = '';
                    this.newProductName = '';
                },

                async confirmDuplicate() {
                    if (!this.duplicateProductId || !this.newProductName.trim()) return;

                    try {
                        const response = await fetch(`/{{ $store->slug }}/admin/products/${this.duplicateProductId}/duplicate`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                name: this.newProductName.trim()
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // ✅ Reload con delay para evitar modal automático  
                            setTimeout(() => window.location.reload(), 1500);
                            alert('Producto duplicado correctamente');
                        } else {
                            alert(data.error || 'Error al duplicar el producto');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al duplicar el producto');
                    }

                    this.closeDuplicateModal();
                },

                applyFilters() {
                    const params = new URLSearchParams(window.location.search);
                    
                    if (this.filterStatus) {
                        params.set('status', this.filterStatus);
                    } else {
                        params.delete('status');
                    }
                    
                    if (this.filterType) {
                        params.set('type', this.filterType);
                    } else {
                        params.delete('type');
                    }

                    if (this.filterCategory) {
                        params.set('category', this.filterCategory);
                    } else {
                        params.delete('category');
                    }
                    
                    window.location.search = params.toString();
                },

            }));
        });

        // Toggle functionality
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('product-toggle')) {
                const productId = e.target.dataset.productId;
                const url = e.target.dataset.url;
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // ✅ Sin reload automático - el toggle ya se actualizó visualmente
                        console.log('Estado actualizado correctamente');
                    } else {
                        alert(data.error || 'Error al cambiar el estado');
                        // Revertir el toggle si hay error
                        e.target.checked = !e.target.checked;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cambiar el estado');
                    // Revertir el toggle si hay error
                    e.target.checked = !e.target.checked;
                });
            }
        });
    </script>
    @endpush
    @endsection
</x-tenant-admin-layout> 