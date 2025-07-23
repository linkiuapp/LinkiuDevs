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
        Schema::create('variable_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variable_id')->constrained('product_variables')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price_modifier', 10, 2)->default(0); // Modificador de precio
            $table->string('color_hex')->nullable(); // Color opcional para opciones
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Índices
            $table->index(['variable_id', 'is_active']);
            $table->index(['variable_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variable_options');
    }
};
