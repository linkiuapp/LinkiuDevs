/**
 * Fiscal Information Step Component
 * 
 * Handles fiscal information collection for enterprise store templates
 * Requirements: 3.3, 3.4 - Conditional fiscal information with business document validation
 */

class FiscalInformationStep {
    constructor() {
        this.formData = {
            fiscal_document_type: '',
            fiscal_document_number: '',
            fiscal_country: 'Colombia',
            fiscal_department: '',
            fiscal_city: '',
            fiscal_address: '',
            tax_regime: '',
            economic_activity: '',
            tax_responsibility_declaration: false,
            fiscal_data_processing_consent: false,
            legal_representative_declaration: false
        };
        
        this.errors = {};
        this.isValidFiscalDocument = false;
        this.fiscalDocumentSuggestion = '';
        this.complianceStatus = '';
        
        // Country-specific tax regimes
        this.taxRegimes = {
            'Colombia': {
                'simplificado': 'Régimen Simplificado',
                'comun': 'Régimen Común',
                'gran_contribuyente': 'Gran Contribuyente',
                'no_responsable': 'No Responsable de IVA'
            },
            'México': {
                'general': 'Régimen General',
                'incorporacion_fiscal': 'Régimen de Incorporación Fiscal',
                'actividades_empresariales': 'Actividades Empresariales y Profesionales'
            },
            'Argentina': {
                'monotributo': 'Monotributo',
                'responsable_inscripto': 'Responsable Inscripto',
                'exento': 'Exento'
            },
            'Chile': {
                'primera_categoria': 'Primera Categoría',
                'segunda_categoria': 'Segunda Categoría',
                'pro_pyme': 'Régimen Pro PyME'
            },
            'default': {
                'general': 'Régimen General',
                'simplificado': 'Régimen Simplificado'
            }
        };
        
        // Document validation patterns by country
        this.documentPatterns = {
            'nit': {
                'Colombia': /^\d{9}-?\d$/,
                'pattern': /^\d{9,10}$/,
                'description': 'NIT debe tener 9 dígitos + dígito de verificación'
            },
            'rut': {
                'Chile': /^\d{8}-?[0-9kK]$/,
                'pattern': /^\d{8}[0-9kK]$/,
                'description': 'RUT debe tener 8 dígitos + dígito verificador'
            },
            'rfc': {
                'México': /^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/,
                'pattern': /^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/,
                'description': 'RFC debe tener 4 letras + 6 números + 3 caracteres'
            },
            'cedula': {
                'pattern': /^\d{6,10}$/,
                'description': 'Cédula debe tener entre 6 y 10 dígitos'
            }
        };
        
        this.init();
    }
    
    init() {
        console.log('🏛️ Fiscal Information Step: Initialized');
        this.loadSavedData();
        this.setupEventListeners();
        this.validateCompliance();
    }
    
    loadSavedData() {
        const savedData = localStorage.getItem('wizard_fiscal_data');
        if (savedData) {
            try {
                const parsed = JSON.parse(savedData);
                this.formData = { ...this.formData, ...parsed };
                console.log('📋 Loaded saved fiscal data');
            } catch (e) {
                console.warn('Failed to load saved fiscal data:', e);
            }
        }
    }
    
    setupEventListeners() {
        // Auto-save on data changes
        document.addEventListener('input', (e) => {
            if (e.target.closest('.fiscal-information-step')) {
                this.saveData();
            }
        });
        
        document.addEventListener('change', (e) => {
            if (e.target.closest('.fiscal-information-step')) {
                this.saveData();
                this.validateCompliance();
            }
        });
    }
    
    saveData() {
        localStorage.setItem('wizard_fiscal_data', JSON.stringify(this.formData));
    }
    
    shouldShowFiscalStep() {
        // Show fiscal step for enterprise template or when explicitly enabled
        const wizardState = JSON.parse(localStorage.getItem('wizard_state') || '{}');
        const template = wizardState.selectedTemplate;
        return template === 'enterprise' || template === 'complete';
    }
    
