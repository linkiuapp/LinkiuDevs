<div class="main-wrapper">
    <nav class="navbar">
        <div class="py-4 px-2">
            <div class="flex items-center justify-between">
                <div class="inline-block items-center justify-start">
                    <span class="user-name-navbar">
                       Hola, {{ auth()->user()->name }} - Bienvenido a {{ $store->name }}
                    </span>
                    <div class="breadcrumb">
                        <ul class="flex items-center gap-[2px]">
                            <li>
                                <a href="{{ route('tenant.admin.dashboard', ['store' => $store->slug]) }}" class="flex items-center gap-2 hover:text-primary-600 dark:text-white-50">
                                    <x-solar-widget-2-outline class="w-3 h-3" />
                                    Dashboard
                                </a>
                            </li>
                            <li class="dark:text-white-50"> > </li>
                            <li class="font-medium dark:text-white-50">@yield('title')</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <!-- Store Status -->
                    <div class="hidden md:flex items-center">
                        <div class="flex items-center gap-2 px-3 py-1 bg-{{ $store->status === 'active' ? 'success' : 'warning' }}-100 rounded-full">
                            <div class="w-2 h-2 bg-{{ $store->status === 'active' ? 'success' : 'warning' }}-300 rounded-full"></div>
                            <span class="text-xs font-medium text-{{ $store->status === 'active' ? 'success' : 'warning' }}-400">
                                {{ $store->status === 'active' ? 'Tienda Activa' : 'Tienda Inactiva' }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Badge Verificado -->
                     <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 px-3 py-1 bg-{{ $store->verified ? 'success' : 'warning' }}-100 rounded-full">
                            <div class="w-2 h-2 bg-{{ $store->verified ? 'success' : 'warning' }}-300 rounded-full"></div>
                            <span class="text-xs font-medium text-{{ $store->verified ? 'success' : 'warning' }}-400">
                                {{ $store->verified ? 'Verificado' : 'No Verificado' }}
                            </span>
                        </div>
                     </div>

                     <!-- Ver tienda -->
                     <div class="flex items-center gap-2">
                     <a href="#" 
                        class="flex items-center gap-2 px-3 py-1 text-xs font-medium text-primary-300 bg-primary-100 rounded-full hover:bg-primary-200 transition-colors">
                        <x-solar-eye-outline class="w-3 h-3" />
                        Ver Tienda
                     </a>

                     <a href="#" 
                        class="flex items-center gap-2 px-3 py-1 text-xs font-medium text-secondary-300 bg-secondary-100 rounded-full hover:bg-secondary-200 transition-colors">
                        <x-solar-add-circle-outline class="w-3 h-3" />
                        Crear Producto
                    </a>

                    </div>
                


                    <!-- Search mobile -->
                    <button type="button" class="p-2 text-gray-500 rounded-lg lg:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white-50">
                        <x-solar-magnifer-outline class="w-6 h-6" />
                    </button>

                    <!-- Notifications -->
                    <div class="flex items-center gap-4">
                        <!-- Pending Orders -->
                        @if(($store->pending_orders_count ?? 0) > 0)
                            <a href="{{ route('tenant.admin.orders.index', $store->slug) }}" 
                               class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700">
                                <span class="sr-only">Pedidos pendientes</span>
                                <div class="relative">
                                    <x-solar-clipboard-list-outline class="w-6 h-6" />
                                    <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white-50 bg-warning-300 border-2 border-white-50 rounded-full -top-2 -end-2 dark:border-gray-900">
                                        {{ $store->pending_orders_count }}
                                    </div>
                                </div>
                            </a>
                        @endif

                        <!-- Support Tickets -->
                        @if(($store->open_tickets_count ?? 0) > 0)
                            <a href="{{ route('tenant.admin.support.index', $store->slug) }}" 
                               class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700">
                                <span class="sr-only">Tickets de soporte</span>
                                <div class="relative">
                                    <x-solar-chat-round-call-outline class="w-6 h-6" />
                                    <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white-50 bg-info-300 border-2 border-white-50 rounded-full -top-2 -end-2 dark:border-gray-900">
                                        {{ $store->open_tickets_count }}
                                    </div>
                                </div>
                            </a>
                        @endif

                        <!-- Support Messages (New responses from SuperLinkiu) -->
                        <a href="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
                           class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700"
                           data-support-messages-link>
                            <span class="sr-only">Mensajes del soporte</span>
                            <div class="relative">
                                <x-solar-chat-round-dots-outline class="w-6 h-6" data-badge="support-messages" />
                                <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white-50 bg-primary-300 border-2 border-white-50 rounded-full -top-2 -end-2 dark:border-gray-900" id="support-messages-badge">
                                    {{ max($store->unread_support_responses_count, 0) }}
                                </div>
                            </div>
                        </a>

                        <!-- Announcements -->
                        @if(($store->unread_announcements_count ?? 0) > 0)
                            <a href="{{ route('tenant.admin.announcements.index', $store->slug) }}" 
                               class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700">
                                <span class="sr-only">Anuncios sin leer</span>
                                <div class="relative">
                                    <x-solar-siren-rounded-outline class="w-6 h-6" />
                                    <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white-50 bg-error-300 border-2 border-white-50 rounded-full -top-2 -end-2 dark:border-gray-900">
                                        {{ $store->unread_announcements_count }}
                                    </div>
                                </div>
                            </a>
                        @endif

                        <!-- Profile Settings -->
                        <a href="#" 
                           class="pt-2 items-center text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white-50 dark:hover:bg-gray-700">
                            <span class="sr-only">Configurar perfil</span>
                            <div class="relative">
                                <x-solar-settings-outline class="w-6 h-6" />
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para actualizar los badges de TenantAdmin
    function updateNotificationBadges(data) {
        
        // Actualizar badge de mensajes nuevos del soporte (siempre visible)
        const messageBadge = document.getElementById('support-messages-badge');
        if (messageBadge) {
            messageBadge.textContent = data.new_messages || 0;
        }
    }
    
    // Configurar WebSocket para TenantAdmin
    function setupWebSocket() {
        const storeSlug = window.location.pathname.split('/')[1];
        console.log(`🔌 Setting up WebSocket for TenantAdmin (${storeSlug})...`);
        
        if (window.Echo && storeSlug) {
            console.log(`✅ Echo available, subscribing to store.${storeSlug}.notifications`);
            
            // Escuchar en el canal específico de la tienda
            window.Echo.channel(`store.${storeSlug}.notifications`)
                .listen('.ticket.response.added', (e) => {
                    console.log('🔔 NEW SUPPORT RESPONSE RECEIVED IN TENANT:', e);
                    
                    // Actualizar contadores inmediatamente
                    refreshNotificationCounts();
                    
                    // Mostrar notificación destacada
                    if (e.response_from === 'super_admin') {
                        console.log(`🎯 Super admin responded to ticket ${e.ticket_number}`);
                        showToast(`Respuesta del soporte en ticket ${e.ticket_number}`, e.message_preview);
                    }
                });
                
            console.log(`✅ TenantAdmin WebSocket listeners configured for ${storeSlug}`);
        } else {
            console.error('❌ Echo not available or storeSlug missing for TenantAdmin');
            console.log('🔍 Debug - window.Echo:', window.Echo);
            console.log('🔍 Debug - window.pusher:', window.pusher);
            console.log('🔍 Debug - storeSlug:', storeSlug);
            
            // Fallback: Usar Pusher directamente si está disponible
            if (window.pusher && storeSlug) {
                console.log('🔧 Using direct Pusher connection for tenant');
                const channel = window.pusher.subscribe(`store.${storeSlug}.notifications`);
                channel.bind('ticket.response.added', function(e) {
                    console.log('🔔 NEW TENANT RESPONSE (via Pusher):', e);
                    refreshNotificationCounts();
                });
            }
        }
    }
    
    // Función para refrescar contadores via API (solo cuando sea necesario)
    function refreshNotificationCounts() {
        const storeSlug = window.location.pathname.split('/')[1];
        
        console.log('Attempting to fetch notifications for store:', storeSlug);
        
        fetch(`/api/tenant/${storeSlug}/notifications`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Updating tenant badges with:', data);
            updateNotificationBadges(data);
        })
        .catch(error => {
            console.log('Error refreshing tenant notifications:', error);
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