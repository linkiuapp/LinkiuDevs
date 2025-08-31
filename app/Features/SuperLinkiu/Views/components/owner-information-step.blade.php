{{--
    Owner Information Step Component
    
    Enhanced step for collecting owner details with real-time validation,
    document type validation per country, location autocomplete, and password generation
    
    Props:
    - formData: Current form data
    - template: Selected template configuration
    - errors: Validation errors array
    - class: Additional CSS classes
    
    Requirements: 2.1, 2.4, 4.2
--}}

@props([
    'formData' => [],
    'template' => null,
    'errors' => [],
    'class' => ''
])

<div 
    x-data="ownerInformationStep({
        formData: {{ json_encode($formData) }},
        template: {{ json_encode($template) }},
        errors: {{ json_encode($errors) }}
    })"
    class="owner-information-step {{ $class }}"
    x-cloak
>
    {{-- Step Header --}}
    <div class="step-header mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Información del Propietario</h2>
        <p class="text-gray-600">Ingresa los datos del propietario y administrador de la tienda</p>
    </div>

    {{-- Form Grid --}}
    <div class="form-grid">
        {{-- Owner Name --}}
        <div class="form-group col-span-2">
            <label for="owner_name" class="form-label required">
                <span x-text="getFieldLabel('owner_name')"></span>
            </label>
            <input 
                type="text" 
                id="owner_name"
                name="owner_name"
                x-model="formData.owner_name"
                @input="validateField('owner_name', $event.target.value)"
                @blur="validateField('owner_name', $event.target.value)"
                class="form-input"
                :class="{ 'error': hasFieldError('owner_name') }"
                placeholder="Nombre completo del propietario"
                required
            >
            <div x-show="hasFieldError('owner_name')" class="field-error">
                <span x-text="getFieldError('owner_name')"></span>
            </div>
        </div>

        {{-- Admin Email --}}
        <div class="form-group col-span-2">
            <label for="admin_email" class="form-label required">Email del Administrador</label>
            <div class="input-with-validation">
                <input 
                    type="email" 
                    id="admin_email"
                    name="admin_email"
                    x-model="formData.admin_email"
                    @input="debounceValidation('admin_email', $event.target.value)"
                    @blur="validateField('admin_email', $event.target.value)"
                    class="form-input"
                    :class="{ 
                        'error': hasFieldError('admin_email'),
                        'validating': isValidating('admin_email'),
                        'valid': isFieldValid('admin_email')
                    }"
                    placeholder="email@ejemplo.com"
                    required
                >
                <div class="validation-indicator">
                    <div x-show="isValidating('admin_email')" class="spinner"></div>
                    <div x-show="isFieldValid('admin_email')" class="success-icon">
                        <x-solar-check-circle-outline class="w-5 h-5 text-green-600" />
                    </div>
                    <div x-show="hasFieldError('admin_email')" class="error-icon">
                        <x-solar-close-circle-outline class="w-5 h-5 text-red-600" />
                    </div>
                </div>
            </div>
            <div x-show="hasFieldError('admin_email')" class="field-error">
                <span x-text="getFieldError('admin_email')"></span>
            </div>
            <div x-show="getFieldSuggestion('admin_email')" class="field-suggestion">
                <span x-text="getFieldSuggestion('admin_email')"></span>
            </div>
        </div>

        {{-- Document Type --}}
        <div class="form-group" x-show="shouldShowField('owner_document_type')">
            <label for="owner_document_type" class="form-label required">Tipo de Documento</label>
            <select 
                id="owner_document_type"
                name="owner_document_type"
                x-model="formData.owner_document_type"
                @change="onDocumentTypeChange($event.target.value)"
                class="form-select"
                :class="{ 'error': hasFieldError('owner_document_type') }"
                required
            >
                <option value="">Selecciona un tipo</option>
                <template x-for="option in getDocumentTypeOptions()" :key="option.value">
                    <option :value="option.value" x-text="option.label"></option>
                </template>
            </select>
            <div x-show="hasFieldError('owner_document_type')" class="field-error">
                <span x-text="getFieldError('owner_document_type')"></span>
            </div>
        </div>

        {{-- Document Number --}}
        <div class="form-group" x-show="shouldShowField('owner_document_number')">
            <label for="owner_document_number" class="form-label required">
                <span x-text="getDocumentNumberLabel()"></span>
            </label>
            <div class="input-with-validation">
                <input 
                    type="text" 
                    id="owner_document_number"
                    name="owner_document_number"
                    x-model="formData.owner_document_number"
                    @input="validateDocumentNumber($event.target.value)"
                    @blur="validateField('owner_document_number', $event.target.value)"
                    class="form-input"
                    :class="{ 
                        'error': hasFieldError('owner_document_number'),
                        'valid': isFieldValid('owner_document_number')
                    }"
                    :placeholder="getDocumentNumberPlaceholder()"
                    :maxlength="getDocumentNumberMaxLength()"
                    required
                >
                <div class="validation-indicator">
                    <div x-show="isFieldValid('owner_document_number')" class="success-icon">
                        <x-solar-check-circle-outline class="w-5 h-5 text-green-600" />
                    </div>
                    <div x-show="hasFieldError('owner_document_number')" class="error-icon">
                        <x-solar-close-circle-outline class="w-5 h-5 text-red-600" />
                    </div>
                </div>
            </div>
            <div x-show="hasFieldError('owner_document_number')" class="field-error">
                <span x-text="getFieldError('owner_document_number')"></span>
            </div>
            <div x-show="getDocumentNumberHint()" class="field-hint">
                <span x-text="getDocumentNumberHint()"></span>
            </div>
        </div>

        {{-- Country --}}
        <div class="form-group" x-show="shouldShowField('owner_country')">
            <x-superlinkiu::location-autocomplete
                fieldName="owner_country"
                type="countries"
                label="País"
                placeholder="Escribe para buscar país"
                :required="true"
                :initialValue="$formData['owner_country'] ?? ''"
                @location-selected="onLocationSelected"
                @location-validation-error="onLocationValidationError"
            />
        </div>

        {{-- Department/State --}}
        <div class="form-group" x-show="shouldShowField('owner_department')">
            <x-superlinkiu::location-autocomplete
                fieldName="owner_department"
                type="departments"
                :label="getDepartmentLabel()"
                :placeholder="getDepartmentPlaceholder()"
                :country="formData.owner_country"
                :required="true"
                :initialValue="$formData['owner_department'] ?? ''"
                @location-selected="onLocationSelected"
                @location-validation-error="onLocationValidationError"
            />
        </div>

        {{-- City --}}
        <div class="form-group" x-show="shouldShowField('owner_city')">
            <x-superlinkiu::location-autocomplete
                fieldName="owner_city"
                type="cities"
                label="Ciudad"
                placeholder="Escribe para buscar ciudad"
                :country="formData.owner_country"
                :department="formData.owner_department"
                :required="true"
                :initialValue="$formData['owner_city'] ?? ''"
                @location-selected="onLocationSelected"
                @location-validation-error="onLocationValidationError"
            />
        </div>

        {{-- Password Section --}}
        <div class="form-group col-span-2">
            <div class="password-section">
                <div class="password-header">
                    <label for="admin_password" class="form-label required">Contraseña del Administrador</label>
                    <button 
                        type="button"
                        @click="generatePassword()"
                        class="generate-password-btn"
                    >
                        <x-solar-refresh-outline class="w-4 h-4 mr-1" />
                        Generar Automática
                    </button>
                </div>
                
                <div class="password-input-wrapper">
                    <input 
                        :type="showPassword ? 'text' : 'password'"
                        id="admin_password"
                        name="admin_password"
                        x-model="formData.admin_password"
                        @input="validatePassword($event.target.value)"
                        @blur="validateField('admin_password', $event.target.value)"
                        class="form-input password-input"
                        :class="{ 
                            'error': hasFieldError('admin_password'),
                            'valid': isFieldValid('admin_password')
                        }"
                        placeholder="Mínimo 8 caracteres"
                        minlength="8"
                        required
                    >
                    <button 
                        type="button"
                        @click="togglePasswordVisibility()"
                        class="password-toggle"
                    >
                        <x-solar-eye-outline x-show="!showPassword" class="w-5 h-5" />
                        <x-solar-eye-closed-outline x-show="showPassword" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Password Strength Indicator --}}
                <div class="password-strength">
                    <div class="strength-bar">
                        <div 
                            class="strength-fill"
                            :class="getPasswordStrengthClass()"
                            :style="{ width: getPasswordStrengthPercentage() + '%' }"
                        ></div>
                    </div>
                    <div class="strength-label">
                        <span x-text="getPasswordStrengthLabel()"></span>
                    </div>
                </div>

                {{-- Password Requirements --}}
                <div class="password-requirements">
                    <div class="requirement-item" :class="{ 'met': passwordChecks.length }">
                        <x-solar-check-circle-outline x-show="passwordChecks.length" class="w-4 h-4 text-green-600" />
                        <x-solar-close-circle-outline x-show="!passwordChecks.length" class="w-4 h-4 text-gray-400" />
                        <span>Mínimo 8 caracteres</span>
                    </div>
                    <div class="requirement-item" :class="{ 'met': passwordChecks.uppercase }">
                        <x-solar-check-circle-outline x-show="passwordChecks.uppercase" class="w-4 h-4 text-green-600" />
                        <x-solar-close-circle-outline x-show="!passwordChecks.uppercase" class="w-4 h-4 text-gray-400" />
                        <span>Al menos una mayúscula</span>
                    </div>
                    <div class="requirement-item" :class="{ 'met': passwordChecks.lowercase }">
                        <x-solar-check-circle-outline x-show="passwordChecks.lowercase" class="w-4 h-4 text-green-600" />
                        <x-solar-close-circle-outline x-show="!passwordChecks.lowercase" class="w-4 h-4 text-gray-400" />
                        <span>Al menos una minúscula</span>
                    </div>
                    <div class="requirement-item" :class="{ 'met': passwordChecks.number }">
                        <x-solar-check-circle-outline x-show="passwordChecks.number" class="w-4 h-4 text-green-600" />
                        <x-solar-close-circle-outline x-show="!passwordChecks.number" class="w-4 h-4 text-gray-400" />
                        <span>Al menos un número</span>
                    </div>
                    <div class="requirement-item" :class="{ 'met': passwordChecks.special }">
                        <x-solar-check-circle-outline x-show="passwordChecks.special" class="w-4 h-4 text-green-600" />
                        <x-solar-close-circle-outline x-show="!passwordChecks.special" class="w-4 h-4 text-gray-400" />
                        <span>Al menos un carácter especial</span>
                    </div>
                </div>

                <div x-show="hasFieldError('admin_password')" class="field-error">
                    <span x-text="getFieldError('admin_password')"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Summary --}}
    <div x-show="isStepValid()" class="form-summary">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <x-solar-check-circle-outline class="w-5 h-5 text-green-600 mr-2" />
                <span class="font-medium text-green-800">Información del propietario completa</span>
            </div>
            <div class="text-sm text-green-700">
                <p>Propietario: <span class="font-medium" x-text="formData.owner_name"></span></p>
                <p>Email: <span class="font-medium" x-text="formData.admin_email"></span></p>
                <p x-show="formData.owner_country">Ubicación: <span class="font-medium" x-text="getLocationSummary()"></span></p>
            </div>
        </div>
    </div>

    {{-- Validation Errors Summary --}}
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
.owner-information-step {
    @apply w-full;
}

