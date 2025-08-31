{{--
    Template and Plan Selection Step Component
    
    Enhanced step for selecting store template and plan with dynamic options,
    feature comparison, and pricing display
    
    Props:
    - templates: Array of available templates
    - plans: Array of available plans
    - selectedTemplate: Currently selected template ID
    - selectedPlan: Currently selected plan ID
    - showComparison: Show template/plan comparison (default: true)
    - class: Additional CSS classes
    
    Requirements: 3.1, 6.1
--}}

@props([
    'templates' => [],
    'plans' => [],
    'selectedTemplate' => null,
    'selectedPlan' => null,
    'showComparison' => true,
    'class' => ''
])

<div 
    x-data="templatePlanSelection({
        templates: {{ json_encode($templates) }},
        plans: {{ json_encode($plans) }},
        selectedTemplate: '{{ $selectedTemplate }}',
        selectedPlan: '{{ $selectedPlan }}',
        showComparison: {{ $showComparison ? 'true' : 'false' }}
    })"
    class="template-plan-selection-step {{ $class }}"
    x-cloak
>
    {{-- Step Header --}}
    <div class="step-header mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Selecciona Plantilla y Plan</h2>
        <p class="text-gray-600">Elige el tipo de tienda y plan que mejor se adapte a tus necesidades</p>
    </div>

    {{-- Template Selection Section --}}
    <div class="template-selection-section mb-12">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">1. Tipo de Tienda</h3>
            <button 
                type="button"
                @click="toggleTemplateComparison()"
                class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
            >
                <x-solar-scale-outline class="w-4 h-4 mr-1" />
                <span x-text="showTemplateComparison ? 'Ocultar Comparación' : 'Comparar Plantillas'"></span>
            </button>
        </div>

        {{-- Template Comparison Table --}}
        <div x-show="showTemplateComparison" x-transition class="template-comparison mb-6">
            <div class="bg-gray-50 rounded-lg p-6">
                <h4 class="font-medium text-gray-900 mb-4">Comparación de Plantillas</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-3">Característica</th>
                                <template x-for="template in templates" :key="template.id">
                                    <th class="text-center py-2 px-3" x-text="template.name"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="feature in getTemplateComparisonFeatures()" :key="feature.key">
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 px-3 font-medium" x-text="feature.name"></td>
                                    <template x-for="template in templates" :key="template.id">
                                        <td class="text-center py-2 px-3">
                                            <span 
                                                x-show="templateHasFeature(template, feature.key)"
                                                class="text-green-600"
                                            >
                                                <x-solar-check-circle-outline class="w-4 h-4 inline" />
                                            </span>
                                            <span 
                                                x-show="!templateHasFeature(template, feature.key)"
                                                class="text-gray-400"
                                            >
                                                <x-solar-close-circle-outline class="w-4 h-4 inline" />
                                            </span>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Template Cards --}}
        <div class="template-cards-grid">
            <template x-for="template in templates" :key="template.id">
                <div 
                    class="template-card"
                    :class="{
                        'selected': selectedTemplate === template.id,
                        'recommended': template.isRecommended
                    }"
                    @click="selectTemplate(template.id)"
                >
                    {{-- Recommended Badge --}}
                    <div x-show="template.isRecommended" class="template-badge">
                        <x-solar-star-outline class="w-4 h-4 mr-1" />
                        Recomendada
                    </div>

                    {{-- Template Icon --}}
                    <div class="template-icon">
                        <div 
                            class="template-icon-wrapper"
                            :class="template.iconColor || 'text-blue-500'"
                        >
                            <template x-if="template.icon === 'shop'">
                                <x-solar-shop-outline class="w-12 h-12" />
                            </template>
                            <template x-if="template.icon === 'buildings'">
                                <x-solar-buildings-outline class="w-12 h-12" />
                            </template>
                            <template x-if="template.icon === 'case'">
                                <x-solar-case-minimalistic-outline class="w-12 h-12" />
                            </template>
                        </div>
                    </div>

                    {{-- Template Content --}}
                    <div class="template-content">
                        <h4 class="template-title" x-text="template.name"></h4>
                        <p class="template-description" x-text="template.description"></p>
                        
                        {{-- Template Features --}}
                        <div class="template-features">
                            <template x-for="feature in template.features.slice(0, 3)" :key="feature">
                                <div class="template-feature">
                                    <x-solar-check-circle-outline class="w-3 h-3 mr-1" />
                                    <span x-text="feature"></span>
                                </div>
                            </template>
                            <div x-show="template.features.length > 3" class="template-feature-more">
                                +<span x-text="template.features.length - 3"></span> más
                            </div>
                        </div>

                        {{-- Template Stats --}}
                        <div class="template-stats">
                            <div class="template-stat">
                                <span class="template-stat-label">Campos:</span>
                                <span class="template-stat-value" x-text="template.fieldCount || 'N/A'"></span>
                            </div>
                            <div class="template-stat">
                                <span class="template-stat-label">Tiempo:</span>
                                <span class="template-stat-value" x-text="template.estimatedTime || 'N/A'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Selection Indicator --}}
                    <div 
                        x-show="selectedTemplate === template.id"
                        class="template-selection-indicator"
                    >
                        <x-solar-check-circle-outline class="w-6 h-6" />
                    </div>

                    {{-- Hidden Input --}}
                    <input 
                        type="radio" 
                        name="template" 
                        :value="template.id"
                        :checked="selectedTemplate === template.id"
                        class="sr-only"
                    >
                </div>
            </template>
        </div>
    </div>

    {{-- Plan Selection Section --}}
    <div class="plan-selection-section" x-show="selectedTemplate">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">2. Plan de Suscripción</h3>
            <div class="flex items-center space-x-4">
                <button 
                    type="button"
                    @click="togglePlanComparison()"
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                >
                    <x-solar-scale-outline class="w-4 h-4 mr-1" />
                    <span x-text="showPlanComparison ? 'Ocultar Comparación' : 'Comparar Planes'"></span>
                </button>
                
                <div class="billing-period-toggle">
                    <label class="text-sm text-gray-600 mr-2">Período:</label>
                    <select 
                        x-model="billingPeriod" 
                        @change="updatePlanPricing()"
                        class="text-sm border-gray-300 rounded-md"
                    >
                        <option value="monthly">Mensual</option>
                        <option value="quarterly">Trimestral</option>
                        <option value="biannual">Semestral</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Plan Comparison Table --}}
        <div x-show="showPlanComparison" x-transition class="plan-comparison mb-6">
            <div class="bg-gray-50 rounded-lg p-6">
                <h4 class="font-medium text-gray-900 mb-4">Comparación de Planes</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-3">Característica</th>
                                <template x-for="plan in getCompatiblePlans()" :key="plan.id">
                                    <th class="text-center py-2 px-3">
                                        <div x-text="plan.name"></div>
                                        <div class="text-xs text-gray-500" x-text="getPlanPriceDisplay(plan)"></div>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="feature in getPlanComparisonFeatures()" :key="feature.key">
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 px-3 font-medium" x-text="feature.name"></td>
                                    <template x-for="plan in getCompatiblePlans()" :key="plan.id">
                                        <td class="text-center py-2 px-3">
                                            <span x-text="getPlanFeatureValue(plan, feature.key)"></span>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Plan Cards --}}
        <div class="plan-cards-grid">
            <template x-for="plan in getCompatiblePlans()" :key="plan.id">
                <div 
                    class="plan-card"
                    :class="{
                        'selected': selectedPlan === plan.id,
                        'featured': plan.is_featured,
                        'incompatible': !isPlanCompatible(plan)
                    }"
                    @click="selectPlan(plan.id)"
                >
                    {{-- Featured Badge --}}
                    <div x-show="plan.is_featured" class="plan-badge">
                        <x-solar-crown-outline class="w-4 h-4 mr-1" />
                        Destacado
                    </div>

                    {{-- Plan Header --}}
                    <div class="plan-header">
                        <h4 class="plan-name" x-text="plan.name"></h4>
                        <div class="plan-pricing">
                            <div class="plan-price">
                                <span class="plan-currency">$</span>
                                <span class="plan-amount" x-text="getPlanPrice(plan)"></span>
                                <span class="plan-period" x-text="getPlanPeriodLabel()"></span>
                            </div>
                            <div x-show="getDiscount(plan) > 0" class="plan-discount">
                                <span x-text="getDiscount(plan) + '% descuento'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Plan Description --}}
                    <div class="plan-description">
                        <p x-text="plan.description"></p>
                    </div>

                    {{-- Plan Features --}}
                    <div class="plan-features">
                        <template x-for="feature in getPlanMainFeatures(plan)" :key="feature.key">
                            <div class="plan-feature">
                                <x-solar-check-circle-outline class="w-4 h-4 mr-2 text-green-600" />
                                <span x-text="feature.label"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Plan Limitations --}}
                    <div class="plan-limitations" x-show="getPlanLimitations(plan).length > 0">
                        <h5 class="plan-limitations-title">Limitaciones:</h5>
                        <template x-for="limitation in getPlanLimitations(plan)" :key="limitation">
                            <div class="plan-limitation">
                                <x-solar-info-circle-outline class="w-4 h-4 mr-2 text-amber-500" />
                                <span x-text="limitation"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Plan Actions --}}
                    <div class="plan-actions">
                        <button 
                            type="button"
                            @click="selectPlan(plan.id)"
                            class="plan-select-btn"
                            :class="{ 'selected': selectedPlan === plan.id }"
                            :disabled="!isPlanCompatible(plan)"
                        >
                            <template x-if="selectedPlan === plan.id">
                                <span class="flex items-center">
                                    <x-solar-check-circle-outline class="w-4 h-4 mr-1" />
                                    Seleccionado
                                </span>
                            </template>
                            <template x-if="selectedPlan !== plan.id">
                                <span>Seleccionar Plan</span>
                            </template>
                        </button>
                    </div>

                    {{-- Selection Indicator --}}
                    <div 
                        x-show="selectedPlan === plan.id"
                        class="plan-selection-indicator"
                    >
                        <x-solar-check-circle-outline class="w-6 h-6" />
                    </div>

                    {{-- Hidden Input --}}
                    <input 
                        type="radio" 
                        name="plan_id" 
                        :value="plan.id"
                        :checked="selectedPlan === plan.id"
                        class="sr-only"
                    >
                </div>
            </template>
        </div>

        {{-- Plan Compatibility Warning --}}
        <div x-show="selectedTemplate && getCompatiblePlans().length === 0" class="plan-compatibility-warning">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-center">
                    <x-solar-info-circle-outline class="w-5 h-5 text-amber-600 mr-2" />
                    <span class="text-amber-800">
                        No hay planes compatibles con la plantilla seleccionada. 
                        Por favor, selecciona una plantilla diferente.
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Selection Summary --}}
    <div x-show="selectedTemplate && selectedPlan" class="selection-summary">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="font-medium text-blue-900 mb-4">Resumen de Selección</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="summary-item">
                    <label class="summary-label">Plantilla:</label>
                    <span class="summary-value" x-text="getSelectedTemplate()?.name"></span>
                </div>
                <div class="summary-item">
                    <label class="summary-label">Plan:</label>
                    <span class="summary-value" x-text="getSelectedPlan()?.name"></span>
                </div>
                <div class="summary-item">
                    <label class="summary-label">Precio:</label>
                    <span class="summary-value" x-text="getSelectedPlanPriceDisplay()"></span>
                </div>
                <div class="summary-item">
                    <label class="summary-label">Período:</label>
                    <span class="summary-value" x-text="getPlanPeriodLabel()"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Errors --}}
    <div x-show="validationErrors.length > 0" class="validation-errors">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <x-solar-info-circle-outline class="w-5 h-5 text-red-600 mr-2 mt-0.5" />
                <div>
                    <h5 class="font-medium text-red-800 mb-2">Errores de validación:</h5>
                    <ul class="text-red-700 text-sm space-y-1">
                        <template x-for="error in validationErrors" :key="error">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Inputs for Form Submission --}}
    <input type="hidden" name="template" :value="selectedTemplate">
    <input type="hidden" name="billing_period" :value="billingPeriod">
