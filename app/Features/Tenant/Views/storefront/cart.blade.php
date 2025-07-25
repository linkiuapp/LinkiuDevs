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
               class="bg-primary-300 hover:bg-primary-200 text-white-50 px-6 py-3 rounded-lg font-medium transition-colors">
                Ver productos
            </a>
        </div>

        <!-- Cart items -->
        <div id="cart-items" class="space-y-3 hidden">
            <!-- Items will be loaded here -->
        </div>

        <!-- Cart summary -->
        <div id="cart-summary" class="bg-white-50 rounded-lg p-4 border border-white-200 hidden">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-black-400">Subtotal:</span>
                    <span id="cart-subtotal" class="font-semibold text-black-500">$0</span>
                </div>
                <div class="border-t border-white-200 pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-black-500">Total:</span>
                        <span id="cart-total" class="text-lg font-bold text-primary-300">$0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div id="cart-actions" class="space-y-3 hidden">
            <button id="checkout-btn" 
                    class="w-full bg-success-300 hover:bg-success-200 text-white-50 py-3 rounded-lg font-semibold transition-colors">
                Proceder al Checkout
            </button>
            <button id="clear-cart-btn" 
                    class="w-full bg-error-300 hover:bg-error-200 text-white-50 py-2 rounded-lg font-medium transition-colors">
                Vaciar Carrito
            </button>
        </div>
    </div>
</div>

<!-- Template para items del carrito -->
<template id="cart-item-template">
    <div class="cart-item bg-white-50 rounded-lg p-4 border border-white-200">
        <div class="flex items-center gap-3">
            <!-- Imagen del producto -->
            <div class="w-16 h-16 bg-white-100 rounded-lg overflow-hidden flex-shrink-0">
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
                <button class="quantity-decrease bg-white-200 hover:bg-white-300 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                    <x-solar-square-alt-arrow-left-outline class="w-4 h-4 text-black-400" />
                </button>
                <span class="item-quantity font-semibold text-black-500 min-w-[2rem] text-center"></span>
                <button class="quantity-increase bg-white-200 hover:bg-white-300 w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                    <x-solar-square-alt-arrow-right-outline class="w-4 h-4 text-black-400" />
                </button>
            </div>
            
            <!-- Botón eliminar -->
            <button class="remove-item text-error-300 hover:text-error-200 p-2 transition-colors">
                <x-solar-trash-bin-trash-outline class="w-5 h-5" />
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
        // Configurar imagen del producto
        const imageElement = itemElement.querySelector('.item-image');
        if (item.product.main_image_url) {
            imageElement.src = item.product.main_image_url;
        } else {
            imageElement.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyMEg0NFY0NEgyMFYyMFoiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPHBhdGggZD0iTTI4IDI4TDM2IDM2IiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo=';
        }
        itemElement.querySelector('.item-image').alt = item.product.name;
        itemElement.querySelector('.item-name').textContent = item.product.name;
        itemElement.querySelector('.item-variants').textContent = item.variant_display || '';
        // Usar el precio unitario correcto (incluye modificadores de variantes)
        const unitPrice = item.product_price + (item.variant_details?.precio_modificador || 0);
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
                // Actualizar carrito flotante
                if (window.cart) {
                    window.cart.syncWithServer();
                }
            } else {
                showError(data.message || 'Error al actualizar cantidad');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            showError('Error al actualizar cantidad');
        }
    }

    async function removeItem(itemKey) {
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
                loadCart();
                // Actualizar carrito flotante
                if (window.cart) {
                    window.cart.updateCartDisplay();
                }
            } else {
                showError(data.message || 'Error al eliminar producto');
            }
        } catch (error) {
            console.error('Error removing item:', error);
            showError('Error de conexión');
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
                    // Actualizar carrito flotante
                    if (window.cart) {
                        window.cart.updateCartDisplay();
                    }
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
                showError('Error al vaciar carrito');
            }
        }
    });

    // Event listener para checkout
    document.getElementById('checkout-btn').addEventListener('click', function() {
        window.location.href = '{{ route("tenant.checkout.create", $store->slug) }}';
    });

    function formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    }

    function showError(message) {
        // Mostrar notificación de error
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-error-300 text-white-50 px-4 py-2 rounded-lg shadow-lg z-50';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
});
</script>
@endpush
@endsection