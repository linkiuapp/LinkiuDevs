/**
 * Template Selection Component
 * 
 * Interactive template selection with comparison, preview, and validation
 * Requirements: 3.1, 3.2
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('templateSelection', (config = {}) => ({
        // Configuration
        templates: config.templates || [],
        selectedTemplate: config.selectedTemplate || null,
        showComparison: config.showComparison || false,
        showPreview: config.showPreview || true,
        
        // State
        validationError: null,
        previewModal: {
            isOpen: false,
            template: null
        },
        
        // Comparison features configuration
        comparisonFeatures: [
            { key: 'basicInfo', name: 'Información Básica' },
            { key: 'fiscalInfo', name: 'Información Fiscal' },
            { key: 'seoConfig', name: 'Configuración SEO' },
            { key: 'advancedConfig', name: 'Configuración Avanzada' },
            { key: 'bulkImport', name: 'Importación Masiva' },
            { key: 'customDomain', name: 'Dominio Personalizado' },
            { key: 'analytics', name: 'Analytics Integrado' },
            { key: 'multiLanguage', name: 'Multi-idioma' }
        ],

        init() {
            console.log('🎨 Template Selection: Initialized with', this.templates.length, 'templates');
            
            // Set up event listeners
            this.$watch('selectedTemplate', (value) => {
                this.validateSelection();
                this.notifyParent('template-selected', { templateId: value });
            });

            // Auto-select first template if none selected and only one available
            if (!this.selectedTemplate && this.templates.length === 1) {
                this.selectTemplate(this.templates[0].id);
            }
        },

        /**
         * Select a template
         */
        selectTemplate(templateId) {
            if (!templateId) {
                this.validationError = 'ID de plantilla inválido';
                return;
            }

            const template = this.templates.find(t => t.id === templateId);
            if (!template) {
                this.validationError = 'Plantilla no encontrada';
                return;
            }

            this.selectedTemplate = templateId;
            this.validationError = null;
            
            console.log('🎨 Template Selected:', template.name);
            
            // Trigger custom event for parent components
            this.$dispatch('template-changed', {
                templateId: templateId,
                template: template
            });
        },

        /**
         * Toggle comparison view
         */
        toggleComparison() {
            this.showComparison = !this.showComparison;
            console.log('🎨 Template Comparison:', this.showComparison ? 'Shown' : 'Hidden');
        },

        /**
         * Open template preview modal
         */
        previewTemplate(templateId) {
            const template = this.templates.find(t => t.id === templateId);
            if (!template) {
                console.error('Template not found for preview:', templateId);
                return;
            }

            this.previewModal = {
                isOpen: true,
                template: template
            };

            console.log('🎨 Template Preview:', template.name);
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        },

        /**
         * Close preview modal
         */
        closePreview() {
            this.previewModal = {
                isOpen: false,
                template: null
            };
            
            // Restore body scroll
            document.body.style.overflow = '';
        },

        /**
         * Select template from preview modal
         */
        selectTemplateFromPreview() {
            if (this.previewModal.template) {
                this.selectTemplate(this.previewModal.template.id);
                this.closePreview();
            }
        },

        /**
         * Get comparison features
         */
        getComparisonFeatures() {
            return this.comparisonFeatures;
        },

        /**
         * Check if template has a specific feature
         */
        hasFeature(template, featureKey) {
            if (!template.capabilities) return false;
            return template.capabilities.includes(featureKey);
        },

        /**
         * Get selected template object
         */
        getSelectedTemplate() {
            if (!this.selectedTemplate) return null;
            return this.templates.find(t => t.id === this.selectedTemplate);
        },

        /**
         * Validate current selection
         */
        validateSelection() {
            if (!this.selectedTemplate) {
                this.validationError = 'Debes seleccionar una plantilla para continuar';
                return false;
            }

            const template = this.getSelectedTemplate();
            if (!template) {
                this.validationError = 'La plantilla seleccionada no es válida';
                return false;
            }

            this.validationError = null;
            return true;
        },

        /**
         * Get template by ID
         */
        getTemplate(templateId) {
            return this.templates.find(t => t.id === templateId);
        },

        /**
         * Check if template is recommended
         */
        isRecommended(templateId) {
            const template = this.getTemplate(templateId);
            return template?.isRecommended || false;
        },

        /**
         * Get template icon component name
         */
        getTemplateIcon(template) {
            const iconMap = {
                'basic': 'shop',
                'complete': 'buildings', 
                'enterprise': 'case'
            };
            return iconMap[template.id] || 'shop';
        },

        /**
         * Get template icon color class
         */
        getTemplateIconColor(template) {
            const colorMap = {
                'basic': 'text-blue-500',
                'complete': 'text-green-500',
                'enterprise': 'text-purple-500'
            };
            return colorMap[template.id] || 'text-blue-500';
        },

        /**
         * Notify parent component
         */
        notifyParent(eventName, data) {
            this.$dispatch(eventName, data);
        },

        /**
         * Handle keyboard navigation
         */
        handleKeydown(event) {
            if (this.previewModal.isOpen && event.key === 'Escape') {
                this.closePreview();
                return;
            }

            // Arrow key navigation for template cards
            if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(event.key)) {
                this.handleArrowNavigation(event);
            }
        },

        /**
         * Handle arrow key navigation
         */
        handleArrowNavigation(event) {
            const currentIndex = this.templates.findIndex(t => t.id === this.selectedTemplate);
            let newIndex = currentIndex;

            switch (event.key) {
                case 'ArrowLeft':
                case 'ArrowUp':
                    newIndex = currentIndex > 0 ? currentIndex - 1 : this.templates.length - 1;
                    break;
                case 'ArrowRight':
                case 'ArrowDown':
                    newIndex = currentIndex < this.templates.length - 1 ? currentIndex + 1 : 0;
                    break;
            }

            if (newIndex !== currentIndex && this.templates[newIndex]) {
                this.selectTemplate(this.templates[newIndex].id);
                event.preventDefault();
            }
        },

        /**
         * Get template statistics
         */
        getTemplateStats(template) {
            return {
                fieldCount: template.fieldCount || this.calculateFieldCount(template),
                estimatedTime: template.estimatedTime || this.calculateEstimatedTime(template),
                stepCount: template.steps?.length || 0
            };
        },

        /**
         * Calculate field count for template
         */
        calculateFieldCount(template) {
            if (!template.steps) return 0;
            return template.steps.reduce((total, step) => {
                return total + (step.fields?.length || 0);
            }, 0);
        },

        /**
         * Calculate estimated completion time
         */
        calculateEstimatedTime(template) {
            const fieldCount = this.calculateFieldCount(template);
            const baseTime = 2; // 2 minutes base
            const timePerField = 0.5; // 30 seconds per field
            const totalMinutes = Math.ceil(baseTime + (fieldCount * timePerField));
            
            if (totalMinutes < 60) {
                return `${totalMinutes} min`;
            } else {
                const hours = Math.floor(totalMinutes / 60);
                const minutes = totalMinutes % 60;
                return minutes > 0 ? `${hours}h ${minutes}m` : `${hours}h`;
            }
        },

        /**
         * Export current selection for form submission
         */
        exportSelection() {
            return {
                templateId: this.selectedTemplate,
                template: this.getSelectedTemplate(),
                isValid: this.validateSelection()
            };
        }
    }));
});

