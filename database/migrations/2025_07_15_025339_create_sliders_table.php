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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('url')->nullable();
            $table->enum('url_type', ['internal', 'external', 'none'])->default('none');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_scheduled')->default(false);
            $table->boolean('is_permanent')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('scheduled_days')->nullable(); // {"monday": true, "tuesday": false, ...}
            $table->integer('sort_order')->default(0);
            $table->enum('transition_duration', ['3', '5', '7'])->default('5');
            $table->timestamps();

            // Índices
            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'sort_order']);
            $table->index(['store_id', 'is_scheduled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
