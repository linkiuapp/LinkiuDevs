{{--
    Dynamic Form Generator Component
    
    Generates form fields dynamically based on template configuration
    Supports conditional field display, dependencies, and validation
    
    Props:
    - templateId: Selected template ID
    - stepId: Current step ID
    - fields: Array of field configurations
    - formData: Current form data
    - validationErrors: Validation errors
    - showOptionalFields: Show optional fields (default: true)
    - class: Additional CSS classes
    
    Requirements: 3.2, 3.4
--}}

@props([
    'templateId' => null,
    'stepId' => null,
    'fields' => [],
    'formData' => [],
    'validationErrors' => [],
    'showOptionalFields' => true,
    'class' => ''
])

<div 
    x-data="dynamicFormGenerator({
        templateId: '{{ $templateId }}',
        stepId: '{{ $stepId }}',
        fields: {{ json_encode($fields) }},
        formData: {{ json_encode($formData) }},
        validationErrors: {{ json_encode($validationErrors) }},
        showOptionalFields: {{ $showOptionalFields ? 'true' : 'false' }}
    })"
    class="dynamic-form-generator {{ $class }}"
    x-cloak
>
    {{-- Form Fields Container --}}
    <div class="form-fields-container">
        <template x-for="field in visibleFields" :key="field.name">
            <div 
                class="form-field-wrapper"
                :class="{
                    'required': field.required,
                    'optional': !field.required,
                    'has-error': hasFieldError(field.name),
                    'dependent': field.dependsOn,
                    'hidden': !isFieldVisible(field)
                }"
                x-show="isFieldVisible(field)"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
            >
                {{-- Field Label --}}
                <label 
                    :for="field.name"
                    class="form-field-label"
                    :class="{ 'required': field.required }"
                >
                    <span x-text="field.label"></span>
                    <span x-show="field.required" class="required-indicator">*</span>
                    <span x-show="!field.required && showOptionalFields" class="optional-indicator">(Opcional)</span>
                </label>

                {{-- Field Input --}}
                <div class="form-field-input">
                    {{-- Text Input --}}
                    <template x-if="field.type === 'text'">
                        <input 
                            type="text"
                            :name="field.name"
                            :id="field.name"
                            :placeholder="field.placeholder || ''"
                            :required="field.required"
                            :disabled="isFieldDisabled(field)"
                            x-model="formData[field.name]"
                            @input="handleFieldChange(field.name, $event.target.value)"
                            @blur="validateField(field.name)"
                            class="form-input"
                            :class="{ 'error': hasFieldError(field.name) }"
                        >
                    </template>

                    {{-- Email Input --}}
                    <template x-if="field.type === 'email'">
                        <input 
                            type="email"
                            :name="field.name"
                            :id="field.name"
                            :placeholder="field.placeholder || 'ejemplo@correo.com'"
                            :required="field.required"
                            :disabled="isFieldDisabled(field)"
                            x-model="formData[field.name]"
                            @input="handleFieldChange(field.name, $event.target.value)"
                            @blur="validateField(field.name)"
                            class="form-input"
                            :class="{ 'error': hasFieldError(field.name) }"
                        >
                    </template>

                    {{-- Password Input --}}
                    <template x-if="field.type === 'password'">
                        <div class="password-input-wrapper">
                            <input 
                                :type="passwordVisible[field.name] ? 'text' : 'password'"
                                :name="field.name"
                                :id="field.name"
                                :placeholder="field.placeholder || ''"
                                :required="field.required"
                                :disabled="isFieldDisabled(field)"
                                x-model="formData[field.name]"
                                @input="handleFieldChange(field.name, $event.target.value)"
                                @blur="validateField(field.name)"
                                class="form-input password-input"
                                :class="{ 'error': hasFieldError(field.name) }"
                            >
                            <button 
                                type="button"
                                @click="togglePasswordVisibility(field.name)"
                                class="password-toggle"
                            >
                                <x-solar-eye-outline x-show="!passwordVisible[field.name]" class="w-5 h-5" />
                                <x-solar-eye-closed-outline x-show="passwordVisible[field.name]" class="w-5 h-5" />
                            </button>
                        </div>
                    </template>

                    {{-- Number Input --}}
                    <template x-if="field.type === 'number'">
                        <input 
                            type="number"
                            :name="field.name"
                            :id="field.name"
                            :placeholder="field.placeholder || ''"
                            :required="field.required"
                            :disabled="isFieldDisabled(field)"
                            :min="field.min || null"
                            :max="field.max || null"
                            :step="field.step || null"
                            x-model="formData[field.name]"
                            @input="handleFieldChange(field.name, $event.target.value)"
                            @blur="validateField(field.name)"
                            class="form-input"
                            :class="{ 'error': hasFieldError(field.name) }"
                        >
                    </template>

                    {{-- Tel Input --}}
                    <template x-if="field.type === 'tel'">
                        <input 
                            type="tel"
                            :name="field.name"
                            :id="field.name"
                            :placeholder="field.placeholder || '+57 300 123 4567'"
                            :required="field.required"
                            :disabled="isFieldDisabled(field)"
                            x-model="formData[field.name]"
                            @input="handleFieldChange(field.name, $event.target.value)"
                            @blur="validateField(field.name)"
                            class="form-input"
                            :class="{ 'error': hasFieldError(field.name) }"
                        >
                    </template>

                    {{-- URL Input --}}
                    <template x-if="field.type === 'url'">
                        <input 
                            type="url"
                            :name="field.name"
                            :id="field.name"
                            :placeholder="field.placeholder || 'https://ejemplo.com'"
                            :required="field.required"
                            :disabled="isFieldDisabled(field)"
                            x-model="formData[field.name]"
                            @input="handleFieldChange(field.name, $event.target.value)"
                            @blur="validateField(field.name)"
                            class="form-input"
                            :class="{ 'error': hasFieldError(field.name) }"
                        >
                    </template>

                    {{-- Select Input --}}
                    <template x-if="field.type === 'select'">
                        <select 
                            :name="field.name"
                            :id="field.name"
                            :required="field.required"
                            :disabled="isFieldDisabled(field)"
                            x-model="formData[field.name]"
                            @change="handleFieldChange(field.name, $event.target.value)"
                            @blur="validateField(field.name)"
                            class="form-select"
                            :class="{ 'error': hasFieldError(field.name) }"
                        >
                            <option value="">
                                <span x-text="field.placeholder || 'Seleccionar...'"></span>
                            </option>
                            <template x-for="(optionLabel, optionValue) in field.options" :key="optionValue">
                                <option :value="optionValue" x-text="optionLabel"></option>
                            </template>
                        </select>
                    </template>

                    {{-- Textarea Input --}}
                    <template x-if="field.type === 'textarea'">
                        <textarea 
                            :name="field.name"
                            :id="field.name"
                            :placeholder="field.placeholder || ''"
                            :required="field.required"
                            :disabled="isFieldDisabled(field)"
                            :rows="field.rows || 3"
                            :maxlength="field.maxlength || null"
                            x-model="formData[field.name]"
                            @input="handleFieldChange(field.name, $event.target.value)"
                            @blur="validateField(field.name)"
                            class="form-textarea"
                            :class="{ 'error': hasFieldError(field.name) }"
                        ></textarea>
                    </template>

                    {{-- Autocomplete Input --}}
                    <template x-if="field.type === 'autocomplete'">
                        <div class="autocomplete-wrapper" x-data="autocompleteField(field)">
                            <input 
                                type="text"
                                :name="field.name"
                                :id="field.name"
                                :placeholder="field.placeholder || ''"
                                :required="field.required"
                                :disabled="isFieldDisabled(field)"
                                x-model="searchQuery"
                                @input="handleAutocompleteInput($event.target.value)"
                                @blur="handleAutocompleteBlur()"
                                @focus="handleAutocompleteFocus()"
                                @keydown.arrow-down.prevent="selectNext()"
                                @keydown.arrow-up.prevent="selectPrevious()"
                                @keydown.enter.prevent="selectCurrent()"
                                @keydown.escape="hideSuggestions()"
                                class="form-input autocomplete-input"
                                :class="{ 'error': hasFieldError(field.name) }"
                                autocomplete="off"
                            >
                            
                            {{-- Autocomplete Suggestions --}}
                            <div 
                                x-show="showSuggestions && suggestions.length > 0"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="autocomplete-suggestions"
                            >
                                <template x-for="(suggestion, index) in suggestions" :key="index">
                                    <div 
                                        class="autocomplete-suggestion"
                                        :class="{ 'selected': selectedIndex === index }"
                                        @click="selectSuggestion(suggestion)"
                                        @mouseenter="selectedIndex = index"
                                    >
                                        <span x-text="suggestion.label || suggestion"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Field Help Text --}}
                <div x-show="field.helpText" class="form-field-help">
                    <span x-text="field.helpText"></span>
                </div>

                {{-- Field Error --}}
                <div x-show="hasFieldError(field.name)" class="form-field-error">
                    <x-solar-info-circle-outline class="w-4 h-4 mr-1" />
                    <span x-text="getFieldError(field.name)"></span>
                </div>

                {{-- Field Success --}}
                <div x-show="hasFieldSuccess(field.name)" class="form-field-success">
                    <x-solar-check-circle-outline class="w-4 h-4 mr-1" />
                    <span x-text="getFieldSuccess(field.name)"></span>
                </div>

                {{-- Character Counter --}}
                <div 
                    x-show="field.maxlength && (field.type === 'text' || field.type === 'textarea')"
                    class="form-field-counter"
                >
                    <span x-text="getCharacterCount(field.name)"></span>/<span x-text="field.maxlength"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- Optional Fields Toggle --}}
    <div x-show="hasOptionalFields()" class="optional-fields-toggle">
        <button 
            type="button"
            @click="toggleOptionalFields()"
            class="optional-fields-btn"
        >
            <span x-show="!showOptionalFields">
                <x-solar-add-circle-outline class="w-4 h-4 mr-2" />
                Mostrar campos opcionales
            </span>
            <span x-show="showOptionalFields">
                <x-solar-minus-circle-outline class="w-4 h-4 mr-2" />
                Ocultar campos opcionales
            </span>
        </button>
    </div>

    {{-- Field Dependencies Info --}}
    <div x-show="hasDependentFields()" class="field-dependencies-info">
        <div class="dependencies-header">
            <x-solar-info-circle-outline class="w-4 h-4 mr-2" />
            <span>Algunos campos aparecerán según tus selecciones</span>
        </div>
    </div>
