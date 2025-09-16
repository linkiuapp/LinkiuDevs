@extends('frontend.layouts.app')

@section('content')
<div class="px-4 py-6 space-y-6">
    <!-- Header del carrito -->
    <div class="text-center">
        <h1 class="text-2xl font-bold text-black-500 mb-2">Mi Carrito</h1>
        <p class="text-black-300">Revisa tus productos antes de continuar</p>
    </div>

    <!-- Contenedor del carrito -->
    <div id="cart-container" class="space-y-4">
        <!-- Loading state -->
        <div id="cart-loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-300 mx-auto"></div>
            <p class="text-black-300 mt-2">Cargando carrito...</p>
        </div>

        <!-- Empty state -->
        <div id="cart-empty" class="text-center py-12 hidden">
            <x-solar-bag-2-outline class="w-16 h-16 mx-auto mb-4 text-black-200" />
            <h3 class="text-lg font-semibold text-black-400 mb-2">Tu carrito está vacío</h3>
            <p class="text-black-300 mb-6">¡Agrega algunos productos deliciosos!</p>
            <a href="{{ route('tenant.home', $store->slug) }}" 
               class="bg-primary-300 hover:bg-primary-200 text-accent-50 px-6 py-3 rounded-lg font-medium transition-colors">
                Ver productos
            </a>
        </div>

        <!-- Cart items -->
        <div id="cart-items" class="space-y-3 hidden">
            <!-- Items will be loaded here -->
        </div>

        <!-- Cart summary -->
        <div id="cart-summary" class="bg-accent-50 rounded-lg p-4 border border-accent-200 hidden">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-black-400">Subtotal:</span>
                    <span id="cart-subtotal" class="font-semibold text-black-500">$0</span>
                </div>
                <div class="border-t border-accent-200 pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-black-500">Total:</span>
                        <span id="cart-total" class="text-lg font-bold text-primary-300">$0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div id="cart-actions" class="space-y-3 hidden">
            <a href="{{ route('tenant.checkout.create', $store->slug) }}" 
               id="checkout-btn" 
               class="block w-full bg-success-300 hover:bg-success-200 text-accent-50 py-3 rounded-lg font-semibold transition-colors text-center">
                Proceder al Checkout
            </a>
            <button id="clear-cart-btn" 
                    class="w-full bg-error-300 hover:bg-error-200 text-accent-50 py-2 rounded-lg font-medium transition-colors">
                Vaciar Carrito
            </button>
        </div>
    </div>
</div>

