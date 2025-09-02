@extends('shared::layouts.admin')

@section('title', 'Crear Nueva Tienda')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Crear Nueva Tienda</h1>
        <a href="{{ route('superlinkiu.stores.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver
        </a>
    </div>

    <form action="{{ route('superlinkiu.stores.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

        <!-- Campos ocultos para valores old() -->
        <input type="hidden" name="_old_plan_id" value="{{ old('plan_id', '') }}">
        <input type="hidden" name="_old_slug" value="{{ old('slug', '') }}">
        
        <!-- Card única con toda la información -->
        <div class="bg-accent-50 rounded-lg p-0 overflow-hidden">
            <div class="border-b border-accent-100 bg-accent-50 py-4 px-6">
                <h2 class="text-lg font-semibold text-black-400 mb-0">Información de la Tienda</h2>
            </div>
            
            <div class="p-6">
                <!-- Sección: Información del Propietario -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-user-outline class="w-5 h-5" />
                        Información del Propietario
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Nombre del Representante <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="owner_name"
                                value="{{ old('owner_name') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('owner_name') border-error-200 @enderror"
                                required>
                            @error('owner_name')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Correo del Administrador <span class="text-error-300">*</span>
                            </label>
                            <input type="email"
                                name="admin_email"
                                value="{{ old('admin_email') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('admin_email') border-error-200 @enderror"
                                required>
                            @error('admin_email')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Tipo de Documento <span class="text-error-300">*</span>
                            </label>
                            <select name="owner_document_type"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('owner_document_type') border-error-200 @enderror"
                                required>
                                <option value="">Seleccionar tipo</option>
                                <option value="cedula" {{ old('owner_document_type') == 'cedula' ? 'selected' : '' }}>Cédula</option>
                                <option value="nit" {{ old('owner_document_type') == 'nit' ? 'selected' : '' }}>NIT</option>
                                <option value="pasaporte" {{ old('owner_document_type') == 'pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                            </select>
                            @error('owner_document_type')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Número de Documento <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="owner_document_number"
                                value="{{ old('owner_document_number') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('owner_document_number') border-error-200 @enderror"
                                required>
                            @error('owner_document_number')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                País <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="owner_country"
                                value="{{ old('owner_country', 'Colombia') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('owner_country') border-error-200 @enderror"
                                required>
                            @error('owner_country')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Departamento <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="owner_department"
                                value="{{ old('owner_department') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('owner_department') border-error-200 @enderror"
                                placeholder="Antioquia"
                                required>
                            @error('owner_department')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Ciudad <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="owner_city"
                                value="{{ old('owner_city') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('owner_city') border-error-200 @enderror"
                                placeholder="Medellín"
                                required>
                            @error('owner_city')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Contraseña del Administrador <span class="text-error-300">*</span>
                            </label>
                            <input type="password"
                                name="admin_password"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('admin_password') border-error-200 @enderror"
                                required
                                minlength="8">
                            @error('admin_password')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-black-200 mt-1">Mínimo 8 caracteres</p>
                        </div>
                    </div>
                </div>

                <!-- Sección: Información de la Tienda -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-shop-outline class="w-5 h-5" />
                        Información de la Tienda
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Nombre de la Tienda <span class="text-error-300">*</span>
                            </label>
                            <input type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('name') border-error-200 @enderror"
                                placeholder="Mi Tienda Online"
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
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('plan_id') border-error-200 @enderror"
                                required>
                                <option value="">Seleccionar Plan</option>
                                @foreach($plans as $plan)
                                                                    <option value="{{ $plan->id }}" 
                                    data-plan-name="{{ $plan->name }}"
                                    data-allow-custom="{{ $plan->allow_custom_slug ? 'true' : 'false' }}"
                                    {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
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
                                URL de la Tienda (Slug) <span class="text-error-300">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-black-300">linkiu.bio/</span>
                                <input type="text"
                                    name="slug"
                                    id="slug-input"
                                    value="{{ old('slug') }}"
                                    class="flex-1 px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('slug') border-error-200 @enderror"
                                    placeholder="mi-tienda"
                                    required>
                            </div>
                            <p class="text-xs text-black-300 mt-1" id="slug-help-text">
                                La URL única de tu tienda. Solo letras, números y guiones.
                            </p>
                            @error('slug')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Email de Contacto de la Tienda
                            </label>
                            <input type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('email') border-error-200 @enderror"
                                placeholder="Opcional - Email público de la tienda">
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
                                value="{{ old('phone') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('phone') border-error-200 @enderror"
                                placeholder="+57 300 123 4567">
                            @error('phone')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Estado Inicial
                            </label>
                            <select name="status"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                                <option value="active">Activa</option>
                                <option value="inactive">Inactiva</option>
                            </select>
                        </div>

                        <!-- Período de facturación para planes de pago -->
                        <div class="billing-fields">
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Período de Facturación <span class="text-error-300">*</span>
                            </label>
                            <select name="billing_period"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('billing_period') border-error-200 @enderror">
                                <option value="">Seleccionar período</option>
                                <option value="monthly" {{ old('billing_period') == 'monthly' ? 'selected' : '' }}>Mensual</option>
                                <option value="quarterly" {{ old('billing_period') == 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                                <option value="biannual" {{ old('billing_period') == 'biannual' ? 'selected' : '' }}>Semestral</option>
                            </select>
                            @error('billing_period')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-black-300 mt-1">
                                Aplica solo para planes de pago (no Explorer).
                            </p>
                        </div>

                        <!-- Estado de pago inicial para planes de pago -->
                        <div class="billing-fields">
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Estado de Pago Inicial
                            </label>
                            <select name="initial_payment_status"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none">
                                <option value="pending" {{ old('initial_payment_status') == 'pending' ? 'selected' : '' }}>Pendiente de Pago</option>
                                <option value="paid" {{ old('initial_payment_status') == 'paid' ? 'selected' : '' }}>Pagado</option>
                            </select>
                            <p class="text-xs text-black-300 mt-1">
                                Selecciona si la primera factura generada estará marcada como pagada o pendiente. Aplica solo para planes de pago.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Descripción
                        </label>
                            <textarea
                                name="description"
                                rows="3"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('description') border-error-200 @enderror"
                                placeholder="Breve descripción de la tienda...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                        </div>
                    </div>
                </div>

                <!-- Sección: Información Fiscal y Ubicación de la Tienda -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-black-400 mb-4 flex items-center gap-2">
                        <x-solar-document-text-outline class="w-5 h-5" />
                        Información Fiscal y Ubicación de la Tienda
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Tipo de Documento de la Empresa
                            </label>
                            <select name="document_type"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('document_type') border-error-200 @enderror">
                                <option value="">Seleccionar tipo</option>
                                <option value="nit" {{ old('document_type') == 'nit' ? 'selected' : '' }}>NIT</option>
                                <option value="cedula" {{ old('document_type') == 'cedula' ? 'selected' : '' }}>Cédula</option>
                            </select>
                        @error('document_type')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Número de Documento de la Empresa
                            </label>
                            <input type="text"
                                name="document_number"
                                value="{{ old('document_number') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('document_number') border-error-200 @enderror"
                                placeholder="123456789-0">
                        @error('document_number')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                País de la Tienda
                            </label>
                            <input type="text"
                                name="country"
                                value="{{ old('country', 'Colombia') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('country') border-error-200 @enderror">
                        @error('country')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Departamento de la Tienda
                            </label>
                            <input type="text"
                                name="department"
                                value="{{ old('department') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('department') border-error-200 @enderror"
                                placeholder="Antioquia">
                        @error('department')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Ciudad de la Tienda
                            </label>
                            <input type="text"
                                name="city"
                                value="{{ old('city') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('city') border-error-200 @enderror"
                                placeholder="Medellín">
                        @error('city')
                                <p class="text-xs text-error-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Dirección de la Tienda
                            </label>
                            <input type="text"
                                name="address"
                                value="{{ old('address') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none @error('address') border-error-200 @enderror"
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
                                value="{{ old('meta_title') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                placeholder="Mi Tienda Online - Los mejores productos">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Meta Descripción
                            </label>
                        <textarea
                                name="meta_description"
                                rows="2"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                placeholder="Descripción para motores de búsqueda...">{{ old('meta_description') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-black-300 mb-2">
                                Meta Keywords
                    </label>
                            <input type="text"
                                name="meta_keywords"
                                value="{{ old('meta_keywords') }}"
                                class="w-full px-4 py-2 border border-accent-200 rounded-lg focus:border-primary-200 focus:ring-1 focus:ring-primary-200 focus:outline-none"
                                placeholder="tienda, online, productos, calidad">
                        </div>
                    </div>
                </div>
                </div>

            <!-- Footer con botones -->
            <div class="border-t border-accent-100 bg-accent-50 px-6 py-4">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('superlinkiu.stores.index') }}"
                        class="btn-outline-secondary px-6 py-2 rounded-lg">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                        <x-solar-diskette-outline class="w-5 h-5" />
                        Crear Tienda
                    </button>
                </div>
            </div>
        </div>
    </form>
    </div>


@endsection

@push('scripts')
{{-- 🚀 VALIDACIONES EN TIEMPO REAL --}}
<script src="{{ asset('js/stores/real-time-validation.js') }}"></script>
{{-- 🛡️ MANEJO DE ERRORES --}}
<script src="{{ asset('js/stores/error-handling.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const planSelect = document.querySelector('select[name="plan_id"]');
    const slugInput = document.getElementById('slug-input');
    const slugHelpText = document.getElementById('slug-help-text');
    const storeNameInput = document.querySelector('input[name="name"]');
    
    // Datos de planes (pasados desde el servidor)
    const plansData = @json($plans->mapWithKeys(function($plan) {
        return [$plan->id => [
            'allow_custom_slug' => $plan->allow_custom_slug,
            'name' => $plan->name
        ]];
    }));

    function generateRandomSlug() {
        const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        let result = 'tienda-';
        for (let i = 0; i < 8; i++) {
            result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return result;
    }

    function generateSlugFromName(name) {
        if (!name) return generateRandomSlug();
        
        return name
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Eliminar acentos
            .replace(/[^a-z0-9\s-]/g, '') // Eliminar caracteres especiales
            .trim()
            .replace(/\s+/g, '-') // Espacios a guiones
            .replace(/-+/g, '-') // Múltiples guiones a uno solo
            .replace(/^-+|-+$/g, '') // Eliminar guiones al inicio/final
            .slice(0, 50); // Limitar longitud
    }

    function sanitizeSlugInput(slug) {
        return slug
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Eliminar acentos
            .replace(/[^a-z0-9\s-]/g, '') // Eliminar caracteres especiales excepto espacios y guiones
            .replace(/\s+/g, '-') // Espacios a guiones
            .replace(/-+/g, '-') // Múltiples guiones a uno solo
            .replace(/^-+|-+$/g, ''); // Eliminar guiones al inicio/final
    }

    function updateSlugField() {
        const selectedPlanId = planSelect.value;
        
        if (!selectedPlanId || !plansData[selectedPlanId]) {
            return;
        }

        const planData = plansData[selectedPlanId];
        
        if (planData.allow_custom_slug) {
            // Plan permite slug personalizado
            slugInput.removeAttribute('readonly');
            slugInput.classList.remove('bg-accent-100', 'cursor-not-allowed');
            slugInput.classList.add('bg-accent-50');
            slugInput.placeholder = 'mi-tienda-personalizada';
            slugHelpText.innerHTML = '✅ Este plan permite URLs personalizadas. Elige la URL que prefieras.';
            slugHelpText.className = 'text-xs text-success-300 mt-1';
            
            // Si el slug actual parece ser automático, limpiarlo
            if (slugInput.value.startsWith('tienda-') && slugInput.value.length === 14) {
                slugInput.value = '';
            }
        } else {
            // Plan NO permite slug personalizado - generar aleatorio
            slugInput.setAttribute('readonly', 'readonly');
            slugInput.classList.add('bg-accent-100', 'cursor-not-allowed');
            slugInput.classList.remove('bg-accent-50');
            
            const randomSlug = generateRandomSlug();
            slugInput.value = randomSlug;
            slugInput.placeholder = randomSlug;
            
            slugHelpText.innerHTML = `⚠️ Plan "${planData.name}": URL asignada automáticamente. <strong>Actualiza a un plan superior</strong> para elegir tu propia URL.`;
            slugHelpText.className = 'text-xs text-warning-300 mt-1';
        }
    }

    // Auto-generar slug basado en nombre de tienda (solo si plan permite personalización)
    function updateSlugFromName() {
        const selectedPlanId = planSelect.value;
        
        if (!selectedPlanId || !plansData[selectedPlanId] || !plansData[selectedPlanId].allow_custom_slug) {
            return;
        }

        // Solo auto-generar si el campo está vacío o tiene el valor anterior autogenerado
        if (!slugInput.value || slugInput.value === slugInput.getAttribute('data-prev-generated')) {
            const newSlug = generateSlugFromName(storeNameInput.value);
            slugInput.value = newSlug;
            slugInput.setAttribute('data-prev-generated', newSlug);
        }
    }

    // Event listeners
    planSelect.addEventListener('change', updateSlugField);
    storeNameInput.addEventListener('input', updateSlugFromName);
    
    // Sanitizar slug en tiempo real cuando el usuario escriba (solo para planes personalizados)
    slugInput.addEventListener('input', function() {
        const selectedPlanId = planSelect.value;
        
        if (selectedPlanId && plansData[selectedPlanId] && plansData[selectedPlanId].allow_custom_slug) {
            const cursorPosition = this.selectionStart;
            const originalValue = this.value;
            const sanitizedValue = sanitizeSlugInput(originalValue);
            
            if (originalValue !== sanitizedValue) {
                this.value = sanitizedValue;
                // Mantener posición del cursor ajustada
                const newPosition = Math.min(cursorPosition, sanitizedValue.length);
                this.setSelectionRange(newPosition, newPosition);
                
                // Mostrar feedback visual
                this.classList.add('border-warning-200');
                setTimeout(() => {
                    this.classList.remove('border-warning-200');
                }, 500);
            }
        }
    });
    
    // Inicializar en carga de página
    updateSlugField();
});
</script>
@endpush 