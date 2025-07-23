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
        Schema::create('store_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            // Logo y Favicon
            $table->string('logo_url')->nullable();
            $table->string('logo_webp_url')->nullable();
            $table->string('favicon_url')->nullable();
            
            // Header
            $table->enum('header_background_type', ['solid'])->default('solid');
            $table->string('header_background_color', 20)->nullable();
            
            // Textos
            $table->string('header_text_color', 20)->nullable();
            $table->string('header_description_color', 20)->nullable();
            
            // Estado
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('store_design_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_design_id')->constrained('store_designs')->onDelete('cascade');
            $table->json('data');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_design_histories');
        Schema::dropIfExists('store_designs');
    }
}; 