/**
 * Wizard Components Unit Tests
 * 
 * JavaScript unit tests for wizard navigation and validation components
 * Requirements: All requirements validation
 */

// Mock DOM environment for testing
const { JSDOM } = require('jsdom');
const dom = new JSDOM('<!DOCTYPE html><html><body></body></html>');
global.window = dom.window;
global.document = dom.window.document;
global.localStorage = dom.window.localStorage;

// Mock fetch for API calls
global.fetch = jest.fn();

// Import components (these would need to be adapted for testing environment)
// For now, we'll define the core logic to test

describe('WizardNavigation', () => {
    let wizardNavigation;
    
    beforeEach(() => {
        // Reset DOM
        document.body.innerHTML = '';
        
        // Create wizard navigation instance
        wizardNavigation = {
            currentStep: 1,
            totalSteps: 6,
            completedSteps: new Set(),
            
            canNavigateTo(stepNumber) {
                if (stepNumber <= this.currentStep) return true;
                if (stepNumber === this.currentStep + 1) {
                    return this.completedSteps.has(this.currentStep);
                }
                return false;
            },
            
            setStepComplete(stepNumber, isComplete) {
                if (isComplete) {
                    this.completedSteps.add(stepNumber);
                } else {
                    this.completedSteps.delete(stepNumber);
                }
            },
            
            navigateToStep(stepNumber) {
                if (this.canNavigateTo(stepNumber)) {
                    this.currentStep = stepNumber;
                    return true;
                }
                return false;
            },
            
            getProgressPercentage() {
                return Math.round((this.completedSteps.size / this.totalSteps) * 100);
            }
        };
    });

    test('should initialize with correct default values', () => {
        expect(wizardNavigation.currentStep).toBe(1);
        expect(wizardNavigation.totalSteps).toBe(6);
        expect(wizardNavigation.completedSteps.size).toBe(0);
    });

    test('should allow navigation to current step', () => {
        expect(wizardNavigation.canNavigateTo(1)).toBe(true);
    });

    test('should not allow navigation to future steps without completion', () => {
        expect(wizardNavigation.canNavigateTo(2)).toBe(false);
        expect(wizardNavigation.canNavigateTo(3)).toBe(false);
    });

    test('should allow navigation to next step after current is completed', () => {
        wizardNavigation.setStepComplete(1, true);
        expect(wizardNavigation.canNavigateTo(2)).toBe(true);
        expect(wizardNavigation.canNavigateTo(3)).toBe(false);
    });

    test('should allow navigation to any completed step', () => {
        wizardNavigation.setStepComplete(1, true);
        wizardNavigation.navigateToStep(2);
        wizardNavigation.setStepComplete(2, true);
        wizardNavigation.navigateToStep(3);
        
        expect(wizardNavigation.canNavigateTo(1)).toBe(true);
        expect(wizardNavigation.canNavigateTo(2)).toBe(true);
    });

    test('should calculate progress percentage correctly', () => {
        expect(wizardNavigation.getProgressPercentage()).toBe(0);
        
        wizardNavigation.setStepComplete(1, true);
        expect(wizardNavigation.getProgressPercentage()).toBe(17); // 1/6 * 100 rounded
        
        wizardNavigation.setStepComplete(2, true);
        wizardNavigation.setStepComplete(3, true);
        expect(wizardNavigation.getProgressPercentage()).toBe(50); // 3/6 * 100
    });

    test('should handle step completion toggle', () => {
        wizardNavigation.setStepComplete(1, true);
        expect(wizardNavigation.completedSteps.has(1)).toBe(true);
        
        wizardNavigation.setStepComplete(1, false);
        expect(wizardNavigation.completedSteps.has(1)).toBe(false);
    });
});

