@extends('shared::layouts.admin')

@section('title', 'Detalles del Plan - ' . $plan->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl text-black-500 mb-0">{{ $plan->name }}</h1>
            <p class="text-black-300 mt-1">Información detallada del plan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superlinkiu.plans.index') }}" class="bg-accent-100 hover:bg-accent-200 text-black-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <x-solar-arrow-left-outline class="w-5 h-5" />
                Volver a Planes
            </a>
            <a href="{{ route('superlinkiu.plans.edit', $plan) }}" class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <x-solar-pen-outline class="w-5 h-5" />
                Editar Plan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información Básica -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Información Básica</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Nombre</label>
                            <p class="text-black-500 font-medium">{{ $plan->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Precio Base</label>
                            <p class="text-black-500 font-medium">{{ $plan->getPriceFormatted() }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Moneda</label>
                            <p class="text-black-500 font-medium">{{ $plan->currency }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Duración</label>
                            <p class="text-black-500 font-medium">{{ $plan->duration_in_days }} días</p>
                        </div>
                        @if($plan->trial_days > 0)
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Días de Prueba</label>
                            <p class="text-black-500 font-medium">{{ $plan->trial_days }} días</p>
                        </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-1">Versión</label>
                            <p class="text-black-500 font-medium">{{ $plan->version }}</p>
                        </div>
                    </div>
                    
                    @if($plan->description)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-black-400 mb-2">Descripción</label>
                        <p class="text-black-400 leading-relaxed">{{ $plan->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Precios por Período -->
            @if($plan->prices)
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Precios por Período</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach(['monthly' => 'Mensual', 'quarterly' => 'Trimestral', 'semester' => 'Semestral'] as $period => $label)
                            @if(isset($plan->prices[$period]) && $plan->prices[$period] > 0)
                                <div class="text-center p-4 bg-accent-100 rounded-lg">
                                    <h3 class="text-sm font-medium text-black-400 mb-1">{{ $label }}</h3>
                                    <p class="text-2xl font-bold text-primary-300">{{ $plan->getFormattedPriceForPeriod($period) }}</p>
                                    @if($discount = $plan->getDiscountForPeriod($period))
                                        <p class="text-sm text-success-300 font-medium">Ahorro: {{ $discount }}%</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Límites del Plan -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Límites del Plan</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-box-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Productos</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_products }}</p>
                        </div>
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-gallery-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Sliders</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_slider }}</p>
                        </div>
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-tag-price-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Promociones</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_active_promotions }}</p>
                        </div>
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-ticket-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Cupones</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_active_coupons }}</p>
                        </div>
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-folder-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Categorías</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_categories }}</p>
                        </div>
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-buildings-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Sedes</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_sedes }}</p>
                        </div>
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-users-group-rounded-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Admins</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_admins }}</p>
                        </div>
                        <div class="text-center p-3 bg-accent-100 rounded-lg">
                            <x-solar-map-point-outline class="w-8 h-8 mx-auto mb-2 text-primary-300" />
                            <p class="text-sm text-black-400">Zonas Reparto</p>
                            <p class="text-lg font-bold text-black-500">{{ $plan->max_delivery_zones }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Características -->
            @php
                $features = $plan->features_list;
                if (is_string($features)) {
                    $features = json_decode($features, true) ?: [];
                }
                $features = is_array($features) ? $features : [];
            @endphp
            @if($features && count($features) > 0)
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Características Incluidas</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($features as $feature)
                            <div class="flex items-start">
                                <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mr-3 flex-shrink-0 mt-0.5" />
                                <span class="text-black-400">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
            <!-- Estado del Plan -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Estado</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Activo</span>
                        @if($plan->is_active)
                            <span class="bg-success-200 text-accent-50 px-2 py-1 rounded text-sm">Sí</span>
                        @else
                            <span class="bg-error-200 text-accent-50 px-2 py-1 rounded text-sm">No</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Público</span>
                        @if($plan->is_public)
                            <span class="bg-success-200 text-accent-50 px-2 py-1 rounded text-sm">Sí</span>
                        @else
                            <span class="bg-error-200 text-accent-50 px-2 py-1 rounded text-sm">No</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Destacado</span>
                        @if($plan->is_featured)
                            <span class="bg-warning-200 text-black-400 px-2 py-1 rounded text-sm">Sí</span>
                        @else
                            <span class="bg-accent-200 text-black-400 px-2 py-1 rounded text-sm">No</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Slug Personalizado</span>
                        @if($plan->allow_custom_slug)
                            <span class="bg-success-200 text-accent-50 px-2 py-1 rounded text-sm">Permitido</span>
                        @else
                            <span class="bg-error-200 text-accent-50 px-2 py-1 rounded text-sm">No Permitido</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Orden</span>
                        <span class="text-black-500 font-medium">{{ $plan->sort_order }}</span>
                    </div>
                </div>
            </div>

            <!-- Soporte -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Soporte</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">Nivel</label>
                        <span class="bg-info-200 text-accent-50 px-2 py-1 rounded text-sm capitalize">{{ $plan->support_level }}</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-1">Tiempo de Respuesta</label>
                        <p class="text-black-500 font-medium">{{ $plan->support_response_time }} horas</p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Estadísticas</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Tiendas Activas</span>
                        <span class="text-black-500 font-bold">{{ $plan->stores_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Creado</span>
                        <span class="text-black-400 text-sm">{{ $plan->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black-400">Actualizado</span>
                        <span class="text-black-400 text-sm">{{ $plan->updated_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                    <h2 class="text-3xl text-black-500 mb-0">Acciones</h2>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('superlinkiu.plans.edit', $plan) }}" 
                       class="w-full bg-primary-50 hover:bg-primary-100 text-primary-300 py-2 rounded-lg text-center transition-colors block">
                        <x-solar-pen-outline class="w-4 h-4 inline mr-2" />
                        Editar Plan
                    </a>
                    
                    @if(!$plan->hasActiveStores())
                        <form action="{{ route('superlinkiu.plans.destroy', $plan) }}" method="POST" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('¿Estás seguro de eliminar este plan?')"
                                    class="w-full bg-error-50 hover:bg-error-100 text-error-300 py-2 rounded-lg transition-colors">
                                <x-solar-trash-bin-trash-outline class="w-4 h-4 inline mr-2" />
                                Eliminar Plan
                            </button>
                        </form>
                    @else
                        <div class="text-center py-2 text-sm text-black-300">
                            <x-solar-info-circle-outline class="w-4 h-4 inline mr-1" />
                            No se puede eliminar: tiene tiendas activas
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 