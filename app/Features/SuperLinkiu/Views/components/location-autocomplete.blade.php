{{--
    Location Autocomplete Component
    
    Enhanced autocomplete for geographic location fields with cascade selection
    
    Props:
    - fieldName: Unique identifier for the field
    - type: Type of location to search ('countries', 'departments', 'cities', 'all')
    - placeholder: Input placeholder text
    - label: Field label
    - country: Filter by country (for departments/cities)
    - department: Filter by department (for cities)
    - required: Whether the field is required
    - initialValue: Initial value to display
    - class: Additional CSS classes
    - error: Error message to display
    
    Requirements: 2.4 - Geographic autocomplete for location fields
--}}

@props([
    'fieldName' => 'location',
    'type' => 'all',
    'placeholder' => 'Buscar ubicación...',
    'label' => null,
    'country' => null,
    'department' => null,
    'required' => false,
    'initialValue' => '',
    'class' => '',
    'error' => null
])

<div 
    class="location-autocomplete-wrapper {{ $class }}"
    x-data="locationAutocomplete({
        fieldName: '{{ $fieldName }}',
        type: '{{ $type }}',
        placeholder: '{{ $placeholder }}',
        country: {{ $country ? "'" . $country . "'" : 'null' }},
        department: {{ $department ? "'" . $department . "'" : 'null' }},
        required: {{ $required ? 'true' : 'false' }},
        initialValue: '{{ $initialValue }}'
    })"
    data-location-autocomplete="{{ $fieldName }}"
>
    {{-- Label --}}
    @if($label)
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ $label }}
        @if($required)
        <span class="text-red-500">*</span>
        @endif
    </label>
    @endif

    {{-- Input Container --}}
    <div class="relative">
        {{-- Input Field --}}
        <input 
            type="text"
            name="{{ $fieldName }}"
            x-model="query"
            @focus="onFocus()"
            @blur="onBlur()"
            @keydown="onKeydown($event)"
            :placeholder="placeholder"
            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            :class="{ 
                'border-red-300 focus:border-red-500 focus:ring-red-500': {{ $error ? 'true' : 'false' }} || !isValid(),
                'border-green-300 focus:border-green-500 focus:ring-green-500': selectedItem && isValid()
            }"
            autocomplete="off"
        >

        {{-- Loading Spinner --}}
        <div 
            x-show="isLoading" 
            class="absolute right-3 top-1/2 transform -translate-y-1/2"
        >
            <div class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        {{-- Clear Button --}}
        <button 
            type="button"
            x-show="query && !isLoading"
            @click="clear()"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
        >
            <x-solar-close-circle-outline class="w-5 h-5" />
        </button>

        {{-- Success Icon --}}
        <div 
            x-show="selectedItem && isValid() && !isLoading"
            class="absolute right-3 top-1/2 transform -translate-y-1/2"
        >
            <x-solar-check-circle-outline class="w-5 h-5 text-green-600" />
        </div>

        {{-- Suggestions Dropdown --}}
        <div 
            x-show="showSuggestions && suggestions.length > 0"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute z-50 w-full mt-1 bg-accent border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        >
            <template x-for="(suggestion, index) in suggestions" :key="suggestion.code || index">
                <div 
                    @click="selectSuggestion(suggestion)"
                    class="px-4 py-3 cursor-pointer hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                    :class="{ 'bg-blue-50': suggestion.highlighted }"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900" x-text="getSuggestionDisplay(suggestion)"></div>
                            <div class="text-sm text-gray-500" x-text="getSuggestionTypeLabel(suggestion)"></div>
                        </div>
                        <div class="ml-2">
                            <x-solar-arrow-right-outline class="w-4 h-4 text-gray-400" />
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- No Results Message --}}
        <div 
            x-show="showSuggestions && suggestions.length === 0 && !isLoading && query.length >= 2"
            class="absolute z-50 w-full mt-1 bg-accent border border-gray-300 rounded-lg shadow-lg p-4 text-center text-gray-500"
        >
            <x-solar-magnifer-outline class="w-6 h-6 mx-auto mb-2 text-gray-400" />
            <p class="text-sm">No se encontraron resultados para "<span x-text="query"></span>"</p>
        </div>
    </div>

    {{-- Error Message --}}
    @if($error)
    <div class="mt-1 text-sm text-red-600 flex items-center">
        <x-solar-info-circle-outline class="w-4 h-4 mr-1" />
        {{ $error }}
    </div>
    @endif

    {{-- Dynamic Error Message --}}
    <div 
        x-show="!isValid() && query.length >= 2"
        class="mt-1 text-sm text-red-600 flex items-center"
    >
        <x-solar-info-circle-outline class="w-4 h-4 mr-1" />
        <span>Por favor selecciona una opción válida de la lista</span>
    </div>

    {{-- Help Text --}}
    <div class="mt-1 text-xs text-gray-500">
        Escribe al menos 2 caracteres para buscar
    </div>
</div>

@push('styles')
<style>
.location-autocomplete-wrapper {
    position: relative;
}

.location-autocomplete-wrapper .suggestions-dropdown {
    max-height: 240px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.location-autocomplete-wrapper .suggestions-dropdown::-webkit-scrollbar {
    width: 6px;
}

.location-autocomplete-wrapper .suggestions-dropdown::-webkit-scrollbar-track {
    background: #f7fafc;
}

.location-autocomplete-wrapper .suggestions-dropdown::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.location-autocomplete-wrapper .suggestions-dropdown::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

.location-autocomplete-wrapper .suggestion-item {
    transition: background-color 0.15s ease-in-out;
}

.location-autocomplete-wrapper .suggestion-item:hover {
    background-color: #f7fafc;
}

.location-autocomplete-wrapper .suggestion-item.highlighted {
    background-color: #ebf8ff;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/components/location-autocomplete.js') }}"></script>
@endpush