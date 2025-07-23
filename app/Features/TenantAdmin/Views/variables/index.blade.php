<x-tenant-admin-layout :store="$store">
    @section('title', 'Variables')

    @section('content')
    <div x-data="variableManagement" class="space-y-4">
        <!-- Header con contador y botón crear -->
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-black-500 mb-2">Variables</h2>
                        <p class="text-sm text-black-300">
                            Usando {{ $totalVariables }} de {{ $variableLimit }} variables disponibles en tu plan {{ $store->plan->name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($totalVariables < $variableLimit)
                            <a href="{{ route('tenant.admin.variables.create', $store->slug) }}" 
                               class="btn-primary flex items-center gap-2">
                                <x-solar-add-circle-outline class="w-5 h-5" />
                                Nueva Variable
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
            <div class="px-6 py-3 border-b border-white-100 bg-white-50">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <!-- Filtros rápidos -->
                        <select x-model="filterStatus" @change="applyFilters()" 
                                class="px-3 py-1.5 border border-white-200 rounded-lg text-sm focus:outline-none">
                            <option value="">Todas</option>
                            <option value="active">Activas</option>
                            <option value="inactive">Inactivas</option>
                        </select>
                        
                        <select x-model="filterType" @change="applyFilters()" 
                                class="px-3 py-1.5 border border-white-200 rounded-lg text-sm focus:outline-none">
                            <option value="">Todos los tipos</option>
                            <option value="radio">Selección única</option>
                            <option value="checkbox">Selección múltiple</option>
                            <option value="text">Texto libre</option>
                            <option value="numeric">Numérico</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-black-300">{{ $variables->total() }} variables</span>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white-100">
                        <tr class="text-left text-xs font-medium text-black-400 uppercase tracking-wider">
                            <th class="px-6 py-3">
                                <input type="checkbox" id="selectAll" class="rounded border-white-300">
                            </th>
                            <th class="px-6 py-3">Variable</th>
                            <th class="px-6 py-3">Tipo</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-center">Toggle</th>
                            <th class="px-6 py-3 text-center">Opciones</th>
                            <th class="px-6 py-3 text-center">Productos</th>
                            <th class="px-6 py-3 text-center">Orden</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white-50 divide-y divide-white-100" id="sortableVariables">
                        @forelse($variables as $variable)
                            <tr class="text-black-400 hover:bg-white-100" data-id="{{ $variable->id }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="variable-checkbox rounded border-white-300" value="{{ $variable->id }}">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-sm">
                                        <div class="w-10 h-10 mr-3 flex items-center justify-center bg-white-100 rounded-lg">
                                            <x-dynamic-component :component="$variable->type_icon" class="w-5 h-5 text-primary-200" />
                                        </div>
                                        <div>
                                            <p class="font-semibold">{{ $variable->name }}</p>
                                            <p class="text-xs text-black-200">
                                                @if($variable->requiresOptions())
                                                    {{ $variable->options->count() }} opciones
                                                @elseif($variable->isNumeric())
                                                    @if($variable->min_value || $variable->max_value)
                                                        Rango: {{ $variable->min_value ?? 0 }} - {{ $variable->max_value ?? '∞' }}
                                                    @else
                                                        Sin límites
                                                    @endif
                                                @else
                                                    Texto libre
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="text-xs bg-primary-50 text-primary-300 px-2 py-1 rounded">
                                        {{ $variable->type_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($variable->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-200 text-black-300">
                                            <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                            Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-error-200 text-white-50">
                                            <x-solar-close-circle-outline class="w-3 h-3 mr-1" />
                                            Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                            class="sr-only peer variable-toggle"
                                            {{ $variable->is_active ? 'checked' : '' }}
                                            data-variable-id="{{ $variable->id }}"
                                            data-url="{{ route('tenant.admin.variables.toggle-status', [$store->slug, $variable->id]) }}">
                                        <div class="w-11 h-6 bg-white-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    @if($variable->requiresOptions())
                                        <span class="font-medium">{{ $variable->options->count() }}</span>
                                    @else
                                        <span class="text-black-200">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="font-medium">{{ $variable->products_count }}</span>
                                </td>
                                <td class="pl-16 py-4 text-sm text-right">
                                    <span class="drag-handle cursor-move">
                                        <x-solar-sort-outline class="w-5 h-5 text-black-300" />
                                    </span>
                                </td>
                                <td class="py-4">
                                    <div class="flex items-center justify-center gap-4">
                                        <a href="{{ route('tenant.admin.variables.show', [$store->slug, $variable->id]) }}" 
                                           class="text-info-200 hover:text-info-300" title="Ver">
                                            <x-solar-eye-outline class="w-5 h-5" />
                                        </a>
                                        <button @click="duplicateVariable({{ $variable->id }}, '{{ $variable->name }}')" 
                                                class="text-secondary-200 hover:text-secondary-300" title="Duplicar">
                                            <x-solar-copy-outline class="w-5 h-5" />
                                        </button>
                                        <a href="{{ route('tenant.admin.variables.edit', [$store->slug, $variable->id]) }}" 
                                           class="text-primary-200 hover:text-primary-300" title="Editar">
                                            <x-solar-pen-new-square-outline class="w-5 h-5" />
                                        </a>
                                        <button @click="deleteVariable({{ $variable->id }}, '{{ $variable->name }}')" 
                                                class="text-error-200 hover:text-error-300" title="Eliminar"
                                                @if($variable->products_count > 0) disabled class="opacity-50 cursor-not-allowed" @endif>
                                            <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <x-solar-settings-outline class="w-12 h-12 text-black-200 mb-3" />
                                        <p class="text-black-300">No hay variables creadas</p>
                                        @if($totalVariables < $variableLimit)
                                            <a href="{{ route('tenant.admin.variables.create', $store->slug) }}" 
                                               class="btn-primary mt-3 text-sm">
                                                Crear primera variable
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
            @if($variables->hasPages())
                <div class="px-6 py-4 border-t border-white-100">
                    {{ $variables->links() }}
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
                     class="inline-block align-bottom bg-white-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error-50 sm:mx-0 sm:h-10 sm:w-10">
                                <x-solar-trash-bin-trash-bold class="h-6 w-6 text-error-300" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-black-400">
                                    Eliminar Variable
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-black-300">
                                        ¿Estás seguro de que deseas eliminar la variable <span class="font-semibold" x-text="deleteVariableName"></span>? 
                                        Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                @click="confirmDelete()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-error-200 text-base font-medium text-white-50 hover:bg-error-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-error-200 sm:ml-3 sm:w-auto sm:text-sm">
                            Eliminar
                        </button>
                        <button type="button" 
                                @click="closeDeleteModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-white-300 shadow-sm px-4 py-2 bg-white-50 text-base font-medium text-black-400 hover:bg-white-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>3

        <!-- Modal de duplicar variable -->
        <div x-show="showDuplicateModal" 
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
                     class="inline-block align-bottom bg-white-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-secondary-50 sm:mx-0 sm:h-10 sm:w-10">
                                <x-solar-copy-outline class="h-6 w-6 text-secondary-300" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-black-400">
                                    Duplicar Variable
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-black-300 mb-4">
                                        Ingresa el nombre para la copia de <span class="font-semibold" x-text="duplicateVariableName"></span>
                                    </p>
                                    <input type="text" 
                                           x-model="newVariableName"
                                           class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200"
                                           placeholder="Nombre de la nueva variable">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                @click="confirmDuplicate()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-secondary-200 text-base font-medium text-white-50 hover:bg-secondary-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-200 sm:ml-3 sm:w-auto sm:text-sm">
                            Duplicar
                        </button>
                        <button type="button" 
                                @click="closeDuplicateModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-white-300 shadow-sm px-4 py-2 bg-white-50 text-base font-medium text-black-400 hover:bg-white-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
            Alpine.data('variableManagement', () => ({
                showDeleteModal: false,
                deleteVariableId: null,
                deleteVariableName: '',
                showDuplicateModal: false,
                duplicateVariableId: null,
                duplicateVariableName: '',
                newVariableName: '',
                filterStatus: '',
                filterType: '',

                init() {
                    // Inicializar Sortable.js para drag & drop si está disponible
                    if (typeof Sortable !== 'undefined') {
                        new Sortable(document.getElementById('sortableVariables'), {
                            handle: '.drag-handle',
                            animation: 150,
                            onEnd: () => {
                                this.updateOrder();
                            }
                        });
                    }
                },

                deleteVariable(id, name) {
                    this.deleteVariableId = id;
                    this.deleteVariableName = name;
                    this.showDeleteModal = true;
                },

                closeDeleteModal() {
                    this.showDeleteModal = false;
                    this.deleteVariableId = null;
                    this.deleteVariableName = '';
                },

                async confirmDelete() {
                    if (!this.deleteVariableId) return;

                    try {
                        const response = await fetch(`/{{ $store->slug }}/admin/variables/${this.deleteVariableId}`, {
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
                            alert(data.error || 'Error al eliminar la variable');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al eliminar la variable');
                    }

                    this.closeDeleteModal();
                },

                duplicateVariable(id, name) {
                    this.duplicateVariableId = id;
                    this.duplicateVariableName = name;
                    this.newVariableName = name + ' (Copia)';
                    this.showDuplicateModal = true;
                },

                closeDuplicateModal() {
                    this.showDuplicateModal = false;
                    this.duplicateVariableId = null;
                    this.duplicateVariableName = '';
                    this.newVariableName = '';
                },

                async confirmDuplicate() {
                    if (!this.duplicateVariableId || !this.newVariableName.trim()) return;

                    try {
                        const response = await fetch(`/{{ $store->slug }}/admin/variables/${this.duplicateVariableId}/duplicate`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                name: this.newVariableName.trim()
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert(data.error || 'Error al duplicar la variable');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al duplicar la variable');
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
                    
                    window.location.search = params.toString();
                }
            }));
        });

        // Toggle functionality
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('variable-toggle')) {
                const variableId = e.target.dataset.variableId;
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