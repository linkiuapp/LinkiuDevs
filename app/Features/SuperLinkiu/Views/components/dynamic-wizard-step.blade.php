{{--
    Dynamic Wizard Step Component
    
    Enhanced wizard step that integrates with dynamic form generation
    Automatically generates form fields based on template configuration
    
    Props:
    - stepId: Unique identifier for the step
    - stepIndex: Numeric index of the step
    - title: Step title for display
    - description: Optional step description
    - templateId: Selected template ID
    - isOptional: Whether the step is optional (default: false)
    - validationRules: Array of validation rules
    - initialData: Initial form data
    - autoSaveInterval: Auto-save interval in milliseconds (default: 30000)
    
    Requirements: 3.2, 3.4
--}}

@props([
    'stepId' => '',
    'stepIndex' => 0,
    'title' => '',
    'description' => '',
    'templateId' => null,
    'isOptional' => false,
    'validationRules' => [],
    'initialData' => [],
    'autoSaveInterval' => 30000,
    'class' => ''
])

<div 
    x-data="dynamicWizardStep({
        stepId: '{{ $stepId }}',
        stepIndex: {{ $stepIndex }},
        templateId: '{{ $templateId }}',
        isOptional: {{ $isOptional ? 'true' : 'false' }},
        validationRules: {{ json_encode($validationRules) }},
        initialData: {{ json_encode($initialData) }},
        autoSaveInterval: {{ $autoSaveInterval }}
    })"
    data-step="{{ $stepIndex }}"
    data-step-id="{{ $stepId }}"
    class="wizard-step dynamic-wizard-step {{ $class }}"
    :class="{ 'hidden': $parent.currentStep !== {{ $stepIndex }} }"
    x-cloak
