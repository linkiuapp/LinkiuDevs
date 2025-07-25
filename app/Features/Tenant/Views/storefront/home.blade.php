@extends('frontend.layouts.app')

@section('content')
<div class="px-2 py-6">
    <!-- Slider de Novedades -->
    @if($sliders->count() > 0)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-black-500 mb-4">Novedades</h2>
            
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
                                    :class="currentSlide === {{ $index }} ? 'bg-primary-400 w-6' : 'bg-white-300 hover:bg-white-400'">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Contenido principal del home -->
    <div class="space-y-6 px-2">
        <!-- Mensaje de bienvenida -->
        <div class="text-center bg-white-50 rounded-xl p-6 border border-white-200">
            <h2 class="text-2xl font-bold text-black-500 mb-3">
                ¡Bienvenido a {{ $store->name }}!
            </h2>
            <p class="text-black-300 leading-relaxed">
                {{ $store->description ?? 'Descubre nuestros productos y servicios' }}
            </p>
        </div>
        
        <!-- Sección de productos destacados -->
        <div class="bg-white-50 rounded-xl p-6 border border-white-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-black-500">Productos Destacados</h3>
                <span class="text-sm text-black-300">Próximamente</span>
            </div>
            
            <div class="text-center text-black-300 py-6">
                <x-solar-box-outline class="w-12 h-12 mx-auto mb-3 text-black-200" />
                <p class="text-sm">Aquí aparecerán nuestros productos más populares</p>
                <div class="mt-3 text-xs text-black-300 bg-white-100 rounded-lg px-3 py-2 inline-block">
                    En desarrollo
                </div>
            </div>
        </div>
        
        <!-- Sección de categorías -->
        <div class="bg-white-50 rounded-xl p-6 border border-white-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-black-500">Categorías</h3>
                <a href="{{ route('tenant.categories', $store->slug) }}" 
                   class="text-sm text-primary-400 hover:text-primary-300 font-medium flex items-center gap-1">
                    Ver todas
                    <x-solar-arrow-right-outline class="w-4 h-4" />
                </a>
            </div>
            
            <div class="text-center text-black-300 py-6">
                <x-solar-gallery-outline class="w-12 h-12 mx-auto mb-3 text-black-200" />
                <p class="text-sm">Explora nuestras categorías de productos</p>
                <a href="{{ route('tenant.categories', $store->slug) }}" 
                   class="inline-flex items-center mt-3 px-4 py-2 bg-primary-400 text-white-50 rounded-lg text-sm hover:bg-primary-300 transition-colors">
                    <x-solar-gallery-outline class="w-4 h-4 mr-2" />
                    Ver categorías
                </a>
            </div>
        </div>
        
        <!-- Información de contacto -->
        @if($store->email || $store->phone)
        <div class="bg-primary-50 rounded-xl p-6 border border-primary-100">
            <h3 class="text-lg font-semibold text-primary-500 mb-4 text-center">
                ¿Necesitas ayuda?
            </h3>
            <div class="flex flex-col gap-3">
                @if($store->email)
                    <a href="mailto:{{ $store->email }}" 
                       class="flex items-center justify-center gap-2 bg-white-50 text-primary-400 py-3 px-4 rounded-xl hover:bg-primary-100 hover:text-primary-500 transition-colors border border-primary-100">
                        <x-solar-letter-outline class="w-5 h-5" />
                        {{ $store->email }}
                    </a>
                @endif
                
                @if($store->phone)
                    <a href="tel:{{ $store->phone }}" 
                       class="flex items-center justify-center gap-2 bg-white-50 text-primary-400 py-3 px-4 rounded-xl hover:bg-primary-100 hover:text-primary-500 transition-colors border border-primary-100">
                        <x-solar-phone-outline class="w-5 h-5" />
                        {{ $store->phone }}
                    </a>
                @endif
            </div>
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