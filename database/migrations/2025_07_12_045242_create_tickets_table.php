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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // TK-YYYYMM0001
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['technical', 'billing', 'general', 'feature_request'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('metadata')->nullable(); // Para datos adicionales
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['store_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['assigned_to', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
