/**
 * =============================================================================
 * STORE MANAGEMENT SYSTEM
 * =============================================================================
 * Arquitectura modular para gestión de tiendas en SuperLinkiu
 * 
 * Módulos:
 * - Utils: Funciones helper compartidas
 * - StoreManagement: Gestión del index (checkboxes, bulk actions, verificación)
 * - StoreForm: Formularios de creación y edición
 * - Notifications: Sistema de notificaciones
 * =============================================================================
 */

console.log('🚀 Store Management System loaded');

// =============================================================================
// UTILS - Funciones helper compartidas
// =============================================================================
const StoreUtils = {
    /**
     * Obtiene el token CSRF
     */
    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) {
            console.error('❌ CSRF token not found');
            throw new Error('CSRF token not found');
        }
        return token;
    },

    /**
     * Realiza una llamada API con configuración estándar
     */
    async apiCall(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        };

        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('❌ API Call failed:', error);
            throw error;
        }
    },

    /**
     * Genera slug desde texto
     */
    generateSlug(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    },

    /**
     * Genera slug aleatorio
     */
    generateRandomSlug(prefix = 'tienda') {
        const randomString = Math.random().toString(36).substring(2, 8);
        return `${prefix}-${randomString}`;
    },

    /**
     * Debugging condicional
     */
    debug(...args) {
        if (window.APP_DEBUG || localStorage.getItem('store_debug')) {
            console.log('🐛', ...args);
        }
    }
};

// =============================================================================
// HACER DISPONIBLE GLOBALMENTE PARA DEBUGGING
// =============================================================================
window.StoreUtils = StoreUtils;

// =============================================================================
// NOTIFICATIONS - Sistema de notificaciones
// =============================================================================
const NotificationMixin = {
    showNotification: false,
    notificationMessage: '',
    notificationType: 'success',

    showNotificationMessage(message, type = 'success') {
        this.notificationMessage = message;
        this.notificationType = type;
        this.showNotification = true;
        
        setTimeout(() => {
            this.showNotification = false;
        }, 5000);
    },

    handleError(error, defaultMessage = 'Ha ocurrido un error') {
        console.error('❌ Error:', error);
        this.showNotificationMessage(defaultMessage, 'error');
    }
};

