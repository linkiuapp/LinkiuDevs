<x-tenant-admin-layout :store="$store">
    @section('title', 'Dashboard')
    @section('subtitle', 'Panel de administración de tu tienda')

    
    @section('content')

        <div class="grid grid-cols-10 grid-rows-6 gap-3">

            <!-- Widget de estado de la tienda -->
            @if($recentOrders->count() > 0)
                <div class="col-span-6 row-span-5 bg-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                    <div class="p-6">
                        <!-- Título -->
                        <div class="flex items-center justify-between">
                            <h2 class="text-body-large text-black-500 mb-0 font-medium">
                                Pedidos Recientes
                            </h2>
                            <a href="{{ route('tenant.admin.orders.index', ['store' => $store->slug]) }}" 
                               class="flex items-center text-secondary-300 text-body-small font-medium transition-colors border border-secondary-300 rounded-lg px-2 py-2">
                                Ver todos
                                <x-lucide-arrow-up-right class="w-4 h-4 ml-2" />
                            </a>
                        </div>
                        <!-- Contenido -->
                        <div class="pt-6">
                            <div class="space-y-4" x-data="recentOrdersWidget">
                                @foreach($recentOrders as $order)
                                    <div class="flex items-center gap-4 p-4 border border-accent-100 rounded-lg hover:bg-accent-100 transition-colors">
                                        <!-- Imagen del primer producto -->
                                        <div class="flex-shrink-0">
                                            @if($order->items->first() && $order->items->first()->product && $order->items->first()->product->mainImage)
                                                <img src="{{ $order->items->first()->product->mainImage->image_url }}" 
                                                     alt="{{ $order->items->first()->product_name }}"
                                                     class="w-12 h-12 object-cover rounded-lg border border-accent-200">
                                            @else
                                                <div class="w-12 h-12 bg-accent-200 rounded-lg flex items-center justify-center">
                                                    <x-solar-bag-3-outline class="w-6 h-6 text-black-300" />
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Info del pedido -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <a href="{{ route('tenant.admin.orders.show', ['store' => $store->slug, 'order' => $order->id]) }}" 
                                                   class="text-body-small font-bold text-black-300 hover:text-info-300 transition-colors hover:underline">
                                                    #{{ $order->order_number }}
                                                </a>
                                                <span class="text-small px-2 py-1 rounded-full {{ $order->status_color_class }}">
                                                    {{ $order->status_label }}
                                                </span>
                                            </div>
                                            <p class="text-small font-regular text-black-300 truncate">
                                                {{ $order->customer_name }} • {{ $order->items_count }} producto(s)
                                            </p>
                                            <p class="text-small font-regular text-black-300">
                                                {{ $order->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <!-- Total -->
                                            <div class="text-right">
                                                <div class="text-body-large font-bold text-black-300">
                                                    ${{ number_format($order->total, 0, ',', '.') }}
                                                </div>
                                            </div>
                                            <!-- Select de estado -->
                                            <div class="flex-shrink-0">
                                                <select 
                                                    class="text-caption px-2 py-2 border border-white-200 rounded focus:ring-1 focus:ring-primary-200 focus:border-primary-300 transition-colors"
                                                    @change="updateOrderStatus({{ $order->id }}, $event.target.value, $event.target)"
                                                    :disabled="updating"
                                                >
                                                    @foreach(\App\Shared\Models\Order::STATUSES as $status => $label)
                                                        <option 
                                                            value="{{ $status }}" 
                                                            :selected="order.status === '{{ $status }}'"
                                                            class="text-xs"
                                                        >
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Widget botones de acciones -->
            <!-- Crear Producto -->
            <div class="col-start-7 bg-primary-300 rounded-lg p-0 shadow-sm flex items-center justify-center py-4 hover:bg-primary-400">
                <a href="{{ route('tenant.admin.products.create', ['store' => $store->slug]) }}" class="flex flex-col items-center justify-center text-white-50 text-body-small font-medium transition-colors">
                    <x-lucide-circle-plus class="w-8 h-8 mb-2" />
                    <span>Agregar Producto</span>
                </a>
            </div>
            <!-- Crear Pedido -->
            <div class="col-start-8 bg-info-300 rounded-lg p-0 shadow-sm flex items-center justify-center py-4 hover:bg-info-400">
                <a href="{{ route('tenant.admin.orders.create', ['store' => $store->slug]) }}" class="flex flex-col items-center justify-center text-white-50 text-body-small font-medium transition-colors">
                    <x-lucide-circle-plus class="w-8 h-8 mb-2" />
                    <span>Crear Pedido</span>
                </a>
            </div>
            <!-- Crear Cupón -->
            <div class="col-start-9 bg-secondary-300 rounded-lg p-0 shadow-sm flex items-center justify-center py-4 hover:bg-secondary-200">
                <a href="{{ route('tenant.admin.coupons.create', ['store' => $store->slug]) }}" class="flex flex-col items-center justify-center text-white-50 text-body-small font-medium transition-colors">
                    <x-lucide-circle-plus class="w-8 h-8 mb-2" />
                    <span>Crear Cupón</span>
                </a>
            </div>
            <!-- Crear Slider -->
            <div class="col-start-10 bg-warning-300 rounded-lg p-0 shadow-sm flex items-center justify-center py-4 hover:bg-warning-200">
                <a href="{{ route('tenant.admin.sliders.create', ['store' => $store->slug]) }}" class="flex flex-col items-center justify-center text-black-300 text-body-small font-medium transition-colors">
                    <x-lucide-circle-plus class="w-8 h-8 mb-2" />
                    <span>Crear Slider</span>
                </a>
            </div>

            <!-- Widget contadores de estadísticas -->
            <!-- Total de pedidos -->
            <div class="col-span-2 col-start-7 row-start-2 bg-gradient-to-r from-accent-200 to-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-8 py-6 gap-8">
                    <h2 class="text-body-large text-secondary-100 mb-0 font-medium">
                        Total de pedidos
                    </h2>
                    <h3 class="text-h3 text-black-300 mb-0 font-extrabold">
                        {{ $stats['total'] }}
                    </h3>
                </div>
            </div>
            <!-- Pedidos confirmados -->
            <div class="col-span-2 col-start-9 row-start-2 bg-gradient-to-r from-primary-75/50 to-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-8 py-6 gap-8">
                    <h2 class="text-body-large text-secondary-100 mb-0 font-medium">
                        Pedidos confirmados
                    </h2>
                    <h3 class="text-h3 text-black-300 mb-0 font-extrabold">
                        {{ $stats['confirmed'] }}
                    </h3>
                </div>
            </div>
            <!-- Pedidos pendientes -->
            <div class="col-span-2 col-start-7 row-start-3 bg-gradient-to-r from-error-75/30 to-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-8 py-6 gap-8">
                    <h2 class="text-body-large text-secondary-100 mb-0 font-medium">
                        Pedidos pendientes
                    </h2>
                    <h3 class="text-h3 text-black-300 mb-0 font-extrabold">
                        {{ $stats['pending'] }}
                    </h3>
                </div>
            </div>
            <!-- Pedidos en preparación -->
            <div class="col-span-2 col-start-9 row-start-3 bg-gradient-to-r from-warning-75/30 to-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-8 py-6 gap-8">
                    <h2 class="text-body-large text-secondary-100 mb-0 font-medium">
                        Pedidos en preparación
                    </h2>
                    <h3 class="text-h3 text-black-300 mb-0 font-extrabold">
                        {{ $stats['preparing'] }}
                    </h3>
                </div>
            </div>
            <!-- Pedidos en Enviados -->
            <div class="col-span-2 col-start-7 row-start-4 bg-gradient-to-r from-info-75/30 to-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-8 py-6 gap-8">
                    <h2 class="text-body-large text-secondary-100 mb-0 font-medium">
                        Pedidos en Enviados
                    </h2>
                    <h3 class="text-h3 text-black-300 mb-0 font-extrabold">
                        {{ $stats['shipped'] }}
                    </h3>
                </div>
            </div>
            <!-- Pedidos entregados -->
            <div class="col-span-2 col-start-9 row-start-4 bg-gradient-to-r from-success-75/30 to-accent-50 rounded-lg p-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-8 py-6 gap-8">
                    <h2 class="text-body-large text-secondary-100 mb-0 font-medium">
                        Pedidos entregados
                    </h2>
                    <h3 class="text-h3 text-black-300 mb-0 font-extrabold">
                        {{ $stats['delivered'] }}
                    </h3>
                </div>
            </div>

            <!-- Widget anuncios -->
            <div class="col-span-4 row-span-2 col-start-7 row-start-5">
                <div class="mb-8" x-data="announcementBanners" x-init="loadBanners()" x-show="banners.length > 0">
                    <div class="relative overflow-hidden rounded-xl shadow-sm mx-auto w-full">
                        <!-- Contenedor del carrusel -->
                        <div class="relative w-full h-[200px]">
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
                                             class="w-full h-full object-none rounded-xl">
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