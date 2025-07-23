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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image_path'); // Ruta de la imagen original
            $table->string('thumbnail_path')->nullable(); // 150x150
            $table->string('medium_path')->nullable(); // 500x500
            $table->string('large_path')->nullable(); // 1200x1200
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_main')->default(false);
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['product_id', 'sort_order']);
            $table->index(['product_id', 'is_main']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
