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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            
            // Estados de suscripción
            $table->enum('status', ['active', 'cancelled', 'expired', 'suspended', 'grace_period'])->default('active');
            
            // Período de facturación
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'biannual'])->default('monthly');
            
            // Fechas del período actual
            $table->date('current_period_start');
            $table->date('current_period_end');
            
            // Fechas de control
            $table->timestamp('trial_start')->nullable();
            $table->timestamp('trial_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('grace_period_end')->nullable();
            
            // Próxima facturación
            $table->date('next_billing_date')->nullable();
            $table->decimal('next_billing_amount', 10, 2)->nullable();
            
            // Metadata adicional
            $table->json('metadata')->nullable(); // Para guardar info adicional como razón de cancelación, etc.
            
            $table->timestamps();
            
            // Índices
            $table->index(['store_id', 'status']);
            $table->index('next_billing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
