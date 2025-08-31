<x-tenant-admin-layout :store="$store">

@section('title', 'Detalles de Sede')

@section('content')
<div class="container-fluid" x-data="locationManagement">
    {{-- Sistema de Notificaciones --}}
    @include('tenant-admin::locations.components.notifications')

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="text-primary-300 font-bold text-xl">
                    {{ strtoupper(substr($location->name, 0, 2)) }}
                </span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-black-400">
                    {{ $location->name }}
                    @if($location->is_main)
                        <span class="badge-soft-primary ml-2">Principal</span>
                    @endif
                </h1>
                <div class="flex items-center gap-2">
                    <p class="text-sm text-black-300">{{ $location->city }}, {{ $location->department }}</p>
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('tenant.admin.locations.edit', ['store' => $store->slug, 'location' => $location->id]) }}"
                class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-pen-2-outline class="w-5 h-5" />
                Editar Sede
            </a>
            <a href="{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}" 
                class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-5 h-5" />
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Información básica --}}
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Información Básica</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Nombre</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $location->name }}</dd>
                        </div>
                        
                        @if($location->manager_name)
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Encargado</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $location->manager_name }}</dd>
                        </div>
                        @endif
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Teléfono</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $location->phone }}</dd>
                        </div>
                        
                        @if($location->whatsapp)
                        <div>
                            <dt class="text-sm font-semibold text-black-300">WhatsApp</dt>
                            <dd class="mt-1 text-sm text-black-400">
                                <div class="flex items-center gap-2">
                                    <span>{{ $location->whatsapp }}</span>
                                    <a href="{{ \App\Shared\Models\LocationSocialLink::formatWhatsAppUrl($location->whatsapp, $location->whatsapp_message) }}" 
                                       target="_blank" 
                                       class="text-success-200 hover:text-success-300"
                                       @click="incrementWhatsAppClicks">
                                        <x-solar-chat-round-line-linear class="w-4 h-4" />
                                    </a>
                                </div>
                            </dd>
                        </div>
                        @endif
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Ubicación</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $location->address }}, {{ $location->city }}, {{ $location->department }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-semibold text-black-300">Estado</dt>
                            <dd class="mt-1 text-sm text-black-400 pt-2">
                                @if($location->is_active)
                                    <span class="badge-soft-success">Activa</span>
                                @else
                                    <span class="badge-soft-secondary">Inactiva</span>
                                @endif
                            </dd>
                        </div>
                        
                        <div class="md:col-span-2">
                            <dt class="text-sm font-semibold text-black-300">Descripción</dt>
                            <dd class="mt-1 text-sm text-black-400">{{ $location->description ?: 'Sin descripción' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Horarios --}}
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Horarios de Atención</h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-accent-100">
                            <thead class="bg-accent-100">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Día
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-black-300 uppercase tracking-wider">
                                        Horario
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-accent-50 divide-y divide-accent-100">
                                @php
                                    $days = [
                                        0 => 'Domingo',
                                        1 => 'Lunes',
                                        2 => 'Martes',
                                        3 => 'Miércoles',
                                        4 => 'Jueves',
                                        5 => 'Viernes',
                                        6 => 'Sábado',
                                    ];
                                    
                                    $today = now()->dayOfWeek;
                                @endphp
                                
                                @foreach($days as $dayNum => $dayName)
                                    @php
                                        $schedule = $schedulesByDay[$dayNum] ?? null;
                                        $isToday = $dayNum === $today;
                                    @endphp
                                    <tr class="{{ $isToday ? 'bg-primary-50' : '' }}">
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-black-400">
                                                {{ $dayName }}
                                                @if($isToday)
                                                    <span class="text-primary-200 text-xs ml-1">(Hoy)</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(!$schedule || $schedule->is_closed)
                                                <span class="badge-soft-error px-3 py-1 text-xs">Cerrado</span>
                                            @else
                                                <span class="badge-soft-success px-3 py-1 text-xs">Abierto</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(!$schedule || $schedule->is_closed)
                                                <span class="text-sm text-black-300">No disponible</span>
                                            @else
                                                <div class="text-sm text-black-400">
                                                    @php
                                                        $openTime1 = \Carbon\Carbon::parse($schedule->open_time_1)->format('H:i');
                                                        $closeTime1 = \Carbon\Carbon::parse($schedule->close_time_1)->format('H:i');
                                                    @endphp
                                                    <span>{{ $openTime1 }} - {{ $closeTime1 }}</span>
                                                    
                                                    @if($schedule->open_time_2 && $schedule->close_time_2)
                                                        @php
                                                            $openTime2 = \Carbon\Carbon::parse($schedule->open_time_2)->format('H:i');
                                                            $closeTime2 = \Carbon\Carbon::parse($schedule->close_time_2)->format('H:i');
                                                        @endphp
                                                        <span class="ml-2 text-black-300">y {{ $openTime2 }} - {{ $closeTime2 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Redes Sociales --}}
            @if($location->socialLinks->count() > 0)
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Redes Sociales</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($location->socialLinks as $socialLink)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full {{ $socialLink->getPlatformColor() }} bg-opacity-10 flex items-center justify-center">
                                    <x-dynamic-component :component="$socialLink->getPlatformIcon()" class="w-5 h-5 {{ $socialLink->getPlatformColor() }}" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-black-400">{{ $platforms[$socialLink->platform] }}</p>
                                    <a href="{{ $socialLink->url }}" target="_blank" class="text-xs text-primary-200 hover:text-primary-300">
                                        {{ Str::limit($socialLink->url, 30) }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Columna lateral --}}
        <div class="space-y-6">
            {{-- Estado actual --}}
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Estado Actual</h2>
                </div>
                <div class="p-6">
                    <div class="text-center mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-{{ $location->currentStatus['statusColor'] }}-50 mb-3">
                            @if($location->currentStatus['status'] === 'open')
                                <x-solar-exit-outline class="w-8 h-8 text-{{ $location->currentStatus['statusColor'] }}-300" />
                            @elseif($location->currentStatus['status'] === 'temporarily_closed')
                                <x-solar-clock-circle-outline class="w-8 h-8 text-{{ $location->currentStatus['statusColor'] }}-300" />
                            @else
                                <x-solar-folder-2-outline class="w-8 h-8 text-{{ $location->currentStatus['statusColor'] }}-300" />
                            @endif
                        </div>
                        <p class="text-2xl font-bold text-{{ $location->currentStatus['statusColor'] }}-300">{{ $location->currentStatus['statusText'] }}</p>
                        @if($location->currentStatus['nextChangeText'])
                            <p class="text-sm text-black-300 mt-2">{{ $location->currentStatus['nextChangeText'] }}</p>
                        @endif
                    </div>
                    
                    <div class="space-y-3 pt-4 border-t border-accent-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-black-300">Día actual</span>
                            <span class="font-semibold text-black-400">{{ $days[now()->dayOfWeek] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-black-300">Hora actual</span>
                            <span class="font-semibold text-black-400">{{ now()->format('H:i') }}</span>
                        </div>
                        @if($location->whatsapp)
                        <div class="flex justify-between text-sm">
                            <span class="text-black-300">Clics en WhatsApp</span>
                            <span class="font-semibold text-black-400">{{ $location->whatsapp_clicks }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Acciones Rápidas</h2>
                </div>
                <div class="p-6 space-y-3">
                    @if(!$location->is_main)
                        <button @click="setAsMain({{ $location->id }})"
                            class="w-full btn-outline-primary px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-star-outline class="w-5 h-5" />
                            Establecer como Principal
                        </button>
                    @endif
                    
                    <button @click="toggleStatus({{ $location->id }}, {{ $location->is_active ? 'true' : 'false' }})"
                        class="w-full btn-outline-{{ $location->is_active ? 'warning' : 'success' }} px-4 py-2 rounded-lg flex items-center justify-center gap-2"
                        {{ $location->is_main ? 'disabled' : '' }}>
                        @if($location->is_active)
                            <x-solar-eye-closed-outline class="w-5 h-5" />
                            Desactivar Sede
                        @else
                            <x-solar-eye-outline class="w-5 h-5" />
                            Activar Sede
                        @endif
                    </button>
                    
                    <button @click="openDeleteModal({{ $location->id }}, '{{ $location->name }}')"
                        class="w-full btn-outline-error px-4 py-2 rounded-lg flex items-center justify-center gap-2"
                        {{ $location->is_main ? 'disabled' : '' }}>
                        <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                        Eliminar Sede
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de eliminación --}}
    @include('tenant-admin::locations.components.delete-modal')
</div>

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
                        window.location.href = '{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}';
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
            const url = `{{ route('tenant.admin.locations.toggle-status', ['store' => $currentStore->slug, 'location' => ':id']) }}`.replace(':id', id);
            
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
            const url = `{{ route('tenant.admin.locations.set-as-main', ['store' => $currentStore->slug, 'location' => ':id']) }}`.replace(':id', id);
            
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
        
        incrementWhatsAppClicks() {
            const url = `{{ route('tenant.admin.locations.whatsapp-click', ['store' => $currentStore->slug, 'location' => $location->id]) }}`;
            
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
                    console.log('WhatsApp click registered');
                }
            })
            .catch(error => {
                console.error('Error registering WhatsApp click');
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
