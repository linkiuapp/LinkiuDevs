
console.log('🟢 Starting app.js execution...');

import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'

console.log('🟢 Imports loaded successfully');

import Pusher from 'pusher-js'

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
        
        init() {
            // Vacío - sin errores
        }
    }));
});

Alpine.plugin(collapse)
window.Alpine = Alpine
Alpine.start()

console.log('🟢 Alpine started successfully')
