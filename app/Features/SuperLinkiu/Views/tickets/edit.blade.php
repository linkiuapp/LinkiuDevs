@extends('shared::layouts.admin')

@section('title', 'Editar Ticket #' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Editar Ticket #{{ $ticket->ticket_number }}</h1>
            <p class="text-sm text-black-300">{{ $ticket->title }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superlinkiu.tickets.show', $ticket) }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-eye-outline class="w-4 h-4" />
                Ver Detalle
            </a>
            <a href="{{ route('superlinkiu.tickets.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
        </div>
    </div>

    <form action="{{ route('superlinkiu.tickets.update', $ticket) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Card única con toda la información -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Información del Ticket</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna izquierda -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Tienda
                            </label>
                            <div class="w-full px-4 py-2 bg-accent-100 border border-accent-200 rounded-lg text-black-400">
                                {{ $ticket->store->name }}
                            </div>
                            <p class="text-xs text-black-200 mt-1">
                                La tienda no se puede cambiar una vez creado el ticket
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Título <span class="text-error-300">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title', $ticket->title) }}"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('title') border-error-200 @enderror"
                                   placeholder="Título descriptivo del problema"
                                   required>
                            @error('title')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Categoría <span class="text-error-300">*</span>
                            </label>
                            <select name="category" 
                                    class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('category') border-error-200 @enderror"
                                    required>
                                <option value="">Seleccionar categoría</option>
                                <option value="technical" {{ old('category', $ticket->category) == 'technical' ? 'selected' : '' }}>Técnico</option>
                                <option value="billing" {{ old('category', $ticket->category) == 'billing' ? 'selected' : '' }}>Facturación</option>
                                <option value="general" {{ old('category', $ticket->category) == 'general' ? 'selected' : '' }}>General</option>
                                <option value="feature_request" {{ old('category', $ticket->category) == 'feature_request' ? 'selected' : '' }}>Solicitud de Función</option>
                            </select>
                            @error('category')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Prioridad <span class="text-error-300">*</span>
                            </label>
                            <select name="priority" 
                                    class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('priority') border-error-200 @enderror"
                                    required>
                                <option value="">Seleccionar prioridad</option>
                                <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="urgent" {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priority')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Asignar a
                            </label>
                            <select name="assigned_to" 
                                    class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('assigned_to') border-error-200 @enderror">
                                <option value="">Sin asignar</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ old('assigned_to', $ticket->assigned_to) == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado actual -->
                        <div class="bg-info-100 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-info-300 mb-1">Estado Actual</h4>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $ticket->status_color }}-200 text-accent-50">
                                            {{ $ticket->status_label }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $ticket->priority_color }}-200 text-accent-50">
                                            {{ $ticket->priority_label }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-info-300">Creado</p>
                                    <p class="text-xs text-info-300">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción (ancho completo) -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-black-300 mb-2">
                        Descripción <span class="text-error-300">*</span>
                    </label>
                    <textarea name="description" rows="6"
                              class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('description') border-error-200 @enderror"
                              placeholder="Describe detalladamente el problema o solicitud..."
                              required>{{ old('description', $ticket->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Advertencias -->
                @if($ticket->status === 'closed')
                    <div class="mt-6 bg-warning-100 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <x-solar-danger-outline class="w-5 h-5 text-warning-300 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="text-sm font-medium text-warning-300 mb-1">Ticket Cerrado</h4>
                                <p class="text-sm text-warning-300">
                                    Este ticket está cerrado. Los cambios que realices no afectarán el estado del ticket.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($ticket->responses->count() > 0)
                    <div class="mt-6 bg-info-100 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <x-solar-chat-round-outline class="w-5 h-5 text-info-300 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="text-sm font-medium text-info-300 mb-1">Conversación Activa</h4>
                                <p class="text-sm text-info-300">
                                    Este ticket tiene {{ $ticket->responses->count() }} respuesta(s). 
                                    <a href="{{ route('superlinkiu.tickets.show', $ticket) }}" class="underline">Ver conversación completa</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer con botones -->
            <div class="border-t border-accent-100 bg-accent-50 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-black-300">
                        Última actualización: {{ $ticket->updated_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('superlinkiu.tickets.show', $ticket) }}"
                            class="btn-outline-secondary px-6 py-2 rounded-lg">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                            <x-solar-diskette-outline class="w-5 h-5" />
                            Actualizar Ticket
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection 