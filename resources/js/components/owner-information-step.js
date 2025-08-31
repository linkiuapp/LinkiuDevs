/**
 * Owner Information Step Component
 * 
 * Enhanced step for collecting owner details with real-time validation,
 * document type validation per country, location autocomplete, and password generation
 * Requirements: 2.1, 2.4, 4.2
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('ownerInformationStep', (config = {}) => ({
        // Configuration
        formData: config.formData || {
            owner_name: '',
            admin_email: '',
            owner_document_type: '',
            owner_document_number: '',
            owner_country: 'Colombia',
            owner_department: '',
            owner_city: '',
            admin_password: ''
        },
        template: config.template || null,
        errors: config.errors || [],
        
        // State
        validationErrors: [],
        fieldValidation: {},
        validatingFields: new Set(),
        showPassword: false,
        passwordChecks: {
            length: false,
            uppercase: false,
            lowercase: false,
            number: false,
            special: false
        },
        
        // Autocomplete state
        showCountrySuggestions: false,
        showDepartmentSuggestions: false,
        showCitySuggestions: false,
        countrySuggestions: [],
        departmentSuggestions: [],
        citySuggestions: [],
        
        // Debounce timers
        debounceTimers: {},

        init() {
            console.log('👤 Owner Information Step: Initialized');
            
            // Set up watchers
            this.$watch('formData.owner_country', (value) => {
                this.onCountryChange(value);
            });

            this.$watch('formData.owner_document_type', (value) => {
                this.onDocumentTypeChange(value);
            });

            this.$watch('formData.admin_password', (value) => {
                this.validatePassword(value);
            });

            // Initialize validation
            this.initializeValidation();
            
            // Load initial data
            this.loadCountries();
            
            // Set up location autocomplete event listeners
            this.setupLocationEventListeners();
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.autocomplete-wrapper')) {
                    this.closeAllDropdowns();
                }
            });
        },

        /**
         * Field Visibility Methods
         */
        shouldShowField(fieldName) {
            if (!this.template) return true;
            
            const fieldMapping = {
                'owner_document_type': ['complete', 'enterprise'],
                'owner_document_number': ['complete', 'enterprise'],
                'owner_country': ['complete', 'enterprise'],
                'owner_department': ['enterprise'],
                'owner_city': ['enterprise']
            };

            if (!fieldMapping[fieldName]) return true;
            
            return fieldMapping[fieldName].includes(this.template.id);
        },

        getFieldLabel(fieldName) {
            const labels = {
                'owner_name': this.template?.id === 'enterprise' ? 'Nombre del Representante Legal' : 'Nombre del Propietario'
            };
            
            return labels[fieldName] || fieldName;
        },

        /**
         * Validation Methods
         */
        initializeValidation() {
            // Initialize field validation states
            Object.keys(this.formData).forEach(field => {
                this.fieldValidation[field] = {
                    isValid: false,
                    message: null,
                    suggestion: null
                };
            });
        },

        async validateField(fieldName, value) {
            console.log(`👤 Validating field: ${fieldName}`, value);
            
            // Clear previous validation
            this.clearFieldError(fieldName);
            
            if (!value || value.trim() === '') {
                if (this.isFieldRequired(fieldName)) {
                    this.setFieldError(fieldName, 'Este campo es obligatorio');
                }
                return false;
            }

            // Field-specific validation
            switch (fieldName) {
                case 'owner_name':
                    return this.validateOwnerName(value);
                case 'admin_email':
                    return await this.validateEmail(value);
                case 'owner_document_number':
                    return this.validateDocumentNumber(value);
                case 'owner_country':
                    return this.validateCountry(value);
                case 'owner_department':
                    return this.validateDepartment(value);
                case 'owner_city':
                    return this.validateCity(value);
                case 'admin_password':
                    return this.validatePassword(value);
                default:
                    return true;
            }
        },

        validateOwnerName(name) {
            if (name.length < 2) {
                this.setFieldError('owner_name', 'El nombre debe tener al menos 2 caracteres');
                return false;
            }

            if (name.length > 255) {
                this.setFieldError('owner_name', 'El nombre no puede exceder 255 caracteres');
                return false;
            }

            // Check for valid characters (letters, spaces, some special chars)
            const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-'\.]+$/;
            if (!nameRegex.test(name)) {
                this.setFieldError('owner_name', 'El nombre contiene caracteres no válidos');
                return false;
            }

            this.setFieldValid('owner_name');
            return true;
        },

        async validateEmail(email) {
            // Basic format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                this.setFieldError('admin_email', 'Formato de email inválido');
                return false;
            }

            // Check for common typos and suggest corrections
            const suggestion = this.getEmailSuggestion(email);
            if (suggestion) {
                this.setFieldSuggestion('admin_email', `¿Quisiste decir ${suggestion}?`);
            }

            // Async uniqueness validation
            try {
                this.validatingFields.add('admin_email');
                
                const response = await fetch('/api/stores/validate-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({ email })
                });

                const result = await response.json();
                
                if (result.success && result.data) {
                    if (result.data.is_valid) {
                        this.setFieldValid('admin_email');
                        return true;
                    } else {
                        this.setFieldError('admin_email', result.data.message);
                        return false;
                    }
                } else {
                    this.setFieldError('admin_email', 'Error al validar el email');
                    return false;
                }
            } catch (error) {
                console.error('Email validation error:', error);
                this.setFieldError('admin_email', 'Error de conexión al validar email');
                return false;
            } finally {
                this.validatingFields.delete('admin_email');
            }
        },

        validateDocumentNumber(number) {
            const documentType = this.formData.owner_document_type;
            const country = this.formData.owner_country;

            if (!documentType) {
                this.setFieldError('owner_document_number', 'Selecciona primero el tipo de documento');
                return false;
            }

            // Country and document type specific validation
            if (country === 'Colombia') {
                return this.validateColombianDocument(documentType, number);
            }

            // Generic validation for other countries
            if (number.length < 5) {
                this.setFieldError('owner_document_number', 'El número de documento es muy corto');
                return false;
            }

            if (number.length > 20) {
                this.setFieldError('owner_document_number', 'El número de documento es muy largo');
                return false;
            }

            this.setFieldValid('owner_document_number');
            return true;
        },

        validateColombianDocument(type, number) {
            // Remove spaces and special characters
            const cleanNumber = number.replace(/[\s\-\.]/g, '');

            switch (type) {
                case 'cedula':
                    if (!/^\d{6,10}$/.test(cleanNumber)) {
                        this.setFieldError('owner_document_number', 'La cédula debe tener entre 6 y 10 dígitos');
                        return false;
                    }
                    break;
                    
                case 'nit':
                    if (!/^\d{9,10}$/.test(cleanNumber)) {
                        this.setFieldError('owner_document_number', 'El NIT debe tener 9 o 10 dígitos');
                        return false;
                    }
                    // TODO: Add NIT validation algorithm
                    break;
                    
                case 'pasaporte':
                    if (!/^[A-Z0-9]{6,12}$/.test(cleanNumber.toUpperCase())) {
                        this.setFieldError('owner_document_number', 'El pasaporte debe tener entre 6 y 12 caracteres alfanuméricos');
                        return false;
                    }
                    break;
            }

            this.setFieldValid('owner_document_number');
            return true;
        },

        validatePassword(password) {
            // Update password checks
            this.passwordChecks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password)
            };

            const passedChecks = Object.values(this.passwordChecks).filter(Boolean).length;
            
            if (passedChecks < 3) {
                this.setFieldError('admin_password', 'La contraseña debe cumplir al menos 3 de los requisitos');
                return false;
            }

            this.setFieldValid('admin_password');
            return true;
        },

        /**
         * Document Type Methods
         */
        getDocumentTypeOptions() {
            const country = this.formData.owner_country;
            
            if (country === 'Colombia') {
                return [
                    { value: 'cedula', label: 'Cédula de Ciudadanía' },
                    { value: 'nit', label: 'NIT' },
                    { value: 'pasaporte', label: 'Pasaporte' }
                ];
            }

            // Default options for other countries
            return [
                { value: 'cedula', label: 'Documento de Identidad' },
                { value: 'pasaporte', label: 'Pasaporte' }
            ];
        },

        getDocumentNumberLabel() {
            const type = this.formData.owner_document_type;
            const labels = {
                'cedula': 'Número de Cédula',
                'nit': 'Número de NIT',
                'pasaporte': 'Número de Pasaporte'
            };
            return labels[type] || 'Número de Documento';
        },

        getDocumentNumberPlaceholder() {
            const type = this.formData.owner_document_type;
            const placeholders = {
                'cedula': '12345678',
                'nit': '900123456-7',
                'pasaporte': 'AB123456'
            };
            return placeholders[type] || 'Ingresa el número';
        },

        getDocumentNumberMaxLength() {
            const type = this.formData.owner_document_type;
            const maxLengths = {
                'cedula': 10,
                'nit': 12,
                'pasaporte': 12
            };
            return maxLengths[type] || 20;
        },

        getDocumentNumberHint() {
            const type = this.formData.owner_document_type;
            const country = this.formData.owner_country;
            
            if (country === 'Colombia') {
                const hints = {
                    'cedula': 'Sin puntos ni espacios',
                    'nit': 'Incluye el dígito de verificación',
                    'pasaporte': 'Tal como aparece en el documento'
                };
                return hints[type];
            }
            
            return null;
        },

        onDocumentTypeChange(type) {
            // Clear document number when type changes
            this.formData.owner_document_number = '';
            this.clearFieldError('owner_document_number');
        },

        /**
         * Location Autocomplete Methods
         */
        loadCountries() {
            // Load common countries
            this.countrySuggestions = [
                { code: 'CO', name: 'Colombia', flag: '🇨🇴' },
                { code: 'US', name: 'Estados Unidos', flag: '🇺🇸' },
                { code: 'MX', name: 'México', flag: '🇲🇽' },
                { code: 'AR', name: 'Argentina', flag: '🇦🇷' },
                { code: 'PE', name: 'Perú', flag: '🇵🇪' },
                { code: 'CL', name: 'Chile', flag: '🇨🇱' },
                { code: 'EC', name: 'Ecuador', flag: '🇪🇨' },
                { code: 'VE', name: 'Venezuela', flag: '🇻🇪' },
                { code: 'ES', name: 'España', flag: '🇪🇸' }
            ];
        },

        searchCountries(query) {
            if (!query || query.length < 2) {
                this.showCountrySuggestions = false;
                return;
            }

            const filtered = this.countrySuggestions.filter(country =>
                country.name.toLowerCase().includes(query.toLowerCase())
            );

            this.countrySuggestions = filtered;
            this.showCountrySuggestions = true;
        },

        selectCountry(country) {
            this.formData.owner_country = country.name;
            this.showCountrySuggestions = false;
            this.validateField('owner_country', country.name);
            
            // Reset dependent fields
            this.formData.owner_department = '';
            this.formData.owner_city = '';
            
            // Load departments for selected country
            this.loadDepartments(country.code);
        },

        loadDepartments(countryCode) {
            // Load departments/states based on country
            if (countryCode === 'CO') {
                this.departmentSuggestions = [
                    { code: 'ANT', name: 'Antioquia' },
                    { code: 'ATL', name: 'Atlántico' },
                    { code: 'BOG', name: 'Bogotá D.C.' },
                    { code: 'BOL', name: 'Bolívar' },
                    { code: 'BOY', name: 'Boyacá' },
                    { code: 'CAL', name: 'Caldas' },
                    { code: 'CAQ', name: 'Caquetá' },
                    { code: 'CAS', name: 'Casanare' },
                    { code: 'CAU', name: 'Cauca' },
                    { code: 'CES', name: 'Cesar' },
                    { code: 'COR', name: 'Córdoba' },
                    { code: 'CUN', name: 'Cundinamarca' },
                    { code: 'HUI', name: 'Huila' },
                    { code: 'LAG', name: 'La Guajira' },
                    { code: 'MAG', name: 'Magdalena' },
                    { code: 'MET', name: 'Meta' },
                    { code: 'NAR', name: 'Nariño' },
                    { code: 'NSA', name: 'Norte de Santander' },
                    { code: 'QUI', name: 'Quindío' },
                    { code: 'RIS', name: 'Risaralda' },
                    { code: 'SAN', name: 'Santander' },
                    { code: 'SUC', name: 'Sucre' },
                    { code: 'TOL', name: 'Tolima' },
                    { code: 'VAC', name: 'Valle del Cauca' }
                ];
            } else {
                this.departmentSuggestions = [];
            }
        },

        searchDepartments(query) {
            if (!query || query.length < 2) {
                this.showDepartmentSuggestions = false;
                return;
            }

            const filtered = this.departmentSuggestions.filter(dept =>
                dept.name.toLowerCase().includes(query.toLowerCase())
            );

            this.departmentSuggestions = filtered;
            this.showDepartmentSuggestions = true;
        },

        selectDepartment(department) {
            this.formData.owner_department = department.name;
            this.showDepartmentSuggestions = false;
            this.validateField('owner_department', department.name);
            
            // Reset city
            this.formData.owner_city = '';
            
            // Load cities for selected department
            this.loadCities(department.code);
        },

        loadCities(departmentCode) {
            // Load cities based on department
            const citiesByDepartment = {
                'ANT': [
                    { code: 'MED', name: 'Medellín', department: 'Antioquia' },
                    { code: 'BEL', name: 'Bello', department: 'Antioquia' },
                    { code: 'ITA', name: 'Itagüí', department: 'Antioquia' },
                    { code: 'ENV', name: 'Envigado', department: 'Antioquia' }
                ],
                'BOG': [
                    { code: 'BOG', name: 'Bogotá', department: 'Bogotá D.C.' }
                ],
                'VAC': [
                    { code: 'CAL', name: 'Cali', department: 'Valle del Cauca' },
                    { code: 'PAL', name: 'Palmira', department: 'Valle del Cauca' },
                    { code: 'BUE', name: 'Buenaventura', department: 'Valle del Cauca' }
                ]
            };

            this.citySuggestions = citiesByDepartment[departmentCode] || [];
        },

        searchCities(query) {
            if (!query || query.length < 2) {
                this.showCitySuggestions = false;
                return;
            }

            const filtered = this.citySuggestions.filter(city =>
                city.name.toLowerCase().includes(query.toLowerCase())
            );

            this.citySuggestions = filtered;
            this.showCitySuggestions = true;
        },

        selectCity(city) {
            this.formData.owner_city = city.name;
            this.showCitySuggestions = false;
            this.validateField('owner_city', city.name);
        },

        closeAllDropdowns() {
            this.showCountrySuggestions = false;
            this.showDepartmentSuggestions = false;
            this.showCitySuggestions = false;
        },

        getDepartmentLabel() {
            const country = this.formData.owner_country;
            return country === 'Colombia' ? 'Departamento' : 'Estado/Provincia';
        },

        getDepartmentPlaceholder() {
            const country = this.formData.owner_country;
            return country === 'Colombia' ? 'Escribe para buscar departamento' : 'Escribe para buscar estado';
        },

        validateCountry(country) {
            if (country.length < 2) {
                this.setFieldError('owner_country', 'Selecciona un país válido');
                return false;
            }
            this.setFieldValid('owner_country');
            return true;
        },

        validateDepartment(department) {
            if (department.length < 2) {
                this.setFieldError('owner_department', 'Selecciona un departamento válido');
                return false;
            }
            this.setFieldValid('owner_department');
            return true;
        },

        validateCity(city) {
            if (city.length < 2) {
                this.setFieldError('owner_city', 'Selecciona una ciudad válida');
                return false;
            }
            this.setFieldValid('owner_city');
            return true;
        },

        onCountryChange(country) {
            console.log('👤 Country changed to:', country);
            // Reset dependent fields
            this.formData.owner_department = '';
            this.formData.owner_city = '';
            this.formData.owner_document_type = '';
            this.formData.owner_document_number = '';
            
            // Clear validation for dependent fields
            this.clearFieldError('owner_department');
            this.clearFieldError('owner_city');
            this.clearFieldError('owner_document_type');
            this.clearFieldError('owner_document_number');
        },

        /**
         * Password Methods
         */
        generatePassword() {
            const length = 12;
            const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            let password = '';
            
            // Ensure at least one of each required type
            password += this.getRandomChar('abcdefghijklmnopqrstuvwxyz'); // lowercase
            password += this.getRandomChar('ABCDEFGHIJKLMNOPQRSTUVWXYZ'); // uppercase
            password += this.getRandomChar('0123456789'); // number
            password += this.getRandomChar('!@#$%^&*'); // special
            
            // Fill the rest randomly
            for (let i = password.length; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            
            // Shuffle the password
            password = password.split('').sort(() => Math.random() - 0.5).join('');
            
            this.formData.admin_password = password;
            this.validatePassword(password);
        },

        getRandomChar(charset) {
            return charset.charAt(Math.floor(Math.random() * charset.length));
        },

        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },

        getPasswordStrengthPercentage() {
            const checks = Object.values(this.passwordChecks).filter(Boolean).length;
            return (checks / 5) * 100;
        },

        getPasswordStrengthClass() {
            const checks = Object.values(this.passwordChecks).filter(Boolean).length;
            if (checks <= 2) return 'weak';
            if (checks === 3) return 'fair';
            if (checks === 4) return 'good';
            return 'strong';
        },

        getPasswordStrengthLabel() {
            const checks = Object.values(this.passwordChecks).filter(Boolean).length;
            const labels = ['Muy débil', 'Débil', 'Regular', 'Buena', 'Fuerte', 'Muy fuerte'];
            return labels[checks] || 'Muy débil';
        },

        /**
         * Utility Methods
         */
        debounceValidation(fieldName, value) {
            // Clear existing timer
            if (this.debounceTimers[fieldName]) {
                clearTimeout(this.debounceTimers[fieldName]);
            }

            // Set new timer
            this.debounceTimers[fieldName] = setTimeout(() => {
                this.validateField(fieldName, value);
            }, 500);
        },

        getEmailSuggestion(email) {
            const commonDomains = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com'];
            const [localPart, domain] = email.split('@');
            
            if (!domain) return null;
            
            // Check for common typos
            const typos = {
                'gmial.com': 'gmail.com',
                'gmai.com': 'gmail.com',
                'hotmial.com': 'hotmail.com',
                'yahooo.com': 'yahoo.com',
                'outlok.com': 'outlook.com'
            };
            
            if (typos[domain]) {
                return `${localPart}@${typos[domain]}`;
            }
            
            return null;
        },

        isFieldRequired(fieldName) {
            const requiredFields = ['owner_name', 'admin_email', 'admin_password'];
            
            if (requiredFields.includes(fieldName)) return true;
            
            // Template-specific required fields
            if (this.template) {
                const templateRequiredFields = {
                    'complete': ['owner_document_type', 'owner_document_number', 'owner_country'],
                    'enterprise': ['owner_document_type', 'owner_document_number', 'owner_country', 'owner_department', 'owner_city']
                };
                
                const required = templateRequiredFields[this.template.id] || [];
                return required.includes(fieldName);
            }
            
            return false;
        },

        hasFieldError(fieldName) {
            return this.fieldValidation[fieldName]?.message !== null;
        },

        getFieldError(fieldName) {
            return this.fieldValidation[fieldName]?.message;
        },

        setFieldError(fieldName, message) {
            if (!this.fieldValidation[fieldName]) {
                this.fieldValidation[fieldName] = {};
            }
            this.fieldValidation[fieldName].isValid = false;
            this.fieldValidation[fieldName].message = message;
        },

        clearFieldError(fieldName) {
            if (this.fieldValidation[fieldName]) {
                this.fieldValidation[fieldName].message = null;
            }
        },

        setFieldValid(fieldName) {
            if (!this.fieldValidation[fieldName]) {
                this.fieldValidation[fieldName] = {};
            }
            this.fieldValidation[fieldName].isValid = true;
            this.fieldValidation[fieldName].message = null;
        },

        isFieldValid(fieldName) {
            return this.fieldValidation[fieldName]?.isValid === true;
        },

        isValidating(fieldName) {
            return this.validatingFields.has(fieldName);
        },

        getFieldSuggestion(fieldName) {
            return this.fieldValidation[fieldName]?.suggestion;
        },

        setFieldSuggestion(fieldName, suggestion) {
            if (!this.fieldValidation[fieldName]) {
                this.fieldValidation[fieldName] = {};
            }
            this.fieldValidation[fieldName].suggestion = suggestion;
        },

        /**
         * Step Validation
         */
        isStepValid() {
            const requiredFields = this.getRequiredFields();
            
            return requiredFields.every(field => {
                const value = this.formData[field];
                return value && value.trim() !== '' && this.isFieldValid(field);
            });
        },

        getRequiredFields() {
            const baseFields = ['owner_name', 'admin_email', 'admin_password'];
            
            if (!this.template) return baseFields;
            
            const templateFields = {
                'basic': [],
                'complete': ['owner_document_type', 'owner_document_number', 'owner_country'],
                'enterprise': ['owner_document_type', 'owner_document_number', 'owner_country', 'owner_department', 'owner_city']
            };
            
            return [...baseFields, ...(templateFields[this.template.id] || [])];
        },

        getLocationSummary() {
            const parts = [
                this.formData.owner_city,
                this.formData.owner_department,
                this.formData.owner_country
            ].filter(Boolean);
            
            return parts.join(', ');
        },

        /**
         * Location Autocomplete Event Handlers
         */
        setupLocationEventListeners() {
            // Listen for location selection events
            document.addEventListener('location-selected', (event) => {
                this.onLocationSelected(event.detail);
            });
            
            // Listen for location validation errors
            document.addEventListener('location-validation-error', (event) => {
                this.onLocationValidationError(event.detail);
            });
            
            // Listen for location cleared events
            document.addEventListener('location-cleared', (event) => {
                this.onLocationCleared(event.detail);
            });
        },

        onLocationSelected(detail) {
            const { fieldName, selection, type } = detail;
            
            console.log('👤 Location selected:', { fieldName, selection, type });
            
            // Update form data based on field
            switch (fieldName) {
                case 'owner_country':
                    this.formData.owner_country = selection.name;
                    // Reset dependent fields when country changes
                    this.formData.owner_department = '';
                    this.formData.owner_city = '';
                    this.clearFieldError('owner_country');
                    this.setFieldValid('owner_country');
                    break;
                    
                case 'owner_department':
                    this.formData.owner_department = selection.name;
                    // Reset city when department changes
                    this.formData.owner_city = '';
                    this.clearFieldError('owner_department');
                    this.setFieldValid('owner_department');
                    break;
                    
                case 'owner_city':
                    this.formData.owner_city = selection.name;
                    this.clearFieldError('owner_city');
                    this.setFieldValid('owner_city');
                    break;
            }
        },

        onLocationValidationError(detail) {
            const { fieldName, message } = detail;
            
            console.log('👤 Location validation error:', { fieldName, message });
            
            this.setFieldError(fieldName, message);
        },

        onLocationCleared(detail) {
            const { fieldName } = detail;
            
            console.log('👤 Location cleared:', { fieldName });
            
            // Clear form data and dependent fields
            switch (fieldName) {
                case 'owner_country':
                    this.formData.owner_country = '';
                    this.formData.owner_department = '';
                    this.formData.owner_city = '';
                    this.clearFieldError('owner_country');
                    break;
                    
                case 'owner_department':
                    this.formData.owner_department = '';
                    this.formData.owner_city = '';
                    this.clearFieldError('owner_department');
                    break;
                    
                case 'owner_city':
                    this.formData.owner_city = '';
                    this.clearFieldError('owner_city');
                    break;
            }
        },

        /**
         * Export Methods
         */
        getStepData() {
            return { ...this.formData };
        },

        exportValidation() {
            return {
                isValid: this.isStepValid(),
                errors: this.validationErrors,
                fieldValidation: this.fieldValidation
            };
        }
    }));
});

console.log('👤 Owner Information Step Component: Loaded');