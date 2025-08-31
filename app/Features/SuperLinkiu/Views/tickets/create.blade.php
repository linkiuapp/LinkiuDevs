@extends('shared::layouts.admin')

@section('title', 'Crear Nuevo Ticket')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Crear Nuevo Ticket</h1>
        <a href="{{ route('superlinkiu.tickets.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver
        </a>
    </div>

    <form action="{{ route('superlinkiu.tickets.store') }}" method="POST">
        @csrf
        
        <!-- Card única con toda la información -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Información del Ticket</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna izquierda -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Tienda <span class="text-error-300">*</span>
                            </label>
                            <select name="store_id" 
                                    class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('store_id') border-error-200 @enderror"
                                    required>
                                <option value="">Seleccionar tienda</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('store_id')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Título <span class="text-error-300">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('title') border-error-200 @enderror"
                                   placeholder="Título descriptivo del problema"
                                   required>
                            @error('title')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Categoría <span class="text-error-300">*</span>
                            </label>
                            <select name="category" 
                                    class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('category') border-error-200 @enderror"
                                    required>
                                <option value="">Seleccionar categoría</option>
                                <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Técnico</option>
                                <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Facturación</option>
                                <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General</option>
                                <option value="feature_request" {{ old('category') == 'feature_request' ? 'selected' : '' }}>Solicitud de Función</option>
                            </select>
                            @error('category')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Prioridad <span class="text-error-300">*</span>
                            </label>
                            <select name="priority" 
                                    class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('priority') border-error-200 @enderror"
                                    required>
                                <option value="">Seleccionar prioridad</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('priority')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Asignar a
                            </label>
                            <select name="assigned_to" 
                                    class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('assigned_to') border-error-200 @enderror">
                                <option value="">Sin asignar</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ old('assigned_to') == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Información de ayuda -->
                        <div class="bg-info-100 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <x-solar-info-circle-outline class="w-5 h-5 text-info-300 flex-shrink-0 mt-0.5" />
                                <div>
                                    <h4 class="text-sm font-medium text-info-300 mb-1">Información</h4>
                                    <p class="text-sm text-info-300">
                                        El ticket se creará con estado "Abierto" y se generará automáticamente un número único.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción (ancho completo) -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-black-300 mb-2">
                        Descripción <span class="text-error-300">*</span>
                    </label>
                    <textarea name="description" rows="6"
                              class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('description') border-error-200 @enderror"
                              placeholder="Describe detalladamente el problema o solicitud..."
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-black-200 mt-1">
                        Proporciona toda la información relevante para ayudar a resolver el ticket más rápidamente.
                    </p>
                </div>
            </div>

            <!-- Footer con botones -->
            <div class="border-t border-accent-100 bg-accent-50 px-6 py-4">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('superlinkiu.tickets.index') }}"
                        class="btn-outline-secondary px-6 py-2 rounded-lg">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                        <x-solar-add-circle-outline class="w-5 h-5" />
                        Crear Ticket
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection 