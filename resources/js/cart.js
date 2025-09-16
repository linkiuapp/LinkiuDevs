// Sistema de carrito unificado con Session (servidor)
class Cart {
    constructor() {
        this.items = []; // Solo para cache local temporal
        
        // Verificar que estamos en el contexto correcto
        if (!this.isValidContext()) {
            console.log('ℹ️ Cart: Invalid context, skipping initialization');
            return;
        }
        
        try {
            this.initializeEvents();
            this.syncWithServer();
        } catch (error) {
            console.error('❌ Cart: Error during initialization:', error);
        }
    }
    
    // Verificar que estamos en un contexto válido para el carrito
    isValidContext() {
        // Verificar que tenemos los elementos necesarios del DOM
        const hasCSRFToken = document.querySelector('meta[name="csrf-token"]') !== null;
        const isStorefront = !window.location.pathname.includes('/admin') && 
                            !window.location.pathname.includes('/superlinkiu');
        
        return hasCSRFToken && isStorefront;
    }

    // Ya no usamos LocalStorage, todo se maneja en servidor
    // Mantenemos cache local solo para UI responsiva
    loadCart() {
        // Cache local solo temporal, la fuente de verdad es el servidor
        return this.items;
    }

    // Sincronizar con servidor en lugar de guardar localmente
    saveCart() {
        // No guardamos en LocalStorage, confiamos en el servidor
        this.updateCartDisplay();
    }

