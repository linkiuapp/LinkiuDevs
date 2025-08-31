{{--
    WizardStep Blade Component
    
    Abstract step component for consistent behavior across wizard steps
    Provides validation interface, error handling, and auto-save functionality
    
    Props:
    - stepId: Unique identifier for the step
    - stepIndex: Numeric index of the step
    - title: Step title for display
    - description: Optional step description
    - isOptional: Whether the step is optional (default: false)
    - validationRules: Array of validation rules
    - initialData: Initial form data
    - autoSaveInterval: Auto-save interval in milliseconds (default: 30000)
    
    Requirements: 1.2, 5.2
--}}

@props([
    'stepId' => '',
    'stepIndex' => 0,
    'title' => '',
    'description' => '',
    'isOptional' => false,
    'validationRules' => [],
    'initialData' => [],
    'autoSaveInterval' => 30000,
    'class' => ''
])

<div 
    x-data="wizardStep({
        stepId: '{{ $stepId }}',
        stepIndex: {{ $stepIndex }},
        isOptional: {{ $isOptional ? 'true' : 'false' }},
        validationRules: {{ json_encode($validationRules) }},
        initialData: {{ json_encode($initialData) }},
        autoSaveInterval: {{ $autoSaveInterval }}
    })"
    data-step="{{ $stepIndex }}"
    class="wizard-step {{ $class }}"
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

    {{-- Validation Errors --}}
    <div x-show="hasErrors()" class="wizard-error mb-6">
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

    {{-- Step Content --}}
    <div class="wizard-step-content">
        {{ $slot }}
    </div>

    {{-- Step Actions (if provided) --}}
    @isset($actions)
    <div class="wizard-step-actions mt-6 pt-4 border-t border-gray-200">
        {{ $actions }}
    </div>
    @endisset

    {{-- Validation Status Indicator --}}
    <div class="wizard-step-status mt-4">
        <div x-show="isValidating" class="flex items-center text-blue-600">
            <div class="wizard-spinner mr-2"></div>
            <span class="text-sm">Validando...</span>
        </div>
        
        <div x-show="isCompleted && !hasErrors()" class="flex items-center text-green-600">
            <x-solar-check-circle-outline class="w-5 h-5 mr-2" />
            <span class="text-sm">Paso completado</span>
        </div>
        
        <div x-show="hasErrors()" class="flex items-center text-red-600">
            <x-solar-close-circle-outline class="w-5 h-5 mr-2" />
            <span class="text-sm">Errores de validación</span>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="{{ asset('js/components/wizard-step.js') }}"></script>
@endpush
@endonce