// =============================================================================
// ALPINE.JS COMPONENTS
// =============================================================================
document.addEventListener('alpine:init', () => {
    StoreUtils.debug('Alpine init detected - registering components');

    // =========================================================================
    // STORE MANAGEMENT - Index de tiendas
    // =========================================================================
    Alpine.data('storeManagement', () => ({
        // State
        selectedStores: [],
        showDeleteModal: false,
        deleteStoreId: null,
        deleteStoreName: '',

        // Mixins
        ...NotificationMixin,

        /**
         * Inicialización del componente
         */
        init() {
            console.log('🚀 StoreManagement component initializing...');
            StoreUtils.debug('StoreManagement initialized');
            this.$nextTick(() => {
                console.log('⏭️ $nextTick executing - about to initialize components');
                this.initializeComponents();
            });
        },

        /**
         * Inicializa todos los componentes
         */
        initializeComponents() {
            console.log('🔧 Initializing all components...');
            this.setupCheckboxes();
            this.setupVerificationSwitches();
            console.log('✅ All components initialized successfully');
            StoreUtils.debug('All components initialized');
        },

        // =====================================================================
        // CHECKBOX MANAGEMENT
        // =====================================================================
        
        /**
         * Configura el sistema de checkboxes
         */
        setupCheckboxes() {
            const selectAll = document.getElementById('selectAll');
            const storeCheckboxes = document.querySelectorAll('.store-checkbox');
            
            if (!selectAll || storeCheckboxes.length === 0) {
                StoreUtils.debug('No checkboxes found, skipping setup');
                return;
            }

            // Checkbox "Seleccionar todo"
            selectAll.addEventListener('change', () => {
                this.handleSelectAll(selectAll.checked, storeCheckboxes);
            });

            // Checkboxes individuales
            storeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    this.handleIndividualCheckbox(checkbox);
                });
            });

            StoreUtils.debug(`Checkboxes configured: ${storeCheckboxes.length} individual + 1 select-all`);
        },

        /**
         * Maneja la selección de "Seleccionar todo"
         */
        handleSelectAll(isChecked, storeCheckboxes) {
            this.selectedStores = [];
            
            storeCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                if (isChecked) {
                    this.selectedStores.push(checkbox.value);
                }
            });
            
            this.updateBulkActionsUI();
        },

        /**
         * Maneja checkboxes individuales
         */
        handleIndividualCheckbox(checkbox) {
            if (checkbox.checked) {
                if (!this.selectedStores.includes(checkbox.value)) {
                    this.selectedStores.push(checkbox.value);
                }
            } else {
                this.selectedStores = this.selectedStores.filter(id => id !== checkbox.value);
            }
            
            this.updateBulkActionsUI();
            this.updateSelectAllState();
        },

        /**
         * Actualiza la UI de acciones bulk
         */
        updateBulkActionsUI() {
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (bulkActions) {
                bulkActions.style.display = this.selectedStores.length > 0 ? 'flex' : 'none';
            }
            
            if (selectedCount) {
                selectedCount.textContent = this.selectedStores.length;
            }
        },

        /**
         * Actualiza el estado del checkbox "Seleccionar todo"
         */
        updateSelectAllState() {
            const selectAll = document.getElementById('selectAll');
            const storeCheckboxes = document.querySelectorAll('.store-checkbox');
            
            if (!selectAll) return;
            
            const checkedCount = document.querySelectorAll('.store-checkbox:checked').length;
            const totalCount = storeCheckboxes.length;
            
            selectAll.checked = checkedCount === totalCount && checkedCount > 0;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        },

        // =====================================================================
        // VERIFICATION SYSTEM
        // =====================================================================
        
        /**
         * Configura los switches de verificación
         */
        setupVerificationSwitches() {
            console.log('🎛️ Setting up verification switches...');
            const toggles = document.querySelectorAll('.verified-toggle');
            console.log('🔍 Found toggles:', toggles.length);
            
            if (toggles.length === 0) {
                console.warn('⚠️ No verification toggles found on page');
                console.log('🔍 Available elements with classes:', {
                    'verified-toggle': document.querySelectorAll('.verified-toggle').length,
                    'store-checkbox': document.querySelectorAll('.store-checkbox').length,
                    'elements-with-data-url': document.querySelectorAll('[data-url]').length
                });
                return;
            }
            
            StoreUtils.debug(`Setting up ${toggles.length} verified toggles`);
            
            toggles.forEach((toggle, index) => {
                console.log(`🔧 Configuring toggle ${index + 1}:`, {
                    element: toggle,
                    dataUrl: toggle.dataset.url,
                    dataStoreId: toggle.dataset.storeId,
                    checked: toggle.checked,
                    classes: toggle.className
                });
                this.configureVerificationToggle(toggle);
            });
            
            console.log('✅ All verification switches configured');
        },

        /**
         * Configura un toggle individual
         */
        configureVerificationToggle(toggle) {
            // Remover focus styles
            toggle.addEventListener('focus', (e) => e.target.blur());
            
            // Manejar cambios
            toggle.addEventListener('change', async (e) => {
                await this.handleVerificationToggle(e);
            });
        },

        /**
         * Maneja el cambio de un toggle de verificación
         */
        /*async handleVerificationToggle(event) {
            const toggle = event.target;
            const url = toggle.dataset.url;
            const newState = toggle.checked;
            
            // =====================================================================
            // DEBUGGING DETALLADO - ACTIVAR CON: localStorage.setItem('store_debug', 'true')
            // =====================================================================
            console.log('🎯 TOGGLE VERIFICATION STARTED');
            console.log('📍 Toggle element:', toggle);
            console.log('🔗 URL:', url);
            console.log('⚡ New state:', newState);
            console.log('🏪 Store ID from data-store-id:', toggle.dataset.storeId);
            console.log('📋 All toggle datasets:', toggle.dataset);
            
            StoreUtils.debug('Verified toggle changed:', { url, newState });
            
            if (!url) {
                console.error('❌ No URL found on toggle element');
                console.error('📋 Available datasets:', toggle.dataset);
                console.error('🔍 Expected data-url attribute missing');
                toggle.checked = !newState;
                this.showNotificationMessage('Error: URL de verificación no encontrada', 'error');
                return;
            }

            // Verificar CSRF token antes de la llamada
            let csrfToken;
            try {
                csrfToken = StoreUtils.getCsrfToken();
                console.log('🔐 CSRF Token obtained successfully');
            } catch (error) {
                console.error('❌ CSRF Token error:', error);
                toggle.checked = !newState;
                this.showNotificationMessage('Error de seguridad: Token CSRF no encontrado', 'error');
                return;
            }

            try {
                console.log('📡 Making API call to:', url);
                console.log('🔐 Using CSRF token:', csrfToken ? 'PRESENT' : 'MISSING');
                
                const response = await StoreUtils.apiCall(url, {
                    method: 'POST'
                });

                console.log('📦 API Response received:', response);
                StoreUtils.debug('Verification response:', response);

                if (response.success) {
                    console.log('✅ Success response received');
                    console.log('🔄 Server verified state:', response.verified);
                    console.log('💬 Server message:', response.message);
                    
                    // Forzar el estado correcto desde el servidor
                    const oldChecked = toggle.checked;
                    toggle.checked = response.verified;
                    
                    console.log('🔄 Toggle state updated:', {
                        old: oldChecked,
                        new: toggle.checked,
                        serverState: response.verified
                    });
                    
                    this.showNotificationMessage(
                        response.message || 
                        (response.verified ? 'Tienda verificada exitosamente' : 'Verificación removida exitosamente'),
                        'success'
                    );
                    
                    console.log('🎉 TOGGLE VERIFICATION COMPLETED SUCCESSFULLY');
                } else {
                    console.error('❌ Server returned success:false');
                    console.error('📄 Full response:', response);
                    throw new Error(response.message || 'Error al actualizar verificación');
                }
            } catch (error) {
                console.error('💥 VERIFICATION TOGGLE ERROR:');
                console.error('🔥 Error object:', error);
                console.error('📊 Error details:', {
                    message: error.message,
                    name: error.name,
                    stack: error.stack
                });
                
                // Revertir el estado en caso de error
                const revertedState = !newState;
                toggle.checked = revertedState;
                console.log('🔄 State reverted to:', revertedState);
                
                this.handleError(error, 'Error al actualizar verificación');
            }
        },*/

        // =====================================================================
        // BULK ACTIONS
        // =====================================================================
        
        /**
         * Ejecuta una acción bulk
         */
        async executeBulkAction() {
            const actionSelect = document.getElementById('bulkActionSelect');
            
            if (!actionSelect) {
                this.showNotificationMessage('Elemento de acción no encontrado', 'error');
                return;
            }

            const action = actionSelect.value;
            
            if (!action) {
                this.showNotificationMessage('Por favor selecciona una acción', 'warning');
                return;
            }
            
            if (this.selectedStores.length === 0) {
                this.showNotificationMessage('Por favor selecciona al menos una tienda', 'warning');
                return;
            }
            
            if (!confirm(`¿Estás seguro de aplicar esta acción a ${this.selectedStores.length} tienda(s)?`)) {
                return;
            }

            try {
                const response = await StoreUtils.apiCall('/superlinkiu/stores/bulk-action', {
                    method: 'POST',
                    body: JSON.stringify({
                        store_ids: this.selectedStores,
                        action: action
                    })
                });

                if (response.success) {
                    this.showNotificationMessage(response.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(response.message || 'Error al ejecutar la acción');
                }
            } catch (error) {
                this.handleError(error, 'Error al ejecutar la acción bulk');
            }
        },

        // =====================================================================
        // OTHER ACTIONS
        // =====================================================================
        
        /**
         * Exporta datos
         */
        exportData(format) {
            const url = new URL(window.location.href);
            url.searchParams.set('export', format);
            window.location.href = url.toString();
        },

        /**
         * Inicia sesión como tienda
         */
        async loginAsStore(storeId) {
            if (!confirm('¿Deseas entrar como administrador de esta tienda?')) {
                return;
            }

            try {
                const response = await StoreUtils.apiCall(`/superlinkiu/stores/${storeId}/login-as`, {
                    method: 'POST'
                });

                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    throw new Error(response.message || 'Error al iniciar sesión');
                }
            } catch (error) {
                this.handleError(error, 'Error al iniciar sesión como tienda');
            }
        },

        // =====================================================================
        // DELETE MODAL
        // =====================================================================
        
        /**
         * Abre modal de eliminación
         */
        openDeleteModal(storeId, storeName) {
            this.deleteStoreId = storeId;
            this.deleteStoreName = storeName;
            this.showDeleteModal = true;
        },

        /**
         * Cierra modal de eliminación
         */
        closeDeleteModal() {
            this.showDeleteModal = false;
            this.deleteStoreId = null;
            this.deleteStoreName = '';
        },

        /**
         * Confirma eliminación
         */
        async confirmDelete() {
            if (!this.deleteStoreId) return;

            try {
                const response = await StoreUtils.apiCall(`/superlinkiu/stores/${this.deleteStoreId}`, {
                    method: 'DELETE'
                });

                if (response.success !== false) {
                    this.closeDeleteModal();
                    this.showNotificationMessage('Tienda eliminada exitosamente', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(response.message || 'Error al eliminar la tienda');
                }
            } catch (error) {
                this.handleError(error, 'Error al eliminar la tienda');
            }
        }
    }));

    // =========================================================================
    // STORE FORMS - Creación y edición
    // =========================================================================
    
    /**
     * Formulario de creación de tienda
     */
    Alpine.data('createStore', () => ({
        selectedPlan: '',
        slug: '',
        isXplorer: false,

        init() {
            this.loadOldValues();
            StoreUtils.debug('CreateStore initialized');
        },

        /**
         * Carga valores old() de Laravel
         */
        loadOldValues() {
            const oldPlan = document.querySelector('input[name="_old_plan_id"]')?.value;
            const oldSlug = document.querySelector('input[name="_old_slug"]')?.value;
            
            if (oldPlan) {
                this.selectedPlan = oldPlan;
                this.checkPlanType();
            }
            
            if (oldSlug) {
                this.slug = oldSlug;
            }
        },

        /**
         * Genera slug desde el nombre
         */
        generateSlug(event) {
            if (this.isXplorer) return; // Explorer mantiene slug aleatorio
            
            const value = event.target.value;
            this.slug = StoreUtils.generateSlug(value);
        },

        /**
         * Verifica el tipo de plan seleccionado
         */
        checkPlanType() {
            const select = document.querySelector('select[name="plan_id"]');
            if (!select) return;
            
            const selectedOption = Array.from(select.options)
                .find(option => option.value === this.selectedPlan);
            
            if (!selectedOption) return;
            
            const planName = selectedOption.getAttribute('data-plan-name');
            const allowCustom = selectedOption.getAttribute('data-allow-custom') === 'true';
            
            this.isXplorer = planName === 'Explorer';
            
            if (this.isXplorer) {
                this.slug = StoreUtils.generateRandomSlug();
            } else if (allowCustom) {
                const nameInput = document.querySelector('input[name="name"]');
                if (nameInput?.value) {
                    this.slug = StoreUtils.generateSlug(nameInput.value);
                }
            }
        }
    }));

    /**
     * Formulario de edición de tienda
     */
    Alpine.data('editStore', () => ({
        selectedPlan: '',
        slug: '',
        originalPlanId: '',
        originalPlanSlug: '',
        originalSlug: '',
        canEditSlug: false,
        isUpgrading: false,

        init() {
            this.loadOriginalValues();
            this.checkInitialSlugEditability();
            StoreUtils.debug('EditStore initialized');
        },

        /**
         * Carga valores originales
         */
        loadOriginalValues() {
            const getValue = (id) => document.getElementById(id)?.value || '';
            
            this.originalPlanId = getValue('original_plan_id');
            this.originalPlanSlug = getValue('original_plan_slug');
            this.originalSlug = getValue('original_slug');
            
            this.selectedPlan = this.originalPlanId;
            this.slug = this.originalSlug;
        },

        /**
         * Verifica editabilidad inicial del slug
         */
        checkInitialSlugEditability() {
            // Verificar si el plan actual permite slug personalizado
            const select = document.querySelector('select[name="plan_id"]');
            if (!select) return;
            
            const currentOption = Array.from(select.options)
                .find(option => option.value === this.originalPlanId);
            
            if (currentOption) {
                const allowCustomSlug = currentOption.getAttribute('data-allow-custom') === 'true';
                this.canEditSlug = allowCustomSlug;
                console.log('🔧 EDIT STORE: Editabilidad inicial del slug:', allowCustomSlug);
            }
        },

        /**
         * Verifica cambios de plan
         */
        checkPlanChange() {
            const select = document.querySelector('select[name="plan_id"]');
            if (!select) return;
            
            const selectedOption = Array.from(select.options)
                .find(option => option.value === this.selectedPlan);
            
            if (!selectedOption) return;
            
            const allowCustomSlug = selectedOption.getAttribute('data-allow-custom') === 'true';
            const wasUpgrading = this.isUpgrading;
            
            console.log('🔧 EDIT STORE: Plan cambiado', {
                plan_id: this.selectedPlan,
                allow_custom_slug: allowCustomSlug,
                original_plan: this.originalPlanId
            });
            
            // Determinar si se puede editar slug
            this.canEditSlug = allowCustomSlug;
            
            // Determinar si es upgrade (cambio hacia plan que permite personalización)
            this.isUpgrading = (this.selectedPlan !== this.originalPlanId && allowCustomSlug);
            
            // Si se cambió de plan que no permite personalización a uno que sí permite,
            // mantener el slug actual para que el usuario pueda editarlo
            if (!wasUpgrading && this.isUpgrading) {
                console.log('🔧 EDIT STORE: Upgrade detectado - slug editable');
                // Mantener el slug actual, no restaurar
            } else if (!allowCustomSlug) {
                console.log('🔧 EDIT STORE: Plan no permite personalización - slug readonly');
                // Si el plan no permite personalización, restaurar slug original
                this.slug = this.originalSlug;
            }
        }
    }));

    StoreUtils.debug('All Alpine components registered successfully');
}); 