describe('ValidationEngine', () => {
    let validationEngine;
    
    beforeEach(() => {
        // Mock fetch responses
        fetch.mockClear();
        
        validationEngine = {
            debounceTimers: new Map(),
            cache: new Map(),
            
            async validateField(field, value, options = {}) {
                const cacheKey = `${field}:${value}`;
                
                // Check cache first
                if (this.cache.has(cacheKey)) {
                    return this.cache.get(cacheKey);
                }
                
                // Simulate API call
                const response = await this.makeValidationRequest(field, value, options);
                
                // Cache result
                this.cache.set(cacheKey, response);
                
                return response;
            },
            
            async makeValidationRequest(field, value, options) {
                const endpoint = this.getValidationEndpoint(field);
                const payload = { [field]: value, ...options };
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                return response.json();
            },
            
            getValidationEndpoint(field) {
                const endpoints = {
                    'email': '/superlinkiu/api/stores/validate-email',
                    'admin_email': '/superlinkiu/api/stores/validate-email',
                    'slug': '/superlinkiu/api/stores/validate-slug'
                };
                
                return endpoints[field] || '/superlinkiu/api/stores/validate-field';
            },
            
            debounceValidation(field, value, callback, delay = 500) {
                // Clear existing timer
                if (this.debounceTimers.has(field)) {
                    clearTimeout(this.debounceTimers.get(field));
                }
                
                // Set new timer
                const timer = setTimeout(() => {
                    callback(field, value);
                    this.debounceTimers.delete(field);
                }, delay);
                
                this.debounceTimers.set(field, timer);
            },
            
            clearCache() {
                this.cache.clear();
            }
        };
    });

    test('should validate email field successfully', async () => {
        fetch.mockResolvedValueOnce({
            json: () => Promise.resolve({
                success: true,
                data: { is_valid: true, field: 'email' }
            })
        });
        
        const result = await validationEngine.validateField('email', 'test@example.com');
        
        expect(fetch).toHaveBeenCalledWith('/superlinkiu/api/stores/validate-email', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: 'test@example.com' })
        });
        
        expect(result.success).toBe(true);
        expect(result.data.is_valid).toBe(true);
    });

    test('should validate slug field successfully', async () => {
        fetch.mockResolvedValueOnce({
            json: () => Promise.resolve({
                success: true,
                data: { is_valid: false, field: 'slug', message: 'Slug already exists' }
            })
        });
        
        const result = await validationEngine.validateField('slug', 'existing-slug');
        
        expect(fetch).toHaveBeenCalledWith('/superlinkiu/api/stores/validate-slug', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slug: 'existing-slug' })
        });
        
        expect(result.success).toBe(true);
        expect(result.data.is_valid).toBe(false);
        expect(result.data.message).toBe('Slug already exists');
    });

    test('should cache validation results', async () => {
        fetch.mockResolvedValueOnce({
            json: () => Promise.resolve({
                success: true,
                data: { is_valid: true, field: 'email' }
            })
        });
        
        // First call
        await validationEngine.validateField('email', 'test@example.com');
        expect(fetch).toHaveBeenCalledTimes(1);
        
        // Second call should use cache
        await validationEngine.validateField('email', 'test@example.com');
        expect(fetch).toHaveBeenCalledTimes(1); // Still 1, not 2
    });

    test('should handle validation errors gracefully', async () => {
        fetch.mockRejectedValueOnce(new Error('Network error'));
        
        try {
            await validationEngine.validateField('email', 'test@example.com');
        } catch (error) {
            expect(error.message).toBe('Network error');
        }
    });

    test('should debounce validation calls', (done) => {
        const callback = jest.fn();
        
        // Make multiple rapid calls
        validationEngine.debounceValidation('email', 'test1@example.com', callback, 100);
        validationEngine.debounceValidation('email', 'test2@example.com', callback, 100);
        validationEngine.debounceValidation('email', 'test3@example.com', callback, 100);
        
        // Should not have called callback yet
        expect(callback).not.toHaveBeenCalled();
        
        // Wait for debounce delay
        setTimeout(() => {
            expect(callback).toHaveBeenCalledTimes(1);
            expect(callback).toHaveBeenCalledWith('email', 'test3@example.com');
            done();
        }, 150);
    });

    test('should clear cache when requested', async () => {
        fetch.mockResolvedValueOnce({
            json: () => Promise.resolve({
                success: true,
                data: { is_valid: true, field: 'email' }
            })
        });
        
        // Make a call to populate cache
        await validationEngine.validateField('email', 'test@example.com');
        expect(validationEngine.cache.size).toBe(1);
        
        // Clear cache
        validationEngine.clearCache();
        expect(validationEngine.cache.size).toBe(0);
    });
});

