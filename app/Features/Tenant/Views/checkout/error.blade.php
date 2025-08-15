@extends('frontend.layouts.app')

@section('content')
<div class="px-4 py-6 space-y-6">
    <!-- Header de error -->
    <div class="text-center">
        <div class="w-16 h-16 bg-error-300 rounded-full flex items-center justify-center mx-auto mb-4">
            <x-solar-close-circle-outline class="w-8 h-8 text-white-50" />
        </div>
        <h1 class="text-2xl font-bold text-black-500 mb-2">Error en el Pedido</h1>
        <p class="text-black-300">Ocurrió un problema al procesar tu pedido</p>
    </div>

    <!-- Mensaje de error -->
    <div class="bg-error-50 border border-error-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <x-solar-danger-triangle-outline class="w-5 h-5 text-error-300 flex-shrink-0 mt-0.5" />
            <div>
                <h3 class="font-semibold text-error-400 mb-1">Error:</h3>
                <p class="text-error-300 text-sm">
                    {{ $errorMessage ?? 'Ocurrió un error inesperado al procesar tu pedido. Por favor intenta nuevamente.' }}
                </p>
            </div>
        </div>
    </div>

    @if(config('app.debug') && isset($technicalError))
    <!-- Información técnica (solo en modo debug) -->
    <div class="bg-black-50 border border-black-200 rounded-lg p-4">
        <details class="cursor-pointer">
            <summary class="font-medium text-black-400 text-sm">Información técnica (Debug)</summary>
            <pre class="mt-2 text-xs text-black-300 overflow-x-auto">{{ $technicalError }}</pre>
        </details>
    </div>
    @endif

    <!-- Sugerencias -->
    <div class="bg-white-50 rounded-lg p-4 border border-white-200">
        <h3 class="font-semibold text-black-500 mb-3">¿Qué puedes hacer?</h3>
        <div class="space-y-2 text-sm text-black-400">
            <p>• Verifica que todos los datos estén correctos</p>
            <p>• Asegúrate de tener una conexión estable a internet</p>
            <p>• Si el problema persiste, contacta con la tienda</p>
            <p>• Puedes intentar realizar el pedido nuevamente</p>
        </div>
    </div>

    <!-- Acciones -->
    <div class="space-y-3">
        <a href="{{ route('tenant.checkout.create', $store->slug) }}" 
           class="block w-full bg-primary-300 hover:bg-primary-200 text-white-50 py-3 rounded-lg font-semibold transition-colors text-center">
            Intentar nuevamente
        </a>
        <a href="{{ route('tenant.cart.index', $store->slug) }}" 
           class="block w-full bg-white-200 hover:bg-white-300 text-black-500 py-3 rounded-lg font-medium transition-colors text-center">
            Volver al carrito
        </a>
        <a href="{{ route('tenant.home', $store->slug) }}" 
           class="block w-full bg-white-100 hover:bg-white-200 text-black-400 py-3 rounded-lg font-medium transition-colors text-center">
            Ir al inicio
        </a>
    </div>

    <!-- Contacto con la tienda -->
    <div class="bg-info-50 border border-info-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <x-solar-chat-dots-outline class="w-5 h-5 text-info-300 flex-shrink-0 mt-0.5" />
            <div>
                <h3 class="font-semibold text-info-400 mb-1">¿Necesitas ayuda?</h3>
                <p class="text-info-300 text-sm mb-2">
                    Contáctanos directamente para resolver cualquier inconveniente
                </p>
                <a href="{{ route('tenant.contact', $store->slug) }}" 
                   class="inline-flex items-center gap-1 text-info-300 hover:text-info-200 text-sm font-medium transition-colors">
                    <span>Ver información de contacto</span>
                    <x-solar-arrow-right-outline class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
