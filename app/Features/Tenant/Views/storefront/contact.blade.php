@extends('frontend.layouts.app')

@section('content')
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="space-y-3">
            <!-- Breadcrumbs -->
            <nav class="flex text-small font-regular text-info-300">
                <a href="{{ route('tenant.home', $store->slug) }}" class="hover:text-info-200 transition-colors">Inicio</a>
                <span class="mx-2">/</span>
                <span class="text-secondary-300 font-medium">Contacto</span>
            </nav>
            
            <!-- Title -->
            <div class="space-y-2">
                <h7 class="text-h7 font-bold text-black-300">Nuestras Sedes</h7>
                <p class="text-body-small font-regular text-black-200">Encuentra la sede más cercana a ti</p>
            </div>
        </div>

        @if($locations->count() > 0)
            <!-- Lista de sedes -->
            <div class="space-y-4">
                @foreach($locations as $location)
                    <div class="bg-white-50 rounded-xl border border-white-200 overflow-hidden" x-data="{ showSchedule: false }">
                        <!-- Header de la sede -->
                        <div class="p-4 pb-3">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-lg font-semibold text-black-400">{{ $location->name }}</h3>
                                        @if($location->is_main)
                                            <span class="bg-primary-300 text-white-50 text-xs px-2 py-1 rounded-full font-medium">Principal</span>
                                        @endif
                                    </div>
                                    
                                    @if($location->description)
                                        <p class="text-sm text-black-300 mb-2">{{ $location->description }}</p>
                                    @endif
                                    
                                    <!-- Estado actual con badge -->
                                    <div class="flex items-center gap-2">
                                        @if($location->currentStatus['status'] === 'open')
                                            <span class="bg-success-200 text-black-400 text-xs px-3 py-1 rounded-full font-medium flex items-center gap-1">
                                                <div class="w-1.5 h-1.5 bg-white-50 rounded-full"></div>
                                                {{ $location->currentStatus['text'] }}
                                            </span>
                                        @elseif($location->currentStatus['status'] === 'closed')
                                            <span class="bg-error-200 text-white-50 text-xs px-3 py-1 rounded-full font-medium flex items-center gap-1">
                                                <div class="w-1.5 h-1.5 bg-white-50 rounded-full"></div>
                                                {{ $location->currentStatus['text'] }}
                                            </span>
                                        @else
                                            <span class="bg-warning-300 text-black-400 text-xs px-3 py-1 rounded-full font-medium flex items-center gap-1">
                                                <div class="w-1.5 h-1.5 bg-black-400 rounded-full"></div>
                                                {{ $location->currentStatus['text'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de contacto y ubicación -->
                        <div class="px-4 pb-4 space-y-3">
                            <!-- Ubicación -->
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 mt-0.5 flex-shrink-0">
                                    <x-solar-map-point-outline class="w-full h-full text-black-300" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-black-400 font-medium">{{ $location->address }}</p>
                                    <p class="text-xs text-black-300">{{ $location->city }}, {{ $location->department }}</p>
                                </div>
                            </div>

                            <!-- Manager (si existe) -->
                            @if($location->manager_name)
                                <div class="flex items-center gap-3">
                                    <div class="w-5 h-5 flex-shrink-0">
                                        <x-solar-user-outline class="w-full h-full text-black-300" />
                                    </div>
                                    <p class="text-sm text-black-400">{{ $location->manager_name }}</p>
                                </div>
                            @endif

                            <!-- Contacto -->
                            <div class="space-y-2">
                                @if($location->phone)
                                    <a href="tel:{{ $location->phone }}" 
                                       class="flex items-center gap-3 p-3 bg-white-100 rounded-lg hover:bg-primary-50 transition-colors">
                                        <div class="w-5 h-5 flex-shrink-0">
                                            <x-solar-phone-outline class="w-full h-full text-primary-300" />
                                        </div>
                                        <span class="text-sm text-black-400 font-medium">{{ $location->phone }}</span>
                                    </a>
                                @endif

                                @if($location->whatsapp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $location->whatsapp) }}{{ $location->whatsapp_message ? '?text=' . urlencode($location->whatsapp_message) : '' }}" 
                                       target="_blank"
                                       class="flex items-center gap-3 p-3 bg-success-50 rounded-lg hover:bg-success-100 transition-colors">
                                        <div class="w-5 h-5 flex-shrink-0">
                                            <svg class="w-full h-full text-success-300" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 2.079.549 4.03 1.506 5.719L0 24l6.573-1.525c1.647.872 3.516 1.373 5.512 1.373 6.624 0 11.99-5.367 11.99-11.99C24.075 5.367 18.641.001 12.017.001z"/>
                                                <path fill="#fff" d="M18.832 14.615c-.297.832-1.792 1.573-2.53 1.679-.732.105-1.69.094-2.73-.64-1.33-.938-2.197-2.303-2.197-2.303s-.469-.625-.719-1.105c-.25-.48-.469-1.042-.312-1.667.156-.625.781-1.146 1.042-1.406.26-.26.573-.375.781-.375.209 0 .417.008.594.016.187.011.447-.071.698.533.26.625.885 2.188.958 2.344.073.156.125.344.031.563-.094.218-.146.344-.292.531-.146.188-.302.417-.438.563-.146.146-.302.302-.125.594.177.292.781 1.302 1.688 2.115 1.156.104 1.417 1.219 1.5 1.531.083.313.083.583-.021.781z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-success-500 font-medium">WhatsApp</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Horarios -->
                        @if($location->schedules->count() > 0)
                            <div class="border-t border-white-200">
                                <button @click="showSchedule = !showSchedule" 
                                        class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-white-100 transition-colors">
                                    <span class="text-sm text-black-400 font-medium">Ver horarios</span>
                                    <x-solar-alt-arrow-down-outline class="w-4 h-4 text-black-300 transition-transform" 
                                                                     x-bind:class="showSchedule ? 'rotate-180' : ''" />
                                </button>
                                
                                <div x-show="showSchedule" 
                                     x-collapse
                                     class="px-4 pb-4">
                                    <div class="space-y-2">
                                        @php
                                            $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                        @endphp
                                        @for($day = 0; $day < 7; $day++)
                                            @php
                                                $schedule = $location->schedules->where('day_of_week', $day)->first();
                                            @endphp
                                            <div class="flex justify-between items-center py-1">
                                                <span class="text-xs text-black-300 font-medium">{{ $days[$day] }}</span>
                                                <div class="text-xs text-black-400">
                                                    @if($schedule && !$schedule->is_closed)
                                                        <span>{{ substr($schedule->open_time_1, 0, 5) }} - {{ substr($schedule->close_time_1, 0, 5) }}</span>
                                                        @if($schedule->open_time_2 && $schedule->close_time_2)
                                                            <span class="text-black-300"> • </span>
                                                            <span>{{ substr($schedule->open_time_2, 0, 5) }} - {{ substr($schedule->close_time_2, 0, 5) }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-error-300">Cerrado</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Redes sociales -->
                        @if($location->socialLinks->count() > 0)
                            <div class="border-t border-white-200 p-4">
                                <p class="text-xs text-black-300 font-medium mb-2">Síguenos</p>
                                <div class="flex gap-2">
                                    @foreach($location->socialLinks as $social)
                                        <a href="{{ $social->url }}" 
                                           target="_blank"
                                           class="w-8 h-8 bg-white-200 rounded-lg flex items-center justify-center hover:bg-primary-100 transition-colors">
                                            @if($social->platform === 'facebook')
                                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                            @elseif($social->platform === 'instagram')
                                                <svg class="w-4 h-4 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                </svg>
                                            @elseif($social->platform === 'whatsapp')
                                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 2.079.549 4.03 1.506 5.719L0 24l6.573-1.525c1.647.872 3.516 1.373 5.512 1.373 6.624 0 11.99-5.367 11.99-11.99C24.075 5.367 18.641.001 12.017.001z"/>
                                                    <path fill="#fff" d="M18.832 14.615c-.297.832-1.792 1.573-2.53 1.679-.732.105-1.69.094-2.73-.64-1.33-.938-2.197-2.303-2.197-2.303s-.469-.625-.719-1.105c-.25-.48-.469-1.042-.312-1.667.156-.625.781-1.146 1.042-1.406.26-.26.573-.375.781-.375.209 0 .417.008.594.016.187.011.447-.071.698.533.26.625.885 2.188.958 2.344.073.156.125.344.031.563-.094.218-.146.344-.292.531-.146.188-.302.417-.438.563-.146.146-.302.302-.125.594.177.292.781 1.302 1.688 2.115 1.156.104 1.417 1.219 1.5 1.531.083.313.083.583-.021.781z"/>
                                                </svg>
                                            @elseif($social->platform === 'tiktok')
                                                <svg class="w-4 h-4 text-black-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                                </svg>
                                            @elseif($social->platform === 'youtube')
                                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                </svg>
                                            @elseif($social->platform === 'twitter' || $social->platform === 'x')
                                                <svg class="w-4 h-4 text-black-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                                </svg>
                                            @else
                                                <x-solar-link-outline class="w-4 h-4 text-black-400" />
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <!-- Estado vacío -->
            <div class="bg-accent-50 rounded-xl p-8 text-center border border-accent-200">
                <div class="w-16 h-16 bg-accent-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-lucide-building-2 class="w-8 h-8 text-black-300" />
                </div>
                <h3 class="text-h7 font-bold text-black-300 mb-2">Próximamente</h3>
                <p class="text-body-small font-regular text-black-300 mb-4">Estamos trabajando en agregar nuestras sedes</p>
                @if($store->phone || $store->email)
                    <div class="space-y-2">
                        @if($store->phone)
                            <a href="tel:{{ $store->phone }}" 
                               class="inline-flex items-center gap-2 bg-primary-300 text-white-50 px-4 py-2 rounded-lg text-caption font-medium hover:bg-primary-200 transition-colors">
                                <x-solar-phone-outline class="w-4 h-4" />
                                {{ $store->phone }}
                            </a>
                        @endif
                        @if($store->email)
                            <a href="mailto:{{ $store->email }}" 
                               class="inline-flex items-center gap-2 bg-white-200 text-black-300 px-4 py-2 rounded-lg text-caption font-medium hover:bg-white-300 transition-colors">
                                <x-solar-letter-outline class="w-4 h-4" />
                                {{ $store->email }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    @push('scripts')
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endsection 