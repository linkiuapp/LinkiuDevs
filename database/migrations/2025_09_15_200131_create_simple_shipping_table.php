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
        Schema::create('simple_shipping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            
            // Recogida en tienda
            $table->boolean('pickup_enabled')->default(true);
            $table->text('pickup_instructions')->nullable();
            $table->string('pickup_preparation_time')->default('1h');
            
            // Envío local
            $table->boolean('local_enabled')->default(true);
            $table->decimal('local_cost', 10, 2)->default(3000);
            $table->decimal('local_free_from', 10, 2)->nullable();
            $table->string('local_city')->nullable();
            $table->text('local_instructions')->nullable();
            $table->string('local_preparation_time')->default('2h');
            
            // Envío nacional
            $table->boolean('national_enabled')->default(false);
            $table->decimal('national_free_from', 10, 2)->nullable();
            $table->text('national_instructions')->nullable();
            
            // Configuración para ciudades no encontradas
            $table->boolean('allow_unlisted_cities')->default(true);
            $table->decimal('unlisted_cities_cost', 10, 2)->default(12000);
            $table->string('unlisted_cities_message')->default('Contacta para confirmar disponibilidad');
            
            $table->timestamps();
            
            // Índices
            $table->unique('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simple_shipping');
    }
};
