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
        Schema::create('shipping_method_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('default_method_id')->nullable()->constrained('shipping_methods')->onDelete('set null');
            $table->integer('min_active_methods')->default(1); // Mínimo de métodos activos
            $table->timestamps();
            
            // Una configuración por tienda
            $table->unique('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_method_config');
    }
}; 