<!-- Template para items del carrito -->
<template id="cart-item-template">
    <div class="cart-item bg-accent-50 rounded-lg p-4 border border-accent-200">
        <div class="flex items-center gap-3">
            <!-- Imagen del producto -->
            <div class="w-16 h-16 bg-accent-100 rounded-lg overflow-hidden flex-shrink-0">
                <img class="item-image w-full h-full object-cover" src="" alt="">
            </div>
            
            <!-- Información del producto -->
            <div class="flex-1 min-w-0">
                <h3 class="item-name font-semibold text-black-500 truncate"></h3>
                <p class="item-variants text-sm text-black-300"></p>
                <p class="item-price text-primary-300 font-bold"></p>
            </div>
            
            <!-- Controles de cantidad -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <button class="quantity-decrease bg-accent-200 hover:bg-accent-300 w-8 h-8 rounded-full flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-solar-square-alt-arrow-left-outline class="w-4 h-4 text-black-400" />
                    <div class="loading-spinner hidden w-4 h-4 border-2 border-black-200 border-t-black-400 rounded-full animate-spin"></div>
                </button>
                <span class="item-quantity font-semibold text-black-500 min-w-[2rem] text-center"></span>
                <button class="quantity-increase bg-accent-200 hover:bg-accent-300 w-8 h-8 rounded-full flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-solar-square-alt-arrow-right-outline class="w-4 h-4 text-black-400" />
                    <div class="loading-spinner hidden w-4 h-4 border-2 border-black-200 border-t-black-400 rounded-full animate-spin"></div>
                </button>
            </div>
            
            <!-- Botón eliminar -->
            <button class="remove-item text-error-300 hover:text-error-200 p-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <x-solar-trash-bin-trash-outline class="w-5 h-5" />
                <div class="loading-spinner hidden w-4 h-4 border-2 border-error-200 border-t-error-300 rounded-full animate-spin"></div>
            </button>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartContainer = document.getElementById('cart-container');
    const cartLoading = document.getElementById('cart-loading');
    const cartEmpty = document.getElementById('cart-empty');
    const cartItems = document.getElementById('cart-items');
    const cartSummary = document.getElementById('cart-summary');
    const cartActions = document.getElementById('cart-actions');
    const itemTemplate = document.getElementById('cart-item-template');

    // Cargar carrito al iniciar
    loadCart();

    async function loadCart() {
        try {
            const response = await fetch('{{ route("tenant.cart.get", $store->slug) }}');
            const data = await response.json();

            if (data.success) {
                displayCart(data);
            } else {
                showError('Error al cargar el carrito');
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            showError('Error de conexión');
        }
    }

    function displayCart(data) {
        cartLoading.classList.add('hidden');

        if (data.items.length === 0) {
            cartEmpty.classList.remove('hidden');
            cartItems.classList.add('hidden');
            cartSummary.classList.add('hidden');
            cartActions.classList.add('hidden');
        } else {
            cartEmpty.classList.add('hidden');
            cartItems.classList.remove('hidden');
            cartSummary.classList.remove('hidden');
            cartActions.classList.remove('hidden');

            // Limpiar items existentes
            cartItems.innerHTML = '';

            // Agregar cada item
            data.items.forEach(item => {
                const itemElement = createCartItem(item);
                cartItems.appendChild(itemElement);
            });

            // Actualizar totales
            document.getElementById('cart-subtotal').textContent = data.formatted_total;
            document.getElementById('cart-total').textContent = data.formatted_total;
        }
    }

    function createCartItem(item) {
        const template = itemTemplate.content.cloneNode(true);
        const itemElement = template.querySelector('.cart-item');

        // Configurar datos del item
        itemElement.dataset.itemKey = item.key;
        // Configurar imagen del producto (compatible con nuevo y viejo formato)
        const imageElement = itemElement.querySelector('.item-image');
        const imageUrl = item.image_url || (item.product && item.product.main_image_url) || null;
        if (imageUrl) {
            imageElement.src = imageUrl;
        } else {
            imageElement.src = '{{ asset("assets/images/placeholder-product.svg") }}';
        }
        
        // Configurar datos (compatible con nuevo y viejo formato)
        const productName = item.product_name || (item.product && item.product.name) || 'Producto sin nombre';
        const productPrice = item.product_price || (item.product && item.product.price) || 0;
        
        itemElement.querySelector('.item-image').alt = productName;
        itemElement.querySelector('.item-name').textContent = productName;
        itemElement.querySelector('.item-variants').textContent = item.variant_display || '';
        
        // Usar el precio unitario correcto
        const unitPrice = productPrice + (item.variant_details?.precio_modificador || 0);
        itemElement.querySelector('.item-price').textContent = `$${formatPrice(unitPrice)}`;
        itemElement.querySelector('.item-quantity').textContent = item.quantity;

        // Event listeners
        itemElement.querySelector('.quantity-decrease').addEventListener('click', () => {
            updateQuantity(item.key, item.quantity - 1);
        });

        itemElement.querySelector('.quantity-increase').addEventListener('click', () => {
            updateQuantity(item.key, item.quantity + 1);
        });

        itemElement.querySelector('.remove-item').addEventListener('click', () => {
            removeItem(item.key);
        });

        return itemElement;
    }

    async function updateQuantity(itemKey, newQuantity) {
        if (newQuantity <= 0) {
            removeItem(itemKey);
            return;
        }

        // Mostrar loading state
        const itemElement = document.querySelector(`[data-item-key="${itemKey}"]`);
        if (itemElement) {
            showItemLoading(itemElement, 'quantity');
        }

        try {
            const response = await fetch('{{ route("tenant.cart.update", $store->slug) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    item_key: itemKey,
                    quantity: newQuantity
                })
            });

            const data = await response.json();
            if (data.success) {
                loadCart();
                // El carrito flotante se actualiza automáticamente vía cart.js
            } else {
                showError(data.message || 'Error al actualizar cantidad');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            showError('Error al actualizar cantidad');
        } finally {
            // Ocultar loading state
            if (itemElement) {
                hideItemLoading(itemElement, 'quantity');
            }
        }
    }

    async function removeItem(itemKey) {
        // ⚡ ELIMINACIÓN INSTANTÁNEA EN UI
        const itemElement = document.querySelector(`[data-item-key="${itemKey}"]`);
        if (itemElement) {
            // Animar eliminación instantánea
            itemElement.style.transform = 'translateX(-100%)';
            itemElement.style.opacity = '0';
            itemElement.style.transition = 'all 0.3s ease-out';
            
            // Remover del DOM después de la animación
            setTimeout(() => {
                itemElement.remove();
                // Verificar si el carrito quedó vacío
                checkIfCartEmpty();
            }, 300);
        }

        // Procesar eliminación en servidor en segundo plano
        try {
            const response = await fetch('{{ route("tenant.cart.remove", $store->slug) }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ item_key: itemKey })
            });

            const data = await response.json();
            if (data.success) {
                // Recargar carrito para actualizar totales correctos
                setTimeout(() => {
                    loadCart();
                }, 400);
                // El carrito flotante se actualiza automáticamente vía cart.js
            } else {
                // Si falla, mostrar error y recargar carrito para restaurar estado
                showError(data.message || 'Error al eliminar producto');
                setTimeout(() => {
                    loadCart();
                }, 500);
            }
        } catch (error) {
            console.error('Error removing item:', error);
            showError('Error de conexión');
            // Si falla, recargar carrito para restaurar estado
            setTimeout(() => {
                loadCart();
            }, 500);
        }
    }
    
    // Función para verificar si el carrito quedó vacío después de eliminación
    function checkIfCartEmpty() {
        const remainingItems = document.querySelectorAll('#cart-items .cart-item');
        if (remainingItems.length === 0) {
            cartEmpty.classList.remove('hidden');
            cartItems.classList.add('hidden');
            cartSummary.classList.add('hidden');
            cartActions.classList.add('hidden');
        }
    }

    // Event listener para vaciar carrito
    document.getElementById('clear-cart-btn').addEventListener('click', async function() {
        if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
            try {
                const response = await fetch('{{ route("tenant.cart.clear", $store->slug) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    loadCart();
                    // El carrito flotante se actualiza automáticamente vía cart.js
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
                showError('Error al vaciar carrito');
            }
        }
    });

    // Checkout button is now a direct link - no JavaScript needed

    function formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    }

    // Mostrar loading state en elemento específico
    function showItemLoading(itemElement, type) {
        if (type === 'quantity') {
            const decreaseBtn = itemElement.querySelector('.quantity-decrease');
            const increaseBtn = itemElement.querySelector('.quantity-increase');
            
            if (decreaseBtn && increaseBtn) {
                // Deshabilitar botones
                decreaseBtn.disabled = true;
                increaseBtn.disabled = true;
                
                // Ocultar íconos y mostrar spinners
                decreaseBtn.querySelector('svg').classList.add('hidden');
                increaseBtn.querySelector('svg').classList.add('hidden');
                decreaseBtn.querySelector('.loading-spinner').classList.remove('hidden');
                increaseBtn.querySelector('.loading-spinner').classList.remove('hidden');
            }
        } else if (type === 'remove') {
            const removeBtn = itemElement.querySelector('.remove-item');
            
            if (removeBtn) {
                // Deshabilitar botón
                removeBtn.disabled = true;
                
                // Ocultar ícono y mostrar spinner
                removeBtn.querySelector('svg').classList.add('hidden');
                removeBtn.querySelector('.loading-spinner').classList.remove('hidden');
            }
        }
    }

    // Ocultar loading state en elemento específico
    function hideItemLoading(itemElement, type) {
        if (type === 'quantity') {
            const decreaseBtn = itemElement.querySelector('.quantity-decrease');
            const increaseBtn = itemElement.querySelector('.quantity-increase');
            
            if (decreaseBtn && increaseBtn) {
                // Habilitar botones
                decreaseBtn.disabled = false;
                increaseBtn.disabled = false;
                
                // Mostrar íconos y ocultar spinners
                decreaseBtn.querySelector('svg').classList.remove('hidden');
                increaseBtn.querySelector('svg').classList.remove('hidden');
                decreaseBtn.querySelector('.loading-spinner').classList.add('hidden');
                increaseBtn.querySelector('.loading-spinner').classList.add('hidden');
            }
        } else if (type === 'remove') {
            const removeBtn = itemElement.querySelector('.remove-item');
            
            if (removeBtn) {
                // Habilitar botón
                removeBtn.disabled = false;
                
                // Mostrar ícono y ocultar spinner
                removeBtn.querySelector('svg').classList.remove('hidden');
                removeBtn.querySelector('.loading-spinner').classList.add('hidden');
            }
        }
    }

    function showError(message) {
        // Evitar spam de notificaciones
        const existingNotification = document.querySelector('.cart-page-error');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Mostrar notificación de error mejorada
        const notification = document.createElement('div');
        notification.className = 'cart-page-error fixed top-4 right-4 bg-error-300 text-accent-50 px-4 py-3 rounded-lg shadow-lg z-50 max-w-sm transform transition-all duration-300 translate-x-full';
        notification.innerHTML = `
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="flex-1">
                    <span class="text-sm font-medium">${message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-accent-50 hover:text-accent-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Auto-hide después de 4 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, 4000);
    }
});
</script>
@endpush
@endsection