@extends('shared::layouts.tenant-admin')

@section('title', 'Soporte y Tickets')

@section('content')
<div class="container-fluid" x-data="ticketsIndex()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Soporte y Tickets</h1>
            <p class="text-sm text-black-300">Gestiona tus consultas y solicitudes de soporte</p>
        </div>
        <a href="{{ route('tenant.admin.tickets.create', ['store' => $store->slug]) }}" 
           class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-add-circle-outline class="w-5 h-5" />
            Crear Ticket
        </a>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Total</p>
                    <p class="text-2xl font-bold text-black-400">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-info-100 rounded-lg flex items-center justify-center">
                    <x-solar-ticket-outline class="w-5 h-5 text-info-300" />
                </div>
            </div>
        </div>

        <div class="bg-white-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Abiertos</p>
                    <p class="text-2xl font-bold text-info-300">{{ $stats['open'] }}</p>
                </div>
                <div class="w-10 h-10 bg-info-100 rounded-lg flex items-center justify-center">
                    <x-solar-clock-circle-outline class="w-5 h-5 text-info-300" />
                </div>
            </div>
        </div>

        <div class="bg-white-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">En Progreso</p>
                    <p class="text-2xl font-bold text-warning-300">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="w-10 h-10 bg-warning-100 rounded-lg flex items-center justify-center">
                    <x-solar-settings-outline class="w-5 h-5 text-warning-300" />
                </div>
            </div>
        </div>

        <div class="bg-white-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Resueltos</p>
                    <p class="text-2xl font-bold text-success-300">{{ $stats['resolved'] }}</p>
                </div>
                <div class="w-10 h-10 bg-success-100 rounded-lg flex items-center justify-center">
                    <x-solar-check-circle-outline class="w-5 h-5 text-success-300" />
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white-50 rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Número, asunto..."
                       class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('search') border-error-300 @enderror">
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todos los estados</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Abierto</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Prioridad</label>
                <select name="priority" class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todas las prioridades</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Categoría</label>
                <select name="category" class="w-full px-3 py-2 border border-white-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $slug => $name)
                        <option value="{{ $slug }}" {{ request('category') == $slug ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 lg:col-span-4 flex items-end gap-2">
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-magnifer-outline class="w-4 h-4" />
                    Filtrar
                </button>
                <a href="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
                   class="btn-outline-secondary px-4 py-2 rounded-lg">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Tickets -->
    <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">
                Mis Tickets ({{ $tickets->total() }})
            </h2>
        </div>

        <div class="p-6">
            @forelse($tickets as $ticket)
                <div class="border border-white-100 rounded-lg p-4 mb-4 hover:bg-white-100 transition-colors duration-150">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm font-semibold text-primary-300">
                                {{ $ticket->ticket_number }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $ticket->status_color == 'info' ? 'bg-info-300 text-white-50' : ($ticket->status_color == 'warning' ? 'bg-warning-300 text-black-500' : ($ticket->status_color == 'success' ? 'bg-success-300 text-white-50' : 'bg-secondary-300 text-white-50')) }}">
                                {{ $ticket->status_label }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $ticket->priority_color == 'success' ? 'bg-success-300 text-white-50' : ($ticket->priority_color == 'info' ? 'bg-info-300 text-white-50' : ($ticket->priority_color == 'warning' ? 'bg-warning-300 text-black-500' : 'bg-error-300 text-white-50')) }}">
                                {{ $ticket->priority_label }}
                            </span>
                            @if($ticket->has_new_support_responses)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-200 text-white-50" title="Respuestas nuevas del soporte">
                                    <x-solar-chat-round-dots-outline class="w-3 h-3 mr-1" />
                                    {{ $ticket->new_support_responses_count }} nueva{{ $ticket->new_support_responses_count > 1 ? 's' : '' }}
                                </span>
                            @endif
                            <span class="text-xs text-black-300">
                                {{ $ticket->category_label }}
                            </span>
                        </div>
                        <div class="text-xs text-black-300">
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <h3 class="font-semibold text-black-400 mb-1">{{ $ticket->title }}</h3>
                        <p class="text-sm text-black-300">{{ Str::limit($ticket->description, 150) }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-xs text-black-300">
                            @if($ticket->responses->count() > 0)
                                Última respuesta: {{ $ticket->responses->last()->created_at->diffForHumans() }}
                            @else
                                Sin respuestas
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('tenant.admin.tickets.show', ['store' => $store->slug, 'ticket' => $ticket]) }}" 
                               class="btn-outline-primary px-3 py-1 text-xs rounded flex items-center gap-1">
                                <x-solar-eye-outline class="w-3 h-3" />
                                Ver Ticket
                            </a>
                            @if(in_array($ticket->status, ['open', 'in_progress']))
                                <a href="{{ route('tenant.admin.tickets.show', ['store' => $store->slug, 'ticket' => $ticket]) }}#response" 
                                   class="btn-primary px-3 py-1 text-xs rounded flex items-center gap-1">
                                    <x-solar-chat-round-dots-outline class="w-3 h-3" />
                                    Responder
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="flex flex-col items-center gap-2">
                        <x-solar-ticket-outline class="w-12 h-12 text-black-200" />
                        <p class="text-black-300">No tienes tickets de soporte</p>
                        <a href="{{ route('tenant.admin.tickets.create', ['store' => $store->slug]) }}" 
                           class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2 mt-2">
                            <x-solar-add-circle-outline class="w-4 h-4" />
                            Crear tu primer ticket
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($tickets->hasPages())
            <div class="border-t border-white-100 px-6 py-4">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function ticketsIndex() {
    return {
        // Funciones futuras para filtros dinámicos
    }
}
</script>
@endsection 