    validateField(fieldName, value) {
        // Clear previous error
        delete this.errors[fieldName];
        
        // Validate based on field type
        switch (fieldName) {
            case 'fiscal_document_type':
                if (!value) {
                    this.errors[fieldName] = 'El tipo de documento fiscal es requerido';
                }
                break;
                
            case 'fiscal_document_number':
                if (!value) {
                    this.errors[fieldName] = 'El número de documento fiscal es requerido';
                } else if (!this.isValidFiscalDocumentNumber(value)) {
                    this.errors[fieldName] = 'El formato del documento fiscal no es válido';
                }
                break;
                
            case 'fiscal_country':
                if (!value) {
                    this.errors[fieldName] = 'El país es requerido';
                }
                break;
                
            case 'fiscal_department':
                if (!value) {
                    this.errors[fieldName] = 'El departamento/estado es requerido';
                }
                break;
                
            case 'fiscal_city':
                if (!value) {
                    this.errors[fieldName] = 'La ciudad es requerida';
                }
                break;
                
            case 'fiscal_address':
                if (!value) {
                    this.errors[fieldName] = 'La dirección fiscal es requerida';
                } else if (value.length < 10) {
                    this.errors[fieldName] = 'La dirección debe ser más específica (mínimo 10 caracteres)';
                }
                break;
                
            case 'tax_regime':
                if (!value) {
                    this.errors[fieldName] = 'El régimen tributario es requerido';
                }
                break;
        }
        
        this.updateFieldValidationUI(fieldName);
    }
    
    validateFiscalDocument(value) {
        this.isValidFiscalDocument = false;
        this.fiscalDocumentSuggestion = '';
        
        if (!value || !this.formData.fiscal_document_type) return;
        
        const docType = this.formData.fiscal_document_type;
        const isValid = this.isValidFiscalDocumentNumber(value);
        
        this.isValidFiscalDocument = isValid;
        
        if (!isValid && value.length > 3) {
            this.fiscalDocumentSuggestion = this.getFiscalDocumentSuggestion(docType, value);
        }
        
        this.validateField('fiscal_document_number', value);
    }
    
    isValidFiscalDocumentNumber(value) {
        const docType = this.formData.fiscal_document_type;
        const country = this.formData.fiscal_country;
        
        if (!docType || !value) return false;
        
        const pattern = this.documentPatterns[docType];
        if (!pattern) return false;
        
        // Check country-specific pattern first
        if (pattern[country]) {
            return pattern[country].test(value);
        }
        
        // Fall back to general pattern
        return pattern.pattern.test(value.replace(/[^0-9A-Za-z]/g, ''));
    }
    
    getFiscalDocumentSuggestion(docType, value) {
        const cleanValue = value.replace(/[^0-9A-Za-z]/g, '');
        const pattern = this.documentPatterns[docType];
        
        if (!pattern) return '';
        
        switch (docType) {
            case 'nit':
                if (cleanValue.length === 9) {
                    return `¿Quizás quisiste decir: ${cleanValue}-X? (falta el dígito de verificación)`;
                }
                break;
                
            case 'rut':
                if (cleanValue.length === 8) {
                    return `¿Quizás quisiste decir: ${cleanValue}-X? (falta el dígito verificador)`;
                }
                break;
                
            case 'rfc':
                if (value.length < 13) {
                    return 'El RFC debe tener 13 caracteres (4 letras + 6 números + 3 caracteres)';
                }
                break;
        }
        
        return pattern.description || '';
    }
    
    onCountryChange(country) {
        this.formData.fiscal_country = country;
        this.validateField('fiscal_country', country);
        
        // Reset tax regime when country changes
        this.formData.tax_regime = '';
        
        // Re-validate fiscal document with new country context
        if (this.formData.fiscal_document_number) {
            this.validateFiscalDocument(this.formData.fiscal_document_number);
        }
    }
    
    validateCompliance() {
        const required = [
            this.formData.tax_responsibility_declaration,
            this.formData.fiscal_data_processing_consent
        ];
        
        // Add legal representative declaration for companies
        if (this.formData.fiscal_document_type === 'nit' || this.formData.fiscal_document_type === 'rut') {
            required.push(this.formData.legal_representative_declaration);
        }
        
        const allChecked = required.every(item => item === true);
        this.complianceStatus = allChecked ? 'complete' : 'incomplete';
        
        this.updateComplianceUI();
    }
    
    updateFieldValidationUI(fieldName) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        const errorElement = field.parentNode.querySelector('.error-message');
        const successElement = field.parentNode.querySelector('.success-message');
        
        // Remove existing classes
        field.classList.remove('border-red-500', 'border-green-500');
        
