/**
 * SuggestionSystem - Advanced suggestion engine for form fields
 */
class SuggestionSystem {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/superlinkiu/api/stores',
            debounceDelay: 300,
            maxSuggestions: 5,
            ...options
        };
        
        this.activeSuggestions = new Map();
        this.suggestionContainers = new Map();
        this.debounceTimers = new Map();
        
        this.init();
    }

    init() {
        // Initialize CSRF token
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Set up global click handler to close suggestions
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.suggestion-container')) {
                this.closeAllSuggestions();
            }
        });
    }

    /**
     * Set up slug suggestions for a field
     */
    setupSlugSuggestions(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        field.addEventListener('input', (event) => {
            const value = event.target.value.trim();
            
            if (value.length >= 2) {
                this.debounceRequest(fieldName, () => {
                    this.getSlugSuggestions(fieldName, value);
                });
            } else {
                this.closeSuggestions(fieldName);
            }
        });

        field.addEventListener('focus', () => {
            if (field.value.trim().length >= 2) {
                this.getSlugSuggestions(fieldName, field.value.trim());
            }
        });
    }

    /**
     * Set up email domain suggestions for a field
     */
    setupEmailSuggestions(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        field.addEventListener('blur', (event) => {
            const value = event.target.value.trim();
            
            if (value && this.isValidEmailFormat(value)) {
                this.getEmailDomainSuggestions(fieldName, value);
            }
        });
    }

    /**
     * Set up location autocomplete for a field
     */
    setupLocationAutocomplete(fieldName, type = 'all') {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        field.addEventListener('input', (event) => {
            const value = event.target.value.trim();
            
            if (value.length >= 2) {
                this.debounceRequest(fieldName, () => {
                    this.getLocationSuggestions(fieldName, value, type);
                });
            } else {
                this.closeSuggestions(fieldName);
            }
        });

        field.addEventListener('focus', () => {
            if (field.value.trim().length >= 2) {
                this.getLocationSuggestions(fieldName, field.value.trim(), type);
            }
        });
    }

    /**
     * Get slug suggestions from API
     */
    async getSlugSuggestions(fieldName, value) {
        try {
            const response = await this.makeRequest('/suggest-slug', { slug: value });
            
            if (response.success && response.data.suggestions.length > 0) {
                this.displaySuggestions(fieldName, response.data.suggestions, 'slug');
            } else {
                this.closeSuggestions(fieldName);
            }
        } catch (error) {
            console.error('Error getting slug suggestions:', error);
            this.closeSuggestions(fieldName);
        }
    }

    /**
     * Get email domain suggestions from API
     */
    async getEmailDomainSuggestions(fieldName, value) {
        try {
            const response = await this.makeRequest('/suggest-email-domain', { email: value });
            
            if (response.success && response.data.suggestions.length > 0) {
                this.displaySuggestions(fieldName, response.data.suggestions, 'email', {
                    title: '¿Quisiste decir?',
                    type: 'correction'
                });
            }
        } catch (error) {
            console.error('Error getting email suggestions:', error);
        }
    }

    /**
     * Get location suggestions from API
     */
    async getLocationSuggestions(fieldName, value, type = 'all') {
        try {
            const response = await this.makeRequest('/search-locations', { 
                query: value, 
                type: type 
            }, 'GET');
            
            if (response.success && response.data.results.length > 0) {
                const suggestions = response.data.results.map(location => ({
                    value: location.name,
                    display: location.full_name,
                    data: location
                }));
                
                this.displaySuggestions(fieldName, suggestions, 'location');
            } else {
                this.closeSuggestions(fieldName);
            }
        } catch (error) {
            console.error('Error getting location suggestions:', error);
            this.closeSuggestions(fieldName);
        }
    }

    /**
     * Display suggestions for a field
     */
    displaySuggestions(fieldName, suggestions, type, options = {}) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // Close existing suggestions
        this.closeSuggestions(fieldName);

        // Create suggestion container
        const container = document.createElement('div');
        container.className = 'suggestion-container absolute z-50 w-full mt-1 bg-accent border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto';
        
        // Add title if provided
        if (options.title) {
            const title = document.createElement('div');
            title.className = 'px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-50 border-b';
            title.textContent = options.title;
            container.appendChild(title);
        }

        // Create suggestion items
        suggestions.forEach((suggestion, index) => {
            const item = document.createElement('div');
            item.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0 transition-colors';
            
            if (type === 'location' && suggestion.display) {
                item.innerHTML = `
                    <div class="font-medium text-gray-900">${this.escapeHtml(suggestion.value)}</div>
                    <div class="text-xs text-gray-500">${this.escapeHtml(suggestion.display)}</div>
                `;
            } else {
                item.textContent = typeof suggestion === 'string' ? suggestion : suggestion.value;
            }
            
            // Add click handler
            item.addEventListener('click', () => {
                const value = typeof suggestion === 'string' ? suggestion : suggestion.value;
                field.value = value;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                this.closeSuggestions(fieldName);
                field.focus();
            });

            // Add keyboard navigation
            item.addEventListener('mouseenter', () => {
                this.highlightSuggestion(container, index);
            });

            container.appendChild(item);
        });

        // Position container
        const fieldRect = field.getBoundingClientRect();
        const fieldParent = field.parentNode;
        
        // Make sure parent has relative positioning
        if (getComputedStyle(fieldParent).position === 'static') {
            fieldParent.style.position = 'relative';
        }
        
        fieldParent.appendChild(container);
        
        // Store reference
        this.suggestionContainers.set(fieldName, container);
        this.activeSuggestions.set(fieldName, suggestions);

        // Set up keyboard navigation
        this.setupKeyboardNavigation(fieldName, field, container);
    }

    /**
     * Set up keyboard navigation for suggestions
     */
    setupKeyboardNavigation(fieldName, field, container) {
        let selectedIndex = -1;
        const items = container.querySelectorAll('.cursor-pointer');

        const keyHandler = (event) => {
            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                    this.highlightSuggestion(container, selectedIndex);
                    break;
                    
                case 'ArrowUp':
                    event.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    this.highlightSuggestion(container, selectedIndex);
                    break;
                    
                case 'Enter':
                    if (selectedIndex >= 0) {
                        event.preventDefault();
                        items[selectedIndex].click();
                    }
                    break;
                    
                case 'Escape':
                    this.closeSuggestions(fieldName);
                    break;
            }
        };

        field.addEventListener('keydown', keyHandler);
        
        // Store handler for cleanup
        container.keyHandler = keyHandler;
    }

    /**
     * Highlight a suggestion item
     */
    highlightSuggestion(container, index) {
        const items = container.querySelectorAll('.cursor-pointer');
        
        // Remove previous highlight
        items.forEach(item => {
            item.classList.remove('bg-blue-100');
        });
        
        // Add highlight to selected item
        if (index >= 0 && index < items.length) {
            items[index].classList.add('bg-blue-100');
        }
    }

    /**
     * Close suggestions for a specific field
     */
    closeSuggestions(fieldName) {
        const container = this.suggestionContainers.get(fieldName);
        if (container) {
            // Remove keyboard handler
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field && container.keyHandler) {
                field.removeEventListener('keydown', container.keyHandler);
            }
            
            container.remove();
            this.suggestionContainers.delete(fieldName);
            this.activeSuggestions.delete(fieldName);
        }
    }

    /**
     * Close all active suggestions
     */
    closeAllSuggestions() {
        for (const fieldName of this.suggestionContainers.keys()) {
            this.closeSuggestions(fieldName);
        }
    }

    /**
     * Debounce API requests
     */
    debounceRequest(fieldName, callback) {
        // Clear existing timer
        if (this.debounceTimers.has(fieldName)) {
            clearTimeout(this.debounceTimers.get(fieldName));
        }

        // Set new timer
        const timer = setTimeout(callback, this.options.debounceDelay);
        this.debounceTimers.set(fieldName, timer);
    }

    /**
     * Make HTTP request
     */
    async makeRequest(endpoint, payload, method = 'POST') {
        const url = `${this.options.baseUrl}${endpoint}`;
        
        const config = {
            method: method,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (method === 'GET') {
            const params = new URLSearchParams(payload);
            const fullUrl = `${url}?${params}`;
            const response = await fetch(fullUrl, config);
            return await response.json();
        } else {
            config.headers['Content-Type'] = 'application/json';
            config.body = JSON.stringify(payload);
            const response = await fetch(url, config);
            return await response.json();
        }
    }

    /**
     * Validate email format
     */
    isValidEmailFormat(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Set up suggestions for store creation form
     */
    setupStoreCreationSuggestions() {
        // Slug suggestions
        this.setupSlugSuggestions('slug');
        
        // Email domain suggestions
        this.setupEmailSuggestions('admin_email');
        this.setupEmailSuggestions('email');
        
        // Location autocomplete
        this.setupLocationAutocomplete('owner_country', 'country');
        this.setupLocationAutocomplete('owner_department', 'department');
        this.setupLocationAutocomplete('owner_city', 'city');
        this.setupLocationAutocomplete('country', 'country');
        this.setupLocationAutocomplete('department', 'department');
        this.setupLocationAutocomplete('city', 'city');
    }

    /**
     * Clean up resources
     */
    destroy() {
        // Clear all timers
        this.debounceTimers.forEach(timer => clearTimeout(timer));
        this.debounceTimers.clear();
        
        // Close all suggestions
        this.closeAllSuggestions();
        
        // Clear references
        this.activeSuggestions.clear();
        this.suggestionContainers.clear();
    }
}

// Export for use in other modules
window.SuggestionSystem = SuggestionSystem;

// Auto-initialize if DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (!window.suggestionSystem) {
        window.suggestionSystem = new SuggestionSystem();
    }
});