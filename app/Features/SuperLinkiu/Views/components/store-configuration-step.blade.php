{{--
    Store Configuration Step Component
    
    Enhanced step for store configuration with slug auto-generation,
    availability checking, plan-based feature toggles, and contact information
    
    Props:
    - formData: Current form data
    - template: Selected template configuration
    - plan: Selected plan configuration
    - errors: Validation errors array
    - class: Additional CSS classes
    
    Requirements: 1.4, 1.5, 2.2, 2.3
--}}

@props([
    'formData' => [],
    'template' => null,
    'plan' => null,
    'errors' => [],
    'class' => ''
])

<div 
    x-data="storeConfigurationStep({
        formData: {{ json_encode($formData) }},
        template: {{ json_encode($template) }},
        plan: {{ json_encode($plan) }},
        errors: {{ json_encode($errors) }}
    })"
    class="store-configuration-step {{ $class }}"
    x-cloak
>
    {{-- Step Header --}}
    <div class="step-header mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Configuración de la Tienda</h2>
        <p class="text-gray-600">Define los datos básicos y configuración de tu tienda</p>
    </div>

    {{-- Form Grid --}}
    <div class="form-grid">
        {{-- Store Name --}}
        <div class="form-group col-span-2">
            <label for="name" class="form-label required">
                <span x-text="getStoreNameLabel()"></span>
            </label>
            <input 
                type="text" 
                id="name"
                name="name"
                x-model="formData.name"
                @input="onStoreNameChange($event.target.value)"
                @blur="validateField('name', $event.target.value)"
                class="form-input"
                :class="{ 'error': hasFieldError('name') }"
                :placeholder="getStoreNamePlaceholder()"
                required
            >
            <div x-show="hasFieldError('name')" class="field-error">
                <span x-text="getFieldError('name')"></span>
            </div>
            <div x-show="getFieldHint('name')" class="field-hint">
                <span x-text="getFieldHint('name')"></span>
            </div>
        </div>

        {{-- Store Slug/URL --}}
        <div class="form-group col-span-2">
            <label for="slug" class="form-label required">URL de la Tienda</label>
            <div class="slug-input-wrapper">
                <div class="slug-prefix">
                    <span class="text-gray-500">{{ url('/') }}/</span>
                </div>
                <div class="slug-input-container">
                    <input 
                        type="text" 
                        id="slug"
                        name="slug"
                        x-model="formData.slug"
                        @input="onSlugChange($event.target.value)"
                        @blur="validateField('slug', $event.target.value)"
                        class="form-input slug-input"
                        :class="{ 
                            'error': hasFieldError('slug'),
                            'validating': isValidating('slug'),
                            'valid': isFieldValid('slug'),
                            'readonly': !canEditSlug()
                        }"
                        :placeholder="getSlugPlaceholder()"
                        :readonly="!canEditSlug()"
                        required
                    >
                    <div class="slug-actions">
                        <button 
                            type="button"
                            x-show="canEditSlug() && formData.name"
                            @click="generateSlugFromName()"
                            class="slug-generate-btn"
                            title="Generar desde el nombre"
                        >
                            <x-solar-refresh-outline class="w-4 h-4" />
                        </button>
                        <div class="validation-indicator">
                            <div x-show="isValidating('slug')" class="spinner"></div>
                            <div x-show="isFieldValid('slug')" class="success-icon">
                                <x-solar-check-circle-outline class="w-5 h-5 text-green-600" />
                            </div>
                            <div x-show="hasFieldError('slug')" class="error-icon">
                                <x-solar-close-circle-outline class="w-5 h-5 text-red-600" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Slug Status Messages --}}
            <div x-show="hasFieldError('slug')" class="field-error">
                <span x-text="getFieldError('slug')"></span>
            </div>
            <div x-show="getSlugSuggestions().length > 0" class="slug-suggestions">
                <p class="text-sm text-gray-600 mb-2">Sugerencias disponibles:</p>
                <div class="suggestions-list">
                    <template x-for="suggestion in getSlugSuggestions()" :key="suggestion">
                        <button 
                            type="button"
                            @click="selectSlugSuggestion(suggestion)"
                            class="suggestion-btn"
                        >
                            <span x-text="suggestion"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div x-show="!canEditSlug()" class="slug-readonly-notice">
                <div class="flex items-center text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-3">
                    <x-solar-info-circle-outline class="w-4 h-4 mr-2" />
                    <span>El plan seleccionado genera la URL automáticamente. Actualiza a un plan superior para personalizar la URL.</span>
                </div>
            </div>
        </div>

        {{-- Store Email --}}
        <div class="form-group" x-show="shouldShowField('email')">
            <label for="email" class="form-label" :class="{ 'required': isFieldRequired('email') }">
                Email de Contacto
            </label>
            <div class="input-with-validation">
                <input 
                    type="email" 
                    id="email"
                    name="email"
                    x-model="formData.email"
                    @input="debounceValidation('email', $event.target.value)"
                    @blur="validateField('email', $event.target.value)"
                    class="form-input"
                    :class="{ 
                        'error': hasFieldError('email'),
                        'validating': isValidating('email'),
                        'valid': isFieldValid('email')
                    }"
                    placeholder="contacto@mitienda.com"
                    :required="isFieldRequired('email')"
                >
                <div class="validation-indicator">
                    <div x-show="isValidating('email')" class="spinner"></div>
                    <div x-show="isFieldValid('email')" class="success-icon">
                        <x-solar-check-circle-outline class="w-5 h-5 text-green-600" />
                    </div>
                    <div x-show="hasFieldError('email')" class="error-icon">
                        <x-solar-close-circle-outline class="w-5 h-5 text-red-600" />
                    </div>
                </div>
            </div>
            <div x-show="hasFieldError('email')" class="field-error">
                <span x-text="getFieldError('email')"></span>
            </div>
        </div>

        {{-- Store Phone --}}
        <div class="form-group" x-show="shouldShowField('phone')">
            <label for="phone" class="form-label" :class="{ 'required': isFieldRequired('phone') }">
                Teléfono de Contacto
            </label>
            <input 
                type="tel" 
                id="phone"
                name="phone"
                x-model="formData.phone"
                @input="validatePhone($event.target.value)"
                @blur="validateField('phone', $event.target.value)"
                class="form-input"
                :class="{ 'error': hasFieldError('phone') }"
                placeholder="+57 300 123 4567"
                :required="isFieldRequired('phone')"
            >
            <div x-show="hasFieldError('phone')" class="field-error">
                <span x-text="getFieldError('phone')"></span>
            </div>
        </div>

        {{-- Store Description --}}
        <div class="form-group col-span-2" x-show="shouldShowField('description')">
            <label for="description" class="form-label">Descripción de la Tienda</label>
            <textarea 
                id="description"
                name="description"
                x-model="formData.description"
                @input="validateDescription($event.target.value)"
                @blur="validateField('description', $event.target.value)"
                class="form-textarea"
                :class="{ 'error': hasFieldError('description') }"
                rows="4"
                placeholder="Describe brevemente tu tienda, productos o servicios..."
                maxlength="1000"
            ></textarea>
            <div class="character-counter">
                <span x-text="(formData.description || '').length"></span>/1000 caracteres
            </div>
            <div x-show="hasFieldError('description')" class="field-error">
                <span x-text="getFieldError('description')"></span>
            </div>
        </div>

        {{-- Store Status --}}
        <div class="form-group" x-show="shouldShowField('status')">
            <label for="status" class="form-label required">Estado Inicial</label>
            <select 
                id="status"
                name="status"
                x-model="formData.status"
                @change="validateField('status', $event.target.value)"
                class="form-select"
                :class="{ 'error': hasFieldError('status') }"
                required
            >
                <option value="active">Activa</option>
                <option value="inactive">Inactiva</option>
            </select>
            <div x-show="hasFieldError('status')" class="field-error">
                <span x-text="getFieldError('status')"></span>
            </div>
            <div class="field-hint">
                <span>Las tiendas activas estarán disponibles inmediatamente</span>
            </div>
        </div>
    </div>

    {{-- Plan Features Summary --}}
    <div x-show="plan" class="plan-features-summary">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="font-medium text-blue-900 mb-4 flex items-center">
                <x-solar-settings-outline class="w-5 h-5 mr-2" />
                Características del Plan Seleccionado
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="feature-item" x-show="plan?.max_products">
                    <div class="feature-icon">
                        <x-solar-box-outline class="w-5 h-5 text-blue-600" />
                    </div>
                    <div class="feature-content">
                        <div class="feature-label">Productos</div>
                        <div class="feature-value" x-text="getPlanFeatureValue('max_products')"></div>
                    </div>
                </div>
                
                <div class="feature-item" x-show="plan?.max_categories">
                    <div class="feature-icon">
                        <x-solar-folder-outline class="w-5 h-5 text-blue-600" />
                    </div>
                    <div class="feature-content">
                        <div class="feature-label">Categorías</div>
                        <div class="feature-value" x-text="getPlanFeatureValue('max_categories')"></div>
                    </div>
                </div>
                
                <div class="feature-item" x-show="plan?.max_admins">
                    <div class="feature-icon">
                        <x-solar-users-group-rounded-outline class="w-5 h-5 text-blue-600" />
                    </div>
                    <div class="feature-content">
                        <div class="feature-label">Administradores</div>
                        <div class="feature-value" x-text="getPlanFeatureValue('max_admins')"></div>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <x-solar-link-outline class="w-5 h-5 text-blue-600" />
                    </div>
                    <div class="feature-content">
                        <div class="feature-label">URL Personalizada</div>
                        <div class="feature-value" x-text="plan?.allow_custom_slug ? 'Permitida' : 'No disponible'"></div>
                    </div>
                </div>
                
                <div class="feature-item" x-show="plan?.support_level">
                    <div class="feature-icon">
                        <x-solar-chat-round-outline class="w-5 h-5 text-blue-600" />
                    </div>
                    <div class="feature-content">
                        <div class="feature-label">Soporte</div>
                        <div class="feature-value" x-text="getPlanFeatureValue('support_level')"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Store Preview --}}
    <div x-show="isStepValid()" class="store-preview">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h4 class="font-medium text-gray-900 mb-4 flex items-center">
                <x-solar-eye-outline class="w-5 h-5 mr-2" />
                Vista Previa de la Tienda
            </h4>
            <div class="preview-content">
                <div class="preview-header">
                    <div class="preview-name" x-text="formData.name || 'Nombre de la Tienda'"></div>
                    <div class="preview-url">
                        <x-solar-link-outline class="w-4 h-4 mr-1" />
                        <span x-text="getStoreUrl()"></span>
                    </div>
                </div>
                <div class="preview-details">
                    <div x-show="formData.description" class="preview-description">
                        <p x-text="formData.description"></p>
                    </div>
                    <div class="preview-contact">
                        <div x-show="formData.email" class="contact-item">
                            <x-solar-letter-outline class="w-4 h-4 mr-2" />
                            <span x-text="formData.email"></span>
                        </div>
                        <div x-show="formData.phone" class="contact-item">
                            <x-solar-phone-outline class="w-4 h-4 mr-2" />
                            <span x-text="formData.phone"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Summary --}}
    <div x-show="validationErrors.length > 0" class="validation-errors-summary">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <x-solar-info-circle-outline class="w-5 h-5 text-red-600 mr-2 mt-0.5" />
                <div>
                    <h5 class="font-medium text-red-800 mb-2">Errores de validación:</h5>
                    <ul class="text-red-700 text-sm space-y-1">
                        <template x-for="error in validationErrors" :key="error.field">
                            <li>
                                <span class="font-medium" x-text="error.label + ':'"></span>
                                <span x-text="error.message"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.store-configuration-step {
    @apply w-full;
}

