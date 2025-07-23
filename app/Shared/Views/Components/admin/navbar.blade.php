<div class="main-wrapper">
    <nav class="navbar">
        <div class="py-3 px-2">
            <div class="flex items-center justify-between">
                <div class="inline-block items-center justify-start">
                    <span class="user-name-navbar">
                        {{auth()->user()->name}}
                    </span>
                    <div class="breadcrumb">
                        <ul class="flex items-center gap-[2px]">
                            <li>
                                <a href="{{route('superlinkiu.dashboard')}}" class="flex items-center gap-2 hover:text-primary-600 dark:text-white-50">
                                    <x-solar-widget-2-outline class="w-3 h-3" />
                                    Dashboard
                                </a>
                            </li>
                            <li class="dark:text-white-50"> > </li>
                            <li class="font-medium dark:text-white-50">@yield('title')</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center">
                    <!-- Search mobile -->
                    <button type="button" class="p-2 text-gray-500 rounded-lg lg:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white-50">
                        <iconify-icon icon="heroicons:magnifying-glass" class="w-6 h-6"></iconify-icon>
                    </button>

                    <!-- Notifications -->
                    <div class="flex items-center gap-4">
                        <a href="{{ route('superlinkiu.tickets.index') }}" class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700">
                            <span class="sr-only">Ver tickets abiertos</span>
                            <div class="relative">
                                <x-solar-ticker-star-outline class="w-6 h-6" data-badge="tickets" />
                                @php
                                    $openTicketsCount = \App\Shared\Models\Ticket::whereIn('status', ['open', 'in_progress'])->count();
                                @endphp
                                <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white-50 bg-red-500 border-2 border-white-50 rounded-full -top-2 -end-2 dark:border-gray-900" id="tickets-badge">
                                    {{ max($openTicketsCount, 0) }}
                                </div>
                            </div>
                        </a>

                        <!-- Messages from Store Admins -->
                        @php
                            $newMessagesCount = \App\Shared\Models\TicketResponse::whereHas('user', function($query) {
                                    $query->where('role', 'store_admin');
                                })
                                ->where('created_at', '>=', now()->subDays(7))
                                ->count();
                        @endphp
                        <a href="{{ route('superlinkiu.tickets.index') }}" class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700" data-messages-link>
                            <span class="sr-only">Mensajes de tiendas</span>
                            <div class="relative">
                                <x-solar-chat-round-dots-outline class="w-6 h-6" data-badge="messages" />
                                <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white-50 bg-primary-300 border-2 border-white-50 rounded-full -top-2 -end-2 dark:border-gray-900" id="messages-badge">
                                    {{ max($newMessagesCount, 0) }}
                                </div>
                            </div>
                        </a>

                        <button type="button" class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700">
                            <span class="sr-only">Configurar perfil</span>
                            <div class="relative">
                                <x-solar-settings-outline class="w-6 h-6" />
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧪 TEST: JavaScript is working in SuperAdmin navbar');
    console.log('🧪 TEST: app.js should have run before this');
    // Función para actualizar los badges de SuperLinkiu
    function updateNotificationBadges(data) {
        
        // Actualizar badge de tickets abiertos (siempre visible)
        const ticketBadge = document.getElementById('tickets-badge');
        if (ticketBadge) {
            ticketBadge.textContent = data.open_tickets || 0;
        }
        
        // Actualizar badge de mensajes nuevos (siempre visible)
        const messageBadge = document.getElementById('messages-badge');
        if (messageBadge) {
            messageBadge.textContent = data.new_messages || 0;
        }
    }
    
    // Configurar WebSocket para SuperLinkiu
    function setupWebSocket() {
        console.log('🔌 Setting up WebSocket for SuperLinkiu...');
        console.log('🔍 Debug - window.Echo:', window.Echo);
        console.log('🔍 Debug - window.pusher:', window.pusher);
        console.log('🔍 Debug - Pusher:', window.Pusher);
        
        if (window.Echo) {
            console.log('✅ Echo available, subscribing to superlinkiu-notifications');
            
            // Escuchar en el canal de SuperLinkiu
            window.Echo.channel('superlinkiu-notifications')
                .listen('.ticket.response.added', (e) => {
                    console.log('🔔 NEW TICKET RESPONSE RECEIVED IN SUPERLINKIU:', e);
                    
                    // Actualizar contadores inmediatamente
                    refreshNotificationCounts();
                    
                    // Mostrar notificación destacada
                    if (e.response_from === 'store_admin') {
                        console.log(`🎯 Store admin responded to ticket ${e.ticket_number}`);
                        showToast(`Nueva respuesta en ticket ${e.ticket_number}`, e.message_preview);
                    }
                });
                
            console.log('✅ SuperLinkiu WebSocket listeners configured');
        } else {
            console.error('❌ Echo not available for SuperLinkiu');
            console.log('🔧 Trying to setup basic Pusher connection...');
            
            // Fallback: Usar Pusher directamente
            if (window.pusher) {
                console.log('✅ Using direct Pusher connection');
                const channel = window.pusher.subscribe('superlinkiu-notifications');
                channel.bind('ticket.response.added', function(e) {
                    console.log('🔔 NEW TICKET RESPONSE (via Pusher):', e);
                    refreshNotificationCounts();
                });
            }
        }
    }
    
    // Función para refrescar contadores via API (solo cuando sea necesario)
    function refreshNotificationCounts() {
        fetch('/api/superlinkiu/notifications', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            updateNotificationBadges(data);
        })
        .catch(error => {
            console.log('Error refreshing notifications:', error);
        });
    }
    
    // Función para mostrar toast (opcional)
    function showToast(title, message) {
        // Implementar notificación visual si se desea
        console.log(`🔔 ${title}: ${message}`);
    }
    
    // Inicializar WebSocket
    setupWebSocket();
    
    // Cargar contadores inicial
    refreshNotificationCounts();
});
</script>