/**
 * Template Selection Utilities
 */
window.TemplateSelectionUtils = {
    /**
     * Create default template configuration
     */
    createDefaultTemplates() {
        return [
            {
                id: 'basic',
                name: 'Tienda Básica',
                description: 'Configuración rápida con campos esenciales',
                icon: 'shop',
                iconColor: 'text-blue-500',
                isRecommended: true,
                fieldCount: 8,
                estimatedTime: '5 min',
                features: [
                    'Información básica del propietario',
                    'Configuración de tienda simple',
                    'Plan básico incluido',
                    'Configuración rápida'
                ],
                capabilities: ['basicInfo'],
                steps: [
                    {
                        id: 'owner-info',
                        title: 'Información del Propietario',
                        order: 1,
                        isOptional: false,
                        fields: [
                            { name: 'owner_name', label: 'Nombre', required: true },
                            { name: 'admin_email', label: 'Email', required: true },
                            { name: 'admin_password', label: 'Contraseña', required: true }
                        ]
                    },
                    {
                        id: 'store-config',
                        title: 'Configuración de Tienda',
                        order: 2,
                        isOptional: false,
                        fields: [
                            { name: 'name', label: 'Nombre de la Tienda', required: true },
                            { name: 'slug', label: 'URL', required: true },
                            { name: 'plan_id', label: 'Plan', required: true }
                        ]
                    }
                ]
            },
            {
                id: 'complete',
                name: 'Tienda Completa',
                description: 'Configuración completa con todas las opciones',
                icon: 'buildings',
                iconColor: 'text-green-500',
                isRecommended: false,
                fieldCount: 15,
                estimatedTime: '10 min',
                features: [
                    'Información completa del propietario',
                    'Configuración avanzada de tienda',
                    'Información fiscal opcional',
                    'Configuración SEO básica',
                    'Múltiples opciones de plan'
                ],
                capabilities: ['basicInfo', 'fiscalInfo', 'seoConfig'],
                steps: [
                    {
                        id: 'owner-info',
                        title: 'Información del Propietario',
                        order: 1,
                        isOptional: false,
                        fields: [
                            { name: 'owner_name', label: 'Nombre', required: true },
                            { name: 'admin_email', label: 'Email', required: true },
                            { name: 'document_type', label: 'Tipo de Documento', required: true },
                            { name: 'document_number', label: 'Número de Documento', required: true },
                            { name: 'country', label: 'País', required: true },
                            { name: 'admin_password', label: 'Contraseña', required: true }
                        ]
                    },
                    {
                        id: 'store-config',
                        title: 'Configuración de Tienda',
                        order: 2,
                        isOptional: false,
                        fields: [
                            { name: 'name', label: 'Nombre de la Tienda', required: true },
                            { name: 'slug', label: 'URL', required: true },
                            { name: 'email', label: 'Email de Contacto', required: false },
                            { name: 'phone', label: 'Teléfono', required: false },
                            { name: 'description', label: 'Descripción', required: false },
                            { name: 'plan_id', label: 'Plan', required: true }
                        ]
                    },
                    {
                        id: 'seo-config',
                        title: 'Configuración SEO',
                        order: 3,
                        isOptional: true,
                        fields: [
                            { name: 'meta_title', label: 'Título SEO', required: false },
                            { name: 'meta_description', label: 'Descripción SEO', required: false },
                            { name: 'meta_keywords', label: 'Palabras Clave', required: false }
                        ]
                    }
                ]
            },
            {
                id: 'enterprise',
                name: 'Tienda Empresarial',
                description: 'Enfoque en información fiscal y compliance',
                icon: 'case',
                iconColor: 'text-purple-500',
                isRecommended: false,
                fieldCount: 20,
                estimatedTime: '15 min',
                features: [
                    'Información fiscal completa',
                    'Configuración empresarial',
                    'Compliance y documentación',
                    'Configuración SEO avanzada',
                    'Opciones de dominio personalizado',
                    'Analytics integrado'
                ],
                capabilities: ['basicInfo', 'fiscalInfo', 'seoConfig', 'advancedConfig', 'customDomain', 'analytics'],
                steps: [
                    {
                        id: 'owner-info',
                        title: 'Información del Propietario',
                        order: 1,
                        isOptional: false,
                        fields: [
                            { name: 'owner_name', label: 'Nombre del Representante', required: true },
                            { name: 'admin_email', label: 'Email Corporativo', required: true },
                            { name: 'document_type', label: 'Tipo de Documento', required: true },
                            { name: 'document_number', label: 'Número de Documento', required: true },
                            { name: 'country', label: 'País', required: true },
                            { name: 'department', label: 'Departamento', required: true },
                            { name: 'city', label: 'Ciudad', required: true },
                            { name: 'admin_password', label: 'Contraseña', required: true }
                        ]
                    },
                    {
                        id: 'store-config',
                        title: 'Configuración de Tienda',
                        order: 2,
                        isOptional: false,
                        fields: [
                            { name: 'name', label: 'Razón Social', required: true },
                            { name: 'slug', label: 'URL Corporativa', required: true },
                            { name: 'email', label: 'Email de Contacto', required: true },
                            { name: 'phone', label: 'Teléfono Corporativo', required: true },
                            { name: 'description', label: 'Descripción Empresarial', required: false },
                            { name: 'plan_id', label: 'Plan Empresarial', required: true }
                        ]
                    },
                    {
                        id: 'fiscal-info',
                        title: 'Información Fiscal',
                        order: 3,
                        isOptional: false,
                        fields: [
                            { name: 'fiscal_document_type', label: 'Tipo de Documento Fiscal', required: true },
                            { name: 'fiscal_document_number', label: 'NIT/RUT', required: true },
                            { name: 'fiscal_address', label: 'Dirección Fiscal', required: true },
                            { name: 'tax_regime', label: 'Régimen Tributario', required: true }
                        ]
                    },
                    {
                        id: 'seo-config',
                        title: 'Configuración SEO',
                        order: 4,
                        isOptional: true,
                        fields: [
                            { name: 'meta_title', label: 'Título SEO', required: false },
                            { name: 'meta_description', label: 'Descripción SEO', required: false }
                        ]
                    }
                ]
            }
        ];
    },

    /**
     * Validate template configuration
     */
    validateTemplate(template) {
        const required = ['id', 'name', 'description'];
        const missing = required.filter(field => !template[field]);
        
        if (missing.length > 0) {
            throw new Error(`Template missing required fields: ${missing.join(', ')}`);
        }

        return true;
    }
};

console.log('🎨 Template Selection Component: Loaded');