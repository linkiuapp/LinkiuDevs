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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // Relación con el pedido
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Información del producto (histórica)
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_name'); // Nombre del producto al momento de la compra
            $table->decimal('product_price', 10, 2); // Precio del producto al momento de la compra
            
            // Cantidad y total del item
            $table->integer('quantity')->default(1);
            $table->decimal('item_total', 10, 2); // product_price * quantity
            
            // Variantes del producto (JSON)
            $table->json('variant_details')->nullable(); // {"talla": "M", "color": "Rojo", "precio_modificador": 5000}
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['order_id']);
            $table->index(['product_id']);
            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
}; 