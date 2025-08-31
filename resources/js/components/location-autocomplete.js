/**
 * Location Autocomplete Component
 * 
 * Enhanced autocomplete for geographic location fields with cascade selection
 * Requirements: 2.4 - Geographic autocomplete for location fields
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('locationAutocomplete', (config = {}) => ({
        // Configuration
        fieldName: config.fieldName || 'location',
        type: config.type || 'all', // 'countries', 'departments', 'cities', 'all'
        placeholder: config.placeholder || 'Buscar ubicación...',
        country: config.country || null,
        department: config.department || null,
        required: config.required || false,
        
        // State
        query: config.initialValue || '',
        suggestions: [],
        showSuggestions: false,
        isLoading: false,
        selectedItem: null,
        
        // Debounce
        searchTimeout: null,
        
        // Cache
        cache: new Map(),

        init() {
            console.log('🌍 Location Autocomplete: Initialized', {
                fieldName: this.fieldName,
                type: this.type,
                country: this.country,
                department: this.department
            });
            
            // Set up watchers
            this.$watch('query', (value) => {
                this.onQueryChange(value);
            });
            
            // Close suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest(`[data-location-autocomplete="${this.fieldName}"]`)) {
                    this.closeSuggestions();
                }
            });
            
            // Initialize with existing value if provided
            if (this.query) {
                this.validateCurrentValue();
            }
        },

        /**
         * Handle query changes with debouncing
         */
        onQueryChange(value) {
            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            // Reset selection if query changes
            if (this.selectedItem && this.selectedItem.name !== value) {
                this.selectedItem = null;
            }
            
            // Don't search for very short queries
            if (!value || value.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }
            
            // Debounce search
            this.searchTimeout = setTimeout(() => {
                this.searchLocations(value);
            }, 300);
        },

        /**
         * Search locations via API
         */
        async searchLocations(query) {
            try {
                this.isLoading = true;
                
                // Check cache first
                const cacheKey = `${this.type}_${this.country}_${this.department}_${query}`;
                if (this.cache.has(cacheKey)) {
                    this.suggestions = this.cache.get(cacheKey);
                    this.showSuggestions = true;
                    this.isLoading = false;
                    return;
                }
                
                const params = new URLSearchParams({
                    query: query,
                    type: this.type
                });
                
                if (this.country) {
                    params.append('country', this.country);
                }
                
                if (this.department) {
                    params.append('department', this.department);
                }
                
                const response = await fetch(`/superlinkiu/api/stores/search-locations?${params}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.suggestions = result.data || [];
                    this.cache.set(cacheKey, this.suggestions);
                    this.showSuggestions = true;
                } else {
                    console.error('Location search failed:', result.message);
                    this.suggestions = [];
                    this.showSuggestions = false;
                }
                
            } catch (error) {
                console.error('Location search error:', error);
                this.suggestions = [];
                this.showSuggestions = false;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Select a suggestion
         */
        selectSuggestion(suggestion) {
            this.query = suggestion.name;
            this.selectedItem = suggestion;
            this.showSuggestions = false;
            
            // Emit selection event
            this.$dispatch('location-selected', {
                fieldName: this.fieldName,
                selection: suggestion,
                type: suggestion.type
            });
            
            console.log('🌍 Location selected:', suggestion);
        },

        /**
         * Handle input focus
         */
        onFocus() {
            if (this.suggestions.length > 0) {
                this.showSuggestions = true;
            } else if (this.query.length >= 2) {
                this.searchLocations(this.query);
            }
        },

        /**
         * Handle input blur
         */
        onBlur() {
            // Delay closing to allow for suggestion clicks
            setTimeout(() => {
                this.showSuggestions = false;
            }, 200);
            
            // Validate current value
            this.validateCurrentValue();
        },

        /**
         * Handle keyboard navigation
         */
        onKeydown(event) {
            if (!this.showSuggestions || this.suggestions.length === 0) {
                return;
            }
            
            const currentIndex = this.suggestions.findIndex(s => s.highlighted);
            
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.highlightSuggestion(currentIndex + 1);
                    break;
                    
                case 'ArrowUp':
                    event.preventDefault();
                    this.highlightSuggestion(currentIndex - 1);
                    break;
                    
                case 'Enter':
                    event.preventDefault();
                    const highlighted = this.suggestions.find(s => s.highlighted);
                    if (highlighted) {
                        this.selectSuggestion(highlighted);
                    }
                    break;
                    
                case 'Escape':
                    this.closeSuggestions();
                    break;
            }
        },

        /**
         * Highlight suggestion by index
         */
        highlightSuggestion(index) {
            // Remove previous highlight
            this.suggestions.forEach(s => s.highlighted = false);
            
            // Normalize index
            if (index < 0) index = this.suggestions.length - 1;
            if (index >= this.suggestions.length) index = 0;
            
            // Set new highlight
            if (this.suggestions[index]) {
                this.suggestions[index].highlighted = true;
            }
        },

        /**
         * Close suggestions dropdown
         */
        closeSuggestions() {
            this.showSuggestions = false;
            this.suggestions.forEach(s => s.highlighted = false);
        },

        /**
         * Clear the input
         */
        clear() {
            this.query = '';
            this.selectedItem = null;
            this.suggestions = [];
            this.showSuggestions = false;
            
            this.$dispatch('location-cleared', {
                fieldName: this.fieldName
            });
        },

        /**
         * Validate current value
         */
        async validateCurrentValue() {
            if (!this.query || this.query.length < 2) {
                return;
            }
            
            // If we have a selected item that matches the query, it's valid
            if (this.selectedItem && this.selectedItem.name === this.query) {
                return;
            }
            
            // Otherwise, try to find an exact match in suggestions
            const exactMatch = this.suggestions.find(s => 
                s.name.toLowerCase() === this.query.toLowerCase()
            );
            
            if (exactMatch) {
                this.selectedItem = exactMatch;
                this.$dispatch('location-selected', {
                    fieldName: this.fieldName,
                    selection: exactMatch,
                    type: exactMatch.type
                });
            } else if (this.required) {
                // If required and no exact match, show validation error
                this.$dispatch('location-validation-error', {
                    fieldName: this.fieldName,
                    message: 'Por favor selecciona una opción válida de la lista'
                });
            }
        },

        /**
         * Get suggestion display text
         */
        getSuggestionDisplay(suggestion) {
            switch (suggestion.type) {
                case 'country':
                    return `${suggestion.flag || '🌍'} ${suggestion.name}`;
                case 'department':
                    return `📍 ${suggestion.name}${suggestion.country ? `, ${suggestion.country}` : ''}`;
                case 'city':
                    return `🏙️ ${suggestion.name}${suggestion.department ? `, ${suggestion.department}` : ''}`;
                default:
                    return suggestion.display || suggestion.name;
            }
        },

        /**
         * Get suggestion type label
         */
        getSuggestionTypeLabel(suggestion) {
            const labels = {
                'country': 'País',
                'department': 'Departamento',
                'city': 'Ciudad'
            };
            return labels[suggestion.type] || suggestion.type;
        },

        /**
         * Check if field has valid selection
         */
        isValid() {
            if (!this.required) return true;
            return this.selectedItem !== null && this.selectedItem.name === this.query;
        },

        /**
         * Get current value for form submission
         */
        getValue() {
            return this.selectedItem ? {
                name: this.selectedItem.name,
                code: this.selectedItem.code,
                type: this.selectedItem.type,
                country: this.selectedItem.country,
                department: this.selectedItem.department
            } : null;
        },

        /**
         * Set value programmatically
         */
        setValue(value, item = null) {
            this.query = value;
            this.selectedItem = item;
            
            if (item) {
                this.$dispatch('location-selected', {
                    fieldName: this.fieldName,
                    selection: item,
                    type: item.type
                });
            }
        }
    }));
});

console.log('🌍 Location Autocomplete Component: Loaded');