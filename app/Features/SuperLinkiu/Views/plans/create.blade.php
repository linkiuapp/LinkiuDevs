@extends('shared::layouts.admin')

@section('title', 'Crear Nuevo Plan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl text-black-500 mb-0">Crear Nuevo Plan</h1>
            <p class="text-black-300 mt-1">Configura un nuevo plan de suscripción</p>
        </div>
        <a href="{{ route('superlinkiu.plans.index') }}" class="bg-accent-100 hover:bg-accent-200 text-black-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver a Planes
        </a>
    </div>

    <!-- Formulario -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-3xl text-black-500 mb-0">Información del Plan</h2>
        </div>
        
        <form action="{{ route('superlinkiu.plans.store') }}" method="POST" class="p-6" x-data="createPlan">
            @csrf
            
            <!-- Información Básica -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Nombre del Plan <span class="text-error-300">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('name') border-error-200 @enderror"
                           placeholder="Ej: Master Plan">
                    @error('name')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Precio Base (COP) <span class="text-error-300">*</span>
                    </label>
                    <input type="number" 
                           name="price" 
                           value="{{ old('price') }}"
                           min="0"
                           step="1000"
                           class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('price') border-error-200 @enderror"
                           placeholder="60000">
                    @error('price')
                        <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Descripción -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-black-400 mb-2">
                    Descripción
                </label>
                <textarea name="description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('description') border-error-200 @enderror"
                          placeholder="Describe las características principales del plan">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                @enderror
            </div>

            <!-- Precios por Período -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Precios por Período</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Mensual (COP)
                        </label>
                        <input type="number" 
                               name="prices[monthly]" 
                               value="{{ old('prices.monthly') }}"
                               min="0"
                               step="1000"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent"
                               placeholder="60000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Trimestral (COP)
                        </label>
                        <input type="number" 
                               name="prices[quarterly]" 
                               value="{{ old('prices.quarterly') }}"
                               min="0"
                               step="1000"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent"
                               placeholder="160000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Semestral (COP)
                        </label>
                        <input type="number" 
                               name="prices[semester]" 
                               value="{{ old('prices.semester') }}"
                               min="0"
                               step="1000"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent"
                               placeholder="320000">
                    </div>
                </div>
            </div>

            <!-- Límites del Plan -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Límites del Plan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Productos <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_products" 
                               value="{{ old('max_products', 20) }}"
                               min="1"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_products') border-error-200 @enderror">
                        @error('max_products')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Sliders <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_slider" 
                               value="{{ old('max_slider', 1) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_slider') border-error-200 @enderror">
                        @error('max_slider')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Promociones <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_active_promotions" 
                               value="{{ old('max_active_promotions', 1) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_active_promotions') border-error-200 @enderror">
                        @error('max_active_promotions')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Cupones <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_active_coupons" 
                               value="{{ old('max_active_coupons', 1) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_active_coupons') border-error-200 @enderror">
                        @error('max_active_coupons')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Categorías <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_categories" 
                               value="{{ old('max_categories', 3) }}"
                               min="1"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_categories') border-error-200 @enderror">
                        @error('max_categories')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Sedes <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_sedes" 
                               value="{{ old('max_sedes', 1) }}"
                               min="1"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_sedes') border-error-200 @enderror">
                        @error('max_sedes')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Administradores <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_admins" 
                               value="{{ old('max_admins', 1) }}"
                               min="1"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_admins') border-error-200 @enderror">
                        @error('max_admins')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Zonas de Reparto <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="max_delivery_zones" 
                               value="{{ old('max_delivery_zones', 1) }}"
                               min="1"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_delivery_zones') border-error-200 @enderror">
                        @error('max_delivery_zones')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Soporte -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Configuración de Soporte</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Nivel de Soporte <span class="text-error-300">*</span>
                        </label>
                        <select name="support_level" 
                                class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('support_level') border-error-200 @enderror">
                            <option value="basic" {{ old('support_level') == 'basic' ? 'selected' : '' }}>Básico</option>
                            <option value="priority" {{ old('support_level') == 'priority' ? 'selected' : '' }}>Prioritario</option>
                            <option value="premium" {{ old('support_level') == 'premium' ? 'selected' : '' }}>Premium</option>
                        </select>
                        @error('support_level')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Tiempo de Respuesta (horas) <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="support_response_time" 
                               value="{{ old('support_response_time', 24) }}"
                               min="1"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('support_response_time') border-error-200 @enderror">
                        @error('support_response_time')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Características del Plan -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Características del Plan</h3>
                <div x-data="{ features: @json(old('features_list', [])) }">
                    <div class="space-y-2 mb-4">
                        <template x-for="(feature, index) in features" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="text" 
                                       :name="`features_list[${index}]`"
                                       x-model="features[index]"
                                       class="flex-1 px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent"
                                       placeholder="Característica del plan">
                                <button type="button" 
                                        @click="features.splice(index, 1)"
                                        class="bg-error-50 hover:bg-error-100 text-error-300 p-2 rounded-lg transition-colors">
                                    <x-solar-trash-bin-trash-outline class="w-4 h-4" />
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" 
                            @click="features.push('')"
                            class="bg-primary-50 hover:bg-primary-100 text-primary-300 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                        <x-solar-add-circle-outline class="w-4 h-4" />
                        Agregar Característica
                    </button>
                </div>
            </div>

            <!-- Configuración Adicional -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Configuración Adicional</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Moneda <span class="text-error-300">*</span>
                        </label>
                        <select name="currency" 
                                class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('currency') border-error-200 @enderror">
                            <option value="COP" {{ old('currency') == 'COP' ? 'selected' : '' }}>COP - Peso Colombiano</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Duración (días) <span class="text-error-300">*</span>
                        </label>
                        <input type="number" 
                               name="duration_in_days" 
                               value="{{ old('duration_in_days', 30) }}"
                               min="1"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('duration_in_days') border-error-200 @enderror">
                        @error('duration_in_days')
                            <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Días de Prueba
                        </label>
                        <input type="number" 
                               name="trial_days" 
                               value="{{ old('trial_days', 0) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Orden de Visualización
                        </label>
                        <input type="number" 
                               name="sort_order" 
                               value="{{ old('sort_order', 0) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Opciones -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-black-400 mb-4">Opciones del Plan</h3>
                <div class="space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="allow_custom_slug" 
                               value="1"
                               {{ old('allow_custom_slug') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Permitir slug personalizado</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Plan activo</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_public" 
                               value="1"
                               {{ old('is_public', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Plan público</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_featured" 
                               value="1"
                               {{ old('is_featured') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Plan destacado</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="allow_custom_slug" 
                               value="1"
                               {{ old('allow_custom_slug') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">
                            Permitir slug personalizado
                            <span class="block text-xs text-black-300 mt-1">Los usuarios podrán elegir su propia URL (ej: linkiu.bio/mi-tienda)</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('superlinkiu.plans.index') }}" 
                   class="bg-accent-100 hover:bg-accent-200 text-black-400 px-6 py-2 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-6 py-2 rounded-lg transition-colors">
                    Crear Plan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 