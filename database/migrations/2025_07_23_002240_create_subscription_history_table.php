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
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            
            // Cambios de plan
            $table->foreignId('old_plan_id')->nullable()->constrained('plans')->onDelete('set null');
            $table->foreignId('new_plan_id')->nullable()->constrained('plans')->onDelete('set null');
            
            // Cambios de ciclo de facturación
            $table->enum('old_billing_cycle', ['monthly', 'quarterly', 'biannual'])->nullable();
            $table->enum('new_billing_cycle', ['monthly', 'quarterly', 'biannual'])->nullable();
            
            // Cambios de estado
            $table->enum('old_status', ['active', 'cancelled', 'expired', 'suspended', 'grace_period'])->nullable();
            $table->enum('new_status', ['active', 'cancelled', 'expired', 'suspended', 'grace_period'])->nullable();
            
            // Tipo de cambio
            $table->enum('change_type', [
                'plan_upgrade', 
                'plan_downgrade', 
                'billing_cycle_change', 
                'cancellation', 
                'reactivation', 
                'suspension', 
                'expiration',
                'creation'
            ]);
            
            // Razón del cambio
            $table->text('change_reason')->nullable();
            
            // Usuario que realizó el cambio
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('changed_by_role', ['store_admin', 'super_admin', 'system'])->default('system');
            
            // Información financiera del cambio
            $table->decimal('old_amount', 10, 2)->nullable();
            $table->decimal('new_amount', 10, 2)->nullable();
            $table->decimal('proration_amount', 10, 2)->nullable(); // Para futuros prorrateos
            
            // Metadata adicional
            $table->json('metadata')->nullable(); // Info adicional como IP, user agent, etc.
            
            $table->timestamp('changed_at');
            $table->timestamps();
            
            // Índices
            $table->index(['store_id', 'changed_at']);
            $table->index(['subscription_id', 'change_type']);
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_history');
    }
};
