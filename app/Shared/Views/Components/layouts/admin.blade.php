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
        $appFavicon = env('APP_FAVICON');
        $disk = config('filesystems.default');
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