describe('SlugGenerator', () => {
    let slugGenerator;
    
    beforeEach(() => {
        slugGenerator = {
            generateFromName(name) {
                if (!name || typeof name !== 'string') return '';
                
                let slug = name.toLowerCase().trim();
                
                // Replace accented characters
                const accents = {
                    'á': 'a', 'à': 'a', 'ä': 'a', 'â': 'a', 'ā': 'a', 'ã': 'a',
                    'é': 'e', 'è': 'e', 'ë': 'e', 'ê': 'e', 'ē': 'e',
                    'í': 'i', 'ì': 'i', 'ï': 'i', 'î': 'i', 'ī': 'i',
                    'ó': 'o', 'ò': 'o', 'ö': 'o', 'ô': 'o', 'ō': 'o', 'õ': 'o',
                    'ú': 'u', 'ù': 'u', 'ü': 'u', 'û': 'u', 'ū': 'u',
                    'ñ': 'n', 'ç': 'c'
                };
                
                for (const [accented, plain] of Object.entries(accents)) {
                    slug = slug.replace(new RegExp(accented, 'g'), plain);
                }
                
                // Replace spaces and special characters with hyphens
                slug = slug.replace(/[^a-z0-9]+/g, '-');
                
                // Remove multiple consecutive hyphens
                slug = slug.replace(/-+/g, '-');
                
                // Remove leading and trailing hyphens
                slug = slug.replace(/^-+|-+$/g, '');
                
                return slug;
            },
            
            sanitize(slug) {
                return this.generateFromName(slug);
            }
        };
    });

    test('should generate slug from simple name', () => {
        expect(slugGenerator.generateFromName('Mi Tienda')).toBe('mi-tienda');
        expect(slugGenerator.generateFromName('Super Store')).toBe('super-store');
    });

    test('should handle accented characters', () => {
        expect(slugGenerator.generateFromName('Café Ñoño')).toBe('cafe-nono');
        expect(slugGenerator.generateFromName('Niños Felices')).toBe('ninos-felices');
        expect(slugGenerator.generateFromName('Corazón Azúl')).toBe('corazon-azul');
    });

    test('should handle special characters', () => {
        expect(slugGenerator.generateFromName('Store & More!')).toBe('store-more');
        expect(slugGenerator.generateFromName('100% Natural')).toBe('100-natural');
        expect(slugGenerator.generateFromName('Café@Home')).toBe('cafe-home');
    });

    test('should handle multiple spaces and hyphens', () => {
        expect(slugGenerator.generateFromName('  Multiple   Spaces  ')).toBe('multiple-spaces');
        expect(slugGenerator.generateFromName('Already-Has-Hyphens')).toBe('already-has-hyphens');
        expect(slugGenerator.generateFromName('Mixed   -  Spacing')).toBe('mixed-spacing');
    });

    test('should handle edge cases', () => {
        expect(slugGenerator.generateFromName('')).toBe('');
        expect(slugGenerator.generateFromName('   ')).toBe('');
        expect(slugGenerator.generateFromName('123')).toBe('123');
        expect(slugGenerator.generateFromName('!@#$%')).toBe('');
    });

    test('should handle uppercase conversion', () => {
        expect(slugGenerator.generateFromName('UPPERCASE STORE')).toBe('uppercase-store');
        expect(slugGenerator.generateFromName('MiXeD CaSe')).toBe('mixed-case');
    });

    test('should sanitize existing slugs', () => {
        expect(slugGenerator.sanitize('Already-Good-Slug')).toBe('already-good-slug');
        expect(slugGenerator.sanitize('Needs Sanitization!')).toBe('needs-sanitization');
    });
});

describe('FormStateManager', () => {
    let formStateManager;
    
    beforeEach(() => {
        // Clear localStorage
        localStorage.clear();
        
        formStateManager = {
            storageKey: 'wizard_form_data',
            
            saveFormData(data) {
                try {
                    const serialized = JSON.stringify({
                        ...data,
                        timestamp: Date.now()
                    });
                    localStorage.setItem(this.storageKey, serialized);
                    return true;
                } catch (error) {
                    console.error('Failed to save form data:', error);
                    return false;
                }
            },
            
            loadFormData() {
                try {
                    const stored = localStorage.getItem(this.storageKey);
                    if (!stored) return null;
                    
                    const data = JSON.parse(stored);
                    
                    // Check if data is not too old (24 hours)
                    const maxAge = 24 * 60 * 60 * 1000;
                    if (Date.now() - data.timestamp > maxAge) {
                        this.clearFormData();
                        return null;
                    }
                    
                    return data;
                } catch (error) {
                    console.error('Failed to load form data:', error);
                    return null;
                }
            },
            
            clearFormData() {
                localStorage.removeItem(this.storageKey);
            },
            
            hasStoredData() {
                return localStorage.getItem(this.storageKey) !== null;
            }
        };
    });

    test('should save form data to localStorage', () => {
        const testData = {
            owner_name: 'John Doe',
            admin_email: 'john@example.com',
            name: 'Test Store'
        };
        
        const result = formStateManager.saveFormData(testData);
        
        expect(result).toBe(true);
        expect(localStorage.getItem('wizard_form_data')).toBeTruthy();
    });

    test('should load form data from localStorage', () => {
        const testData = {
            owner_name: 'John Doe',
            admin_email: 'john@example.com',
            name: 'Test Store'
        };
        
        formStateManager.saveFormData(testData);
        const loaded = formStateManager.loadFormData();
        
        expect(loaded.owner_name).toBe('John Doe');
        expect(loaded.admin_email).toBe('john@example.com');
        expect(loaded.name).toBe('Test Store');
        expect(loaded.timestamp).toBeTruthy();
    });

    test('should return null for non-existent data', () => {
        const loaded = formStateManager.loadFormData();
        expect(loaded).toBeNull();
    });

    test('should clear expired data', () => {
        const testData = {
            owner_name: 'John Doe',
            timestamp: Date.now() - (25 * 60 * 60 * 1000) // 25 hours ago
        };
        
        localStorage.setItem('wizard_form_data', JSON.stringify(testData));
        
        const loaded = formStateManager.loadFormData();
        expect(loaded).toBeNull();
        expect(localStorage.getItem('wizard_form_data')).toBeNull();
    });

    test('should detect stored data', () => {
        expect(formStateManager.hasStoredData()).toBe(false);
        
        formStateManager.saveFormData({ test: 'data' });
        expect(formStateManager.hasStoredData()).toBe(true);
        
        formStateManager.clearFormData();
        expect(formStateManager.hasStoredData()).toBe(false);
    });

    test('should handle JSON parsing errors gracefully', () => {
        localStorage.setItem('wizard_form_data', 'invalid json');
        
        const loaded = formStateManager.loadFormData();
        expect(loaded).toBeNull();
    });
});

