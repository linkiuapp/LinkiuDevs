{{--
    Template Selection Component
    
    Interactive template selection interface with visual cards, comparison, and preview
    
    Props:
    - templates: Array of available templates
    - selectedTemplate: Currently selected template ID
    - showComparison: Show template comparison interface (default: false)
    - showPreview: Show template preview functionality (default: true)
    - class: Additional CSS classes
    
    Requirements: 3.1, 3.2
--}}

@props([
    'templates' => [],
    'selectedTemplate' => null,
    'showComparison' => false,
    'showPreview' => true,
    'class' => ''
])

<div 
    x-data="templateSelection({
        templates: {{ json_encode($templates) }},
        selectedTemplate: '{{ $selectedTemplate }}',
        showComparison: {{ $showComparison ? 'true' : 'false' }},
        showPreview: {{ $showPreview ? 'true' : 'false' }}
    })"
    class="template-selection {{ $class }}"
    x-cloak
>
    {{-- Template Selection Header --}}
    <div class="template-selection-header">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Selecciona una Plantilla</h3>
        <p class="text-gray-600 mb-6">Elige el tipo de tienda que mejor se adapte a tus necesidades</p>
        
        {{-- Comparison Toggle --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <button 
                    type="button"
                    @click="toggleComparison()"
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                >
                    <x-solar-scale-outline class="w-4 h-4 mr-1" />
                    <span x-text="showComparison ? 'Ocultar Comparación' : 'Comparar Plantillas'"></span>
                </button>
            </div>
            
            <div class="text-sm text-gray-500">
                <span x-text="templates.length"></span> plantillas disponibles
            </div>
        </div>
    </div>

    {{-- Template Comparison Table --}}
    <div x-show="showComparison" x-transition class="template-comparison mb-8">
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
                        <template x-for="feature in getComparisonFeatures()" :key="feature.key">
                            <tr class="border-b border-gray-100">
                                <td class="py-2 px-3 font-medium" x-text="feature.name"></td>
                                <template x-for="template in templates" :key="template.id">
                                    <td class="text-center py-2 px-3">
                                        <span 
                                            x-show="hasFeature(template, feature.key)"
                                            class="text-green-600"
                                        >
                                            <x-solar-check-circle-outline class="w-4 h-4 inline" />
                                        </span>
                                        <span 
                                            x-show="!hasFeature(template, feature.key)"
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

    {{-- Template Cards Grid --}}
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

                {{-- Template Actions --}}
                <div class="template-actions">
                    <button 
                        type="button"
                        @click.stop="previewTemplate(template.id)"
                        x-show="showPreview"
                        class="template-action-btn template-preview-btn"
                    >
                        <x-solar-eye-outline class="w-4 h-4 mr-1" />
                        Vista Previa
                    </button>
                    
                    <button 
                        type="button"
                        @click="selectTemplate(template.id)"
                        class="template-action-btn template-select-btn"
                        :class="{ 'selected': selectedTemplate === template.id }"
                    >
                        <template x-if="selectedTemplate === template.id">
                            <span class="flex items-center">
                                <x-solar-check-circle-outline class="w-4 h-4 mr-1" />
                                Seleccionada
                            </span>
                        </template>
                        <template x-if="selectedTemplate !== template.id">
                            <span>Seleccionar</span>
                        </template>
                    </button>
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

    {{-- Template Preview Modal --}}
    <div 
        x-show="previewModal.isOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="template-preview-modal"
        @click.self="closePreview()"
    >
        <div 
            class="template-preview-content"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
        >
            {{-- Preview Header --}}
            <div class="template-preview-header">
                <h3 class="text-lg font-semibold" x-text="previewModal.template?.name"></h3>
                <button 
                    type="button"
                    @click="closePreview()"
                    class="template-preview-close"
                >
                    <x-solar-close-circle-outline class="w-6 h-6" />
                </button>
            </div>

            {{-- Preview Body --}}
            <div class="template-preview-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Template Info --}}
                    <div>
                        <h4 class="font-medium mb-3">Información de la Plantilla</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium">Descripción:</span>
                                <span x-text="previewModal.template?.description"></span>
                            </div>
                            <div>
                                <span class="font-medium">Campos requeridos:</span>
                                <span x-text="previewModal.template?.fieldCount"></span>
                            </div>
                            <div>
                                <span class="font-medium">Tiempo estimado:</span>
                                <span x-text="previewModal.template?.estimatedTime"></span>
                            </div>
                        </div>

                        <h5 class="font-medium mt-4 mb-2">Características incluidas:</h5>
                        <ul class="space-y-1 text-sm">
                            <template x-for="feature in previewModal.template?.features || []" :key="feature">
                                <li class="flex items-center">
                                    <x-solar-check-circle-outline class="w-3 h-3 mr-2 text-green-600" />
                                    <span x-text="feature"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Form Preview --}}
                    <div>
                        <h4 class="font-medium mb-3">Vista Previa del Formulario</h4>
                        <div class="template-form-preview">
                            <template x-for="step in previewModal.template?.steps || []" :key="step.id">
                                <div class="preview-step">
                                    <div class="preview-step-header">
                                        <span class="preview-step-number" x-text="step.order"></span>
                                        <span class="preview-step-title" x-text="step.title"></span>
                                        <span x-show="step.isOptional" class="preview-step-optional">Opcional</span>
                                    </div>
                                    <div class="preview-step-fields">
                                        <template x-for="field in step.fields.slice(0, 3)" :key="field.name">
                                            <div class="preview-field">
                                                <span x-text="field.label"></span>
                                                <span x-show="field.required" class="text-red-500">*</span>
                                            </div>
                                        </template>
                                        <div x-show="step.fields.length > 3" class="preview-field-more">
                                            +<span x-text="step.fields.length - 3"></span> campos más
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Preview Actions --}}
            <div class="template-preview-actions">
                <button 
                    type="button"
                    @click="closePreview()"
                    class="btn btn-secondary"
                >
                    Cerrar
                </button>
                <button 
                    type="button"
                    @click="selectTemplateFromPreview()"
                    class="btn btn-primary"
                >
                    <x-solar-check-circle-outline class="w-4 h-4 mr-2" />
                    Seleccionar esta Plantilla
                </button>
            </div>
        </div>
    </div>

    {{-- Validation Error --}}
    <div x-show="validationError" class="template-validation-error">
        <x-solar-info-circle-outline class="w-5 h-5 mr-2" />
        <span x-text="validationError"></span>
    </div>
