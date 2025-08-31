<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin {{ $store->name }} - Iniciar Sesión</title>
    
    <!-- Meta tags -->
    <meta name="description" content="Panel de administración para {{ $store->name }}">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-primary-50 to-secondary-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto px-4 py-8">
        <div class="bg-accent-50 rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-200 to-secondary-200 px-8 py-8 text-center">
                @if($store->logo_url)
                    <div class="mb-4">
                        <img src="{{ $store->logo_url }}" 
                             alt="{{ $store->name }}" 
                             class="h-16 w-auto mx-auto rounded-lg shadow-lg">
                    </div>
                @else
                    <div class="mb-4">
                        <div class="w-16 h-16 mx-auto bg-accent-50 rounded-full flex items-center justify-center shadow-lg">
                            <span class="text-xl font-bold text-primary-300">
                                {{ strtoupper(substr($store->name, 0, 2)) }}
                            </span>
                        </div>
                    </div>
                @endif
                
                <h1 class="text-2xl font-bold text-accent-50 mb-2">
                    {{ $store->name }}
                </h1>
                <p class="text-accent-100 text-sm">
                    Panel de Administración
                </p>
            </div>
            
            <!-- Formulario de login -->
            <div class="px-8 py-8">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-semibold text-black-400 mb-2">
                        Iniciar Sesión
                    </h2>
                    <p class="text-sm text-black-300">
                        Ingresa tus credenciales para acceder al panel
                    </p>
                </div>
                
                <form method="POST" action="{{ route('tenant.admin.login.submit', $store->slug) }}">
                    @csrf
                    
                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-black-300 mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative">
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   class="w-full pl-10 pr-4 py-3 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('email') border-error-200 @enderror"
                                   placeholder="admin@tienda.com"
                                   required>
                            <x-solar-letter-outline class="w-5 h-5 text-black-200 absolute left-3 top-3.5" />
                        </div>
                        @error('email')
                            <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-black-300 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="w-full pl-10 pr-4 py-3 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('password') border-error-200 @enderror"
                                   placeholder="••••••••"
                                   required>
                            <x-solar-lock-password-outline class="w-5 h-5 text-black-200 absolute left-3 top-3.5" />
                        </div>
                        @error('password')
                            <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Remember me -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="remember" 
                                   class="rounded border-accent-300 text-primary-200 focus:border-primary-200 focus:ring-primary-200">
                            <span class="ml-2 text-sm text-black-300">Recordarme</span>
                        </label>
                    </div>
                    
                    <!-- Submit button -->
                    <button type="submit" 
                            class="w-full bg-primary-200 hover:bg-primary-300 text-accent-50 font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                        <x-solar-login-3-outline class="w-5 h-5" />
                        Iniciar Sesión
                    </button>
                </form>
                
                <!-- Links adicionales -->
                <div class="mt-6 text-center">
                    <div class="border-t border-accent-100 pt-4">
                        <a href="{{ route('tenant.home', $store->slug) }}" 
                           class="inline-flex items-center gap-2 text-sm text-primary-300 hover:text-primary-200 transition-colors">
                            <x-solar-arrow-left-outline class="w-4 h-4" />
                            Volver a la tienda
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="mt-6 text-center">
            <p class="text-xs text-black-200">
                ¿Problemas para acceder? Contacta al Super Admin
            </p>
        </div>
    </div>
    
    <!-- Script para mejorar UX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus en el primer campo
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.focus();
            }
            
            // Mostrar/ocultar contraseña (opcional)
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.target.closest('form').submit();
                    }
                });
            }
        });
    </script>
</body>
</html> 