    // Agregar producto al carrito
    async addProduct(product) {
        try {
            const url = this.getCartAddUrl();
            console.log('🛒 Adding product to cart:', product);
            console.log('🌐 Cart URL:', url);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }
            
            // Enviar al servidor (única fuente de verdad)
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content
                },
                body: JSON.stringify({
                    product_id: product.id,
                    quantity: 1,
                    variants: product.variants || null
                })
            });

            console.log('🌐 Response status:', response.status);
            console.log('🌐 Response ok:', response.ok);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Response error:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            
            const data = await response.json();
            console.log('📦 Response data:', data);
            
            if (data.success) {
                // Solo mostrar feedback y actualizar UI con datos del servidor
                this.showAddedFeedback(product.name);
                this.updateCartDisplayFromServer(data);
            } else {
                console.error('❌ Server error:', data.message);
                this.showError(data.message || 'Error al agregar producto');
            }
        } catch (error) {
            console.error('❌ Cart error:', error);
            const isNetworkError = !navigator.onLine || error.name === 'NetworkError' || error.message.includes('fetch');
            this.showError('Error al agregar producto: ' + error.message, isNetworkError);
        }
    }

    // Remover producto del carrito (ahora vía servidor)
    async removeProduct(itemKey) {
        try {
            const response = await fetch(this.getCartRemoveUrl(), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ item_key: itemKey })
            });

            const data = await response.json();
            if (data.success) {
                this.updateCartDisplayFromServer(data);
            } else {
                this.showError(data.message || 'Error al eliminar producto');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            const isNetworkError = !navigator.onLine || error.name === 'NetworkError' || error.message.includes('fetch');
            this.showError('Error al eliminar producto', isNetworkError);
        }
    }

    // Actualizar cantidad de producto (ahora vía servidor)
    async updateQuantity(itemKey, quantity) {
        if (quantity <= 0) {
            this.removeProduct(itemKey);
            return;
        }

        try {
            const response = await fetch(this.getCartUpdateUrl(), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    item_key: itemKey,
                    quantity: quantity
                })
            });

            const data = await response.json();
            if (data.success) {
                this.updateCartDisplayFromServer(data);
            } else {
                this.showError(data.message || 'Error al actualizar cantidad');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            const isNetworkError = !navigator.onLine || error.name === 'NetworkError' || error.message.includes('fetch');
            this.showError('Error al actualizar cantidad', isNetworkError);
        }
    }

    // Limpiar carrito (ahora vía servidor)
    async clearCart() {
        try {
            const response = await fetch(this.getCartClearUrl(), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            if (data.success) {
                this.updateCartDisplayFromServer(data);
            } else {
                this.showError(data.message || 'Error al vaciar carrito');
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            const isNetworkError = !navigator.onLine || error.name === 'NetworkError' || error.message.includes('fetch');
            this.showError('Error al vaciar carrito', isNetworkError);
        }
    }

    // Obtener total de productos (ahora desde el servidor)
    getTotalItems() {
        // Ya no usamos this.items, confiamos en los datos del servidor
        // Este método se mantiene para compatibilidad pero debería usarse updateCartDisplayFromServer
        const badge = document.querySelector('.cart-badge');
        return badge ? parseInt(badge.textContent) || 0 : 0;
    }

    // Obtener total del precio (ahora desde el servidor)
    getTotalPrice() {
        // Ya no calculamos localmente, confiamos en los datos del servidor
        const priceText = document.querySelector('.cart-total-price');
        if (priceText) {
            const price = priceText.textContent.replace(/[^\d]/g, '');
            return parseInt(price) || 0;
        }
        return 0;
    }

    // Actualizar display del carrito flotante
    updateCartDisplay() {
        const cartFloat = document.getElementById('cart-float');
        if (!cartFloat) return;

        const totalItems = this.getTotalItems();
        const totalPrice = this.getTotalPrice();

        // Actualizar badge contador
        const badge = cartFloat.querySelector('.cart-badge');
        if (badge) {
            badge.textContent = totalItems;
            badge.style.display = totalItems > 0 ? 'flex' : 'none';
        }

        // Actualizar texto de cantidad
        const countText = cartFloat.querySelector('.cart-count-text');
        if (countText) {
            countText.textContent = totalItems === 1 ? '1 producto' : `${totalItems} productos`;
        }

        // Actualizar precio total
        const priceText = cartFloat.querySelector('.cart-total-price');
        if (priceText) {
            priceText.textContent = `$${this.formatPrice(totalPrice)}`;
        }

        // Mostrar carrito (siempre visible)
        cartFloat.classList.remove('hidden');
    }

    // Formatear precio
    formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    }

    // Sincronizar con el servidor al cargar
    async syncWithServer() {
        try {
            const response = await fetch(this.getCartGetUrl());
            const data = await response.json();
            
            if (data.success) {
                this.updateCartDisplayFromServer(data);
            } else {
                // Si no hay carrito en servidor, mostrar carrito vacío
                this.updateCartDisplayFromServer({
                    cart_count: 0,
                    formatted_cart_total: '$0'
                });
            }
        } catch (error) {
            console.error('Error syncing with server:', error);
            // En caso de error, mostrar carrito vacío en lugar de usar localStorage
            this.updateCartDisplayFromServer({
                cart_count: 0,
                formatted_cart_total: '$0'
            });
        }
    }

    // Obtener URL para agregar al carrito
    getCartAddUrl() {
        // Intentar obtener el slug de la tienda desde el meta tag primero
        const storeSlugFromMeta = document.querySelector('meta[name="store-slug"]');
        if (storeSlugFromMeta) {
            return `/${storeSlugFromMeta.content}/carrito/agregar`;
        }
        
        // Fallback: obtener desde la URL
        const pathParts = window.location.pathname.split('/').filter(part => part);
        const storeSlug = pathParts[0] || '';
        
        if (!storeSlug) {
            console.error('No se pudo determinar el slug de la tienda desde URL:', window.location.pathname);
            throw new Error('No se pudo determinar el slug de la tienda');
        }
        
        return `/${storeSlug}/carrito/agregar`;
    }

    // Obtener URL para obtener carrito
    getCartGetUrl() {
        const pathParts = window.location.pathname.split('/').filter(part => part);
        const storeSlug = pathParts[0] || '';
        
        if (!storeSlug) {
            throw new Error('No se pudo determinar el slug de la tienda');
        }
        
        return `/${storeSlug}/carrito/contenido`;
    }

    // Obtener URL para actualizar carrito
    getCartUpdateUrl() {
        const pathParts = window.location.pathname.split('/').filter(part => part);
        const storeSlug = pathParts[0] || '';
        
        if (!storeSlug) {
            throw new Error('No se pudo determinar el slug de la tienda');
        }
        
        return `/${storeSlug}/carrito/actualizar`;
    }

    // Obtener URL para eliminar del carrito
    getCartRemoveUrl() {
        const pathParts = window.location.pathname.split('/').filter(part => part);
        const storeSlug = pathParts[0] || '';
        
        if (!storeSlug) {
            throw new Error('No se pudo determinar el slug de la tienda');
        }
        
        return `/${storeSlug}/carrito/eliminar`;
    }

    // Obtener URL para limpiar carrito
    getCartClearUrl() {
        const pathParts = window.location.pathname.split('/').filter(part => part);
        const storeSlug = pathParts[0] || '';
        
        if (!storeSlug) {
            throw new Error('No se pudo determinar el slug de la tienda');
        }
        
        return `/${storeSlug}/carrito/limpiar`;
    }

    // Actualizar display con datos del servidor
    updateCartDisplayFromServer(serverData) {
        const cartFloat = document.getElementById('cart-float');
        if (!cartFloat) return;

        // Actualizar badge contador
        const badge = cartFloat.querySelector('.cart-badge');
        if (badge) {
            badge.textContent = serverData.cart_count || 0;
            badge.style.display = serverData.cart_count > 0 ? 'flex' : 'none';
        }

        // Actualizar texto de cantidad
        const countText = cartFloat.querySelector('.cart-count-text');
        if (countText) {
            const count = serverData.cart_count || 0;
            countText.textContent = count === 1 ? '1 producto' : `${count} productos`;
        }

        // Actualizar precio total
        const priceText = cartFloat.querySelector('.cart-total-price');
        if (priceText) {
            priceText.textContent = serverData.formatted_cart_total || '$0';
        }

        // Mostrar carrito (siempre visible)
        cartFloat.classList.remove('hidden');
    }

    // Mostrar feedback cuando se agrega producto
    showAddedFeedback(productName) {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-success-300 text-accent-50 px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm font-medium">Agregado al carrito</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Animar salida y eliminar
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 2000);
    }

    // Mostrar error con manejo robusto
    showError(message, isNetworkError = false) {
        // Evitar spam de notificaciones
        const existingNotification = document.querySelector('.cart-error-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Determinar el tipo de error y mensaje apropiado
        let finalMessage = message;
        let iconPath = "M6 18L18 6M6 6l12 12"; // X icon (default)
        
        if (isNetworkError) {
            finalMessage = "Sin conexión. Verifica tu internet e intenta nuevamente.";
            iconPath = "M18.364 5.636l-12.728 12.728"; // Network icon
        } else if (message.includes('404')) {
            finalMessage = "Producto no encontrado. La página será actualizada.";
            setTimeout(() => window.location.reload(), 2000);
        } else if (message.includes('500')) {
            finalMessage = "Error del servidor. Intenta nuevamente en unos momentos.";
        } else if (message.includes('no disponible')) {
            finalMessage = "Producto agotado o no disponible.";
        }

        const notification = document.createElement('div');
        notification.className = 'cart-error-notification fixed top-4 right-4 bg-error-300 text-accent-50 px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full max-w-sm';
        notification.innerHTML = `
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
                </svg>
                <div class="flex-1">
                    <span class="text-sm font-medium block">${finalMessage}</span>
                    ${isNetworkError ? '<span class="text-xs opacity-75 block mt-1">Se reintentará automáticamente</span>' : ''}
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-accent-50 hover:text-accent-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto-hide después de más tiempo si es un error de red
        const hideTime = isNetworkError ? 5000 : 4000;
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, hideTime);
    }

    // Inicializar eventos
    initializeEvents() {
        // Eventos para botones "agregar al carrito"
        document.addEventListener('click', (e) => {
            if (e.target.closest('.add-to-cart-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.add-to-cart-btn');
                const productData = {
                    id: parseInt(btn.dataset.productId),
                    name: btn.dataset.productName,
                    price: parseFloat(btn.dataset.productPrice),
                    image: btn.dataset.productImage || null
                };
                
                this.addProduct(productData);
            }
        });

        // Evento para ir al carrito
        document.addEventListener('click', (e) => {
            if (e.target.closest('.view-cart-btn')) {
                e.preventDefault();
                window.location.href = e.target.closest('.view-cart-btn').href;
            }
        });
    }
}

// Función para inicializar el carrito
function initializeCart() {
    console.log('🚀 Attempting to initialize cart...');
    console.log('📄 Document ready state:', document.readyState);
    console.log('🌐 Current URL:', window.location.href);
    console.log('📍 Pathname:', window.location.pathname);
    
    // Verificar que estamos en una página de storefront usando el meta tag
    const storeSlugMeta = document.querySelector('meta[name="store-slug"]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const isAdminPage = window.location.pathname.includes('/admin');
    const isSuperLinkiuPage = window.location.pathname.includes('/superlinkiu');
    const isMainPage = window.location.pathname === '/';
    const isAuthPage = window.location.pathname.includes('/login') || window.location.pathname.includes('/register');
    
    // Es storefront si tiene store-slug meta tag y NO es una página admin/auth
    const isStorefront = storeSlugMeta && !isAdminPage && !isSuperLinkiuPage && !isMainPage && !isAuthPage;
    
    console.log('🔍 Cart initialization check:');
    console.log('  - Store slug meta exists:', !!storeSlugMeta);
    console.log('  - Store slug value:', storeSlugMeta?.content);
    console.log('  - CSRF token exists:', !!csrfToken);
    console.log('  - Is admin page:', isAdminPage);
    console.log('  - Is SuperLinkiu page:', isSuperLinkiuPage);
    console.log('  - Is main page:', isMainPage);
    console.log('  - Is auth page:', isAuthPage);
    console.log('  - Is storefront (final):', isStorefront);
    
    if (isStorefront) {
        try {
            window.cart = new Cart();
            console.log('✅ Cart initialized successfully');
            console.log('🛒 Cart instance:', window.cart);
        } catch (error) {
            console.error('❌ Error initializing cart:', error);
        }
    } else {
        console.log('ℹ️ Cart not initialized (not in storefront context)');
        console.log('   Reasons:');
        if (!storeSlugMeta) console.log('   - No store-slug meta tag found');
        if (isAdminPage) console.log('   - Is admin page');
        if (isSuperLinkiuPage) console.log('   - Is SuperLinkiu page');
        if (isMainPage) console.log('   - Is main page');
        if (isAuthPage) console.log('   - Is auth page');
    }
}

// Inicializar carrito de múltiples formas para asegurar que se ejecute
console.log('📦 Cart.js loaded, setting up initialization...');

// Método 1: Si el DOM ya está listo
if (document.readyState === 'loading') {
    console.log('📄 DOM still loading, waiting for DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', initializeCart);
} else {
    console.log('📄 DOM already ready, initializing immediately...');
    initializeCart();
}

// Método 2: Fallback con timeout
setTimeout(() => {
    if (!window.cart) {
        console.log('⏰ Fallback: attempting cart initialization after timeout...');
        initializeCart();
    }
}, 1000);

// Exponer funciones globales si es necesario (solo si el carrito está inicializado)
window.addToCart = function(productId, productName, productPrice, productImage) {
    if (window.cart && typeof window.cart.addProduct === 'function') {
        try {
            window.cart.addProduct({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage
            });
        } catch (error) {
            console.error('❌ Error adding to cart:', error);
        }
    } else {
        console.warn('⚠️ Cart not available or not initialized');
    }
};

window.removeFromCart = function(productId) {
    if (window.cart && typeof window.cart.removeProduct === 'function') {
        try {
            window.cart.removeProduct(productId);
        } catch (error) {
            console.error('❌ Error removing from cart:', error);
        }
    } else {
        console.warn('⚠️ Cart not available or not initialized');
    }
};

window.updateCartQuantity = function(productId, quantity) {
    if (window.cart && typeof window.cart.updateQuantity === 'function') {
        try {
            window.cart.updateQuantity(productId, quantity);
        } catch (error) {
            console.error('❌ Error updating cart quantity:', error);
        }
    } else {
        console.warn('⚠️ Cart not available or not initialized');
    }
};

window.clearCart = function() {
    if (window.cart && typeof window.cart.clearCart === 'function') {
        try {
            window.cart.clearCart();
        } catch (error) {
            console.error('❌ Error clearing cart:', error);
        }
    } else {
        console.warn('⚠️ Cart not available or not initialized');
    }
}; 