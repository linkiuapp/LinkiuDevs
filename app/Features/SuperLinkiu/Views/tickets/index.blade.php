@extends('shared::layouts.admin')

@section('title', 'Gestión de Tickets')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Gestión de Tickets</h1>
        <a href="{{ route('superlinkiu.tickets.create') }}" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-add-circle-outline class="w-5 h-5" />
            Crear Ticket
        </a>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-accent-50 rounded-lg p-4">
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

        <div class="bg-accent-50 rounded-lg p-4">
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

        <div class="bg-accent-50 rounded-lg p-4">
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

        <div class="bg-accent-50 rounded-lg p-4">
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

        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Urgentes</p>
                    <p class="text-2xl font-bold text-error-300">{{ $stats['urgent'] }}</p>
                </div>
                <div class="w-10 h-10 bg-error-100 rounded-lg flex items-center justify-center">
                    <x-solar-danger-outline class="w-5 h-5 text-error-300" />
                </div>
            </div>
        </div>

        <div class="bg-accent-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-black-300">Vencidos</p>
                    <p class="text-2xl font-bold text-error-300">{{ $stats['overdue'] }}</p>
                </div>
                <div class="w-10 h-10 bg-error-100 rounded-lg flex items-center justify-center">
                    <x-solar-clock-circle-outline class="w-5 h-5 text-error-300" />
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-accent-50 rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('superlinkiu.tickets.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Número, título o tienda..."
                       class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todos los estados</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Abierto</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Prioridad</label>
                <select name="priority" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todas las prioridades</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Categoría</label>
                <select name="category" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todas las categorías</option>
                    <option value="technical" {{ request('category') == 'technical' ? 'selected' : '' }}>Técnico</option>
                    <option value="billing" {{ request('category') == 'billing' ? 'selected' : '' }}>Facturación</option>
                    <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                    <option value="feature_request" {{ request('category') == 'feature_request' ? 'selected' : '' }}>Solicitud de Función</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Tienda</label>
                <select name="store_id" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todas las tiendas</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-black-300 mb-2">Asignado a</label>
                <select name="assigned_to" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                    <option value="">Todos</option>
                    <option value="unassigned" {{ request('assigned_to') == 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-magnifer-outline class="w-4 h-4" />
                    Filtrar
                </button>
                <a href="{{ route('superlinkiu.tickets.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de tickets -->
    <div class="bg-accent-50 rounded-lg overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">
                Lista de Tickets ({{ $tickets->total() }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-accent-100">
                    <tr>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Número</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Título</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Tienda</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Categoría</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Prioridad</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Estado</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Asignado a</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Fecha</th>
                        <th class="text-left py-3 px-4 font-medium text-black-300">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-accent-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-accent-100 transition-colors duration-150">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('superlinkiu.tickets.show', $ticket) }}" 
                                       class="font-mono text-sm text-primary-300 hover:text-primary-400 hover:underline transition-colors duration-150"
                                       title="Ver detalle del ticket">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                    @if($ticket->has_new_store_responses)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-200 text-accent-50" title="Respuestas nuevas de la tienda">
                                            <x-solar-chat-round-dots-outline class="w-3 h-3 mr-1" />
                                            {{ $ticket->new_store_responses_count }} nueva{{ $ticket->new_store_responses_count > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    @if($ticket->is_overdue)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-error-200 text-accent-50">
                                            <x-solar-clock-circle-outline class="w-3 h-3 mr-1" />
                                            Vencido
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="max-w-xs">
                                    <p class="font-medium text-black-400 truncate">{{ $ticket->title }}</p>
                                    <p class="text-sm text-black-300 truncate">{{ Str::limit($ticket->description, 50) }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-black-400">{{ $ticket->store->name }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-black-400">{{ $ticket->category_label }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $ticket->priority_color }}-200 text-accent-50">
                                    {{ $ticket->priority_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $ticket->status_color }}-200 text-accent-50">
                                    {{ $ticket->status_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($ticket->assignedTo)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-primary-200 rounded-full flex items-center justify-center">
                                            <span class="text-xs text-accent-50">{{ substr($ticket->assignedTo->name, 0, 1) }}</span>
                                        </div>
                                        <span class="text-sm text-black-400">{{ $ticket->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-black-200">Sin asignar</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-sm text-black-400">
                                    {{ $ticket->created_at->format('d/m/Y') }}
                                    <div class="text-xs text-black-300">{{ $ticket->created_at->format('H:i') }}</div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('superlinkiu.tickets.show', $ticket) }}" 
                                       class="text-info-300 hover:text-info-400 transition-colors duration-150"
                                       title="Ver detalle">
                                        <x-solar-eye-outline class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('superlinkiu.tickets.edit', $ticket) }}" 
                                       class="text-warning-300 hover:text-warning-400 transition-colors duration-150"
                                       title="Editar">
                                        <x-solar-pen-outline class="w-4 h-4" />
                                    </a>
                                    @if($ticket->status !== 'closed')
                                        <form method="POST" action="{{ route('superlinkiu.tickets.destroy', $ticket) }}" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este ticket?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-error-300 hover:text-error-400 transition-colors duration-150"
                                                    title="Eliminar">
                                                <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 px-4 text-center text-black-300">
                                <div class="flex flex-col items-center gap-2">
                                    <x-solar-ticket-outline class="w-12 h-12 text-black-200" />
                                    <p>No se encontraron tickets</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($tickets->hasPages())
            <div class="border-t border-accent-100 px-6 py-4">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 