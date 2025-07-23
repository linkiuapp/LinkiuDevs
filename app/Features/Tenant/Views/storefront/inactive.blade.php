<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $store->name }} - Tienda No Disponible</title>
    
    <!-- Meta tags -->
    <meta name="description" content="La tienda {{ $store->name }} no está disponible temporalmente">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-error-50 to-warning-50 min-h-screen flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white-50 rounded-2xl shadow-xl overflow-hidden text-center">
            <div class="px-8 py-12">
                <!-- Icono de estado -->
                <div class="mb-8">
                    @if($store->status === 'inactive')
                        <div class="w-24 h-24 mx-auto bg-warning-100 rounded-full flex items-center justify-center">
                            <x-solar-pause-circle-outline class="w-12 h-12 text-warning-300" />
                        </div>
                    @elseif($store->status === 'suspended')
                        <div class="w-24 h-24 mx-auto bg-error-100 rounded-full flex items-center justify-center">
                            <x-solar-forbidden-circle-outline class="w-12 h-12 text-error-300" />
                        </div>
                    @endif
                </div>
                
                <!-- Título -->
                <h1 class="text-3xl font-bold text-black-400 mb-4">
                    {{ $store->name }}
                </h1>
                
                <!-- Mensaje según el estado -->
                @if($store->status === 'inactive')
                    <h2 class="text-xl font-semibold text-warning-300 mb-4">
                        Tienda Temporalmente Inactiva
                    </h2>
                    <p class="text-black-300 mb-8">
                        Esta tienda está temporalmente inactiva. Por favor, inténtalo más tarde o contacta al administrador.
                    </p>
                @elseif($store->status === 'suspended')
                    <h2 class="text-xl font-semibold text-error-300 mb-4">
                        Tienda Suspendida
                    </h2>
                    <p class="text-black-300 mb-8">
                        Esta tienda ha sido suspendida temporalmente. Para más información, contacta al administrador.
                    </p>
                @endif
                
                <!-- Información de contacto si está disponible -->
                @if($store->email)
                <div class="bg-white-100 rounded-xl p-6 mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-3">
                        Contacto
                    </h3>
                    <a href="mailto:{{ $store->email }}" 
                       class="inline-flex items-center gap-2 text-primary-300 hover:text-primary-200 transition-colors">
                        <x-solar-letter-outline class="w-5 h-5" />
                        {{ $store->email }}
                    </a>
                </div>
                @endif
                
                <!-- Enlace para volver -->
                <div class="text-center">
                    <a href="/" 
                       class="inline-flex items-center gap-2 text-primary-300 hover:text-primary-200 transition-colors">
                        <x-solar-arrow-left-outline class="w-5 h-5" />
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 