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
        Schema::create('bulk_import_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->enum('status', ['queued', 'processing', 'completed', 'failed', 'cancelled'])->default('queued');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('error_details')->nullable();
            $table->integer('processing_time_seconds')->nullable();
            $table->string('template_type')->default('basic');
            $table->json('column_mapping')->nullable();
            $table->json('validation_errors')->nullable();
            $table->json('created_stores_data')->nullable();
            $table->string('queue_name')->default('bulk-import');
            $table->integer('job_attempts')->default(0);
            $table->decimal('memory_usage_mb', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_import_logs');
    }
};
