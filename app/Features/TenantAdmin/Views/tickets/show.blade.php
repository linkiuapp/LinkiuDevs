@extends('shared::layouts.tenant-admin')

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
            @if(in_array($ticket->status, ['open', 'in_progress']))
                <button x-on:click="showStatusModal = true" 
                        class="btn-outline-warning px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-settings-outline class="w-4 h-4" />
                    Cambiar Estado
                </button>
            @endif
            <a href="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
               class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
        </div>
    </div>

    <!-- Información del Ticket -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden mb-6">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-lg font-semibold text-black-400 mb-0">Información del Ticket</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-black-300 mb-1">Estado</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->status_color == 'info' ? 'bg-info-300 text-accent-50' : ($ticket->status_color == 'warning' ? 'bg-warning-300 text-black-500' : ($ticket->status_color == 'success' ? 'bg-success-300 text-accent-50' : 'bg-secondary-300 text-accent-50')) }}">
                        {{ $ticket->status_label }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-black-300 mb-1">Prioridad</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->priority_color == 'success' ? 'bg-success-300 text-accent-50' : ($ticket->priority_color == 'info' ? 'bg-info-300 text-accent-50' : ($ticket->priority_color == 'warning' ? 'bg-warning-300 text-black-500' : 'bg-error-300 text-accent-50')) }}">
                        {{ $ticket->priority_label }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-black-300 mb-1">Categoría</label>
                    <span class="text-black-400">{{ $ticket->category_label }}</span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-accent-100">
                <label class="block text-sm font-medium text-black-300 mb-2">Descripción Original</label>
                <p class="text-black-400">{{ $ticket->description }}</p>
                
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

            @if($ticket->metadata)
                <div class="mt-4 pt-4 border-t border-accent-100">
                    <label class="block text-sm font-medium text-black-300 mb-2">Información Técnica</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        @if(isset($ticket->metadata['browser']))
                            <div>
                                <span class="text-black-300">Navegador:</span>
                                <span class="text-black-400">{{ $ticket->metadata['browser'] }}</span>
                            </div>
                        @endif
                        @if(isset($ticket->metadata['plan']))
                            <div>
                                <span class="text-black-300">Plan:</span>
                                <span class="text-black-400">{{ $ticket->metadata['plan'] }}</span>
                            </div>
                        @endif
                        @if(isset($ticket->metadata['created_by']))
                            <div>
                                <span class="text-black-300">Creado por:</span>
                                <span class="text-black-400">{{ $ticket->metadata['created_by'] }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="text-black-300">Fecha:</span>
                            <span class="text-black-400">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Conversación -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden mb-6" x-data="{ showAllMessages: {{ $ticket->responses->count() <= 3 ? 'true' : 'false' }} }">
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

                            <!-- Archivos adjuntos -->
                            @if($response->attachments && count($response->attachments) > 0)
                                <div class="mt-3 pt-3 border-t border-accent-200">
                                    <label class="block text-xs font-medium text-black-300 mb-2">Archivos adjuntos:</label>
                                    <div class="space-y-1">
                                        @foreach($response->attachments as $attachment)
                                            <div class="flex items-center gap-2 text-sm">
                                                <x-solar-document-outline class="w-4 h-4 text-black-300" />
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

    <!-- Formulario de Respuesta -->
    @if(in_array($ticket->status, ['open', 'in_progress']))
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden" id="response">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Agregar Respuesta</h2>
            </div>
            
            <form action="{{ route('tenant.admin.tickets.add-response', ['store' => $store->slug, 'ticket' => $ticket]) }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf
                
                <div class="p-6">
                    <div>
                        <label class="block text-sm font-medium text-black-300 mb-2">Tu respuesta</label>
                        <textarea name="message" 
                                  rows="4"
                                  placeholder="Escribe tu respuesta..."
                                  class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 @error('message') border-error-300 @enderror"
                                  required
                                  maxlength="2000">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-black-200 text-xs mt-1">Máximo 2000 caracteres</p>
                    </div>

                    <!-- Archivos adjuntos -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-black-300 mb-2">Archivos adjuntos (opcional)</label>
                        <input type="file" 
                               name="attachments[]" 
                               multiple
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200">
                        <p class="text-black-200 text-xs mt-1">Máximo 3 archivos, 5MB cada uno</p>
                        @error('attachments.*')
                            <p class="text-error-300 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-accent-100">
                        <button type="submit" 
                                class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                            <x-solar-plain-2-outline class="w-4 h-4" />
                            Enviar Respuesta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="bg-accent-50 rounded-lg p-6 text-center">
            <div class="flex flex-col items-center gap-2">
                <x-solar-lock-outline class="w-12 h-12 text-black-200" />
                <p class="text-black-300">Este ticket está {{ $ticket->status_label }} y no acepta más respuestas</p>
            </div>
        </div>
    @endif

    <!-- Modal para cambiar estado -->
    <div x-show="showStatusModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         style="display: none;">
        
        <div x-on:click.away="showStatusModal = false" 
             class="bg-accent-50 rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-black-400 mb-4">Cambiar Estado del Ticket</h3>
            
            <div class="space-y-3">
                <button x-on:click="updateStatus('resolved')" 
                        class="w-full btn-success px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-check-circle-outline class="w-4 h-4" />
                    Marcar como Resuelto
                </button>
                
                <button x-on:click="updateStatus('closed')" 
                        class="w-full btn-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-lock-outline class="w-4 h-4" />
                    Cerrar Ticket
                </button>
            </div>
            
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-accent-100">
                <button x-on:click="showStatusModal = false" 
                        class="btn-outline-secondary px-4 py-2 rounded-lg">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function ticketDetail() {
    return {
        showStatusModal: false,
        
        async updateStatus(status) {
            try {
                const response = await fetch(`{{ route('tenant.admin.tickets.update-status', ['store' => $store->slug, 'ticket' => $ticket]) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: status
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.showStatusModal = false;
                    window.location.reload();
                } else {
                    alert('Error al actualizar el estado');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar el estado');
            }
        }
    }
}
</script>
@endsection 