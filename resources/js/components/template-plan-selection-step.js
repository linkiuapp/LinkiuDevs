/**
 * Template and Plan Selection Step Component
 * 
 * Enhanced step for selecting store template and plan with dynamic options,
 * feature comparison, pricing display, and compatibility validation
 * Requirements: 3.1, 6.1
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('templatePlanSelection', (config = {}) => ({
        // Configuration
        templates: config.templates || [],
        plans: config.plans || [],
        selectedTemplate: config.selectedTemplate || null,
        selectedPlan: config.selectedPlan || null,
        showComparison: config.showComparison || true,
        
        // State
        showTemplateComparison: false,
        showPlanComparison: false,
        billingPeriod: 'monthly',
        validationErrors: [],
        
        // Template comparison features
        templateComparisonFeatures: [
            { key: 'basicInfo', name: 'Información Básica' },
            { key: 'fiscalInfo', name: 'Información Fiscal' },
            { key: 'seoConfig', name: 'Configuración SEO' },
            { key: 'advancedConfig', name: 'Configuración Avanzada' },
            { key: 'customDomain', name: 'Dominio Personalizado' },
            { key: 'analytics', name: 'Analytics Integrado' }
        ],

        // Plan comparison features
        planComparisonFeatures: [
            { key: 'max_products', name: 'Productos Máximos' },
            { key: 'max_categories', name: 'Categorías Máximas' },
            { key: 'max_admins', name: 'Administradores' },
            { key: 'max_sedes', name: 'Sedes Máximas' },
            { key: 'allow_custom_slug', name: 'URL Personalizada' },
            { key: 'support_level', name: 'Nivel de Soporte' }
        ],

        init() {
            console.log('🎨 Template Plan Selection: Initialized');
            console.log('Templates:', this.templates.length);
            console.log('Plans:', this.plans.length);
            
            // Set up watchers
            this.$watch('selectedTemplate', (value) => {
                this.onTemplateChange(value);
            });

            this.$watch('selectedPlan', (value) => {
                this.onPlanChange(value);
            });

            this.$watch('billingPeriod', (value) => {
                this.updatePlanPricing();
            });

            // Auto-select recommended template if none selected
            if (!this.selectedTemplate && this.templates.length > 0) {
                const recommended = this.templates.find(t => t.isRecommended);
                if (recommended) {
                    this.selectTemplate(recommended.id);
                }
            }

            // Validate initial state
            this.validateSelection();
        },

        /**
         * Template Selection Methods
         */
        selectTemplate(templateId) {
            if (!templateId) {
                this.addValidationError('ID de plantilla inválido');
                return;
            }

            const template = this.templates.find(t => t.id === templateId);
            if (!template) {
                this.addValidationError('Plantilla no encontrada');
                return;
            }

            this.selectedTemplate = templateId;
            this.clearValidationError('template');
            
            console.log('🎨 Template Selected:', template.name);
            
            // Reset plan selection if incompatible
            if (this.selectedPlan && !this.isPlanCompatible(this.getSelectedPlan())) {
                this.selectedPlan = null;
                console.log('🎨 Plan reset due to template incompatibility');
            }

            // Auto-select compatible plan if only one available
            const compatiblePlans = this.getCompatiblePlans();
            if (compatiblePlans.length === 1) {
                this.selectPlan(compatiblePlans[0].id);
            }

            this.$dispatch('template-selected', {
                templateId: templateId,
                template: template
            });
        },

        toggleTemplateComparison() {
            this.showTemplateComparison = !this.showTemplateComparison;
        },

        getTemplateComparisonFeatures() {
            return this.templateComparisonFeatures;
        },

        templateHasFeature(template, featureKey) {
            if (!template.capabilities) return false;
            return template.capabilities.includes(featureKey);
        },

        getSelectedTemplate() {
            if (!this.selectedTemplate) return null;
            return this.templates.find(t => t.id === this.selectedTemplate);
        },

        /**
         * Plan Selection Methods
         */
        selectPlan(planId) {
            if (!planId) {
                this.addValidationError('ID de plan inválido');
                return;
            }

            const plan = this.plans.find(p => p.id === planId);
            if (!plan) {
                this.addValidationError('Plan no encontrado');
                return;
            }

            if (!this.isPlanCompatible(plan)) {
                this.addValidationError('Plan no compatible con la plantilla seleccionada');
                return;
            }

            this.selectedPlan = planId;
            this.clearValidationError('plan');
            
            console.log('🎨 Plan Selected:', plan.name);

            this.$dispatch('plan-selected', {
                planId: planId,
                plan: plan,
                billingPeriod: this.billingPeriod
            });
        },

        togglePlanComparison() {
            this.showPlanComparison = !this.showPlanComparison;
        },

        getPlanComparisonFeatures() {
            return this.planComparisonFeatures;
        },

        getSelectedPlan() {
            if (!this.selectedPlan) return null;
            return this.plans.find(p => p.id === this.selectedPlan);
        },

        getCompatiblePlans() {
            if (!this.selectedTemplate) return [];
            
            const template = this.getSelectedTemplate();
            if (!template) return this.plans;

            // Filter plans based on template compatibility
            return this.plans.filter(plan => this.isPlanCompatible(plan));
        },

        isPlanCompatible(plan) {
            if (!this.selectedTemplate || !plan) return false;
            
            const template = this.getSelectedTemplate();
            if (!template) return false;

            // Basic compatibility - all plans are compatible with basic template
            if (template.id === 'basic') return true;

            // Complete template requires standard or higher plans
            if (template.id === 'complete') {
                return ['standard', 'premium', 'enterprise'].includes(plan.name.toLowerCase()) ||
                       plan.max_products >= 100; // Fallback based on product limit
            }

            // Enterprise template requires premium or enterprise plans
            if (template.id === 'enterprise') {
                return ['premium', 'enterprise'].includes(plan.name.toLowerCase()) ||
                       plan.max_products >= 500; // Fallback based on product limit
            }

            return true;
        },

        /**
         * Pricing Methods
         */
        updatePlanPricing() {
            console.log('🎨 Updating plan pricing for period:', this.billingPeriod);
            // Trigger reactivity for price displays
            this.$nextTick(() => {
                this.$dispatch('pricing-updated', {
                    billingPeriod: this.billingPeriod
                });
            });
        },

        getPlanPrice(plan) {
            if (!plan) return '0';
            
            // Try to get price for current billing period
            if (plan.prices && plan.prices[this.billingPeriod]) {
                return this.formatPrice(plan.prices[this.billingPeriod]);
            }

            // Calculate based on monthly price
            if (plan.price) {
                let multiplier = 1;
                switch (this.billingPeriod) {
                    case 'quarterly':
                        multiplier = 3;
                        break;
                    case 'biannual':
                        multiplier = 6;
                        break;
                }
                return this.formatPrice(plan.price * multiplier);
            }

            return '0';
        },

        getPlanPriceDisplay(plan) {
            const price = this.getPlanPrice(plan);
            const period = this.getPlanPeriodLabel();
            return `$${price} ${period}`;
        },

        getSelectedPlanPriceDisplay() {
            const plan = this.getSelectedPlan();
            if (!plan) return 'N/A';
            return this.getPlanPriceDisplay(plan);
        },

        getPlanPeriodLabel() {
            const labels = {
                'monthly': '/mes',
                'quarterly': '/trimestre',
                'biannual': '/semestre'
            };
            return labels[this.billingPeriod] || '/mes';
        },

        getDiscount(plan) {
            if (this.billingPeriod === 'monthly' || !plan.price) return 0;

            const monthlyTotal = plan.price * (this.billingPeriod === 'quarterly' ? 3 : 6);
            const periodPrice = this.getPlanPriceRaw(plan);
            
            if (periodPrice && monthlyTotal > periodPrice) {
                return Math.round(((monthlyTotal - periodPrice) / monthlyTotal) * 100);
            }
            
            return 0;
        },

        getPlanPriceRaw(plan) {
            if (!plan) return 0;
            
            if (plan.prices && plan.prices[this.billingPeriod]) {
                return plan.prices[this.billingPeriod];
            }

            if (plan.price) {
                let multiplier = 1;
                switch (this.billingPeriod) {
                    case 'quarterly':
                        multiplier = 3;
                        break;
                    case 'biannual':
                        multiplier = 6;
                        break;
                }
                return plan.price * multiplier;
            }

            return 0;
        },

        formatPrice(price) {
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(price);
        },

        /**
         * Feature Display Methods
         */
        getPlanMainFeatures(plan) {
            if (!plan) return [];

            const features = [];

            if (plan.max_products) {
                features.push({
                    key: 'products',
                    label: `Hasta ${plan.max_products} productos`
                });
            }

            if (plan.max_categories) {
                features.push({
                    key: 'categories',
                    label: `${plan.max_categories} categorías`
                });
            }

            if (plan.max_admins) {
                features.push({
                    key: 'admins',
                    label: `${plan.max_admins} administradores`
                });
            }

            if (plan.allow_custom_slug) {
                features.push({
                    key: 'custom_slug',
                    label: 'URL personalizada'
                });
            }

            if (plan.support_level) {
                features.push({
                    key: 'support',
                    label: `Soporte ${plan.support_level}`
                });
            }

            return features.slice(0, 5); // Show max 5 main features
        },

        getPlanLimitations(plan) {
            if (!plan) return [];

            const limitations = [];

            if (!plan.allow_custom_slug) {
                limitations.push('URL generada automáticamente');
            }

            if (plan.max_products && plan.max_products < 100) {
                limitations.push('Productos limitados');
            }

            if (plan.support_level === 'basic') {
                limitations.push('Soporte básico únicamente');
            }

            return limitations;
        },

        getPlanFeatureValue(plan, featureKey) {
            if (!plan) return 'N/A';

            switch (featureKey) {
                case 'max_products':
                    return plan.max_products || 'Ilimitado';
                case 'max_categories':
                    return plan.max_categories || 'Ilimitado';
                case 'max_admins':
                    return plan.max_admins || 'Ilimitado';
                case 'max_sedes':
                    return plan.max_sedes || 'Ilimitado';
                case 'allow_custom_slug':
                    return plan.allow_custom_slug ? 'Sí' : 'No';
                case 'support_level':
                    return plan.support_level || 'Básico';
                default:
                    return plan[featureKey] || 'N/A';
            }
        },

        /**
         * Validation Methods
         */
        validateSelection() {
            this.validationErrors = [];

            if (!this.selectedTemplate) {
                this.addValidationError('Debes seleccionar una plantilla');
            }

            if (this.selectedTemplate && !this.selectedPlan) {
                this.addValidationError('Debes seleccionar un plan');
            }

            if (this.selectedTemplate && this.selectedPlan) {
                const plan = this.getSelectedPlan();
                if (!this.isPlanCompatible(plan)) {
                    this.addValidationError('El plan seleccionado no es compatible con la plantilla');
                }
            }

            return this.validationErrors.length === 0;
        },

        addValidationError(message) {
            if (!this.validationErrors.includes(message)) {
                this.validationErrors.push(message);
            }
        },

        clearValidationError(type) {
            const errorPatterns = {
                'template': ['plantilla', 'template'],
                'plan': ['plan']
            };

            if (errorPatterns[type]) {
                this.validationErrors = this.validationErrors.filter(error => {
                    return !errorPatterns[type].some(pattern => 
                        error.toLowerCase().includes(pattern)
                    );
                });
            }
        },

        /**
         * Event Handlers
         */
        onTemplateChange(templateId) {
            console.log('🎨 Template changed to:', templateId);
            this.validateSelection();
        },

        onPlanChange(planId) {
            console.log('🎨 Plan changed to:', planId);
            this.validateSelection();
        },

        /**
         * Export Methods
         */
        exportSelection() {
            return {
                templateId: this.selectedTemplate,
                template: this.getSelectedTemplate(),
                planId: this.selectedPlan,
                plan: this.getSelectedPlan(),
                billingPeriod: this.billingPeriod,
                isValid: this.validateSelection(),
                errors: this.validationErrors
            };
        },

        /**
         * Utility Methods
         */
        isStepComplete() {
            return this.selectedTemplate && this.selectedPlan && this.validateSelection();
        },

        getStepData() {
            return {
                template: this.selectedTemplate,
                plan_id: this.selectedPlan,
                billing_period: this.billingPeriod
            };
        },

        resetSelection() {
            this.selectedTemplate = null;
            this.selectedPlan = null;
            this.validationErrors = [];
        }
    }));
});

