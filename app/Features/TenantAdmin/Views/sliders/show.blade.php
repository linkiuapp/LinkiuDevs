<x-tenant-admin-layout :store="$store">
    @section('title', 'Detalles del Slider')

    @section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('tenant.admin.sliders.index', $store->slug) }}" 
                       class="text-black-300 hover:text-black-400">
                        <x-solar-arrow-left-outline class="w-6 h-6" />
                    </a>
                    <h1 class="text-lg font-semibold text-black-500">Detalles del Slider</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tenant.admin.sliders.edit', [$store->slug, $slider->id]) }}" 
                       class="btn-outline-primary">
                        <x-solar-pen-new-square-outline class="w-4 h-4 mr-2" />
                        Editar
                    </a>
                    <form action="{{ route('tenant.admin.sliders.toggle-status', [$store->slug, $slider->id]) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-outline-{{ $slider->is_active ? 'warning' : 'success' }}">
                            @if($slider->is_active)
                                <x-solar-eye-closed-outline class="w-4 h-4 mr-2" />
                                Desactivar
                            @else
                                <x-solar-eye-outline class="w-4 h-4 mr-2" />
                                Activar
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Información Básica -->
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
            <div class="p-6 space-y-4">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-black-500 mb-1">Información Básica</h3>
                    <p class="text-sm text-black-300">Datos principales del slider</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">Nombre</label>
                        <p class="text-black-500 font-medium">{{ $slider->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">Estado</label>
                        @if($slider->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-200 text-black-300">
                                <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                Activo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-error-200 text-white-50">
                                <x-solar-close-circle-outline class="w-3 h-3 mr-1" />
                                Inactivo
                            </span>
                        @endif
                    </div>
                </div>
                
                @if($slider->description)
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-1">Descripción</label>
                    <p class="text-black-500">{{ $slider->description }}</p>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">Transición</label>
                        <p class="text-black-500">{{ $slider->transition_duration }} segundos</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">Orden</label>
                        <p class="text-black-500">#{{ $slider->sort_order }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Imagen -->
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-black-500 mb-1">Imagen</h3>
                    <p class="text-sm text-black-300">Imagen del slider</p>
                </div>
                
                @if($slider->image_path)
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $slider->image_path) }}" 
                             alt="{{ $slider->name }}" 
                             class="max-w-full h-auto mx-auto rounded-lg shadow-sm border border-white-200">
                        <p class="text-xs text-black-300 mt-2">170x100px</p>
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-solar-gallery-outline class="w-16 h-16 text-black-300 mx-auto mb-4" />
                        <p class="text-black-400">No hay imagen</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Enlace -->
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
            <div class="p-6 space-y-4">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-black-500 mb-1">Enlace</h3>
                    <p class="text-sm text-black-300">Configuración del enlace</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-1">Tipo de enlace</label>
                    @if($slider->url_type === 'none')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-black-100 text-black-400">
                            <x-solar-close-circle-outline class="w-3 h-3 mr-1" />
                            Sin enlace
                        </span>
                    @elseif($slider->url_type === 'internal')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-400">
                            <x-solar-home-outline class="w-3 h-3 mr-1" />
                            Enlace interno
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-400">
                            <x-solar-link-outline class="w-3 h-3 mr-1" />
                            Enlace externo
                        </span>
                    @endif
                </div>
                
                @if($slider->url)
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-1">URL</label>
                    <p class="text-black-500 break-all">{{ $slider->url }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Programación -->
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-6">
            <div class="p-6 space-y-4">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-black-500 mb-1">Programación</h3>
                    <p class="text-sm text-black-300">Configuración de horarios</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-1">Estado</label>
                    @if($slider->is_scheduled)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-400">
                            <x-solar-clock-circle-outline class="w-3 h-3 mr-1" />
                            Programado
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-400">
                            <x-solar-infinity-outline class="w-3 h-3 mr-1" />
                            Siempre activo
                        </span>
                    @endif
                </div>

                @if($slider->is_scheduled)
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">Tipo de programación</label>
                        @if($slider->is_permanent)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-400">
                                <x-solar-infinity-outline class="w-3 h-3 mr-1" />
                                Permanente
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-400">
                                <x-solar-calendar-outline class="w-3 h-3 mr-1" />
                                Con fecha fin
                            </span>
                        @endif
                    </div>

                    @if(!$slider->is_permanent && ($slider->start_date || $slider->end_date))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($slider->start_date)
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Fecha inicio</label>
                            <p class="text-black-500">{{ \Carbon\Carbon::parse($slider->start_date)->format('d/m/Y') }}</p>
                        </div>
                        @endif
                        @if($slider->end_date)
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Fecha fin</label>
                            <p class="text-black-500">{{ \Carbon\Carbon::parse($slider->end_date)->format('d/m/Y') }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($slider->start_time || $slider->end_time)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($slider->start_time)
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Hora inicio</label>
                            <p class="text-black-500">{{ $slider->start_time }}</p>
                        </div>
                        @endif
                        @if($slider->end_time)
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Hora fin</label>
                            <p class="text-black-500">{{ $slider->end_time }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($slider->scheduled_days && count(array_filter($slider->scheduled_days)) > 0)
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">Días de la semana</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles', 'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'] as $day => $label)
                                @if($slider->scheduled_days[$day] ?? false)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-400">
                                        {{ $label }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Botones -->
        <div class="bg-white-100 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.admin.sliders.index', $store->slug) }}" 
                   class="btn-outline-secondary">
                    Volver
                </a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.admin.sliders.edit', [$store->slug, $slider->id]) }}" 
                   class="btn-primary">
                    Editar Slider
                </a>
            </div>
        </div>
    </div>
    @endsection
</x-tenant-admin-layout> 