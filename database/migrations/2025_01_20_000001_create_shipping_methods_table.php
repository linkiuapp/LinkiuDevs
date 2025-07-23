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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['domicilio', 'pickup']); // Tipo de método
            $table->string('name'); // Nombre personalizable
            $table->boolean('is_active')->default(false); // Estado activo/inactivo
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->text('instructions')->nullable(); // Instrucciones para el cliente
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            // Campos específicos para pickup
            $table->enum('preparation_time', ['30min', '1h', '2h', '4h'])->nullable();
            $table->boolean('notification_enabled')->default(false); // WhatsApp futuro
            
            $table->timestamps();
            
            // Índices
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'type']);
            $table->unique(['store_id', 'type']); // Solo un método de cada tipo por tienda
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
}; 