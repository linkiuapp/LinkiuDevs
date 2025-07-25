@extends('shared::layouts.admin')
@section('title', 'Perfil de Administrador')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="container-fluid">
    <!-- Header con título y botón de acción -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Perfil de Administrador</h1>
    </div>
    
    @if(session('success'))
    <div class="alert-success mb-6">
        <div class="flex items-start gap-3 flex-1">
            <x-solar-check-circle-bold class="w-6 h-6 shrink-0 mt-1" />
            <div class="flex-1">
                <h4 class="font-semibold mb-1">¡Éxito!</h4>
                <p class="text-sm font-normal opacity-90">{{ session('success') }}</p>
            </div>
        </div>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <x-solar-close-circle-outline class="w-5 h-5" />
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert-error mb-6">
        <div class="flex items-start gap-3 flex-1">
            <x-solar-close-circle-bold class="w-6 h-6 shrink-0 mt-1" />
            <div class="flex-1">
                <h4 class="font-semibold mb-1">Error</h4>
                <p class="text-sm font-normal opacity-90">{{ session('error') }}</p>
            </div>
        </div>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <x-solar-close-circle-outline class="w-5 h-5" />
        </button>
    </div>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna de información personal -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <div class="profile-pic mb-6">
                            {{-- 🔧 DEBUG INFO (TEMPORAL) --}}
                            <div class="bg-error-50 border border-error-200 rounded p-3 mb-4 text-xs">
                                <strong>🔍 DEBUG Avatar:</strong><br>
                                <strong>avatar_path (BD):</strong> {{ $user->avatar_path ?? 'NULL' }}<br>
                                <strong>avatar_url (accessor):</strong> {{ $user->avatar_url ?? 'NULL' }}<br>
                                <strong>S3 directo:</strong> {{ $user->avatar_path ? Storage::disk('s3')->url($user->avatar_path) : 'No path' }}<br>
                                <strong>Local asset:</strong> {{ $user->avatar_path ? asset('storage/' . $user->avatar_path) : 'No path' }}<br>
                                <strong>Usado en img src:</strong> <code>{{ $user->avatar_url }}</code>
                            </div>
                            
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="Avatar" class="rounded-full mx-auto" width="150">
                            @else
                                <div class="w-32 h-32 bg-primary-200 rounded-full flex items-center justify-center mx-auto">
                                    <span class="text-white-50 text-4xl font-medium">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            <div class="mt-4">
                                <form action="{{ route('superlinkiu.profile.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="email" value="{{ $user->email }}">
                                    <div class="mb-3">
                                        <input type="file" name="avatar" class="block w-full text-sm text-black-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-300 hover:file:bg-primary-100 @error('avatar') border-error-200 @enderror">
                                        @error('avatar')
                                            <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn-primary text-sm px-4 py-2">Actualizar imagen</button>
                                </form>
                            </div>
                        </div>
                        <h4 class="text-xl font-bold mb-1">{{ $user->name }}</h4>
                        <p class="text-black-200">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="border-t border-white-100 p-4">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <span class="text-sm text-black-200">Rol</span>
                            <h5 class="font-semibold text-black-400">Super Admin</h5>
                        </div>
                        <div>
                            <span class="text-sm text-black-200">Último acceso</span>
                            <h5 class="font-semibold text-black-400">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'N/A' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Columna de edición de perfil -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <div class="flex border-b border-white-100">
                        <button type="button" class="tab-btn tab-active" data-tab="profile">
                            <x-solar-user-outline class="w-4 h-4 mr-2" />
                            Perfil
                        </button>
                        <button type="button" class="tab-btn" data-tab="settings">
                            <x-solar-settings-outline class="w-4 h-4 mr-2" />
                            Configuración
                        </button>
                    </div>
                </div>
                
                <div class="tab-content">
                    <!-- Tab de perfil -->
                    <div id="profile-tab" class="tab-pane active">
                        <div class="card-body">
                            <form action="{{ route('superlinkiu.profile.update') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="name" class="form-input @error('name') border-error-200 @enderror" value="{{ old('name', $user->name) }}">
                                    @error('name')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Correo electrónico</label>
                                    <input type="email" name="email" class="form-input @error('email') border-error-200 @enderror" value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Contraseña actual</label>
                                    <input type="password" name="current_password" class="form-input @error('current_password') border-error-200 @enderror">
                                    <p class="text-xs text-black-200 mt-1">Dejar en blanco si no desea cambiar la contraseña</p>
                                    @error('current_password')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Nueva contraseña</label>
                                    <input type="password" name="password" class="form-input @error('password') border-error-200 @enderror">
                                    @error('password')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Confirmar nueva contraseña</label>
                                    <input type="password" name="password_confirmation" class="form-input">
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="btn-primary">Guardar cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tab de configuración -->
                    <div id="settings-tab" class="tab-pane hidden">
                        <div class="card-body">
                            <form action="{{ route('superlinkiu.profile.update-system') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label">Nombre de la aplicación</label>
                                    <input type="text" name="app_name" class="form-input @error('app_name') border-error-200 @enderror" value="{{ old('app_name', config('app.name')) }}">
                                    @error('app_name')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Logo de la aplicación</label>
                                    <input type="file" name="app_logo" class="block w-full text-sm text-black-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-300 hover:file:bg-primary-100 @error('app_logo') border-error-200 @enderror">
                                    <p class="text-xs text-black-200 mt-1">Tamaño recomendado: 200x50px</p>
                                    @error('app_logo')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Favicon</label>
                                    <input type="file" name="app_favicon" class="block w-full text-sm text-black-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-300 hover:file:bg-primary-100 @error('app_favicon') border-error-200 @enderror">
                                    <p class="text-xs text-black-200 mt-1">Tamaño recomendado: 32x32px</p>
                                    @error('app_favicon')
                                        <p class="text-error-200 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="btn-primary">Guardar configuración</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    // Manejo de tabs
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Desactivar todos los botones y ocultar todos los paneles
                tabButtons.forEach(btn => btn.classList.remove('tab-active'));
                tabPanes.forEach(pane => pane.classList.add('hidden'));
                
                // Activar el botón y mostrar el panel seleccionado
                this.classList.add('tab-active');
                document.getElementById(tabId + '-tab').classList.remove('hidden');
            });
        });
    });
</script>
@endpush
@endsection