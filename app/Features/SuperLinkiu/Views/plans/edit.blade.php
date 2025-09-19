@extends('shared::layouts.admin')

@section('title', 'Editar Plan - ' . $plan->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl text-black-500 mb-0">Editar Plan: {{ $plan->name }}</h1>
            <p class="text-black-300 mt-1">Modifica la configuración del plan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superlinkiu.plans.show', $plan) }}" class="bg-accent-100 hover:bg-accent-200 text-black-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <x-solar-arrow-left-outline class="w-5 h-5" />
                Ver Plan
            </a>
            <a href="{{ route('superlinkiu.plans.index') }}" class="bg-accent-100 hover:bg-accent-200 text-black-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                <x-solar-list-outline class="w-5 h-5" />
                Lista de Planes
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
        <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
            <h2 class="text-3xl text-black-500 mb-0">Información del Plan</h2>
        </div>
        
        <form action="{{ route('superlinkiu.plans.update', $plan) }}" method="POST" class="p-6" x-data="editPlan">
            @csrf
            @method('PUT')
            
            <!-- Información Básica -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-black-400 mb-2">
                        Nombre del Plan <span class="text-error-300">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name', $plan->name) }}"
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
                           value="{{ old('price', $plan->price) }}"
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
                          placeholder="Describe las características principales del plan">{{ old('description', $plan->description) }}</textarea>
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
                               value="{{ old('prices.monthly', $plan->prices['monthly'] ?? '') }}"
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
                               value="{{ old('prices.quarterly', $plan->prices['quarterly'] ?? '') }}"
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
                               value="{{ old('prices.semester', $plan->prices['semester'] ?? '') }}"
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
                <!-- 📦 PRODUCTOS -->
                <div class="mb-6">
                    <h4 class="text-base font-medium text-black-400 mb-3 flex items-center gap-2">
                        <span>📦</span> Productos y Catálogo
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Productos totales <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_products" 
                                   value="{{ old('max_products', $plan->max_products) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_products') border-error-200 @enderror">
                            @error('max_products')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Categorías <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_categories" 
                                   value="{{ old('max_categories', $plan->max_categories) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_categories') border-error-200 @enderror">
                            @error('max_categories')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Variables de producto <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_variables" 
                                   value="{{ old('max_variables', $plan->max_variables ?? 10) }}"
                                   min="0"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_variables') border-error-200 @enderror">
                            @error('max_variables')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Imágenes por producto <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_product_images" 
                                   value="{{ old('max_product_images', $plan->max_product_images ?? 3) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_product_images') border-error-200 @enderror">
                            @error('max_product_images')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 🎨 DISEÑO Y MARKETING -->
                <div class="mb-6">
                    <h4 class="text-base font-medium text-black-400 mb-3 flex items-center gap-2">
                        <span>🎨</span> Diseño y Marketing
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Sliders homepage <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_sliders" 
                                   value="{{ old('max_sliders', $plan->max_sliders ?? 1) }}"
                                   min="0"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_sliders') border-error-200 @enderror">
                            @error('max_sliders')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Cupones activos simultáneamente <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_active_coupons" 
                                   value="{{ old('max_active_coupons', $plan->max_active_coupons) }}"
                                   min="0"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_active_coupons') border-error-200 @enderror">
                            @error('max_active_coupons')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 🚚 ENVÍOS Y LOGÍSTICA -->
                <div class="mb-6">
                    <h4 class="text-base font-medium text-black-400 mb-3 flex items-center gap-2">
                        <span>🚚</span> Envíos y Logística
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Sedes físicas <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_locations" 
                                   value="{{ old('max_locations', $plan->max_locations ?? 1) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_locations') border-error-200 @enderror">
                            @error('max_locations')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Zonas de envío nacional <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_delivery_zones" 
                                   value="{{ old('max_delivery_zones', $plan->max_delivery_zones) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_delivery_zones') border-error-200 @enderror">
                            @error('max_delivery_zones')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 💰 PAGOS -->
                <div class="mb-6">
                    <h4 class="text-base font-medium text-black-400 mb-3 flex items-center gap-2">
                        <span>💰</span> Pagos
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Métodos de pago activos <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_payment_methods" 
                                   value="{{ old('max_payment_methods', $plan->max_payment_methods ?? 2) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_payment_methods') border-error-200 @enderror">
                            @error('max_payment_methods')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Cuentas bancarias <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_bank_accounts" 
                                   value="{{ old('max_bank_accounts', $plan->max_bank_accounts ?? 2) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_bank_accounts') border-error-200 @enderror">
                            @error('max_bank_accounts')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 📊 VENTAS Y PEDIDOS -->
                <div class="mb-6">
                    <h4 class="text-base font-medium text-black-400 mb-3 flex items-center gap-2">
                        <span>📊</span> Ventas y Pedidos
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Meses de historial de pedidos <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="order_history_months" 
                                   value="{{ old('order_history_months', $plan->order_history_months ?? 6) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('order_history_months') border-error-200 @enderror">
                            @error('order_history_months')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 👥 ADMINISTRACIÓN -->
                <div class="mb-6">
                    <h4 class="text-base font-medium text-black-400 mb-3 flex items-center gap-2">
                        <span>👥</span> Administración
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Usuarios administradores <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_admins" 
                                   value="{{ old('max_admins', $plan->max_admins) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_admins') border-error-200 @enderror">
                            @error('max_admins')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Tickets de soporte mensuales <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="max_tickets_per_month" 
                                   value="{{ old('max_tickets_per_month', $plan->max_tickets_per_month ?? 2) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('max_tickets_per_month') border-error-200 @enderror">
                            @error('max_tickets_per_month')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 📈 ANALÍTICAS -->
                <div class="mb-6">
                    <h4 class="text-base font-medium text-black-400 mb-3 flex items-center gap-2">
                        <span>📈</span> Analíticas
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-400 mb-2">
                                Días de retención de analytics <span class="text-error-300">*</span>
                            </label>
                            <input type="number" 
                                   name="analytics_retention_days" 
                                   value="{{ old('analytics_retention_days', $plan->analytics_retention_days ?? 30) }}"
                                   min="7"
                                   class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent @error('analytics_retention_days') border-error-200 @enderror">
                            @error('analytics_retention_days')
                                <p class="mt-1 text-sm text-error-300">{{ $message }}</p>
                            @enderror
                        </div>
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
                            <option value="basic" {{ old('support_level', $plan->support_level) == 'basic' ? 'selected' : '' }}>Básico</option>
                            <option value="priority" {{ old('support_level', $plan->support_level) == 'priority' ? 'selected' : '' }}>Prioritario</option>
                            <option value="premium" {{ old('support_level', $plan->support_level) == 'premium' ? 'selected' : '' }}>Premium</option>
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
                               value="{{ old('support_response_time', $plan->support_response_time) }}"
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
                @php
                    $existingFeatures = $plan->features_list;
                    if (is_string($existingFeatures)) {
                        $existingFeatures = json_decode($existingFeatures, true) ?: [];
                    }
                    $existingFeatures = is_array($existingFeatures) ? $existingFeatures : [];
                    $features = old('features_list', $existingFeatures);
                @endphp
                <div x-data="{ features: @json($features) }">
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
                            <option value="COP" {{ old('currency', $plan->currency) == 'COP' ? 'selected' : '' }}>COP - Peso Colombiano</option>
                            <option value="USD" {{ old('currency', $plan->currency) == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
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
                               value="{{ old('duration_in_days', $plan->duration_in_days) }}"
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
                               value="{{ old('trial_days', $plan->trial_days) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-accent-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black-400 mb-2">
                            Orden de Visualización
                        </label>
                        <input type="number" 
                               name="sort_order" 
                               value="{{ old('sort_order', $plan->sort_order) }}"
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
                               {{ old('allow_custom_slug', $plan->allow_custom_slug) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Permitir slug personalizado</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Plan activo</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_public" 
                               value="1"
                               {{ old('is_public', $plan->is_public) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Plan público</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_featured" 
                               value="1"
                               {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-300 border-accent-200 rounded focus:ring-primary-200">
                        <span class="ml-2 text-sm text-black-400">Plan destacado</span>
                    </label>
                </div>
            </div>

            <!-- Información de Versión -->
            @if($plan->hasActiveStores())
            <div class="mb-8 p-4 bg-warning-50 border border-warning-200 rounded-lg">
                <div class="flex items-start">
                    <x-solar-info-circle-outline class="w-5 h-5 text-warning-300 mr-3 flex-shrink-0 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-warning-300 mb-1">Advertencia</h4>
                        <p class="text-sm text-black-400">
                            Este plan tiene tiendas activas. Los cambios en precios y límites principales incrementarán automáticamente la versión del plan.
                            Los cambios solo afectarán a nuevas suscripciones.
                        </p>
                        <p class="text-sm text-black-300 mt-2">
                            Versión actual: <span class="font-medium">{{ $plan->version }}</span>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Botones -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('superlinkiu.plans.show', $plan) }}" 
                   class="bg-accent-100 hover:bg-accent-200 text-black-400 px-6 py-2 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-primary-200 hover:bg-primary-300 text-accent-50 px-6 py-2 rounded-lg transition-colors">
                    Actualizar Plan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 