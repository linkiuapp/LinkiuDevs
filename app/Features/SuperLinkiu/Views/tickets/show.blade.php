@extends('shared::layouts.admin')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div class="container-fluid" x-data="ticketDetail()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Ticket #{{ $ticket->ticket_number }}</h1>
            <p class="text-sm text-black-300">{{ $ticket->title }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superlinkiu.tickets.edit', $ticket) }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-pen-outline class="w-4 h-4" />
                Editar
            </a>
            <a href="{{ route('superlinkiu.tickets.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contenido principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información del ticket -->
            <div class="bg-accent-50 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-black-400">Información del Ticket</h2>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $ticket->priority_color }}-200 text-accent-50">
                            {{ $ticket->priority_label }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $ticket->status_color }}-200 text-accent-50">
                            {{ $ticket->status_label }}
                        </span>
                        @if($ticket->is_overdue)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-error-200 text-accent-50">
                                <x-solar-clock-circle-outline class="w-4 h-4 mr-1" />
                                Vencido
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-black-300 mb-1">Tienda</label>
                        <p class="text-sm text-black-400">{{ $ticket->store->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-300 mb-1">Categoría</label>
                        <p class="text-sm text-black-400">{{ $ticket->category_label }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-300 mb-1">Fecha de Creación</label>
                        <p class="text-sm text-black-400">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($ticket->resolved_at)
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-1">Fecha de Resolución</label>
                            <p class="text-sm text-black-400">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-black-300 mb-2">Descripción</label>
                    <div class="bg-accent-100 rounded-lg p-4">
                        <p class="text-sm text-black-400 whitespace-pre-wrap">{{ $ticket->description }}</p>
                        
                        <!-- Archivos adjuntos del ticket original -->
                        @if($ticket->attachments && count($ticket->attachments) > 0)
                            <div class="mt-3 pt-3 border-t border-accent-200">
                                <label class="block text-xs font-medium text-black-300 mb-2">Archivos adjuntos:</label>
                                <div class="space-y-1">
                                    @foreach($ticket->attachments as $attachment)
                                        <div class="flex items-center gap-2 text-sm">
                                            @if(in_array($attachment['mime_type'], ['image/jpeg', 'image/jpg', 'image/png']))
                                                <x-solar-gallery-outline class="w-4 h-4 text-primary-300" />
                                            @else
                                                <x-solar-document-outline class="w-4 h-4 text-black-300" />
                                            @endif
                                            <a href="{{ $ticket->getAttachmentUrl($attachment) }}" 
                                               target="_blank"
                                               class="text-primary-300 hover:text-primary-400 truncate">
                                                {{ $attachment['original_name'] }}
                                            </a>
                                            <span class="text-xs text-black-200">
                                                ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Conversación -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden" x-data="{ showAllMessages: {{ $ticket->responses->count() <= 3 ? 'true' : 'false' }} }">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Conversación ({{ $ticket->responses->count() }} respuestas)</h2>
                        @if($ticket->responses->count() > 3)
                            <button x-on:click="showAllMessages = !showAllMessages" 
                                    class="btn-outline-secondary px-3 py-1 text-sm rounded-lg flex items-center gap-2">
                                <x-solar-eye-outline class="w-4 h-4" x-show="!showAllMessages" />
                                <x-solar-eye-closed-outline class="w-4 h-4" x-show="showAllMessages" />
                                <span x-text="showAllMessages ? 'Mostrar solo recientes' : 'Mostrar todo el historial'"></span>
                            </button>
                        @endif
                    </div>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Indicador de mensajes ocultos -->
                    @if($ticket->responses->count() > 3)
                        <div x-show="!showAllMessages" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             class="text-center py-4 border border-accent-200 rounded-lg bg-accent-100">
                            <div class="flex items-center justify-center gap-2 text-black-300">
                                <x-solar-history-outline class="w-5 h-5" />
                                <span class="font-medium">{{ $ticket->responses->count() - 3 }} mensajes anteriores ocultos</span>
                            </div>
                            <button x-on:click="showAllMessages = true" 
                                    class="mt-2 text-primary-300 hover:text-primary-400 text-sm font-medium">
                                Mostrar todo el historial →
                            </button>
                        </div>
                    @endif

                    @forelse($ticket->responses as $index => $response)
                        <!-- Solo mostrar los últimos 3 mensajes por defecto, el resto colapsados -->
                        <div x-show="showAllMessages || {{ $index >= $ticket->responses->count() - 3 ? 'true' : 'false' }}" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="flex gap-4 {{ $response->is_support_team ? 'justify-end' : 'justify-start' }}">
                            
                            <div class="w-full max-w-2xl {{ $response->is_support_team ? 'order-2' : 'order-1' }}">
                                <div class="bg-{{ $response->is_support_team ? 'primary' : 'accent' }}-100 rounded-lg p-4 border border-accent-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-{{ $response->is_support_team ? 'primary' : 'secondary' }}-200 rounded-full flex items-center justify-center">
                                                <span class="text-xs text-accent-50">{{ substr($response->user->name ?? 'U', 0, 1) }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-black-400">
                                                                                        @if($response->is_support_team)
                                            🛠️ {{ $response->user->name ?? 'Soporte Linkiu' }}
                                        @else
                                            👤 {{ $response->user->name ?? 'Usuario de la tienda' }}
                                        @endif
                                            </span>
                                            @if($response->response_type === 'status_change')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning-300 text-black-500">
                                                    <x-solar-refresh-outline class="w-3 h-3 mr-1" />
                                                    Cambio de Estado
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-black-300">{{ $response->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="text-sm text-black-400 whitespace-pre-wrap leading-relaxed">{{ $response->message }}</div>
                                    
                                    <!-- Archivos adjuntos de la respuesta -->
                                    @if($response->attachments && count($response->attachments) > 0)
                                        <div class="mt-3 pt-3 border-t border-accent-200">
                                            <label class="block text-xs font-medium text-black-300 mb-2">Archivos adjuntos:</label>
                                            <div class="space-y-1">
                                                @foreach($response->attachments as $attachment)
                                                    <div class="flex items-center gap-2 text-sm">
                                                        @if(in_array($attachment['mime_type'], ['image/jpeg', 'image/jpg', 'image/png']))
                                                            <x-solar-gallery-outline class="w-4 h-4 text-primary-300" />
                                                        @else
                                                            <x-solar-document-outline class="w-4 h-4 text-black-300" />
                                                        @endif
                                                        <a href="{{ $response->getAttachmentUrl($attachment) }}" 
                                                           target="_blank"
                                                           class="text-primary-300 hover:text-primary-400 truncate">
                                                            {{ $attachment['original_name'] }}
                                                        </a>
                                                        <span class="text-xs text-black-200">
                                                            ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($response->response_type !== 'response')
                                        <div class="mt-2 text-xs text-black-300 italic">
                                            {{ $response->response_type_label }}
                                            @if(!$response->is_public)
                                                - Nota interna
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="flex flex-col items-center gap-2">
                                <x-solar-chat-round-dots-outline class="w-12 h-12 text-black-200" />
                                <p class="text-black-300">Aún no hay respuestas en este ticket</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Formulario de respuesta -->
            @if($ticket->status !== 'closed')
                <div class="bg-accent-50 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-4">Agregar Respuesta</h2>
                    
                    <form method="POST" action="{{ route('superlinkiu.tickets.add-response', $ticket) }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">Tipo de Respuesta</label>
                                <select name="response_type" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                                    <option value="response">Respuesta Pública</option>
                                    <option value="internal_note">Nota Interna</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black-300 mb-2">Cambiar Estado</label>
                                <select name="change_status" class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                                    <option value="">Mantener estado actual</option>
                                    <option value="in_progress">En Progreso</option>
                                    <option value="resolved">Resuelto</option>
                                    <option value="closed">Cerrado</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-black-300 mb-2">Mensaje</label>
                            <textarea name="message" rows="4" 
                                      class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('message') border-error-200 @enderror"
                                      placeholder="Escribe tu respuesta aquí..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                                <x-solar-chat-round-outline class="w-4 h-4" />
                                Enviar Respuesta
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Panel lateral -->
        <div class="space-y-6">
            <!-- Acciones rápidas -->
            <div class="bg-accent-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Acciones Rápidas</h3>
                
                <div class="space-y-3">
                    <!-- Cambiar estado -->
                    <div>
                        <label class="block text-sm font-medium text-black-300 mb-2">Estado</label>
                        <select x-model="currentStatus" @change="updateStatus()" 
                                class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Abierto</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resuelto</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Cerrado</option>
                        </select>
                    </div>

                    <!-- Asignar -->
                    <div>
                        <label class="block text-sm font-medium text-black-300 mb-2">Asignado a</label>
                        <select x-model="currentAssignedTo" @change="updateAssignment()" 
                                class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                            <option value="">Sin asignar</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Prioridad -->
                    <div>
                        <label class="block text-sm font-medium text-black-300 mb-2">Prioridad</label>
                        <select x-model="currentPriority" @change="updatePriority()" 
                                class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgente</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Métricas -->
            <div class="bg-accent-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Métricas</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Tiempo de respuesta</span>
                        <span class="text-sm text-black-400">
                            @if($ticket->response_time)
                                {{ $ticket->response_time }}h
                            @else
                                Sin respuesta
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Tiempo de resolución</span>
                        <span class="text-sm text-black-400">
                            @if($ticket->resolution_time)
                                {{ $ticket->resolution_time }}h
                            @else
                                Sin resolver
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Respuestas</span>
                        <span class="text-sm text-black-400">{{ $ticket->responses->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Última actividad</span>
                        <span class="text-sm text-black-400">
                            {{ $ticket->responses->last() ? $ticket->responses->last()->created_at->diffForHumans() : $ticket->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ticketDetail() {
    return {
        currentStatus: '{{ $ticket->status }}',
        currentAssignedTo: '{{ $ticket->assigned_to }}',
        currentPriority: '{{ $ticket->priority }}',
        
        async updateStatus() {
            try {
                const response = await fetch(`/superlinkiu/tickets/{{ $ticket->id }}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: this.currentStatus
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('Error al actualizar el estado', 'error');
                }
            } catch (error) {
                this.showNotification('Error al actualizar el estado', 'error');
            }
        },
        
        async updateAssignment() {
            try {
                const response = await fetch(`/superlinkiu/tickets/{{ $ticket->id }}/assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        assigned_to: this.currentAssignedTo || null
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('Error al actualizar la asignación', 'error');
                }
            } catch (error) {
                this.showNotification('Error al actualizar la asignación', 'error');
            }
        },
        
        async updatePriority() {
            try {
                const response = await fetch(`/superlinkiu/tickets/{{ $ticket->id }}/priority`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        priority: this.currentPriority
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('Error al actualizar la prioridad', 'error');
                }
            } catch (error) {
                this.showNotification('Error al actualizar la prioridad', 'error');
            }
        },
        
        showNotification(message, type) {
            // Implementar notificación toast aquí
            alert(message);
        }
    }
}
</script>
@endsection 