.form-grid {
    @apply grid grid-cols-1 md:grid-cols-2 gap-6 mb-8;
}

.form-group {
    @apply space-y-2;
}

.form-label {
    @apply block text-sm font-medium text-gray-700;
}

.form-label.required::after {
    content: ' *';
    @apply text-red-500;
}

.form-input {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}

.form-input.error {
    @apply border-red-300 focus:border-red-500 focus:ring-red-500;
}

.form-input.valid {
    @apply border-green-300 focus:border-green-500 focus:ring-green-500;
}

.form-input.validating {
    @apply border-blue-300 focus:border-blue-500 focus:ring-blue-500;
}

.form-input.readonly {
    @apply bg-gray-100 cursor-not-allowed;
}

.form-select {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}

.form-textarea {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 resize-vertical;
}

.slug-input-wrapper {
    @apply flex border border-gray-300 rounded-md overflow-hidden focus-within:ring-blue-500 focus-within:border-blue-500;
}

.slug-prefix {
    @apply bg-gray-50 px-3 py-2 border-r border-gray-300 flex items-center;
}

.slug-input-container {
    @apply flex-1 relative;
}

.slug-input {
    @apply border-0 focus:ring-0 pr-20;
}

.slug-actions {
    @apply absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-2;
}

.slug-generate-btn {
    @apply text-gray-400 hover:text-gray-600 p-1;
}

