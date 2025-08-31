{{--
    WizardNavigation Blade Component
    
    Reusable wizard navigation component with Alpine.js integration
    Provides progress bar, step indicators, and navigation controls
    
    Props:
    - steps: Array of step configurations
    - initialStep: Starting step index (default: 0)
    - allowBackNavigation: Allow backward navigation (default: true)
    - showProgressBar: Show progress bar (default: true)
    - showBreadcrumbs: Show breadcrumb navigation (default: true)
    - compact: Use compact mode (default: false)
    
    Requirements: 1.1, 1.2
--}}

@props([
    'steps' => [],
    'initialStep' => 0,
    'allowBackNavigation' => true,
    'showProgressBar' => true,
    'showBreadcrumbs' => true,
    'compact' => false,
    'class' => ''
])

<div 
    x-data="wizardNavigation({
        steps: {{ json_encode($steps) }},
        initialStep: {{ $initialStep }},
        allowBackNavigation: {{ $allowBackNavigation ? 'true' : 'false' }},
        showProgressBar: {{ $showProgressBar ? 'true' : 'false' }},
        showBreadcrumbs: {{ $showBreadcrumbs ? 'true' : 'false' }}
    })"
    class="wizard-container {{ $compact ? 'wizard-compact' : '' }} {{ $class }}"
    x-cloak
>
    {{-- Progress Bar --}}
    <div x-show="showProgressBar" class="wizard-progress">
        <div 
            class="wizard-progress-bar"
            :style="`width: ${getProgressPercentage()}%`"
        ></div>
        <div class="wizard-progress-text">
            <span x-text="`Paso ${currentStep + 1} de ${steps.length}`"></span>
            <span x-show="getProgressPercentage() > 0" x-text="`(${getProgressPercentage()}% completado)`"></span>
        </div>
    </div>

    {{-- Breadcrumbs --}}
    <div x-show="showBreadcrumbs && !compact" class="wizard-breadcrumbs">
        <template x-for="(step, index) in steps" :key="index">
            <div class="flex items-center">
                <span 
                    x-text="step.title"
                    :class="{
                        'wizard-breadcrumb current': index === currentStep,
                        'wizard-breadcrumb': index !== currentStep && canNavigateToStep(index),
                        'text-gray-400': !canNavigateToStep(index)
                    }"
                    @click="canNavigateToStep(index) && navigateToStep(index)"
                ></span>
                <span 
                    x-show="index < steps.length - 1" 
                    class="wizard-breadcrumb-separator mx-2"
                >
                    /
                </span>
            </div>
        </template>
    </div>

    {{-- Step Navigation --}}
    <nav class="wizard-nav">
        <template x-for="(step, index) in steps" :key="index">
            <div class="wizard-nav-step" :class="{ 'completed': isStepCompleted(index) }">
                <div 
                    class="wizard-step-indicator"
                    :class="getStepStatus(index)"
                    @click="canNavigateToStep(index) && navigateToStep(index)"
                >
                    {{-- Completed Step Icon --}}
                    <template x-if="isStepCompleted(index)">
                        <x-solar-check-circle-outline class="wizard-step-icon wizard-step-check" />
                    </template>
                    
                    {{-- Error Step Icon --}}
                    <template x-if="hasStepErrors(index) && index === currentStep">
                        <x-solar-close-circle-outline class="wizard-step-icon wizard-step-error" />
                    </template>
                    
                    {{-- Step Number --}}
                    <template x-if="!isStepCompleted(index) && !hasStepErrors(index)">
                        <span x-text="index + 1"></span>
                    </template>
                </div>
                
                {{-- Step Label --}}
                <div 
                    x-show="!compact"
                    class="wizard-step-label"
                    :class="getStepStatus(index)"
                    @click="canNavigateToStep(index) && navigateToStep(index)"
                >
                    <div class="font-medium" x-text="step.title"></div>
                    <div x-show="step.description" class="text-xs text-gray-500" x-text="step.description"></div>
                </div>
            </div>
        </template>
    </nav>

    {{-- Validation Errors --}}
    <div x-show="hasStepErrors(currentStep)" class="wizard-error">
        <div class="wizard-error-title">
            <x-solar-info-circle-outline class="w-5 h-5 inline mr-2" />
            Por favor corrige los siguientes errores:
        </div>
        <ul class="wizard-error-list">
            <template x-for="(error, field) in getStepErrors(currentStep)" :key="field">
                <li x-text="error"></li>
            </template>
        </ul>
    </div>

    {{-- Step Content Container --}}
    <div class="wizard-step-content">
        {{ $slot }}
    </div>

    {{-- Navigation Controls --}}
    <div class="wizard-controls">
        <button 
            type="button"
            class="wizard-btn wizard-btn-secondary"
            :disabled="!canGoPrevious() || isNavigating"
            @click="previousStep()"
            x-show="!isFirstStep()"
        >
            <x-solar-arrow-left-outline class="w-5 h-5 inline mr-2" />
            Anterior
        </button>

        <div class="flex-1"></div>

        <button 
            type="button"
            class="wizard-btn wizard-btn-primary"
            :disabled="!canGoNext() || isNavigating || hasStepErrors(currentStep)"
            @click="nextStep()"
            x-show="!isLastStep()"
        >
            <span x-show="!isNavigating">Siguiente</span>
            <span x-show="isNavigating">Validando...</span>
            <x-solar-arrow-right-outline class="w-5 h-5 inline ml-2" x-show="!isNavigating" />
            <div class="wizard-spinner inline ml-2" x-show="isNavigating"></div>
        </button>

        {{-- Custom action slot for last step --}}
        <div x-show="isLastStep()">
            {{ $actions ?? '' }}
        </div>
    </div>

    {{-- Auto-save Indicator --}}
    <div 
        class="wizard-autosave"
        :class="{ 'visible': $store.autosave.isVisible }"
        x-show="$store.autosave && $store.autosave.isVisible"
    >
        <div class="flex items-center">
            <div class="wizard-spinner mr-2" x-show="$store.autosave.status === 'saving'"></div>
            <x-solar-check-circle-outline class="w-4 h-4 mr-2" x-show="$store.autosave.status === 'saved'" />
            <x-solar-close-circle-outline class="w-4 h-4 mr-2" x-show="$store.autosave.status === 'error'" />
            <span x-text="$store.autosave.message"></span>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/wizard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/components/wizard-navigation.js') }}"></script>
@endpush