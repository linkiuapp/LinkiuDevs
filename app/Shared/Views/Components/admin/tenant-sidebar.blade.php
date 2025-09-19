@php
use Illuminate\Support\Facades\Storage;
@endphp

<!-- Sidebar Admin de Tienda -->
<aside class="fixed left-0 top-0 z-40 h-screen sidebar bg-accent-75 shadow-2xl transition-transform duration-300 ease-in-out flex flex-col" 
       x-data="{ sidebarOpen: true }"
       :class="{ '-translate-x-full': !sidebarOpen }">

    <!-- User section -->
        <div class="px-6 pt-6 pb-2 flex-shrink-0">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @php
                        // Obtener la imagen de perfil del store design (logo de la tienda)
                        $profileImage = $store->design?->logo_url ?? $store->logo_url;
                    @endphp
                    
                    @if($profileImage)
                        <img src="{{ $profileImage }}" 
                            alt="Perfil" 
                            class="w-8 h-8 rounded-full object-cover border border-accent-200">
                    @else
                        <div class="w-8 h-8 bg-primary-300 rounded-full flex items-center justify-center">
                            <span class="text-accent-50 text-body-small font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-body-small font-bold text-black-300 truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-caption text-regular text-black-300 truncate">
                        Admin de Tienda
                    </p>
                </div>
                <div class="flex-shrink-0 items-center justify-center">
                    <form method="POST" action="{{ route('tenant.admin.logout', $store->slug) }}">
                        @csrf
                        <button type="submit" 
                                class="text-error-300 hover:text-error-400 transition-colors duration-200"
                                title="Cerrar sesión">
                                <x-lucide-log-out class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <!--End User section -->
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-6 py-4" x-data="sidebarSections()">
        <ul class="space-y-1">
        <!-- Favoritos-->
            <li>
                <button @click="toggleSection('favoritos')" 
                        class="title-group-sidebar w-full flex justify-between items-center hover:bg-accent-100 px-2 py-1 rounded transition-colors">
                    <span>Favoritos</span>
                    <x-lucide-chevron-down class="w-4 h-4 transition-transform" 
                                           x-bind:class="{ 'rotate-180': sections.favoritos }"/>
                </button>
                
                <ul x-show="sections.favoritos" x-collapse class="mt-2 space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('tenant.admin.dashboard', ['store' => $store->slug]) }}" 
                           class="item-sidebar {{ request()->routeIs('tenant.admin.dashboard') ? 'item-sidebar-active' : '' }}">
                            <x-lucide-layout-dashboard class="w-4 h-4 mr-2" />
                            Dashboard
                        </a>
                    </li>

                    <!-- Pedidos (Prioritario) -->
                    <li>
                        <a href="{{ route('tenant.admin.orders.index', ['store' => $store->slug]) }}" 
                           class="item-sidebar {{ request()->routeIs('tenant.admin.orders.*') ? 'item-sidebar-active' : '' }}">
                            <x-lucide-party-popper class="w-4 h-4 mr-2" />
                            Pedidos
                            @if(($store->pending_orders_count ?? 0) > 0)
                                <span class="ml-auto text-caption bg-error-300 text-accent-300 px-2 py-1 rounded-full font-medium">
                                    {{ $store->pending_orders_count }}
                                </span>
                            @else
                                <span class="ml-auto text-small bg-black-50 text-black-500 px-2 py-1 rounded-full font-medium">
                                    0
                                </span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

        <!-- Tienda y productos-->
            <li>
                <button @click="toggleSection('tienda')" 
                        class="title-group-sidebar w-full flex justify-between items-center hover:bg-accent-100 px-2 py-1 rounded transition-colors">
                    <span>Tienda y productos</span>
                    <x-lucide-chevron-down class="w-4 h-4 transition-transform" 
                                           x-bind:class="{ 'rotate-180': sections.tienda }"/>
                </button>
                
                <ul x-show="sections.tienda" x-collapse class="mt-2 space-y-1">

                    <!-- Categorías -->
                            <li>
                        <a href="{{ route('tenant.admin.categories.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.categories.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-layout-list class="w-4 h-4 mr-2" />
                    Categorías
                    @php
                        $categoriesUsed = $store->categories_count ?? 0;
                        $categoriesLimit = $store->plan->max_categories;
                        $categoriesPercent = $categoriesLimit > 0 ? ($categoriesUsed / $categoriesLimit) * 100 : 0;
                        $categoriesBadgeColor = $categoriesPercent >= 90 ? 'bg-error-300 text-accent-300' : ($categoriesPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-black-50 text-black-500');
                    @endphp
                    <span class="ml-auto text-small {{ $categoriesBadgeColor }} px-2 py-1 rounded-full font-medium tracking-widest">
                        {{ $categoriesUsed }}/{{ $categoriesLimit }}
                    </span>
                        </a>
                    </li>
                    <!-- Variables -->
                            <li>
                        <a href="{{ route('tenant.admin.variables.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.variables.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-tag class="w-4 h-4 mr-2" />
                    Variables
                    @php
                        $variablesUsed = $store->variables_count ?? 0;
                        $variablesLimit = $store->plan->max_variables ?? 50;
                        $variablesPercent = $variablesLimit > 0 ? ($variablesUsed / $variablesLimit) * 100 : 0;
                        $variablesBadgeColor = $variablesPercent >= 90 ? 'bg-error-300 text-accent-300' : ($variablesPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-black-50 text-black-500');
                    @endphp
                    <span class="ml-auto text-small {{ $variablesBadgeColor }} px-2 py-1 rounded-full font-medium tracking-widest">
                        {{ $variablesUsed }}/{{ $variablesLimit }}
                    </span>
                        </a>
                    </li>
            <!-- Productos -->
                    <li>
                        <a href="{{ route('tenant.admin.products.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.products.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-package class="w-4 h-4 mr-2" />
                    Productos
                    @php
                        $productsUsed = $store->products_count ?? 0;
                        $productsLimit = $store->plan->max_products;
                        $productsPercent = $productsLimit > 0 ? ($productsUsed / $productsLimit) * 100 : 0;
                        $productsBadgeColor = $productsPercent >= 90 ? 'bg-error-300 text-accent-300' : ($productsPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-black-50 text-black-500');
                    @endphp
                    <span class="ml-auto text-small {{ $productsBadgeColor }} px-2 py-1 rounded-full font-medium tracking-widest">
                        {{ $productsUsed }}/{{ $productsLimit }}
                    </span>
                        </a>
                    </li>
            <!-- Configuración de Envíos (NUEVO SISTEMA) -->
                    <li>
                        <a href="{{ route('tenant.admin.simple-shipping.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.simple-shipping.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-truck class="w-4 h-4 mr-2" />
                    Gestión de Envíos
                    @php
                        // Obtener zonas de envío actuales del nuevo sistema
                        $simpleShipping = \App\Features\TenantAdmin\Models\SimpleShipping::where('store_id', $store->id)->first();
                        $currentZones = $simpleShipping ? $simpleShipping->zones()->count() : 0;
                        
                        // Límites según el plan desde SuperAdmin
                        $zoneLimits = [
                            'explorer' => 2,
                            'master' => 3, 
                            'legend' => 4
                        ];
                        $planSlug = strtolower($store->plan->slug ?? 'explorer');
                        $maxZones = $zoneLimits[$planSlug] ?? 2;
                        
                        $zonesPercent = $maxZones > 0 ? ($currentZones / $maxZones) * 100 : 0;
                        $zonesBadgeColor = $zonesPercent >= 90 ? 'bg-error-300 text-accent-300' : ($zonesPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-black-50 text-black-500');
                    @endphp
                    <span class="ml-auto text-small {{ $zonesBadgeColor }} px-2 py-1 rounded-full font-medium tracking-widest">
                        {{ $currentZones }}/{{ $maxZones }}
                    </span>
                        </a>
                    </li>
            <!-- Métodos de Pago -->
                    <li>
                        <a href="{{ route('tenant.admin.payment-methods.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.payment-methods.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-dock class="w-4 h-4 mr-2" />
                    Métodos de Pago
                        </a>
                    </li>
            <!-- Sedes -->
                    <li>
                        <a href="{{ route('tenant.admin.locations.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.locations.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-store class="w-4 h-4 mr-2" />
                    Sedes
                    @php
                        $locationsUsed = $store->locations_count ?? 0;
                        $locationsLimit = $store->plan->max_locations ?? $store->plan->max_sedes ?? 1;
                        $locationsPercent = $locationsLimit > 0 ? ($locationsUsed / $locationsLimit) * 100 : 0;
                        $locationsBadgeColor = $locationsPercent >= 90 ? 'bg-error-300 text-accent-300' : ($locationsPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-black-50 text-black-500');
                    @endphp
                    <span class="ml-auto text-small {{ $locationsBadgeColor }} px-2 py-1 rounded-full font-medium tracking-widest">
                        {{ $locationsUsed }}/{{ $locationsLimit }}
                    </span>
                        </a>
                    </li>

                </ul>
            </li>

        <!-- Marketing-->
            <li>
                <button @click="toggleSection('marketing')" 
                        class="title-group-sidebar w-full flex justify-between items-center hover:bg-accent-100 px-2 py-1 rounded transition-colors">
                    <span>Marketing</span>
                    <x-lucide-chevron-down class="w-4 h-4 transition-transform" 
                                           x-bind:class="{ 'rotate-180': sections.marketing }"/>
                </button>
                
                <ul x-show="sections.marketing" x-collapse class="mt-2 space-y-1">

            <!-- Cupones -->
                    <li>
                        <a href="{{ route('tenant.admin.coupons.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.coupons.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-ticket-percent class="w-4 h-4 mr-2" />
                    Cupones
                    @php
                        $couponsUsed = $store->active_coupons_count ?? 0;
                        $couponsLimit = $store->plan->max_active_coupons;
                        $couponsPercent = $couponsLimit > 0 ? ($couponsUsed / $couponsLimit) * 100 : 0;
                        $couponsBadgeColor = $couponsPercent >= 90 ? 'bg-error-300 text-accent-300' : ($couponsPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-black-50 text-black-500');
                    @endphp
                    <span class="ml-auto text-small {{ $couponsBadgeColor }} px-2 py-1 rounded-full font-medium tracking-widest">
                        {{ $couponsUsed }}/{{ $couponsLimit }}
                    </span>
                        </a>
                    </li>
            <!-- Slider -->
                    <li>
                        <a href="{{ route('tenant.admin.sliders.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.sliders.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-images class="w-4 h-4 mr-2" />
                    Slider
                    @php
                        $slidersUsed = $store->sliders_count ?? 0;
                        $slidersLimit = $store->plan->max_sliders ?? $store->plan->max_slider ?? 1;
                        $slidersPercent = $slidersLimit > 0 ? ($slidersUsed / $slidersLimit) * 100 : 0;
                        $slidersBadgeColor = $slidersPercent >= 90 ? 'bg-error-300 text-accent-300' : ($slidersPercent >= 70 ? 'bg-warning-300 text-black-500' : 'bg-black-50 text-black-500');
                    @endphp
                    <span class="ml-auto text-small {{ $slidersBadgeColor }} px-2 py-1 rounded-full font-medium tracking-widest">
                        {{ $slidersUsed }}/{{ $slidersLimit }}
                    </span>
                        </a>
                    </li>
            <!-- Integrcion ADS -->
            <!-- <li>
                <a href="#" 
                   class="item-sidebar">
                    <x-lucide-unplug class="w-4 h-4 mr-2" />
                    Integración ADS
                        </a>
                    </li> -->

                </ul>
            </li>

        <!-- Perfil y facturación-->
            <li>
                <button @click="toggleSection('perfil')" 
                        class="title-group-sidebar w-full flex justify-between items-center hover:bg-accent-100 px-2 py-1 rounded transition-colors">
                    <span>Perfil y facturación</span>
                    <x-lucide-chevron-down class="w-4 h-4 transition-transform" 
                                           x-bind:class="{ 'rotate-180': sections.perfil }"/>
                </button>
                
                <ul x-show="sections.perfil" x-collapse class="mt-2 space-y-1">

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


                </ul>
            </li>

        <!-- Anuncios y soporte-->
            <li>
                <button @click="toggleSection('soporte')" 
                        class="title-group-sidebar w-full flex justify-between items-center hover:bg-accent-100 px-2 py-1 rounded transition-colors">
                    <span>Anuncios y soporte</span>
                    <x-lucide-chevron-down class="w-4 h-4 transition-transform" 
                                           x-bind:class="{ 'rotate-180': sections.soporte }"/>
                </button>
                
                <ul x-show="sections.soporte" x-collapse class="mt-2 space-y-1">


            <!-- Soporte y Tickets -->
                    <li>
                        <a href="{{ route('tenant.admin.tickets.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.tickets.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-server-crash class="w-4 h-4 mr-2" />
                    Soporte y Tickets
                    @php
                        $openTicketsCount = $store->tickets()->whereIn('status', ['open', 'in_progress'])->count();
                    @endphp
                    @if($openTicketsCount > 0)
                        <span class="ml-auto text-xs bg-error-300 text-accent-50 px-2 py-1 rounded-full font-medium">
                            {{ $openTicketsCount }}
                        </span>
                    @endif
                        </a>
                    </li>

            <!-- Anuncios de Linkiu -->
                    <li>
                        <a href="{{ route('tenant.admin.announcements.index', ['store' => $store->slug]) }}" 
                   class="item-sidebar {{ request()->routeIs('tenant.admin.announcements.*') ? 'item-sidebar-active' : '' }}">
                    <x-lucide-megaphone class="w-4 h-4 mr-2" />
                    Anuncios de Linkiu
                    @if(($store->unread_announcements_count ?? 0) > 0)
                        <span class="ml-auto text-xs bg-warning-300 text-black-500 px-2 py-1 rounded-full font-medium">
                            {{ $store->unread_announcements_count }}
                        </span>
                    @endif
                </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Banner consumo de plan-->
    <div class="px-6 pb-6 flex-shrink-0">
        @php
            $planName = strtolower($store->plan?->name ?? 'explorer');

            // Fondo del banner por plan usando paleta oficial
            $planToClasses = [
                'explorer' => 'bg-gradient-to-r from-primary-200 to-primary-300 text-black-500',
                'master'   => 'bg-gradient-to-r from-secondary-200 to-secondary-300 text-accent-300',
                'legend'   => 'bg-gradient-to-r from-warning-200 to-warning-300 text-black-500',
            ];
            $bannerClasses = $planToClasses[$planName] ?? 'bg-gradient-to-r from-secondary-200 to-secondary-300 text-accent-300';

            // Usar PlanUsageService para cálculos precisos
            try {
                $planUsageService = new \App\Services\PlanUsageService();
                $planUsage = $planUsageService->calculateUsage($store);
                $usagePercent = $planUsage['overall_percentage'] ?? 0;
            } catch (Exception $e) {
                // Fallback si hay error
                $usagePercent = 0;
                \Log::warning('Error calculando uso del plan en sidebar: ' . $e->getMessage());
            }

            // Color dinámico para la barra de progreso
            $progressBgTrack = 'bg-success-50';
            $progressFill = 'bg-success-400';
            if ($usagePercent >= 90) {
                $progressBgTrack = 'bg-error-50';
                $progressFill = 'bg-error-400';
            } elseif ($usagePercent >= 70) {
                $progressBgTrack = 'bg-warning-50';
                $progressFill = 'bg-warning-300';
            }
        @endphp

        <div class="rounded-xl p-4 {{ $bannerClasses }}">
            <div class="flex-2 flex items-center justify-between gap-2">
                <div class="flex-1">
                    <p class="text-small text-medium">Has consumido de tu plan</p>
                    <div class="w-full {{ $progressBgTrack }} rounded-full h-2 mt-2">
                        <div class="{{ $progressFill }} h-2 rounded-full transition-all duration-500" style="width: {{ number_format($usagePercent, 0) }}%"></div>
                    </div>
                    <p class="mt-3 text-caption font-medium leading-none">
                        Tu plan activo es el <br>
                        <span class="text-body-large font-bold">{{ 'Plan ' . ucfirst($planName) }}</span>
                    </p>
                </div>

                <div class="flex-shrink-0">
                    @if($store->plan && $store->plan->image_url)
                        <img src="{{ $store->plan->image_url }}" alt="Plan {{ $store->plan->name }}" class="w-20 h-20 object-cover rounded-lg">
                    @else
                        <!-- Fallback a imágenes por defecto si no hay imagen configurada -->
                        @if($planName === 'explorer')
                            <img src="{{ asset('assets/images/img_plan_explorer.png') }}" alt="Plan Explorer" class="w-20 h-20">
                        @elseif($planName === 'master')
                            <img src="{{ asset('assets/images/img_plan_master.png') }}" alt="Plan Master" class="w-20 h-20">
                        @else
                            <img src="{{ asset('assets/images/img_plan_legend.png') }}" alt="Plan Legend" class="w-20 h-20">
                        @endif
                    @endif
                </div>
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

<script>
function sidebarSections() {
    return {
        sections: {
            favoritos: true,  // Favoritos siempre abierto
            tienda: false,
            marketing: false,
            perfil: false,
            soporte: false
        },
        
        init() {
            // Cargar estado del localStorage
            const saved = localStorage.getItem('sidebarSections');
            if (saved) {
                this.sections = { ...this.sections, ...JSON.parse(saved) };
            }
            
            // Auto-expandir sección activa
            this.expandActiveSection();
        },
        
        toggleSection(section) {
            this.sections[section] = !this.sections[section];
            this.saveState();
        },
        
        saveState() {
            localStorage.setItem('sidebarSections', JSON.stringify(this.sections));
        },
        
        expandActiveSection() {
            const currentRoute = window.location.pathname;
            
            // Mapear rutas a secciones
            if (currentRoute.includes('/categories') || currentRoute.includes('/variables') || 
                currentRoute.includes('/products') || currentRoute.includes('/simple-shipping')) {
                this.sections.tienda = true;
            } else if (currentRoute.includes('/coupons') || currentRoute.includes('/sliders')) {
                this.sections.marketing = true;
            } else if (currentRoute.includes('/business-profile') || currentRoute.includes('/store-design') || 
                      currentRoute.includes('/billing')) {
                this.sections.perfil = true;
            } else if (currentRoute.includes('/tickets') || currentRoute.includes('/announcements')) {
                this.sections.soporte = true;
            }
            
            this.saveState();
        }
    }
}
</script> 