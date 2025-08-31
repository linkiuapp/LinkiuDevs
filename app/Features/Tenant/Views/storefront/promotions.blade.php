@extends('frontend.layouts.app')

@section('content')
<div class="p-4 space-y-6">
    <!-- Header -->
    <div class="space-y-3">
        <!-- Breadcrumbs -->
        <nav class="flex text-small font-regular text-info-300">
            <a href="{{ route('tenant.home', $store->slug) }}" class="hover:text-info-200 transition-colors">Inicio</a>
            <span class="mx-2">/</span>
            <span class="text-secondary-300 font-medium">Promociones</span>
        </nav>
        
        <!-- Title -->
        <div class="space-y-2">
            <h7 class="text-h7 font-bold text-black-300">🎉 Promociones Activas</h7>
            <p class="text-body-small font-regular text-black-200">Aprovecha estas ofertas especiales</p>
        </div>
    </div>

    @if($coupons->count() > 0)
        <!-- Lista de promociones -->
        <div class="space-y-4">
            @foreach($coupons as $coupon)
                <div class="bg-accent-50 rounded-lg border border-accent-200 overflow-hidden shadow-sm" 
                     x-data="{ copied: false }">
                    
                    <!-- Header del cupón con descuento destacado -->
                    <div class="bg-gradient-to-r from-primary-300 to-secondary-300 p-4 text-accent-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <!-- Descuento principal -->
                                <div class="text-2xl font-bold mb-1">
                                    @if($coupon->discount_type === 'percentage')
                                        {{ $coupon->formatted_discount }} OFF
                                    @else
                                        {{ $coupon->formatted_discount }} OFF
                                    @endif
                                </div>
                                
                                <!-- Nombre del cupón -->
                                <div class="text-sm opacity-90">{{ $coupon->name }}</div>
                            </div>
                            
                            <!-- Badge de estado -->
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $coupon->status_info['bg'] }} {{ $coupon->status_info['color'] }} border {{ $coupon->status_info['border'] }}">
                                    {{ $coupon->status_info['text'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido del cupón -->
                    <div class="p-4 space-y-4">
                        <!-- Código del cupón -->
                        <div class="text-center">
                            <div class="text-sm text-black-300 mb-2">Código de descuento</div>
                            <div class="bg-accent-100 border-2 border-dashed border-primary-200 rounded-lg p-3 flex items-center justify-between">
                                <span class="font-mono text-lg font-bold text-primary-300">{{ $coupon->code }}</span>
                                <button @click="
                                    navigator.clipboard.writeText('{{ $coupon->code }}');
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                " 
                                class="bg-primary-300 hover:bg-primary-200 text-accent-50 px-3 py-1 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                    <template x-if="!copied">
                                        <div class="flex items-center gap-2">
                                            <x-solar-copy-outline class="w-4 h-4" />
                                            <span>Copiar</span>
                                        </div>
                                    </template>
                                    <template x-if="copied">
                                        <div class="flex items-center gap-2 text-success-50">
                                            <x-solar-check-circle-outline class="w-4 h-4" />
                                            <span>¡Copiado!</span>
                                        </div>
                                    </template>
                                </button>
                            </div>
                        </div>

                        <!-- Descripción -->
                        @if($coupon->description)
                            <div class="text-sm text-black-300 text-center">
                                {{ $coupon->description }}
                            </div>
                        @endif

                        <!-- Aplicabilidad -->
                        @if($coupon->type !== 'global')
                            <div class="bg-info-50 border border-info-100 rounded-lg p-3">
                                <div class="flex items-center gap-2 text-info-300">
                                    <x-solar-info-circle-outline class="w-4 h-4 flex-shrink-0" />
                                    <span class="text-sm font-medium">
                                        @if($coupon->type === 'categories')
                                            Válido para categorías específicas
                                        @else
                                            Válido para productos específicos
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif

                        <!-- Condiciones y validez -->
                        <div class="space-y-2">
                            @if($coupon->conditions_text)
                                <div class="flex items-center gap-2 text-black-300">
                                    <x-solar-document-text-outline class="w-4 h-4 flex-shrink-0" />
                                    <span class="text-sm">{{ $coupon->conditions_text }}</span>
                                </div>
                            @endif

                            @if($coupon->expiry_text)
                                <div class="flex items-center gap-2 text-black-300">
                                    <x-solar-calendar-outline class="w-4 h-4 flex-shrink-0" />
                                    <span class="text-sm">{{ $coupon->expiry_text }}</span>
                                </div>
                            @endif

                            <!-- Días y horarios específicos -->
                            @if($coupon->days_of_week || $coupon->start_time || $coupon->end_time)
                                <div class="flex items-center gap-2 text-black-300">
                                    <x-solar-clock-circle-outline class="w-4 h-4 flex-shrink-0" />
                                    <span class="text-sm">
                                        @if($coupon->days_of_week)
                                            @php
                                                $dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                                                $selectedDays = collect($coupon->days_of_week)->map(fn($day) => $dayNames[$day] ?? $day);
                                            @endphp
                                            {{ $selectedDays->join(', ') }}
                                        @endif
                                        @if($coupon->start_time || $coupon->end_time)
                                            {{ $coupon->start_time ? $coupon->start_time->format('H:i') : '00:00' }} - 
                                            {{ $coupon->end_time ? $coupon->end_time->format('H:i') : '23:59' }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Call to action -->
                        <div class="pt-2">
                            <div class="bg-success-50 border border-success-100 rounded-lg p-3 text-center">
                                <div class="text-success-300 text-sm font-medium">
                                    ✨ Copia el código y úsalo en tu próxima compra
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Información adicional -->
        <div class="bg-accent-100 rounded-lg p-4 border border-accent-200">
            <h3 class="font-semibold text-black-400 mb-2">📋 Cómo usar tus cupones</h3>
            <div class="space-y-2 text-sm text-black-300">
                <div class="flex items-start gap-2">
                    <span class="font-bold text-primary-300">1.</span>
                    <span>Copia el código de descuento tocando el botón "Copiar"</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="font-bold text-primary-300">2.</span>
                    <span>Agrega productos a tu carrito de compras</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="font-bold text-primary-300">3.</span>
                    <span>En el checkout, pega el código en el campo "Cupón de descuento"</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="font-bold text-primary-300">4.</span>
                    <span>¡Disfruta tu descuento! 🎉</span>
                </div>
            </div>
        </div>

    @else
        <!-- Estado vacío -->
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-secondary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <x-lucide-ticket class="w-10 h-10 text-secondary-200" />
            </div>
            <h3 class="text-h7 font-bold text-black-300 mb-2">No hay promociones activas</h3>
            <p class="text-body-small font-regular text-black-200 mb-6">
                Por el momento no tenemos promociones disponibles, pero mantente atento porque pronto habrán nuevas ofertas.
            </p>
            
            <!-- Botón para continuar comprando -->
            <a href="{{ route('tenant.home', $store->slug) }}" 
               class="inline-flex items-center gap-2 bg-primary-300 hover:bg-primary-200 text-accent-50 px-6 py-3 rounded-lg font-medium transition-colors">
                <x-lucide-shopping-cart class="w-5 h-5" />
                Continuar comprando
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-scroll to copied coupon for better UX
    document.addEventListener('alpine:init', () => {
        Alpine.data('couponCard', () => ({
            copied: false,
            
            async copyCoupon(code) {
                try {
                    await navigator.clipboard.writeText(code);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                    
                    // Opcional: mostrar toast notification
                    // this.showToast('Código copiado exitosamente');
                } catch (err) {
                    console.error('Error al copiar:', err);
                    // Fallback para dispositivos que no soportan clipboard API
                    this.fallbackCopy(code);
                }
            },
            
            fallbackCopy(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        }));
    });
</script>
@endpush
@endsection 