>
    {{-- Step Header --}}
    @if($title || $description)
    <div class="wizard-step-header mb-6">
        @if($title)
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
            {{ $title }}
            @if($isOptional)
                <span class="text-sm font-normal text-gray-500">(Opcional)</span>
            @endif
        </h2>
        @endif
        
        @if($description)
        <p class="text-gray-600">{{ $description }}</p>
        @endif
    </div>
    @endif

    {{-- Loading State --}}
    <div x-show="isLoadingFields" class="wizard-loading">
        <div class="flex items-center justify-center py-8">
            <div class="wizard-spinner mr-3"></div>
            <span class="text-gray-600">Cargando campos del formulario...</span>
        </div>
    </div>

    {{-- Dynamic Form Fields --}}
    <div x-show="!isLoadingFields && fields.length > 0">
        <x-superlinkiu::dynamic-form-generator
            :templateId="$templateId"
            :stepId="$stepId"
            x-bind:fields="fields"
            x-bind:formData="formData"
            x-bind:validationErrors="validationErrors"
            x-bind:showOptionalFields="showOptionalFields"
            @field-changed="handleFieldChange($event.detail)"
        />
    </div>

    {{-- Static Content (fallback) --}}
    <div x-show="!isLoadingFields && fields.length === 0" class="wizard-step-content">
        {{ $slot }}
    </div>

    {{-- Template-specific Content --}}
    <div x-show="!isLoadingFields && templateSpecificContent" class="template-specific-content mt-6">
        {{-- Plan Selection (for template selection step) --}}
        <template x-if="stepId === 'template-selection' && selectedTemplate">
            <div class="plan-selection mt-8" x-transition>
                <label class="block text-sm font-medium text-gray-700 mb-2">Plan</label>
                <select 
                    name="plan_id" 
                    x-model="formData.plan_id"
                    @change="handleFieldChange({ fieldName: 'plan_id', value: $event.target.value })"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                >
                    <option value="">Seleccionar Plan</option>
                    @if(isset($plans))
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} - {{ $plan->getPriceFormatted() }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </template>

        {{-- Billing Configuration (for review step) --}}
        <template x-if="stepId === 'review' && templateConfig">
            <div class="billing-configuration mt-8" x-transition>
                <h3 class="text-lg font-semibold mb-4">Configuración de Facturación</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Período de Facturación</label>
                        <select 
                            name="billing_period" 
                            x-model="formData.billing_period"
                            @change="handleFieldChange({ fieldName: 'billing_period', value: $event.target.value })"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="monthly">Mensual</option>
                            <option value="quarterly">Trimestral</option>
                            <option value="biannual">Semestral</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado Inicial del Pago</label>
                        <select 
                            name="initial_payment_status" 
                            x-model="formData.initial_payment_status"
                            @change="handleFieldChange({ fieldName: 'initial_payment_status', value: $event.target.value })"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="pending">Pendiente</option>
                            <option value="paid">Pagado</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Validation Errors --}}
    <div x-show="hasErrors()" class="wizard-error mt-6">
        <div class="wizard-error-title">
            <x-solar-info-circle-outline class="w-5 h-5 inline mr-2" />
            Por favor corrige los siguientes errores:
        </div>
        <ul class="wizard-error-list">
            <template x-for="(error, field) in validationErrors" :key="field">
                <li x-text="error"></li>
            </template>
        </ul>
    </div>

    {{-- Step Actions (if provided) --}}
    @isset($actions)
    <div class="wizard-step-actions mt-6 pt-4 border-t border-gray-200">
        {{ $actions }}
    </div>
    @endisset

    {{-- Step Status Indicators --}}
    <div class="wizard-step-status mt-4">
        {{-- Auto-save Indicator --}}
        <div x-show="autoSaveStatus.isVisible" class="flex items-center mb-2" :class="{
            'text-blue-600': autoSaveStatus.status === 'saving',
            'text-green-600': autoSaveStatus.status === 'saved',
            'text-red-600': autoSaveStatus.status === 'error'
        }">
            <div x-show="autoSaveStatus.status === 'saving'" class="wizard-spinner mr-2"></div>
            <x-solar-check-circle-outline x-show="autoSaveStatus.status === 'saved'" class="w-4 h-4 mr-2" />
            <x-solar-close-circle-outline x-show="autoSaveStatus.status === 'error'" class="w-4 h-4 mr-2" />
            <span class="text-sm" x-text="autoSaveStatus.message"></span>
        </div>

        {{-- Validation Status --}}
        <div x-show="isValidating" class="flex items-center text-blue-600 mb-2">
            <div class="wizard-spinner mr-2"></div>
            <span class="text-sm">Validando campos...</span>
        </div>
        
        <div x-show="isCompleted && !hasErrors()" class="flex items-center text-green-600 mb-2">
            <x-solar-check-circle-outline class="w-5 h-5 mr-2" />
            <span class="text-sm">Paso completado correctamente</span>
        </div>
        
        <div x-show="hasErrors()" class="flex items-center text-red-600 mb-2">
            <x-solar-close-circle-outline class="w-5 h-5 mr-2" />
            <span class="text-sm">
                <span x-text="Object.keys(validationErrors).length"></span> 
                <span x-text="Object.keys(validationErrors).length === 1 ? 'error' : 'errores'"></span>
                de validación
            </span>
        </div>

        {{-- Field Count Info --}}
        <div x-show="fields.length > 0" class="text-xs text-gray-500">
            <span x-text="getCompletedFieldsCount()"></span> de <span x-text="getRequiredFieldsCount()"></span> campos completados
        </div>
    </div>
</div>

@push('styles')
<style>
.dynamic-wizard-step {
    @apply min-h-96;
}

.wizard-loading {
    @apply bg-gray-50 rounded-lg p-8;
}

.template-specific-content {
    @apply border-t border-gray-200 pt-6;
}

.plan-selection {
    @apply bg-blue-50 rounded-lg p-4;
}

.billing-configuration {
    @apply bg-gray-50 rounded-lg p-4;
}

.wizard-step-status {
    @apply border-t border-gray-100 pt-4;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/components/dynamic-wizard-step.js') }}"></script>
@endpush