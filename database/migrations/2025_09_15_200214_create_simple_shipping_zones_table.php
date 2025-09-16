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
        Schema::create('simple_shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simple_shipping_id')->constrained('simple_shipping')->onDelete('cascade');
            $table->string('name'); // "Ciudades Principales", "Costa Caribe", etc.
            $table->decimal('cost', 10, 2); // Costo del envío
            $table->string('delivery_time'); // Tiempo de entrega
            $table->json('cities'); // Array de ciudades ["Bogotá", "Medellín", "Cali"]
            $table->integer('sort_order')->default(0); // Orden de las zonas
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index(['simple_shipping_id', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simple_shipping_zones');
    }
};