</div>

@push('styles')
<style>
.template-plan-selection-step {
    @apply w-full;
}

.template-cards-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6;
}

.template-card {
    @apply relative border-2 border-gray-200 rounded-lg p-6 cursor-pointer transition-all duration-200 hover:border-blue-300 hover:shadow-md;
}

.template-card.selected {
    @apply border-blue-500 bg-blue-50 shadow-md;
}

.template-card.recommended {
    @apply ring-2 ring-yellow-400 ring-opacity-50;
}

.template-badge {
    @apply absolute top-3 right-3 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full flex items-center;
}

.template-icon {
    @apply text-center mb-4;
}

.template-content {
    @apply text-center;
}

.template-title {
    @apply text-lg font-semibold text-gray-900 mb-2;
}

.template-description {
    @apply text-gray-600 text-sm mb-4;
}

.template-features {
    @apply space-y-1 mb-4 text-left;
}

.template-feature {
    @apply flex items-center text-xs text-gray-600;
}

.template-feature-more {
    @apply text-xs text-blue-600 font-medium;
}

.template-stats {
    @apply flex justify-between text-xs text-gray-500 mb-4;
}

.template-selection-indicator {
    @apply absolute top-3 left-3 text-blue-600;
}

.plan-cards-grid {
    @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6;
}