/**
 * Template Plan Selection Utilities
 */
window.TemplatePlanSelectionUtils = {
    /**
     * Create default plan configuration for testing
     */
    createDefaultPlans() {
        return [
            {
                id: 1,
                name: 'Básico',
                description: 'Perfecto para empezar tu tienda online',
                price: 29900,
                prices: {
                    monthly: 29900,
                    quarterly: 80730, // 10% discount
                    biannual: 149500   // 16% discount
                },
                currency: 'COP',
                max_products: 50,
                max_categories: 10,
                max_admins: 1,
                max_sedes: 1,
                allow_custom_slug: false,
                support_level: 'básico',
                is_active: true,
                is_public: true,
                is_featured: false
            },
            {
                id: 2,
                name: 'Estándar',
                description: 'Para tiendas en crecimiento',
                price: 59900,
                prices: {
                    monthly: 59900,
                    quarterly: 161730, // 10% discount
                    biannual: 299500   // 16% discount
                },
                currency: 'COP',
                max_products: 200,
                max_categories: 25,
                max_admins: 3,
                max_sedes: 2,
                allow_custom_slug: true,
                support_level: 'estándar',
                is_active: true,
                is_public: true,
                is_featured: true
            },
            {
                id: 3,
                name: 'Premium',
                description: 'Para tiendas establecidas',
                price: 99900,
                prices: {
                    monthly: 99900,
                    quarterly: 269730, // 10% discount
                    biannual: 499500   // 16% discount
                },
                currency: 'COP',
                max_products: 1000,
                max_categories: 100,
                max_admins: 10,
                max_sedes: 5,
                allow_custom_slug: true,
                support_level: 'premium',
                is_active: true,
                is_public: true,
                is_featured: false
            },
            {
                id: 4,
                name: 'Enterprise',
                description: 'Para grandes empresas',
                price: 199900,
                prices: {
                    monthly: 199900,
                    quarterly: 539730, // 10% discount
                    biannual: 999500   // 16% discount
                },
                currency: 'COP',
                max_products: null, // Unlimited
                max_categories: null,
                max_admins: null,
                max_sedes: null,
                allow_custom_slug: true,
                support_level: 'enterprise',
                is_active: true,
                is_public: true,
                is_featured: false
            }
        ];
    },

    /**
     * Validate plan configuration
     */
    validatePlan(plan) {
        const required = ['id', 'name', 'description', 'price'];
        const missing = required.filter(field => !plan[field]);
        
        if (missing.length > 0) {
            throw new Error(`Plan missing required fields: ${missing.join(', ')}`);
        }

        return true;
    }
};

console.log('🎨 Template Plan Selection Step Component: Loaded');