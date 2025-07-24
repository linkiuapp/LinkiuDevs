@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('shared::layouts.admin')

@section('title', 'Mi Perfil')

@section('content')
<div class="container-fluid">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-lg font-bold text-black-400">Mi Perfil</h1>
            <p class="text-sm text-black-300">Gestiona tu información personal y configuración de cuenta</p>
        </div>

        <!-- Messages -->
        @if (session('status') === 'profile-updated')
            <div class="mb-6 bg-success-50 border-l-4 border-success-300 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <x-solar-check-circle-outline class="w-5 h-5 text-success-300 mr-3" />
                    <p class="text-sm text-success-300">Perfil actualizado correctamente</p>
                </div>
            </div>
        @endif

        @if (session('status') === 'password-updated')
            <div class="mb-6 bg-success-50 border-l-4 border-success-300 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <x-solar-shield-check-outline class="w-5 h-5 text-success-300 mr-3" />
                    <p class="text-sm text-success-300">Contraseña actualizada correctamente</p>
                </div>
            </div>
        @endif

        @if (session('status') === 'avatar-deleted')
            <div class="mb-6 bg-info-50 border-l-4 border-info-300 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <x-solar-trash-bin-minimalistic-outline class="w-5 h-5 text-info-300 mr-3" />
                    <p class="text-sm text-info-300">Avatar eliminado correctamente</p>
                </div>
            </div>
        @endif

        @if (session('status') === 'app-settings-updated')
            <div class="mb-6 bg-success-50 border-l-4 border-success-300 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <x-solar-settings-outline class="w-5 h-5 text-success-300 mr-3" />
                    <p class="text-sm text-success-300">Configuración de la aplicación actualizada correctamente</p>
                </div>
            </div>
        @endif

        @if (session('status') === 'app-settings-error')
            <div class="mb-6 bg-error-50 border-l-4 border-error-300 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <x-solar-danger-triangle-outline class="w-5 h-5 text-error-300 mr-3" />
                    <p class="text-sm text-error-300">Error al actualizar la configuración. Intenta nuevamente.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Avatar Section -->
            <div class="lg:col-span-1">
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-300 mb-0">Foto de Perfil</h2>
                    </div>
                    <div class="p-6 text-center">
                        <!-- Avatar Display -->
                        <div class="mb-6">
                            <img src="{{ asset('storage/' . $user->avatar_path) }}" 
                                 alt="Avatar de {{ $user->name }}" 
                                 class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white-200">
                        </div>

                        <!-- Avatar Upload Form -->
                        <form action="{{ route('superlinkiu.profile.update') }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            
                            <div class="mb-4">
                                <label for="avatar" class="block text-sm text-black-300 mb-2">Nueva imagen</label>
                                <input type="file" 
                                       name="avatar" 
                                       id="avatar" 
                                       accept="image/*"
                                       class="block w-full text-sm text-black-300
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-sm
                                              file:bg-primary-50 file:text-primary-200
                                              hover:file:bg-primary-100
                                              border border-white-200 rounded-lg
                                              focus:ring-2 focus:ring-primary-100 focus:border-primary-200">
                                @error('avatar')
                                    <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <button type="submit" 
                                        class="w-full bg-primary-200 text-white-50 py-2 px-4 rounded-lg text-sm font-medium
                                               hover:bg-primary-300 focus:ring-2 focus:ring-primary-100 transition-colors">
                                    <x-solar-camera-outline class="w-4 h-4 inline mr-2" />
                                    Subir Avatar
                                </button>
                            </div>
                        </form>

                        <!-- Delete Avatar Form (Separate) -->
                        @if($user->avatar_path)
                            <form action="{{ route('superlinkiu.profile.delete-avatar') }}" method="POST" class="mb-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-error-200 text-white-50 py-2 px-4 rounded-lg text-sm font-medium
                                               hover:bg-error-300 focus:ring-2 focus:ring-error-100 transition-colors"
                                        onclick="return confirm('¿Estás seguro de eliminar tu avatar?')">
                                    <x-solar-trash-bin-minimalistic-outline class="w-4 h-4 inline mr-2" />
                                    Eliminar Avatar
                                </button>
                            </form>
                        @endif

                        <p class="text-xs text-black-300">
                            Formatos permitidos: JPG, PNG, GIF<br>
                            Tamaño máximo: 2MB
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="lg:col-span-2">
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden mb-8">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-300 mb-0">Información Personal</h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('superlinkiu.profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm text-black-300 mb-2">Nombre completo</label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name', $user->name) }}"
                                           class="w-full px-4 py-3 border border-white-200 rounded-lg text-sm
                                                  focus:ring-2 focus:ring-primary-100 focus:border-primary-200
                                                  transition-colors">
                                    @error('name')
                                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm text-black-300 mb-2">Correo electrónico</label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email', $user->email) }}"
                                           class="w-full px-4 py-3 border border-white-200 rounded-lg text-sm
                                                  focus:ring-2 focus:ring-primary-100 focus:border-primary-200
                                                  transition-colors">
                                    @error('email')
                                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" 
                                        class="bg-primary-200 text-white-50 py-3 px-6 rounded-lg text-sm font-medium
                                               hover:bg-primary-300 focus:ring-2 focus:ring-primary-100 transition-colors">
                                    <x-solar-diskette-outline class="w-5 h-5 inline mr-2" />
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-300 mb-0">Cambiar Contraseña</h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('superlinkiu.profile.update-password') }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-6">
                                <!-- Current Password -->
                                <div>
                                    <label for="current_password" class="block text-sm text-black-300 mb-2">Contraseña actual</label>
                                    <input type="password" 
                                           name="current_password" 
                                           id="current_password"
                                           class="w-full px-4 py-3 border border-white-200 rounded-lg text-sm
                                                  focus:ring-2 focus:ring-primary-100 focus:border-primary-200
                                                  transition-colors">
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- New Password -->
                                    <div>
                                        <label for="password" class="block text-sm text-black-300 mb-2">Nueva contraseña</label>
                                        <input type="password" 
                                               name="password" 
                                               id="password"
                                               class="w-full px-4 py-3 border border-white-200 rounded-lg text-sm
                                                      focus:ring-2 focus:ring-primary-100 focus:border-primary-200
                                                      transition-colors">
                                        @error('password')
                                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Confirm Password -->
                                    <div>
                                        <label for="password_confirmation" class="block text-sm text-black-300 mb-2">Confirmar contraseña</label>
                                        <input type="password" 
                                               name="password_confirmation" 
                                               id="password_confirmation"
                                               class="w-full px-4 py-3 border border-white-200 rounded-lg text-sm
                                                      focus:ring-2 focus:ring-primary-100 focus:border-primary-200
                                                      transition-colors">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" 
                                        class="bg-secondary-200 text-white-50 py-3 px-6 rounded-lg text-sm font-medium
                                               hover:bg-secondary-300 focus:ring-2 focus:ring-secondary-100 transition-colors">
                                    <x-solar-shield-check-outline class="w-5 h-5 inline mr-2" />
                                    Actualizar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- App Settings -->
                <div class="bg-white-50 rounded-lg p-0 overflow-hidden mt-8">
                    <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                        <h2 class="text-lg font-semibold text-black-300 mb-0">Configuración de la Aplicación</h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('superlinkiu.profile.update-app-settings') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-6">
                                <!-- App Name -->
                                <div>
                                    <label for="app_name" class="block text-sm text-black-300 mb-2">Nombre de la Aplicación</label>
                                    <input type="text" 
                                           name="app_name" 
                                           id="app_name"
                                           value="{{ old('app_name', config('app.name')) }}"
                                           class="w-full px-4 py-3 border border-white-200 rounded-lg text-sm
                                                  focus:ring-2 focus:ring-primary-100 focus:border-primary-200
                                                  transition-colors">
                                    @error('app_name')
                                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- App Logo -->
                                    <div>
                                        <label for="app_logo" class="block text-sm text-black-300 mb-2">Logo de la Aplicación</label>
                                        
                                        <!-- Current Logo Preview -->
                                        @php
                                            // Priorizar logo temporal de sesión, luego .env
                                            $tempLogo = session('temp_app_logo');
                                            $appLogo = $tempLogo ?: env('APP_LOGO');
                                            
                                            // Detectar automáticamente el disk correcto
                                            $disk = 'public'; // Default para local
                                            if (config('filesystems.disks.storage')) {
                                                $disk = 'storage'; // Laravel Cloud
                                            } elseif (config('filesystems.default') !== 'local') {
                                                $disk = config('filesystems.default'); // Otros servicios S3
                                            }
                                        @endphp
                                        @if($appLogo && Storage::disk($disk)->exists($appLogo))
                                            <div class="mb-3">
                                                <p class="text-xs text-black-200 mb-2">Logo actual:</p>
                                                <img src="{{ Storage::disk($disk)->url($appLogo) }}" 
                                                     alt="Logo actual" 
                                                     class="h-12 object-contain border border-white-200 rounded-lg p-2">
                                            </div>
                                        @endif
                                        
                                        <input type="file" 
                                               name="app_logo" 
                                               id="app_logo" 
                                               accept="image/*"
                                               class="block w-full text-sm text-black-300
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-lg file:border-0
                                                      file:text-sm
                                                      file:bg-primary-50 file:text-primary-200
                                                      hover:file:bg-primary-100
                                                      border border-white-200 rounded-lg
                                                      focus:ring-2 focus:ring-primary-100 focus:border-primary-200">
                                        <p class="text-xs text-black-200 mt-1">
                                            Formatos: JPG, PNG, GIF, SVG<br>
                                            Tamaño recomendado: 200x50px | Máx: 2MB
                                        </p>
                                        @error('app_logo')
                                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- App Favicon -->
                                    <div>
                                        <label for="app_favicon" class="block text-sm text-black-300 mb-2">Favicon</label>
                                        
                                        <!-- Current Favicon Preview -->
                                        @php
                                            // Priorizar favicon temporal de sesión, luego .env
                                            $tempFavicon = session('temp_app_favicon');
                                            $appFavicon = $tempFavicon ?: env('APP_FAVICON');
                                            
                                            // Detectar automáticamente el disk correcto
                                            $disk = 'public'; // Default para local
                                            if (config('filesystems.disks.storage')) {
                                                $disk = 'storage'; // Laravel Cloud
                                            } elseif (config('filesystems.default') !== 'local') {
                                                $disk = config('filesystems.default'); // Otros servicios S3
                                            }
                                        @endphp
                                        @if($appFavicon && Storage::disk($disk)->exists($appFavicon))
                                            <div class="mb-3">
                                                <p class="text-xs text-black-200 mb-2">Favicon actual:</p>
                                                <img src="{{ Storage::disk($disk)->url($appFavicon) }}" 
                                                     alt="Favicon actual" 
                                                     class="w-8 h-8 object-contain border border-white-200 rounded p-1">
                                            </div>
                                        @endif
                                        
                                        <input type="file" 
                                               name="app_favicon" 
                                               id="app_favicon" 
                                               accept=".ico,.png"
                                               class="block w-full text-sm text-black-300
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-lg file:border-0
                                                      file:text-sm
                                                      file:bg-secondary-50 file:text-secondary-200
                                                      hover:file:bg-secondary-100
                                                      border border-white-200 rounded-lg
                                                      focus:ring-2 focus:ring-secondary-100 focus:border-secondary-200">
                                        <p class="text-xs text-black-200 mt-1">
                                            Formatos: ICO, PNG<br>
                                            Tamaño: 16x16, 32x32 o 64x64px | Máx: 1MB
                                        </p>
                                        @error('app_favicon')
                                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" 
                                        class="bg-info-200 text-white-50 py-3 px-6 rounded-lg text-sm font-medium
                                               hover:bg-info-300 focus:ring-2 focus:ring-info-100 transition-colors">
                                    <x-solar-settings-outline class="w-5 h-5 inline mr-2" />
                                    Actualizar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-submit form when file is selected
document.getElementById('avatar').addEventListener('change', function() {
    if (this.files[0]) {
        // Opcional: mostrar preview antes de subir
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('img[alt*="Avatar"]').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endsection 