.form-grid {
    @apply grid grid-cols-1 md:grid-cols-2 gap-6;
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

.form-select {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}

.input-with-validation {
    @apply relative;
}

.validation-indicator {
    @apply absolute right-3 top-1/2 transform -translate-y-1/2;
}

.spinner {
    @apply w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin;
}

.field-error {
    @apply text-red-600 text-sm flex items-center;
}

.field-suggestion {
    @apply text-blue-600 text-sm;
}

.field-hint {
    @apply text-gray-500 text-sm;
}

.autocomplete-wrapper {
    @apply relative;
}

.autocomplete-dropdown {
    @apply absolute z-10 w-full bg-accent border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto;
}

.autocomplete-item {
    @apply px-3 py-2 cursor-pointer hover:bg-gray-100 flex items-center space-x-2;
}

.country-flag {
    @apply text-lg;
}

.password-section {
    @apply space-y-3;
}

.password-header {
    @apply flex items-center justify-between;
}

.generate-password-btn {
    @apply text-sm text-blue-600 hover:text-blue-800 flex items-center;
}

.password-input-wrapper {
    @apply relative;
}

.password-input {
    @apply pr-12;
}

.password-toggle {
    @apply absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600;
}

.password-strength {
    @apply space-y-1;
}

.strength-bar {
    @apply w-full h-2 bg-gray-200 rounded-full overflow-hidden;
}

.strength-fill {
    @apply h-full transition-all duration-300;
}

.strength-fill.weak {
    @apply bg-red-500;
}

.strength-fill.fair {
    @apply bg-yellow-500;
}

.strength-fill.good {
    @apply bg-blue-500;
}

.strength-fill.strong {
    @apply bg-green-500;
}

.strength-label {
    @apply text-sm text-gray-600;
}

.password-requirements {
    @apply space-y-1;
}

.requirement-item {
    @apply flex items-center space-x-2 text-sm;
}

.requirement-item.met {
    @apply text-green-700;
}

.requirement-item:not(.met) {
    @apply text-gray-500;
}

.form-summary {
    @apply mt-8;
}

.validation-errors-summary {
    @apply mt-6;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/components/owner-information-step.js') }}"></script>
@endpush