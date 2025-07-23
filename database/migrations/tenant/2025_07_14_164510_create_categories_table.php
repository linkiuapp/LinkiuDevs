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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->foreignId('icon_id')->nullable()->constrained('category_icons')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('products_count')->default(0); // Cache del contador de productos
            $table->timestamps();
            
            // Índices
            $table->unique(['store_id', 'slug']); // Slug único por tienda
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'parent_id']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
