@extends('frontend.layouts.app')

@section('content')
<div class="px-4 py-6 space-y-6">
    <!-- Breadcrumbs -->
    <nav class="flex flex-wrap items-center text-sm text-black-300 gap-1">
        @foreach($breadcrumbs as $index => $breadcrumb)
            @if($breadcrumb['url'])
                <a href="{{ $breadcrumb['url'] }}" class="hover:text-primary-300 transition-colors">
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
            @if($category->icon && $category->icon->image_url)
                <div class="w-12 h-12 bg-white-100 rounded-lg p-2 flex items-center justify-center">
                    <img src="{{ $category->icon->image_url }}" 
                         alt="{{ $category->name }}" 
                         class="w-full h-full object-contain">
                </div>
            @endif
            
            <div class="flex-1">
                <h1 class="text-lg font-bold text-black-400">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="text-black-300 mt-1 text-sm">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Subcategorías -->
    @if($subcategories->count() > 0)
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-black-400">Subcategorías</h2>
            
            <div class="grid grid-cols-2 gap-3">
                @foreach($subcategories as $subcategory)
                    <a href="{{ route('tenant.category', [$store->slug, $subcategory->slug]) }}" 
                       class="flex flex-col items-center p-3 bg-white-50 rounded-lg border border-white-200 hover:shadow-sm hover:border-primary-200 transition-all group">
                        
                        <!-- Icono de subcategoría -->
                        <div class="w-10 h-10 mb-2 flex items-center justify-center rounded-lg bg-white-100 group-hover:bg-primary-50 transition-colors">
                            @if($subcategory->icon && $subcategory->icon->image_url)
                                <img src="{{ $subcategory->icon->image_url }}" 
                                     alt="{{ $subcategory->name }}" 
                                     class="w-6 h-6 object-contain">
                            @else
                                <x-solar-gallery-outline class="w-5 h-5 text-black-300 group-hover:text-primary-300" />
                            @endif
                        </div>
                        
                        <!-- Nombre de subcategoría -->
                        <span class="text-xs text-center text-black-400 font-medium group-hover:text-primary-300 transition-colors">
                            {{ $subcategory->name }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Productos -->
    @if($products->count() > 0)
        <div>
            <h2 class="text-lg font-bold text-black-400 mb-4">
                Productos
                <span class="text-sm font-normal text-black-300">({{ $products->count() }})</span>
            </h2>
            
            <div class="space-y-3">
                @foreach($products as $product)
                    <div class="bg-white-50 rounded-lg p-3 flex items-center gap-3 border border-white-200 hover:shadow-sm transition-shadow relative">
                        <!-- Imagen del producto -->
                        <div class="w-16 h-16 bg-white-100 rounded-lg flex-shrink-0 overflow-hidden">
                            @if($product->mainImage)
                                <img src="{{ $product->main_image_url }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-16 h-16 object-cover">
                            @else
                                <div class="w-16 h-16 flex items-center justify-center text-black-200">
                                    <x-solar-gallery-outline class="w-6 h-6" />
                                </div>
                            @endif
                        </div>
                        
                        <!-- Información del producto -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-black-400 text-sm">{{ $product->name }}</h3>
                            @if($product->description)
                                <p class="text-xs text-black-300 mt-1 line-clamp-2">{{ $product->description }}</p>
                            @endif
                            
                            <!-- Categorías del producto (solo si tiene más de una) -->
                            @if($product->categories->count() > 1)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($product->categories->where('id', '!=', $category->id)->take(2) as $cat)
                                        <span class="text-xs bg-primary-100 text-primary-300 px-2 py-1 rounded-full">
                                            {{ $cat->name }}
                                        </span>
                                    @endforeach
                                    @if($product->categories->where('id', '!=', $category->id)->count() > 2)
                                        <span class="text-xs text-black-300">+{{ $product->categories->where('id', '!=', $category->id)->count() - 2 }}</span>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="mt-2 text-lg font-bold text-black-500">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </div>
                        </div>
                        
                        <!-- Botón agregar -->
                        <button class="add-to-cart-btn bg-secondary-300 hover:bg-secondary-200 text-white-50 w-8 h-8 rounded-full flex items-center justify-center transition-colors flex-shrink-0" 
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ $product->name }}"
                                data-product-price="{{ $product->price }}"
                                data-product-image="{{ $product->main_image_url }}">
                            <x-solar-add-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Estado: Sin productos -->
        @if($subcategories->count() == 0)
            <div class="text-center py-12 space-y-4">
                <div class="w-16 h-16 bg-white-100 rounded-full flex items-center justify-center mx-auto">
                    <x-solar-box-outline class="w-8 h-8 text-black-200" />
                </div>
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-black-400">No hay productos en esta categoría</h3>
                    <p class="text-black-300 max-w-sm mx-auto text-sm">
                        Aún no tenemos productos disponibles en esta categoría. 
                        Explora otras categorías o regresa pronto.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('tenant.categories', $store->slug) }}" 
                       class="inline-flex items-center px-4 py-2 bg-primary-300 text-white-50 rounded-lg hover:bg-primary-200 transition-colors text-sm">
                        <x-solar-gallery-outline class="w-4 h-4 mr-2" />
                        Ver categorías
                    </a>
                    <a href="{{ route('tenant.home', $store->slug) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white-200 text-black-400 rounded-lg hover:bg-white-300 transition-colors text-sm">
                        <x-solar-home-2-outline class="w-4 h-4 mr-2" />
                        Ir al inicio
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection 