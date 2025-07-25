@extends('frontend.layouts.app')

@section('content')
<div class="p-4 space-y-6">
    <!-- Header con breadcrumbs -->
    <div class="space-y-3">
        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-black-300">
            <a href="{{ route('tenant.home', $store->slug) }}" class="hover:text-primary-400 transition-colors">Inicio</a>
            <span class="mx-2">/</span>
            <span class="text-black-400 font-medium">Categorías</span>
        </nav>
        
        <!-- Title -->
        <h1 class="text-2xl font-bold text-black-500">Categorías</h1>
        <p class="text-black-300">Explora nuestras categorías de productos</p>
    </div>

    @if($categories->count() > 0)
        <!-- Lista de categorías -->
        <div class="space-y-3">
            @foreach($categories as $category)
                <a href="{{ route('tenant.category', [$store->slug, $category->slug]) }}" 
                   class="block bg-white-50 rounded-xl p-4 border border-white-200 hover:border-primary-200 hover:shadow-md transition-all duration-200">
                    
                    <div class="flex items-center space-x-4">
                        <!-- Icono de la categoría -->
                        <div class="w-12 h-12 bg-white-100 rounded-lg p-2 flex items-center justify-center flex-shrink-0">
                            @if($category->icon && $category->icon->image_path)
                                <img src="{{ $category->icon->image_url }}" 
                                     alt="{{ $category->name }}" 
                                     class="w-full h-full object-contain">
                            @else
                                <!-- Icono por defecto -->
                                <svg class="w-6 h-6 text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            @endif
                        </div>

                        <!-- Información de la categoría -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-black-500 truncate">{{ $category->name }}</h3>
                            
                            @if($category->description)
                                <p class="text-sm text-black-300 mt-1 line-clamp-2">{{ $category->description }}</p>
                            @endif

                            <!-- Subcategorías info -->
                            @if($category->children->count() > 0)
                                <div class="flex items-center mt-2 text-xs text-primary-400">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <span>{{ $category->children->count() }} subcategoría{{ $category->children->count() !== 1 ? 's' : '' }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Flecha derecha -->
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <!-- Estado vacío -->
        <div class="text-center py-12 space-y-4">
            <div class="w-16 h-16 bg-black-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div class="space-y-2">
                <h3 class="text-lg font-semibold text-black-400">No hay categorías disponibles</h3>
                <p class="text-black-300 max-w-sm mx-auto">
                    Por el momento no tenemos categorías configuradas. 
                    ¡Regresa pronto para ver nuestros productos!
                </p>
            </div>
            <a href="{{ route('tenant.home', $store->slug) }}" 
               class="inline-flex items-center px-4 py-2 bg-primary-400 text-white-50 rounded-lg hover:bg-primary-300 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Ir al inicio
            </a>
        </div>
    @endif
</div>
@endsection 