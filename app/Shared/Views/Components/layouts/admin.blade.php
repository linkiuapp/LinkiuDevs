@php
use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{config('app.name', 'Linkiu.bio')}} - @yield('title', 'Super Linkiu')</title>
    
    <!-- Favicon -->
    @php
        // Priorizar favicon temporal de sesión, luego .env, luego fallback  
        $tempFavicon = session('temp_app_favicon');
        $appFavicon = $tempFavicon ?: env('APP_FAVICON');
        
        // Detectar automáticamente el disk correcto (misma lógica que sidebar)
        $disk = 'public'; // Default fallback
        
        // En Laravel Cloud existe el disk 'storage'
        if (config('filesystems.disks.storage')) {
            $disk = 'storage';
        } else {
            // En local y otros entornos, usar el disk por defecto o public
            $defaultDisk = config('filesystems.default', 'public');
            
            // Si el disk por defecto es 'local', usar 'public' para URLs públicas
            if ($defaultDisk === 'local') {
                $disk = 'public';
            } else {
                $disk = $defaultDisk;
            }
        }
    @endphp
    
    @if($appFavicon && Storage::disk($disk)->exists($appFavicon))
        <link rel="icon" type="image/x-icon" href="{{ Storage::disk($disk)->url($appFavicon) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-inter bg-black-50/70">
    <x-admin-sidebar />
    <x-admin-navbar />
    
    <!-- Main content -->
    <main class="main-content">
        @yield('content')
    </main>

    <x-admin-footer />
    
    @stack('scripts')
</body>
</html>