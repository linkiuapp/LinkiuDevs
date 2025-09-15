<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $store->name }}</title>
    
    <!-- Favicon -->
    @php
        // Función para obtener el favicon más reciente del SuperAdmin
        $getLatestAppFavicon = function() {
            try {
                // Primero verificar sesión temporal
                $tempFavicon = session('temp_app_favicon');
                if ($tempFavicon) {
                    return $tempFavicon;
                }
                
                // Luego verificar variable de entorno
                $envFavicon = env('APP_FAVICON');
                if ($envFavicon) {
                    return $envFavicon;
                }
                
                // Temporalmente desactivado para evitar problemas con fileinfo
                // TODO: Reactivar cuando se solucione el problema de finfo en producción
                /*
                // Finalmente, buscar el favicon más reciente en S3
                if (config('filesystems.disks.s3.bucket')) {
                    try {
                        $files = Storage::disk('public')->files('system');
                        $faviconFiles = array_filter($files, function($file) {
                            return str_contains(basename($file), 'favicon_');
                        });
                    } catch (\Exception $e) {
                        Log::error('Error accessing storage files: ' . $e->getMessage());
                        $faviconFiles = [];
                    }
                    
                    if (!empty($faviconFiles)) {
                        // Ordenar por fecha en el nombre del archivo (más reciente primero)
                        usort($faviconFiles, function($a, $b) {
                            $timeA = preg_match('/favicon_(\d+)\./', basename($a), $matchesA) ? (int)$matchesA[1] : 0;
                            $timeB = preg_match('/favicon_(\d+)\./', basename($b), $matchesB) ? (int)$matchesB[1] : 0;
                            return $timeB - $timeA; // Más reciente primero
                        });
                        
                        return $faviconFiles[0]; // Retornar el más reciente
                    }
                }
                */
                
                return null;
            } catch (\Exception $e) {
                Log::error('Error searching for app favicon: ' . $e->getMessage());
                return null;
            }
        };
        
        $appFavicon = $getLatestAppFavicon();
        $faviconSrc = asset('favicon.ico'); // Default fallback
        
        if ($appFavicon) {
            try {
                // Temporalmente simplificado para evitar problemas con fileinfo
                $faviconSrc = asset('storage/' . $appFavicon);
                /*
                if (config('filesystems.disks.s3.bucket')) {
                    $faviconSrc = \Storage::disk('public')->url($appFavicon);
                } else {
                    $faviconSrc = asset('storage/' . $appFavicon);
                }
                */
            } catch (\Exception $e) {
                Log::error('Error generating favicon URL: ' . $e->getMessage());
                $faviconSrc = asset('storage/' . $appFavicon);
            }
        }
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $store->design && $store->design->is_published && $store->design->favicon_url ? $store->design->favicon_url : $faviconSrc }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Additional Head Content -->
    @stack('styles')
</head>
<body class="bg-secondary-50 font-body">
    <!-- Sidebar del Admin de Tienda -->
    @include('shared::admin.tenant-sidebar', ['store' => $store])
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar del Admin de Tienda -->
        @include('shared::admin.tenant-navbar', ['store' => $store])
        
        <!-- Page Content -->
        <main class="main-content-inner">
            <!-- Page Header -->
            @hasSection('header')
                <div class="page-header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="heading-2 text-black-500 mb-1">@yield('title')</h1>
                            @hasSection('subtitle')
                                <p class="body-base text-black-300">@yield('subtitle')</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            @yield('actions')
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center gap-3">
                        <x-solar-check-circle-outline class="w-5 h-5 text-success-300" />
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center gap-3">
                        <x-solar-close-circle-outline class="w-5 h-5 text-error-300" />
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="ml-auto">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error mb-6" x-data="{ show: true }" x-show="show">
                    <div class="flex items-start gap-3">
                        <x-solar-close-circle-outline class="w-5 h-5 text-error-300 mt-0.5" />
                        <div class="flex-1">
                            <p class="font-medium mb-2">Por favor corrige los siguientes errores:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="body-small">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="ml-auto">
                            <x-solar-close-circle-outline class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @endif
            
            <!-- Main Content -->
            <div class="content-area">
                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        @include('shared::admin.footer')
    </div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black-500 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-accent-50 rounded-lg p-6 flex items-center gap-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-300"></div>
            <span class="body-base text-black-400">Cargando...</span>
        </div>
    </div>
    
    <!-- Scripts -->
    @stack('scripts')
    
    <!-- Store Data for JavaScript -->
    <script>
        window.store = {
            id: {{ $store->id }},
            name: '{{ $store->name }}',
            slug: '{{ $store->slug }}',
            status: '{{ $store->status }}',
            plan: {
                name: '{{ $store->plan->name ?? 'Basic' }}',
                limits: {
                    products: {{ $store->plan->max_products ?? 20 }},
                    categories: {{ $store->plan->max_categories ?? 3 }},
                    variables: {{ $store->plan->max_variables ?? 5 }},
                    coupons: {{ $store->plan->max_active_coupons ?? 1 }},
                    sliders: {{ $store->plan->max_slider ?? 1 }},
                    locations: {{ $store->plan->max_sedes ?? 1 }}
                }
            }
        };
    </script>
</body>
</html> 