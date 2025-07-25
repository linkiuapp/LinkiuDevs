<!-- Sidebar Admin de Tienda -->
<aside class="fixed left-0 top-0 z-40 h-screen sidebar bg-white-50 shadow-lg dark:bg-gray-800 transition-transform duration-300 ease-in-out flex flex-col" 
       x-data="{ sidebarOpen: true }"
       :class="{ '-translate-x-full': !sidebarOpen }">
    
    <!-- Logo/Header -->
    <div class="flex h-10 mt-6 items-center justify-center px-6 flex-shrink-0">
        <div class="flex items-center">
            <a href="{{ route('tenant.admin.dashboard', ['store' => $store->slug]) }}">
                @php
                    // Obtener logo de la aplicación configurado en SuperAdmin
                    $tempLogo = session('temp_app_logo');
                    $appLogo = $tempLogo ?: env('APP_LOGO');
                    $logoSrc = $appLogo ? asset('storage/' . $appLogo) : asset('assets/images/logo_Linkiu.svg');
                @endphp
                <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" class="w-auto h-10 mt-1">
            </a>
        </div>
        <!-- Toggle button for mobile -->
        <button @click="sidebarOpen = !sidebarOpen" 
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 lg:hidden">
                <x-solar-widget-2-outline class="w-4 h-4" />
        </button>
    </div>
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-4 py-6">
        <ul class="space-y-1">
            <!-- 🎯 ÁREA DE CONTROL -->
            
            <!-- Dashboard -->
            <li>
                <a href="{{ route('tenant.admin.dashboard', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.dashboard') ? 'item-sidebar-active' : '' }}">
                    <x-solar-widget-2-outline class="w-4 h-4 mr-2" />
                    Dashboard
                </a>
            </li>

            <!-- Pedidos (Prioritario) -->
            <li>
                <a href="{{ route('tenant.admin.orders.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.orders.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-clipboard-list-outline class="w-4 h-4 mr-2" />
                    Pedidos
                    @if(($store->pending_orders_count ?? 0) > 0)
                        <span class="ml-auto text-xs bg-warning-300 text-black-500 px-2 py-1 rounded-full font-medium">
                            {{ $store->pending_orders_count }}
                        </span>
                    @endif
                </a>
            </li>

            <!-- 🏪 GESTIÓN DE TIENDA -->
            <h3 class="title-group-sidebar mt-6">
                Tienda
            </h3>

            <!-- Perfil del Negocio -->
            <li>
                <a href="{{ route('tenant.admin.business-profile.index', ['store' => $store->slug]) }}"
                   class="item-sidebar {{ request()->routeIs('tenant.admin.business-profile.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-shop-outline class="w-4 h-4 mr-2" />
                    Perfil del Negocio
                </a>
            </li>

            <!-- Diseño de la Tienda -->
            <li>
                <a href="{{ route('tenant.admin.store-design.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.store-design.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-pallete-2-outline class="w-4 h-4 mr-2" />
                    Diseño de la Tienda
                </a>
            </li>

            <!-- Plan y Facturación -->
            <li>
                <a href="{{ route('tenant.admin.billing.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.billing.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-card-outline class="w-4 h-4 mr-2" />
                    Plan y Facturación
                </a>
            </li>

            <!-- 📦 PRODUCTOS -->
            <h3 class="title-group-sidebar mt-6">
                Productos
            </h3>

            <!-- Categorías -->
            <li>
                <a href="{{ route('tenant.admin.categories.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.categories.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-folder-outline class="w-4 h-4 mr-2" />
                    Categorías
                    @php
                        $categoriesUsed = $store->categories_count ?? 0;
                        $categoriesLimit = $store->plan->max_categories;
                        $categoriesPercent = $categoriesLimit > 0 ? ($categoriesUsed / $categoriesLimit) * 100 : 0;
                        $categoriesBadgeColor = $categoriesPercent >= 90 ? 'bg-error-300' : ($categoriesPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-info-300');
                    @endphp
                    <span class="ml-auto text-xs {{ $categoriesBadgeColor }} text-white-50 px-2 py-1 rounded-full font-medium">
                        {{ $categoriesUsed }}/{{ $categoriesLimit }}
                    </span>
                </a>
            </li>

            <!-- Variables -->
            <li>
                <a href="{{ route('tenant.admin.variables.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.variables.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-settings-outline class="w-4 h-4 mr-2" />
                    Variables
                    @php
                        $variablesUsed = $store->variables_count ?? 0;
                        $variablesLimit = $store->plan->max_variables ?? 50;
                        $variablesPercent = $variablesLimit > 0 ? ($variablesUsed / $variablesLimit) * 100 : 0;
                        $variablesBadgeColor = $variablesPercent >= 90 ? 'bg-error-300' : ($variablesPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-info-300');
                    @endphp
                    <span class="ml-auto text-xs {{ $variablesBadgeColor }} text-white-50 px-2 py-1 rounded-full font-medium">
                        {{ $variablesUsed }}/{{ $variablesLimit }}
                    </span>
                </a>
            </li>

            <!-- Productos -->
            <li>
                <a href="{{ route('tenant.admin.products.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.products.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-box-outline class="w-4 h-4 mr-2" />
                    Productos
                    @php
                        $productsUsed = $store->products_count ?? 0;
                        $productsLimit = $store->plan->max_products;
                        $productsPercent = $productsLimit > 0 ? ($productsUsed / $productsLimit) * 100 : 0;
                        $productsBadgeColor = $productsPercent >= 90 ? 'bg-error-300' : ($productsPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-info-300');
                    @endphp
                    <span class="ml-auto text-xs {{ $productsBadgeColor }} text-white-50 px-2 py-1 rounded-full font-medium">
                        {{ $productsUsed }}/{{ $productsLimit }}
                    </span>
                </a>
            </li>

            <!-- 💰 VENTAS -->
            <h3 class="title-group-sidebar mt-6">
                Ventas
            </h3>

            <!-- Métodos de Pago -->
            <li>
                <a href="{{ route('tenant.admin.payment-methods.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.payment-methods.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-card-outline class="w-4 h-4 mr-2" />
                    Métodos de Pago
                </a>
            </li>

            <!-- Métodos de Envío -->
            <li>
                <a href="{{ route('tenant.admin.shipping-methods.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.shipping-methods.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-delivery-outline class="w-4 h-4 mr-2" />
                    Métodos de Envío
                    @php
                        $shippingZonesCount = $store->shippingZones()->count();
                        $maxShippingZones = match($store->plan->slug) {
                            'explorer' => 1,
                            'master' => 2,
                            'legend' => 4,
                            default => 1
                        };
                        $shippingPercent = $maxShippingZones > 0 ? ($shippingZonesCount / $maxShippingZones) * 100 : 0;
                        $shippingBadgeColor = $shippingPercent >= 90 ? 'bg-error-300' : ($shippingPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-info-300');
                    @endphp
                    <span class="ml-auto text-xs {{ $shippingBadgeColor }} text-white-50 px-2 py-1 rounded-full font-medium">
                        {{ $shippingZonesCount }}/{{ $maxShippingZones }}
                    </span>
                </a>
            </li>

            <!-- 📢 MARKETING -->
            <h3 class="title-group-sidebar mt-6">
                Marketing
            </h3>

            <!-- Cupones -->
            <li>
                <a href="#" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.coupons.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-ticket-outline class="w-4 h-4 mr-2" />
                    Cupones
                    @php
                        $couponsUsed = $store->active_coupons_count ?? 0;
                        $couponsLimit = $store->plan->max_active_coupons;
                        $couponsPercent = $couponsLimit > 0 ? ($couponsUsed / $couponsLimit) * 100 : 0;
                        $couponsBadgeColor = $couponsPercent >= 90 ? 'bg-error-300' : ($couponsPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-info-300');
                    @endphp
                    <span class="ml-auto text-xs {{ $couponsBadgeColor }} text-white-50 px-2 py-1 rounded-full font-medium">
                        {{ $couponsUsed }}/{{ $couponsLimit }}
                    </span>
                </a>
            </li>

            <!-- Slider -->
            <li>
                <a href="{{ route('tenant.admin.sliders.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.sliders.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-gallery-outline class="w-4 h-4 mr-2" />
                    Slider
                    @php
                        $slidersUsed = $store->sliders_count ?? 0;
                        $slidersLimit = $store->plan->max_slider;
                        $slidersPercent = $slidersLimit > 0 ? ($slidersUsed / $slidersLimit) * 100 : 0;
                        $slidersBadgeColor = $slidersPercent >= 90 ? 'bg-error-300' : ($slidersPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-info-300');
                    @endphp
                    <span class="ml-auto text-xs {{ $slidersBadgeColor }} text-white-50 px-2 py-1 rounded-full font-medium">
                        {{ $slidersUsed }}/{{ $slidersLimit }}
                    </span>
                </a>
            </li>

            <!-- Sedes -->
            <li>
                <a href="{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.locations.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-buildings-outline class="w-4 h-4 mr-2" />
                    Sedes
                    @php
                        $locationsUsed = $store->locations_count ?? 0;
                        $locationsLimit = $store->plan->max_locations ?? 5;
                        $locationsPercent = $locationsLimit > 0 ? ($locationsUsed / $locationsLimit) * 100 : 0;
                        $locationsBadgeColor = $locationsPercent >= 90 ? 'bg-error-300' : ($locationsPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-info-300');
                    @endphp
                    <span class="ml-auto text-xs {{ $locationsBadgeColor }} text-white-50 px-2 py-1 rounded-full font-medium">
                        {{ $locationsUsed }}/{{ $locationsLimit }}
                    </span>
                </a>
            </li>

            <!-- 🛠️ SOPORTE -->
            <h3 class="title-group-sidebar mt-6">
                Soporte
            </h3>

            <!-- Soporte y Tickets -->
            <li>
                <a href="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.tickets.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-chat-round-call-outline class="w-4 h-4 mr-2" />
                    Soporte y Tickets
                    @php
                        $openTicketsCount = $store->tickets()->whereIn('status', ['open', 'in_progress'])->count();
                    @endphp
                    @if($openTicketsCount > 0)
                        <span class="ml-auto text-xs bg-error-300 text-white-50 px-2 py-1 rounded-full font-medium">
                            {{ $openTicketsCount }}
                        </span>
                    @endif
                </a>
            </li>

            <!-- Anuncios de Linkiu -->
            <li>
                <a href="{{ route('tenant.admin.announcements.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.announcements.*') ? 'item-sidebar-active' : '' }}">
                    <x-solar-bell-bing-outline class="w-4 h-4 mr-2" />
                    Anuncios de Linkiu
                    @if(($store->unread_announcements_count ?? 0) > 0)
                        <span class="ml-auto text-xs bg-warning-300 text-black-500 px-2 py-1 rounded-full font-medium">
                            {{ $store->unread_announcements_count }}
                        </span>
                    @endif
                </a>
            </li>
        </ul>
    </nav>

    <!-- User section -->
    <div class="p-4 border-t dark:border-gray-700 flex-shrink-0">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-primary-300 rounded-full flex items-center justify-center">
                    <span class="text-white-50 text-sm font-medium">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </span>
                </div>
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white-50 truncate">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    Admin de Tienda
                </p>
            </div>
            <div class="flex-shrink-0">
                <form method="POST" action="{{ route('tenant.admin.logout', $store->slug) }}">
                    @csrf
                    <button type="submit" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors duration-200"
                            title="Cerrar sesión">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile sidebar overlay -->
<div x-show="!sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-30 bg-gray-600 bg-opacity-75 lg:hidden"
     @click="sidebarOpen = true">
</div> 