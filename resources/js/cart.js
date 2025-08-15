// Sistema de carrito unificado con Session (servidor)
class Cart {
    constructor() {
        this.items = []; // Solo para cache local temporal
        this.initializeEvents();
        this.syncWithServer();
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
            // Enviar al servidor (única fuente de verdad)
            const response = await fetch(this.getCartAddUrl(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: product.id,
                    quantity: 1,
                    variants: product.variants || null
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Solo mostrar feedback y actualizar UI con datos del servidor
                this.showAddedFeedback(product.name);
                this.updateCartDisplayFromServer(data);
            } else {
                this.showError(data.message || 'Error al agregar producto');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            const isNetworkError = !navigator.onLine || error.name === 'NetworkError' || error.message.includes('fetch');
            this.showError('Error al agregar producto', isNetworkError);
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
        const storeSlug = window.location.pathname.split('/')[1];
        return `/${storeSlug}/carrito/agregar`;
    }

    // Obtener URL para obtener carrito
    getCartGetUrl() {
        const storeSlug = window.location.pathname.split('/')[1];
        return `/${storeSlug}/carrito/contenido`;
    }

    // Obtener URL para actualizar carrito
    getCartUpdateUrl() {
        const storeSlug = window.location.pathname.split('/')[1];
        return `/${storeSlug}/carrito/actualizar`;
    }

    // Obtener URL para eliminar del carrito
    getCartRemoveUrl() {
        const storeSlug = window.location.pathname.split('/')[1];
        return `/${storeSlug}/carrito/eliminar`;
    }

    // Obtener URL para limpiar carrito
    getCartClearUrl() {
        const storeSlug = window.location.pathname.split('/')[1];
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
        notification.className = 'fixed top-4 right-4 bg-success-300 text-white-50 px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
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
        notification.className = 'cart-error-notification fixed top-4 right-4 bg-error-300 text-white-50 px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full max-w-sm';
        notification.innerHTML = `
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
                </svg>
                <div class="flex-1">
                    <span class="text-sm font-medium block">${finalMessage}</span>
                    ${isNetworkError ? '<span class="text-xs opacity-75 block mt-1">Se reintentará automáticamente</span>' : ''}
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white-50 hover:text-white-200">
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

// Inicializar carrito cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    window.cart = new Cart();
});

// Exponer funciones globales si es necesario
window.addToCart = function(productId, productName, productPrice, productImage) {
    if (window.cart) {
        window.cart.addProduct({
            id: productId,
            name: productName,
            price: productPrice,
            image: productImage
        });
    }
};

window.removeFromCart = function(productId) {
    if (window.cart) {
        window.cart.removeProduct(productId);
    }
};

window.updateCartQuantity = function(productId, quantity) {
    if (window.cart) {
        window.cart.updateQuantity(productId, quantity);
    }
};

window.clearCart = function() {
    if (window.cart) {
        window.cart.clearCart();
    }
}; 