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
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained('shipping_methods')->onDelete('cascade');
            $table->string('name'); // Ciudad Principal, Pueblos Aledaños, etc.
            $table->text('description')->nullable(); // Bogotá y alrededores
            $table->decimal('cost', 10, 2); // Costo de envío
            $table->decimal('free_shipping_from', 10, 2)->nullable(); // Envío gratis desde X monto
            $table->string('estimated_time'); // 2-4 horas, 4-8 horas, etc.
            $table->json('delivery_days'); // {"L": true, "M": true, ...}
            $table->time('start_time'); // Hora inicio entrega
            $table->time('end_time'); // Hora fin entrega
            $table->boolean('is_active')->default(true);
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->timestamps();
            
            // Índices
            $table->index(['shipping_method_id', 'is_active']);
            $table->index(['store_id', 'is_active']);
            
            // Nombre único por método de envío
            $table->unique(['shipping_method_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
}; 