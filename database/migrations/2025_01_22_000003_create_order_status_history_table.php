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
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            
            // Relación con el pedido
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Estados (antes y después)
            $table->enum('old_status', [
                'pending', 'confirmed', 'preparing', 'shipped', 'delivered', 'cancelled'
            ])->nullable(); // Null para la creación inicial
            $table->enum('new_status', [
                'pending', 'confirmed', 'preparing', 'shipped', 'delivered', 'cancelled'
            ]);
            
            // Usuario que realizó el cambio
            $table->string('changed_by'); // Nombre del usuario/admin o "Sistema"
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // ID del usuario si existe
            
            // Notas del cambio
            $table->text('notes')->nullable();
            
            $table->timestamp('created_at');
            
            // Índices para optimización
            $table->index(['order_id', 'created_at']);
            $table->index(['order_id', 'new_status']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
}; 