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
        
        <div class="space-y-1">
            <h1 class="text-lg font-semibold text-black-300">Catálogo</h1>
            <p class="text-sm text-black-200">Encuentra todos nuestros productos</p>
        </div>
    </div>

    <!-- Buscador con Auto-resultados -->
    <div class="bg-accent-50 rounded-xl p-4">
        <form method="GET" action="{{ route('tenant.catalog', $store->slug) }}">
            <!-- Input con autocomplete -->
            <div class="relative" id="search-container">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-solar-minimalistic-magnifer-outline class="h-4 w-4 text-black-300" />
                </div>
                <input type="text" 
                       name="search" 
                       id="search-input"
                       value="{{ request('search') }}"
                       placeholder="Buscar productos..."
                       autocomplete="off"
                       class="w-full pl-10 pr-3 py-2.5 bg-accent-100 border border-accent-200 rounded-lg text-sm text-black-400 placeholder-black-300 focus:outline-none focus:ring-1 focus:ring-primary-200 focus:border-transparent transition-all">
                
                <!-- Botón limpiar solo si hay búsqueda -->
                @if(request('search'))
                    <button type="button" 
                            onclick="clearSearch()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <x-solar-close-circle-outline class="h-4 w-4 text-black-300 hover:text-black-400" />
                    </button>
                @endif

                <!-- Dropdown de resultados -->
                <div id="search-results" 
                     class="absolute top-full left-0 right-0 mt-1 bg-white border border-accent-200 rounded-lg shadow-lg z-10 hidden max-h-64 overflow-y-auto">
                </div>
            </div>
        </form>
    </div>

    <!-- Grid de Categorías -->
    <div class="space-y-3">
        <h2 class="text-base font-semibold text-black-400">Categorías</h2>
        
        @if($categories->count() > 0)
            <div class="grid grid-cols-4 gap-3">
                @foreach($categories as $category)
                    <a href="{{ route('tenant.category', ['store' => $store->slug, 'categorySlug' => $category->slug]) }}" 
                       class="flex flex-col items-center group">
                        
                        <!-- Icono de la categoría con fondo colorido -->
                        <div class="w-16 h-16 mb-2 flex items-center justify-center rounded-2xl bg-gradient-to-br from-accent-100 to-accent-200 group-hover:from-primary-50 group-hover:to-primary-100 transition-all duration-200 shadow-sm group-hover:shadow-md">
                             @if($category->icon && $category->icon->image_url)
                                 <img src="{{ $category->icon->image_url }}" 
                                      alt="{{ $category->name }}" 
                                      class="w-10 h-10 object-contain">
                             @else
                                 <x-solar-gallery-outline class="w-8 h-8 text-black-300 group-hover:text-primary-300" />
                             @endif
                        </div>

                        <!-- Nombre de la categoría -->
                        <span class="text-xs text-center text-black-400 font-medium group-hover:text-primary-300 transition-colors leading-tight">
                            {{ $category->name }}
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-black-300">
                <x-solar-gallery-outline class="w-10 h-10 mx-auto mb-2 text-black-200" />
                <p class="text-sm">No hay categorías disponibles</p>
            </div>
        @endif
    </div>

    <!-- Resultados -->
    @if(request('search'))
        <div class="bg-info-50 border border-info-200 rounded-lg p-3">
            <p class="text-xs text-info-300">
                Resultados para "<strong>{{ request('search') }}</strong>" - {{ $products->total() }} productos
            </p>
        </div>
    @endif

    <!-- Grid de Productos -->
    @if($products->count() > 0)
        <div class="grid grid-cols-1 gap-3">
            @foreach($products as $product)
                <a href="{{ route('tenant.product', [$store->slug, $product->slug]) }}" 
                   class="bg-white rounded-lg p-3 border border-accent-200 hover:border-primary-200 hover:shadow-sm transition-all duration-200 block">
                    <div class="flex items-center gap-3">
                        <!-- Imagen del producto -->
                        <div class="w-16 h-16 bg-accent-100 rounded-lg flex-shrink-0 overflow-hidden">
                            @if($product->main_image_url)
                                <img src="{{ $product->main_image_url }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-black-200">
                                    <x-solar-gallery-outline class="w-6 h-6" />
                                </div>
                            @endif
                        </div>

                        <!-- Información del producto -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-black-400 text-sm mb-1 line-clamp-1">{{ $product->name }}</h3>
                            
                            @if($product->description)
                                <p class="text-xs text-black-300 mb-2 line-clamp-1">{{ $product->description }}</p>
                            @endif

                            <!-- Precio prominente -->
                            <div class="text-base font-bold text-primary-300 mb-1">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </div>

                            <!-- Categorías pequeñas -->
                            @if($product->categories->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($product->categories->take(2) as $category)
                                        <span class="px-2 py-0.5 bg-primary-50 text-primary-300 rounded text-xs">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                    @if($product->categories->count() > 2)
                                        <span class="text-xs text-black-200">+{{ $product->categories->count() - 2 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Botón agregar al carrito -->
                        <div class="flex-shrink-0">
                            <button class="add-to-cart-btn bg-secondary-300 hover:bg-secondary-200 text-white w-10 h-10 rounded-lg flex items-center justify-center transition-colors" 
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-price="{{ $product->price }}"
                                    data-product-image="{{ $product->main_image_url }}"
                                    onclick="event.preventDefault(); event.stopPropagation();">
                                <x-solar-cart-plus-outline class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </a>
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
        <div class="text-center py-8 space-y-3">
            <div class="w-16 h-16 bg-accent-100 rounded-full flex items-center justify-center mx-auto">
                <x-solar-box-outline class="w-8 h-8 text-black-200" />
            </div>
            <h3 class="text-base font-medium text-black-400">
                @if(request('search'))
                    No encontramos productos
                @else
                    No hay productos disponibles
                @endif
            </h3>
            <p class="text-sm text-black-300 max-w-sm mx-auto">
                @if(request('search'))
                    Intenta con otros términos de búsqueda.
                @else
                    Esta tienda aún no tiene productos.
                @endif
            </p>
            @if(request('search'))
                <a href="{{ route('tenant.catalog', $store->slug) }}" 
                   class="inline-flex items-center mt-3 px-3 py-2 bg-primary-300 text-white rounded-lg text-sm hover:bg-primary-200 transition-colors">
                    <x-solar-refresh-outline class="w-4 h-4 mr-1" />
                    Ver todos
                </a>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
    let searchTimeout;
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');

    function clearSearch() {
        searchInput.value = '';
        searchResults.classList.add('hidden');
        searchInput.form.submit();
    }

    function performSearch(query) {
        if (query.length < 3) {
            searchResults.classList.add('hidden');
            return;
        }

        fetch(`{{ route('tenant.search.api', $store->slug) }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(products => {
                if (products.length === 0) {
                    searchResults.classList.add('hidden');
                    return;
                }

                const html = products.map(product => `
                    <a href="${product.url}" class="flex items-center gap-3 p-3 hover:bg-accent-50 transition-colors border-b border-accent-100 last:border-b-0">
                        <div class="w-12 h-12 bg-accent-100 rounded-lg overflow-hidden flex-shrink-0">
                            ${product.image ? 
                                `<img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover">` :
                                `<div class="w-full h-full flex items-center justify-center text-black-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>`
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-sm text-black-400 line-clamp-1">${product.name}</h4>
                            <p class="text-sm font-bold text-primary-300">$${product.price}</p>
                        </div>
                    </a>
                `).join('');

                searchResults.innerHTML = html;
                searchResults.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error en búsqueda:', error);
                searchResults.classList.add('hidden');
            });
    }

    // Eventos del input
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
        if (e.key === 'Escape') {
            clearSearch();
        }
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!document.getElementById('search-container').contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection

