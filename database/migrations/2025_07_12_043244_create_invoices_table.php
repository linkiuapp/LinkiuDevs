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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('period', ['monthly', 'quarterly', 'biannual'])->default('monthly');
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Para información adicional
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['store_id', 'status']);
            $table->index(['plan_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
