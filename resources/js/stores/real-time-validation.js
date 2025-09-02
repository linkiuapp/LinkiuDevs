/**
 * 🚀 VALIDACIONES EN TIEMPO REAL - GESTIÓN DE TIENDAS
 * Sistema de validación asíncrona para mejorar UX
 */

class StoreRealTimeValidation {
    constructor() {
        this.debounceTimeout = null;
        this.validationCache = new Map();
        this.isValidating = false;
    }

    init() {
        console.log('🔄 REAL-TIME VALIDATION: Inicializando sistema');
        this.bindEmailValidation();
        this.bindSlugValidation();
        this.bindSlugSuggestions();
    }

    /**
     * 📧 VALIDACIÓN DE EMAIL EN TIEMPO REAL
     */
    bindEmailValidation() {
        const emailInputs = document.querySelectorAll('input[name="admin_email"], input[name="email"]');
        
        emailInputs.forEach(input => {
            input.addEventListener('blur', (e) => this.validateEmail(e.target));
            input.addEventListener('input', (e) => this.debounceValidation(() => this.validateEmail(e.target), 800));
        });
    }

    async validateEmail(input) {
        const email = input.value.trim();
        const storeId = document.querySelector('input[name="_store_id"]')?.value;
        
        if (!email || !this.isValidEmail(email)) {
            this.clearValidationFeedback(input);
            return;
        }

        // Check cache first
        const cacheKey = `email:${email}:${storeId || 'new'}`;
        if (this.validationCache.has(cacheKey)) {
            const cachedResult = this.validationCache.get(cacheKey);
            this.showEmailValidationFeedback(input, cachedResult);
            return;
        }

        this.showValidationLoading(input);

        try {
            const response = await fetch('/superlinkiu/api/stores/validate-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    email: email,
                    store_id: storeId
                })
            });

            const data = await response.json();
            
            if (data.success && data.data) {
                this.validationCache.set(cacheKey, data.data);
                this.showEmailValidationFeedback(input, data.data);
            } else {
                this.showValidationError(input, 'Error al validar email');
            }

        } catch (error) {
            console.error('Error validating email:', error);
            this.showValidationError(input, 'Error de conexión');
        }
    }

    /**
     * 🔗 VALIDACIÓN DE SLUG EN TIEMPO REAL
     */
    bindSlugValidation() {
        const slugInput = document.querySelector('input[name="slug"]');
        if (!slugInput) return;

        slugInput.addEventListener('blur', (e) => this.validateSlug(e.target));
        slugInput.addEventListener('input', (e) => {
            this.sanitizeSlugInput(e.target);
            this.debounceValidation(() => this.validateSlug(e.target), 600);
        });
    }

    async validateSlug(input) {
        const slug = input.value.trim();
        const storeId = document.querySelector('input[name="_store_id"]')?.value;
        
        if (!slug) {
            this.clearValidationFeedback(input);
            return;
        }

        // Check cache first
        const cacheKey = `slug:${slug}:${storeId || 'new'}`;
        if (this.validationCache.has(cacheKey)) {
            const cachedResult = this.validationCache.get(cacheKey);
            this.showSlugValidationFeedback(input, cachedResult);
            return;
        }

        this.showValidationLoading(input);

        try {
            const response = await fetch('/superlinkiu/api/stores/validate-slug', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    slug: slug,
                    store_id: storeId
                })
            });

            const data = await response.json();
            
            if (data.success && data.data) {
                this.validationCache.set(cacheKey, data.data);
                this.showSlugValidationFeedback(input, data.data);
            } else {
                this.showValidationError(input, 'Error al validar URL');
            }

        } catch (error) {
            console.error('Error validating slug:', error);
            this.showValidationError(input, 'Error de conexión');
        }
    }

    /**
     * 💡 SUGERENCIAS DE SLUG
     */
    bindSlugSuggestions() {
        const storeNameInput = document.querySelector('input[name="name"]');
        const slugInput = document.querySelector('input[name="slug"]');
        
        if (!storeNameInput || !slugInput) return;

        storeNameInput.addEventListener('input', (e) => {
            this.debounceValidation(() => this.generateSlugSuggestions(e.target.value, slugInput), 1000);
        });
    }

    async generateSlugSuggestions(storeName, slugInput) {
        if (!storeName.trim() || slugInput.value.trim()) {
            return; // No generar si ya hay slug o no hay nombre
        }

        try {
            const response = await fetch('/superlinkiu/api/stores/suggest-slug', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ slug: storeName })
            });

            const data = await response.json();
            
            if (data.success && data.data && data.data.suggestions.length > 0) {
                slugInput.value = data.data.suggestions[0];
                slugInput.dispatchEvent(new Event('input'));
            }

        } catch (error) {
            console.error('Error generating slug suggestions:', error);
        }
    }

    /**
     * 🎨 FEEDBACK VISUAL
     */
    showEmailValidationFeedback(input, result) {
        this.clearValidationFeedback(input);
        
        const wrapper = this.getOrCreateFeedbackWrapper(input);
        
        if (result.is_valid) {
            wrapper.innerHTML = `
                <div class="flex items-center gap-2 text-success-300 text-xs mt-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Email disponible
                </div>
            `;
            input.classList.remove('border-error-200');
            input.classList.add('border-success-200');
        } else {
            wrapper.innerHTML = `
                <div class="flex items-center gap-2 text-error-300 text-xs mt-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    ${result.message}
                </div>
            `;
            input.classList.remove('border-success-200');
            input.classList.add('border-error-200');
        }
    }

    showSlugValidationFeedback(input, result) {
        this.clearValidationFeedback(input);
        
        const wrapper = this.getOrCreateFeedbackWrapper(input);
        
        if (result.is_valid) {
            const previewUrl = `linkiu.bio/${result.sanitized_value || input.value}`;
            wrapper.innerHTML = `
                <div class="text-success-300 text-xs mt-1">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        URL disponible
                    </div>
                    <div class="text-black-300 mt-1">Preview: ${previewUrl}</div>
                </div>
            `;
            input.classList.remove('border-error-200');
            input.classList.add('border-success-200');
        } else {
            wrapper.innerHTML = `
                <div class="flex items-center gap-2 text-error-300 text-xs mt-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    ${result.message}
                </div>
            `;
            input.classList.remove('border-success-200');
            input.classList.add('border-error-200');
        }
    }

    showValidationLoading(input) {
        const wrapper = this.getOrCreateFeedbackWrapper(input);
        wrapper.innerHTML = `
            <div class="flex items-center gap-2 text-black-300 text-xs mt-1">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Validando...
            </div>
        `;
    }

    showValidationError(input, message) {
        const wrapper = this.getOrCreateFeedbackWrapper(input);
        wrapper.innerHTML = `
            <div class="flex items-center gap-2 text-error-300 text-xs mt-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                </svg>
                ${message}
            </div>
        `;
    }

    clearValidationFeedback(input) {
        const wrapper = input.parentNode.querySelector('.validation-feedback');
        if (wrapper) {
            wrapper.remove();
        }
        input.classList.remove('border-success-200', 'border-error-200');
    }

    getOrCreateFeedbackWrapper(input) {
        let wrapper = input.parentNode.querySelector('.validation-feedback');
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'validation-feedback';
            input.parentNode.appendChild(wrapper);
        }
        return wrapper;
    }

    /**
     * 🛠️ UTILIDADES
     */
    debounceValidation(func, delay) {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(func, delay);
    }

    sanitizeSlugInput(input) {
        const cursorPosition = input.selectionStart;
        const originalValue = input.value;
        
        // Sanitización básica en el frontend
        let sanitized = originalValue
            .toLowerCase()
            .replace(/[áàäâāã]/g, 'a')
            .replace(/[éèëêē]/g, 'e')
            .replace(/[íìïîī]/g, 'i')
            .replace(/[óòöôōõ]/g, 'o')
            .replace(/[úùüûū]/g, 'u')
            .replace(/[ñ]/g, 'n')
            .replace(/[ç]/g, 'c')
            .replace(/[^a-z0-9\-\s]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');

        if (originalValue !== sanitized) {
            input.value = sanitized;
            // Mantener posición del cursor
            const newPosition = Math.min(cursorPosition, sanitized.length);
            input.setSelectionRange(newPosition, newPosition);
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// 🚀 AUTO-INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('form[action*="stores"]')) {
        const validator = new StoreRealTimeValidation();
        validator.init();
        
        // Hacer disponible globalmente para debugging
        window.storeValidator = validator;
    }
});

// 📤 EXPORTAR PARA USO MODULAR
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StoreRealTimeValidation;
}
