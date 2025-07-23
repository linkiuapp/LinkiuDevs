<x-tenant-admin-layout :store="$store">
@section('title', 'Detalles de Variable')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('tenant.admin.variables.index', $store->slug) }}" 
               class="text-black-300 hover:text-black-400">
                <x-solar-arrow-left-outline class="w-6 h-6" />
            </a>
            <h1 class="text-lg font-semibold text-black-500">Detalles de Variable</h1>
        </div>
    </div>

    <!-- Card principal -->
    <div class="bg-white-50 rounded-lg p-0 overflow-hidden p-6">
        <div class="border-b border-white-100 bg-white-50 py-4 px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white-100 rounded-lg p-3 flex items-center justify-center">
                        <x-dynamic-component :component="$variable->type_icon" class="w-full h-full text-primary-200" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-black-500 mb-1">{{ $variable->name }}</h2>
                        <div class="flex items-center gap-3">
                            @if($variable->is_active)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-200 text-black-300">
                                    <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                    Activa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-error-200 text-black-300">
                                    <x-solar-close-circle-outline class="w-3 h-3 mr-1" />
                                    Inactiva
                                </span>
                            @endif
                            @if($variable->is_required_default)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-info-200 text-black-300">
                                    <x-solar-star-outline class="w-3 h-3 mr-1" />
                                    Requerida
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('tenant.admin.variables.edit', [$store->slug, $variable]) }}" 
                       class="px-3 py-2 bg-primary-200 text-white-50 rounded-lg hover:bg-primary-300 transition-colors text-sm flex items-center gap-2">
                        <x-solar-pen-outline class="w-4 h-4" />
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <!-- Información general -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-black-400 mb-3">Información General</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-black-400">Tipo:</span>
                            <p class="text-black-500">
                                @switch($variable->type)
                                    @case('radio')
                                        Selección única
                                        @break
                                    @case('checkbox')
                                        Selección múltiple
                                        @break
                                    @case('text')
                                        Texto libre
                                        @break
                                    @case('numeric')
                                        Numérico
                                        @break
                                @endswitch
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-black-400">Creado:</span>
                            <p class="text-black-500">{{ $variable->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-black-400">Actualizado:</span>
                            <p class="text-black-500">{{ $variable->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                @if($variable->type === 'numeric')
                <div>
                    <h3 class="text-sm font-medium text-black-400 mb-3">Configuración Numérica</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-black-400">Valor mínimo:</span>
                            <p class="text-black-500">{{ $variable->min_value ?? 'Sin límite' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-black-400">Valor máximo:</span>
                            <p class="text-black-500">{{ $variable->max_value ?? 'Sin límite' }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Opciones Card -->
    @if($variable->type === 'radio' || $variable->type === 'checkbox')
        <div class="bg-white-50 rounded-lg p-6 mt-6">
            <h3 class="text-lg font-semibold text-black-500 mb-4">Opciones de la Variable</h3>
            @if($variable->options->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($variable->options as $option)
                        <div class="bg-white-100 rounded-lg p-4 border border-white-200">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-black-500">{{ $option->name }}</h4>
                                <span class="text-sm text-black-300">#{{ $loop->iteration }}</span>
                            </div>
                            @if($option->price_modifier != 0)
                                <div class="text-sm text-black-400 mb-2">
                                    <span class="font-medium">Precio:</span> 
                                    @if($option->price_modifier > 0)
                                        <span class="text-success-400">+${{ number_format($option->price_modifier, 2) }}</span>
                                    @else
                                        <span class="text-error-400">-${{ number_format(abs($option->price_modifier), 2) }}</span>
                                    @endif
                                </div>
                            @endif
                            @if($option->color_hex)
                                <div class="text-sm text-black-400 mb-2 flex items-center">
                                    <span class="font-medium mr-2">Color:</span> 
                                    <span class="inline-block w-4 h-4 rounded border border-white-200 mr-2" style="background-color: {{ $option->color_hex }}"></span>
                                    <span class="font-mono text-xs">{{ $option->color_hex }}</span>
                                </div>
                            @endif
                            <div class="mt-2 pt-2 border-t border-white-200">
                                <span class="text-xs text-black-300">
                                    Creado: {{ $option->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-solar-document-outline class="w-12 h-12 mx-auto text-black-200 mb-4" />
                    <h4 class="font-medium text-black-400 mb-2">No hay opciones</h4>
                    <p class="text-sm text-black-300">Esta variable no tiene opciones configuradas.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Estadísticas Card -->
    <div class="bg-white-50 rounded-lg p-6 mt-6">
        <h3 class="text-lg font-semibold text-black-500 mb-4">Estadísticas</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="bg-primary-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <x-solar-list-outline class="w-8 h-8 text-primary-400" />
                </div>
                <h4 class="font-medium text-black-500 mb-1">{{ $variable->options->count() }}</h4>
                <p class="text-sm text-black-300">Opciones totales</p>
            </div>
            
            <div class="text-center">
                <div class="bg-success-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <x-solar-box-outline class="w-8 h-8 text-success-400" />
                </div>
                <h4 class="font-medium text-black-500 mb-1">0</h4>
                <p class="text-sm text-black-300">Productos usando</p>
            </div>
            
            <div class="text-center">
                <div class="bg-info-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <x-solar-calendar-outline class="w-8 h-8 text-info-400" />
                </div>
                <h4 class="font-medium text-black-500 mb-1">{{ $variable->created_at->diffForHumans() }}</h4>
                <p class="text-sm text-black-300">Última actividad</p>
            </div>
        </div>
    </div>
</div>
@endsection
</x-tenant-admin-layout>