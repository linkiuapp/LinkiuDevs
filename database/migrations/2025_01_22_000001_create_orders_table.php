<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Información básica del pedido
            $table->string('order_number')->unique(); // Único globalmente
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 'shipped', 'delivered', 'cancelled'
            ])->default('pending');
            
            // Información del cliente
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->string('department');
            $table->string('city');
            
            // Tipo de entrega y costos
            $table->enum('delivery_type', ['domicilio', 'pickup']);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            
            // Método de pago y comprobante
            $table->enum('payment_method', ['transferencia', 'contra_entrega', 'efectivo']);
            $table->string('payment_proof_path')->nullable(); // Ruta del comprobante
            
            // Montos y totales
            $table->decimal('subtotal', 10, 2); // Suma de productos
            $table->decimal('coupon_discount', 10, 2)->default(0); // Descuento por cupón
            $table->decimal('total', 10, 2); // subtotal + shipping - coupon_discount
            
            // Notas adicionales
            $table->text('notes')->nullable();
            
            // Relación con tienda
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'created_at']);
            $table->index(['store_id', 'customer_phone']);
            $table->index(['store_id', 'customer_name']);
            $table->index(['order_number']);
            $table->index(['status']);
            $table->index(['payment_method']);
            $table->index(['delivery_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
}; 