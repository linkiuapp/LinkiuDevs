@extends('frontend.layouts.app')

@section('content')
<div class="p-4 space-y-6">
    <!-- Header -->
    <div class="space-y-3">
        <nav class="flex text-small font-regular text-info-300">
            <a href="{{ route('tenant.home', $store->slug) }}" class="hover:text-info-200 transition-colors">Inicio</a>
            <span class="mx-2">/</span>
            <span class="text-secondary-300 font-medium">Catálogo</span>
        </nav>
        
        <div class="space-y-2">
            <h1 class="text-h7 font-bold text-black-300">Catálogo de Productos</h1>
            <p class="text-body-small font-regular text-black-200">Encuentra todos nuestros productos</p>
        </div>
    </div>

    <!-- Buscador Bonito -->
    <div class="bg-accent-50 rounded-2xl p-6 border border-accent-200 shadow-sm">
        <form method="GET" action="{{ route('tenant.catalog', $store->slug) }}" class="space-y-4">
            <!-- Input de búsqueda principal -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <x-solar-minimalistic-magnifer-outline class="h-5 w-5 text-black-300" />
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Buscar productos, categorías o SKU..."
                       class="w-full pl-12 pr-4 py-3 bg-accent-100 border border-accent-200 rounded-xl text-black-400 placeholder-black-300 focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent transition-all">
            </div>

            <!-- Filtros en una fila -->
            <div class="flex flex-wrap gap-3 items-center">
                <!-- Filtro por categoría -->
                <select name="category" 
                        class="px-3 py-2 bg-accent-100 border border-accent-200 rounded-lg text-sm text-black-400 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Ordenar por -->
                <select name="sort" 
                        class="px-3 py-2 bg-accent-100 border border-accent-200 rounded-lg text-sm text-black-400 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Alfabético</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más nuevos</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio: menor a mayor</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio: mayor a menor</option>
                </select>

                <!-- Botón buscar -->
                <button type="submit" 
                        class="px-4 py-2 bg-primary-300 text-accent-50 rounded-lg hover:bg-primary-200 transition-colors flex items-center gap-2">
                    <x-solar-minimalistic-magnifer-outline class="w-4 h-4" />
                    Buscar
                </button>

                <!-- Botón limpiar -->
                @if(request()->hasAny(['search', 'category', 'sort']))
                    <a href="{{ route('tenant.catalog', $store->slug) }}" 
                       class="px-4 py-2 bg-secondary-300 text-accent-50 rounded-lg hover:bg-secondary-200 transition-colors flex items-center gap-2">
                        <x-solar-close-circle-outline class="w-4 h-4" />
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Resultados -->
    @if(request('search') || request('category'))
        <div class="bg-info-50 border border-info-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <x-solar-info-circle-outline class="w-5 h-5 text-info-300 flex-shrink-0" />
                <p class="text-sm text-info-300">
                    @if(request('search'))
                        Resultados para "<strong>{{ request('search') }}</strong>"
                        @if(request('category'))
                            en categoría "{{ $categories->find(request('category'))->name ?? 'N/A' }}"
                        @endif
                    @elseif(request('category'))
                        Mostrando productos de "{{ $categories->find(request('category'))->name ?? 'N/A' }}"
                    @endif
                    - {{ $products->total() }} producto(s) encontrado(s)
                </p>
            </div>
        </div>
    @endif

    <!-- Grid de Productos -->
    @if($products->count() > 0)
        <div class="grid grid-cols-1 gap-4">
            @foreach($products as $product)
                <div class="bg-accent-50 rounded-xl p-4 border border-accent-200 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <!-- Imagen del producto -->
                        <div class="w-20 h-20 bg-accent-100 rounded-xl flex-shrink-0 overflow-hidden">
                            @if($product->main_image_url)
                                <img src="{{ $product->main_image_url }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-black-200">
                                    <x-solar-gallery-outline class="w-8 h-8" />
                                </div>
                            @endif
                        </div>

                        <!-- Información del producto -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-black-400 text-base mb-1">{{ $product->name }}</h3>
                            
                            @if($product->description)
                                <p class="text-sm text-black-300 mb-2 line-clamp-2">{{ $product->description }}</p>
                            @endif

                            <!-- Categorías -->
                            @if($product->categories->count() > 0)
                                <div class="flex flex-wrap gap-1 mb-2">
                                    @foreach($product->categories->take(3) as $category)
                                        <span class="px-2 py-1 bg-accent-200 text-black-300 rounded-full text-xs">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                    @if($product->categories->count() > 3)
                                        <span class="px-2 py-1 bg-accent-200 text-black-300 rounded-full text-xs">
                                            +{{ $product->categories->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Precio -->
                            <div class="text-xl font-bold text-primary-300">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </div>

                            @if($product->sku)
                                <p class="text-xs text-black-200 mt-1">SKU: {{ $product->sku }}</p>
                            @endif
                        </div>

                        <!-- Botón agregar al carrito -->
                        <div class="flex-shrink-0">
                            <button class="add-to-cart-btn bg-secondary-300 hover:bg-secondary-200 text-accent-50 w-12 h-12 rounded-full flex items-center justify-center transition-colors" 
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}"
                                    data-product-image="{{ $product->main_image_url }}">
                                <x-solar-add-circle-outline class="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        @if($products->hasPages())
            <div class="flex justify-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <!-- Estado vacío -->
        <div class="text-center py-12 space-y-4">
            <div class="w-20 h-20 bg-accent-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <x-solar-box-outline class="w-10 h-10 text-black-200" />
            </div>
            <h3 class="text-lg font-semibold text-black-400">
                @if(request()->hasAny(['search', 'category']))
                    No encontramos productos
                @else
                    No hay productos disponibles
                @endif
            </h3>
            <p class="text-black-300 max-w-sm mx-auto">
                @if(request()->hasAny(['search', 'category']))
                    Intenta con otros términos de búsqueda o revisa todas las categorías.
                @else
                    Esta tienda aún no tiene productos disponibles.
                @endif
            </p>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('tenant.catalog', $store->slug) }}" 
                   class="inline-flex items-center mt-4 px-4 py-2 bg-primary-300 text-accent-50 rounded-lg text-sm hover:bg-primary-200 transition-colors">
                    <x-solar-refresh-outline class="w-4 h-4 mr-2" />
                    Ver todos los productos
                </a>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-submit al cambiar filtros
    document.querySelector('select[name="category"]').addEventListener('change', function() {
        this.form.submit();
    });
    
    document.querySelector('select[name="sort"]').addEventListener('change', function() {
        this.form.submit();
    });

    // Limpiar input de búsqueda con Escape
    document.querySelector('input[name="search"]').addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.form.submit();
        }
    });
</script>
@endpush
@endsection
