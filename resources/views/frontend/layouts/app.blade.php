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
                             class="w-24 h-24 object-contain">
                    @endif
                </div>
            </div>
            
            <!-- Badge Verificado -->
            <div class="mb-4" x-data="verificationBadge" x-init="startPolling()">
                @if($store->verified)
                    <div x-show="verified" class="inline-flex items-center text-black-400 bg-success-200 px-4 py-1 rounded-full text-sm font-semibold">
                        <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                        Verificado
                    </div>
                @else
                    <div x-show="!verified" class="inline-flex items-center text-black-300 bg-white-200 px-4 py-1 rounded-full text-sm font-semibold">
                        <x-solar-close-circle-outline class="w-4 h-4 mr-2" />
                        No Verificado
                    </div>
                @endif
            </div>
            
            <!-- Nombre de la tienda -->
            <h1 class="text-xl font-black" style="color: {{ $store->design && $store->design->is_published ? $store->design->header_text_color : '#ffffff' }}">{{ $store->name ?? 'Linkiu Rest' }}</h1>
            
            <!-- Descripción -->
            <p class="text-base font-semibold" style="color: {{ $store->design && $store->design->is_published ? $store->design->header_description_color : '#e9d5ff' }}">{{ $store->description ?? 'Comidas Rápidas en Sincelejo' }}</p>
        </div>
    </header>
    <!-- Menu inferior -->
    <nav class="w-full max-w-[480px] bg-white-50 rounded-b-3xl px-4 py-4 shadow-3xl">
        <div class="flex justify-around items-center">
            <!-- Contacto -->
            <a href="{{ route('tenant.contact', $store->slug) }}" 
               class="flex flex-col items-center py-2 px-3 {{ request()->routeIs('tenant.contact') ? 'text-white bg-purple-600 rounded-2xl' : 'text-gray-500 hover:text-purple-600' }} transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <span class="text-xs">Contacto</span>
            </a>
            
            <!-- Menú -->
            <a href="{{ route('tenant.categories', $store->slug) }}" 
               class="flex flex-col items-center py-2 px-3 {{ request()->routeIs('tenant.categories', 'tenant.category') ? 'text-white bg-purple-600 rounded-2xl' : 'text-gray-500 hover:text-purple-600' }} transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <span class="text-xs">Categorías</span>
            </a>
            
            <!-- Inicio -->
            <a href="{{ route('tenant.home', $store->slug) }}" 
               class="flex flex-col items-center py-2 px-3 {{ request()->routeIs('tenant.home') ? 'text-white bg-purple-600 rounded-2xl' : 'text-gray-500 hover:text-purple-600' }} transition-colors">
                <svg class="w-6 h-6 mb-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path>
                </svg>
                <span class="text-xs">Inicio</span>
            </a>
            
            <!-- Promos -->
            <a href="{{ route('tenant.promotions', $store->slug) }}" 
               class="flex flex-col items-center py-2 px-3 {{ request()->routeIs('tenant.promotions') ? 'text-white bg-purple-600 rounded-2xl' : 'text-gray-500 hover:text-purple-600' }} transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="text-xs">Promos</span>
            </a>
            
            <!-- Favoritos -->
            <a href="#" class="flex flex-col items-center py-2 px-3 text-gray-500 hover:text-purple-600 transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-xs">Favoritos</span>
            </a>
        </div>
    </nav>
    
    <!-- Contenido principal -->
    <main class="min-h-screen pb-20">
        @yield('content')
    </main>
    <!-- Carrito flotante -->
    <x-cart-float :store="$store" />
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
    
    
    
    @stack('scripts')
    
</body>
</html> 