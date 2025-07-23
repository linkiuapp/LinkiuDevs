@extends('shared::layouts.admin')

@section('title', 'Error 404')

@section('content')
<div class="card">
    <div class="py-16 lg:py-24 px-8 text-center">
        <!-- Icono de error -->
        <div class="mb-8 flex justify-center">
            <div class="bg-error-200 rounded-full p-8">
                <x-solar-forbidden-circle-outline class="w-32 h-32 text-error-50" />
            </div>
        </div>

        <!-- Título principal -->
        <h1 class="text-5xl font-black text-primary-400 mb-4">404</h1>
        
        <!-- Subtítulo -->
        <h2 class="text-xl font-semibold text-black-500 mb-4">Página no encontrada</h2>
        
        <!-- Descripción -->
        <p class="text-base text-black-300 mb-8 max-w-2xl mx-auto">
            Lo sentimos, la página que estás buscando no existe o ha sido movida. 
            Verifica la URL o regresa a la página principal.
        </p>

        <!-- Estadísticas de error -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 max-w-2xl mx-auto">
            <div class="bg-white-100 rounded-lg p-6">
                <x-solar-home-outline class="w-8 h-8 text-primary-200 mx-auto mb-2" />
                <p class="text-sm font-semibold text-black-300">Ir al inicio</p>
            </div>
            <div class="bg-white-100 rounded-lg p-6">
                <x-solar-refresh-outline class="w-8 h-8 text-success-200 mx-auto mb-2" />
                <p class="text-sm font-semibold text-black-300">Recargar página</p>
            </div>
            <div class="bg-white-100 rounded-lg p-6">
                <x-solar-chat-round-outline class="w-8 h-8 text-info-200 mx-auto mb-2" />
                <p class="text-sm font-semibold text-black-300">Contactar soporte</p>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('superlinkiu.dashboard') }}" 
               class="bg-primary-200 text-white-50 px-8 py-3 rounded-lg font-semibold hover:bg-primary-300 transition-colors duration-200 flex items-center space-x-2">
                <x-solar-home-outline class="w-5 h-5" />
                <span>Volver al Dashboard</span>
            </a>
            
            <button 
                onclick="history.back()"
                class="bg-white-100 text-black-500 px-8 py-3 rounded-lg font-semibold hover:bg-white-200 transition-colors duration-200 flex items-center space-x-2">
                <x-solar-arrow-left-outline class="w-5 h-5" />
                <span>Página Anterior</span>
            </button>
        </div>

        <!-- Enlaces adicionales -->
        <div class="mt-12 pt-8 border-t border-white-100">
            <h3 class="text-base font-semibold text-black-500 mb-4">¿Necesitas ayuda?</h3>
            <div class="flex flex-wrap items-center justify-center gap-6">
                <a href="#" class="text-primary-400 hover:text-primary-200 transition-colors duration-200 flex items-center space-x-2">
                    <x-solar-question-circle-outline class="w-5 h-5" />
                    <span>FAQ</span>
                </a>
                <a href="#" class="text-primary-400 hover:text-primary-200 transition-colors duration-200 flex items-center space-x-2">
                    <x-solar-document-text-outline class="w-5 h-5" />
                    <span>Documentación</span>
                </a>
                <a href="#" class="text-primary-400 hover:text-primary-200 transition-colors duration-200 flex items-center space-x-2">
                    <x-solar-chat-round-outline class="w-5 h-5" />
                    <span>Soporte</span>
                </a>
                <a href="#" class="text-primary-400 hover:text-primary-200 transition-colors duration-200 flex items-center space-x-2">
                    <x-solar-phone-outline class="w-5 h-5" />
                    <span>Contacto</span>
                </a>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="mt-8 p-6 bg-white-100 rounded-lg max-w-2xl mx-auto">
            <div class="flex items-center justify-center space-x-2 mb-3">
                <x-solar-info-circle-outline class="w-5 h-5 text-info-200" />
                <h4 class="text-base font-semibold text-black-500">Información técnica</h4>
            </div>
            <p class="text-xs text-black-300 mb-4">
                Si continúas experimentando problemas, por favor contacta a nuestro equipo de soporte con la siguiente información:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="bg-white-50 rounded p-3">
                    <strong class="text-black-500">Código de error:</strong> 404
                </div>
                <div class="bg-white-50 rounded p-3">
                    <strong class="text-black-500">Timestamp:</strong> {{ now()->format('Y-m-d H:i:s') }}
                </div>
                <div class="bg-white-50 rounded p-3">
                    <strong class="text-black-500">URL solicitada:</strong> {{ request()->fullUrl() }}
                </div>
                <div class="bg-white-50 rounded p-3">
                    <strong class="text-black-500">Navegador:</strong> {{ request()->userAgent() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 