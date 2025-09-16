<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $store->name }}</title>
    
    <!-- Favicon -->
    @php
        // Función para obtener el favicon más reciente del SuperAdmin
        $getLatestAppFavicon = function() {
            try {
                // Primero verificar sesión temporal
                $tempFavicon = session('temp_app_favicon');
                if ($tempFavicon) {
                    return $tempFavicon;
                }
                
                // Luego verificar variable de entorno
                $envFavicon = env('APP_FAVICON');
                if ($envFavicon) {
                    return $envFavicon;
                }
                
                // Temporalmente desactivado para evitar problemas con fileinfo
                // TODO: Reactivar cuando se solucione el problema de finfo en producción
                /*
                // Finalmente, buscar el favicon más reciente en S3
                if (config('filesystems.disks.s3.bucket')) {
                    try {
                        $files = Storage::disk('public')->files('system');
                        $faviconFiles = array_filter($files, function($file) {
                            return str_contains(basename($file), 'favicon_');
                        });
                    } catch (\Exception $e) {
                        Log::error('Error accessing storage files: ' . $e->getMessage());
                        $faviconFiles = [];
                    }
                    
                    if (!empty($faviconFiles)) {
                        // Ordenar por fecha en el nombre del archivo (más reciente primero)
                        usort($faviconFiles, function($a, $b) {
                            $timeA = preg_match('/favicon_(\d+)\./', basename($a), $matchesA) ? (int)$matchesA[1] : 0;
                            $timeB = preg_match('/favicon_(\d+)\./', basename($b), $matchesB) ? (int)$matchesB[1] : 0;
                            return $timeB - $timeA; // Más reciente primero
                        });
                        
                        return $faviconFiles[0]; // Retornar el más reciente
                    }
                }
                */
                
                return null;
            } catch (\Exception $e) {
                Log::error('Error searching for app favicon: ' . $e->getMessage());
                return null;
            }
        };
        
        $appFavicon = $getLatestAppFavicon();
        $faviconSrc = asset('favicon.ico'); // Default fallback
        
        if ($appFavicon) {
            try {
                // Temporalmente simplificado para evitar problemas con fileinfo
                $faviconSrc = asset('storage/' . $appFavicon);
                /*
                if (config('filesystems.disks.s3.bucket')) {
                    $faviconSrc = \Storage::disk('public')->url($appFavicon);
                } else {
                    $faviconSrc = asset('storage/' . $appFavicon);
                }
                */
            } catch (\Exception $e) {
                Log::error('Error generating favicon URL: ' . $e->getMessage());
                $faviconSrc = asset('storage/' . $appFavicon);
            }
        }
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $store->design && $store->design->is_published && $store->design->favicon_url ? $store->design->favicon_url : $faviconSrc }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Additional Head Content -->
    @stack('styles')
    
    <!-- 🔔 SISTEMA DE NOTIFICACIONES DE ESCRITORIO - RECREADO -->
    <script>
        // 📱 Variables globales para notificaciones
        let lastOrderCount = 0;
        let notificationInterval = null;
        const storeSlug = '{{ $store->slug ?? "" }}';
        
        // 🚀 Inicializar sistema de notificaciones
        function initDesktopNotifications() {
            console.log('🔔 Inicializando notificaciones de escritorio...');
            
            // Solicitar permisos inmediatamente
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    console.log('📱 Permiso de notificaciones:', permission);
                    if (permission === 'granted') {
                        showWelcomeNotification();
                        startOrderPolling();
                    }
                });
            } else if (Notification.permission === 'granted') {
                console.log('✅ Permisos ya concedidos');
                showWelcomeNotification();
                startOrderPolling();
            } else {
                console.log('❌ Permisos denegados');
            }
        }
        
        // 👋 Notificación de bienvenida
        function showWelcomeNotification() {
            const notification = new Notification('🔔 Sistema de pedidos activado', {
                body: 'Te notificaremos cuando lleguen nuevos pedidos',
                icon: '/favicon.ico',
                tag: 'welcome',
                silent: true
            });
            
            setTimeout(() => notification.close(), 3000);
        }
        
        // ⏰ Iniciar polling cada 15 segundos (AGRESIVO)
        function startOrderPolling() {
            console.log('⏰ Iniciando polling de pedidos...');
            
            // Cargar conteo inicial
            loadInitialCount();
            
            // Verificar cada 15 segundos - MÁS AGRESIVO
            notificationInterval = setInterval(() => {
                checkForNewOrders();
            }, 15000);
            
            // También verificar cuando la pestaña vuelve a tener focus
            window.addEventListener('focus', () => {
                console.log('👁️ Pestaña enfocada - verificando pedidos...');
                checkForNewOrders();
            });
            
            // Verificar cuando la pestaña se vuelve visible
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    console.log('👁️ Pestaña visible - verificando pedidos...');
                    checkForNewOrders();
                }
            });
        }
        
        // 📊 Cargar conteo inicial
        async function loadInitialCount() {
            if (!storeSlug) return;
            
            try {
                const response = await fetch(`/${storeSlug}/admin/orders/api/count`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        lastOrderCount = data.count;
                        console.log('📊 Conteo inicial de pedidos:', lastOrderCount);
                    }
                }
            } catch (error) {
                console.error('❌ Error cargando conteo inicial:', error);
            }
        }
        
        // 🔍 Verificar nuevos pedidos
        async function checkForNewOrders() {
            if (!storeSlug) return;
            
            const timestamp = new Date().toLocaleTimeString();
            console.log(`🔍 ${timestamp} - Verificando pedidos... (Tab visible: ${!document.hidden})`);
            
            try {
                const response = await fetch(`/${storeSlug}/admin/orders/api/count`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        const currentCount = data.count;
                        console.log(`📊 ${timestamp} - Conteo actual: ${currentCount}, Anterior: ${lastOrderCount}`);
                        
                        // Si hay nuevos pedidos
                        if (currentCount > lastOrderCount && lastOrderCount > 0) {
                            const newOrdersCount = currentCount - lastOrderCount;
                            console.log(`🚨 ${timestamp} - ¡${newOrdersCount} NUEVOS PEDIDOS DETECTADOS!`);
                            showNewOrderNotification(newOrdersCount, data.latest_order);
                        }
                        
                        lastOrderCount = currentCount;
                    }
                }
            } catch (error) {
                console.error(`❌ ${timestamp} - Error verificando pedidos:`, error);
            }
        }
        
        // 🔔 Mostrar notificación de nuevo pedido
        function showNewOrderNotification(count, latestOrder) {
            if (Notification.permission !== 'granted') return;
            
            const title = count === 1 ? '🔔 ¡Nuevo pedido!' : `🔔 ¡${count} nuevos pedidos!`;
            let body = 'Revisa el panel de pedidos';
            
            if (latestOrder) {
                const total = new Intl.NumberFormat('es-CO').format(latestOrder.total);
                body = `Pedido #${latestOrder.order_number}\n💰 $${total}\n👤 ${latestOrder.customer_name}\n\n👆 Click para ver detalles`;
            }
            
            const notification = new Notification(title, {
                body: body,
                icon: '/favicon.ico',
                tag: 'new-order',
                requireInteraction: true,
                silent: false,
                renotify: true,
                vibrate: [200, 100, 200]
            });
            
            // 🎵 Sonido de notificación
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmIePjuVvDY=');
                audio.volume = 0.5;
                audio.play().catch(() => {});
            } catch (e) {
                console.log('🔇 Sin sonido disponible');
            }
            
            // 👆 Click para ir al pedido
            notification.onclick = () => {
                window.focus();
                notification.close();
                
                if (latestOrder && latestOrder.id) {
                    window.location.href = `/${storeSlug}/admin/orders/${latestOrder.id}`;
                } else {
                    window.location.href = `/${storeSlug}/admin/orders`;
                }
            };
        }
        
        // 🚀 Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            initDesktopNotifications();
        });
        
        // 🧹 Limpiar al salir
        window.addEventListener('beforeunload', () => {
            if (notificationInterval) {
                clearInterval(notificationInterval);
            }
        });
    </script>
