// Sistema de carrito con LocalStorage
class Cart {
    constructor() {
        this.items = this.loadCart();
        this.initializeEvents();
        this.syncWithServer();
    }

    // Cargar carrito desde LocalStorage
    loadCart() {
        const saved = localStorage.getItem('linkiu_cart');
        return saved ? JSON.parse(saved) : [];
    }

    // Guardar carrito en LocalStorage
    saveCart() {
        localStorage.setItem('linkiu_cart', JSON.stringify(this.items));
        this.updateCartDisplay();
    }

    // Agregar producto al carrito
    async addProduct(product) {
        try {
            // Enviar al servidor primero
            const response = await fetch(this.getCartAddUrl(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: product.id,
                    quantity: 1
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Actualizar localStorage con datos del servidor
                const existingItem = this.items.find(item => item.id === product.id);
                
                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    this.items.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        image: product.image,
                        quantity: 1
                    });
                }
                
                this.saveCart();
                this.showAddedFeedback(product.name);
                
                // Actualizar display con datos del servidor
                this.updateCartDisplayFromServer(data);
            } else {
                this.showError(data.message || 'Error al agregar producto');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showError('Error de conexión');
        }
    }

    // Remover producto del carrito
    removeProduct(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.saveCart();
    }

    // Actualizar cantidad de producto
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeProduct(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
            }
        }
    }

    // Limpiar carrito
    clearCart() {
        this.items = [];
        this.saveCart();
    }

    // Obtener total de productos
    getTotalItems() {
        return this.items.reduce((total, item) => total + item.quantity, 0);
    }

    // Obtener total del precio
    getTotalPrice() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
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
                this.updateCartDisplay(); // Fallback a localStorage
            }
        } catch (error) {
            console.error('Error syncing with server:', error);
            this.updateCartDisplay(); // Fallback a localStorage
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

    // Mostrar error
    showError(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-error-300 text-white-50 px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="text-sm font-medium">${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
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