@extends('shared::layouts.admin')

@section('title', 'Crear Nueva Tienda - Wizard')

@section('content')
<div class="container-fluid" x-data="storeWizard()">
    
    {{-- Debug: Mostrar errores de validación si existen --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong>Errores de validación:</strong>
            <ul class="mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- Debug: Mostrar datos de sesión --}}
    @if (session()->has('_old_input'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <strong>Datos enviados anteriormente:</strong>
            <pre>{{ json_encode(session('_old_input'), JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-lg font-bold text-black-400">Crear Nueva Tienda</h1>
        <a href="{{ route('superlinkiu.stores.index') }}" class="btn-outline-secondary px-4 py-2 rounded-lg flex items-center gap-2">
            <x-solar-arrow-left-outline class="w-5 h-5" />
            Volver
        </a>
    </div>

    <!-- Progress Bar -->
    <div class="bg-accent rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <template x-for="(step, index) in steps" :key="step.id">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium"
                             :class="index < currentStep ? 'bg-green-500 text-accent' : 
                                     index === currentStep ? 'bg-blue-500 text-accent' : 
                                     'bg-gray-200 text-gray-600'">
                            <span x-text="index + 1"></span>
                        </div>
                        <div class="ml-2 text-sm font-medium" 
                             :class="index <= currentStep ? 'text-gray-900' : 'text-gray-500'"
                             x-text="step.title"></div>
                        <div x-show="index < steps.length - 1" class="ml-4 w-8 h-0.5 bg-gray-200"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Wizard Form -->
    <form id="wizardForm" method="POST" action="{{ route('superlinkiu.stores.store') }}" class="bg-accent rounded-lg shadow-sm" @submit="handleFormSubmit">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        
        <!-- Hidden fields for form data -->
        <input type="hidden" name="plan_id" x-model="formData.plan_id">
        <input type="hidden" name="billing_period" x-model="formData.billing_period">
        <input type="hidden" name="name" x-model="formData.name">
        <input type="hidden" name="slug" x-model="formData.slug">
        <input type="hidden" name="email" x-model="formData.email">
        <input type="hidden" name="phone" x-model="formData.phone">
        <input type="hidden" name="description" x-model="formData.description">
        <input type="hidden" name="owner_name" x-model="formData.owner_name">
        <input type="hidden" name="admin_email" x-model="formData.admin_email">
        <input type="hidden" name="owner_document_type" x-model="formData.owner_document_type">
        <input type="hidden" name="owner_document_number" x-model="formData.owner_document_number">
        <input type="hidden" name="owner_country" x-model="formData.owner_country">
        <input type="hidden" name="owner_department" x-model="formData.owner_department">
        <input type="hidden" name="owner_city" x-model="formData.owner_city">
        <input type="hidden" name="admin_password" x-model="formData.admin_password">
        <input type="hidden" name="document_type" x-model="formData.fiscal_document_type">
        <input type="hidden" name="document_number" x-model="formData.fiscal_document_number">
        <input type="hidden" name="address" x-model="formData.fiscal_address">
        <input type="hidden" name="tax_regime" x-model="formData.tax_regime">
        <input type="hidden" name="status" value="active">
        <input type="hidden" name="initial_payment_status" value="pending">
        <input type="hidden" name="verified" value="0">

        <!-- Step 1: Plan Selection -->
        <div x-show="currentStep === 0" class="p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Seleccionar Plan y Período</h2>
                <p class="text-gray-600">Elige el plan que mejor se adapte a las necesidades de la tienda</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                @foreach($plans as $plan)
                <div class="border rounded-lg p-6 cursor-pointer transition-all hover:shadow-md"
                     :class="formData.plan_id == '{{ $plan->id }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                     @click="formData.plan_id = '{{ $plan->id }}'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>
                        <input type="radio" name="plan_id" value="{{ $plan->id }}" x-model="formData.plan_id" class="text-blue-600">
                    </div>
                    <div class="text-2xl font-bold text-blue-600 mb-2">{{ $plan->getPriceFormatted() }}</div>
                    <p class="text-gray-600 text-sm mb-4">{{ $plan->description ?? 'Plan ' . $plan->name }}</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>✓ Tienda personalizada</li>
                        <li>✓ Soporte técnico</li>
                        <li>✓ SSL incluido</li>
                        @if($plan->name !== 'Básico')
                        <li>✓ Funciones avanzadas</li>
                        @endif
                    </ul>
                </div>
                @endforeach
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Período de Facturación</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                         :class="formData.billing_period === 'monthly' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                         @click="formData.billing_period = 'monthly'">
                        <input type="radio" name="billing_period" value="monthly" x-model="formData.billing_period" class="mb-2">
                        <div class="font-semibold">Mensual</div>
                        <div class="text-sm text-gray-600">Facturación cada mes</div>
                    </div>
                    <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                         :class="formData.billing_period === 'quarterly' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                         @click="formData.billing_period = 'quarterly'">
                        <input type="radio" name="billing_period" value="quarterly" x-model="formData.billing_period" class="mb-2">
                        <div class="font-semibold">Trimestral</div>
                        <div class="text-sm text-gray-600">Facturación cada 3 meses</div>
                        <div class="text-xs text-green-600 font-medium">5% descuento</div>
                    </div>
                    <div class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md"
                         :class="formData.billing_period === 'yearly' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                         @click="formData.billing_period = 'yearly'">
                        <input type="radio" name="billing_period" value="yearly" x-model="formData.billing_period" class="mb-2">
                        <div class="font-semibold">Anual</div>
                        <div class="text-sm text-gray-600">Facturación cada año</div>
                        <div class="text-xs text-green-600 font-medium">15% descuento</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Store Configuration -->
        <div x-show="currentStep === 1" class="p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Configuración de la Tienda</h2>
                <p class="text-gray-600">Nombre, URL y configuración básica de la tienda</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la Tienda <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="formData.name"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        URL de la Tienda (Slug) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 mr-2">linkiu.bio/</span>
                        <input type="text" name="slug" x-model="formData.slug"
                               :readonly="!canEditSlug"
                               @input="userHasEditedSlug = true"
                               :class="canEditSlug ? 
                                   'flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500' : 
                                   'flex-1 px-4 py-2 border border-gray-200 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed'"
                               required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" x-show="canEditSlug">Solo letras minúsculas, números y guiones</p>
                    <p class="text-xs text-orange-600 mt-1" x-show="!canEditSlug">URL generada automáticamente según tu plan</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email de Contacto
                    </label>
                    <input type="email" name="email" x-model="formData.email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono
                    </label>
                    <input type="text" name="phone" x-model="formData.phone"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea name="description" x-model="formData.description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Step 3: Owner Information -->
        <div x-show="currentStep === 2" class="p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Información del Propietario</h2>
                <p class="text-gray-600">Datos del administrador que gestionará la tienda</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Representante <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="owner_name" x-model="formData.owner_name" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Correo del Administrador <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="admin_email" x-model="formData.admin_email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Documento <span class="text-red-500">*</span>
                    </label>
                    <select name="owner_document_type" x-model="formData.owner_document_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="cedula">Cédula</option>
                        <option value="nit">NIT</option>
                        <option value="pasaporte">Pasaporte</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Número de Documento <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="owner_document_number" x-model="formData.owner_document_number"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        País <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="owner_country" x-model="formData.owner_country" value="Colombia"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Departamento <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="owner_department" x-model="formData.owner_department"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ciudad <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="owner_city" x-model="formData.owner_city"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña del Administrador <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="admin_password" x-model="formData.admin_password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required minlength="8">
                    <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                </div>
            </div>
        </div>

        <!-- Step 4: Fiscal Information (Optional) -->
        <div x-show="currentStep === 3" class="p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Información Fiscal</h2>
                <p class="text-gray-600">Datos fiscales y de ubicación (opcional)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Documento Fiscal
                    </label>
                    <select name="fiscal_document_type" x-model="formData.fiscal_document_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar tipo</option>
                        <option value="nit">NIT</option>
                        <option value="rut">RUT</option>
                        <option value="cedula">Cédula</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Número de Documento Fiscal
                    </label>
                    <input type="text" name="fiscal_document_number" x-model="formData.fiscal_document_number"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección Fiscal
                    </label>
                    <textarea name="fiscal_address" x-model="formData.fiscal_address" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Régimen Tributario
                    </label>
                    <select name="tax_regime" x-model="formData.tax_regime"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar régimen</option>
                        <option value="simplificado">Régimen Simplificado</option>
                        <option value="comun">Régimen Común</option>
                        <option value="gran_contribuyente">Gran Contribuyente</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center p-6 border-t border-gray-200">
            <button type="button" @click="previousStep" x-show="currentStep > 0"
                    class="btn-outline-secondary px-6 py-2 rounded-lg flex items-center gap-2">
                <x-solar-arrow-left-outline class="w-4 h-4" />
                Anterior
            </button>
            
            <div class="flex gap-3">
                <button type="button" @click="nextStep" x-show="currentStep < steps.length - 1"
                        class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                    Siguiente
                    <x-solar-arrow-right-outline class="w-4 h-4" />
                </button>
                
                <button type="submit" x-show="currentStep === steps.length - 1"
                        @click="updateHiddenFields()"
                        class="btn-primary px-6 py-2 rounded-lg flex items-center gap-2">
                    <x-solar-diskette-outline class="w-4 h-4" />
                    Crear Tienda
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function storeWizard() {
    return {
        currentStep: 0,
        steps: [
            { id: 'plan-selection', title: 'Plan y Período' },
            { id: 'store-config', title: 'Configuración de Tienda' },
            { id: 'owner-info', title: 'Información del Propietario' },
            { id: 'fiscal-info', title: 'Información Fiscal' }
        ],
        plans: @json($plans),
        formData: {
            // Plan data
            plan_id: '',
            billing_period: 'monthly',
            
            // Owner data
            owner_name: '',
            admin_email: '',
            owner_document_type: '',
            owner_document_number: '',
            owner_country: 'Colombia',
            owner_department: '',
            owner_city: '',
            admin_password: '',
            
            // Store data
            name: '',
            slug: '',
            email: '',
            phone: '',
            description: '',
            
            // Fiscal data
            fiscal_document_type: '',
            fiscal_document_number: '',
            fiscal_address: '',
            tax_regime: ''
        },
        
        // Control variables
        userHasEditedSlug: false,
        
        get selectedPlan() {
            return this.plans.find(plan => plan.id == this.formData.plan_id);
        },
        
        get canEditSlug() {
            return this.selectedPlan ? this.selectedPlan.allow_custom_slug : false;
        },
        
        nextStep() {
            if (this.validateCurrentStep()) {
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                }
            }
        },
        
        previousStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        },
        
        generateSlugFromName() {
            console.log('🔗 WIZARD: generateSlugFromName llamado', {
                name: this.formData.name,
                canEditSlug: this.canEditSlug,
                currentSlug: this.formData.slug
            });
            
            if (this.formData.name) {
                if (!this.canEditSlug) {
                    // Generar slug automático si el plan no permite edición
                    this.formData.slug = this.createSlugFromText(this.formData.name);
                    console.log('🔗 WIZARD: Slug automático generado:', this.formData.slug);
                } else {
                    // Si permite edición, solo generar si no hay slug o si el usuario no ha editado manualmente
                    if (!this.formData.slug || !this.userHasEditedSlug) {
                        this.formData.slug = this.createSlugFromText(this.formData.name);
                        console.log('🔗 WIZARD: Slug sugerido generado:', this.formData.slug);
                    }
                }
            }
        },
        
        createSlugFromText(text) {
            return text
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '') // Remover acentos
                .replace(/[^a-z0-9\s-]/g, '') // Solo letras, números, espacios y guiones
                .trim()
                .replace(/\s+/g, '-') // Reemplazar espacios con guiones
                .replace(/-+/g, '-') // Remover guiones múltiples
                .substring(0, 50); // Limitar longitud
        },
        
        generateRandomSlug() {
            const randomString = Math.random().toString(36).substring(2, 8);
            return 'tienda-' + randomString;
        },
        
        // Inicialización del wizard
        init() {
            console.log('🚀 WIZARD: Inicializando wizard');
            
            // Watch para cambios en el plan
            this.$watch('formData.plan_id', (newPlanId, oldPlanId) => {
                console.log('📋 WIZARD: Plan cambiado de', oldPlanId, 'a', newPlanId);
                
                // Resetear la bandera de edición manual cuando cambia el plan
                this.userHasEditedSlug = false;
                
                if (newPlanId) {
                    const selectedPlan = this.plans.find(plan => plan.id == newPlanId);
                    console.log('📋 WIZARD: Plan seleccionado:', selectedPlan);
                    
                    if (!selectedPlan?.allow_custom_slug) {
                        // Si el plan no permite slug personalizado, generar uno automático
                        if (this.formData.name) {
                            this.formData.slug = this.createSlugFromText(this.formData.name);
                        } else {
                            this.formData.slug = this.generateRandomSlug();
                        }
                        console.log('🔗 WIZARD: Slug generado automáticamente:', this.formData.slug);
                    } else if (this.formData.name) {
                        // Si permite personalización y hay nombre, generar sugerencia
                        this.formData.slug = this.createSlugFromText(this.formData.name);
                        console.log('🔗 WIZARD: Slug sugerido:', this.formData.slug);
                    }
                }
            });
            
            // Watch para cambios en el nombre de la tienda
            this.$watch('formData.name', (newName, oldName) => {
                console.log('👀 WIZARD: Nombre cambió de', oldName, 'a', newName);
                if (newName && newName !== oldName) {
                    // Usar setTimeout para evitar conflictos con el modelo
                    setTimeout(() => {
                        this.generateSlugFromName();
                    }, 10);
                }
            });
            
            // Verificar si hay errores de validación del servidor
            this.checkServerErrors();
        },
        
        checkServerErrors() {
            // Verificar si hay errores de validación del servidor
            const errorContainer = document.querySelector('.bg-red-100');
            if (errorContainer) {
                console.warn('⚠️ WIZARD: Errores de validación del servidor detectados');
            }
        },
        
        validateCurrentStep() {
            try {
                // Basic validation for required fields
                switch (this.currentStep) {
                    case 0: // Plan selection
                        if (!this.formData.plan_id || !this.formData.billing_period) {
                            this.showError('Por favor selecciona un plan y período de facturación');
                            return false;
                        }
                        break;
                    case 1: // Store config
                        if (!this.formData.name || !this.formData.slug) {
                            this.showError('Por favor completa el nombre y URL de la tienda');
                            return false;
                        }
                        // Validar formato del slug
                        const slugPattern = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
                        if (!slugPattern.test(this.formData.slug)) {
                            this.showError('La URL debe contener solo letras minúsculas, números y guiones');
                            return false;
                        }
                        break;
                    case 2: // Owner info
                        const requiredOwnerFields = [
                            { field: 'owner_name', label: 'Nombre del representante' },
                            { field: 'admin_email', label: 'Correo del administrador' },
                            { field: 'owner_document_type', label: 'Tipo de documento' },
                            { field: 'owner_document_number', label: 'Número de documento' },
                            { field: 'owner_country', label: 'País' },
                            { field: 'owner_department', label: 'Departamento' },
                            { field: 'owner_city', label: 'Ciudad' },
                            { field: 'admin_password', label: 'Contraseña del administrador' }
                        ];
                        
                        for (let fieldInfo of requiredOwnerFields) {
                            if (!this.formData[fieldInfo.field]) {
                                this.showError(`Por favor completa el campo: ${fieldInfo.label}`);
                                return false;
                            }
                        }
                        
                        // Validar email
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailPattern.test(this.formData.admin_email)) {
                            this.showError('Por favor ingresa un email válido');
                            return false;
                        }
                        
                        // Validar contraseña
                        if (this.formData.admin_password.length < 8) {
                            this.showError('La contraseña debe tener al menos 8 caracteres');
                            return false;
                        }
                        break;
                }
                return true;
            } catch (error) {
                console.error('Error en validación:', error);
                this.showError('Error en la validación del formulario');
                return false;
            }
        },
        
        showError(message) {
            // Crear un toast de error más elegante
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-500 text-accent px-6 py-3 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // Remover después de 5 segundos
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        },
        
        handleFormSubmit(event) {
            console.log('🚀 WIZARD: Manejando envío del formulario');
            
            // Solo validar si estamos en el último paso
            if (this.currentStep === this.steps.length - 1) {
                if (!this.validateCurrentStep()) {
                    event.preventDefault();
                    console.error('❌ WIZARD: Validación falló, cancelando envío');
                    return false;
                }
                
                // Actualizar campos hidden antes del envío
                this.updateHiddenFields();
                console.log('✅ WIZARD: Formulario validado, enviando...');
            } else {
                // Si no estamos en el último paso, cancelar el envío
                event.preventDefault();
                this.nextStep();
                return false;
            }
        },
        
        submitForm() {
            console.log('🚀 WIZARD: Iniciando envío del formulario');
            console.log('📊 WIZARD: Datos actuales:', this.formData);
            
            try {
                if (this.validateCurrentStep()) {
                    console.log('✅ WIZARD: Validación exitosa, enviando formulario');
                    
                    // Actualizar todos los campos hidden antes de enviar
                    this.updateHiddenFields();
                    
                    // Verificar que el formulario existe
                    const form = document.getElementById('wizardForm');
                    if (!form) {
                        console.error('❌ WIZARD: Formulario no encontrado');
                        this.showError('Error: Formulario no encontrado');
                        return;
                    }
                    
                    // Log de los datos finales que se enviarán
                    const formData = new FormData(form);
                    console.log('📤 WIZARD: Datos del formulario a enviar:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`  ${key}: ${value}`);
                    }
                    
                    // Verificar que los campos requeridos estén presentes
                    const requiredFields = ['name', 'slug', 'plan_id', 'owner_name', 'admin_email'];
                    for (let field of requiredFields) {
                        if (!formData.get(field)) {
                            console.error(`❌ WIZARD: Campo requerido faltante: ${field}`);
                            this.showError(`Campo requerido faltante: ${field}`);
                            return;
                        }
                    }
                    
                    // Usar el formulario HTML nativo
                    form.submit();
                } else {
                    console.error('❌ WIZARD: Validación falló');
                }
            } catch (error) {
                console.error('❌ WIZARD: Error crítico en submitForm:', error);
                this.showError('Error crítico al enviar el formulario: ' + error.message);
            }
        },
        
        updateHiddenFields() {
            try {
                console.log('🔄 WIZARD: Actualizando campos hidden');
                const form = document.getElementById('wizardForm');
                
                if (!form) {
                    console.error('❌ WIZARD: Formulario no encontrado en updateHiddenFields');
                    return;
                }
                
                // Función helper para actualizar campo de forma segura
                const updateField = (name, value) => {
                    const field = form.querySelector(`input[name="${name}"]`);
                    if (field) {
                        field.value = value || '';
                        console.log(`  ✓ ${name}: ${field.value}`);
                    } else {
                        console.warn(`  ⚠️ Campo no encontrado: ${name}`);
                    }
                };
                
                // Actualizar campos de plan
                updateField('plan_id', this.formData.plan_id);
                updateField('billing_period', this.formData.billing_period);
                
                // Actualizar campos de tienda
                updateField('name', this.formData.name);
                updateField('slug', this.formData.slug);
                updateField('email', this.formData.email);
                updateField('phone', this.formData.phone);
                updateField('description', this.formData.description);
                
                // Actualizar campos del propietario
                updateField('owner_name', this.formData.owner_name);
                updateField('admin_email', this.formData.admin_email);
                updateField('owner_document_type', this.formData.owner_document_type);
                updateField('owner_document_number', this.formData.owner_document_number);
                updateField('owner_country', this.formData.owner_country);
                updateField('owner_department', this.formData.owner_department);
                updateField('owner_city', this.formData.owner_city);
                updateField('admin_password', this.formData.admin_password);
                
                // Actualizar campos fiscales
                updateField('document_type', this.formData.fiscal_document_type);
                updateField('document_number', this.formData.fiscal_document_number);
                updateField('address', this.formData.fiscal_address);
                updateField('tax_regime', this.formData.tax_regime);
                
                console.log('✅ WIZARD: Campos hidden actualizados correctamente');
            } catch (error) {
                console.error('❌ WIZARD: Error actualizando campos hidden:', error);
            }
        }
    }
}
</script>
@endpush
@endsection