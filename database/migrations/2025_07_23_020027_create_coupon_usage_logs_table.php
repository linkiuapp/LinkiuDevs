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
        Schema::create('coupon_usage_logs', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Identificación del usuario (sesión para invitados)
            $table->string('session_id')->index(); // Laravel session ID
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Para futuro
            
            // Información del descuento aplicado
            $table->decimal('discount_applied', 10, 2); // Monto real del descuento
            $table->decimal('order_subtotal', 10, 2); // Subtotal de la orden cuando se aplicó
            
            // Metadata adicional
            $table->json('metadata')->nullable(); // Información extra (IP, user agent, etc.)
            
            $table->timestamps();
            
            // Índices
            $table->index(['coupon_id', 'session_id']);
            $table->index(['coupon_id', 'created_at']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usage_logs');
    }
};
