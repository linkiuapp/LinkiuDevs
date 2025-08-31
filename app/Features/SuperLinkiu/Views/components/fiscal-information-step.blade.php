{{-- Fiscal Information Step Component --}}
{{-- Requirements: 3.3, 3.4 - Conditional fiscal information for enterprise templates --}}

@props([
    'template' => null,
    'countries' => ['Colombia', 'México', 'Argentina', 'Chile', 'Perú'],
    'taxRegimes' => [
        'Colombia' => [
            'simplificado' => 'Régimen Simplificado',
            'comun' => 'Régimen Común',
            'gran_contribuyente' => 'Gran Contribuyente',
            'no_responsable' => 'No Responsable de IVA'
        ],
        'México' => [
            'general' => 'Régimen General',
            'incorporacion_fiscal' => 'Régimen de Incorporación Fiscal',
            'actividades_empresariales' => 'Actividades Empresariales y Profesionales'
        ],
        'default' => [
            'general' => 'Régimen General',
            'simplificado' => 'Régimen Simplificado'
        ]
    ]
])

<div class="fiscal-information-step" 
     x-data="fiscalInformationStep()" 
     x-show="shouldShowFiscalStep"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0">
    
    {{-- Step Header --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Información Fiscal</h3>
        <p class="text-sm text-gray-600">
            Complete la información fiscal requerida para su empresa. Esta información es necesaria para la facturación y cumplimiento tributario.
        </p>
    </div>

    {{-- Fiscal Document Information --}}
    <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <h4 class="text-md font-medium text-gray-900 mb-4">Documentos Fiscales</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Fiscal Document Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Documento Fiscal <span class="text-red-500">*</span>
                </label>
                <select name="fiscal_document_type" 
                        x-model="formData.fiscal_document_type"
                        @change="validateField('fiscal_document_type', $event.target.value)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        :class="{ 'border-red-500': errors.fiscal_document_type }"
                        required>
                    <option value="">Seleccionar tipo de documento</option>
                    <option value="nit">NIT (Número de Identificación Tributaria)</option>
                    <option value="rut">RUT (Registro Único Tributario)</option>
                    <option value="rfc">RFC (Registro Federal de Contribuyentes)</option>
                    <option value="cedula">Cédula de Ciudadanía</option>
                </select>
                <div x-show="errors.fiscal_document_type" class="text-red-500 text-xs mt-1" x-text="errors.fiscal_document_type"></div>
                <div x-show="!errors.fiscal_document_type && formData.fiscal_document_type" class="text-green-500 text-xs mt-1">
                    ✓ Tipo de documento válido
                </div>
            </div>

            {{-- Fiscal Document Number --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span x-text="getFiscalDocumentLabel()"></span> <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="fiscal_document_number"
                       x-model="formData.fiscal_document_number"
                       @input="validateFiscalDocument($event.target.value)"
                       @blur="validateField('fiscal_document_number', $event.target.value)"
                       :placeholder="getFiscalDocumentPlaceholder()"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                       :class="{ 'border-red-500': errors.fiscal_document_number, 'border-green-500': !errors.fiscal_document_number && formData.fiscal_document_number && isValidFiscalDocument }"
                       required>
                <div x-show="errors.fiscal_document_number" class="text-red-500 text-xs mt-1" x-text="errors.fiscal_document_number"></div>
                <div x-show="!errors.fiscal_document_number && formData.fiscal_document_number && isValidFiscalDocument" class="text-green-500 text-xs mt-1">
                    ✓ Documento fiscal válido
                </div>
                <div x-show="fiscalDocumentSuggestion" class="text-blue-500 text-xs mt-1">
                    💡 <span x-text="fiscalDocumentSuggestion"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Business Address Information --}}
    <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <h4 class="text-md font-medium text-gray-900 mb-4">Dirección Fiscal</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Country --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    País <span class="text-red-500">*</span>
                </label>
                <select name="fiscal_country" 
                        x-model="formData.fiscal_country"
                        @change="onCountryChange($event.target.value)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        :class="{ 'border-red-500': errors.fiscal_country }"
                        required>
                    <option value="">Seleccionar país</option>
                    @foreach($countries as $country)
                    <option value="{{ $country }}">{{ $country }}</option>
                    @endforeach
                </select>
                <div x-show="errors.fiscal_country" class="text-red-500 text-xs mt-1" x-text="errors.fiscal_country"></div>
            </div>

            {{-- Department/State --}}
            <div>
                <x-superlinkiu::location-autocomplete
                    fieldName="fiscal_department"
                    type="departments"
                    :label="getDepartmentLabel() + ' *'"
                    :placeholder="getDepartmentPlaceholder()"
                    :country="formData.fiscal_country"
                    :required="true"
                    :error="errors.fiscal_department ?? null"
                />
            </div>

            {{-- City --}}
            <div>
                <x-superlinkiu::location-autocomplete
                    fieldName="fiscal_city"
                    type="cities"
                    label="Ciudad *"
                    placeholder="Ej: Bogotá, Medellín, Cali"
                    :country="formData.fiscal_country"
                    :department="formData.fiscal_department"
                    :required="true"
                    :error="errors.fiscal_city ?? null"
                />
            </div>
        </div>

        {{-- Full Address --}}
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Dirección Completa <span class="text-red-500">*</span>
            </label>
            <textarea name="fiscal_address" 
                      x-model="formData.fiscal_address"
                      @input="validateField('fiscal_address', $event.target.value)"
                      rows="3" 
                      placeholder="Ingrese la dirección fiscal completa (calle, número, barrio, etc.)"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      :class="{ 'border-red-500': errors.fiscal_address }"
                      required></textarea>
            <div x-show="errors.fiscal_address" class="text-red-500 text-xs mt-1" x-text="errors.fiscal_address"></div>
            <p class="text-xs text-gray-500 mt-1">Esta dirección aparecerá en las facturas y documentos fiscales</p>
        </div>
    </div>

    {{-- Tax Information --}}
    <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <h4 class="text-md font-medium text-gray-900 mb-4">Información Tributaria</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Tax Regime --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Régimen Tributario <span class="text-red-500">*</span>
                </label>
                <select name="tax_regime" 
                        x-model="formData.tax_regime"
                        @change="validateField('tax_regime', $event.target.value)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        :class="{ 'border-red-500': errors.tax_regime }"
                        required>
                    <option value="">Seleccionar régimen</option>
                    <template x-for="(label, value) in getAvailableTaxRegimes()" :key="value">
                        <option :value="value" x-text="label"></option>
                    </template>
                </select>
                <div x-show="errors.tax_regime" class="text-red-500 text-xs mt-1" x-text="errors.tax_regime"></div>
                <div x-show="getTaxRegimeDescription()" class="text-blue-600 text-xs mt-1" x-text="getTaxRegimeDescription()"></div>
            </div>

            {{-- Economic Activity --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Actividad Económica Principal
                </label>
                <input type="text" 
                       name="economic_activity"
                       x-model="formData.economic_activity"
                       @input="validateField('economic_activity', $event.target.value)"
                       placeholder="Ej: Comercio al por menor, Servicios de consultoría"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                       :class="{ 'border-red-500': errors.economic_activity }">
                <div x-show="errors.economic_activity" class="text-red-500 text-xs mt-1" x-text="errors.economic_activity"></div>
                <p class="text-xs text-gray-500 mt-1">Opcional: Describe la actividad principal de tu empresa</p>
            </div>
        </div>
    </div>

    {{-- Compliance and Legal Requirements --}}
    <div class="bg-blue-50 rounded-lg p-6">
        <h4 class="text-md font-medium text-gray-900 mb-4 flex items-center">
            <x-solar-shield-check-outline class="w-5 h-5 mr-2 text-blue-600" />
            Cumplimiento y Requisitos Legales
        </h4>
        
        <div class="space-y-4">
            {{-- Tax Responsibility Declaration --}}
            <div class="flex items-start">
                <input type="checkbox" 
                       name="tax_responsibility_declaration"
                       x-model="formData.tax_responsibility_declaration"
                       @change="validateCompliance()"
                       id="tax_responsibility_declaration"
                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                       required>
                <label for="tax_responsibility_declaration" class="ml-3 text-sm text-gray-700">
                    <span class="font-medium">Declaración de Responsabilidad Fiscal</span> <span class="text-red-500">*</span>
                    <br>
                    <span class="text-gray-600">
                        Declaro que la información fiscal proporcionada es veraz y me comprometo a mantenerla actualizada. 
                        Entiendo que esta información será utilizada para la generación de facturas y documentos tributarios.
                    </span>
                </label>
            </div>

            {{-- Data Processing Consent --}}
            <div class="flex items-start">
                <input type="checkbox" 
                       name="fiscal_data_processing_consent"
                       x-model="formData.fiscal_data_processing_consent"
                       @change="validateCompliance()"
                       id="fiscal_data_processing_consent"
                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                       required>
                <label for="fiscal_data_processing_consent" class="ml-3 text-sm text-gray-700">
                    <span class="font-medium">Consentimiento para Tratamiento de Datos Fiscales</span> <span class="text-red-500">*</span>
                    <br>
                    <span class="text-gray-600">
                        Autorizo el tratamiento de mis datos fiscales para efectos de facturación, cumplimiento tributario 
                        y reportes ante las autoridades competentes según la normativa vigente.
                    </span>
                </label>
            </div>

            {{-- Legal Representative Declaration (for companies) --}}
            <div x-show="formData.fiscal_document_type === 'nit' || formData.fiscal_document_type === 'rut'" class="flex items-start">
                <input type="checkbox" 
                       name="legal_representative_declaration"
                       x-model="formData.legal_representative_declaration"
                       @change="validateCompliance()"
                       id="legal_representative_declaration"
                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="legal_representative_declaration" class="ml-3 text-sm text-gray-700">
                    <span class="font-medium">Declaración de Representación Legal</span>
                    <br>
                    <span class="text-gray-600">
                        Declaro que tengo la autoridad legal para actuar en nombre de la empresa y para 
                        comprometer a la organización en aspectos fiscales y tributarios.
                    </span>
                </label>
            </div>

            {{-- Compliance Status Indicator --}}
            <div x-show="complianceStatus" class="mt-4 p-3 rounded-lg" :class="complianceStatus === 'complete' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                <div class="flex items-center">
                    <template x-if="complianceStatus === 'complete'">
                        <x-solar-check-circle-outline class="w-5 h-5 mr-2" />
                    </template>
                    <template x-if="complianceStatus === 'incomplete'">
                        <x-solar-info-circle-outline class="w-5 h-5 mr-2" />
                    </template>
                    <span x-text="getComplianceMessage()"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Summary --}}
    <div x-show="Object.keys(errors).length > 0" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <h5 class="text-sm font-medium text-red-800 mb-2">Por favor, corrija los siguientes errores:</h5>
        <ul class="text-sm text-red-700 space-y-1">
            <template x-for="(error, field) in errors" :key="field">
                <li x-text="error"></li>
            </template>
        </ul>
    </div>
</div>

@push('scripts')
<script>
function fiscalInformationStep() {
    return {
        // Form data
        formData: {
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
        },
        
        // Validation state
        errors: {},
        isValidFiscalDocument: false,
        fiscalDocumentSuggestion: '',
        complianceStatus: '',
        
        // Tax regimes by country
        taxRegimes: @json($taxRegimes),
        
        // Computed properties
        get shouldShowFiscalStep() {
            // Show fiscal step for enterprise template or when explicitly enabled
            const template = this.$store?.wizard?.selectedTemplate;
            return template === 'enterprise' || template === 'complete';
        },
        
        // Methods
        init() {
            console.log('🏛️ Fiscal Information Step: Initialized');
            
            // Load saved data if available
            this.loadSavedData();
            
            // Set up validation listeners
            this.setupValidation();
        },
        
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
        },
        
        setupValidation() {
            // Auto-save data changes
            this.$watch('formData', (newData) => {
                localStorage.setItem('wizard_fiscal_data', JSON.stringify(newData));
                this.validateCompliance();
            }, { deep: true });
        },
        
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
        },
        
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
        },
        
        isValidFiscalDocumentNumber(value) {
            const docType = this.formData.fiscal_document_type;
            const cleanValue = value.replace(/[^0-9]/g, '');
            
            switch (docType) {
                case 'nit':
                    // NIT Colombia: 9 digits + verification digit
                    return /^\d{9}-?\d$/.test(value) || /^\d{10}$/.test(cleanValue);
                    
                case 'rut':
                    // RUT Chile: 8 digits + verification digit
                    return /^\d{8}-?[0-9kK]$/.test(value);
                    
                case 'rfc':
                    // RFC México: 4 letters + 6 digits + 3 characters
                    return /^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/.test(value.toUpperCase());
                    
                case 'cedula':
                    // Cédula: 6-10 digits
                    return /^\d{6,10}$/.test(cleanValue);
                    
                default:
                    return cleanValue.length >= 6;
            }
        },
        
        getFiscalDocumentSuggestion(docType, value) {
            const cleanValue = value.replace(/[^0-9A-Za-z]/g, '');
            
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
            
            return '';
        },
        
        onCountryChange(country) {
            this.formData.fiscal_country = country;
            this.validateField('fiscal_country', country);
            
            // Reset tax regime when country changes
            this.formData.tax_regime = '';
        },
        
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
        },
        
        // Helper methods
        getFiscalDocumentLabel() {
            const labels = {
                'nit': 'Número de Identificación Tributaria (NIT)',
                'rut': 'Registro Único Tributario (RUT)',
                'rfc': 'Registro Federal de Contribuyentes (RFC)',
                'cedula': 'Número de Cédula'
            };
            return labels[this.formData.fiscal_document_type] || 'Número de Documento';
        },
        
        getFiscalDocumentPlaceholder() {
            const placeholders = {
                'nit': 'Ej: 900123456-7',
                'rut': 'Ej: 12345678-9',
                'rfc': 'Ej: ABC123456XYZ',
                'cedula': 'Ej: 12345678'
            };
            return placeholders[this.formData.fiscal_document_type] || 'Ingrese el número';
        },
        
        getDepartmentLabel() {
            const labels = {
                'Colombia': 'Departamento',
                'México': 'Estado',
                'Argentina': 'Provincia',
                'Chile': 'Región'
            };
            return labels[this.formData.fiscal_country] || 'Departamento/Estado';
        },
        
        getDepartmentPlaceholder() {
            const placeholders = {
                'Colombia': 'Ej: Cundinamarca, Antioquia',
                'México': 'Ej: Ciudad de México, Jalisco',
                'Argentina': 'Ej: Buenos Aires, Córdoba',
                'Chile': 'Ej: Metropolitana, Valparaíso'
            };
            return placeholders[this.formData.fiscal_country] || 'Ingrese el departamento';
        },
        
        getAvailableTaxRegimes() {
            return this.taxRegimes[this.formData.fiscal_country] || this.taxRegimes.default;
        },
        
        getTaxRegimeDescription() {
            const descriptions = {
                'simplificado': 'Para pequeñas empresas con ingresos limitados',
                'comun': 'Régimen general para la mayoría de empresas',
                'gran_contribuyente': 'Para empresas con altos volúmenes de facturación',
                'no_responsable': 'No responsable del impuesto sobre las ventas'
            };
            return descriptions[this.formData.tax_regime] || '';
        },
        
        getComplianceMessage() {
            if (this.complianceStatus === 'complete') {
                return 'Todos los requisitos de cumplimiento han sido aceptados';
            } else {
                return 'Por favor, acepte todos los términos requeridos para continuar';
            }
        },
        
        // Validation method for wizard
        isStepValid() {
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
        },
        
        // Get form data for submission
        getFormData() {
            return { ...this.formData };
        }
    };
}
</script>
@endpush