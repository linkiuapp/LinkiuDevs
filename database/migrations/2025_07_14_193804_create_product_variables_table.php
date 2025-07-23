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
        Schema::create('product_variables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['radio', 'checkbox', 'text', 'numeric']);
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->decimal('min_value', 10, 2)->nullable(); // Para tipo numérico
            $table->decimal('max_value', 10, 2)->nullable(); // Para tipo numérico
            $table->timestamps();
            
            // Índices
            $table->unique(['store_id', 'name']); // Nombre único por tienda
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'type']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variables');
    }
};