.plan-card {
    @apply relative border-2 border-gray-200 rounded-lg p-6 cursor-pointer transition-all duration-200 hover:border-blue-300 hover:shadow-md;
}

.plan-card.selected {
    @apply border-blue-500 bg-blue-50 shadow-md;
}

.plan-card.featured {
    @apply ring-2 ring-purple-400 ring-opacity-50;
}

.plan-card.incompatible {
    @apply opacity-50 cursor-not-allowed;
}

.plan-badge {
    @apply absolute top-3 right-3 bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full flex items-center;
}

.plan-header {
    @apply text-center mb-4;
}

.plan-name {
    @apply text-lg font-semibold text-gray-900 mb-2;
}

.plan-pricing {
    @apply space-y-1;
}

.plan-price {
    @apply flex items-baseline justify-center space-x-1;
}

.plan-currency {
    @apply text-lg font-medium text-gray-600;
}

.plan-amount {
    @apply text-2xl font-bold text-gray-900;
}

.plan-period {
    @apply text-sm text-gray-600;
}

.plan-discount {
    @apply text-xs text-green-600 font-medium;
}

.plan-description {
    @apply text-center text-gray-600 text-sm mb-4;
}

.plan-features {
    @apply space-y-2 mb-4;
}

.plan-feature {
    @apply flex items-center text-sm text-gray-700;
}

.plan-limitations {
    @apply mb-4;
}

.plan-limitations-title {
    @apply text-sm font-medium text-gray-900 mb-2;
}

.plan-limitation {
    @apply flex items-center text-xs text-amber-700 mb-1;
}

.plan-actions {
    @apply mt-auto;
}

.plan-select-btn {
    @apply w-full px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200 flex items-center justify-center;
    @apply bg-blue-600 text-accent hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed;
}

.plan-select-btn.selected {
    @apply bg-green-600 hover:bg-green-700;
}

.plan-selection-indicator {
    @apply absolute top-3 left-3 text-blue-600;
}

.selection-summary {
    @apply mt-8;
}

.summary-item {
    @apply flex justify-between;
}

.summary-label {
    @apply font-medium text-blue-900;
}

.summary-value {
    @apply text-blue-800;
}

.validation-errors {
    @apply mt-6;
}

.billing-period-toggle select {
    @apply text-sm border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500;
}

.template-comparison,
.plan-comparison {
    @apply mb-6;
}

.plan-compatibility-warning {
    @apply mt-6;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/components/template-plan-selection-step.js') }}"></script>
@endpush