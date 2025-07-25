@extends('frontend.layouts.app')

@section('content')
<div class="px-4 py-6 space-y-6">
    <!-- Slider de Novedades -->
    @if($sliders->count() > 0)
        <div class="slider-container relative" x-data="sliderComponent({{ $sliders->toJson() }}, {{ $sliders->first()->transition_duration ?? 5 }})">
            <!-- Slider principal -->
            <div class="overflow-hidden rounded-lg">
                <div class="flex gap-8 transition-transform duration-500 ease-in-out" 
                     :style="`transform: translateX(-${currentSlide * 33.333}%)`">
                    
                    @foreach($sliders as $index => $slider)
                        <div class="w-1/3 flex-shrink-0 relative flex justify-center px-1">
                            @if($slider->url && $slider->url_type !== 'none')
                                @if($slider->url_type === 'external')
                                    <a href="{{ $slider->url }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="block relative group">
                                @else
                                    <a href="{{ $slider->url_type === 'internal' ? url($store->slug . '/' . ltrim($slider->url, '/')) : '#' }}" 
                                       class="block relative group">
                                @endif
                            @else
                                <div class="block relative group">
                            @endif
                            
                            <!-- Imagen del slider -->
                            <div class="w-[170px] h-[100px] bg-white-100 rounded-lg overflow-hidden relative">
                                @if($slider->image_path)
                                    <img src="{{ Storage::disk('s3')->url($slider->image_path) }}" 
                                         alt="{{ $slider->name }}" 
                                         class="w-[170px] h-[100px] object-cover object-center transition-transform duration-300 group-hover:scale-105">
                                @endif
                                
                                <!-- Overlay suave (solo si tiene enlace) -->
                                @if($slider->url && $slider->url_type !== 'none')
                                    <div class="absolute inset-0 bg-gradient-to-t from-black-500/20 via-transparent to-transparent"></div>
                                @endif
                                
                                <!-- Indicador de enlace -->
                                @if($slider->url && $slider->url_type !== 'none')
                                    <div class="absolute top-1 right-1 bg-white-50/20 backdrop-blur-sm rounded-full p-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                                        <x-solar-arrow-right-outline class="w-2 h-2 text-white-50" />
                                    </div>
                                @endif
                            </div>
                            
                            @if($slider->url && $slider->url_type !== 'none')
                                </a>
                            @else
                                </div>
                            @endif
                        </div>
                    @endforeach
                    
                    <!-- Duplicar los primeros slides para efecto infinito -->
                    @if($sliders->count() > 1)
                        @foreach($sliders->take(3) as $index => $slider)
                            <div class="w-1/3 flex-shrink-0 relative flex justify-center px-1">
                                @if($slider->url && $slider->url_type !== 'none')
                                    @if($slider->url_type === 'external')
                                        <a href="{{ $slider->url }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="block relative group">
                                    @else
                                        <a href="{{ $slider->url_type === 'internal' ? url($store->slug . '/' . ltrim($slider->url, '/')) : '#' }}" 
                                           class="block relative group">
                                    @endif
                                @else
                                    <div class="block relative group">
                                @endif
                                
                                <!-- Imagen del slider -->
                                <div class="w-[170px] h-[100px] bg-white-100 rounded-lg overflow-hidden relative">
                                    @if($slider->image_path)
                                        <img src="{{ Storage::disk('s3')->url($slider->image_path) }}" 
                                             alt="{{ $slider->name }}" 
                                             class="w-[170px] h-[100px] object-cover object-center transition-transform duration-300 group-hover:scale-105">
                                    @endif
                                    
                                    <!-- Overlay suave (solo si tiene enlace) -->
                                    @if($slider->url && $slider->url_type !== 'none')
                                        <div class="absolute inset-0 bg-gradient-to-t from-black-500/20 via-transparent to-transparent"></div>
                                    @endif
                                    
                                    <!-- Indicador de enlace -->
                                    @if($slider->url && $slider->url_type !== 'none')
                                        <div class="absolute top-1 right-1 bg-white-50/20 backdrop-blur-sm rounded-full p-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                                            <x-solar-arrow-right-outline class="w-2 h-2 text-white-50" />
                                        </div>
                                    @endif
                                </div>
                                
                                @if($slider->url && $slider->url_type !== 'none')
                                    </a>
                                @else
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <!-- Indicadores (dots) - Solo si hay más de 1 slide -->
            @if($sliders->count() > 1)
                <div class="flex justify-center mt-4 space-x-2">
                    @foreach($sliders as $index => $slider)
                        <button @click="goToSlide({{ $index }})"
                                class="w-2 h-2 rounded-full transition-all duration-300"
                                :class="currentSlide === {{ $index }} ? 'bg-primary-300 w-6' : 'bg-white-300 hover:bg-white-400'">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- Top 3 más vendidos -->
    <div>
        <h2 class="text-lg font-bold text-black-400 mb-4">Top 3 más vendidos</h2>
        
        @if($topProducts->count() > 0)
            <div class="space-y-3">
                @foreach($topProducts as $product)
                                         <div class="bg-white-50 rounded-lg p-3 flex items-center gap-3 border border-white-200 hover:shadow-sm transition-shadow relative">
                         <!-- Badge MÁS VENDIDO -->
                         <div class="absolute -top-1 -left-1 bg-error-300 text-white-50 text-xs px-2 py-1 rounded-full font-bold z-10">
                             🔥 MÁS VENDIDO
                         </div>
                         
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
                            <p class="text-xs text-black-300 mt-1 line-clamp-2">{{ $product->description }}</p>
                            <div class="mt-2 text-lg font-bold text-black-500">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </div>
                        </div>
                        
                        <!-- Botón agregar -->
                        <button class="bg-secondary-300 hover:bg-secondary-200 text-white-50 w-8 h-8 rounded-full flex items-center justify-center transition-colors flex-shrink-0">
                            <x-solar-add-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-black-300">
                <x-solar-box-outline class="w-12 h-12 mx-auto mb-3 text-black-200" />
                <p class="text-sm">No hay productos disponibles</p>
            </div>
        @endif
    </div>

    <!-- Lo más nuevo -->
    <div>
        <h2 class="text-lg font-bold text-black-400 mb-4">Lo más nuevo</h2>
        
        @if($newProducts->count() > 0)
            <div class="space-y-3">
                @foreach($newProducts as $product)
                                         <div class="bg-white-50 rounded-lg p-3 flex items-center gap-3 border border-white-200 hover:shadow-sm transition-shadow relative">
                         <!-- Badge NUEVO -->
                         <div class="absolute -top-1 -left-1 bg-success-300 text-white-50 text-xs px-2 py-1 rounded-full font-bold z-10">
                             ✨ NUEVO
                         </div>
                         
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
                            <p class="text-xs text-black-300 mt-1 line-clamp-2">{{ $product->description }}</p>
                            <div class="mt-2 text-lg font-bold text-black-500">
                                ${{ number_format($product->price, 0, ',', '.') }}
                            </div>
                        </div>
                        
                        <!-- Botón agregar -->
                        <button class="bg-secondary-300 hover:bg-secondary-200 text-white-50 w-8 h-8 rounded-full flex items-center justify-center transition-colors flex-shrink-0">
                            <x-solar-add-circle-outline class="w-5 h-5" />
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-black-300">
                <x-solar-box-outline class="w-12 h-12 mx-auto mb-3 text-black-200" />
                <p class="text-sm">No hay productos nuevos disponibles</p>
            </div>
        @endif
    </div>

    <!-- Categorías -->
    <div>
        <h2 class="text-lg font-bold text-black-400 mb-4">Categorías</h2>
        
        @if($categories->count() > 0)
            <div class="grid grid-cols-4 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('tenant.category', ['store' => $store->slug, 'categorySlug' => $category->slug]) }}" 
                       class="flex flex-col items-center p-3 bg-white-50 rounded-lg border border-white-200 hover:shadow-sm hover:border-primary-200 transition-all group">
                        
                                                 <!-- Icono de la categoría -->
                         <div class="w-12 h-12 mb-2 flex items-center justify-center rounded-lg bg-white-100 group-hover:bg-primary-50 transition-colors">
                             @if($category->icon && $category->icon->image_url)
                                 <img src="{{ $category->icon->image_url }}" 
                                      alt="{{ $category->name }}" 
                                      class="w-8 h-8 object-contain">
                             @else
                                 <x-solar-gallery-outline class="w-6 h-6 text-black-300 group-hover:text-primary-300" />
                             @endif
                         </div>
                        
                        <!-- Nombre de la categoría -->
                        <span class="text-xs text-center text-black-400 font-medium group-hover:text-primary-300 transition-colors">
                            {{ $category->name }}
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-black-300">
                <x-solar-gallery-outline class="w-12 h-12 mx-auto mb-3 text-black-200" />
                <p class="text-sm">No hay categorías disponibles</p>
                <a href="{{ route('tenant.categories', $store->slug) }}" 
                   class="inline-flex items-center mt-3 px-4 py-2 bg-primary-300 text-white-50 rounded-lg text-sm hover:bg-primary-200 transition-colors">
                    <x-solar-gallery-outline class="w-4 h-4 mr-2" />
                    Explorar
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function sliderComponent(sliders, duration = 5) {
    return {
        currentSlide: 0,
        sliders: sliders,
        duration: duration * 1000, // Convertir a milisegundos
        autoPlayInterval: null,
        isPlaying: true,
        
        init() {
            if (this.sliders.length > 1) {
                this.startAutoPlay();
            }
        },
        
        goToSlide(index) {
            this.currentSlide = index;
            this.resetAutoPlay();
        },
        
        nextSlide() {
            this.currentSlide = this.currentSlide + 1;
            
            // Si llegó al final de los slides originales, hacer reset suave
            if (this.currentSlide >= this.sliders.length) {
                setTimeout(() => {
                    // Deshabilitar transición
                    const sliderElement = document.querySelector('.slider-container .flex');
                    if (sliderElement) {
                        sliderElement.style.transition = 'none';
                        this.currentSlide = 0;
                        
                        // Restaurar transición después de un frame
                        setTimeout(() => {
                            sliderElement.style.transition = 'transform 500ms ease-in-out';
                        }, 50);
                    }
                }, 500); // Esperar que termine la transición actual
            }
            
            this.resetAutoPlay();
        },
        
        prevSlide() {
            this.currentSlide = this.currentSlide === 0 ? this.sliders.length - 1 : this.currentSlide - 1;
            this.resetAutoPlay();
        },
        
        startAutoPlay() {
            if (this.sliders.length <= 1) return;
            
            this.autoPlayInterval = setInterval(() => {
                if (this.isPlaying) {
                    this.nextSlide();
                }
            }, this.duration);
        },
        
        stopAutoPlay() {
            if (this.autoPlayInterval) {
                clearInterval(this.autoPlayInterval);
                this.autoPlayInterval = null;
            }
        },
        
        resetAutoPlay() {
            this.stopAutoPlay();
            this.startAutoPlay();
        },
        
        pauseAutoPlay() {
            this.isPlaying = false;
        },
        
        resumeAutoPlay() {
            this.isPlaying = true;
        }
    }
}

// Pausar auto-play cuando el usuario interactúa
document.addEventListener('DOMContentLoaded', function() {
    const sliderContainer = document.querySelector('.slider-container');
    
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', function() {
            const component = Alpine.$data(this);
            if (component && component.pauseAutoPlay) {
                component.pauseAutoPlay();
            }
        });
        
        sliderContainer.addEventListener('mouseleave', function() {
            const component = Alpine.$data(this);
            if (component && component.resumeAutoPlay) {
                component.resumeAutoPlay();
            }
        });
    }
});
</script>
@endpush

@endsection 