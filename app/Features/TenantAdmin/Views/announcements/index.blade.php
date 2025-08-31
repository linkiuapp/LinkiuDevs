@extends('shared::layouts.tenant-admin')

@section('title', 'Anuncios de Linkiu')

@section('content')
<div class="container-fluid" x-data="announcementsBoard">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">📢 Anuncios de Linkiu</h1>
            <p class="text-sm text-black-300">Mantente informado sobre actualizaciones y novedades de la plataforma</p>
        </div>
        <div class="flex items-center gap-3">
            @if($stats['unread'] > 0)
                <button @click="markAllAsRead()" 
                        class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-check-read-outline class="w-4 h-4" />
                    Marcar Todos como Leídos
                </button>
            @endif
        </div>
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
                    <p class="text-sm text-black-300">Sin Leer</p>
                    <p class="text-2xl font-bold text-warning-300">{{ $stats['unread'] }}</p>
                </div>
                <div class="w-10 h-10 bg-warning-100 rounded-lg flex items-center justify-center">
                    <x-solar-notification-unread-outline class="w-5 h-5 text-warning-300" />
                </div>
            </div>
        </div>

        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Banners</p>
                    <p class="text-2xl font-bold text-primary-300">{{ $stats['banners'] }}</p>
                </div>
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <x-solar-gallery-outline class="w-5 h-5 text-primary-300" />
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
                <label class="block text-sm font-medium text-black-300 mb-2">Estado de Lectura</label>
                <select name="status" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todos</option>
                    <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Sin leer</option>
                    <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Leídos</option>
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
                <a href="{{ route('tenant.admin.announcements.index', $store->slug) }}" 
                   class="btn-outline-secondary px-4 py-2 rounded-lg">
                    <x-solar-refresh-outline class="w-4 h-4" />
                </a>
            </div>
        </form>
    </div>

    <!-- Listado de Anuncios -->
    <div class="space-y-4">
        @forelse($announcements as $announcement)
            @php
                $isRead = $announcement->isReadBy($store->id);
            @endphp
            
            <div class="bg-accent-50 rounded-lg border-l-4 border-{{ $announcement->type_color }}-200 p-0 overflow-hidden {{ !$isRead ? 'ring-2 ring-' . $announcement->type_color . '-100' : '' }}">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <!-- Header del anuncio -->
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-10 h-10 bg-{{ $announcement->type_color }}-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg">{{ $announcement->type_icon }}</span>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-lg font-semibold text-black-400">{{ $announcement->title }}</h3>
                                        @if(!$isRead)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning-200 text-accent-50">
                                                Nuevo
                                            </span>
                                        @endif
                                        @if($announcement->show_as_banner)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-300">
                                                <x-solar-gallery-outline class="w-3 h-3 mr-1" />
                                                Banner
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center gap-4 text-sm text-black-300">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $announcement->type_color }}-100 text-{{ $announcement->type_color }}-300">
                                            {{ $announcement->type_label }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <x-solar-calendar-outline class="w-4 h-4" />
                                            {{ $announcement->created_at->format('d/m/Y') }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <x-solar-star-outline class="w-4 h-4" />
                                            Prioridad {{ $announcement->priority }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenido -->
                            <div class="ml-13">
                                <p class="text-black-400 leading-relaxed mb-4">
                                    {{ Str::limit($announcement->content, 200) }}
                                </p>

                                <!-- Banner preview si existe -->
                                @if($announcement->banner_image)
                                    <div class="mb-4">
                                        <img src="{{ $announcement->banner_image_url }}" 
                                             alt="Banner" 
                                             class="border border-accent-200 rounded"
                                             style="width: 160px; height: 50px; object-fit: cover;">
                                    </div>
                                @endif

                                <!-- Acciones -->
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('tenant.admin.announcements.show', ['store' => $store->slug, 'announcement' => $announcement]) }}" 
                                       class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                        Leer Completo
                                    </a>

                                    @if(!$isRead)
                                        <button @click="markAsRead({{ $announcement->id }})"
                                                class="btn-outline-success px-4 py-2 rounded-lg flex items-center gap-2">
                                            <x-solar-check-circle-outline class="w-4 h-4" />
                                            Marcar como Leído
                                        </button>
                                    @else
                                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm text-success-300 bg-success-50">
                                            <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                                            Leído ✓
                                        </span>
                                    @endif

                                    @if($announcement->banner_link)
                                        <a href="{{ $announcement->banner_link }}" 
                                           target="_blank"
                                           class="btn-outline-info px-4 py-2 rounded-lg flex items-center gap-2">
                                            <x-solar-link-outline class="w-4 h-4" />
                                            Ver Enlace
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Fecha relativa -->
                        <div class="text-right text-sm text-black-300">
                            {{ $announcement->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-accent-50 rounded-lg p-12 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-16 h-16 bg-black-100 rounded-full flex items-center justify-center">
                        <x-solar-chat-dots-outline class="w-8 h-8 text-black-200" />
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-black-400 mb-2">No hay anuncios</h3>
                        <p class="text-black-300">
                            @if(request()->hasAny(['type', 'status', 'search']))
                                No se encontraron anuncios con los filtros aplicados.
                            @else
                                Aún no hay anuncios disponibles para tu tienda.
                            @endif
                        </p>
                    </div>
                    @if(request()->hasAny(['type', 'status', 'search']))
                        <a href="{{ route('tenant.admin.announcements.index', $store->slug) }}" 
                           class="btn-outline-primary px-4 py-2 rounded-lg">
                            Limpiar Filtros
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="mt-6">
            {{ $announcements->links() }}
        </div>
    @endif

    <!-- Toast de notificación -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-4 right-4 z-50 max-w-sm"
         style="display: none;">
        <div class="bg-success-200 text-accent-50 rounded-lg shadow-lg p-4 flex items-center gap-3">
            <x-solar-check-circle-outline class="w-5 h-5 flex-shrink-0" />
            <span x-text="toastMessage" class="text-sm font-medium"></span>
        </div>
    </div>
</div>

@push('scripts')
<script>
function announcementsBoard() {
    return {
        showToast: false,
        toastMessage: '',
        
        markAsRead(announcementId) {
            fetch(`{{ route('tenant.admin.announcements.mark-as-read', ['store' => $store->slug, 'announcement' => ':id']) }}`.replace(':id', announcementId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showToastMessage(data.message);
                    // Recargar página para actualizar estados
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        },
        
        markAllAsRead() {
            if (confirm('¿Marcar todos los anuncios como leídos?')) {
                fetch(`{{ route('tenant.admin.announcements.mark-all-as-read', $store->slug) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showToastMessage(data.message);
                        // Recargar página para actualizar estados
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        },
        
        showToastMessage(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        }
    }
}
</script>
@endpush
@endsection 