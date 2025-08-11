<!-- Carrito flotante centrado -->
<div id="cart-float" class="fixed bottom-4 left-1/2 transform -translate-x-1/2 w-full max-w-[480px] bg-white-50 border border-white-200 rounded-xl shadow-lg z-[1000] transition-all duration-300">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between gap-4">
            <!-- Icono carrito con badge -->
            <div class="relative flex-shrink-0">
                <div class="w-12 h-12 bg-primary-300 rounded-full flex items-center justify-center">
                    <x-solar-bag-2-outline class="w-6 h-6 text-white-50" />
                </div>
                <!-- Badge contador -->
                <div class="cart-badge absolute -top-1 -right-1 bg-error-300 text-white-50 text-xs font-bold rounded-full min-w-[20px] h-5 flex items-center justify-center px-1" style="display: none;">
                    0
                </div>
            </div>
            
            <!-- Información del carrito -->
            <div class="flex-1 min-w-0 text-center">
                <p class="cart-count-text text-sm font-medium text-black-400 mb-1">
                    0 productos
                </p>
                <p class="cart-total-price text-xl font-bold text-black-500">
                    $0
                </p>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex-shrink-0 flex gap-2">
                <a href="{{ route('tenant.cart.index', $store->slug ?? 'store') }}" 
                   class="view-cart-btn bg-white-200 hover:bg-white-300 text-black-500 px-4 py-3 rounded-xl text-sm font-medium transition-colors flex items-center gap-1">
                    <span>Ver</span>
                </a>
                <a href="{{ route('tenant.checkout.create', $store->slug ?? 'store') }}" 
                   class="checkout-btn bg-secondary-300 hover:bg-secondary-200 text-white-50 px-4 py-3 rounded-xl text-sm font-semibold transition-colors flex items-center gap-1">
                    <span>Comprar</span>
                    <x-solar-arrow-right-outline class="w-4 h-4" />
                </a>
            </div>
        </div>
        
        <!-- Estado vacío (mensaje alentador) -->
        <div class="cart-empty-state mt-3" style="display: none;">
            <p class="text-xs text-black-300 text-center">
                ¡Agrega productos para empezar tu pedido! 🛒
            </p>
        </div>
    </div>
</div>

<!-- Spacer para que el contenido no quede oculto debajo del carrito -->
<div class="h-24"></div>

<script>
    // Script adicional para mejorar la experiencia del carrito
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar/ocultar mensaje de carrito vacío
        function updateEmptyState() {
            const cartFloat = document.getElementById('cart-float');
            const emptyState = cartFloat?.querySelector('.cart-empty-state');
            const badge = cartFloat?.querySelector('.cart-badge');
            
            if (emptyState && badge) {
                const isEmpty = badge.style.display === 'none';
                emptyState.style.display = isEmpty ? 'block' : 'none';
            }
        }
        
        // Observar cambios en el badge para actualizar estado vacío
        const observer = new MutationObserver(updateEmptyState);
        const badge = document.querySelector('.cart-badge');
        
        if (badge) {
            observer.observe(badge, { 
                attributes: true, 
                attributeFilter: ['style'] 
            });
        }
        
        // Animación del badge cuando se actualiza (usando Tailwind)
        let lastBadgeCount = 0;
        setInterval(() => {
            const badge = document.querySelector('.cart-badge');
            if (badge && badge.textContent) {
                const currentCount = parseInt(badge.textContent);
                if (currentCount !== lastBadgeCount && currentCount > 0) {
                    badge.classList.add('animate-pulse');
                    setTimeout(() => badge.classList.remove('animate-pulse'), 600);
                    lastBadgeCount = currentCount;
                }
            }
        }, 500);
        
        // Llamar función inicial
        updateEmptyState();
    });
</script> 