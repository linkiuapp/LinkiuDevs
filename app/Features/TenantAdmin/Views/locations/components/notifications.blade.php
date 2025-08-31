{{-- ================================================================ --}}
{{-- SISTEMA DE NOTIFICACIONES --}}
{{-- ================================================================ --}}

<div x-show="showNotification" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform translate-y-2"
     class="fixed top-4 right-4 z-50 max-w-sm">
    
    <div :class="{
        'bg-success-200 text-accent-50': notificationType === 'success',
        'bg-error-200 text-accent-50': notificationType === 'error',
        'bg-warning-200 text-black-400': notificationType === 'warning',
        'bg-info-200 text-accent-50': notificationType === 'info'
    }" class="rounded-lg shadow-lg p-4 flex items-center gap-3">
        
        <template x-if="notificationType === 'success'">
            <x-solar-check-circle-bold class="w-5 h-5 flex-shrink-0" />
        </template>
        <template x-if="notificationType === 'error'">
            <x-solar-close-circle-bold class="w-5 h-5 flex-shrink-0" />
        </template>
        <template x-if="notificationType === 'warning'">
            <x-solar-danger-triangle-bold class="w-5 h-5 flex-shrink-0" />
        </template>
        <template x-if="notificationType === 'info'">
            <x-solar-info-circle-bold class="w-5 h-5 flex-shrink-0" />
        </template>
        
        <span x-text="notificationMessage" class="text-sm font-semibold"></span>
        
        <button @click="showNotification = false" class="ml-auto">
            <x-solar-close-circle-bold class="w-4 h-4" />
        </button>
    </div>
</div>
