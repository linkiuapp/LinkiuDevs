@extends('frontend.layouts.app')

@section('content')
<div class="p-4 space-y-4">
    <!-- Breadcrumb -->
    <nav class="flex text-xs text-info-300">
        <a href="{{ route('tenant.home', $store->slug) }}" class="hover:text-info-200 transition-colors">Inicio</a>
        <span class="mx-1">/</span>
        <a href="{{ route('tenant.catalog', $store->slug) }}" class="hover:text-info-200 transition-colors">Catálogo</a>
        <span class="mx-1">/</span>
        <span class="text-secondary-300 font-medium">{{ $product->name }}</span>
    </nav>

    <!-- Producto Principal -->
    <div class="bg-white rounded-lg p-4 space-y-4">
        <!-- Imagen Principal -->
        <div class="w-full h-72 bg-accent-100 rounded-xl overflow-hidden mb-3" id="main-image-container">
            @if($product->images->count() > 0)
                <img src="{{ $product->images->first()->image_url }}" 
                     alt="{{ $product->name }}" 
                     id="main-image"
                     class="w-full h-full object-cover transition-all duration-300">
            @elseif($product->main_image_url)
                <img src="{{ $product->main_image_url }}" 
                     alt="{{ $product->name }}" 
                     id="main-image"
                     class="w-full h-full object-cover transition-all duration-300">
            @else
                <div class="w-full h-full flex items-center justify-center text-black-200">
                    <x-solar-gallery-outline class="w-16 h-16" />
                </div>
            @endif
        </div>

        <!-- Galería de miniaturas (solo si hay más de 1 imagen) -->
        @if($product->images->count() > 1)
            <div class="space-y-2">
                <p class="text-xs font-medium text-black-400">Imágenes ({{ $product->images->count() }})</p>
                <div class="flex gap-2 overflow-x-auto pb-2">
                    @foreach($product->images as $index => $image)
                        <div class="w-16 h-16 bg-accent-100 rounded-lg overflow-hidden flex-shrink-0 cursor-pointer border-2 transition-all duration-200 image-thumb {{ $index === 0 ? 'border-primary-300' : 'border-transparent hover:border-primary-200' }}" 
                             onclick="changeMainImage('{{ $image->image_url }}', {{ $index }})">
                            <img src="{{ $image->image_url }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Información del Producto -->
        <div class="space-y-3">
            <!-- Título y precio -->
            <div>
                <h1 class="text-lg font-semibold text-black-400 mb-1">{{ $product->name }}</h1>
                <div class="text-xl font-bold text-primary-300">
                    ${{ number_format($product->price, 0, ',', '.') }}
                </div>
            </div>

            <!-- Categorías -->
            @if($product->categories->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($product->categories as $category)
                        <a href="{{ route('tenant.category', [$store->slug, $category->slug]) }}" 
                           class="flex items-center gap-1 px-2 py-1 bg-primary-50 text-primary-300 rounded-lg text-xs hover:bg-primary-100 transition-colors">
                            @if($category->icon && $category->icon->image_url)
                                <img src="{{ $category->icon->image_url }}" 
                                     alt="{{ $category->name }}" 
                                     class="w-3 h-3 object-contain">
                            @endif
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Descripción -->
            @if($product->description)
                <div class="space-y-2">
                    <h3 class="text-sm font-medium text-black-400">Descripción</h3>
                    <p class="text-sm text-black-300 leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif

            <!-- SKU -->
            @if($product->sku)
                <div class="text-xs text-black-200">
                    SKU: {{ $product->sku }}
                </div>
            @endif
        </div>

        <!-- Botón Agregar al Carrito -->
        <div class="pt-2">
            <button class="w-full bg-secondary-300 hover:bg-secondary-200 text-white py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2 add-to-cart-btn"
                    data-product-id="{{ $product->id }}"
                    data-product-name="{{ $product->name }}"
                    data-product-price="{{ $product->price }}"
                    data-product-image="{{ $product->main_image_url }}">
                <x-solar-cart-plus-outline class="w-5 h-5" />
                Agregar al Carrito
            </button>
        </div>
    </div>

    <!-- Productos Relacionados -->
    @if($relatedProducts->count() > 0)
        <div class="space-y-3">
            <h2 class="text-base font-semibold text-black-400">Productos Relacionados</h2>
            
            <div class="grid grid-cols-2 gap-3">
                @foreach($relatedProducts as $relatedProduct)
                    <a href="{{ route('tenant.product', [$store->slug, $relatedProduct->slug]) }}" 
                       class="bg-white rounded-lg p-3 border border-accent-200 hover:border-primary-200 transition-colors">
                        <!-- Imagen -->
                        <div class="w-full h-24 bg-accent-100 rounded-lg overflow-hidden mb-2">
                            @if($relatedProduct->main_image_url)
                                <img src="{{ $relatedProduct->main_image_url }}" 
                                     alt="{{ $relatedProduct->name }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-black-200">
                                    <x-solar-gallery-outline class="w-6 h-6" />
                                </div>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="space-y-1">
                            <h3 class="text-sm font-medium text-black-400 line-clamp-1">{{ $relatedProduct->name }}</h3>
                            <div class="text-sm font-bold text-primary-300">
                                ${{ number_format($relatedProduct->price, 0, ',', '.') }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function changeMainImage(imageUrl, index) {
        // Cambiar imagen principal
        const mainImage = document.getElementById('main-image');
        if (mainImage) {
            mainImage.style.opacity = '0.7';
            setTimeout(() => {
                mainImage.src = imageUrl;
                mainImage.style.opacity = '1';
            }, 150);
        }

        // Actualizar thumbnails activos
        document.querySelectorAll('.image-thumb').forEach((thumb, i) => {
            if (i === index) {
                thumb.classList.remove('border-transparent', 'hover:border-primary-200');
                thumb.classList.add('border-primary-300');
            } else {
                thumb.classList.remove('border-primary-300');
                thumb.classList.add('border-transparent', 'hover:border-primary-200');
            }
        });
    }

    // Navegación con teclado (opcional)
    document.addEventListener('keydown', function(e) {
        const thumbs = document.querySelectorAll('.image-thumb');
        if (thumbs.length <= 1) return;

        const currentActive = Array.from(thumbs).findIndex(thumb => 
            thumb.classList.contains('border-primary-300'));
        
        if (e.key === 'ArrowRight' && currentActive < thumbs.length - 1) {
            thumbs[currentActive + 1].click();
        } else if (e.key === 'ArrowLeft' && currentActive > 0) {
            thumbs[currentActive - 1].click();
        }
    });
</script>
@endpush
@endsection
