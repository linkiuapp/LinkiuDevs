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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('code')->index(); // NAVIDAD2024
            $table->string('name'); // Descuento Navidad 2024
            $table->text('description')->nullable(); // 15% de descuento en toda la tienda
            
            // Tipo de aplicación (global, categories, products)
            $table->enum('type', ['global', 'categories', 'products'])->default('global');
            
            // Tipo y valor del descuento
            $table->enum('discount_type', ['fixed', 'percentage']); // fixed = valor fijo, percentage = porcentaje
            $table->decimal('discount_value', 10, 2); // 15 (para 15%) o 5000 (para $5,000)
            $table->decimal('max_discount_amount', 10, 2)->nullable(); // Para porcentajes: máximo $10,000
            
            // Restricciones
            $table->decimal('min_purchase_amount', 10, 2)->nullable(); // Compra mínima $50,000
            $table->integer('max_uses')->nullable(); // Límite de usos: 100
            $table->integer('current_uses')->default(0); // Usos actuales
            $table->integer('uses_per_session')->nullable(); // Usos por sesión (cliente)
            
            // Vigencia
            $table->datetime('start_date')->nullable(); // Fecha inicio
            $table->datetime('end_date')->nullable(); // Fecha fin
            
            // Días de la semana y horarios
            $table->json('days_of_week')->nullable(); // ["monday", "tuesday", "wednesday"]
            $table->time('start_time')->nullable(); // 09:00
            $table->time('end_time')->nullable(); // 18:00
            
            // Estados
            $table->boolean('is_active')->default(true); // Activo/Inactivo
            $table->boolean('is_public')->default(true); // Público/Privado
            $table->boolean('is_automatic')->default(false); // Se aplica automáticamente
            
            // Relación con tienda
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->unique(['code', 'store_id']); // Código único por tienda
            $table->index(['store_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
