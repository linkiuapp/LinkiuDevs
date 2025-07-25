@extends('frontend.layouts.app')

@section('content')
<div class="p-4 space-y-6">
    <!-- Breadcrumbs -->
    <nav class="flex flex-wrap items-center text-sm text-black-300 gap-1">
        @foreach($breadcrumbs as $index => $breadcrumb)
            @if($breadcrumb['url'])
                <a href="{{ $breadcrumb['url'] }}" class="hover:text-primary-400 transition-colors">
                    {{ $breadcrumb['name'] }}
                </a>
                @if($index < count($breadcrumbs) - 1)
                    <span class="mx-1">/</span>
                @endif
            @else
                <span class="text-black-400 font-medium">{{ $breadcrumb['name'] }}</span>
            @endif
        @endforeach
    </nav>

    <!-- Header de la categoría -->
    <div class="space-y-3">
        <div class="flex items-center space-x-3">
            <!-- Icono de la categoría -->
            @if($category->icon && $category->icon->image_path)
                <div class="w-12 h-12 bg-white-100 rounded-lg p-2 flex items-center justify-center">
                    <img src="{{ $category->icon->image_url }}" 
                         alt="{{ $category->name }}" 
                         class="w-full h-full object-contain">
                </div>
            @endif
            
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-black-500">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="text-black-300 mt-1">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Subcategorías -->
    @if($subcategories->count() > 0)
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-black-500">Subcategorías</h2>
            
            <div class="space-y-2">
                @foreach($subcategories as $subcategory)
                                         <a href="{{ route('tenant.category', [$store->slug, $subcategory->slug]) }}" 
                       class="block bg-white-50 rounded-lg p-3 border border-white-200 hover:border-primary-200 hover:shadow-sm transition-all duration-200">
                        
                        <div class="flex items-center space-x-3">
                            <!-- Icono de subcategoría -->
                            <div class="w-10 h-10 bg-white-100 rounded-lg p-2 flex items-center justify-center flex-shrink-0">
                                @if($subcategory->icon && $subcategory->icon->image_path)
                                    <img src="{{ $subcategory->icon->image_url }}" 
                                         alt="{{ $subcategory->name }}" 
                                         class="w-full h-full object-contain">
                                @else
                                    <svg class="w-5 h-5 text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                @endif
                            </div>
                            
                            <!-- Info de subcategoría -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-black-500 truncate">{{ $subcategory->name }}</h3>
                                @if($subcategory->description)
                                    <p class="text-sm text-black-300 truncate">{{ $subcategory->description }}</p>
                                @endif
                            </div>
                            
                            <!-- Flecha -->
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Productos -->
    @if($products->count() > 0)
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-black-500">
                Productos 
                <span class="text-sm font-normal text-black-300">({{ $products->count() }})</span>
            </h2>
            
            <div class="space-y-3">
                @foreach($products as $product)
                    <div class="bg-white-50 rounded-xl border border-white-200 overflow-hidden hover:shadow-sm transition-shadow duration-200">
                        <div class="flex">
                            <!-- Imagen del producto -->
                            <div class="w-24 h-24 bg-white-100 flex-shrink-0">
                                @if($product->images->count() > 0)
                                    <img src="{{ $product->images->first()->url }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Info del producto -->
                            <div class="flex-1 p-4 min-w-0">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 min-w-0 pr-3">
                                        <h3 class="font-semibold text-black-500 truncate">{{ $product->name }}</h3>
                                        
                                        @if($product->description)
                                            <p class="text-sm text-black-300 mt-1 line-clamp-2">{{ $product->description }}</p>
                                        @endif
                                        
                                        <!-- Categorías del producto -->
                                        @if($product->categories->count() > 1)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($product->categories->take(2) as $cat)
                                                    <span class="text-xs bg-primary-100 text-primary-400 px-2 py-1 rounded-full">
                                                        {{ $cat->name }}
                                                    </span>
                                                @endforeach
                                                @if($product->categories->count() > 2)
                                                    <span class="text-xs text-black-300">+{{ $product->categories->count() - 2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Precio -->
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-lg font-bold text-primary-400">
                                            ${{ number_format($product->price, 0, ',', '.') }}
                                        </p>
                                        
                                        <!-- Botón de acción -->
                                        <button class="mt-2 px-3 py-1 bg-primary-400 text-white-50 text-sm rounded-lg hover:bg-primary-300 transition-colors">
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Estado: Sin productos -->
        @if($subcategories->count() == 0)
            <div class="text-center py-12 space-y-4">
                <div class="w-16 h-16 bg-black-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-black-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-black-400">No hay productos en esta categoría</h3>
                    <p class="text-black-300 max-w-sm mx-auto">
                        Aún no tenemos productos disponibles en esta categoría. 
                        Explora otras categorías o regresa pronto.
                    </p>
                </div>
                                 <div class="flex flex-col sm:flex-row gap-3 justify-center">
                     <a href="{{ route('tenant.categories', $store->slug) }}" 
                        class="inline-flex items-center px-4 py-2 bg-primary-400 text-white-50 rounded-lg hover:bg-primary-300 transition-colors">
                         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                         </svg>
                         Ver categorías
                     </a>
                     <a href="{{ route('tenant.home', $store->slug) }}" 
                        class="inline-flex items-center px-4 py-2 bg-white-200 text-black-400 rounded-lg hover:bg-white-300 transition-colors">
                         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                         </svg>
                         Ir al inicio
                     </a>
                 </div>
            </div>
        @endif
    @endif
</div>
@endsection 