</head>
<body class="bg-secondary-50 font-body">
    <!-- Sidebar del Admin de Tienda -->
    @include('shared::admin.tenant-sidebar', ['store' => $store])
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar del Admin de Tienda -->
        @include('shared::admin.tenant-navbar', ['store' => $store])
        
        <!-- Page Content -->
        <main class="main-content-inner">
            <!-- Page Header -->
            @hasSection('header')
                <div class="page-header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="heading-2 text-black-500 mb-1">@yield('title')</h1>
                            @hasSection('subtitle')
                                <p class="body-base text-black-300">@yield('subtitle')</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            @yield('actions')
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center gap-3">
                        <x-solar-check-circle-outline class="w-5 h-5 text-success-300" />
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center gap-3">
                        <x-solar-close-circle-outline class="w-5 h-5 text-error-300" />
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="ml-auto">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error mb-6" x-data="{ show: true }" x-show="show">
                    <div class="flex items-start gap-3">
                        <x-solar-close-circle-outline class="w-5 h-5 text-error-300 mt-0.5" />
                        <div class="flex-1">
                            <p class="font-medium mb-2">Por favor corrige los siguientes errores:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="body-small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="ml-auto">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @endif
            
            <!-- Main Content -->
            <div class="content-area">
                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        @include('shared::admin.footer')
    </div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black-500 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-accent-50 rounded-lg p-6 flex items-center gap-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-300"></div>
            <span class="body-base text-black-400">Cargando...</span>
        </div>
    </div>
    
    <!-- Scripts -->
    @stack('scripts')
    
    <!-- Store Data for JavaScript -->
    <script>
        window.store = {
            id: {{ $store->id }},
            name: '{{ $store->name }}',
            slug: '{{ $store->slug }}',
            status: '{{ $store->status }}',
            plan: {
                name: '{{ $store->plan->name ?? 'Basic' }}',
                limits: {
                    products: {{ $store->plan->max_products ?? 20 }},
                    categories: {{ $store->plan->max_categories ?? 3 }},
                    variables: {{ $store->plan->max_variables ?? 5 }},
                    coupons: {{ $store->plan->max_active_coupons ?? 1 }},
                    sliders: {{ $store->plan->max_slider ?? 1 }},
                    locations: {{ $store->plan->max_sedes ?? 1 }}
                }
            }
        };
    </script>
</body>
</html> 