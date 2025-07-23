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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Tipo de método (efectivo, transferencia, datáfono)
            $table->string('name'); // Nombre del método
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->text('instructions')->nullable(); // Instrucciones para el cliente (opcional)
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade'); // ID de la tienda (FK)
            $table->boolean('available_for_pickup')->default(true); // Disponible para recogida en tienda
            $table->boolean('available_for_delivery')->default(true); // Disponible para entrega a domicilio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
