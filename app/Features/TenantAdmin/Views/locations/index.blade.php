<x-tenant-admin-layout :store="$store">

@section('title', 'Gestión de Sedes')

@section('content')
<div class="container-fluid" x-data="locationManagement">
    {{-- ================================================================ --}}
    {{-- MODALES Y NOTIFICACIONES --}}
    {{-- ================================================================ --}}
    
    {{-- Modal de Eliminación --}}
    @include('tenant-admin::locations.components.delete-modal')
    
    {{-- Sistema de Notificaciones --}}
    @include('tenant-admin::locations.components.notifications')

    {{-- ================================================================ --}}
    {{-- HEADER Y ACCIONES PRINCIPALES --}}
    {{-- ================================================================ --}}
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Gestión de Sedes</h1>
        <div class="flex items-center gap-4">
            <span class="text-sm text-black-300">
                Sedes: <span class="font-semibold">{{ $currentCount }}/{{ $maxLocations }}</span> 
                (Plan {{ $store->plan->name }})
            </span>
            @if($remainingSlots > 0)
                <a href="{{ route('tenant.admin.locations.create', ['store' => $store->slug]) }}" 
                    class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-add-circle-outline class="w-5 h-5" />
                    Crear Sede
                </a>
            @else
                <button disabled 
                    class="btn-primary opacity-50 cursor-not-allowed px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-add-circle-outline class="w-5 h-5" />
                    Límite Alcanzado
                </button>
            @endif
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- FILTROS Y BÚSQUEDA --}}
    {{-- ================================================================ --}}
    
    <div class="bg-accent-50 rounded-lg shadow-sm mb-6">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">Filtros y Búsqueda</h2>
        </div>
        
        <div class="p-6">
            <form method="GET" action="{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-black-300 mb-2">
                            Buscar
                        </label>
                        <input type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Nombre, ciudad o encargado..."
                            class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-black-300 mb-2">
                            Estado
                        </label>
                        <select name="status"
                            class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                            <option value="">Todas</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activas</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivas</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end gap-3">
                        <button type="submit" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                            <x-solar-filter-outline class="w-5 h-5" />
                            Filtrar
                        </button>
                        <a href="{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}" class="btn-outline-secondary px-4 py-2 rounded-lg">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- CONTENIDO PRINCIPAL - TABLA DE SEDES --}}
    {{-- ================================================================ --}}
    
    <div class="bg-accent-50 rounded-lg shadow-sm mb-6 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">Listado de Sedes</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-accent-100">
                <thead class="bg-accent-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                            Sede
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                            Ubicación
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                            Contacto
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-accent-50 divide-y divide-accent-100">
                    @forelse($locations as $location)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-primary-300 font-bold text-sm">
                                            {{ strtoupper(substr($location->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-black-400">
                                            {{ $location->name }}
                                            @if($location->is_main)
                                                <span class="bg-primary-50 px-2 py-1 text-xs rounded-lg border border-primary-300 ml-2">Principal</span>
                                            @endif
                                        </p>
                                        @if($location->manager_name)
                                            <p class="text-sm text-black-300">
                                                <span class="font-semibold">Encargado:</span> {{ $location->manager_name }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-black-400">{{ $location->city }}, {{ $location->department }}</p>
                                <p class="text-xs text-black-300">{{ $location->address }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-black-400">{{ $location->phone }}</p>
                                @if($location->whatsapp)
                                    <p class="text-xs text-black-300">
                                        <span class="font-semibold">WhatsApp:</span> {{ $location->whatsapp }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-2">
                                    @if($location->is_active)
                                        <span class="badge-soft-success px-3 py-1 text-xs">Activa</span>
                                    @else
                                        <span class="badge-soft-secondary px-3 py-1 text-xs">Inactiva</span>
                                    @endif
                                    
                                    @if($location->currentStatus['status'] === 'open')
                                        <span class="badge-soft-success px-3 py-1 text-xs flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-success-300"></span>
                                            Abierto
                                        </span>
                                    @elseif($location->currentStatus['status'] === 'temporarily_closed')
                                        <span class="badge-soft-warning px-3 py-1 text-xs flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-warning-300"></span>
                                            Cerrado temporalmente
                                        </span>
                                    @else
                                        <span class="badge-soft-error px-3 py-1 text-xs flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-error-300"></span>
                                            Cerrado
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('tenant.admin.locations.show', ['store' => $store->slug, 'location' => $location->id]) }}"
                                        class="table-action-show" 
                                        title="Ver detalles">
                                        <x-solar-eye-outline class="table-action-icon" />
                                    </a>
                                    <a href="{{ route('tenant.admin.locations.edit', ['store' => $store->slug, 'location' => $location->id]) }}"
                                        class="table-action-edit"
                                        title="Editar">
                                        <x-solar-pen-2-outline class="table-action-icon" />
                                    </a>
                                    <button @click="openDeleteModal({{ $location->id }}, '{{ $location->name }}')"
                                        class="table-action-delete"
                                        title="Eliminar">
                                        <x-solar-trash-bin-trash-outline class="table-action-icon" />
                                    </button>
                                    
                                    @if(!$location->is_main)
                                        <button @click="setAsMain({{ $location->id }})"
                                            class="table-action-primary"
                                            title="Establecer como principal">
                                            <x-solar-star-outline class="table-action-icon" />
                                        </button>
                                    @endif
                                    
                                    <button @click="toggleStatus({{ $location->id }}, {{ $location->is_active ? 'true' : 'false' }})"
                                        class="table-action-{{ $location->is_active ? 'warning' : 'success' }}"
                                        title="{{ $location->is_active ? 'Desactivar' : 'Activar' }}">
                                        @if($location->is_active)
                                            <x-solar-eye-closed-outline class="table-action-icon" />
                                        @else
                                            <x-solar-eye-outline class="table-action-icon" />
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-black-300">
                                No hay sedes registradas.
                                @if($remainingSlots > 0)
                                    <a href="{{ route('tenant.admin.locations.create', ['store' => $store->slug]) }}" class="text-primary-200 hover:text-primary-300">
                                        Crear una sede
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PAGINACIÓN --}}
    {{-- ================================================================ --}}
    
    <div class="mt-4">
        {{ $locations->links('tenant-admin::locations.components.pagination') }}
    </div>
</div>

{{-- ================================================================ --}}
{{-- SCRIPTS ESPECÍFICOS --}}
{{-- ================================================================ --}}

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('locationManagement', () => ({
        showDeleteModal: false,
        deleteLocationId: null,
        deleteLocationName: '',
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        
        init() {
            // Inicialización
        },
        
        openDeleteModal(id, name) {
            this.deleteLocationId = id;
            this.deleteLocationName = name;
            this.showDeleteModal = true;
        },
        
        closeDeleteModal() {
            this.showDeleteModal = false;
            this.deleteLocationId = null;
            this.deleteLocationName = '';
        },
        
        confirmDelete() {
            if (!this.deleteLocationId) return;
            
            const url = `{{ route('tenant.admin.locations.destroy', ['store' => $store->slug, 'location' => ':id']) }}`.replace(':id', this.deleteLocationId);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotificationMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotificationMessage(data.message, 'error');
                }
                this.closeDeleteModal();
            })
            .catch(error => {
                this.showNotificationMessage('Error al eliminar la sede', 'error');
                this.closeDeleteModal();
            });
        },
        
        toggleStatus(id, isActive) {
            const url = `{{ route('tenant.admin.locations.toggle-status', ['store' => $store->slug, 'location' => ':id']) }}`.replace(':id', id);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotificationMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotificationMessage(data.message, 'error');
                }
            })
            .catch(error => {
                this.showNotificationMessage('Error al cambiar el estado de la sede', 'error');
            });
        },
        
        setAsMain(id) {
            const url = `{{ route('tenant.admin.locations.set-as-main', ['store' => $store->slug, 'location' => ':id']) }}`.replace(':id', id);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotificationMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotificationMessage(data.message, 'error');
                }
            })
            .catch(error => {
                this.showNotificationMessage('Error al establecer la sede como principal', 'error');
            });
        },
        
        showNotificationMessage(message, type = 'success') {
            this.notificationMessage = message;
            this.notificationType = type;
            this.showNotification = true;
            
            setTimeout(() => {
                this.showNotification = false;
            }, 5000);
        }
    }));
});
</script>
@endpush
@endsection
</x-tenant-admin-layout> 
