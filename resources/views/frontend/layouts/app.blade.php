<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $store->name ?? 'Linkiu Store' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if($store->design && $store->design->is_published && $store->design->favicon_url)
        <link rel="icon" type="image/x-icon" href="{{ $store->design->favicon_url }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white-100 max-w-[480px] mx-auto">

    <!-- Header -->
    <header class="relative overflow-hidden" style="background: {{ $store->design && $store->design->is_published ? $store->design->header_background_color : '' }}">
        <div class="px-6 py-8 text-center">

            <!-- Logo -->
            <div class="mb-4">
                <div class="rounded-full mx-auto flex items-center justify-center">
                    @if($store->design && $store->design->is_published && $store->design->logo_url)
                        <img src="{{ $store->design->logo_url }}" 
                             alt="Logo" 
                             class="w-27 h-27 object-contain">
                    @endif
                </div>
            </div>

            <!-- Badge Verificado -->
            <div class="mb-2" x-data="verificationBadge" x-init="startPolling()">
                @if($store->verified)
                    <div x-show="verified" class="inline-flex items-center bg-success-50 border border-success-300 text-success-400 px-2 py-1 rounded-full text-small font-regular">
                        <x-lucide-badge-check class="w-4 h-4 mr-2" />
                        Tienda Verificada
                    </div>
                @else
                    <div x-show="!verified" class="inline-flex items-center bg-secondary-50 border border-secondary-300 text-secondary-400 px-2 py-1 rounded-full text-small font-regular">
                        <x-lucide-shield-off class="w-4 h-4 mr-2" />
                        Tienda No Verificada
                    </div>
                @endif
            </div>

            <!-- Nombre de la tienda -->
            <h1 class="text-h5 font-bold mb-2" style="color: {{ $store->design && $store->design->is_published ? $store->design->header_text_color : '#ffffff' }}">
                {{ $store->name ?? 'Linkiu Rest' }}
            </h1>

            <!-- Descripción -->
            <p class="text-body-regular font-medium" style="color: {{ $store->design && $store->design->is_published ? $store->design->header_description_color : '#e9d5ff' }}">
                {{ $store->description ?? 'Comidas Rápidas en Sincelejo' }}
            </p>
        </div>
    </header>

    <!-- Menu inferior -->
    <nav class="w-full max-w-[480px] bg-accent-50 rounded-b-3xl px-4 py-4">
        <div class="flex justify-around items-center">

            <!-- Contacto -->
            <a href="{{ route('tenant.contact', $store->slug) }}" 
               class="flex flex-col items-center py-3 px-4 {{ request()->routeIs('tenant.contact') ? 'text-accent-75 bg-primary-300 rounded-xl' : 'text-secondary-300 hover:text-secondary-300 hover:bg-accent-100 hover:rounded-xl' }} transition-colors">
                <x-lucide-store class="w-6 h-6 mb-1" />
                <span class="text-small font-regular">Sedes</span>
            </a>

            <!-- Menú -->
            <a href="{{ route('tenant.categories', $store->slug) }}" 
               class="flex flex-col items-center py-3 px-4 {{ request()->routeIs('tenant.categories', 'tenant.category') ? 'text-accent-75 bg-primary-300 rounded-xl' : 'text-secondary-300 hover:text-secondary-300 hover:bg-accent-100 hover:rounded-xl' }} transition-colors">
                <x-lucide-shopping-basket class="w-6 h-6 mb-1" />
                <span class="text-small font-regular">Categorías</span>
            </a>

            <!-- Inicio -->
            <a href="{{ route('tenant.home', $store->slug) }}" 
               class="flex flex-col items-center py-3 px-4 {{ request()->routeIs('tenant.home') ? 'text-accent-75 bg-primary-300 rounded-xl' : 'text-secondary-300 hover:text-secondary-300 hover:bg-accent-100 hover:rounded-xl' }} transition-colors">
                <x-lucide-home class="w-6 h-6 mb-1" />
                <span class="text-small font-regular">Inicio</span>
            </a>

            <!-- Promos -->
            <a href="{{ route('tenant.promotions', $store->slug) }}" 
               class="flex flex-col items-center py-3 px-4 {{ request()->routeIs('tenant.promotions') ? 'text-accent-75 bg-primary-300 rounded-xl' : 'text-secondary-300 hover:text-secondary-300 hover:bg-accent-100 hover:rounded-xl' }} transition-colors">
                <x-lucide-badge-percent class="w-6 h-6 mb-1" />
                <span class="text-small font-regular">Promos</span>
            </a>

            <!-- Favoritos -->
            <a href="#" class="flex flex-col items-center py-3 px-4 {{ request()->routeIs('tenant.favorites') ? 'text-accent-75 bg-primary-300 rounded-xl' : 'text-secondary-300 hover:text-secondary-300 hover:bg-accent-100 hover:rounded-xl' }} transition-colors">
                <x-lucide-heart class="w-6 h-6 mb-1" />
                <span class="text-small font-regular">Favoritos</span>
            </a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="min-h-screen pb-20">
        @yield('content')
    </main>

    <script>
        function verificationBadge() {
            return {
                verified: {{ $store->verified ? 'true' : 'false' }},

                startPolling() {
                    // Consultar cada 3 segundos
                    setInterval(() => {
                        this.checkVerificationStatus();
                    }, 3000);
                },

                async checkVerificationStatus() {
                    try {
                        const response = await fetch('{{ route("tenant.verification-status", $store->slug) }}');
                        const data = await response.json();
                        this.verified = data.verified;
                    } catch (error) {
                        console.log('Error checking verification status:', error);
                    }
                }
            }
        }
    </script>

    <!-- Carrito flotante (no en checkout) -->
    @unless(request()->routeIs('tenant.checkout.create'))
        <x-cart-float :store="$store" />
    @endunless

    @stack('scripts')

</body>
</html> 