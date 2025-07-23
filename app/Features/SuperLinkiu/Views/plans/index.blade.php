@extends('shared::layouts.admin')

@section('title', 'Gestión de Planes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Gestión de Planes</h1>
        <a href="{{ route('superlinkiu.plans.create') }}" class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-add-circle-outline class="w-5 h-5" />
            Nuevo Plan
        </a>
    </div>

    <!-- Planes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden {{ $plan->is_featured ? 'ring-2 ring-primary-200' : '' }}">
                <!-- Header del plan -->
                <div class="border-b border-white-100 bg-white-50 py-4 px-6 relative">
                    @if($plan->is_featured)
                        <span class="absolute top-2 right-2 badge-soft-primary text-xs">
                            Más Popular
                        </span>
                    @endif
                    <h2 class="text-lg font-semibold text-black-400 mb-1">{{ $plan->name }}</h2>
                    <p class="text-sm text-black-300">{{ $plan->description }}</p>
                    <div class="mt-3">
                        <span class="text-2xl font-bold text-primary-300">
                            {{ $plan->getFormattedPriceForPeriod('monthly') }}
                        </span>
                        <span class="text-sm text-black-300">/mes</span>
                    </div>
                </div>

                <!-- Características -->
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-black-400 mb-3">Características incluidas:</h3>
                    @php
                        $features = $plan->features_list;
                        if (is_string($features)) {
                            $features = json_decode($features, true) ?: [];
                        }
                        $features = is_array($features) ? $features : [];
                    @endphp
                    @if($features && count($features) > 0)
                        <ul class="space-y-2 mb-4">
                            @foreach(array_slice($features, 0, 5) as $feature)
                                <li class="flex items-start text-sm text-black-300">
                                    <x-solar-check-circle-outline class="w-4 h-4 text-success-300 mr-2 flex-shrink-0 mt-0.5" />
                                    {{ $feature }}
                                </li>
                            @endforeach
                            @if(count($features) > 5)
                                <li class="text-sm text-primary-300">
                                    Y {{ count($features) - 5 }} más...
                                </li>
                            @endif
                        </ul>
                    @endif

                    <!-- Precios por período -->
                    @if($plan->prices)
                        <div class="border-t border-white-100 pt-4 mb-4">
                            <p class="text-xs font-medium text-black-300 mb-2">Precios por período:</p>
                            <div class="space-y-1">
                                @foreach(['monthly' => 'Mensual', 'quarterly' => 'Trimestral', 'semester' => 'Semestral'] as $period => $label)
                                    @if(isset($plan->prices[$period]) && $plan->prices[$period] > 0)
                                        <div class="flex justify-between text-xs">
                                            <span class="text-black-300">{{ $label }}:</span>
                                            <span class="text-black-400 font-medium">
                                                {{ $plan->getFormattedPriceForPeriod($period) }}
                                                @if($discount = $plan->getDiscountForPeriod($period))
                                                    <span class="text-success-300 ml-1">(-{{ $discount }}%)</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Información adicional -->
                    <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                        <div>
                            <span class="text-black-300">Estado:</span>
                            <span class="ml-1">
                                @if($plan->is_active)
                                    <span class="badge-soft-success">Activo</span>
                                @else
                                    <span class="badge-soft-error">Inactivo</span>
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-black-300">Versión:</span>
                            <span class="text-black-400 font-medium ml-1">{{ $plan->version }}</span>
                        </div>
                        <div>
                            <span class="text-black-300">Tiendas:</span>
                            <span class="text-black-400 font-medium ml-1">{{ $plan->stores_count ?? 0 }}</span>
                        </div>
                        <div>
                            <span class="text-black-300">Orden:</span>
                            <span class="text-black-400 font-medium ml-1">{{ $plan->sort_order }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="border-t border-white-100 bg-white-50 px-6 py-3">
                    <div class="flex gap-2">
                        <a href="{{ route('superlinkiu.plans.show', $plan) }}"
                            class="flex-1 bg-primary-50 hover:bg-primary-100 text-primary-300 py-2 rounded-lg text-center transition-colors text-sm">
                            Ver detalles
                        </a>
                        <a href="{{ route('superlinkiu.plans.edit', $plan) }}"
                            class="flex-1 bg-info-50 hover:bg-info-100 text-info-300 py-2 rounded-lg text-center transition-colors text-sm">
                            Editar
                        </a>
                        @if(!$plan->hasActiveStores())
                            <form action="{{ route('superlinkiu.plans.destroy', $plan) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('¿Estás seguro de eliminar este plan?')"
                                    class="w-full bg-error-50 hover:bg-error-100 text-error-300 py-2 rounded-lg transition-colors text-sm">
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <x-solar-box-outline class="w-16 h-16 mx-auto mb-4 text-black-100" />
                <p class="text-black-300">No hay planes registrados</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if ($plans->hasPages())
    <div class="mt-6">
        <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
            {{-- Previous Page Link --}}
            @if ($plans->onFirstPage())
                <li class="page-item">
                    <span class="page-link bg-white-100 border border-white-200 text-black-200 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] cursor-not-allowed">
                        <x-solar-arrow-left-outline class="w-4 h-4" />
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link bg-white-50 border border-white-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                       href="{{ $plans->previousPageUrl() }}">
                        <x-solar-arrow-left-outline class="w-4 h-4" />
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($plans->getUrlRange(1, $plans->lastPage()) as $page => $url)
                @if ($page == $plans->currentPage())
                    <li class="page-item">
                        <span class="page-link bg-primary-200 text-white-50 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] w-[40px]">
                            {{ $page }}
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link bg-white-50 border border-white-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] w-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                           href="{{ $url }}">
                            {{ $page }}
                        </a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($plans->hasMorePages())
                <li class="page-item">
                    <a class="page-link bg-white-50 border border-white-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                       href="{{ $plans->nextPageUrl() }}">
                        <x-solar-arrow-right-outline class="w-4 h-4" />
                    </a>
                </li>
            @else
                <li class="page-item">
                    <span class="page-link bg-white-100 border border-white-200 text-black-200 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] cursor-not-allowed">
                        <x-solar-arrow-right-outline class="w-4 h-4" />
                    </span>
                </li>
            @endif
        </ul>
    </div>
    @endif
</div>
@endsection 