</div>

@push('styles')
<style>
.dynamic-form-generator {
    @apply w-full;
}

.form-fields-container {
    @apply space-y-6;
}

.form-field-wrapper {
    @apply relative;
}

.form-field-wrapper.hidden {
    @apply hidden;
}

.form-field-label {
    @apply block text-sm font-medium text-gray-700 mb-2 flex items-center;
}

.form-field-label.required {
    @apply font-semibold;
}

.required-indicator {
    @apply text-red-500 ml-1;
}

.optional-indicator {
    @apply text-gray-400 ml-1 text-xs;
}

.form-field-input {
    @apply relative;
}

.form-input, .form-select, .form-textarea {
    @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200;
}

.form-input.error, .form-select.error, .form-textarea.error {
    @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}

.form-input:disabled, .form-select:disabled, .form-textarea:disabled {
    @apply bg-gray-100 text-gray-500 cursor-not-allowed;
}

.password-input-wrapper {
    @apply relative;
}

.password-input {
    @apply pr-12;
}

.password-toggle {
    @apply absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none;
}

.autocomplete-wrapper {
    @apply relative;
}

.autocomplete-suggestions {
    @apply absolute z-10 w-full bg-accent border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto mt-1;
}

.autocomplete-suggestion {
    @apply px-4 py-2 cursor-pointer hover:bg-gray-100 border-b border-gray-100 last:border-b-0;
}

.autocomplete-suggestion.selected {
    @apply bg-blue-100 text-blue-900;
}

.form-field-help {
    @apply mt-1 text-xs text-gray-500;
}

.form-field-error {
    @apply mt-1 text-sm text-red-600 flex items-center;
}

.form-field-success {
    @apply mt-1 text-sm text-green-600 flex items-center;
}

.form-field-counter {
    @apply mt-1 text-xs text-gray-400 text-right;
}

.optional-fields-toggle {
    @apply mt-6 text-center;
}

.optional-fields-btn {
    @apply text-blue-600 hover:text-blue-800 text-sm flex items-center mx-auto transition-colors duration-200;
}

.field-dependencies-info {
    @apply mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg;
}

.dependencies-header {
    @apply flex items-center text-sm text-blue-700;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/components/dynamic-form-generator.js') }}"></script>
@endpush