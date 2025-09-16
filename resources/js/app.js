
console.log('🟢 Starting app.js execution...');

import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'

console.log('🟢 Imports loaded successfully');

import Pusher from 'pusher-js'

// Sistema de carrito - Cargar siempre, pero que cart.js decida si inicializar
import('./cart.js').then(() => {
    console.log('🛒 Cart.js loaded');
}).catch(error => {
    console.log('❌ Cart.js failed to load:', error.message);
});

console.log('🟢 Pusher imported successfully');

// Configurar Pusher DIRECTAMENTE (sin Laravel Echo para evitar auth automática)
console.log('🚀 Initializing Pusher...');
console.log('📊 VITE_PUSHER_APP_KEY:', import.meta.env.VITE_PUSHER_APP_KEY);
console.log('📊 VITE_PUSHER_APP_CLUSTER:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

try {
    window.Pusher = Pusher
    console.log('🟢 Pusher class assigned to window');
    
    window.pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        maxReconnectionAttempts: 3
    })
    
    console.log('✅ Pusher initialized successfully:', window.pusher);
    
    // Crear objeto Echo-like para compatibilidad
    window.Echo = {
        channel: function(channelName) {
            console.log('📡 Subscribing to channel:', channelName);
            const channel = window.pusher.subscribe(channelName)
            return {
                listen: function(eventName, callback) {
                    // Remover el punto inicial si existe (.ticket.response.added -> ticket.response.added)
                    const cleanEventName = eventName.startsWith('.') ? eventName.substring(1) : eventName
                    console.log('👂 Listening for event:', cleanEventName);
                    channel.bind(cleanEventName, callback)
                    return this
                }
            }
        }
    }
    
    console.log('✅ Echo-like object created:', window.Echo);
    
} catch (error) {
    console.error('❌ Error initializing Pusher:', error);
}

console.log('🟢 About to import component files...');

// Importar archivos de componentes y funcionalidades
try {
    import('./components.js')
    console.log('🟢 components.js imported');
    
    import('./navbar.js')
    console.log('🟢 navbar.js imported');
    
    import('./sidebar.js')
    console.log('🟢 sidebar.js imported');
    
    import('./envios.js')
    console.log('🟢 envios.js imported');
    
    import('./tickets.js')
    console.log('🟢 tickets.js imported');
    
    import('./store.js')
    console.log('🟢 store.js imported');
    
    import('./components/wizard-navigation.js')
    console.log('🟢 wizard-navigation.js imported');
    
    import('./components/wizard-step.js')
    console.log('🟢 wizard-step.js imported');
    
    import('./components/wizard-state-manager.js')
    console.log('🟢 wizard-state-manager.js imported');
    
} catch (error) {
    console.error('❌ Error importing component files:', error);
}

console.log('🟢 Setting up Alpine...');

// DEFINICIONES SIMPLES PARA EVITAR ERRORES
document.addEventListener('alpine:init', () => {
    // Definir storeManagement
    Alpine.data('storeManagement', () => ({
        selectedStores: [],
        showDeleteModal: false,
        deleteStoreId: null,
        deleteStoreName: '',
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        
        showNotificationMessage(message, type = 'success') {
            this.notificationMessage = message;
            this.notificationType = type;
            this.showNotification = true;
            setTimeout(() => { this.showNotification = false; }, 5000);
        },
        
        openDeleteModal(storeIdentifier, storeName) {
            console.log('🗑️ MODAL DELETE: Abriendo modal para tienda:', storeName, 'Identifier:', storeIdentifier);
            this.deleteStoreId = storeIdentifier; // Puede ser ID o slug
            this.deleteStoreName = storeName;
            this.showDeleteModal = true;
        },
        
        closeDeleteModal() {
            console.log('🗑️ MODAL DELETE: Cerrando modal');
            this.showDeleteModal = false;
            this.deleteStoreId = null;
            this.deleteStoreName = '';
        },
        
        confirmDelete() {
            console.log('🗑️ MODAL DELETE: Confirmando eliminación de tienda ID:', this.deleteStoreId);
            if (this.deleteStoreId) {
                // Crear y enviar formulario de eliminación
                const form = document.createElement('form');
                form.method = 'POST';
                
                // Corregir URL: debe ir a /superlinkiu/stores/{id} no /stores/{id}
                const baseUrl = window.location.origin;
                form.action = `${baseUrl}/superlinkiu/stores/${this.deleteStoreId}`;
                
                console.log('🗑️ MODAL DELETE: URL generada:', form.action);
                
                // Token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                console.log('🔐 CSRF TOKEN: Meta tag encontrado:', csrfToken ? 'SÍ' : 'NO');
                if (csrfToken) {
                    console.log('🔐 CSRF TOKEN: Valor:', csrfToken.content);
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.content;
                    form.appendChild(csrfInput);
                } else {
                    console.error('❌ MODAL DELETE: Token CSRF no encontrado en meta tag');
                    // Intentar obtener de otro lugar
                    const altToken = document.querySelector('input[name="_token"]');
                    if (altToken) {
                        console.log('🔐 CSRF TOKEN: Encontrado en input alternativo');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = altToken.value;
                        form.appendChild(csrfInput);
                    } else {
                        console.error('❌ MODAL DELETE: No se encontró token CSRF en ningún lugar');
                        alert('Error: Token CSRF no encontrado. Recarga la página e intenta de nuevo.');
                        return;
                    }
                }
                
                // Método DELETE
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        },
        
        loginAsStore(storeId) {
            console.log('🔑 LOGIN AS STORE: Función llamada para tienda ID:', storeId);
            alert('Funcionalidad "Login como Admin de Tienda" aún no implementada.\n\nPROXIMO TODO: Implementar ruta y controlador para esta función.');
        },
        
        init() {
            console.log('🟢 STORE MANAGEMENT: Componente Alpine inicializado correctamente');
        }
    }));
});

Alpine.plugin(collapse)
window.Alpine = Alpine
Alpine.start()

console.log('🟢 Alpine started successfully')
