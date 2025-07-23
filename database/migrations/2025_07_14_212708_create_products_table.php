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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Precio base
            $table->enum('type', ['simple', 'variable'])->default('simple');
            $table->string('sku')->unique();
            $table->unsignedBigInteger('main_image_id')->nullable(); // Se agregará FK después
            $table->boolean('is_active')->default(true);
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'type']);
            $table->index('slug');
            $table->index('sku');
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
