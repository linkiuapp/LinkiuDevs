<x-tenant-admin-layout :store="$store">
    @section('title', 'Categorías')

    @section('content')
    <div x-data="categoryManagement" class="space-y-4">
        <!-- Header con contador y botón crear -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-black-500 mb-2">Categorías</h2>
                        <p class="text-sm text-black-300">
                            Usando {{ $totalCategories }} de {{ $categoryLimit }} categorías disponibles en tu plan {{ $store->plan->name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($totalCategories < $categoryLimit)
                            <a href="{{ route('tenant.admin.categories.create', $store->slug) }}" 
                               class="btn-primary flex items-center gap-2">
                                <x-solar-add-circle-outline class="w-5 h-5" />
                                Nueva Categoría
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
                                class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none">
                            <option value="">Todas</option>
                            <option value="active">Activas</option>
                            <option value="inactive">Inactivas</option>
                        </select>
                        
                        <select x-model="filterType" @change="applyFilters()" 
                                class="px-3 py-1.5 border border-accent-200 rounded-lg text-sm focus:outline-none">
                            <option value="">Todas las categorías</option>
                            <option value="main">Solo principales</option>
                            <option value="sub">Solo subcategorías</option>
                        </select>
                    </div>

                    <div class="text-sm text-black-300">
                        Mostrando {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} de {{ $categories->total() }} categorías
                    </div>
                </div>
            </div>

            {{-- ================================================================ --}}
            {{-- CONTENIDO PRINCIPAL --}}
            {{-- ================================================================ --}}
            
            @if($viewType === 'table')
                @include('tenant-admin::categories.components.table-view')
            @endif

            <!-- Paginación -->
            @if($categories->hasPages())
                <div class="px-6 py-4 border-t border-accent-100">
                    {{ $categories->links() }}
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
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;">
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
                                    Eliminar Categoría
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-black-300">
                                        ¿Estás seguro de que deseas eliminar la categoría <span class="font-semibold" x-text="deleteCategoryName"></span>? 
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
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('categoryManagement', () => ({
                showDeleteModal: false,
                deleteCategoryId: null,
                deleteCategoryName: '',
                filterStatus: '',
                filterType: '',

                init() {
                    // Inicializar Sortable.js para drag & drop
                    if (typeof Sortable !== 'undefined') {
                        new Sortable(document.getElementById('sortableCategories'), {
                            handle: '.drag-handle',
                            animation: 150,
                            onEnd: (evt) => {
                                this.updateOrder();
                            }
                        });
                    }
                },

                deleteCategory(id, name) {
                    this.deleteCategoryId = id;
                    this.deleteCategoryName = name;
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteCategoryId = null;
                    this.deleteCategoryName = '';
                },

                async confirmDelete() {
                    if (!this.deleteCategoryId) return;

                    try {
                        const response = await fetch(`/{{ $store->slug }}/admin/categories/${this.deleteCategoryId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert(data.error || 'Error al eliminar la categoría');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al eliminar la categoría');
                    }

                    this.closeDeleteModal();
                },

                async toggleStatus(id) {
                    try {
                        const response = await fetch(`/{{ $store->slug }}/admin/categories/${id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Recargar la página para actualizar el estado
                            window.location.reload();
                        } else {
                            alert(data.error || 'Error al cambiar el estado');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al cambiar el estado');
                    }
                },

                async updateOrder() {
                    const rows = document.querySelectorAll('#sortableCategories tr');
                    const categories = [];

                    rows.forEach((row, index) => {
                        const id = row.dataset.id;
                        if (id) {
                            categories.push({
                                id: parseInt(id),
                                sort_order: index
                            });
                        }
                    });

                    try {
                        const response = await fetch(`/{{ $store->slug }}/admin/categories/update-order`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ categories })
                        });

                        if (!response.ok) {
                            console.error('Error al actualizar el orden');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },

                applyFilters() {
                    // Implementar filtros con JavaScript o recargar con parámetros
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
                    
                    window.location.search = params.toString();
                }
            }));
        });

        // Toggle functionality
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('category-toggle')) {
                const categoryId = e.target.dataset.categoryId;
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
                        // Recargar la página para actualizar el estado
                        window.location.reload();
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