.input-with-validation {
    @apply relative;
}

.validation-indicator {
    @apply flex items-center;
}

.spinner {
    @apply w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin;
}

.field-error {
    @apply text-red-600 text-sm flex items-center;
}

.field-hint {
    @apply text-gray-500 text-sm;
}

.character-counter {
    @apply text-right text-sm text-gray-500 mt-1;
}

.slug-suggestions {
    @apply mt-2;
}

.suggestions-list {
    @apply flex flex-wrap gap-2;
}

.suggestion-btn {
    @apply px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors;
}

.slug-readonly-notice {
    @apply mt-2;
}

.plan-features-summary {
    @apply mb-8;
}

.feature-item {
    @apply flex items-center space-x-3;
}

.feature-icon {
    @apply flex-shrink-0;
}

.feature-content {
    @apply min-w-0;
}

.feature-label {
    @apply text-sm font-medium text-blue-900;
}

.feature-value {
    @apply text-sm text-blue-700;
}

.store-preview {
    @apply mb-8;
}

.preview-content {
    @apply space-y-4;
}

.preview-header {
    @apply space-y-2;
}

.preview-name {
    @apply text-lg font-semibold text-gray-900;
}

.preview-url {
    @apply flex items-center text-sm text-blue-600;
}

.preview-details {
    @apply space-y-3;
}

.preview-description {
    @apply text-gray-600 text-sm;
}

.preview-contact {
    @apply flex flex-wrap gap-4;
}

.contact-item {
    @apply flex items-center text-sm text-gray-600;
}

.validation-errors-summary {
    @apply mt-6;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/components/store-configuration-step.js') }}"></script>
@endpush