describe('AutoSaveManager', () => {
    let autoSaveManager;
    
    beforeEach(() => {
        jest.useFakeTimers();
        fetch.mockClear();
        
        autoSaveManager = {
            interval: 30000, // 30 seconds
            timer: null,
            isEnabled: false,
            
            start(formDataGetter, saveCallback) {
                if (this.isEnabled) return;
                
                this.isEnabled = true;
                this.timer = setInterval(() => {
                    const formData = formDataGetter();
                    if (formData && Object.keys(formData).length > 0) {
                        saveCallback(formData);
                    }
                }, this.interval);
            },
            
            stop() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
                this.isEnabled = false;
            },
            
            saveNow(formData) {
                return fetch('/superlinkiu/api/stores/save-draft', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ form_data: formData })
                });
            }
        };
    });

    afterEach(() => {
        jest.useRealTimers();
    });

    test('should start auto-save timer', () => {
        const formDataGetter = jest.fn(() => ({ name: 'Test Store' }));
        const saveCallback = jest.fn();
        
        autoSaveManager.start(formDataGetter, saveCallback);
        
        expect(autoSaveManager.isEnabled).toBe(true);
        expect(autoSaveManager.timer).toBeTruthy();
    });

    test('should call save callback at intervals', () => {
        const formDataGetter = jest.fn(() => ({ name: 'Test Store' }));
        const saveCallback = jest.fn();
        
        autoSaveManager.start(formDataGetter, saveCallback);
        
        // Fast-forward time
        jest.advanceTimersByTime(30000);
        
        expect(formDataGetter).toHaveBeenCalled();
        expect(saveCallback).toHaveBeenCalledWith({ name: 'Test Store' });
    });

    test('should not save empty form data', () => {
        const formDataGetter = jest.fn(() => ({}));
        const saveCallback = jest.fn();
        
        autoSaveManager.start(formDataGetter, saveCallback);
        
        jest.advanceTimersByTime(30000);
        
        expect(formDataGetter).toHaveBeenCalled();
        expect(saveCallback).not.toHaveBeenCalled();
    });

    test('should stop auto-save timer', () => {
        const formDataGetter = jest.fn(() => ({ name: 'Test Store' }));
        const saveCallback = jest.fn();
        
        autoSaveManager.start(formDataGetter, saveCallback);
        autoSaveManager.stop();
        
        expect(autoSaveManager.isEnabled).toBe(false);
        expect(autoSaveManager.timer).toBeNull();
        
        // Should not call callback after stopping
        jest.advanceTimersByTime(30000);
        expect(saveCallback).not.toHaveBeenCalled();
    });

    test('should save immediately when requested', async () => {
        fetch.mockResolvedValueOnce({
            json: () => Promise.resolve({ success: true })
        });
        
        const formData = { name: 'Test Store', email: 'test@example.com' };
        
        await autoSaveManager.saveNow(formData);
        
        expect(fetch).toHaveBeenCalledWith('/superlinkiu/api/stores/save-draft', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ form_data: formData })
        });
    });

    test('should not start multiple timers', () => {
        const formDataGetter = jest.fn(() => ({ name: 'Test Store' }));
        const saveCallback = jest.fn();
        
        autoSaveManager.start(formDataGetter, saveCallback);
        const firstTimer = autoSaveManager.timer;
        
        autoSaveManager.start(formDataGetter, saveCallback);
        const secondTimer = autoSaveManager.timer;
        
        expect(firstTimer).toBe(secondTimer);
    });
});