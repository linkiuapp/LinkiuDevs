{{-- Validation Engine Component --}}
@props([
    'fields' => [],
    'options' => []
])

<div class="validation-engine-container" data-validation-fields="{{ json_encode($fields) }}" data-validation-options="{{ json_encode($options) }}">
    {{ $slot }}
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/validation.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/components/validation-engine.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize validation engine for this component
    const container = document.querySelector('.validation-engine-container');
    if (container) {
        const fields = JSON.parse(container.dataset.validationFields || '[]');
        const options = JSON.parse(container.dataset.validationOptions || '{}');
        
        // Create validation engine instance
        const validationEngine = new ValidationEngine(options);
        
        // Set up validation for each field
        fields.forEach(field => {
            if (typeof field === 'string') {
                validationEngine.setupFieldValidation(field);
            } else if (typeof field === 'object') {
                validationEngine.setupFieldValidation(field.name, field.options || {});
            }
        });
        
        // Store instance for external access
        container.validationEngine = validationEngine;
    }
});
</script>
@endpush

{{-- Validation status indicators --}}
<style>
.validation-engine-container .form-field {
    @apply relative;
}

.validation-engine-container .form-field input,
.validation-engine-container .form-field select,
.validation-engine-container .form-field textarea {
    @apply pr-10;
}

.validation-engine-container .validation-indicator {
    @apply absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none;
}

.validation-engine-container .validation-loading .validation-indicator {
    @apply text-yellow-500;
}

.validation-engine-container .validation-success .validation-indicator {
    @apply text-green-500;
}

.validation-engine-container .validation-error .validation-indicator {
    @apply text-red-500;
}
</style>