@extends('shared::layouts.admin')

@section('title', 'Gestión de Anuncios')

@section('content')
<div class="container-fluid" x-data="announcementsIndex">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Gestión de Anuncios</h1>
            <p class="text-sm text-black-300">Administra comunicados y actualizaciones para las tiendas</p>
        </div>
        <a href="{{ route('superlinkiu.announcements.create') }}" 
           class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-add-circle-outline class="w-5 h-5" />
            Crear Anuncio
        </a>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Total</p>
                    <p class="text-2xl font-bold text-black-400">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-info-100 rounded-lg flex items-center justify-center">
                    <x-solar-chat-dots-outline class="w-5 h-5 text-info-300" />
                </div>
            </div>
        </div>

        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Activos</p>
                    <p class="text-2xl font-bold text-success-300">{{ $stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 bg-success-100 rounded-lg flex items-center justify-center">
                    <x-solar-check-circle-outline class="w-5 h-5 text-success-300" />
                </div>
            </div>
        </div>

        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Banners</p>
                    <p class="text-2xl font-bold text-warning-300">{{ $stats['banners'] }}</p>
                </div>
                <div class="w-10 h-10 bg-warning-100 rounded-lg flex items-center justify-center">
                    <x-solar-gallery-outline class="w-5 h-5 text-warning-300" />
                </div>
            </div>
        </div>

        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Críticos</p>
                    <p class="text-2xl font-bold text-error-300">{{ $stats['critical'] }}</p>
                </div>
                <div class="w-10 h-10 bg-error-100 rounded-lg flex items-center justify-center">
                    <x-solar-danger-triangle-outline class="w-5 h-5 text-error-300" />
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-accent-50 rounded-lg p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Tipo</label>
                <select name="type" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todos los tipos</option>
                    <option value="critical" {{ request('type') === 'critical' ? 'selected' : '' }}>🚨 Crítico</option>
                    <option value="important" {{ request('type') === 'important' ? 'selected' : '' }}>⭐ Importante</option>
                    <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>ℹ️ Información</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirados</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Buscar</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Título o contenido..."
                       class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2 flex-1">
                    <x-solar-magnifer-outline class="w-4 h-4" />
                    Filtrar
                </button>
                <a href="{{ route('superlinkiu.announcements.index') }}" 
                   class="btn-outline-secondary px-4 py-2 rounded-lg">
                    <x-solar-refresh-outline class="w-4 h-4" />
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Anuncios -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">
                Anuncios ({{ $announcements->total() }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-accent-100">
                    <tr>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Anuncio</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Tipo</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Estado</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Fechas</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Banner</th>
                        <th class="text-right py-3 px-4 font-medium text-black-300">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-accent-100">
                    @forelse($announcements as $announcement)
                        <tr class="hover:bg-accent-100 transition-colors duration-150">
                            <td class="py-3 px-4">
                                <div>
                                    <h3 class="font-medium text-black-400">{{ $announcement->title }}</h3>
                                    <p class="text-sm text-black-300 mt-1">
                                        {{ Str::limit($announcement->content, 80) }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs bg-black-100 text-black-300 px-2 py-1 rounded">
                                            Prioridad: {{ $announcement->priority }}
                                        </span>
                                        @if($announcement->target_plans)
                                            <span class="text-xs bg-info-100 text-info-300 px-2 py-1 rounded">
                                                Planes: {{ implode(', ', $announcement->target_plans) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $announcement->type_color }}-100 text-{{ $announcement->type_color }}-300">
                                    {{ $announcement->type_icon }} {{ $announcement->type_label }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1">
                                    @if($announcement->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-300">
                                            <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-black-100 text-black-300">
                                            <x-solar-pause-circle-outline class="w-3 h-3 mr-1" />
                                            Inactivo
                                        </span>
                                    @endif
                                    
                                    @if($announcement->isExpired())
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-error-100 text-error-300">
                                            <x-solar-clock-circle-outline class="w-3 h-3 mr-1" />
                                            Expirado
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3 px-4 text-sm text-black-300">
                                <div class="space-y-1">
                                    <div>
                                        <strong>Publicado:</strong><br>
                                        {{ $announcement->published_at ? $announcement->published_at->format('d/m/Y H:i') : 'Inmediato' }}
                                    </div>
                                    @if($announcement->expires_at)
                                        <div>
                                            <strong>Expira:</strong><br>
                                            {{ $announcement->expires_at->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <div class="text-success-300">Permanente</div>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                @if($announcement->show_as_banner)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning-100 text-warning-300">
                                            <x-solar-gallery-outline class="w-3 h-3 mr-1" />
                                            Banner
                                        </span>
                                        @if($announcement->banner_image)
                                            <img src="{{ $announcement->banner_image_url }}" 
                                                 alt="Banner" 
                                                 class="w-8 h-2 object-cover rounded border">
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-black-200">Sin banner</span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('superlinkiu.announcements.show', $announcement) }}"
                                       class="bg-info-50 hover:bg-info-200 hover:text-info-50 text-info-300 px-2 py-2 rounded-lg"
                                       title="Ver detalle">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </a>
                                    
                                    <a href="{{ route('superlinkiu.announcements.edit', $announcement) }}"
                                       class="bg-warning-50 hover:bg-warning-200 hover:text-warning-50 text-warning-300 px-2 py-2 rounded-lg"
                                       title="Editar">
                                        <x-solar-pen-2-outline class="w-4 h-4" />
                                    </a>

                                    <form method="POST" 
                                          action="{{ route('superlinkiu.announcements.toggle-active', $announcement) }}" 
                                          class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="bg-{{ $announcement->is_active ? 'error' : 'success' }}-50 hover:bg-{{ $announcement->is_active ? 'error' : 'success' }}-200 hover:text-{{ $announcement->is_active ? 'error' : 'success' }}-50 text-{{ $announcement->is_active ? 'error' : 'success' }}-300 px-2 py-2 rounded-lg"
                                                title="{{ $announcement->is_active ? 'Desactivar' : 'Activar' }}">
                                            @if($announcement->is_active)
                                                <x-solar-pause-circle-outline class="w-4 h-4" />
                                            @else
                                                <x-solar-play-circle-outline class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </form>

                                    <button @click="deleteAnnouncement({{ $announcement->id }}, '{{ $announcement->title }}')"
                                            class="bg-error-50 hover:bg-error-200 hover:text-error-50 text-error-300 px-2 py-2 rounded-lg"
                                            title="Eliminar">
                                        <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <x-solar-chat-dots-outline class="w-12 h-12 text-black-200" />
                                    <div>
                                        <h3 class="text-black-300 font-medium">No hay anuncios</h3>
                                        <p class="text-black-200 text-sm">Crea el primer anuncio para comunicarte con las tiendas</p>
                                    </div>
                                    <a href="{{ route('superlinkiu.announcements.create') }}" 
                                       class="btn-primary px-4 py-2 rounded-lg">
                                        Crear Anuncio
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div class="border-t border-accent-100 px-6 py-4">
                {{ $announcements->links() }}
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
         class="fixed inset-0 bg-black-500 bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50"
         style="display: none;">
        <div class="bg-accent-50 rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-black-400 mb-2">Confirmar Eliminación</h3>
            <p class="text-black-300 mb-4">
                ¿Estás seguro de que deseas eliminar el anuncio 
                "<span x-text="announcementToDelete.title"></span>"?
            </p>
            <p class="text-sm text-error-300 mb-6">Esta acción no se puede deshacer.</p>
            
            <div class="flex justify-end gap-3">
                <button @click="showDeleteModal = false" 
                        class="btn-outline-secondary px-4 py-2 rounded-lg">
                    Cancelar
                </button>
                <button @click="confirmDelete()" 
                        class="btn-error px-4 py-2 rounded-lg">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function announcementsIndex() {
    return {
        showDeleteModal: false,
        announcementToDelete: {},
        
        deleteAnnouncement(id, title) {
            this.announcementToDelete = { id, title };
            this.showDeleteModal = true;
        },
        
        confirmDelete() {
            // Crear formulario y enviarlo
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/superlinkiu/announcements/${this.announcementToDelete.id}`;
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
@endpush
@endsection 