        if (this.errors[fieldName]) {
            field.classList.add('border-red-500');
            if (errorElement) {
                errorElement.textContent = this.errors[fieldName];
                errorElement.style.display = 'block';
            }
            if (successElement) {
                successElement.style.display = 'none';
            }
        } else if (this.formData[fieldName]) {
            field.classList.add('border-green-500');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
            if (successElement) {
                successElement.style.display = 'block';
            }
        }
    }
    
    updateComplianceUI() {
        const complianceIndicator = document.querySelector('.compliance-status');
        if (!complianceIndicator) return;
        
        complianceIndicator.className = 'compliance-status mt-4 p-3 rounded-lg';
        
        if (this.complianceStatus === 'complete') {
            complianceIndicator.classList.add('bg-green-100', 'text-green-800');
            complianceIndicator.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Todos los requisitos de cumplimiento han sido aceptados
                </div>
            `;
        } else {
            complianceIndicator.classList.add('bg-yellow-100', 'text-yellow-800');
            complianceIndicator.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    Por favor, acepte todos los términos requeridos para continuar
                </div>
            `;
        }
    }
    
    // Helper methods
    getFiscalDocumentLabel() {
        const labels = {
            'nit': 'Número de Identificación Tributaria (NIT)',
            'rut': 'Registro Único Tributario (RUT)',
            'rfc': 'Registro Federal de Contribuyentes (RFC)',
            'cedula': 'Número de Cédula'
        };
        return labels[this.formData.fiscal_document_type] || 'Número de Documento';
    }
    
    getFiscalDocumentPlaceholder() {
        const placeholders = {
            'nit': 'Ej: 900123456-7',
            'rut': 'Ej: 12345678-9',
            'rfc': 'Ej: ABC123456XYZ',
            'cedula': 'Ej: 12345678'
        };
        return placeholders[this.formData.fiscal_document_type] || 'Ingrese el número';
    }
    
    getDepartmentLabel() {
        const labels = {
            'Colombia': 'Departamento',
            'México': 'Estado',
            'Argentina': 'Provincia',
            'Chile': 'Región'
        };
        return labels[this.formData.fiscal_country] || 'Departamento/Estado';
    }
    
    getDepartmentPlaceholder() {
        const placeholders = {
            'Colombia': 'Ej: Cundinamarca, Antioquia',
            'México': 'Ej: Ciudad de México, Jalisco',
            'Argentina': 'Ej: Buenos Aires, Córdoba',
            'Chile': 'Ej: Metropolitana, Valparaíso'
        };
        return placeholders[this.formData.fiscal_country] || 'Ingrese el departamento';
    }
    
    getAvailableTaxRegimes() {
        return this.taxRegimes[this.formData.fiscal_country] || this.taxRegimes.default;
    }
    
    getTaxRegimeDescription() {
        const descriptions = {
            'simplificado': 'Para pequeñas empresas con ingresos limitados',
            'comun': 'Régimen general para la mayoría de empresas',
            'gran_contribuyente': 'Para empresas con altos volúmenes de facturación',
            'no_responsable': 'No responsable del impuesto sobre las ventas',
            'monotributo': 'Régimen simplificado para pequeños contribuyentes',
            'responsable_inscripto': 'Régimen general con todas las obligaciones fiscales',
            'primera_categoria': 'Para empresas y actividades comerciales',
            'pro_pyme': 'Régimen especial para pequeñas y medianas empresas'
        };
        return descriptions[this.formData.tax_regime] || '';
    }
    
    // Validation method for wizard
    isStepValid() {
        // Only validate if this step should be shown
        if (!this.shouldShowFiscalStep()) {
            return true;
        }
        
        // Validate all required fields
        const requiredFields = [
            'fiscal_document_type',
            'fiscal_document_number', 
            'fiscal_country',
            'fiscal_department',
            'fiscal_city',
            'fiscal_address',
            'tax_regime'
        ];
        
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!this.formData[field]) {
                this.validateField(field, '');
                isValid = false;
            }
        });
        
        // Validate compliance
        if (this.complianceStatus !== 'complete') {
            isValid = false;
        }
        
        // Validate fiscal document format
        if (!this.isValidFiscalDocument) {
            isValid = false;
        }
        
        return isValid && Object.keys(this.errors).length === 0;
    }
    
    // Get form data for submission
    getFormData() {
        return { ...this.formData };
    }
    
    // Reset form data
    reset() {
        this.formData = {
            fiscal_document_type: '',
            fiscal_document_number: '',
            fiscal_country: 'Colombia',
            fiscal_department: '',
            fiscal_city: '',
            fiscal_address: '',
            tax_regime: '',
            economic_activity: '',
            tax_responsibility_declaration: false,
            fiscal_data_processing_consent: false,
            legal_representative_declaration: false
        };
        this.errors = {};
        this.isValidFiscalDocument = false;
        this.fiscalDocumentSuggestion = '';
        this.complianceStatus = '';
        
        localStorage.removeItem('wizard_fiscal_data');
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FiscalInformationStep;
}