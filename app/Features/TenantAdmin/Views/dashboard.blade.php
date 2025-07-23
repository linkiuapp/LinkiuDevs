<x-tenant-admin-layout :store="$store">
    @section('title', 'Dashboard')
    @section('subtitle', 'Panel de administración de tu tienda')
    
    @section('actions')
        <a href="{{ route('tenant.home', $store->slug) }}" 
           target="_blank"
           class="btn-primary">
            <x-solar-global-outline class="w-4 h-4 mr-2" />
            Ver Tienda
        </a>
    @endsection
    
    @section('content')
        <!-- Bienvenida -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-primary-50 to-secondary-50 rounded-xl p-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-primary-200 rounded-full flex items-center justify-center">
                        <x-solar-user-circle-outline class="w-8 h-8 text-white-50" />
                    </div>
                    <div>
                        <h2 class="heading-3 text-black-400">
                            ¡Bienvenido, {{ $stats['admin_name'] }}!
                        </h2>
                        <p class="body-base text-black-300">
                            Este es el panel de administración de tu tienda {{ $store->name }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget de Pedidos Recientes -->
        @if($recentOrders->count() > 0)
        <div class="mb-8">
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg text-black-500 mb-0 font-semibold">Pedidos Recientes</h2>
                        <a href="{{ route('tenant.admin.orders.index', ['store' => $store->slug]) }}" 
                           class="text-primary-200 hover:text-primary-300 text-sm transition-colors">
                            Ver todos
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4" x-data="recentOrdersWidget">
                        @foreach($recentOrders as $order)
                        <div class="flex items-center gap-4 p-4 border border-white-100 rounded-lg hover:bg-white-100 transition-colors">
                            <!-- Imagen del primer producto -->
                            <div class="flex-shrink-0">
                                @if($order->items->first() && $order->items->first()->product && $order->items->first()->product->mainImage)
                                    <img src="{{ $order->items->first()->product->mainImage->image_url }}" 
                                         alt="{{ $order->items->first()->product_name }}"
                                         class="w-12 h-12 object-cover rounded-lg border border-white-200">
                                @else
                                    <div class="w-12 h-12 bg-white-200 rounded-lg flex items-center justify-center">
                                        <x-solar-bag-3-outline class="w-6 h-6 text-black-200" />
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Info del pedido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <a href="{{ route('tenant.admin.orders.show', ['store' => $store->slug, 'order' => $order->id]) }}" class="text-sm font-semibold text-black-500 hover:text-primary-300 transition-colors hover:underline">#{{ $order->order_number }}</a>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $order->status_color_class }}">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-black-300 truncate">
                                    {{ $order->customer_name }} • {{ $order->items_count }} producto(s)
                                </p>
                                <p class="text-xs text-black-200">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            
                            <!-- Total -->
                            <div class="text-right">
                                <div class="text-sm font-semibold text-black-500">
                                    ${{ number_format($order->total, 0, ',', '.') }}
                                </div>
                            </div>
                            
                            <!-- Select de estado -->
                            <div class="flex-shrink-0">
                                <select class="text-xs px-2 py-1 border border-white-200 rounded focus:ring-1 focus:ring-primary-200 focus:border-primary-300 transition-colors"
                                        @change="updateOrderStatus({{ $order->id }}, $event.target.value, $event.target)"
                                        :disabled="updating">
                                    @foreach(\App\Shared\Models\Order::STATUSES as $status => $label)
                                        <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Carrusel de Banners de Anuncios -->
        <div class="mb-8" x-data="announcementBanners" x-init="loadBanners()" x-show="banners.length > 0">
            <div class="relative overflow-hidden rounded-xl shadow-sm mx-auto w-full">
                <!-- Contenedor del carrusel -->
                <div class="relative w-full h-[300px]">
                    <template x-for="(banner, index) in banners" :key="banner.id">
                        <div x-show="currentSlide === index"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform translate-x-full"
                             x-transition:enter-end="opacity-100 transform translate-x-0"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 transform translate-x-0"
                             x-transition:leave-end="opacity-0 transform -translate-x-full"
                             class="absolute inset-0 w-full h-full">
                            <!-- Banner clickeable - Solo imagen -->
                            <a :href="banner.banner_link || banner.show_url" 
                               class="block w-full h-full"
                               :target="banner.banner_link ? '_blank' : '_self'">
                                <img :src="banner.banner_image_url" 
                                     :alt="banner.title"
                                     class="w-full h-full object-cover rounded-xl">
                            </a>
                        </div>
                    </template>
                </div>

                <!-- Controles del carrusel -->
                <div x-show="banners.length > 1" class="absolute inset-0 flex items-center justify-between pointer-events-none">
                    <!-- Botón anterior -->
                    <button @click="previousSlide()" 
                            class="ml-4 w-8 h-8 bg-black bg-opacity-40 hover:bg-opacity-60 rounded-full flex items-center justify-center text-white-50 transition-all duration-200 pointer-events-auto">
                        <x-solar-arrow-left-outline class="w-4 h-4" />
                    </button>
                    
                    <!-- Botón siguiente -->
                    <button @click="nextSlide()" 
                            class="mr-4 w-8 h-8 bg-black bg-opacity-40 hover:bg-opacity-60 rounded-full flex items-center justify-center text-white-50 transition-all duration-200 pointer-events-auto">
                        <x-solar-arrow-right-outline class="w-4 h-4" />
                    </button>
                </div>

                <!-- Indicadores -->
                <div x-show="banners.length > 1" class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex gap-2">
                    <template x-for="(banner, index) in banners" :key="'dot-' + banner.id">
                        <button @click="goToSlide(index)"
                                class="w-2 h-2 rounded-full transition-all duration-200"
                                :class="currentSlide === index ? 'bg-white-50' : 'bg-white-50 bg-opacity-50'">
                        </button>
                    </template>
                </div>
            </div>
        </div>
        
        <!-- Información de la tienda -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Estado de la tienda -->
            <div class="bg-white-50 rounded-lg p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-info-100 rounded-lg flex items-center justify-center">
                        <x-solar-shop-outline class="w-5 h-5 text-info-300" />
                    </div>
                    <h3 class="text-lg font-semibold text-black-400">Estado de la Tienda</h3>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="body-base text-black-300">Estado:</span>
                        @if($stats['store_status'] === 'active')
                            <span class="bg-success-200 text-white-50 px-2 py-1 rounded text-xs font-medium">
                                Activa
                            </span>
                        @elseif($stats['store_status'] === 'inactive')
                            <span class="bg-warning-200 text-black-400 px-2 py-1 rounded text-xs font-medium">
                                Inactiva
                            </span>
                        @else
                            <span class="bg-error-200 text-white-50 px-2 py-1 rounded text-xs font-medium">
                                Suspendida
                            </span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="body-base text-black-300">Plan:</span>
                        <span class="bg-primary-200 text-white-50 px-2 py-1 rounded text-xs font-medium">
                            {{ $stats['plan_name'] }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="body-base text-black-300">Estado de verificación:</span>
                        <span class="bg-success-200 text-black-300 px-2 py-1 rounded text-xs font-medium">
                            Verificada
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Enlaces rápidos -->
            <div class="bg-white-50 rounded-lg p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-secondary-100 rounded-lg flex items-center justify-center">
                        <x-solar-link-outline class="w-5 h-5 text-secondary-300" />
                    </div>
                    <h3 class="text-lg font-semibold text-black-400">Enlaces Rápidos</h3>
                </div>
                <div class="space-y-3">
                    <a href="{{ route('tenant.home', $store->slug) }}" 
                       target="_blank"
                       class="flex items-center gap-2 text-primary-300 hover:text-primary-200 transition-colors">
                        <x-solar-global-outline class="w-4 h-4" />
                        Ver tienda
                    </a>
                    <a href="{{ route('superlinkiu.stores.show', $store->id) }}" 
                       target="_blank"
                       class="flex items-center gap-2 text-info-300 hover:text-info-200 transition-colors">
                        <x-solar-settings-outline class="w-4 h-4" />
                        Configuración avanzada
                    </a>
                </div>
            </div>
            
            <!-- Próximas funcionalidades -->
            <div class="bg-white-50 rounded-lg p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-success-100 rounded-lg flex items-center justify-center">
                        <x-solar-rocket-outline class="w-5 h-5 text-success-300" />
                    </div>
                    <h3 class="text-lg font-semibold text-black-400">Próximamente</h3>
                </div>
                <div class="space-y-2 body-small text-black-300">
                    <p>• Gestión de productos</p>
                    <p>• Administración de pedidos</p>
                    <p>• Base de datos de clientes</p>
                    <p>• Reportes y estadísticas</p>
                </div>
            </div>
        </div>
        
        <!-- Mensaje informativo -->
        <div class="bg-info-50 border border-info-100 rounded-lg p-6">
            <div class="flex items-start gap-3">
                <x-solar-info-circle-outline class="w-6 h-6 text-info-300 flex-shrink-0 mt-0.5" />
                <div>
                    <h3 class="text-lg font-semibold text-info-300 mb-2">
                        🚧 Panel en Desarrollo
                    </h3>
                    <p class="body-base text-info-200 mb-4">
                        Este es un dashboard básico. Estamos trabajando en implementar todas las funcionalidades 
                        para la gestión completa de tu tienda en línea.
                    </p>
                    <div class="bg-info-100 rounded-lg p-4">
                        <h4 class="font-semibold text-info-300 mb-2">Funcionalidades planificadas:</h4>
                        <ul class="body-small text-info-200 space-y-1">
                            <li>✅ Autenticación y seguridad</li>
                            <li>🔄 Gestión de productos y categorías</li>
                            <li>🔄 Procesamiento de pedidos</li>
                            <li>🔄 Administración de clientes</li>
                            <li>🔄 Reportes y análisis</li>
                            <li>🔄 Configuración de la tienda</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
    <script>
    function recentOrdersWidget() {
        return {
            updating: false,
            
            async updateOrderStatus(orderId, newStatus, selectElement) {
                if (this.updating) return;
                
                this.updating = true;
                const originalValue = selectElement.getAttribute('data-original') || selectElement.value;
                
                try {
                    const response = await fetch(`{{ url('/' . $store->slug . '/admin/orders') }}/${orderId}/update-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Actualizar la badge de estado
                        const orderRow = selectElement.closest('.flex');
                        const statusBadge = orderRow.querySelector('.rounded-full');
                        statusBadge.textContent = data.status_label;
                        statusBadge.className = `text-xs px-2 py-1 rounded-full ${data.status_color_class}`;
                        
                        selectElement.setAttribute('data-original', newStatus);
                        
                        // Mostrar notificación de éxito
                        this.showToast('Estado actualizado correctamente', 'success');
                    } else {
                        selectElement.value = originalValue;
                        this.showToast(data.message || 'Error al actualizar el estado', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    selectElement.value = originalValue;
                    this.showToast('Error al actualizar el estado', 'error');
                } finally {
                    this.updating = false;
                }
            },
            
            showToast(message, type = 'info') {
                alert(`${type.toUpperCase()}: ${message}`);
            }
        }
    }
    
    function announcementBanners() {
        return {
            banners: [],
            currentSlide: 0,
            autoplayInterval: null,
            autoplayDelay: 4000, // 4 segundos
            
            async loadBanners() {
                try {
                    const response = await fetch('{{ route("tenant.admin.announcements.api.banners", $store->slug) }}');
                    const data = await response.json();
                    this.banners = data;
                    
                    if (this.banners.length > 0) {
                        this.startAutoplay();
                    }
                } catch (error) {
                    console.error('Error loading banners:', error);
                }
            },
            
            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.banners.length;
                this.resetAutoplay();
            },
            
            previousSlide() {
                this.currentSlide = this.currentSlide === 0 ? this.banners.length - 1 : this.currentSlide - 1;
                this.resetAutoplay();
            },
            
            goToSlide(index) {
                this.currentSlide = index;
                this.resetAutoplay();
            },
            
            startAutoplay() {
                if (this.banners.length <= 1) return;
                
                this.autoplayInterval = setInterval(() => {
                    this.nextSlide();
                }, this.autoplayDelay);
            },
            
            stopAutoplay() {
                if (this.autoplayInterval) {
                    clearInterval(this.autoplayInterval);
                    this.autoplayInterval = null;
                }
            },
            
            resetAutoplay() {
                this.stopAutoplay();
                this.startAutoplay();
            }
        }
    }
    </script>
    @endpush
</x-tenant-admin-layout> 