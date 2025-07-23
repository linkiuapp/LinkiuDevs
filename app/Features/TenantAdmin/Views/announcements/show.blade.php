@extends('shared::layouts.tenant-admin')

@section('title', $announcement->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-2xl">{{ $announcement->type_icon }}</span>
                <h1 class="text-lg font-bold text-black-400">{{ $announcement->title }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $announcement->type_color }}-100 text-{{ $announcement->type_color }}-300">
                    {{ $announcement->type_label }}
                </span>
            </div>
            <p class="text-sm text-black-300">Anuncio de Linkiu</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.admin.announcements.index', $store->slug) }}" 
               class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Volver al Tablón
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Contenido Principal -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Contenido del Anuncio -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden border-l-4 border-{{ $announcement->type_color }}-200">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-black-400 mb-0">Contenido del Anuncio</h2>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-black-300">
                                <x-solar-calendar-outline class="w-4 h-4 inline mr-1" />
                                {{ $announcement->created_at->format('d/m/Y H:i') }}
                            </span>
                            <span class="text-sm text-black-300">
                                <x-solar-star-outline class="w-4 h-4 inline mr-1" />
                                Prioridad {{ $announcement->priority }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="prose max-w-none">
                        <div class="text-black-400 leading-relaxed whitespace-pre-wrap text-base">{{ $announcement->content }}</div>
                    </div>
                </div>
            </div>

            <!-- Banner si existe -->
            @if($announcement->banner_image)
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-400 mb-0 flex items-center gap-2">
                            <x-solar-gallery-outline class="w-5 h-5" />
                            Banner Asociado
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-start gap-6">
                            <img src="{{ $announcement->banner_image_url }}" 
                                 alt="Banner" 
                                 class="border border-white-200 rounded shadow-sm"
                                 style="width: 320px; height: 100px; object-fit: cover;">
                            <div class="flex-1">
                                <h4 class="font-medium text-black-400 mb-2">{{ $announcement->title }}</h4>
                                <p class="text-sm text-black-300 mb-3">
                                    Este banner se muestra en el carrusel del dashboard cuando está activo.
                                </p>
                                @if($announcement->banner_link)
                                    <a href="{{ $announcement->banner_link }}" 
                                       target="_blank"
                                       class="btn-primary px-4 py-2 rounded-lg flex items-center gap-2 w-fit">
                                        <x-solar-link-outline class="w-4 h-4" />
                                        Ir al Enlace
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Información Adicional -->
            @if($announcement->expires_at || $announcement->auto_mark_read_after)
                <div class="bg-info-50 rounded-lg p-6 border border-info-100">
                    <h3 class="text-sm font-medium text-info-300 mb-3 flex items-center gap-2">
                        <x-solar-info-circle-outline class="w-4 h-4" />
                        Información Adicional
                    </h3>
                    <div class="space-y-2 text-sm">
                        @if($announcement->expires_at)
                            <div class="flex items-center gap-2">
                                <x-solar-clock-circle-outline class="w-4 h-4 text-info-300" />
                                <span class="text-black-400">
                                    Este anuncio expira el {{ $announcement->expires_at->format('d/m/Y H:i') }}
                                    ({{ $announcement->expires_at->diffForHumans() }})
                                </span>
                            </div>
                        @endif
                        @if($announcement->auto_mark_read_after)
                            <div class="flex items-center gap-2">
                                <x-solar-check-read-outline class="w-4 h-4 text-info-300" />
                                <span class="text-black-400">
                                    Se marcará automáticamente como leído después de {{ $announcement->auto_mark_read_after }} días
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
            <!-- Estado del Anuncio -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Estado</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-success-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <x-solar-check-circle-outline class="w-8 h-8 text-success-300" />
                        </div>
                        <div class="text-sm font-medium text-success-300 mb-1">✓ Leído</div>
                        <div class="text-xs text-black-300">
                            Marcado como leído al abrir este anuncio
                        </div>
                    </div>

                    <hr class="border-white-200">

                    <div class="space-y-3">
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
                            <span class="text-sm text-black-300">Publicado:</span>
                            <span class="text-sm text-black-400">{{ $announcement->created_at->format('d/m/Y') }}</span>
                        </div>

                        @if($announcement->expires_at)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-black-300">Expira:</span>
                                <span class="text-sm text-black-400">{{ $announcement->expires_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Características -->
            <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                    <h2 class="text-lg font-semibold text-black-400 mb-0">Características</h2>
                </div>
                
                <div class="p-6 space-y-3">
                    @if($announcement->show_as_banner)
                        <div class="flex items-center gap-3 p-3 bg-primary-50 rounded border border-primary-100">
                            <x-solar-gallery-outline class="w-5 h-5 text-primary-300" />
                            <div>
                                <div class="text-sm font-medium text-primary-300">Banner Activo</div>
                                <div class="text-xs text-primary-300">Se muestra en el carrusel del dashboard</div>
                            </div>
                        </div>
                    @endif

                    @if($announcement->show_popup)
                        <div class="flex items-center gap-3 p-3 bg-warning-50 rounded border border-warning-100">
                            <x-solar-widget-3-outline class="w-5 h-5 text-warning-300" />
                            <div>
                                <div class="text-sm font-medium text-warning-300">Popup Automático</div>
                                <div class="text-xs text-warning-300">Se muestra automáticamente al entrar</div>
                            </div>
                        </div>
                    @endif

                    @if($announcement->send_email)
                        <div class="flex items-center gap-3 p-3 bg-info-50 rounded border border-info-100">
                            <x-solar-letter-outline class="w-5 h-5 text-info-300" />
                            <div>
                                <div class="text-sm font-medium text-info-300">Notificación Email</div>
                                <div class="text-xs text-info-300">Se envió por correo electrónico</div>
                            </div>
                        </div>
                    @endif

                    @if(!$announcement->show_as_banner && !$announcement->show_popup && !$announcement->send_email)
                        <div class="text-center py-4">
                            <x-solar-info-circle-outline class="w-8 h-8 text-black-200 mx-auto mb-2" />
                            <p class="text-sm text-black-300">Anuncio estándar sin características especiales</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Navegación -->
            <div class="bg-white-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-black-300 mb-4">Navegación</h3>
                <div class="space-y-3">
                    <a href="{{ route('tenant.admin.announcements.index', $store->slug) }}" 
                       class="w-full btn-primary px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                        <x-solar-arrow-left-outline class="w-4 h-4" />
                        Volver al Tablón
                    </a>

                    @if($announcement->banner_link)
                        <a href="{{ $announcement->banner_link }}" 
                           target="_blank"
                           class="w-full btn-outline-info px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <x-solar-link-outline class="w-4 h-4" />
                            Enlace Relacionado
                        </a>
                    @endif

                    <a href="{{ route('tenant.admin.dashboard', $store->slug) }}" 
                       class="w-full btn-outline-secondary px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                        <x-solar-home-2-outline class="w-4 h-4" />
                        Ir al Dashboard
                    </a>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="bg-info-50 rounded-lg p-6 border border-info-100">
                <h3 class="text-sm font-medium text-info-300 mb-3 flex items-center gap-2">
                    <x-solar-question-circle-outline class="w-4 h-4" />
                    ¿Necesitas Ayuda?
                </h3>
                <p class="text-sm text-info-300 mb-3">
                    Si tienes preguntas sobre este anuncio o necesitas soporte, puedes contactarnos.
                </p>
                <a href="{{ route('tenant.admin.tickets.create', $store->slug) }}" 
                   class="btn-info px-4 py-2 rounded-lg text-sm flex items-center gap-2 w-full justify-center">
                    <x-solar-chat-round-outline class="w-4 h-4" />
                    Crear Ticket de Soporte
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Marcar como leído al cargar la página (ya se hace en el backend)
console.log('📢 Anuncio marcado como leído:', '{{ $announcement->title }}');
</script>
@endpush
@endsection 