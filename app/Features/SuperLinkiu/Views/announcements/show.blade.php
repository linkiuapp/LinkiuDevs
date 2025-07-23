@extends('shared::layouts.admin')

@section('title', 'Detalle del Anuncio')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-lg font-bold text-black-400">Detalle del Anuncio</h1>
            <p class="text-sm text-black-300">{{ $announcement->title }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('superlinkiu.announcements.edit', $announcement) }}" 
               class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-pen-2-outline class="w-4 h-4" />
                Editar
            </a>
            <a href="{{ route('superlinkiu.announcements.index') }}" 
               class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contenido Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información del Anuncio -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Información del Anuncio</h2>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $announcement->type_color }}-100 text-{{ $announcement->type_color }}-300">
                                {{ $announcement->type_icon }} {{ $announcement->type_label }}
                            </span>
                            @if($announcement->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-100 text-success-300">
                                    <x-solar-check-circle-outline class="w-4 h-4 mr-1" />
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-black-100 text-black-300">
                                    <x-solar-pause-circle-outline class="w-4 h-4 mr-1" />
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-xl font-semibold text-black-400 mb-2">{{ $announcement->title }}</h3>
                            <div class="flex items-center gap-4 text-sm text-black-300 mb-4">
                                <span class="flex items-center gap-1">
                                    <x-solar-star-outline class="w-4 h-4" />
                                    Prioridad {{ $announcement->priority }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <x-solar-calendar-outline class="w-4 h-4" />
                                    {{ $announcement->created_at->format('d/m/Y H:i') }}
                                </span>
                                @if($announcement->published_at && $announcement->published_at->isFuture())
                                    <span class="flex items-center gap-1 text-warning-300">
                                        <x-solar-clock-circle-outline class="w-4 h-4" />
                                        Programado para {{ $announcement->published_at->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="prose max-w-none">
                            <div class="text-black-400 leading-relaxed whitespace-pre-wrap">{{ $announcement->content }}</div>
                        </div>

                        @if($announcement->banner_image)
                            <div class="border-t border-white-100 pt-4">
                                <h4 class="text-sm font-medium text-black-300 mb-3">Banner Asociado</h4>
                                <div class="flex items-start gap-4">
                                    <img src="{{ $announcement->banner_image_url }}" 
                                         alt="Banner" 
                                         class="border border-white-200 rounded"
                                         style="width: 320px; height: 100px; object-fit: cover;">
                                    <div>
                                        <p class="text-sm text-black-400 mb-1">{{ $announcement->banner_image }}</p>
                                        @if($announcement->banner_link)
                                            <a href="{{ $announcement->banner_link }}" 
                                               target="_blank"
                                               class="text-sm text-primary-300 hover:text-primary-400 flex items-center gap-1">
                                                <x-solar-link-outline class="w-4 h-4" />
                                                {{ $announcement->banner_link }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Lectura -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Estadísticas de Lectura</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-info-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-info-300">{{ $readStats['total_stores'] }}</div>
                            <div class="text-sm text-info-300">Total Tiendas</div>
                        </div>
                        <div class="bg-success-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-success-300">{{ $readStats['read_count'] }}</div>
                            <div class="text-sm text-success-300">Han Leído</div>
                        </div>
                        <div class="bg-warning-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-warning-300">{{ $readStats['unread_count'] }}</div>
                            <div class="text-sm text-warning-300">Sin Leer</div>
                        </div>
                    </div>

                    @if($readStats['read_count'] > 0)
                        <div class="space-y-3">
                            <h4 class="text-sm font-medium text-black-300">Tiendas que han leído el anuncio:</h4>
                            <div class="max-h-64 overflow-y-auto space-y-2">
                                @foreach($announcement->reads->sortByDesc('read_at') as $read)
                                    <div class="flex items-center justify-between p-3 bg-white-100 rounded border">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-success-200 rounded-full flex items-center justify-center">
                                                <span class="text-xs text-white-50">{{ substr($read->store->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-black-400">{{ $read->store->name }}</div>
                                                <div class="text-xs text-black-300">{{ $read->store->slug }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-black-400">{{ $read->read_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-black-300">{{ $read->read_at->format('H:i') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-solar-eye-closed-outline class="w-12 h-12 text-black-200 mx-auto mb-3" />
                            <p class="text-black-300">Aún no hay lecturas registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
            <!-- Información General -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Información General</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">ID:</span>
                        <span class="text-sm text-black-400 font-mono">#{{ $announcement->id }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Tipo:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $announcement->type_color }}-100 text-{{ $announcement->type_color }}-300">
                            {{ $announcement->type_icon }} {{ $announcement->type_label }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Prioridad:</span>
                        <span class="text-sm text-black-400">{{ $announcement->priority }}/10</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Estado:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $announcement->is_active ? 'success' : 'black' }}-100 text-{{ $announcement->is_active ? 'success' : 'black' }}-300">
                            {{ $announcement->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    @if($announcement->isExpired())
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-black-300">Expiración:</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-error-100 text-error-300">
                                <x-solar-clock-circle-outline class="w-3 h-3 mr-1" />
                                Expirado
                            </span>
                        </div>
                    @endif

                    <hr class="border-white-200">
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Creado:</span>
                        <span class="text-sm text-black-400">{{ $announcement->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-black-300">Modificado:</span>
                        <span class="text-sm text-black-400">{{ $announcement->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Configuración</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- Fechas -->
                    <div>
                        <h4 class="text-sm font-medium text-black-300 mb-2">Fechas</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-black-300">Publicación:</span>
                                <span class="text-black-400">
                                    {{ $announcement->published_at ? $announcement->published_at->format('d/m/Y H:i') : 'Inmediata' }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-black-300">Expiración:</span>
                                <span class="text-black-400">
                                    {{ $announcement->expires_at ? $announcement->expires_at->format('d/m/Y H:i') : 'Permanente' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Segmentación -->
                    @if($announcement->target_plans)
                        <div>
                            <h4 class="text-sm font-medium text-black-300 mb-2">Planes Target</h4>
                            <div class="flex flex-wrap gap-1">
                                @foreach($announcement->target_plans as $plan)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-info-100 text-info-300">
                                        {{ ucfirst($plan) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Banner -->
                    @if($announcement->show_as_banner)
                        <div>
                            <h4 class="text-sm font-medium text-black-300 mb-2">Banner</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <x-solar-gallery-outline class="w-4 h-4 text-warning-300" />
                                    <span class="text-sm text-black-400">Activo como banner</span>
                                </div>
                                @if($announcement->banner_image)
                                    <div class="flex items-center gap-2">
                                        <x-solar-gallery-add-outline class="w-4 h-4 text-success-300" />
                                        <span class="text-sm text-black-400">Con imagen</span>
                                    </div>
                                @endif
                                @if($announcement->banner_link)
                                    <div class="flex items-center gap-2">
                                        <x-solar-link-outline class="w-4 h-4 text-primary-300" />
                                        <span class="text-sm text-black-400">Con enlace</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Comportamiento -->
                    <div>
                        <h4 class="text-sm font-medium text-black-300 mb-2">Comportamiento</h4>
                        <div class="space-y-2">
                            @if($announcement->show_popup)
                                <div class="flex items-center gap-2">
                                    <x-solar-widget-3-outline class="w-4 h-4 text-warning-300" />
                                    <span class="text-sm text-black-400">Popup automático</span>
                                </div>
                            @endif
                            @if($announcement->send_email)
                                <div class="flex items-center gap-2">
                                    <x-solar-letter-outline class="w-4 h-4 text-info-300" />
                                    <span class="text-sm text-black-400">Envío de email</span>
                                </div>
                            @endif
                            @if($announcement->auto_mark_read_after)
                                <div class="flex items-center gap-2">
                                    <x-solar-clock-circle-outline class="w-4 h-4 text-black-300" />
                                    <span class="text-sm text-black-400">Auto-leído en {{ $announcement->auto_mark_read_after }} días</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-300 mb-4">Acciones Rápidas</h3>
                <div class="space-y-3">
                    <a href="{{ route('superlinkiu.announcements.edit', $announcement) }}" 
                       class="w-full btn-primary px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                        <x-solar-pen-2-outline class="w-4 h-4" />
                        Editar Anuncio
                    </a>

                    <form method="POST" 
                          action="{{ route('superlinkiu.announcements.toggle-active', $announcement) }}" 
                          class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full btn-{{ $announcement->is_active ? 'error' : 'success' }} px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            @if($announcement->is_active)
                                <x-solar-pause-circle-outline class="w-4 h-4" />
                                Desactivar
                            @else
                                <x-solar-play-circle-outline class="w-4 h-4" />
                                Activar
                            @endif
                        </button>
                    </form>

                    <form method="POST" 
                          action="{{ route('superlinkiu.announcements.duplicate', $announcement) }}" 
                          class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full btn-outline-warning px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-copy-outline class="w-4 h-4" />
                            Duplicar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 