</div>

@push('styles')
<style>
.template-selection {
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

.template-icon-wrapper {
    @apply mx-auto;
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

.template-stat-label {
    @apply font-medium;
}

.template-actions {
    @apply flex space-x-2;
}

.template-action-btn {
    @apply flex-1 px-3 py-2 text-sm rounded-md transition-colors duration-200 flex items-center justify-center;
}

.template-preview-btn {
    @apply bg-gray-100 text-gray-700 hover:bg-gray-200;
}

.template-select-btn {
    @apply bg-blue-600 text-accent hover:bg-blue-700;
}

.template-select-btn.selected {
    @apply bg-green-600 hover:bg-green-700;
}

.template-selection-indicator {
    @apply absolute top-3 left-3 text-blue-600;
}

.template-preview-modal {
    @apply fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50;
}

.template-preview-content {
    @apply bg-accent rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-screen overflow-y-auto;
}

.template-preview-header {
    @apply flex items-center justify-between p-6 border-b border-gray-200;
}

.template-preview-close {
    @apply text-gray-400 hover:text-gray-600;
}

.template-preview-body {
    @apply p-6;
}

.template-form-preview {
    @apply space-y-3 bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto;
}

.preview-step {
    @apply bg-accent rounded-md p-3;
}

.preview-step-header {
    @apply flex items-center space-x-2 mb-2;
}

.preview-step-number {
    @apply w-6 h-6 bg-blue-100 text-blue-600 rounded-full text-xs flex items-center justify-center font-medium;
}

.preview-step-title {
    @apply font-medium text-sm;
}

.preview-step-optional {
    @apply text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded;
}

.preview-step-fields {
    @apply space-y-1;
}

.preview-field {
    @apply text-xs text-gray-600 flex items-center;
}

.preview-field-more {
    @apply text-xs text-blue-600;
}

.template-preview-actions {
    @apply flex justify-end space-x-3 p-6 border-t border-gray-200;
}

.template-validation-error {
    @apply mt-4 p-3 bg-red-50 border border-red-200 rounded-md text-red-700 text-sm flex items-center;
}

.template-comparison {
    @apply mb-6;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/components/template-selection.js') }}"></script>
@endpush