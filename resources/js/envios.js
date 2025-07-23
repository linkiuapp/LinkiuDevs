/**
 * =============================================================================
 * SHIPPING MANAGEMENT SYSTEM
 * =============================================================================
 * Sistema de gestión de métodos de envío y zonas para TenantAdmin
 * 
 * Funcionalidades:
 * - Toggle de métodos activos/inactivos
 * - Drag & Drop para ordenar métodos
 * - CRUD de zonas de envío
 * - Validaciones de límites por plan
 * - Calculadora de envío gratis
 * =============================================================================
 */

console.log('🚚 Shipping Management System loaded');

// =============================================================================
// SHIPPING MANAGEMENT - Alpine.js Components
// =============================================================================
document.addEventListener('alpine:init', () => {
    
    // =========================================================================
    // SHIPPING INDEX - Gestión principal de métodos
    // =========================================================================
    Alpine.data('shippingIndex', () => ({
        // State
        methods: [],
        maxZones: 0,
        currentZones: 0,
        planName: '',
        isDragging: false,
        
        // Inicialización
        init() {
            console.log('🚚 ShippingIndex component initialized');
            this.loadInitialData();
            this.initializeSortable();
        },
        
        // Cargar datos iniciales desde el DOM
        loadInitialData() {
            const dataEl = document.getElementById('shipping-data');
            if (dataEl) {
                const data = JSON.parse(dataEl.textContent);
                this.methods = data.methods || [];
                this.maxZones = data.maxZones || 1;
                this.currentZones = data.currentZones || 0;
                this.planName = data.planName || '';
            }
        },
        
        // Inicializar Sortable.js para drag & drop
        initializeSortable() {
            const container = document.getElementById('shipping-methods-container');
            if (!container || typeof Sortable === 'undefined') return;
            
            new Sortable(container, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'opacity-50',
                onStart: () => {
                    this.isDragging = true;
                },
                onEnd: (evt) => {
                    this.isDragging = false;
                    this.updateOrder();
                }
            });
        },
        
        // Toggle método activo/inactivo
        async toggleMethod(methodId) {
            try {
                const response = await fetch(this.getToggleUrl(methodId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Actualizar estado local
                    const method = this.methods.find(m => m.id === methodId);
                    if (method) {
                        method.is_active = data.is_active;
                    }
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification(data.message || 'Error al actualizar método', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('Error al actualizar método', 'error');
            }
        },
        
        // Actualizar orden después de drag & drop
        async updateOrder() {
            const items = document.querySelectorAll('[data-method-id]');
            const order = Array.from(items).map((item, index) => ({
                id: parseInt(item.dataset.methodId),
                sort_order: index + 1
            }));
            
            try {
                const response = await fetch(this.getUpdateOrderUrl(), {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ methods: order })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Orden actualizado', 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('Error al actualizar orden', 'error');
            }
        },
        
        // Helpers
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },
        
        getToggleUrl(methodId) {
            const storeSlug = window.location.pathname.split('/')[1];
            return `/${storeSlug}/admin/shipping/toggle-active/${methodId}`;
        },
        
        getUpdateOrderUrl() {
            const storeSlug = window.location.pathname.split('/')[1];
            return `/${storeSlug}/admin/shipping/update-order`;
        },
        
        showNotification(message, type = 'success') {
            // Implementar notificación (puede usar Alpine Store global)
            console.log(`${type}: ${message}`);
        },
        
        // Verificar si puede crear más zonas
        canCreateMoreZones() {
            return this.currentZones < this.maxZones;
        },
        
        // Obtener mensaje de límite
        getZoneLimitMessage() {
            return `Zonas de envío: ${this.currentZones}/${this.maxZones} (Plan ${this.planName})`;
        }
    })),
    
    // =========================================================================
    // SHIPPING METHOD FORM - Edición de método
    // =========================================================================
    Alpine.data('shippingMethodForm', () => ({
        // State
        method: {
            name: '',
            instructions: '',
            preparation_time: '1h',
            notification_enabled: false
        },
        
        // Inicialización
        init() {
            console.log('📝 ShippingMethodForm component initialized');
            this.loadMethodData();
        },
        
        // Cargar datos del método
        loadMethodData() {
            const dataEl = document.getElementById('method-data');
            if (dataEl) {
                this.method = JSON.parse(dataEl.textContent);
            }
        }
    })),
    
    // =========================================================================
    // SHIPPING ZONE FORM - Crear/Editar zona
    // =========================================================================
    Alpine.data('shippingZoneForm', () => ({
        // State
        zone: {
            name: '',
            description: '',
            cost: '',
            free_shipping_from: '',
            estimated_time: '2-4h',
            delivery_days: {
                'L': true,
                'M': true,
                'X': true,
                'J': true,
                'V': true,
                'S': true,
                'D': false
            },
            start_time: '09:00',
            end_time: '18:00',
            is_active: true
        },
        
        // Inicialización
        init() {
            console.log('📍 ShippingZoneForm component initialized');
            this.loadZoneData();
        },
        
        // Cargar datos de la zona
        loadZoneData() {
            const dataEl = document.getElementById('zone-data');
            if (dataEl) {
                const data = JSON.parse(dataEl.textContent);
                if (data.id) {
                    this.zone = data;
                }
            }
        },
        
        // Toggle día de entrega
        toggleDay(day) {
            this.zone.delivery_days[day] = !this.zone.delivery_days[day];
        },
        
        // Seleccionar todos los días
        selectAllDays() {
            Object.keys(this.zone.delivery_days).forEach(day => {
                this.zone.delivery_days[day] = true;
            });
        },
        
        // Deseleccionar todos los días
        deselectAllDays() {
            Object.keys(this.zone.delivery_days).forEach(day => {
                this.zone.delivery_days[day] = false;
            });
        },
        
        // Solo días laborales
        selectWeekdays() {
            this.zone.delivery_days = {
                'L': true,
                'M': true,
                'X': true,
                'J': true,
                'V': true,
                'S': false,
                'D': false
            };
        },
        
        // Formatear número para moneda
        formatCurrency(value) {
            // Eliminar caracteres no numéricos
            const number = value.replace(/\D/g, '');
            // Formatear con separador de miles
            return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        
        // Manejar input de moneda
        handleCurrencyInput(field) {
            const input = this.$refs[field];
            const value = input.value.replace(/\D/g, '');
            input.value = this.formatCurrency(value);
            this.zone[field] = value;
        },
        
        // Validar horarios
        validateTimes() {
            if (this.zone.start_time >= this.zone.end_time) {
                alert('La hora de inicio debe ser menor que la hora de fin');
                return false;
            }
            return true;
        },
        
        // Validar al menos un día seleccionado
        validateDays() {
            const hasSelectedDay = Object.values(this.zone.delivery_days).some(day => day);
            if (!hasSelectedDay) {
                alert('Debe seleccionar al menos un día de entrega');
                return false;
            }
            return true;
        },
        
        // Enviar formulario
        submitForm() {
            if (!this.validateTimes() || !this.validateDays()) {
                return false;
            }
            
            // El formulario se enviará normalmente
            return true;
        }
    }))
});

// =============================================================================
// UTILIDADES GLOBALES
// =============================================================================
window.ShippingUtils = {
    // Formatear precio
    formatPrice(amount) {
        return '$' + new Intl.NumberFormat('es-CO').format(amount);
    },
    
    // Obtener ícono de método
    getMethodIcon(type) {
        return type === 'domicilio' ? '🚚' : '🏪';
    }
}; 