{{--
    WizardContainer Blade Component
    
    Complete wizard container that integrates navigation, steps, and state management
    Provides a unified interface for creating multi-step wizards
    
    Props:
    - wizardId: Unique identifier for the wizard
    - steps: Array of step configurations
    - initialStep: Starting step index (default: 0)
    - enableRouting: Enable URL routing (default: true)
    - enablePersistence: Enable localStorage persistence (default: true)
    - allowBackNavigation: Allow backward navigation (default: true)
    - showProgressBar: Show progress bar (default: true)
    - showBreadcrumbs: Show breadcrumb navigation (default: true)
    - compact: Use compact mode (default: false)
    
    Requirements: 1.1, 1.2, 5.1
--}}

@props([
    'wizardId' => 'wizard',
    'steps' => [],
    'initialStep' => 0,
    'enableRouting' => true,
    'enablePersistence' => true,
    'allowBackNavigation' => true,
    'showProgressBar' => true,
    'showBreadcrumbs' => true,
    'compact' => false,
    'class' => ''
])

<x-superlinkiu::wizard-state-manager
    :wizardId="$wizardId"
    :enableRouting="$enableRouting"
    :enablePersistence="$enablePersistence"
    class="{{ $class }}"
>
    <x-superlinkiu::wizard-navigation
        :steps="$steps"
        :initialStep="$initialStep"
        :allowBackNavigation="$allowBackNavigation"
        :showProgressBar="$showProgressBar"
        :showBreadcrumbs="$showBreadcrumbs"
        :compact="$compact"
    >
        {{ $slot }}
        
        @isset($actions)
        <x-slot name="actions">
            {{ $actions }}
        </x-slot>
        @endisset
    </x-superlinkiu::wizard-navigation>
</x-superlinkiu::wizard-state-manager>