@extends('frontend.layouts.app')

@section('content')
<div class="px-6 py-8">
    <!-- Contenido principal del home -->
    <div class="space-y-6">
        <!-- Mensaje de bienvenida -->
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                ¡Bienvenido a {{ $store->name }}!
            </h2>
            <p class="text-gray-600">
                {{ $store->description ?? 'Descubre nuestros productos y servicios' }}
            </p>
        </div>
        
        <!-- Sección de productos destacados -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Productos Destacados</h3>
            <div class="text-center text-gray-500 py-8">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p>Próximamente productos disponibles</p>
            </div>
        </div>
        
        <!-- Sección de categorías -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Categorías</h3>
            <div class="text-center text-gray-500 py-8">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p>Categorías próximamente</p>
            </div>
        </div>
        
        <!-- Información de contacto -->
        @if($store->email || $store->phone)
        <div class="bg-purple-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-purple-800 mb-4 text-center">
                ¿Necesitas ayuda?
            </h3>
            <div class="flex flex-col gap-3">
                @if($store->email)
                    <a href="mailto:{{ $store->email }}" 
                       class="flex items-center justify-center gap-2 bg-white text-purple-700 py-3 px-4 rounded-lg hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ $store->email }}
                    </a>
                @endif
                
                @if($store->phone)
                    <a href="tel:{{ $store->phone }}" 
                       class="flex items-center justify-center gap-2 bg-white text-purple-700 py-3 px-4 rounded-lg hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        {{ $store->phone }}
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection 