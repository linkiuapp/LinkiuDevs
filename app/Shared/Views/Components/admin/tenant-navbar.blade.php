<div class="main-wrapper">
    <nav class="navbar">
        <div class="py-4 px-2">
            <div class="flex items-center justify-between">
                <div class="inline-block items-center justify-start">
                    <span class="user-name-navbar">
                       Hola, {{ auth()->user()->name }}! Bienvenido a {{ $store->name }}
                    </span>
                    <div class="breadcrumb">
                        <ul class="flex items-center gap-[2px]">
                            <li>
                                <a href="{{ route('tenant.admin.dashboard', ['store' => $store->slug]) }}" class="flex items-center gap-2 hover:text-primary-600 dark:text-accent-50">
                                    <x-lucide-layout-dashboard class="w-3 h-3" />
                                    Dashboard
                                </a>
                            </li>
                            <li class="text-black-300"> > </li>
                            <li class="text-black-300">@yield('title')</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">

                <!-- Badge Verificado -->
                     <div class="flex items-center gap-2">
                        <div id="verification-badge" class="flex items-center gap-2 px-3 py-2 rounded-full {{ $store->verified ? 'bg-info-50 border border-info-300' : 'bg-secondary-50 border border-secondary-100' }}">
                            <span id="verification-text" class="text-caption font-regular {{ $store->verified ? 'text-info-300' : 'text-secondary-100' }}">
                                {{ $store->verified ? 'Verificado' : 'No Verificado' }}
                            </span>
                            @if($store->verified)
                                <div id="verification-indicator">
                                    <x-lucide-badge-check class="w-4 h-4 text-info-300" />
                                </div>
                            @else
                                <div id="verification-indicator">
                                    <x-lucide-shield-off class="w-4 h-4 text-secondary-100" />
                                </div>
                            @endif
                        </div>
                     </div>

                    <!-- Badge Store Status -->
                    <div class="flex items-center gap-2">
                        @php
                            // Determinar clases y textos según el estado
                            $status = $store->status;
                            switch ($status) {
                                case 'active':
                                default:
                                    $badgeClasses = 'bg-success-50 border border-success-500';
                                    $textClasses = 'text-success-500';
                                    $label = 'Tienda Activa';
                                    $iconComponent = 'shield-check';
                                    $iconColor = 'text-success-500';
                                    break;
                                case 'inactive':
                                    $badgeClasses = 'bg-secondary-50 border border-secondary-100';
                                    $textClasses = 'text-secondary-100';
                                    $label = 'Tienda Inactiva';
                                    $iconComponent = 'shield-off';
                                    $iconColor = 'text-secondary-100';
                                    break;
                                case 'suspended':
                                    $badgeClasses = 'bg-warning-50 border border-warning-500';
                                    $textClasses = 'text-warning-500';
                                    $label = 'Tienda Suspendida';
                                    $iconComponent = 'shield-off';
                                    $iconColor = 'text-warning-500';
                                    break;
                            }
                        @endphp
                        <div class="flex items-center gap-2 px-3 py-2 rounded-full {{ $badgeClasses }}">
                            <span class="text-caption font-regular {{ $textClasses }}">
                                {{ $label }}
                            </span>
                            <div>
                                @if($iconComponent === 'shield-check')
                                    <x-lucide-shield-check class="w-4 h-4 {{ $iconColor }}" />
                                @elseif($iconComponent === 'shield-off')
                                    <x-lucide-shield-off class="w-4 h-4 {{ $iconColor }}" />
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Ver tienda -->
                    <div class="flex items-center gap-2">
                        <a href="https://linkiu.bio/{{ $store->slug }}" target="_blank" 
                            class="flex items-center gap-2 px-4 py-2 text-caption font-regular text-accent-300 bg-primary-300 rounded-full hover:bg-primary-500 transition-colors">
                            Ver mi tienda
                            <x-lucide-external-link class="w-4 h-4" />
                        </a>
                    </div>

                    <!-- Notifications -->
                    <div class="flex items-center gap-4">
                        <!-- Pending Orders -->
                        <a href="{{ route('tenant.admin.orders.index', $store->slug) }}" 
                           class="pt-2 items-center text-black-300 hover:text-accent-300">
                            <span class="sr-only">Pedidos pendientes</span>
                            <div class="relative">
                                <x-lucide-party-popper class="w-10 h-10 bg-secondary-50 hover:bg-secondary-300 p-2 rounded-lg transition-colors" />
                                <div class="absolute inline-flex items-center justify-center w-5 h-5 text-small font-medium text-accent-300 bg-error-300 border-2 border-accent-50 rounded-full -top-2 -end-2 dark:border-gray-900">
                                    {{ $store->pending_orders_count ?? 0 }}
                                </div>
                            </div>
                        </a>

                        <!-- Support Tickets -->
                        <a href="{{ route('tenant.admin.tickets.index', $store->slug) }}" 
                           class="pt-2 items-center text-black-300 hover:text-accent-300">
                            <span class="sr-only">Tickets de soporte</span>
                            <div class="relative">
                                <x-lucide-message-square-more class="w-10 h-10 bg-secondary-50 hover:bg-secondary-300 p-2 rounded-lg transition-colors" />
                                <div class="absolute inline-flex items-center justify-center w-5 h-5 text-small font-medium text-accent-300 bg-info-300 border-2 border-accent-50 rounded-full -top-2 -end-2 dark:border-gray-900">
                                    {{ $store->open_tickets_count ?? 0 }}
                                </div>
                            </div>
                        </a>

                        <!-- Announcements -->
                        <a href="{{ route('tenant.admin.announcements.index', $store->slug) }}" 
                           class="pt-2 items-center text-black-300 hover:text-accent-300">
                            <span class="sr-only">Anuncios sin leer</span>
                            <div class="relative">
                                <x-lucide-megaphone class="w-10 h-10 bg-secondary-50 hover:bg-secondary-300 p-2 rounded-lg transition-colors" />
                                <div class="absolute inline-flex items-center justify-center w-5 h-5 text-small font-medium text-accent-300 bg-warning-300 border-2 border-accent-50 rounded-full -top-2 -end-2 dark:border-gray-900">
                                    {{ $store->unread_announcements_count ?? 0 }}
                                </div>
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
    // Función para actualizar badge de verificación
    function updateVerificationBadge(verified) {
        const badge = document.getElementById('verification-badge');
        const indicator = document.getElementById('verification-indicator'); 
        const text = document.getElementById('verification-text');
        
        if (badge && indicator && text) {
            // Remover todas las clases y aplicar las correctas
            badge.removeAttribute('class');
            indicator.removeAttribute('class');
            text.removeAttribute('class');
            
            if (verified) {
                badge.setAttribute('class', 'flex items-center gap-2 px-3 py-2 rounded-full bg-info-50 border border-info-300');
                
                /*indicator.setAttribute('class', 'w-4 h-4 bg-info-300');*/
                
                text.setAttribute('class', 'text-caption font-regular text-info-300');
            } else {
                badge.setAttribute('class', 'flex items-center gap-2 px-3 py-2 rounded-full bg-secondary-50 border border-secondary-100');
                
                /*indicator.setAttribute('class', 'w-4 h-4 bg-secondary-100');*/
                
                text.setAttribute('class', 'text-caption font-regular text-secondary-100');
            }
            
            text.textContent = verified ? 'Verificado' : 'No Verificado';
        }
    }
    
    // Función para verificar estado cada 30 segundos
    function checkVerificationStatus() {
        const storeSlug = window.location.pathname.split('/')[1];
        
        fetch(`/api/store/${storeSlug}/status`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.verified !== undefined) {
                updateVerificationBadge(data.verified);
            }
        })
        .catch(() => {
            // Silenciar errores de red
        });
    }
    
    // Verificar estado cada 30 segundos
    setInterval(checkVerificationStatus, 30000);
    
    // Verificar inmediatamente al cargar
    checkVerificationStatus();
});

</script> 