@extends('shared::layouts.admin')
@section('title', 'Iconos de Categorías')

@section('content')
<div class="flex-1 space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-black-400">Iconos de Categorías</h1>
            <p class="text-black-300 mt-1">Gestiona los iconos disponibles para las categorías de las tiendas</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superlinkiu.category-icons.create') }}" class="btn-primary">
                <x-solar-add-circle-outline class="w-4 h-4 mr-2" />
                Nuevo Icono
            </a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Iconos -->
        <div class="bg-accent-50 rounded-xl p-6 shadow-sm border border-accent-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-black-300 mb-1">Total de Iconos</p>
                    <p class="text-2xl font-bold text-black-500">{{ $totalIcons }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                    <x-solar-gallery-outline class="w-6 h-6 text-primary-300" />
                </div>
            </div>
        </div>

        <!-- Iconos Activos -->
        <div class="bg-accent-50 rounded-xl p-6 shadow-sm border border-accent-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-black-300 mb-1">Iconos Activos</p>
                    <p class="text-2xl font-bold text-success-400">{{ $activeIcons }}</p>
                </div>
                <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                    <x-solar-check-circle-outline class="w-6 h-6 text-success-400" />
                </div>
            </div>
        </div>

        <!-- Iconos Inactivos -->
        <div class="bg-accent-50 rounded-xl p-6 shadow-sm border border-accent-100">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-black-300 mb-1">Iconos Inactivos</p>
                    <p class="text-2xl font-bold text-warning-300">{{ $totalIcons - $activeIcons }}</p>
                </div>
                <div class="w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center">
                    <x-solar-pause-circle-outline class="w-6 h-6 text-warning-300" />
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-accent-50 rounded-xl shadow-sm border border-accent-100 overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-accent-100 bg-gradient-to-r from-accent-50 to-accent-100">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-black-500">Gestión de Iconos</h2>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-black-300 bg-accent-200 px-3 py-1 rounded-full">
                        {{ $icons->total() }} icono{{ $icons->total() !== 1 ? 's' : '' }} total
                    </span>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        @if($icons->count() > 0)
            <div class="p-6">
                <!-- Icons Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6" id="sortable-icons">
                    @foreach($icons as $icon)
                        <div class="icon-card bg-accent-100 rounded-xl p-4 border-2 border-accent-200 hover:border-primary-200 hover:shadow-md transition-all duration-200 cursor-move group"
                             data-icon-id="{{ $icon->id }}"
                             data-sort-order="{{ $icon->sort_order }}">
                            
                            <!-- Icon Preview -->
                            <div class="aspect-square bg-accent-50 rounded-lg p-3 mb-3 flex items-center justify-center overflow-hidden group-hover:bg-primary-50 transition-colors">
                                @if($icon->image_url)
                                    <img src="{{ $icon->image_url }}" 
                                         alt="{{ $icon->display_name }}" 
                                         class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-black-200">
                                        <x-solar-gallery-outline class="w-8 h-8" />
                                    </div>
                                @endif
                            </div>

                            <!-- Icon Info -->
                            <div class="text-center mb-3">
                                <h3 class="text-sm font-semibold text-black-400 mb-1 truncate">{{ $icon->display_name }}</h3>
                                <p class="text-xs text-black-300 truncate">{{ $icon->name }}</p>
                            </div>

                            <!-- Status Badge -->
                            <div class="flex justify-center mb-3">
                                @if($icon->is_active)
                                    <span class="px-2 py-1 text-xs bg-success-100 text-success-400 rounded-full font-medium">Activo</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-warning-100 text-warning-400 rounded-full font-medium">Inactivo</span>
                                @endif
                            </div>

                            <!-- Actions Row -->
                            <div class="flex items-center justify-between gap-2">
                                <!-- Toggle Switch -->
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           class="sr-only peer icon-toggle"
                                           {{ $icon->is_active ? 'checked' : '' }}
                                           data-icon-id="{{ $icon->id }}"
                                           data-url="{{ route('superlinkiu.category-icons.toggle-active', $icon->id) }}">
                                    <div class="w-9 h-5 bg-accent-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-200 rounded-full peer 
                                                peer-checked:after:translate-x-full peer-checked:after:border-accent-50 
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                                after:bg-accent-50 after:rounded-full after:h-4 after:w-4 after:transition-all 
                                                peer-checked:bg-success-300"></div>
                                </label>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('superlinkiu.category-icons.edit', $icon->id) }}" 
                                       class="p-1.5 text-primary-300 hover:text-primary-400 hover:bg-primary-50 rounded-lg transition-colors"
                                       title="Editar">
                                        <x-solar-pen-2-outline class="w-4 h-4" />
                                    </a>

                                    <button onclick="deleteIcon({{ $icon->id }}, '{{ $icon->display_name }}')"
                                            class="p-1.5 text-error-300 hover:text-error-400 hover:bg-error-50 rounded-lg transition-colors"
                                            title="Eliminar">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            @if($icons->hasPages())
                <div class="px-6 py-4 border-t border-accent-100 bg-accent-100">
                    {{ $icons->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-black-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-solar-gallery-outline class="w-10 h-10 text-black-300" />
                </div>
                <h3 class="text-lg font-semibold text-black-400 mb-2">No hay iconos registrados</h3>
                <p class="text-black-300 mb-6 max-w-md mx-auto">
                    Comienza agregando algunos iconos para las categorías de las tiendas. 
                    Los iconos ayudarán a los administradores a organizar mejor sus productos.
                </p>
                <a href="{{ route('superlinkiu.category-icons.create') }}" class="btn-primary">
                    <x-solar-add-circle-outline class="w-4 h-4 mr-2" />
                    Crear Primer Icono
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Toggle activar/desactivar icono
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.icon-toggle');
        
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const iconId = this.dataset.iconId;
                const url = this.dataset.url;
                const isChecked = this.checked;
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar badge de estado
                        const card = document.querySelector(`[data-icon-id="${iconId}"]`);
                        const badge = card.querySelector('span.px-2');
                        
                        if (data.is_active) {
                            badge.className = 'px-2 py-1 text-xs bg-success-100 text-success-400 rounded-full font-medium';
                            badge.textContent = 'Activo';
                        } else {
                            badge.className = 'px-2 py-1 text-xs bg-warning-100 text-warning-400 rounded-full font-medium';
                            badge.textContent = 'Inactivo';
                        }
                        
                        // Mostrar notificación
                        showNotification(data.message, 'success');
                    } else {
                        // Revertir toggle
                        this.checked = !isChecked;
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    // Revertir toggle
                    this.checked = !isChecked;
                    showNotification('Error de conexión', 'error');
                });
            });
        });
    });

    // Función para eliminar icono
    function deleteIcon(iconId, iconName) {
        if (confirm(`¿Estás seguro de que deseas eliminar el icono "${iconName}"?\n\nEsta acción no se puede deshacer.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/superlinkiu/category-icons/${iconId}`;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(methodInput);
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Función para mostrar notificaciones
    function showNotification(message, type) {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-accent-50 font-medium transition-all transform translate-x-full`;
        notification.className += type === 'success' ? ' bg-success-400' : ' bg-error-400';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Mostrar
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
</script>
@endpush
@endsection 