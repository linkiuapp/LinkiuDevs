@extends('shared::layouts.admin')

@section('title', 'Editar Tienda')

@section('content')
<div class="container-fluid" x-data="editStore()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Editar Tienda</h1>
        <a href="{{ route('superlinkiu.stores.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver
        </a>
    </div>

    <form action="{{ route('superlinkiu.stores.update', $store) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Campos ocultos para JavaScript -->
        <input type="hidden" id="original_plan_id" value="{{ $store->plan_id }}">
        <input type="hidden" id="original_plan_slug" value="{{ strtolower($store->plan->slug ?? $store->plan->name) }}">
        <input type="hidden" id="original_slug" value="{{ $store->slug }}">
        
        <!-- Card única con toda la información -->
        <div class="bg-white-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-white-100 bg-white-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Información de la Tienda</h2>
            </div>
            
            <div class="p-6">
                <!-- Sección: Información Básica -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-info-circle-outline class="w-5 h-5" />
                        Información Básica
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Nombre de la Tienda <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                value="{{ old('name', $store->name) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('name') border-error-200 @enderror"
                                required>
                            @error('name')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Plan <span class="text-error-300">*</span>
                            </label>
                            <select name="plan_id"
                                x-model="selectedPlan"
                                @change="checkPlanChange"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('plan_id') border-error-200 @enderror"
                                required>
                                <option value="">Seleccionar Plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" 
                                        data-slug="{{ strtolower($plan->slug ?? $plan->name) }}"
                                        data-allow-custom="{{ $plan->allow_custom_slug ? 'true' : 'false' }}"
                                        {{ old('plan_id', $store->plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - {{ $plan->getPriceFormatted() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                URL de la Tienda (Slug)
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-black-300">linkiu.bio/</span>
                                <input type="text"
                                    name="slug"
                                    x-model="slug"
                                    :readonly="!canEditSlug"
                                    :class="{'bg-white-100 cursor-not-allowed': !canEditSlug, 'bg-white-50': canEditSlug}"
                                    class="flex-1 px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('slug') border-error-200 @enderror">
                            </div>
                            
                            <!-- Mensaje para planes que NO permiten personalización -->
                            <p class="text-xs text-warning-300 mt-1 flex items-center gap-1" x-show="!canEditSlug && !isUpgrading">
                                <x-solar-lock-outline class="w-3 h-3" />
                                Este plan no permite personalizar la URL. Actualiza a un plan superior para editarla.
                            </p>
                            
                            <!-- Mensaje para planes que SÍ permiten personalización -->
                            <p class="text-xs text-success-300 mt-1 flex items-center gap-1" x-show="canEditSlug && !isUpgrading">
                                <x-solar-check-circle-outline class="w-3 h-3" />
                                Puedes personalizar tu URL con este plan.
                            </p>
                            
                            <!-- Mensaje para upgrade -->
                            <p class="text-xs text-primary-300 mt-1 flex items-center gap-1" x-show="isUpgrading">
                                <x-solar-star-outline class="w-3 h-3" />
                                ¡Felicidades! Ahora puedes personalizar tu URL.
                            </p>
                            
                            @error('slug')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Email <span class="text-error-300">*</span>
                            </label>
                            <input type="email"
                                name="email"
                                value="{{ old('email', $store->email) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('email') border-error-200 @enderror"
                                required>
                            @error('email')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Teléfono
                            </label>
                            <input type="text"
                                name="phone"
                                value="{{ old('phone', $store->phone) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('phone') border-error-200 @enderror"
                                placeholder="+57 300 123 4567">
                            @error('phone')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Estado
                            </label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                                <option value="active" {{ old('status', $store->status) == 'active' ? 'selected' : '' }}>Activa</option>
                                <option value="inactive" {{ old('status', $store->status) == 'inactive' ? 'selected' : '' }}>Inactiva</option>
                                <option value="suspended" {{ old('status', $store->status) == 'suspended' ? 'selected' : '' }}>Suspendida</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Descripción
                            </label>
                            <textarea
                                name="description"
                                rows="3"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('description') border-error-200 @enderror"
                                placeholder="Breve descripción de la tienda...">{{ old('description', $store->description) }}</textarea>
                            @error('description')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Verificación
                            </label>
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                        name="verified"
                                        value="1"
                                        class="sr-only peer" 
                                        {{ old('verified', $store->verified) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-white-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white-50 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white-50 after:border-white-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-200"></div>
                                </label>
                                <span class="text-sm text-black-300">Tienda verificada</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Documento y Ubicación -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-document-text-outline class="w-5 h-5" />
                        Documento y Ubicación
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Tipo de Documento
                            </label>
                            <select name="document_type"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('document_type') border-error-200 @enderror">
                                <option value="">Seleccionar tipo</option>
                                <option value="nit" {{ old('document_type', $store->document_type) == 'nit' ? 'selected' : '' }}>NIT</option>
                                <option value="cedula" {{ old('document_type', $store->document_type) == 'cedula' ? 'selected' : '' }}>Cédula</option>
                            </select>
                            @error('document_type')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Número de Documento
                            </label>
                            <input type="text"
                                name="document_number"
                                value="{{ old('document_number', $store->document_number) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('document_number') border-error-200 @enderror"
                                placeholder="123456789-0">
                            @error('document_number')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                País
                            </label>
                            <input type="text"
                                name="country"
                                value="{{ old('country', $store->country) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('country') border-error-200 @enderror">
                            @error('country')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Departamento
                            </label>
                            <input type="text"
                                name="department"
                                value="{{ old('department', $store->department) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('department') border-error-200 @enderror"
                                placeholder="Antioquia">
                            @error('department')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Ciudad
                            </label>
                            <input type="text"
                                name="city"
                                value="{{ old('city', $store->city) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('city') border-error-200 @enderror"
                                placeholder="Medellín">
                            @error('city')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Dirección
                            </label>
                            <input type="text"
                                name="address"
                                value="{{ old('address', $store->address) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('address') border-error-200 @enderror"
                                placeholder="Calle 123 #45-67">
                            @error('address')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección: SEO -->
                <div>
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-global-outline class="w-5 h-5" />
                        SEO y Metadatos
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Meta Título
                            </label>
                            <input type="text"
                                name="meta_title"
                                value="{{ old('meta_title', $store->meta_title) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                placeholder="Mi Tienda Online - Los mejores productos">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Meta Descripción
                            </label>
                            <textarea
                                name="meta_description"
                                rows="2"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                placeholder="Descripción para motores de búsqueda...">{{ old('meta_description', $store->meta_description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Meta Keywords
                            </label>
                            <input type="text"
                                name="meta_keywords"
                                value="{{ old('meta_keywords', $store->meta_keywords) }}"
                                class="w-full px-4 py-2 border border-white-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                placeholder="tienda, online, productos, calidad">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con botones -->
            <div class="border-t border-white-100 bg-white-50 px-6 py-4">
                <div class="flex justify-between">
                    <a href="{{ route('superlinkiu.stores.show', $store) }}"
                        class="btn-outline-primary px-4 py-2 rounded-lg flex items-center gap-2">
                        <x-solar-eye-outline class="w-5 h-5" />
                        Ver Detalles
                    </a>
                    <div class="flex gap-3">
                        <a href="{{ route('superlinkiu.stores.index') }}"
                            class="btn-outline-secondary px-6 py-2 rounded-lg">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                            <x-solar-diskette-outline class="w-5 h-5" />
                            Actualizar Tienda
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection 